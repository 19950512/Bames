<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\ConsultarBoleto;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Cobranca\Enumerados\CobrancaSituacao;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConsultarBoleto;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use DI\Container;
use Exception;
use Override;

readonly final class LidarConsultarboleto implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private Discord $discord,
        private Container $container,
        private RepositorioBoleto $repositorioBoleto,
        private Cache $cache
    ){}

    #[Override] public function lidar(Comando $comando): CobrancaSituacao
    {
        if (!is_a($comando, ComandoConsultarBoleto::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->getEmpresaCodigo();
        $boletoCodigo = $comando->getBoletoCodigo();

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $boletoDados = $this->repositorioBoleto->buscarBoletoPorCodigo(
                codigoBoleto: $boletoCodigo,
                empresaCodigo: $empresaCodigo
            );

            $entidadeBoleto = EntidadeBoleto::instanciarEntidadeBoleto($boletoDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "Boleto não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Problemas com o suposto boleto. - {$erro->getMessage()}");
        }

        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $entidadeBoleto->contaBancariaCodigo->get(),
                empresaCodigo: $empresaCodigo
            );

            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "Ops, não foi possível obter a conta bancária do boleto. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível obter a conta bancária do boleto. {$erro->getMessage()}");
        }

        try {

            $plataformaDeCobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                container: $this->container
            );

        }catch(Exception $e){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage()
            );
            throw new Exception("Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage());
        }

        try {

            $parametrosConsultarBoleto = new EntradaFronteiraConsultarBoleto(
                codigoBoletoNaPlataformaAPICobranca: $entidadeBoleto->codigoBoletoNaPlataformaAPICobranca->get(),
                chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao
            );
            $respostaPlataforma = $plataformaDeCobranca->consultarBoleto($parametrosConsultarBoleto);

            if($respostaPlataforma->status == CobrancaSituacao::DESCONHECIDO){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoConsultar,
                    mensagem: "Ops, não foi possível consultar o boleto. - ".json_encode($respostaPlataforma->toArray()),
                );
                throw new Exception("Ops, não foi possível consultar o boleto. - ".json_encode($respostaPlataforma->toArray()));
            }

            if($respostaPlataforma->status == CobrancaSituacao::PAGO and $entidadeBoleto->status != Status::PAGO){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoConsultar,
                    mensagem: "O boleto foi pago na plataforma mas não foi atualizado no sistema, vamos tentar atualizar. ".PHP_EOL.json_encode($respostaPlataforma->toArray()),
                );

                try {

                    $this->liquidarBoletoNoSitema($entidadeBoleto);

                    $keyCache = "{$entidadeEmpresarial->codigo->get()}/boletoDetalhado/{$entidadeBoleto->codigo->get()}";
                    $this->cache->delete($keyCache);

                    return $respostaPlataforma->status;

                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::BoletoConsultar,
                        mensagem: "Ops, não foi possível atualizar o boleto para pago. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível atualizar o boleto para pago. - {$erro->getMessage()}");
                }
            }

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "O boleto na plataforma está com o status: {$respostaPlataforma->status->value} e no sistema está com o status: {$entidadeBoleto->status->value}. Então não foi necessário atualizar.",
            );

            return $respostaPlataforma->status;

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoConsultar,
                mensagem: "Ops, não foi possível consultar o boleto. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível consultar o boleto. - {$erro->getMessage()}");
        }
    }

    private function liquidarBoletoNoSitema(EntidadeBoleto $entidadeBoleto): void
    {
    }
}
