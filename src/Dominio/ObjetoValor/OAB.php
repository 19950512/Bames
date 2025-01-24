<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;
use App\Dominio\ObjetoValor\Endereco\Estado;

readonly final class OAB
{
    private string $formatada;

    private string $uf;
    private string $numero;

    private string $tipo;

    public function __construct(
        private string $oab
    ){

        $estadoTemp = str_replace(['OAB', 'oab'], '', preg_replace('/[^A-Z]/', '', $this->oab));


        $tipoTemp = Tipo::ADVOGADO;
        if($estadoTemp === 'E'){
            $tipoTemp = Tipo::ESTAGIARIO;
        }

        if(strlen($estadoTemp) == 4){ // Suplementar RS e RJ por exemplo
            $tipoTemp = Tipo::SUPLEMENTAR;
        }

        if($tipoTemp === Tipo::ADVOGADO){
            try {
    
                $estadoSigla = new Estado($estadoTemp);
    
            }catch(Exception $erro){
                throw new Exception("Estado da OAB inválido. - {$erro->getMessage()}");
            }
            
            $this->uf = $estadoSigla->getUF();
        }

        if($tipoTemp === Tipo::ESTAGIARIO){
            $this->uf = 'E';
        }

        $suplementar = '';
        if($tipoTemp === Tipo::SUPLEMENTAR){
            $this->uf = substr($estadoTemp, 0, 2);
            $suplementar = "/{$estadoTemp[2]}";
        }


        $numero = (float) preg_replace('/[^0-9]/', '', $this->oab);

        $this->numero = number_format($numero, 0, '', '.');
        $this->formatada = "OAB/{$this->uf} {$this->numero}{$suplementar}";
    }

    public function get(): string
    {
        return $this->formatada;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getUF(): string
    {
        return $this->uf;
    }
}

enum Tipo: string
{
    case ADVOGADO = 'Advogado';
    case ESTAGIARIO = 'Estagiário';
    case SUPLEMENTAR = 'Suplementar';
    case ESTRANGEIRO = 'Estrangeiro';
}