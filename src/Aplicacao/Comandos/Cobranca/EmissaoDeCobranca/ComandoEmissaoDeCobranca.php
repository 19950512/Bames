<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\EmissaoDeCobranca;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Valor;
use DateTime;
use Exception;
use Override;

readonly final class ComandoEmissaoDeCobranca implements Comando
{
    private string $clienteCodigoPronto;
    private string $descricaoPronto;
    private string $contaBancariaCodigoPronto;
    private string $dataVencimentoPronto;
    private string $meioDePagamentoPronto;
    private array $composicaoDaCobrancaPronto;
    private float $valorJurosPronto;
    private float $valorMultaPronto;
    private int $parcelasPronto;
    public function __construct(
        private string $clienteCodigo,
        private string $descricao,
        private string $dataVencimento,
        private string $contaBancariaCodigo,
        private string $meioDePagamento,
        private array $composicaoDaCobranca,
        private float $valorJuros,
        private float $valorMulta,
        private int $parcelas,
    ) {}

    #[Override] public function executar(): void
    {

        try {
            $clienteCodigo = new IdentificacaoUnica($this->clienteCodigo);
        }catch (Exception $erro){
            throw new Exception("O código do cliente precisa ser informado adequadamente.");
        }

        if(empty($this->dataVencimento)){
            throw new Exception('A data de vencimento da cobrança precisa ser informada adequadamente.');
        }

        if(empty($this->contaBancariaCodigo)){
            throw new Exception('A conta bancária precisa ser informada adequadamente.');
        }

        if($this->valorJuros < 0){
            throw new Exception('O valor dos juros precisa ser informado adequadamente.');
        }

        if($this->valorMulta < 0){
            throw new Exception('O valor da multa precisa ser informado adequadamente.');
        }

        if($this->valorJuros > 1){
            throw new Exception('O juros não pode ser maior que 1%.');
        }

        if($this->valorMulta > 10){
            throw new Exception('A multa não pode ser maior que 10%.');
        }

        try {
            $dataVencimento = new DateTime($this->dataVencimento);
        }catch (Exception $erro){
            throw new Exception("A data de vencimento da cobrança precisa ser informada adequadamente.");
        }

        if(empty($this->descricao)){
            throw new Exception('A descrição da cobrança precisa ser informada adequadamente.');
        }

        try {
            $descricao = new TextoSimples($this->descricao);
        }catch (Exception $erro){
            throw new Exception("A descrição da cobrança precisa ser informada adequadamente.");
        }

        if($this->parcelas < 1){
            throw new Exception('O número de parcelas precisa ser informado adequadamente.');
        }

        if($this->parcelas > 12){
            throw new Exception('O número de parcelas não pode ser maior que 12.');
        }

        if(empty($this->meioDePagamento)){
            throw new Exception('O meio de pagamento precisam ser informados adequadamente, pelo menos 1.');
        }

        if(count($this->composicaoDaCobranca) <= 0){
            throw new Exception('A composição da cobrança precisa ser informada adequadamente.');
        }

        foreach($this->composicaoDaCobranca as $item){
            if(!isset($item['descricao']) || !isset($item['planoDeContaCodigo']) || !isset($item['valor'])){
                throw new Exception('O item da composição da cobrança precisa ser informado adequadamente (descricao, valor e planoDeContaCodigo).');
            }
        }

        try {
            $valorJuros = new Valor($this->valorJuros);
        }catch (Exception $erro){
            throw new Exception("O valor dos juros precisa ser informado adequadamente.");
        }

        try {
            $valorMulta = new Valor($this->valorMulta);
        }catch (Exception $erro){
            throw new Exception("O valor da multa precisa ser informado adequadamente.");
        }

        $meiosDePagamentoAceitos = array_map(function($meioPagamento){
            return $meioPagamento->value;
        },MeioPagamento::obterTodos());

        if(!in_array(mb_ucfirst(mb_strtolower($this->meioDePagamento)), $meiosDePagamentoAceitos)){
            throw new Exception('Meio de pagamento inválido.');
        }

        try {
            $contaBancariaCodigo = new IdentificacaoUnica($this->contaBancariaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da conta bancária precisa ser informado adequadamente.");
        }

        $this->parcelasPronto = $this->parcelas;
        $this->valorJurosPronto = $valorJuros->get();
        $this->valorMultaPronto = $valorMulta->get();
        $this->contaBancariaCodigoPronto = $contaBancariaCodigo->get();
        $this->dataVencimentoPronto = $dataVencimento->format('Y-m-d');
        $this->meioDePagamentoPronto = mb_ucfirst(mb_strtolower($this->meioDePagamento));
        $this->descricaoPronto = $descricao->get();
        $this->clienteCodigoPronto = $clienteCodigo->get();
        $this->composicaoDaCobrancaPronto = $this->composicaoDaCobranca;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'clienteCodigo' => $this->clienteCodigo,
            'descricao' => $this->descricao,
            'dataVencimento' => $this->dataVencimento,
            'meioDePagamento' => $this->meioDePagamento,
            'contaBancariaCodigo' => $this->contaBancariaCodigo,
            'composicaoDaCobranca' => $this->composicaoDaCobranca,
            'juros' => $this->valorJuros,
            'multa' => $this->valorMulta,
            'parcelas' => $this->parcelas,
        ];
    }

    public function getParcelas(): int
    {
        return $this->parcelasPronto;
    }

    public function getComposicaoDaCobranca(): array
    {
        return $this->composicaoDaCobrancaPronto;
    }

    public function getContaBancariaCodigo(): string
    {
        return $this->contaBancariaCodigoPronto;
    }

    public function getValorJuros(): float
    {
        return $this->valorJurosPronto;
    }

    public function getValorMulta(): float
    {
        return $this->valorMultaPronto;
    }

    public function getClienteCodigo(): string
    {
        return $this->clienteCodigoPronto;
    }

    public function getDescricao(): string
    {
        return $this->descricaoPronto;
    }

    public function getMeioDePagamento(): string
    {
        return $this->meioDePagamentoPronto;
    }

    public function getDataVencimento(): string
    {
        return $this->dataVencimentoPronto;
    }
}