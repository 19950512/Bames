<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

final readonly class CNPJ implements DocumentoIdentificacao
{

    private string $numero;
    function __construct(
        private string $numeroDocumento
    ){

        if(!self::valido($this->numeroDocumento)){
            throw new Exception('O CNPJ informado não é válido. '.$this->numeroDocumento);
        }

        $this->numero = (new Mascara($this->numeroDocumento, '##.###.###/####-##'))->get();
    }

    function get(): string
    {
        return $this->numero;
    }

    function tipo(): string
    {
        return 'CNPJ';
    }

    public static function gerar() {
        // Gera os 8 primeiros dígitos do CNPJ (raiz)
        $cnpjRaiz = str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        
        // Calcula os primeiros 12 dígitos do CNPJ
        $cnpjCompleto = $cnpjRaiz . '0001'; // Adiciona o código de filial padrão
        
        // Calcula o primeiro dígito verificador
        $soma = 0;
        $multiplicador = 5;
        for ($i = 0; $i < 12; $i++) {
            $soma += $cnpjCompleto[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        $cnpjCompleto .= $digito1;
        
        // Calcula o segundo dígito verificador
        $soma = 0;
        $multiplicador = 6;
        for ($i = 0; $i < 13; $i++) {
            $soma += $cnpjCompleto[$i] * $multiplicador;
            $multiplicador = ($multiplicador == 2) ? 9 : $multiplicador - 1;
        }
        $resto = $soma % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        $cnpjCompleto .= $digito2;
        
        return $cnpjCompleto;
    }

    static function valido(string $numeroDocumento): bool
    {

        $cnpj = $numeroDocumento;

        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}