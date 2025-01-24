<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca;

use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Valor;

final class ItemDaCobranca
{
    public function __construct(
        public TextoSimples $descricao,
        public int $planoDeContasCodigo,
        public TextoSimples $planoDeContaNome,
        public Valor $valor,
    ){}

    public function toArray(): array
    {
        return [
            'descricao' => $this->descricao->get(),
            'planoDeContasCodigo' => $this->planoDeContasCodigo,
            'planoDeContaNome' => $this->planoDeContaNome->get(),
            'valor' => $this->valor->get()
        ];
    }
}
