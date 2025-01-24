<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Boleto\EntidadeBoleto;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Financeiro\EntidadePlanoDeConta;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Financeiro\Caixa\Fronteiras\FronteiraEntradaLancarMovimentacaoNoCaixa;
use App\Dominio\Repositorios\Financeiro\Caixa\RepositorioCaixa;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use DateTime;
use Exception;

readonly final class LidarLancarMovimentacaoNoCaixa implements Lidar
{

    public function __construct(
        private RepositorioCaixa $repositorioCaixa,
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private RepositorioCobranca $repositorioCobranca,
        private RepositorioClientes $repositorioClientes,
        private RepositorioBoleto $repositorioBoleto,
        private RepositorioPlanoDeContas $repositorioPlanoDeContas,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoLancarMovimentacaoNoCaixa::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigoPronto());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        $usuarioCodigo = $comando->obterUsuarioCodigoPronto();

        try {

            $usuarioDados = $this->repositorioEmpresa->buscarUsuarioPorCodigo($usuarioCodigo);
            $entidadeUsuario = UsuarioSistema::build($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        $movimentacaoCodigo = new IdentificacaoUnica();
        $contaBancariaCodigo = $comando->obterContaBancariaCodigoPronto();
        $planoDeContaCodigo = $comando->obterPlanoDeContaCodigoPronto();
        $descricao = $comando->obterDescricaoPronto();

        $pagadorCodigo = $comando->obterPagadorCodigoPronto();
        $cobrancaCodigo = $comando->obterCobrancaCodigoPronto();
        $boletoCodigo = $comando->obterBoletoCodigoPronto();


        $entidadeCobranca = false;
        if(!empty($cobrancaCodigo)){

            try {
                $cobrancaCodigo = new IdentificacaoUnica($cobrancaCodigo);

                $cobrancaDados = $this->repositorioCobranca->buscarCobrancaPorCodigo(
                    cobrancaCodigo: $cobrancaCodigo->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get()
                );
                $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($cobrancaDados);

            }catch (Exception $erro){
                throw new Exception("Ops, a cobrança informada não é válida. - {$erro->getMessage()}");
            }
        }


        $entidadeBoleto = false;
        if(!empty($boletoCodigo)){

            try {
                $boletoCodigo = new IdentificacaoUnica($boletoCodigo);

                $boletoDados = $this->repositorioBoleto->buscarBoletoPorCodigo(
                    codigoBoleto: $boletoCodigo->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get()
                );
                $entidadeBoleto = EntidadeBoleto::instanciarEntidadeBoleto($boletoDados);

            }catch (Exception $erro){
                throw new Exception("Ops, o boleto informado não é válido. - {$erro->getMessage()}");
            }
        }

        $entidadePagador = false;
        if(!empty($pagadorCodigo)){

            try {
                $pagadorCodigo = new IdentificacaoUnica($pagadorCodigo);

                $pagadorDados = $this->repositorioClientes->buscarClientePorCodigo(
                    codigoCliente: $pagadorCodigo->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get()
                );
                $entidadePagador = EntidadeCliente::instanciarEntidadeCliente($pagadorDados);

            }catch (Exception $erro){
                throw new Exception("Ops, o pagador informado não é válido. - {$erro->getMessage()}");
            }
        }

        try {
            $descricao = new TextoSimples($descricao);
        }catch (Exception $erro){
            throw new Exception("Ops, a descrição informada não é válida. - {$erro->getMessage()}");
        }
        $valor = $comando->obterValorPronto();

        try {
            $valor = new Valor($valor);
        }catch (Exception $erro){
            throw new Exception("Ops, o valor informado não é válido. - {$erro->getMessage()}");
        }
        $dataMovimentacao = $comando->obterDataMovimentacaoPronto();

        try {
            $dataMovimentacao = new DateTime($dataMovimentacao);
        }catch (Exception $erro){
            throw new Exception("Ops, a data de movimentação informada não é válida. - {$erro->getMessage()}");
        }

        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $contaBancariaCodigo,
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::FinanceiroCaixa,
                mensagem: "Ops, não foi possível encontrar a conta bancária. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível encontrar a conta bancária. ".$erro->getMessage());
        }

        try {

            $planoDeContaDados = $this->repositorioPlanoDeContas->buscarPlanoDeContaPorCodigo($planoDeContaCodigo);

            $entidadePlanoDeConta = EntidadePlanoDeConta::instanciarEntidadePlanoDeConta($planoDeContaDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::FinanceiroCaixa,
                mensagem: "Ops, não foi possível encontrar o plano de conta. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível encontrar o plano de conta. ".$erro->getMessage());
        }

        try {

            $parametrosOpcionais = [];
            $parametrosOpcionais['movimentacaoCodigo'] = $movimentacaoCodigo->get();
            $parametrosOpcionais['valor'] = $valor->get();
            $parametrosOpcionais['descricao'] = $descricao->get();
            $parametrosOpcionais['planoDeContaCodigo'] = $entidadePlanoDeConta->codigo;
            $parametrosOpcionais['planoDeContaNome'] = $entidadePlanoDeConta->nome->get();
            $parametrosOpcionais['dataMovimentacao'] = $dataMovimentacao->format('Y-m-d H:i:s');
            $parametrosOpcionais['contaBancariaCodigo'] = $entidadeContaBancaria->codigo->get();
            $parametrosOpcionais['empresaCodigo'] = $entidadeEmpresarial->codigo->get();
            $parametrosOpcionais['usuarioCodigo'] = $entidadeUsuario->codigo->get();

            if(is_a($entidadeCobranca, EntidadeCobranca::class)){
                $parametrosOpcionais['cobrancaCodigo'] = $entidadeCobranca->cobrancaCodigo->get();
            }
            if(is_a($entidadeBoleto, EntidadeBoleto::class)){
                $parametrosOpcionais['boletoCodigo'] = $entidadeBoleto->codigo->get();
                $parametrosOpcionais['boletoNossoNumero'] = $entidadeBoleto->nossoNumero->get();
            }

            if(is_a($entidadePagador, EntidadeCliente::class)){
                $parametrosOpcionais['pagadorCodigo'] = $entidadePagador->codigo->get();
                $parametrosOpcionais['pagadorNomeCompleto'] = $entidadePagador->nomeCompleto->get();
                $parametrosOpcionais['pagadorDocumento'] = $entidadePagador->documento->get();
            }

            $parametrosLancamentoMovimentacaoNoCaixa = new FronteiraEntradaLancarMovimentacaoNoCaixa(...$parametrosOpcionais);
            $this->repositorioCaixa->lancarMovimentacaoNoCaixa($parametrosLancamentoMovimentacaoNoCaixa);

            $this->repositorioCaixa->salvarEvento(
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                movimentacaoCodigo: $movimentacaoCodigo->get(),
                descricao: "{$entidadeUsuario->nomeCompleto->get()} lançou uma movimentação no caixa. ({$entidadePlanoDeConta->nome->get()} - {$descricao->get()}) no valor de {$valor->get()} na data de movimentação {$dataMovimentacao->format('d/m/Y H:i:s')}.",
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::FinanceiroCaixa,
                mensagem: "{$entidadeUsuario->nomeCompleto->get()} lançou uma movimentação no caixa. ({$entidadePlanoDeConta->nome->get()} - {$descricao->get()}) no valor de {$valor->get()} na data de movimentação {$dataMovimentacao->format('d/m/Y H:i:s')}."
            );

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::FinanceiroCaixa,
                mensagem: "Ops, não foi possível lançar a movimentação no caixa. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível lançar a movimentação no caixa. ".$erro->getMessage());
        }

        return null;
    }
}
