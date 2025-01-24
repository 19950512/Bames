<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\NotificarCompromisso;

use DateTime;
use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final class ComandoNotificarCompromisso implements Comando
{

    private string $codigoEventoPronto;
    private string $empresaCodigoPronto;

    public function __construct(
        public string $codigoEvento,
        public string $empresaCodigo,
    ) {}

    #[Override] public function executar(): void
    {

        try {

            $eventoCodigo = new IdentificacaoUnica($this->codigoEvento);
        } catch (Exception $erro) {
            throw new Exception('O código do evento informado está inválido.');
        }

        try {

            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        } catch (Exception $erro) {
            throw new Exception('O código da empresa informado está inválido.');
        }

        $this->codigoEventoPronto = $eventoCodigo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'codigoEvento' => $this->codigoEventoPronto,
            'empresaCodigo' => $this->empresaCodigoPronto,
        ];
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterEventoCodigo(): string
    {
        return $this->codigoEventoPronto;
    }
}
