<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

readonly final class LinkParaDownload
{
    private string $_link;
    public function __construct(
        private string $link
    ){
        if (empty($link)) {
            throw new Exception('Link para download não pode ser vazio.');
        }

        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            throw new Exception('Link para download inválido.');
        }

        $this->_link = $link;
    }

    public function get(): string
    {
        return $this->_link;
    }
}
