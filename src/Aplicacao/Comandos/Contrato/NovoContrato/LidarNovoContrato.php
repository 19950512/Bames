<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Contrato\NovoContrato;

use DateTime;
use Override;
use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Horario;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Contrato\EntidadeContrato;
use App\Dominio\Entidades\Contrato\Enumerados\Status;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Contrato\RepositorioContrato;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Comandos\Contrato\NovoContrato\ComandoNovoContrato;
use App\Dominio\Repositorios\Contrato\Fronteiras\SaidaFronteiraContrato;
use App\Dominio\Repositorios\Contrato\Fronteiras\EntradaFronteiraCriarContrato;

final class LidarNovoContrato implements Lidar
{

    public function __construct(
        private RepositorioClientes $repositorioClientes,
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioContrato $repositorioContrato,
        private Discord $discord,
        private Container $container,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoNovoContrato::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $codigoCliente = $comando->obterClienteCodigo();
        $empresaCodigo = $comando->obterEmpresaCodigo();
        $usuarioCodigo = $comando->obterUsuarioCodigo();
        $horarioEmissao = $comando->obterHorarioEmissaoCobranca();

        $horarioEmissao = Horario::criar($horarioEmissao);

        try {

            $clienteData = $this->repositorioClientes->buscarClientePorCodigo(
                codigoCliente: $codigoCliente,
                empresaCodigo: $empresaCodigo
            );

            $entidadeCliente = EntidadeCliente::instanciarEntidadeCliente($clienteData);
        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Contratos,
                mensagem: "Cliente não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Cliente não encontrado.");
        }

        try {

            $usuarioDados = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $entidadeUsuario = UsuarioSistema::build($usuarioDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Contratos,
                mensagem: "Usuário não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Contratos,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $contratoDados = new SaidaFronteiraContrato(
                codigo: (new IdentificacaoUnica())->get(),
                contaBancariaCodigo: $comando->obterContaBancariaCodigo(),
                clienteCodigo: $entidadeCliente->codigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                status: Status::RASCUNHO->value,
                recorrente: $comando->obterRecorrente(),
                dataInicio: $comando->obterDataInicio(),
                dataCriacao: (new DateTime())->format('Y-m-d H:i:s'),
                meioPagamento: $comando->obterMeioPagamento(),
                diaVencimento: $comando->obterDiaVencimento(),
                horarioEmissaoCobrancaHora: $horarioEmissao->getHora(),
                horarioEmissaoCobrancaMinuto: $horarioEmissao->getMinuto(),
                diaEmissaoCobranca: $comando->obterDiaEmissaoCobranca(),
                parcela: $comando->obterParcela(),
                valor: $comando->obterValor(),
                juros: $comando->obterJuros(),
                multa: $comando->obterMulta(),
                descontoAntecipacao: $comando->obterDescontoAntecipacao(),
                tipoJuro: $comando->obterTipoJuros(),
                tipoMulta: $comando->obterTipoMulta(),
                tipoDescontoAntecipacao: $comando->obterTipoDescontoAntecipacao(),
            );
    
            $entidadeContrato = EntidadeContrato::instanciarEntidadeContrato($contratoDados, $this->container);
        
        }catch(Exception $erro){
            throw new Exception("Erro ao validar o contrato para entidade. {$erro->getMessage()}");
        }

        try {

            $parametrosCriarContrato = new EntradaFronteiraCriarContrato(
                codigo: $entidadeContrato->codigo->get(),
                contaBancariaCodigo: $entidadeContrato->contaBancaria->codigo->get(),
                clienteCodigo: $entidadeContrato->cliente->codigo->get(),
                empresaCodigo: $entidadeContrato->empresaCodigo->get(),
                status: $entidadeContrato->status->value,
                recorrente: $entidadeContrato->recorrente,
                dataInicio: $entidadeContrato->dataInicio->format('Y-m-d'),
                dataCriacao: $entidadeContrato->dataCriacao->format('Y-m-d H:i:s'),
                meioPagamento: $entidadeContrato->meioPagamento->value,
                diaVencimento: $entidadeContrato->diaVencimento->get(),
                horarioEmissaoCobrancaHora: $entidadeContrato->horarioEmissaoCobranca->getHora(),
                horarioEmissaoCobrancaMinuto: $entidadeContrato->horarioEmissaoCobranca->getMinuto(),
                diaEmissaoCobranca: $entidadeContrato->diaEmissaoCobranca->get(),
                parcela: $entidadeContrato->parcela->value,
                valor: $entidadeContrato->valor->get(),
                juros: $entidadeContrato->juros->get(),
                multa: $entidadeContrato->multa->get(),
                descontoAntecipacao: $entidadeContrato->descontoAntecipacao->get(),
                tipoJuro: $entidadeContrato->tipoJuro->value,
                tipoMulta: $entidadeContrato->tipoMulta->value,
                tipoDescontoAntecipacao: $entidadeContrato->tipoDescontoAntecipacao->value,
            );
            $this->repositorioContrato->criarContrato($parametrosCriarContrato);

            $this->repositorioContrato->salvarEvento(
                contratoCodigo: $entidadeContrato->codigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Contrato criado por {$entidadeUsuario->nomeCompleto->get()}",
            );

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Contratos,
                mensagem: "Erro ao criar contrato no repositório. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao criar contrato. - {$erro->getMessage()}");
        }
        return null;
    }
}