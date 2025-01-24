<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BoletoLiquidarManualmente;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\Valor;
use DateTime;
use Exception;
use Override;

readonly final class ComandoBoletoLiquidarManualmente implements Comando
{

    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;
    private string $boletoCodigoPronto;
    private string $dataPagamentoPronto;
    private float $valorRecebidoPronto;
    public function __construct(
        public string $empresaCodigo,
        public string $usuarioCodigo,
        public string $boletoCodigo,
        public string $dataPagamento,
        public float $valorRecebido,
    ){}

    #[Override] public function executar(): void
    {

        if(empty($this->empresaCodigo)){
            throw new Exception("Ops, o parâmetro empresa código não pode ser vazio.");
        }

        if(empty($this->usuarioCodigo)){
            throw new Exception("Ops, o parâmetro usuário código não pode ser vazio.");
        }

        if(empty($this->boletoCodigo)){
            throw new Exception("Ops, o parâmetro boleto código na plataforma não pode ser vazio.");
        }

        if(empty($this->dataPagamento)){
            throw new Exception("Ops, o parâmetro data de pagamento não pode ser vazio.");
        }

        if($this->valorRecebido <= 0){
            throw new Exception("Ops, o valor recebido não pode ser menor ou igual a zero.");
        }

        try {
            $valorRecebido = new Valor($this->valorRecebido);
        }catch (Exception $e){
            throw new Exception("Ops, o valor recebido não é válido. - $this->valorRecebido");
        }

        try {
            $dataPagamento = new DateTime($this->dataPagamento);
        }catch (Exception $e){
            throw new Exception("Ops, a data de pagamento não é válida. - $this->dataPagamento");
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $e){
            throw new Exception("Ops, o parâmetro empresa código não é válido.");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch (Exception $e){
            throw new Exception("Ops, o parâmetro usuário código não é válido.");
        }

        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
        $this->boletoCodigoPronto = $this->boletoCodigo;
        $this->dataPagamentoPronto = $dataPagamento->format('Y-m-d');
        $this->valorRecebidoPronto = $valorRecebido->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'usuarioCodigo' => $this->usuarioCodigo,
            'boletoCodigo' => $this->boletoCodigo,
            'dataPagamento' => $this->dataPagamento,
            'valorRecebido' => $this->valorRecebido
        ];
    }

    public function obterValorRecebidoPronto(): float
    {
        return $this->valorRecebidoPronto;
    }

    public function obterDataPagamentoPronto(): string
    {
        return $this->dataPagamentoPronto;
    }

    public function obterEmpresaCodigoPronto(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterBoletoCodigoPronto(): string
    {
        return $this->boletoCodigoPronto;
    }

    public function obterUsuarioCodigoPronto(): string
    {
        return $this->usuarioCodigoPronto;
    }
}