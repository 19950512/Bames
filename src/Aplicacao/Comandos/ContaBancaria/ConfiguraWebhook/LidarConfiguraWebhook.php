<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\ContaBancaria\ConfiguraWebhook;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConfigurarWebhook;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use DI\Container;
use Exception;

final class LidarConfiguraWebhook implements Lidar
{

    public function __construct(
        private Container $container,
        private RepositorioEmpresa $repositorioEmpresa,
        private Ambiente $ambiente,
        private RepositorioContaBancaria $repositorioContaBancaria
    ){}
    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoConfiguraWebhook::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->getEmpresaCodigo();
        $codigoContaBancaria = $comando->getCodigoContaBancaria();
        $webhookCodigo = new IdentificacaoUnica();

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $contaBancaria = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $codigoContaBancaria,
                empresaCodigo: $empresaCodigo
            );
            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancaria);

        } catch (Exception $erro) {
            throw new Exception("Conta bancária não encontrada. - {$erro->getMessage()}");
        }

        $webhookURL = "{$this->ambiente->get('BASEURL_WEBHOOK')}/asaas?empresaCodigo={$entidadeEmpresarial->codigo->get()}&contaBancariaCodigo={$entidadeContaBancaria->codigo->get()}";

        try {
            $plataformaAPICobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                container: $this->container
            );
        }catch (Exception $erro){
            throw new Exception("Plataforma de cobrança não encontrada. - {$erro->getMessage()}");
        }

        if($this->repositorioContaBancaria->existeWebhookConfiguradoParaConta(
            contaBancariaCodigo: $codigoContaBancaria,
            empresaCodigo: $empresaCodigo
        )){
            return null;
        }

        try {

            $parametrosConexaoComPlataforma = new EntradaFronteiraConfigurarWebhook(
                chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                webhookURL: $webhookURL,
                webhookCodigo: $webhookCodigo->get(),
                contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao
            );

            $plataformaAPICobranca->configurarWebhook($parametrosConexaoComPlataforma);

            $this->repositorioContaBancaria->atualizarOWebhookCodigoDaContaBancaria(
                contaBancariaCodigo: $codigoContaBancaria,
                webhookCodigo: $webhookCodigo->get(),
                empresaCodigo: $empresaCodigo
            );

            $this->repositorioContaBancaria->novoEvento(
                contaBancariaCodigo: $codigoContaBancaria,
                empresaCodigo: $empresaCodigo,
                eventoDescricao: "Webhook configurado com sucesso e com segurança."
            );

        }catch (Exception $erro){
            throw new Exception("Erro ao configurar webhook da conta bancaria na Plataforma API Cobranca. - {$erro->getMessage()}");
        }

        return null;
    }
}
