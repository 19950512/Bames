<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\ContasBancarias;

use App\Dominio\Repositorios\ContaBancaria\Fronteiras\ContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;

final class LeituraContasBancarias
{

    public function __construct(
        private RepositorioContaBancaria $repositorioContaBancaria
    ){}

    public function executar(string $empresaCodigo): array
    {

        return array_map(function($contaBancaria){

            if(is_a($contaBancaria, ContaBancaria::class)){
                return [
                    'codigo' => $contaBancaria->contaBancariaCodigo,
                    'nome' => $contaBancaria->nome,
                    'chaveAPI' => str_repeat('*', 16),
                    'banco' => $contaBancaria->banco,
                    'ambiente' => $contaBancaria->ambiente,
                    'clientID' => $contaBancaria->clientIDAPI,
                    'eventos' => $contaBancaria->obterEventos()
                ];
            }

        }, $this->repositorioContaBancaria->buscarTodasAsContasBancarias(
            empresaCodigo: $empresaCodigo
        )->obterContasBancarias());
    }
}