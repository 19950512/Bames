<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Empresa\Colaboradores;

use Exception;

class Colaboradores
{
	private array $colaboradores = [];

	public function adicionarColaborador(EntidadeColaborador $colaborador): void
	{
		$this->colaboradores[] = $colaborador;
	}

	public function obterColaboradores(): array
	{
		return array_map(function($colaborador){
            return $colaborador->toArray();
        }, $this->colaboradores);
	}

    public function obterColaboradorPorCodigo(string $codigo): EntidadeColaborador
    {
        foreach ($this->colaboradores as $colaborador){

            if(is_a($colaborador, EntidadeColaborador::class) and $colaborador->codigo->get() === $codigo){
                return $colaborador;
            }
        }

        throw new Exception("Colaborador não encontrado.");
    }
}