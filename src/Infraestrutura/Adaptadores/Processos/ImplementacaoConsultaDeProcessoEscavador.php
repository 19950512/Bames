<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Processos;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\Movimentacao;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraMovimentacoesDoProcesso;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraProcessosPorOAB;
use Exception;
use Override;

final class ImplementacaoConsultaDeProcessoEscavador implements ConsultaDeProcesso
{

    private string $accessToken;
    public function __construct(
        private Ambiente $ambiente
    ){
        $this->accessToken = $this->ambiente->get('API_ESCAVADOR_ACCESS_TOKEN');
    }

    #[Override] public function monitorarUmProcesso(string $CNJ): true
    {

        if($this->ambiente->get('APP_DEBUG')){
            return true;
        }

        if(empty($CNJ)){
            throw new Exception('CNJ não informado');
        }

        $fileName = 'resposta-monitorar-um-processo-'.$CNJ.'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            // Hoje já foi solicitado a atualização do processo
            return true;
        }

        $curl = curl_init();

        $parametros = [
            'tipo' => 'UNICO',
            // 'estado_oab' => 'SP', // Não é obrigatório -- Estado da OAB
            'valor' => $CNJ,
            // 'tribunal' => 'TJSP', // Não é obrigatório -- Tribunal
            'frequencia' => 'DIARIA'
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.escavador.com/api/v1/monitoramentos-tribunal",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($parametros),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Requested-With: XMLHttpRequest",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $resposta = json_decode($response, true);

        if(!is_file(__DIR__.'/'.$fileName)){
           file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }

        if(isset($resposta['error'])){
            throw new Exception($resposta['error']);
        }

        if(!isset($resposta['id'])){
            throw new Exception('ID do monitoramento não encontrado');
        }

        if(!isset($resposta['status']) or $resposta['status'] != 'FOUND'){
            throw new Exception('Processo não encontrado');
        }

        return true;
    }

    #[Override] public function solicitarAtualizacaoDoProcesso(string $CNJ): void
    {

        if($this->ambiente->get('APP_DEBUG')){
            return;
        }

        /*
         curl -X POST "https://api.escavador.com/api/v2/processos/numero_cnj/0018063-19.2013.8.26.0002/solicitar-atualizacao" \
            -H "Authorization: Bearer {access_token}" \
            -H "X-Requested-With: XMLHttpRequest" \
            -H "Content-Type: application/json" \
            -d '{"enviar_callback":1,"documentos_publicos":1}'
         */
        if(empty($CNJ)){
            throw new Exception('CNJ não informado');
        }

        $fileName = 'resposta-solicita-atualizacao-do-processo-'.$CNJ.'-'.date('Y-m-d').'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            // Hoje já foi solicitado a atualização do processo
            return;
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.escavador.com/api/v2/processos/numero_cnj/{$CNJ}/solicitar-atualizacao",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{"enviar_callback":1,"documentos_publicos":1}',
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Requested-With: XMLHttpRequest",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $resposta = json_decode($response, true);

        if(isset($resposta['error'])){
            throw new Exception($resposta['error']);
        }

        /*
         RESPOSTA
        {
            "id": 18617621,
            "status": "PENDENTE",
            "criado_em": "2024-09-25T04:38:58+00:00",
            "numero_cnj": "0341163-87.2024.3.00.0000",
            "concluido_em": null,
            "enviar_callback": "SIM"
        }
         */

        if(!is_file(__DIR__.'/'.$fileName)){
           file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }
    }

    #[Override] public function numeroDocumento(string $numeroDocumento): SaidaFronteiraProcessosPorOAB
    {
        try {
            $dados = $this->getProcessosDadosCPF($numeroDocumento);

            if(!isset($dados['envolvido_encontrado']) OR empty($dados['envolvido_encontrado'])){
                throw new Exception('Esse documento não possui processos');
            }

        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }

        return $this->processaProcessosDadosDocumento($dados);
    }

    #[Override] public function OAB(string $OAB): SaidaFronteiraProcessosPorOAB
    {

        try {
            $dados = $this->getProcessosDadosOAB($OAB);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }

        return $this->processaProcessosDadosOAB(
            dados: $dados,
            OAB: $OAB
        );
    }

