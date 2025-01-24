<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Empresa;

use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\Valor;
use Exception;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\Endereco\CEP;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Endereco\Pais;
use App\Dominio\ObjetoValor\Endereco\Estado;
use App\Dominio\ObjetoValor\Endereco\Endereco;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Longitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Localizacao;
use App\Dominio\Entidades\Empresa\Colaboradores\Colaboradores;
use App\Dominio\Entidades\Empresa\Colaboradores\EntidadeColaborador;
use App\Dominio\Entidades\Empresa\Colaboradores\EntidadeResponsavel;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraEmpresa as SaidaFronteiraEmpresaEmpresa;

class EntidadeEmpresarial
{

	public Colaboradores $colaboradores;
    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        public Apelido $apelido,
        public DocumentoIdentificacao $numeroDocumento,
        public Endereco $endereco,
        readonly public EntidadeResponsavel $responsavel,
        public Valor $saldoCreditos = new Valor(0),
        readonly public bool $acessoNaoAutorizado = false,
        readonly public string $acessoNaoAutorizadoMotivo = '',
        readonly public bool $acessoTotalAutorizadoPorMatheusMaydana = false,
    ){}

    public function substituicoes(): array
    {
        return [
            '{{empresa_nome_fantasia}}' => $this->apelido->get(),
            '{{empresa_documento}}' => $this->numeroDocumento->get(),
            '{{empresa_endereco_cidade}}' => $this->endereco->cidade->get(),
            '{{empresa_endereco_estado}}' => $this->endereco->estado->get(),
            '{{empresa_endereco_completo}}' => $this->endereco->enderecoCompleto(),
            '{{empresa_responsavel_nome}}' => $this->responsavel->nomeCompleto->get(),
            '{{empresa_responsavel_email}}' => $this->responsavel->email->get(),
            '{{empresa_responsavel_oab_completa}}' => $this->responsavel->oab->get(),
            '{{empresa_responsavel_oab_numero}}' => $this->responsavel->oab->getNumero(),
            '{{empresa_responsavel_oab_estado}}' => $this->responsavel->oab->getUF(),
        ];
    }

    public function substituicoesCaixaAlta(): array
    {
        $substituicoes = $this->substituicoes();

        foreach($substituicoes as $chave => $valor){
            $substituicoes[mb_strtoupper($chave)] = mb_strtoupper($valor);
        }

        return $substituicoes;
    }

    public static function instanciarEntidadeEmpresarial(SaidaFronteiraEmpresa | SaidaFronteiraEmpresaEmpresa $params): EntidadeEmpresarial
    {

		try {
			$apelido = new Apelido($params->nome);
		}catch (Exception $erro){
			throw new Exception("O Apelido da Entidade Empresarial '{$params->nome}' ID: $params->empresaCodigo não está válido. {$erro->getMessage()}");
		}

        $entidadeEmpresarial = new EntidadeEmpresarial(
            codigo: new IdentificacaoUnica($params->empresaCodigo),
            apelido: $apelido,
            numeroDocumento: new DocumentoDeIdentificacao($params->numeroDocumento),
            endereco: new Endereco(
                rua: new TextoSimples(''),
                numero: new TextoSimples(''),
                bairro: new TextoSimples(''),
                cidade: new TextoSimples('Marau'),
                estado: new Estado('RS'),
                pais: new Pais('BR'),
                cep: new CEP('99150-000'),
                complemento: new TextoSimples(''),
                referencia: new TextoSimples(''),
                localizacao: new Localizacao(
                    latitude: new Latitude(0),
                    longitude: new Longitude(0),
                ),
            ),
            responsavel: new EntidadeResponsavel(
                codigo: new IdentificacaoUnica($params->responsavelCodigo),
                nomeCompleto: new NomeCompleto($params->responsavelNomeCompleto),
                email: new Email($params->responsavelEmail),
                oab: new OAB($params->responsavelOAB),
            ),
            saldoCreditos: new Valor($params->creditoSaldos),
            acessoNaoAutorizado: $params->acessoNaoAutorizado,
            acessoNaoAutorizadoMotivo: $params->acessoNaoAutorizadoMotivo,
            acessoTotalAutorizadoPorMatheusMaydana: $params->acessoTotalAutorizadoPorMatheusMaydana,
        );
		$entidadeEmpresarial->colaboradores = new Colaboradores();

        foreach($params->colaboradores as $colaborador){

            if(!isset($colaborador['codigo'], $colaborador['nome_completo'], $colaborador['email'])){
                continue;
            }

            $entidadeEmpresarial->colaboradores->adicionarColaborador(
                new EntidadeColaborador(
                    codigo: new IdentificacaoUnica($colaborador['codigo']),
                    nomeCompleto: new NomeCompleto($colaborador['nome_completo']),
                    email: new Email($colaborador['email']),
                )
            );
        }

		return $entidadeEmpresarial;
    }

    public function informacoesPublicas(): array
    {
        return [
            'codigo' => $this->codigo->get(),
            'apelido' => $this->apelido->get(),
            'documentoTipo' => is_a($this->numeroDocumento, CNPJ::class) ? 'CNPJ' : 'CPF',
            'documentoNumero' => $this->numeroDocumento->get(),
            'endereco' => $this->endereco->get(),
            'endereco_completo' => $this->endereco->enderecoCompleto(),
            'responsavel' => [
                'codigo' => $this->responsavel->codigo->get(),
                'nomeCompleto' => $this->responsavel->nomeCompleto->get(),
                'email' => $this->responsavel->email->get(),
                'oab' => $this->responsavel->oab->get(),
            ],
            'colaboradores' => $this->colaboradores->obterColaboradores(),
        ];
    }

    public function toArray(): array
    {

        $tipoDocumento = 'CPF';
        if(is_a($this->numeroDocumento, DocumentoDeIdentificacao::class)){
            if(CNPJ::valido($this->numeroDocumento->get())){
                $tipoDocumento = 'CNPJ';
            }
        }
        if(is_a($this->numeroDocumento, CNPJ::class)){
            $tipoDocumento = 'CNPJ';
        }

        return [
            'codigo' => $this->codigo->get(),
            'apelido' => $this->apelido->get(),
            'documentoTipo' => $tipoDocumento,
            'documentoNumero' => $this->numeroDocumento->get(),
            'endereco_completo' => $this->endereco->enderecoCompleto(),
            'endereco' => $this->endereco->get(),
            'responsavel' => [
                'codigo' => $this->responsavel->codigo->get(),
                'nomeCompleto' => $this->responsavel->nomeCompleto->get(),
                'email' => $this->responsavel->email->get(),
            ],
            'colaboradores' => $this->colaboradores->obterColaboradores(),
            'saldoCreditos' => $this->saldoCreditos->get(),
            'acessoTotalAutorizadoPorMatheusMaydana' => $this->acessoTotalAutorizadoPorMatheusMaydana ? 'Sim' : 'Não',
        ];
    }
}