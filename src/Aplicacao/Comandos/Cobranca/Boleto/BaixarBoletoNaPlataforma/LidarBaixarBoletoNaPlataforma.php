<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraBaixarBoleto;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use DI\Container;
use Exception;
use Override;

readonly final class LidarBaixarBoletoNaPlataforma implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private Discord $discord,
        private RepositorioCobranca $repositorioCobranca,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private Container $container,
        private RepositorioBoleto $repositorioBoleto,
        private Cache $cache,
    ){}

    #[Override] public function lidar(Comando $comando): bool
    {
        if (!is_a($comando, ComandoBaixarBoletoNaPlataforma::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->getEmpresaCodigo();
        $boletoCodigo = $comando->getBoletoCodigo();

        $usuarioCodigo = $comando->obterUsuarioCodigoPronto();

        try {

            $usuarioDados = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $entidadeUsuario = UsuarioSistema::build($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
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
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "Boleto não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Problemas com o suposto boleto. - {$erro->getMessage()}");
        }

        if($entidadeBoleto->status == Status::PAGO){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "O boleto já está pago, então não é necessário baixar-lo."
            );

            throw new Exception("O boleto já está pago, então não é necessário baixar-lo.");
            return true;
        }

        if($entidadeBoleto->status == Status::CANCELADO){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "O boleto já está cancelado, então não é necessário baixar-lo."
            );

            throw new Exception("O boleto já está cancelado, então não é necessário baixar-lo.");
        }

        if(!$entidadeBoleto->foiAceitoPelaPlataforma){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "O boleto ainda não foi aceito pela plataforma, então não é possível baixar-lo."
            );

            throw new Exception("O boleto ainda não foi aceito pela plataforma, então não é possível baixar-lo.");
            return true;
        }

        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $entidadeBoleto->contaBancariaCodigo->get(),
                empresaCodigo: $empresaCodigo
            );

            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
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
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage()
            );
            throw new Exception("Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage());
        }

        try {

            $parametrosConsultarBoleto = new EntradaFronteiraBaixarBoleto(
                codigoBoletoNaPlataformaAPICobranca: $entidadeBoleto->codigoBoletoNaPlataformaAPICobranca->get(),
                chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao
            );
            $plataformaDeCobranca->baixarBoleto($parametrosConsultarBoleto);

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "Boleto baixado com sucesso."
            );

            $this->repositorioBoleto->boletoFoiCancelado(
                empresaCodigo: $empresaCodigo,
                boletoCodigo: $boletoCodigo,
                boletoStatus: Status::CANCELADO->value
            );

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $entidadeBoleto->cobrancaCodigo->get(),
                empresaCodigo: $empresaCodigo,
                descricao: "{$entidadeUsuario->nomeCompleto->get()} baixou o boleto nosso número: {$entidadeBoleto->nossoNumero->get()} de R$ {$entidadeBoleto->valor->get()}."
            );

            $keyCache = "$empresaCodigo/boletoDetalhado/$boletoCodigo";
            $this->cache->delete($keyCache);

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/cobrancaDetalhada/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            return true;

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoBaixar,
                mensagem: "Ops, não foi possível consultar o boleto. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível consultar o boleto. - {$erro->getMessage()}");
        }
    }
}
