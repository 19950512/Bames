<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cliente;

use App\Dominio\Entidades\Cliente\Enums\Sexo;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\Endereco\CEP;
use App\Dominio\ObjetoValor\Endereco\Endereco;
use App\Dominio\ObjetoValor\Endereco\Estado;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;
use App\Dominio\ObjetoValor\Endereco\Pais;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\Telefone;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use DateTime;
use Exception;

class EntidadeCliente
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public NomeCompleto $nomeCompleto,
        public Email $email,
        public Telefone $telefone,
        public DocumentoIdentificacao $documento,
        public Endereco $endereco,
        public DateTime $dataNascimento,
        public Sexo $sexo,
        public ?NomeCompleto $nomeDaMae = null,
        public ?DocumentoIdentificacao $documentoDaMae = null,
        public ?NomeCompleto $nomeDoPai = null,
        public ?DocumentoIdentificacao $documentoDoPai = null,
    ){}

    public function subistituicoes(): array
    {
        return [
            // 'cliente_codigo' => $this->codigo->get(),
            '{{cliente_nome}}' => $this->nomeCompleto->get(),
            '{{cliente_email}}' => $this->email->get(),
            '{{cliente_telefone}}' => $this->telefone->get(),
            '{{cliente_documento_numero}}' => $this->documento->get(),
            '{{cliente_documento_tipo}}' => $this->documento->tipo(),
            '{{cliente_profissao}}' => 'Trabalhador',
            '{{cliente_endereco_completo}}' => $this->endereco->enderecoCompleto(),
            '{{cliente_endereco}}' => is_a($this->endereco->rua, TextoSimples::class) ? $this->endereco->rua->get() : '',
            '{{cliente_endereco_numero}}' => is_a($this->endereco->numero, TextoSimples::class) ? $this->endereco->numero->get() : '',
            '{{cliente_endereco_bairro}}' => is_a($this->endereco->bairro, TextoSimples::class) ? $this->endereco->bairro->get() : '',
            '{{cliente_endereco_cidade}}' => is_a($this->endereco->cidade, TextoSimples::class) ? $this->endereco->cidade->get() : '',
            '{{cliente_endereco_estado}}' => is_a($this->endereco->estado, Estado::class) ? $this->endereco->estado->getUF() : '',
            '{{cliente_endereco_pais}}' => is_a($this->endereco->pais, Pais::class) ? $this->endereco->pais->getUF() : '',
            '{{cliente_endereco_cep}}' => is_a($this->endereco->cep, CEP::class) ? $this->endereco->cep->get() : '',
            '{{cliente_endereco_complemento}}' => is_a($this->endereco->complemento, TextoSimples::class) ? $this->endereco->complemento->get() : '',
            '{{cliente_endereco_referencia}}' => is_a($this->endereco->referencia, TextoSimples::class) ? $this->endereco->referencia->get() : '',
            '{{cliente_endereco_localizacao_latitude}}' => is_a($this->endereco->localizacao, Latitude::class) ? $this->endereco->localizacao->getLatitude() : '',
            '{{cliente_endereco_localizacao_longitude}}' => is_a($this->endereco->localizacao, Latitude::class) ? $this->endereco->localizacao->getLongitude() : '',
            '{{cliente_data_nascimento}}' => $this->dataNascimento->format('d/m/Y'),
            '{{cliente_sexo}}' => $this->sexo->getDescricao(),
            '{{cliente_nacionalidade}}' => $this->sexo == Sexo::MASCULINO ? 'Brasileiro' : 'Brasileira',

            '{{cliente_mae_nome}}' => is_a($this->nomeDaMae, NomeCompleto::class) ? $this->nomeDaMae->get() : '',
            '{{cliente_mae_documento}}' => is_a($this->documentoDaMae, DocumentoIdentificacao::class) ? $this->documentoDaMae->get() : '',

            '{{cliente_pai_nome}}' => is_a($this->nomeDoPai, NomeCompleto::class) ? $this->nomeDoPai->get() : '',
            '{{cliente_pai_documento}}' => is_a($this->documentoDoPai, DocumentoIdentificacao::class) ? $this->documentoDoPai->get() : '',
        ];
    }

    public function subistituicoesCaixaAlta(): array
    {
        $subistituicoes = $this->subistituicoes();

        foreach($subistituicoes as $chave => $valor){
            $subistituicoes[mb_strtoupper($chave)] = mb_strtoupper($valor);
        }

        return $subistituicoes;
    }

    public function comparar(EntidadeCliente $cliente): array
    {
        $diferencas = [];
        if($this->nomeCompleto->get() !== $cliente->nomeCompleto->get()){
            $diferencas['nomeCompleto'] = [
                'antigo' => $this->nomeCompleto->get(),
                'novo' => $cliente->nomeCompleto->get(),
            ];
        }

        if($this->email->get() !== $cliente->email->get()){
            $diferencas['email'] = [
                'antigo' => $this->email->get(),
                'novo' => $cliente->email->get(),
            ];
        }

        if($this->telefone->get() !== $cliente->telefone->get()){
            $diferencas['telefone'] = [
                'antigo' => $this->telefone->get(),
                'novo' => $cliente->telefone->get(),
            ];
        }

        if($this->documento->get() !== $cliente->documento->get()){
            $diferencas['documento'] = [
                'antigo' => $this->documento->get(),
                'novo' => $cliente->documento->get(),
            ];
        }

        if($this->dataNascimento->format('Y-m-d') !== $cliente->dataNascimento->format('Y-m-d')){
            $diferencas['dataNascimento'] = [
                'antigo' => $this->dataNascimento->format('Y-m-d'),
                'novo' => $cliente->dataNascimento->format('Y-m-d'),
            ];
        }

        if($this->sexo !== $cliente->sexo){
            $diferencas['sexo'] = [
                'antigo' => $this->sexo->getDescricao(),
                'novo' => $cliente->sexo->getDescricao(),
            ];
        }

        if(is_a($this->nomeDaMae, NomeCompleto::class) and is_a($cliente->nomeDaMae, NomeCompleto::class)) {
            if ($this->nomeDaMae->get() !== $cliente->nomeDaMae->get()) {
                $diferencas['nomeDaMae'] = [
                    'antigo' => $this->nomeDaMae->get(),
                    'novo' => $cliente->nomeDaMae->get(),
                ];
            }
        }

        if(is_null($this->nomeDaMae) and !is_null($cliente->nomeDaMae)){
            $diferencas['nomeDaMae'] = [
                'antigo' => '',
                'novo' => $cliente->nomeDaMae->get(),
            ];
        }

        if(!is_null($this->nomeDaMae) and is_null($cliente->nomeDaMae)){
            $diferencas['nomeDaMae'] = [
                'antigo' => $this->nomeDaMae->get(),
                'novo' => '',
            ];
        }

        $diferencasEndereco = $this->endereco->comparar($cliente->endereco);

        if(is_array($diferencasEndereco) and count($diferencasEndereco) > 0){
            foreach($diferencasEndereco as $chave => $diferenca){
                $diferencas['endereco_'.$chave] = $diferenca;
            }
        }

        return $diferencas;
    }

    public static function instanciarEntidadeCliente(SaidaFronteiraClienteDetalhado $parametros): EntidadeCliente
    {

        $estadoSigla = null;
        if(!empty($parametros->enderecoEstado)){
            try {
                $estadoSigla = new Estado($parametros->enderecoEstado);
            } catch (Exception $e) {
                $estadoSigla = null;
            }
        }

        $cep = null;
        if(!empty($parametros->cep)){
            try {
                $cep = new CEP($parametros->cep);
            } catch (Exception $e) {
                $cep = null;
            }
        }

        $sexo = Sexo::get($parametros->sexo);

        try {
            $nomeDaMae = new NomeCompleto($parametros->nomeMae);
        } catch (Exception $e) {
            $nomeDaMae = null;
        }

        try {
            $documentoDaMae = new DocumentoDeIdentificacao($parametros->cpfMae);
        } catch (Exception $e) {
            $documentoDaMae = null;
        }

        $dataNascimento = new DateTime();
        if(!empty($parametros->dataNascimento)){
            try {
                $dataNascimento = new DateTime(str_replace(['/', '\\'], '-', $parametros->dataNascimento));
            } catch (Exception $e) {
                $dataNascimento = new DateTime();
            }
        }


        try {
            $nomeDoPai = new NomeCompleto($parametros->nomePai);
        }catch (Exception $e){
            $nomeDoPai = null;
        }

        try {
            $documentoDoPai = new DocumentoDeIdentificacao($parametros->cpfPai);
        }catch (Exception $e){
            $documentoDoPai = null;
        }

        return new EntidadeCliente(
            codigo: new IdentificacaoUnica($parametros->codigo),
            nomeCompleto: new NomeCompleto($parametros->nomeCompleto),
            email: new Email($parametros->email),
            telefone: new Telefone($parametros->telefone),
            documento: new DocumentoDeIdentificacao($parametros->documento),
            endereco: new Endereco(
                rua: new TextoSimples($parametros->endereco),
                numero: new TextoSimples($parametros->enderecoNumero),
                bairro: new TextoSimples($parametros->enderecoBairro),
                cidade: new TextoSimples($parametros->enderecoCidade),
                estado: $estadoSigla,
                pais: new Pais('Brazil'),
                cep: $cep,
                complemento: new TextoSimples($parametros->enderecoComplemento),
                referencia: new TextoSimples(''),
                localizacao: null
            ),
            dataNascimento: $dataNascimento,
            sexo: $sexo,
            nomeDaMae: $nomeDaMae,
            documentoDaMae: $documentoDaMae,
            nomeDoPai: $nomeDoPai,
            documentoDoPai: $documentoDoPai,
        );
    }
}