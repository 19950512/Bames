<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\ContaBancaria\VerificaConexaoComPlataformaAPIDeCobrancas;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\ContaBancaria\ConfiguraWebhook\ComandoConfiguraWebhook;
use App\Aplicacao\Comandos\ContaBancaria\ConfiguraWebhook\LidarConfiguraWebhook;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraVerificarConexaoComPlataforma;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use DI\Container;
use Exception;

final class LidarVerificaConexaoComPlataformaAPIDeCobrancas implements Lidar
{

    public function __construct(
        private Container $container,
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private Discord $discord
    ){}
    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoConfiguraWebhook::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->getEmpresaCodigo();
        $codigoContaBancaria = $comando->getCodigoContaBancaria();

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

        try {
            $plataformaAPICobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                container: $this->container
            );
        }catch (Exception $erro){
            throw new Exception("Plataforma de cobrança não encontrada. - {$erro->getMessage()}");
        }

        try {

            $parametrosConexaoComPlataforma = new EntradaFronteiraVerificarConexaoComPlataforma(
                chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao
            );

            if(!$plataformaAPICobranca->conexaoEstabelecidaComSucessoComAPlataformaAPICobranca($parametrosConexaoComPlataforma)){

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::ContaBancariaVerificaIntegracao,
                    mensagem: "Erro ao verificar conexão com a plataforma de cobrança."
                );
                throw new Exception("Erro ao verificar conexão com a plataforma de cobrança.");
            }

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ContaBancariaVerificaIntegracao,
                mensagem: "Erro ao verificar conexão com a plataforma de cobrança. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao verificar conexão com a plataforma de cobrança. - {$erro->getMessage()}");
        }

        try {

            $comando = new ComandoConfiguraWebhook(
                empresaCodigo: $empresaCodigo,
                codigoContaBancaria: $codigoContaBancaria
            );
            $comando->executar();

            $this->container->get(LidarConfiguraWebhook::class)->lidar($comando);

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ContaBancariaVerificaIntegracao,
                mensagem: "Webhook configurado com sucesso."
            );

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ContaBancariaVerificaIntegracao,
                mensagem: "Erro ao configurar webhook. - {$erro->getMessage()}"
            );
        }

        return null;
    }
}
