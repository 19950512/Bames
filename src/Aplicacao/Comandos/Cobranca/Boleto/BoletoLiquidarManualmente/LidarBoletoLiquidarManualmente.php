<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BoletoLiquidarManualmente;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\ComandoLancarMovimentacaoNoCaixa;
use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\LidarLancarMovimentacaoNoCaixa;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraBaixarBoleto;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\Cobranca\ItemDaCobranca;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Financeiro\EntidadePlanoDeConta;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use App\Infraestrutura\Adaptadores\PlataformaDeCobranca\ImplementacaoNenhumPlataformaDeCobranca;
use DateTime;
use DI\Container;
use Exception;
use Override;

readonly final class LidarBoletoLiquidarManualmente implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioCobranca $repositorioCobranca,
        private RepositorioClientes $repositorioClientes,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private RepositorioPlanoDeContas $repositorioPlanoDeContas,
        private Discord $discord,
        private RepositorioBoleto $repositorioBoleto,
        private Container $container,
        private Cache $cache
    ){}
    #[Override] public function lidar(Comando $comando): bool
    {

        if (!is_a($comando, ComandoBoletoLiquidarManualmente::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigoPronto();
        $usuarioCodigo = $comando->obterUsuarioCodigoPronto();
        $boletoCodigo = $comando->obterBoletoCodigoPronto();
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
                canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $usuarioDados = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $entidadeUsuario = UsuarioSistema::build($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        try {

            $boletoDados = $this->repositorioBoleto->buscarBoletoPorCodigo(
                codigoBoleto: $boletoCodigo,
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $entidadeBoleto = EntidadeBoleto::instanciarEntidadeBoleto($boletoDados);

        }catch (Exception $erro) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                mensagem: "Boleto não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Boleto não encontrado. - {$erro->getMessage()}");
        }

        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $entidadeBoleto->contaBancariaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );
            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                mensagem: "Ops, não foi possível encontrar a conta bancária. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível encontrar a conta bancária. - {$erro->getMessage()}");
        }

        try {

            $cobrandaDados = $this->repositorioCobranca->buscarCobrancaPorCodigo(
                cobrancaCodigo: $entidadeBoleto->cobrancaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );
            $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($cobrandaDados);
        }catch (Exception $erro) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                mensagem: "Cobrança não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Cobrança não encontrada. - {$erro->getMessage()}");
        }

        if($entidadeBoleto->status == Status::PAGO){
            throw new Exception("Ops, o boleto já está pago, então não é necessário liquidar-lo.");
        }

        if($entidadeBoleto->status == Status::CANCELADO){
            throw new Exception("Ops, o boleto já está cancelado, então não é necessário liquidar-lo.");
        }

        if($entidadeBoleto->status == Status::EMITIDO_AGUARDANDO_REGISTRO){
            throw new Exception("Ops, o boleto ainda não foi aceito pela plataforma, então não é possível liquidar-lo.");
        }

        if($entidadeBoleto->status == Status::REGISTRADO){

            $this->repositorioBoleto->boletofoiLiquidadoManualmente(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                boletoQuemLiquidouManualmente: $entidadeUsuario->nomeCompleto->get(),
                novoStatus: Status::PAGO->value,
                dataPagamento: $dataPagamento->format('Y-m-d'),
                boletoCodigo: $entidadeBoleto->codigo->get(),
                valorRecebido: $valorRecebido->get()
            );

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/boletoDetalhado/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            $keyCache = "{$entidadeEmpresarial->codigo->get()}/cobrancaDetalhada/{$entidadeBoleto->codigo->get()}";
            $this->cache->delete($keyCache);

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                descricao: "{$entidadeUsuario->nomeCompleto->get()} liquidou o boleto (nosso número: {$entidadeBoleto->nossoNumero->get()}) manualmente.",
            );

            foreach($entidadeCobranca->composicaoDaCobranca->obter() as $itemComposicaoCobranca){

                if(!is_a($itemComposicaoCobranca, ItemDaCobranca::class)){
                    continue;
                }

                try {

                    $planoDeContaDado = $this->repositorioPlanoDeContas->buscarPlanoDeContaPorCodigo($itemComposicaoCobranca->planoDeContasCodigo);
                    $planoDeConta = EntidadePlanoDeConta::instanciarEntidadePlanoDeConta($planoDeContaDado);
                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
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
                        canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                        mensagem: "Ops, não foi possível encontrar o pagador. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível encontrar o pagador. - {$erro->getMessage()}");
                }

                try {

                    // Vamos dividir o valor do item da composicao da cobranca pela quantidade de parcelas para lançar no caixa
                    $valorASerLancado = $itemComposicaoCobranca->valor->get() / $entidadeCobranca->parcela->value;

                    $comando = new ComandoLancarMovimentacaoNoCaixa(
                        valor: $valorASerLancado,
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
                        canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                        mensagem: "Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}"
                    );
                    throw new Exception("Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}");
                }

                try {
                    $this->container->get(LidarLancarMovimentacaoNoCaixa::class)->lidar($comando);
                }catch (Exception $erro){
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
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
                    canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                    mensagem: "Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}"
                );
                throw new Exception("Ops, não foi possível lançar a movimentação no caixa. - {$erro->getMessage()}");
            }

            try {
                $this->container->get(LidarLancarMovimentacaoNoCaixa::class)->lidar($comando);

            }catch (Exception $erro){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                    mensagem: "Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}"
                );
                throw new Exception("Ops, não foi possível lidar com a movimentação no caixa. - {$erro->getMessage()}");
            }

            // Vamos mandar uma instrucao de pagamento para a plataforma de cobranca
            try {

                $plataformaDeCobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                    container: $this->container
                );

            }catch(Exception $e){

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                    mensagem: "Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage()
                );
                throw new Exception("Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage());
            }

            if(is_a($plataformaDeCobranca, ImplementacaoNenhumPlataformaDeCobranca::class)){
                return true;
            }

            try {

                $this->repositorioCobranca->novoEvento(
                    cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    descricao: "Estamos enviando uma instrução de baixa do boleto Nosso número: {$entidadeBoleto->nossoNumero->get()} para a plataforma de cobrança."
                );

                $plataformaDeCobranca->baixarBoleto(new EntradaFronteiraBaixarBoleto(
                    codigoBoletoNaPlataformaAPICobranca: $entidadeBoleto->codigoBoletoNaPlataformaAPICobranca->get(),
                    chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                    contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao
                ));

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                    mensagem: "Boleto liquidado com sucesso."
                );

            }catch (Exception $e){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletoLiquidarManualmente,
                    mensagem: "Ops, não foi possível baixar o boleto na plataforma de cobrança. ".$e->getMessage()
                );
                throw new Exception("Ops, não foi possível baixar o boleto na plataforma de cobrança. ".$e->getMessage());
            }

            return true;
        }

        throw new Exception("Ops, não foi possível liquidar o boleto porque o status atual do boleto {$entidadeBoleto->status->value} não é permitido para liquidar. Somenteo status REGISTRADO é permitido para liquidar.");
    }
}
