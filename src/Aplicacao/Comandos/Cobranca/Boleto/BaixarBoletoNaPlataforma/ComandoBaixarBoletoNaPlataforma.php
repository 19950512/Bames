<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

readonly final class ComandoBaixarBoletoNaPlataforma implements Comando
{
    private string $empresaCodigoPronto;
    private string $boletoCodigoPronto;
    private string $usuarioCodigoPronto;

    public function __construct(
        private string $empresaCodigo,
        private string $usuarioCodigo,
        private string $boletoCodigo,
    ){}

    #[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do usuário informado está inválido. {$erro->getMessage()}");
        }

        try {
            $boletoCodigo = new IdentificacaoUnica($this->boletoCodigo);
        }catch (Exception $erro){
            throw new Exception("O código do boleto informado está inválido. {$erro->getMessage()}");
        }

        $this->usuarioCodigoPronto = $usuarioCodigo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->boletoCodigoPronto = $boletoCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'usuarioCodigo' => $this->usuarioCodigo,
            'boletoCodigo' => $this->boletoCodigo
        ];
    }

    public function getEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function getBoletoCodigo(): string
    {
        return $this->boletoCodigoPronto;
    }

    public function obterUsuarioCodigoPronto(): string
    {
        return $this->usuarioCodigoPronto;
    }
}