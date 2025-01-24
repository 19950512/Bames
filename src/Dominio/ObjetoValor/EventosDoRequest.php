<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use DateTime;

final class EventosDoRequest
{
    private array $eventos = [];
    public IdentificacaoUnica $requestCodigo;

    public DateTime $momento;

    public function __construct(
        readonly public IdentificacaoUnica $empresaCodigo,
        readonly public IdentificacaoUnica $usuarioCodigo,
        readonly public AccessToken $accessToken
    ){
        $this->requestCodigo = new IdentificacaoUnica();
        $this->momento = new DateTime();
    }

    public function adicionar(Evento $evento): void
    {
        $this->eventos[] = $evento;
    }

    public function getArray(): array
    {
        return array_map(function(Evento $evento){
            return [
                'momento' => $evento->momento,
                'descricao' => $evento->get(),
            ];
        }, $this->eventos);
    }

    public function get(): array
    {
        return $this->eventos;
    }
}
