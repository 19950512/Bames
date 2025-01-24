<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use Exception;
use Override;

readonly final class LidarBoletoFoiAceitoNaPlataforma implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioCobranca $repositorioCobranca,
        private Discord $discord,
        private RepositorioBoleto $repositorioBoleto,
        private Cache $cache,
    ){}
    #[Override] public function lidar(Comando $comando): bool
    {

        if (!is_a($comando, ComandoBoletoFoiAceitoNaPlataforma::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigoPronto();
        $boletoCodigoNaPlataforma = $comando->obterBoletoCodigoNaPlataformaPronto();

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Webhook,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $boletoDados = $this->repositorioBoleto->buscarBoletoPorCodigoNaPlataforma(
                codigoBoletoNaPlataformaAPI: $boletoCodigoNaPlataforma,
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $entidadeBoleto = EntidadeBoleto::instanciarEntidadeBoleto($boletoDados);

        }catch (Exception $erro) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Webhook,
                mensagem: "Boleto não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Boleto não encontrado. - {$erro->getMessage()}");
        }

        try {

            $cobrandaDados = $this->repositorioCobranca->buscarCobrancaPorCodigo(
                cobrancaCodigo: $entidadeBoleto->cobrancaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );
            $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($cobrandaDados);
        }catch (Exception $erro) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Webhook,
                mensagem: "Cobrança não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Cobrança não encontrada. - {$erro->getMessage()}");
        }

        if($entidadeBoleto->status == Status::EMITIDO_AGUARDANDO_REGISTRO){

            $this->repositorioBoleto->boletoFoiAceitoPelaPlataforma(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                novoStatus: Status::REGISTRADO->value,
                boletoCodigo: $entidadeBoleto->codigo->get()
            );

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                descricao: "O boleto (seu número: {$entidadeBoleto->seuNumero->get()}) foi aceito pela plataforma.",
            );

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/boletoDetalhado/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/cobrancaDetalhada/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);
            return true;
        }

        $this->discord->enviar(
            canaldeTexto: CanalDeTexto::Webhook,
            mensagem: "O boleto não está mais aguardando registro, então não foi marcado como aceito na plataforma."
        );

        return false;
    }
}
