<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

final readonly class Telefone
{

	private string $value;
    function __construct(
        private string $numero
    ){

		$numero = $this->numero;
        if(!empty($numero)){

            // remover o codigo do país do numero de telefone
            $numero = preg_replace('/^\+?\+55/', '', $numero);

            // remover mascara, deixar somente números
            $numero = preg_replace('/[^0-9]/', '', $numero);

            // remover zeros a esquerda
            $numero = ltrim($numero, '0');

            // acrescentar o 9 no numero de telefone caso não tenha
            /*
            if(strlen($numero) == 10){
                $numero = substr($numero, 0, 2).'9'.substr($numero, 2);
            }
            */
/*
            if(strlen($numero) == 11){
                // remover o 9 do inicio do numero de telefone
                $numero = substr($numero, 0, 2).substr($numero, 3);
            }

            if(strlen($numero) <= 9){
                throw new Exception('O número do telefone informado ("'.$numero.'") não é válido.');
            }*/

            $numero = (new Mascara(
                texto: $numero,
                mascara: strlen($numero) == 10 ? '(##) ####-####' : '(##) #####-####'
            ))->get();
        }
		$this->value = $numero;
    }

    function get(): string
    {
        return $this->value;
    }
}