<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Webhook;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Webhook\Enums\Parceiro;
use Override;

final readonly class ComandoReceberWebhook implements Comando
{
    private array $payloadPronto;
    private array $headersPronto;
    private string $uriPronto;
    private string $metodoPronto;
    private string $ipPronto;
    private string $userAgentPronto;
    private string $parceiroPronto;

    public function __construct(
        private array $payload,
        private array $headers,
        private string $uri,
        private string $metodo,
        private string $ip,
        private string $userAgent,
        private Parceiro $parceiro
    ){}

    #[Override] public function executar(): void
    {
        $this->payloadPronto = $this->payload;
        $this->headersPronto = $this->headers;
        $this->uriPronto = $this->uri;
        $this->metodoPronto = $this->metodo;
        $this->ipPronto = $this->ip;
        $this->userAgentPronto = $this->userAgent;
        $this->parceiroPronto = $this->parceiro->value;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'payload' => $this->payload,
            'headers' => $this->headers,
            'uri' => $this->uri,
            'metodo' => $this->metodo,
            'ip' => $this->ip,
            'userAgent' => $this->userAgent
        ];
    }

    public function obterPayloadPronto(): array
    {
        return $this->payloadPronto;
    }

    public function obterHeadersPronto(): array
    {
        return $this->headersPronto;
    }

    public function obterUriPronto(): string
    {
        return $this->uriPronto;
    }

    public function obterMetodoPronto(): string
    {
        return $this->metodoPronto;
    }

    public function obterIpPronto(): string
    {
        return $this->ipPronto;
    }

    public function obterParceiroPronto(): string
    {
        return $this->parceiroPronto;
    }

    public function obterUserAgentPronto(): string
    {
        return $this->userAgentPronto;
    }
}