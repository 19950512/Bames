<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\ConsultarInformacoesNaInternet;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\ConsultarInformacoesNaInternet;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarCPF;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\Endereco\CEP;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\Telefone;
use Exception;

final class ImplementacaoEscavadorConsultarInformacoesNaInternet implements ConsultarInformacoesNaInternet
{
    private string $token;

    public function __construct(
        readonly private Ambiente $ambiente
    ){
        $this->token = $this->ambiente->get('API_BRASIL_TOKEN');
    }

    public function consultarCPF(string $cpf): SaidaFronteiraConsultarCPF
    {

        if(!$this->ambiente->get('API_BRASIL_UTILIZAR')){
            throw new Exception("API Brasil está desativada.");
        }

        try {

            $cpf = (new CPF($cpf))->get();

            $documento = $cpf;

            $response = $this->getResponseCPF($cpf);

            $nomeCompleto = '';
            $dataNascimento = '';
            $nomeMae = '';
            $cpfMae = '';
            $nomePai = '';
            $cpfPai = '';
            $sexo = '';
            $telefones = [];
            $enderecos = [];
            $emails = [];
            $familiares = [];
            $rg = '';
            $pis = '';
            $carteiraTrabalho = '';

            $conteudo = $response['response']['content'] ?? [];
            if(isset($response['response']) and is_array($response['response'])){

                try {
                    $nomeCompleto = new NomeCompleto($conteudo['nome']['conteudo']['nome']);
                    $nomeCompleto = $nomeCompleto->get();
                }catch (Exception $e){
                    $nomeCompleto = $conteudo['nome']['conteudo']['nome'];
                }

                preg_match('/(\d{2})T\d{2}:\d{2}:\d{2}\.\d{3}Z\/(\d{2})\/(\d{4})/', $conteudo['nome']['conteudo']['data_nascimento'], $matches);

                $date = '';
                if(is_array($matches) and count($matches) === 4){
                    $date = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
                }else{
                    $date = $conteudo['nome']['conteudo']['data_nascimento'];
                }

                $dataNascimento = $date;
                if(isset($conteudo['nome']['conteudo']['mae']) and !empty($conteudo['nome']['conteudo']['mae'])){
                    try {
                        $nomeMae = new NomeCompleto($conteudo['nome']['conteudo']['mae']);
                        $nomeMae = $nomeMae->get();
                    }catch (Exception $e){
                        $nomeMae = $conteudo['nome']['conteudo']['mae'];
                    }
                }
                if(isset($conteudo['nome']['conteudo']['pai']) and !empty($conteudo['nome']['conteudo']['pai'])){
                    try {
                        $nomePai = new NomeCompleto($conteudo['nome']['conteudo']['pai']);
                        $nomePai = $nomePai->get();
                    }catch (Exception $e){
                        $nomePai = $conteudo['nome']['conteudo']['pai'];
                    }
                }

                if(isset($conteudo['outros_documentos'], $conteudo['outros_documentos']['rg']) and !empty($conteudo['outros_documentos']['rg'])){
                    $rg = $conteudo['outros_documentos']['rg'];
                }

                if(isset($conteudo['outros_documentos'], $conteudo['outros_documentos']['pis']) and !empty($conteudo['outros_documentos']['pis'])){
                    $pis = $conteudo['outros_documentos']['pis'];
                }

                if(isset($conteudo['outros_documentos'], $conteudo['outros_documentos']['ctps']) and !empty($conteudo['outros_documentos']['ctps'])){
                    $carteiraTrabalho = $conteudo['outros_documentos']['ctps'];
                }

                if(isset($conteudo['dados_parentes'], $conteudo['dados_parentes']['conteudo'])){
                    foreach($conteudo['dados_parentes']['conteudo'] as $parente){
                        if($parente['tipo'] === 'MÃE'){
                            if(isset($parente['cpf']) and !empty($parente['cpf'])) {

                                try {
                                    $cpfMae = new CPF($parente['cpf']);
                                    $cpfMae = $cpfMae->get();
                                }catch (Exception $e){
                                    $cpfMae = $parente['cpf'];
                                }
                            }
                        }
                        if($parente['tipo'] === 'PAI'){
                            if(isset($parente['cpf']) and !empty($parente['cpf'])) {
                                try {
                                    $cpfPai = new CPF($parente['cpf']);
                                    $cpfPai = $cpfPai->get();
                                }catch (Exception $e){
                                    $cpfPai = $parente['cpf'];
                                }
                            }

                            if(isset($parente['nome']) and !empty($parente['nome'])){
                                try {
                                    $nomePai = new NomeCompleto($parente['nome']);
                                    $nomePai = $nomePai->get();
                                }catch (Exception $e){
                                    $nomePai = $parente['nome'];
                                }
                            }
                        }

                        $parenteNome = '';
                        if(isset($parente['nome']) and !empty($parente['nome'])){
                            try {
                                $parenteNome = new NomeCompleto($parente['nome']);
                                $parenteNome = $parenteNome->get();
                            }catch (Exception $e){
                                $parenteNome = $parente['nome'];
                            }
                        }

                        $parenteDocumento = '';
                        if(isset($parente['cpf']) and !empty($parente['cpf'])){
                            try {
                                $parenteDocumento = new CPF($parente['cpf']);
                                $parenteDocumento = $parenteDocumento->get();
                            }catch (Exception $e){
                                $parenteDocumento = $parente['cpf'];
                            }
                        }

                        $parentesco = '';
                        if(isset($parente['tipo']) and !empty($parente['tipo'])){
                            $parentesco = trim(explode('(',$parente['tipo'])[0] ?? '');
                        }

                        $familiares[] = [
                            'nome' => $parenteNome,
                            'cpf' => $parenteDocumento,
                            'parentesco' => $parentesco
                        ];
                    }
                }

                $sexo = match($conteudo['nome']['conteudo']['sexo']){
                    'M' => 'Masculino',
                    'F' => 'Feminino',
                    default => 'Outro'
                };

                if(isset($conteudo['pesquisa_telefones']['conteudo']) and is_array($conteudo['pesquisa_telefones']['conteudo'])){
                    foreach($conteudo['pesquisa_telefones']['conteudo'] as $telefone){

                        $telefoneEncontrado = '';
                        try {
                            $telefoneEncontrado = new Telefone($telefone['numero']);
                            $telefoneEncontrado = $telefoneEncontrado->get();
                        }catch (Exception $e){
                            $telefoneEncontrado = $telefone['numero'];
                        }
                        $telefones[] = $telefoneEncontrado;
                    }
                }
                if(isset($conteudo['pessoas_contato']['conteudo']) and is_array($conteudo['pessoas_contato']['conteudo'])){
                    foreach($conteudo['pessoas_contato']['conteudo'] as $telefone){
                        $telefoneEncontrado = '';
                        try {
                            $telefoneEncontrado = new Telefone($telefone['numero']);
                            $telefoneEncontrado = $telefoneEncontrado->get();
                        }catch (Exception $e){
                            $telefoneEncontrado = $telefone['numero'];
                        }
                        $telefones[] = $telefoneEncontrado;
                    }
                }

                if(isset($conteudo['pesquisa_enderecos']['conteudo']) and is_array($conteudo['pesquisa_enderecos']['conteudo'])){
                    foreach($conteudo['pesquisa_enderecos']['conteudo'] as $endereco){


                        $cepEndereco = '';
                        if(isset($endereco['cep']) and !empty($endereco['cep'])) {
                            try {
                                $cepEndereco = new CEP($endereco['cep']);
                                $cepEndereco = $cepEndereco->get();
                            }catch (Exception $e){
                                $cepEndereco = $endereco['cep'];
                            }
                        }

                        $logradouroEndereco = '';
                        if(isset($endereco['logradouro']) and !empty($endereco['logradouro'])) {

                            try {
                                $logradouroEndereco = new Apelido($endereco['logradouro']);
                                $logradouroEndereco = $logradouroEndereco->get();
                            }catch (Exception $e){
                                $logradouroEndereco = $endereco['logradouro'];
                            }
                        }

                        $bairroEndereco = '';
                        if(isset($endereco['bairro']) and !empty($endereco['bairro'])) {
                            try {
                                $bairroEndereco = new Apelido($endereco['bairro']);
                                $bairroEndereco = $bairroEndereco->get();
                            }catch (Exception $e){
                                $bairroEndereco = $endereco['bairro'];
                            }
                        }

                        $cidadeEndereco = '';
                        if(isset($endereco['cidade']) and !empty($endereco['cidade'])) {
                            try {
                                $cidadeEndereco = new Apelido($endereco['cidade']);
                                $cidadeEndereco = $cidadeEndereco->get();
                            }catch (Exception $e){
                                $cidadeEndereco = $endereco['cidade'];
                            }
                        }

                        $enderecos[] = [
                            'logradouro' => $logradouroEndereco,
                            'numero' => $endereco['numero'] ?? '',
                            'complemento' => $endereco['complemento'] ?? '',
                            'bairro' => $bairroEndereco,
                            'cep' => $cepEndereco,
                            'cidade' => $cidadeEndereco,
                            'estado' => $endereco['estado'] ?? ''
                        ];
                    }
                }

                if(isset($conteudo['emails']['conteudo']) and is_array($conteudo['emails']['conteudo'])){
                    foreach($conteudo['emails']['conteudo'] as $email){
                        $emailEncontrado = '';
                        try {
                            $emailEncontrado = new Email($email['email']);
                            $emailEncontrado = $emailEncontrado->get();
                        }catch (Exception $e){
                            $emailEncontrado = $email['email'];
                        }
                        $emails[] = $emailEncontrado;
                    }
                }
            }

            $retorno = new SaidaFronteiraConsultarCPF(
                documento: $documento,
                nomeCompleto: $nomeCompleto,
                dataNascimento: $dataNascimento,
                rg: $rg,
                pis: $pis,
                carteiraTrabalho: $carteiraTrabalho,
                nomeMae: $nomeMae,
                cpfMae: $cpfMae,
                nomePai: $nomePai,
                cpfPai: $cpfPai,
                familiares: $familiares,
                sexo: $sexo,
                telefones: $telefones,
                enderecos: $enderecos,
                emails: $emails,
            );

            return $retorno;

        }catch(Exception $e){
            throw new Exception("Erro ao consultar CPF na API Brasil: {$e->getMessage()}");
        }
    }

