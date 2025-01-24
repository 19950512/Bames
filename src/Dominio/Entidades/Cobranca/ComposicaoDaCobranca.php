<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca;

class ComposicaoDaCobranca
{
    private array $items = [];

    public function __construct(){}

    public function adicionarItem(ItemDaCobranca $item): void
    {
        $this->items[] = $item;
    }

    public function obter(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $item) {
            if(is_a($item, ItemDaCobranca::class)){
                $items[] = $item->toArray();
            }
        }
        return $items;
    }

    public function getMensagem(): string
    {
        $mensagens = [];
        foreach ($this->items as $item) {
            if(is_a($item, ItemDaCobranca::class)){
                $mensagens[] = $item->planoDeContaNome->get(). ' - '. $item->descricao->get(). ' - R$: '. $item->valor->get();
            }
        }
        return implode(PHP_EOL, $mensagens);
    }
}
