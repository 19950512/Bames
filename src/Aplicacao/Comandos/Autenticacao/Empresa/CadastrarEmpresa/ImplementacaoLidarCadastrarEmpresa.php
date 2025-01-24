<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa;

use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento as EventoMensageria;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Dominio\ObjetoValor\OAB;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaConta;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

readonly class ImplementacaoLidarCadastrarEmpresa implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private Mensageria $mensageria,
        private Discord $discord,
	){}

    #[Override] public function lidar(Comando $comando): IdentificacaoUnica
	{
		if(!is_a($comando, ComandoCadastrarEmpresa::class)){
		    throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaNomeFantasia = $comando->obterNomeFantasia();
        $empresaNumeroDocumento = $comando->obterNumeroDocumento();
        $responsavelNomeCompleto = $comando->obterResponsavelNomeCompleto();
        $responsavelEmail = $comando->obterResponsavelEmail();
        $responsavelSenha = $comando->obterResponsavelSenha();
        $oab = $comando->obterOAB();

        $empresaCodigo = new IdentificacaoUnica();
        $responsavelCodigo = new IdentificacaoUnica();

        try {
            $oab = new OAB($oab);
        }catch (Exception $erro){
            throw new Exception("{$erro->getMessage()}");
        }

        if($this->repositorioAutenticacaoComando->jaExisteEmpresaComEsseDocumento($empresaNumeroDocumento)){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Já existe uma empresa com número do documento informado. ($empresaNumeroDocumento)"
            );
            throw new Exception("Já existe uma empresa com número do documento informado. ($empresaNumeroDocumento)");
        }

        if($this->repositorioAutenticacaoComando->jaExisteContaComEsseEmail($responsavelEmail)){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Já existe uma conta com o email informado. ($responsavelEmail)"
            );
            throw new Exception("Já existe uma conta com o email informado. ($responsavelEmail)");
        }

        if($this->repositorioAutenticacaoComando->jaExisteUmaContaComEstaOAB($oab->get())){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Já existe uma conta com a OAB informada. ({$oab->get()})"
            );
            throw new Exception("Você não pode registrar uma conta com a OAB de outra pessoa. ({$oab->get()})");
        }

        $parametrosUsuarioSistema = new SaidaFronteiraBuscarContaPorCodigo(
            empresaCodigo: $empresaCodigo->get(),
            contaCodigo: $responsavelCodigo->get(),
            nomeCompleto: $responsavelNomeCompleto,
            email: $responsavelEmail,
            documento: $empresaNumeroDocumento,
            hashSenha: '',
            oab: $oab->get(),
            diretorGeral: true,
        );
        $usuarioSistema = UsuarioSistema::build($parametrosUsuarioSistema);

        $usuarioSistema->gerarNovaHashDaSenha($responsavelSenha);

        $saidaFronteiraEmpresa = new SaidaFronteiraEmpresa(
            empresaCodigo: $empresaCodigo->get(),
            nome: $empresaNomeFantasia,
            numeroDocumento: $empresaNumeroDocumento,
            
            responsavelCodigo: $usuarioSistema->codigo->get(),
            responsavelOAB: $usuarioSistema->oab->get(),
            responsavelNomeCompleto: $usuarioSistema->nomeCompleto->get(),
            responsavelEmail: $usuarioSistema->email->get(),

            acessoNaoAutorizado: false,
            acessoNaoAutorizadoMotivo: '',
        );
        $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($saidaFronteiraEmpresa);

        try {

            $parametrosNovaEmpresa = new EntradaFronteiraNovaEmpresa(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                apelido: $entidadeEmpresarial->apelido->get(),
                numeroDocumento: $entidadeEmpresarial->numeroDocumento->get(),
                responsavelEmail: $usuarioSistema->email->get(),
            );
            $this->repositorioAutenticacaoComando->cadastrarNovaEmpresa($parametrosNovaEmpresa);

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Nova empresa cadastrada com sucesso. ({$entidadeEmpresarial->apelido->get()} - ID: {$entidadeEmpresarial->codigo->get()})"
            );

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Ops, não foi possível cadastrar a empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível cadastrar a empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}");
        }

        $tokenValidacaoEmail = (new IdentificacaoUnica())->get();

        try {

            $this->repositorioAutenticacaoComando->novaConta(new EntradaFronteiraNovaConta(
                empresaCodigo: $empresaCodigo->get(),
                contaCodigo: $usuarioSistema->codigo->get(),
                nomeCompleto: $usuarioSistema->nomeCompleto->get(),
                email: $usuarioSistema->email->get(),
                senha: $usuarioSistema->hashSenha,
                documento: $usuarioSistema->documento->get(),
                tokenValidacaoEmail: $tokenValidacaoEmail,
                oab: $usuarioSistema->oab->get(),
                diretorGeral: $usuarioSistema->diretorGeral,
            ));

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Novo usuário cadastrado com sucesso e vinculado a empresa. ({$usuarioSistema->nomeCompleto->get()} - ID: {$responsavelCodigo->get()} - Empresa: {$entidadeEmpresarial->apelido->get()} ID: {$entidadeEmpresarial->codigo->get()})"
            );

        }catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Ops, não foi possível cadastrar o responsável {$usuarioSistema->nomeCompleto->get()}. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível cadastrar o responsável {$usuarioSistema->nomeCompleto->get()}. {$erro->getMessage()}");
        }

        $this->discord->enviar(
            canaldeTexto: CanalDeTexto::NovosClientes,
            mensagem: "Empresa {$entidadeEmpresarial->apelido->get()} cadastrada com sucesso. (ID: {$entidadeEmpresarial->codigo->get()})"
        );

        $this->mensageria->publicar(
            evento: EventoMensageria::EmpresaRecemCadastradaNoSistema,
            message: json_encode([
                'empresaCodigo' => $entidadeEmpresarial->codigo->get()
            ])
        );

        return $empresaCodigo;
	}
}