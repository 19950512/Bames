<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Contrato\NovoContrato;

use DateTime;
use Override;
use Exception;
use App\Dominio\ObjetoValor\Dia;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Horario;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Cobranca\Enumerados\Parcela;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoDesconto;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;

readonly final class ComandoNovoContrato implements Comando
{

    private string $empresaCodigoPronto;
    private string $clienteCodigoPronto;
    private string $contaBancariaCodigoPronto;
    private string $usuarioCodigoPronto;
    private string $dataInicioPronto;
    private string $meioPagamentoPronto;
    private int $diaVencimentoPronto;
    private int $diaEmissaoCobrancaPronto;
    private string $horarioEmissaoCobrancaPronto;
    private int $parcelaPronto;
    private float $valorPronto;
    private float $jurosPronto;
    private float $multaPronto;
    private float $descontoAntecipacaoPronto;
    private bool $recorrentePronto;
    private string $tipoJurosPronto;
    private string $tipoMultaPronto;
    private string $tipoDescontoAntecipacaoPronto;

    public function __construct(
        private string $empresaCodigo,
        private string $usuarioCodigo,
        private string $contaBancariaCodigo,
        private string $clienteCodigo,
        private string $dataInicio,
        private string $meioPagamento,
        private int $diaVencimento,
        private int $diaEmissaoCobranca,
        private string $horarioEmissaoCobranca,
        private int $parcela,
        private float $valor,
        private float $juros,
        private float $multa,
        private float $descontoAntecipacao,
        private bool $recorrente,
        private string $tipoJuros,
        private string $tipoMulta,
        private string $tipoDescontoAntecipacao
    ){}

	#[Override] public function executar(): void
	{
        
        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa não foi informado.');
        }
        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código da empresa informado está inválido.');
        }

        if(empty($this->usuarioCodigo)){
            throw new Exception('O código do usuário não foi informado.');
        }
        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do usuário informado está inválido. {$erro->getMessage()}");
        }

        if(empty($this->clienteCodigo)){
            throw new Exception('O código do cliente não foi informado.');
        }
        try {
            $clienteCodigo = new IdentificacaoUnica($this->clienteCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código do cliente informado está inválido.');
        }
        
        if(empty($this->contaBancariaCodigo)){
            throw new Exception('O código da conta bancária não foi informado.');
        }
        try {
            $contaBancariaCodigo = new IdentificacaoUnica($this->contaBancariaCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código da conta bancária informado está inválido.');
        }

        try {
            $dataInicio = new DateTime($this->dataInicio);
        } catch (Exception $erro) {
            throw new Exception('A data de início informada está inválida.');
        }

        $meioPagamento = MeioPagamento::tryFrom(mb_ucfirst($this->meioPagamento));
        if($meioPagamento === null){
            throw new Exception("O meio de pagamento informado está inválido.");
        }

        try {
            $diaVencimento = new Dia($this->diaVencimento);
        } catch (Exception $erro) {
            throw new Exception('O dia de vencimento informado está inválido.');
        }

        try {
            $diaEmissaoCobranca = new Dia($this->diaEmissaoCobranca);
        } catch (Exception $erro) {
            throw new Exception('O dia de emissão da cobrança informado está inválido.');
        }

        try {
            $horarioEmissaoCobranca = Horario::criar($this->horarioEmissaoCobranca);
        } catch (Exception $erro) {
            throw new Exception('O horário de emissão da cobrança informado está inválido.');
        }

        $parcela = Parcela::tryFrom($this->parcela);
        if($parcela === null){
            throw new Exception('A parcela informada está inválida.');
        }

        $tipoJuros = TipoJuro::tryFrom(mb_strtoupper($this->tipoJuros));
        if($tipoJuros === null){
            throw new Exception("O tipo de juros informado está inválido.");
        }

        $tipoMulta = TipoMulta::tryFrom(mb_strtoupper($this->tipoMulta));
        if($tipoMulta === null){
            throw new Exception("O tipo de multa informado está inválido.");
        }

        $tipoDescontoAntecipacao = TipoDesconto::tryFrom(mb_strtoupper($this->tipoDescontoAntecipacao));
        if($tipoDescontoAntecipacao === null){
            throw new Exception("O tipo de desconto de antecipação informado está inválido.");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
        $this->contaBancariaCodigoPronto = $contaBancariaCodigo->get();
        $this->clienteCodigoPronto = $clienteCodigo->get();
        $this->dataInicioPronto = $dataInicio->format('Y-m-d H:i:s');
        $this->meioPagamentoPronto = $meioPagamento->value;
        $this->diaVencimentoPronto = $diaVencimento->get();
        $this->diaEmissaoCobrancaPronto = $diaEmissaoCobranca->get();
        $this->horarioEmissaoCobrancaPronto = $horarioEmissaoCobranca->get();
        $this->parcelaPronto = $parcela->value;
        $this->valorPronto = $this->valor;
        $this->jurosPronto = $this->juros;
        $this->multaPronto = $this->multa;
        $this->descontoAntecipacaoPronto = $this->descontoAntecipacao;
        $this->tipoJurosPronto = $tipoJuros->value;
        $this->tipoMultaPronto = $tipoMulta->value;
        $this->tipoDescontoAntecipacaoPronto = $tipoDescontoAntecipacao->value;
        $this->recorrentePronto = $this->recorrente;
	}

	#[Override] public function getPayload(): array
	{
		return [
            'empresaCodigo' => $this->empresaCodigo,
            'clienteCodigo' => $this->clienteCodigo,
            'contaBancariaCodigo' => $this->contaBancariaCodigo,
            'usuarioCodigo' => $this->usuarioCodigo,
            'dataInicio' => $this->dataInicio,
            'meioPagamento' => $this->meioPagamento,
            'diaVencimento' => $this->diaVencimento,
            'diaEmissaoCobranca' => $this->diaEmissaoCobranca,
            'horarioEmissaoCobranca' => $this->horarioEmissaoCobranca,
            'parcela' => $this->parcela,
            'valor' => $this->valor,
            'juros' => $this->juros,
            'multa' => $this->multa,
            'descontoAntecipacao' => $this->descontoAntecipacao,
            'tipoJuros' => $this->tipoJuros,
            'tipoMulta' => $this->tipoMulta,
            'tipoDescontoAntecipacao' => $this->tipoDescontoAntecipacao,
            'recorrente' => $this->recorrente
        ];
	}

    public function obterRecorrente(): bool
    {
        return $this->recorrentePronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterClienteCodigo(): string
    {
        return $this->clienteCodigoPronto;
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioCodigoPronto;
    }

    public function obterContaBancariaCodigo(): string
    {
        return $this->contaBancariaCodigoPronto;
    }

    public function obterDataInicio(): string
    {
        return $this->dataInicioPronto;
    }

    public function obterMeioPagamento(): string
    {
        return $this->meioPagamentoPronto;
    }

    public function obterDiaVencimento(): int
    {
        return $this->diaVencimentoPronto;
    }

    public function obterDiaEmissaoCobranca(): int
    {
        return $this->diaEmissaoCobrancaPronto;
    }

    public function obterHorarioEmissaoCobranca(): string
    {
        return $this->horarioEmissaoCobrancaPronto;
    }

    public function obterParcela(): int
    {
        return $this->parcelaPronto;
    }

    public function obterValor(): float
    {
        return $this->valorPronto;
    }

    public function obterJuros(): float
    {
        return $this->jurosPronto;
    }

    public function obterMulta(): float
    {
        return $this->multaPronto;
    }

    public function obterDescontoAntecipacao(): float
    {
        return $this->descontoAntecipacaoPronto;
    }

    public function obterTipoJuros(): string
    {
        return $this->tipoJurosPronto;
    }

    public function obterTipoMulta(): string
    {
        return $this->tipoMultaPronto;
    }

    public function obterTipoDescontoAntecipacao(): string
    {
        return $this->tipoDescontoAntecipacaoPronto;
    }
}