    private function getResponseCPF(string $cpf): array
    {

        if($this->ambiente->get('API_BRASIL_TOKEN') === ''){
            throw new Exception("Token não configurado.");
        }

        if($this->ambiente->get('APP_DEBUG')){
            $retornoInfomacoes = json_decode(file_get_contents(__DIR__.'/resposta-consulta-cpf-luana-cecilia-original.json'), true);

            if(is_string($retornoInfomacoes)){
                return json_decode($retornoInfomacoes, true);
            }

            return $retornoInfomacoes;
        }

        $fileName = 'resposta-consulta-cpf-'.$cpf.'-'.date('Y-m').'.json';
        if(is_file(__DIR__.'/'.$fileName)){
            $retornoInfomacoes = json_decode(file_get_contents(__DIR__.'/'.$fileName), true);
            if(is_string($retornoInfomacoes)){
                return json_decode($retornoInfomacoes, true);
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://gateway.apibrasil.io/api/v2/dados/cpf/credits",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                "cpf" => $cpf
            ]),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$this->token}"
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        if(!is_file(__DIR__.'/'.$fileName)){
            file_put_contents(__DIR__.'/'.$fileName,
               mb_convert_encoding(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'UTF-8', 'auto')
            );
        }

        return json_decode($response, true);
    }
}