<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\HTTP;

use Exception;

final readonly class RespostaHTTP
{
    public function __construct(
        public int $code,
        public string | array $body,
    ){

		if($this->code < 100 || $this->code > 599){
			throw new Exception('Código de status HTTP inválido. ('.$this->code.')');
		}
    }
}