    #[Override] public function obterMovimentacoesDoProcesso(string $CNJ): SaidaFronteiraMovimentacoesDoProcesso
    {

        if($this->ambiente->get('APP_DEBUG')){
            $movimetacoesDados = json_decode(file_get_contents(__DIR__.'/resposta-movimentacoes-do-processo-0341163-87.2024.3.00.0000-2024-09-25.json'), true);
            return $this->buildSaidaFronteiraMovimentacoesDoProcesso($movimetacoesDados);
        }

        $fileName = 'resposta-movimentacoes-do-processo-'.$CNJ.'-'.date('Y-m-d').'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            return $this->buildSaidaFronteiraMovimentacoesDoProcesso(json_decode(file_get_contents(__DIR__.'/'.$fileName), true));
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.escavador.com/api/v2/processos/numero_cnj/{$CNJ}/movimentacoes?limit=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Requested-With: XMLHttpRequest"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $resposta = json_decode($response, true);

        if(isset($resposta['error'])){
            throw new Exception($resposta['error']);
        }

        if(!is_file(__DIR__.'/'.$fileName)){
            file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }

        return $this->buildSaidaFronteiraMovimentacoesDoProcesso($resposta);
    }

    private function getProcessosDadosOAB(string $OAB): array
    {

        if($this->ambiente->get('APP_DEBUG')){
            return json_decode(file_get_contents(__DIR__.'/resposta_oab_andreiazonta.json'), true);
        }

        $oabfilename = preg_replace('/[^0-9]/', '', $OAB);
        $fileName = 'resposta-oab-'.$oabfilename.'-'.date('Y-m').'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            return json_decode(file_get_contents(__DIR__.'/'.$fileName), true);
        }

        // $OAB === OAB/RS 133.074
        // extraia o estado e o número da OAB
        // $estado = RS
        // $numero = 133074
        $OAB = explode(' ', $OAB);
        $estado = explode('/', $OAB[0])[1];
        $numero = $OAB[1];
        $numero = preg_replace('/[^0-9]/', '', $numero);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.escavador.com/api/v2/advogado/processos?oab_estado={$estado}&oab_numero={$numero}&status=ATIVO",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Requested-With: XMLHttpRequest"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $resposta = json_decode($response, true);

        if(isset($resposta['error'])){
            throw new Exception($resposta['error']);
        }

        if(!is_file(__DIR__.'/'.$fileName)){
           file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }

