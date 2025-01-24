<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiPagoNaPlataforma;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\ComandoLancarMovimentacaoNoCaixa;
use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\LidarLancarMovimentacaoNoCaixa;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\Cobranca\ItemDaCobranca;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Financeiro\EntidadePlanoDeConta;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use DateTime;
use DI\Container;
use Exception;
use Override;

readonly final class LidarBoletoFoiPagoNaPlataforma implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioCobranca $repositorioCobranca,
        private RepositorioClientes $repositorioClientes,
        private RepositorioPlanoDeContas $repositorioPlanoDeContas,
        private Discord $discord,
        private RepositorioBoleto $repositorioBoleto,
        private Container $container,
        private Cache $cache
    ){}
    #[Override] public function lidar(Comando $comando): bool
    {

        if (!is_a($comando, ComandoBoletoFoiPagoNaPlataforma::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigoPronto();
        $boletoCodigoNaPlataforma = $comando->obterBoletoCodigoNaPlataformaPronto();
        $dataPagamento = $comando->obterDataPagamentoPronto();
        $valorRecebido = $comando->obterValorRecebidoPronto();

        try {
            $valorRecebido = new Valor($valorRecebido);
        }catch (Exception $e){
            throw new Exception("Ops, o valor recebido não é válido. - $valorRecebido");
        }

        try {
            $dataPagamento = new DateTime($dataPagamento);
        }catch (Exception $e){
            throw new Exception("Ops, a data de pagamento não é válida. - $dataPagamento");
        }

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

        $this->repositorioCobranca->novoEvento(
            cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            descricao: "O boleto (nosso número: {$entidadeBoleto->nossoNumero->get()}) foi marcado como pago na plataforma.",
        );

        if($entidadeBoleto->status != Status::PAGO){

            $this->repositorioBoleto->boletoFoiPagoNaPlataforma(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                novoStatus: Status::PAGO->value,
                dataPagamento: $dataPagamento->format('Y-m-d'),
                boletoCodigo: $entidadeBoleto->codigo->get(),
                valorRecebido: $valorRecebido->get()
            );

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                descricao: "O boleto (nosso número: {$entidadeBoleto->nossoNumero->get()}) foi marcado como pago no sistema.",
            );

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/boletoDetalhado/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/cobrancaDetalhada/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            foreach($entidadeCobranca->composicaoDaCobranca->obter() as $itemComposicaoCobranca){

                if(!is_a($itemComposicaoCobranca, ItemDaCobranca::class)){
                    continue;
                }

                try {

                    $planoDeContaDado = $this->repositorioPlanoDeContas->buscarPlanoDeContaPorCodigo($itemComposicaoCobranca->planoDeContasCodigo);
                    $planoDeConta = EntidadePlanoDeConta::instanciarEntidadePlanoDeConta($planoDeContaDado);
                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::Webhook,
                        mensagem: "Ops, não foi possível encontrar o plano de contas. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível encontrar o plano de contas. - {$erro->getMessage()}");
                }

                try {

                    $pagador = $this->repositorioClientes->buscarClientePorCodigo(
                        codigoCliente: $entidadeCobranca->clienteCodigo->get(),
                        empresaCodigo: $entidadeEmpresarial->codigo->get()
                    );
                    $entidadeCliente = EntidadeCliente::instanciarEntidadeCliente($pagador);

                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::Webhook,
                        mensagem: "Ops, não foi possível encontrar o pagador. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível encontrar o pagador. - {$erro->getMessage()}");
                }

                try {

                    $comando = new ComandoLancarMovimentacaoNoCaixa(
                        valor: $itemComposicaoCobranca->valor->get() / $entidadeCobranca->parcela->value,
                        descricao: "Recebimento do boleto - {$planoDeConta->nome->get()} - {$itemComposicaoCobranca->descricao->get()}",
                        planoDeContaCodigo: $planoDeConta->codigo,
                        dataMovimentacao: $dataPagamento->format('Y-m-d H:i:s'),
                        contaBancariaCodigo: $entidadeBoleto->contaBancariaCodigo->get(),
                        empresaCodigo: $entidadeEmpresarial->codigo->get(),
                        usuarioCodigo: $entidadeEmpresarial->responsavel->codigo->get(),
                        pagadorCodigo: $entidadeCobranca->clienteCodigo->get(),
                        cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                        boletoCodigo: $entidadeBoleto->codigo->get()
                    );

                    $comando->executar();

                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::Webhook,
                        mensagem: "Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}");
                }

                try {
                    $this->container->get(LidarLancarMovimentacaoNoCaixa::class)->lidar($comando);
                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::Webhook,
                        mensagem: "Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}");
                }
            }

            // AGORA VAMOS LANÇAR A TARIFA DO BOLETO
             try {

                $comando = new ComandoLancarMovimentacaoNoCaixa(
                    valor: -2.50,
                    descricao: "Tarifa de liquidação do boleto",
                    planoDeContaCodigo: 2,
                    dataMovimentacao: $dataPagamento->format('Y-m-d H:i:s'),
                    contaBancariaCodigo: $entidadeBoleto->contaBancariaCodigo->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    usuarioCodigo: $entidadeEmpresarial->responsavel->codigo->get()
                );

                $comando->executar();

            }catch (Exception $erro){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Webhook,
                    mensagem: "Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}"
                );
                throw new Exception("Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}");
            }

            try {
                $this->container->get(LidarLancarMovimentacaoNoCaixa::class)->lidar($comando);

            }catch (Exception $erro){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Webhook,
                    mensagem: "Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}"
                );
                throw new Exception("Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}");
            }

            return true;
        }

        $this->discord->enviar(
            canaldeTexto: CanalDeTexto::Webhook,
            mensagem: "O boleto não foi marcado como pago porque já está como pago no sistema."
        );

        return false;
    }
}
