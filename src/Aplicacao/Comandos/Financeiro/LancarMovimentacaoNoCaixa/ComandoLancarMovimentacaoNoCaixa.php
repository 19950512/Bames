<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Valor;
use DateTime;
use Exception;
use Override;

readonly final class ComandoLancarMovimentacaoNoCaixa implements Comando
{

    private float $valorPronto;
    private string $descricaoPronto;
    private int $planoDeContaCodigoPronto;
    private string $dataMovimentacaoPronto;
    private string $contaBancariaCodigoPronto;
    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;
    private string $pagadorCodigoPronto;
    private string $cobrancaCodigoPronto;

    private string $boletoCodigoPronto;

    public function __construct(
        public float $valor,
        public string $descricao,
        public int $planoDeContaCodigo,
        public string $dataMovimentacao,
        public string $contaBancariaCodigo,
        public string $empresaCodigo,
        public string $usuarioCodigo,
        public string $pagadorCodigo = '',
        public string $cobrancaCodigo = '',
        public string $boletoCodigo = '',
    ){}

    #[Override] public function executar(): void
    {

        if($this->planoDeContaCodigo <= 0){
            throw new Exception('Plano de conta é obrigatório');
        }

        if(empty($this->dataMovimentacao)){
            throw new Exception('Data da movimentação é obrigatória');
        }

        if(empty($this->contaBancariaCodigo)){
            throw new Exception('Conta bancária é obrigatória');
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('Empresa é obrigatória');
        }
        if(empty($this->usuarioCodigo)){
            throw new Exception('Usuário é obrigatório');
        }

        try {
            $valor = new Valor($this->valor);
        }catch (Exception $e){
            throw new Exception('Valor inválido');
        }

        try {
            $descricao = new TextoSimples($this->descricao);
        }catch (Exception $e){
            throw new Exception('Descrição inválida');
        }

        try{
            $dataMovimentacao = new DateTime($this->dataMovimentacao);
        }catch (Exception $e){
            throw new Exception('Data da movimentação inválida');
        }

        try {
            $contaBancariaCodigo = new IdentificacaoUnica($this->contaBancariaCodigo);
        }catch (Exception $e){
            throw new Exception('Conta bancária inválida');
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $e){
            throw new Exception('Empresa inválida');
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch (Exception $e){
            throw new Exception('Usuário inválido');
        }

        $cobrancaCodigo = $this->cobrancaCodigo;
        if(!empty($this->cobrancaCodigo)){
            try {
                $cobrancaCodigo = new IdentificacaoUnica($this->cobrancaCodigo);
            }catch (Exception $e){
                throw new Exception('Código da cobrança inválido');
            }
        }

        $boletoCodigo = $this->boletoCodigo;
        if(!empty($this->boletoCodigo)){
            try {
                $boletoCodigo = new IdentificacaoUnica($this->boletoCodigo);
            }catch (Exception $e){
                throw new Exception('Código do boleto inválido');
            }
        }

        $pagadorCodigo = $this->pagadorCodigo;
        if(!empty($this->pagadorCodigo)){
            try {
                $pagadorCodigo = new IdentificacaoUnica($this->pagadorCodigo);
            }catch (Exception $e){
                throw new Exception('Código do pagador inválido');
            }
        }

        $this->pagadorCodigoPronto = is_a($pagadorCodigo, IdentificacaoUnica::class) ? $pagadorCodigo->get() : $pagadorCodigo;
        $this->boletoCodigoPronto = is_a($boletoCodigo, IdentificacaoUnica::class) ? $boletoCodigo->get() : $boletoCodigo;
        $this->cobrancaCodigoPronto = is_a($cobrancaCodigo, IdentificacaoUnica::class) ? $cobrancaCodigo->get() : $cobrancaCodigo;

        $this->valorPronto = $valor->get();
        $this->descricaoPronto = $descricao->get();
        $this->planoDeContaCodigoPronto = $this->planoDeContaCodigo;
        $this->dataMovimentacaoPronto = $dataMovimentacao->format('Y-m-d H:i:s');
        $this->contaBancariaCodigoPronto = $contaBancariaCodigo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'valor' => $this->valor,
            'descricao' => $this->descricao,
            'planoDeContaCodigo' => $this->planoDeContaCodigo,
            'dataMovimentacao' => $this->dataMovimentacao,
            'contaBancariaCodigo' => $this->contaBancariaCodigo,
            'empresaCodigo' => $this->empresaCodigo,
            'usuarioCodigo' => $this->usuarioCodigo,
            'pagadorCodigo' => $this->pagadorCodigo,
            'cobrancaCodigo' => $this->cobrancaCodigo,
            'boletoCodigo' => $this->boletoCodigo,
        ];
    }

    public function obterEmpresaCodigoPronto(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterValorPronto(): float
    {
        return $this->valorPronto;
    }

    public function obterDescricaoPronto(): string
    {
        return $this->descricaoPronto;
    }

    public function obterPlanoDeContaCodigoPronto(): int
    {
        return $this->planoDeContaCodigoPronto;
    }

    public function obterDataMovimentacaoPronto(): string
    {
        return $this->dataMovimentacaoPronto;
    }

    public function obterContaBancariaCodigoPronto(): string
    {
        return $this->contaBancariaCodigoPronto;
    }

    public function obterUsuarioCodigoPronto(): string
    {
        return $this->usuarioCodigoPronto;
    }

    public function obterPagadorCodigoPronto(): string
    {
        return $this->pagadorCodigoPronto;
    }

    public function obterCobrancaCodigoPronto(): string
    {
        return $this->cobrancaCodigoPronto;
    }

    public function obterBoletoCodigoPronto(): string
    {
        return $this->boletoCodigoPronto;
    }
}