        return $resposta;
    }

    private function getProcessosDadosCPF(string $numeroDocumento): array
    {
        /*
         curl -X GET -G "https://api.escavador.com/api/v2/envolvido/processos?cpf_cnpj=$documento&limit=9999" \
            -H "Authorization: Bearer {access_token}" \
            -H "X-Requested-With: XMLHttpRequest"
         */

        if(empty($numeroDocumento)){
            throw new Exception('Número do documento não informado');
        }

        if($this->ambiente->get('APP_DEBUG')){

            $fileName = 'resposta-processos-do-documento-61908533072.json';
            if(is_file(__DIR__.'/'.$fileName)){
                return json_decode(file_get_contents(__DIR__.'/'.$fileName), true);
            }

            throw new Exception('Arquivo de resposta não encontrado');
        }

        $fileName = 'resposta-processos-do-documento-'.$numeroDocumento.'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            // Hoje já foi solicitado a atualização do processo
            return json_decode(file_get_contents(__DIR__.'/'.$fileName), true);
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.escavador.com/api/v2/envolvido/processos?cpf_cnpj=$numeroDocumento&limit=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->accessToken}",
                "X-Requested-With: XMLHttpRequest",
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $resposta = json_decode($response, true);

        if(!is_file(__DIR__.'/'.$fileName)){
           file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }

        if(isset($resposta['error'])){
            throw new Exception($resposta['error']);
        }

        return $resposta;
    }

    private function processaProcessosDadosOAB(array $dados, string $OAB): SaidaFronteiraProcessosPorOAB
    {

        $retorno = new SaidaFronteiraProcessosPorOAB(
            payload_request: [],
            payload_response: $dados,
            nomeCompleto: (string) ($dados['advogado_encontrado']['nome'] ?? ''),
            tipo: (string) ($dados['advogado_encontrado']['tipo'] ?? ''),
            quantidadeDeProcessos: (int) ($dados['advogado_encontrado']['quantidade_processos'] ?? 0)
        );

        if(!isset($dados['items']) or empty($dados['items'])){
            return $retorno;
        }

        foreach($dados['items'] as $processo){

            $processTemp = [
                'cnj' => $processo['numero_cnj'],
                'dataInicio' => $processo['data_inicio'],
                'dataUltimaMovimentacao' => $processo['data_ultima_movimentacao'],
                'quantidadeMovimentacoes' => $processo['quantidade_movimentacoes'],
                'dataUltimaVerificacao' => $processo['data_ultima_verificacao'],
                'fontes' => [],
                'demandante' => $processo['titulo_polo_ativo'],
                'demandado' => $processo['titulo_polo_passivo']
            ];

            foreach($processo['fontes'] as $fonte){

                $tribunalEntity = [];
                if(isset($fonte['tribunal'], $fonte['tribunal']['nome'])){
                    $tribunalEntity = [
                        'codigoTribunal' => $fonte['tribunal']['id'],
                        'nome' => $fonte['tribunal']['nome'],
                        'sigla' => $fonte['tribunal']['sigla']
                    ];
                }

                $capaEntity = [];
                if(isset($fonte['capa'], $fonte['capa']['classe'])){
                    $assuntoNormalizado = '';
                    if(isset($fonte['capa']['assunto_principal_normalizado'], $fonte['capa']['assunto_principal_normalizado']['nome'])) {
                        $assuntoNormalizado = $fonte['capa']['assunto_principal_normalizado']['nome'];
                    }
                    $capaEntity = [
                        'classe' => $fonte['capa']['classe'],
                        'assunto' => $fonte['capa']['assunto'],
                        'assuntoNormalizado' => $assuntoNormalizado,
                        'area' => $fonte['capa']['area'] ?? '',
                        'orgaoJulgador' => $fonte['capa']['orgao_julgador'] ?? '',
                        'causaValor' => $fonte['capa']['valor_causa']['valor'] ?? 0,
                        'causaMoeda' => $fonte['capa']['valor_causa']['moeda'] ?? '',
                        'dataDistribuicao' => $fonte['capa']['data_distribuicao'],
                        'dataArquivamento' => $fonte['capa']['data_arquivamento'] ?? '',
                        'informacoesComplementares' => $fonte['capa']['informacoes_complementares']
                    ];
                }

                $envolvidos = [];
                if(isset($fonte['envolvidos']) and !empty($fonte['envolvidos']) and is_array($fonte['envolvidos'])){
                    foreach($fonte['envolvidos'] as $envolvido){

                        // Se o envolvido tiver OAB, vamos tentar identificar o advogado do $OAB consultado
                        if(isset($envolvido['oabs']) and is_array($envolvido['oabs'])){

                            foreach($envolvido['oabs'] as $oab){

                                $somenteNumerosoab = preg_replace('/[^0-9]/', '', $OAB);

                                // Se o OAB do envolvido for igual ao OAB consultado, vamos tentar identificar o advogado CASO ele não tenha nome informado
                                if($oab['numero'] == $somenteNumerosoab){

                                    if(empty($retorno->nomeCompleto)){
                                        $retorno->nomeCompleto = $envolvido['nome'];
                                    }

                                    if(empty($retorno->tipo)){
                                        $retorno->tipo = $oab['tipo'];
                                    }
                                    break;
                                }
                            }
                        }

                        $oabTemp = '';
                        if(isset($envolvido['oabs']) and is_array($envolvido['oabs'])) {
                            foreach ($envolvido['oabs'] as $oab) {
                                $oabTemp = $oab['uf'] . ' ' . $oab['numero'];
                                break;
                            }
                        }

                        $envolvidos[] = [
                            'nomeCompleto' => $envolvido['nome'],
                            'quantidadeProcessos' => $envolvido['quantidade_processos'],
                            'tipoNatureza' => $envolvido['tipo_pessoa'],
                            'documento' => $envolvido['cpf'] ?? $envolvido['cnpj'] ?? '',
                            'tipo' => $envolvido['tipo_normalizado'],
                            'polo' => $envolvido['polo'],
                            'sufixo' => $envolvido['sufixo'] ?? '',
                            'oab' => $oabTemp
                        ];
                    }
                }

                $processTemp['fontes'][] = [
                    'id' => $fonte['id'],
                    'descricao' => $fonte['descricao'],
                    'nome' => $fonte['nome'],
                    'sigla' => $fonte['sigla'],
                    'tipo' => $fonte['tipo'],
                    'dataInicio' => $fonte['data_inicio'],
                    'dataUltimaMovimentacao' => $fonte['data_ultima_movimentacao'],
                    'segredoJustica' => $fonte['segredo_justica'] ?? false,
                    'arquivado' => $fonte['arquivado'] ?? false,
                    'fisico' => $fonte['fisico'] ?? false,
                    'sistema' => $fonte['sistema'],
                    'grau' => $fonte['grau'],
                    'capaEntity' => $capaEntity,
                    'url' => $fonte['url'] ?? '',
                    'tribunalEntity' => $tribunalEntity,
                    'quantidadeMovimentacoes' => $fonte['quantidade_movimentacoes'],
                    'quantidadeEnvolvidos' => $fonte['quantidade_envolvidos'],
                    'dataUltimaVerificacao' => $fonte['data_ultima_verificacao'],
                    'envolvidos' => $envolvidos
                ];
            }

            $retorno->adicionar(json_decode(json_encode($processTemp)));
        }

        return $retorno;
    }

    private function processaProcessosDadosDocumento(array $dados): SaidaFronteiraProcessosPorOAB
    {

        $retorno = new SaidaFronteiraProcessosPorOAB(
            payload_request: [],
            payload_response: $dados,
            nomeCompleto: (string) ($dados['envolvido_encontrado']['nome'] ?? ''),
            tipo: (string) ($dados['envolvido_encontrado']['tipo_pessoa'] ?? ''),
            quantidadeDeProcessos: (int) ($dados['envolvido_encontrado']['quantidade_processos'] ?? 0)
        );

        if(!isset($dados['items']) or empty($dados['items'])){
            return $retorno;
        }

        foreach($dados['items'] as $processo){

            $processTemp = [
                'cnj' => $processo['numero_cnj'],
                'dataInicio' => $processo['data_inicio'],
                'dataUltimaMovimentacao' => $processo['data_ultima_movimentacao'],
                'quantidadeMovimentacoes' => $processo['quantidade_movimentacoes'],
                'dataUltimaVerificacao' => $processo['data_ultima_verificacao'],
                'fontes' => [],
                'demandante' => $processo['titulo_polo_ativo'],
                'demandado' => $processo['titulo_polo_passivo'],
                'processos_relacionados' => $processo['processos_relacionados'] ?? []
            ];

            foreach($processo['fontes'] as $fonte){

                $tribunalEntity = [];
                if(isset($fonte['tribunal'], $fonte['tribunal']['nome'])){
                    $tribunalEntity = [
                        'codigoTribunal' => $fonte['tribunal']['id'],
                        'nome' => $fonte['tribunal']['nome'],
                        'sigla' => $fonte['tribunal']['sigla']
                    ];
                }

                $capaEntity = [];
                if(isset($fonte['capa'], $fonte['capa']['classe'])){
                    $assuntoNormalizado = '';
                    if(isset($fonte['capa']['assunto_principal_normalizado'], $fonte['capa']['assunto_principal_normalizado']['nome'])) {
                        $assuntoNormalizado = $fonte['capa']['assunto_principal_normalizado']['nome'];
                    }
                    $capaEntity = [
                        'classe' => $fonte['capa']['classe'],
                        'assunto' => $fonte['capa']['assunto'],
                        'assuntoNormalizado' => $assuntoNormalizado,
                        'area' => $fonte['capa']['area'] ?? '',
                        'orgaoJulgador' => $fonte['capa']['orgao_julgador'] ?? '',
                        'causaValor' => $fonte['capa']['valor_causa']['valor'] ?? 0,
                        'causaMoeda' => $fonte['capa']['valor_causa']['moeda'] ?? '',
                        'dataDistribuicao' => $fonte['capa']['data_distribuicao'],
                        'dataArquivamento' => $fonte['capa']['data_arquivamento'] ?? '',
                        'informacoesComplementares' => $fonte['capa']['informacoes_complementares']
                    ];
                }

                $envolvidos = [];
                if(isset($fonte['envolvidos']) and !empty($fonte['envolvidos']) and is_array($fonte['envolvidos'])){
                    foreach($fonte['envolvidos'] as $envolvido){

                        $envolvidos[] = [
                            'nomeCompleto' => $envolvido['nome'],
                            'quantidadeProcessos' => $envolvido['quantidade_processos'],
                            'tipoNatureza' => $envolvido['tipo_pessoa'],
                            'documento' => $envolvido['cpf'] ?? $envolvido['cnpj'] ?? '',
                            'tipo' => $envolvido['tipo_normalizado'],
                            'polo' => $envolvido['polo'],
                            'sufixo' => $envolvido['sufixo'] ?? '',
                            'oabs' => $envolvido['oabs'] ?? []
                        ];
                    }
                }

                $processTemp['fontes'][] = [
                    'id' => $fonte['id'],
                    'descricao' => $fonte['descricao'],
                    'nome' => $fonte['nome'],
                    'sigla' => $fonte['sigla'],
                    'tipo' => $fonte['tipo'],
                    'dataInicio' => $fonte['data_inicio'],
                    'dataUltimaMovimentacao' => $fonte['data_ultima_movimentacao'],
                    'segredoJustica' => $fonte['segredo_justica'] ?? false,
                    'arquivado' => $fonte['arquivado'] ?? false,
                    'fisico' => $fonte['fisico'] ?? false,
                    'sistema' => $fonte['sistema'],
                    'grau' => $fonte['grau'],
                    'capaEntity' => $capaEntity,
                    'url' => $fonte['url'] ?? '',
                    'tribunalEntity' => $tribunalEntity,
                    'quantidadeMovimentacoes' => $fonte['quantidade_movimentacoes'],
                    'quantidadeEnvolvidos' => $fonte['quantidade_envolvidos'],
                    'dataUltimaVerificacao' => $fonte['data_ultima_verificacao'],
                    'envolvidos' => $envolvidos
                ];
            }

            $retorno->adicionar(json_decode(json_encode($processTemp)));
        }

        return $retorno;
    }

    private function buildSaidaFronteiraMovimentacoesDoProcesso(array $movimetacoesDados): SaidaFronteiraMovimentacoesDoProcesso
    {
        $movimentacoesDoProcesso = new SaidaFronteiraMovimentacoesDoProcesso();

        $movimentacoes = $movimetacoesDados['items'] ?? [];
        foreach($movimentacoes as $movimentacao){

            $classificacaoPreditaNome = '';
            if(isset($movimentacao['classificacao_predita'], $movimentacao['classificacao_predita']['nome'])){
                $classificacaoPreditaNome = $movimentacao['classificacao_predita']['nome'];
            }

            $classificacaoPreditaDescricao = '';
            if(isset($movimentacao['classificacao_predita'], $movimentacao['classificacao_predita']['descricao'])){
                $classificacaoPreditaDescricao = $movimentacao['classificacao_predita']['descricao'];
            }
            $classificacaoPreditaHierarquia = '';
            if(isset($movimentacao['classificacao_predita'], $movimentacao['classificacao_predita']['hierarquia'])){
                $classificacaoPreditaHierarquiaOriginal = $movimentacao['classificacao_predita']['hierarquia'];

                $classificacaoPreditaHierarquia = explode(' > ', $classificacaoPreditaHierarquiaOriginal);
                $classificacaoPreditaHierarquia = array_slice($classificacaoPreditaHierarquia, 2);

                $classificacaoPreditaHierarquia = implode(' > ', $classificacaoPreditaHierarquia);

                if(empty($classificacaoPreditaHierarquia)){
                    $classificacaoPreditaHierarquia = $classificacaoPreditaHierarquiaOriginal;
                }
            }

            $movimentacaoTemp = new Movimentacao(
                id: (string) $movimentacao['id'],
                data: (string) $movimentacao['data'],
                tipo: (string) $movimentacao['tipo'],
                tipoPublicacao: (string) $movimentacao['tipo_publicacao'],
                classificacaoPreditaNome: (string) $classificacaoPreditaNome,
                classificacaoPreditaDescricao: (string) $classificacaoPreditaDescricao,
                classificacaoPreditaHierarquia: (string) $classificacaoPreditaHierarquia,
                conteudo: (string) $movimentacao['conteudo'],
                textoCategoria: (string) $movimentacao['texto_categoria'],
                fonteProcessoFonteId: (string) $movimentacao['fonte']['processo_fonte_id'] ?? '',
                fonteFonteId: (string) $movimentacao['fonte']['fonte_id'] ?? '',
                fonteNome: (string) $movimentacao['fonte']['nome'] ?? '',
                fonteTipo: (string) $movimentacao['fonte']['tipo'] ?? '',
                fonteSigla: (string) $movimentacao['fonte']['sigla'] ?? '',
                fonteGrau: (string) $movimentacao['fonte']['grau'] ?? '',
                fonteGrauFormatado: (string) $movimentacao['fonte']['grau_formatado'] ?? '',
            );
            $movimentacoesDoProcesso->adicionarMovimentacao($movimentacaoTemp);
        }

        return $movimentacoesDoProcesso;
    }
}