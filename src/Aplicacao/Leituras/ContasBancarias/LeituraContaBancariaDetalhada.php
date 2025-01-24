<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\ContasBancarias;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use Exception;

final class LeituraContaBancariaDetalhada
{
    public function __construct(
        private RepositorioContaBancaria $repositorioContaBancaria,
        private Cache $cache
    ){}

    public function executar(string $empresaCodigo, string $contaBancariaCodigo): array
    {

        $keyCache = "$empresaCodigo/contaBancariaDetalhada/$contaBancariaCodigo";
        if($this->cache->get($keyCache)){
            return json_decode($this->cache->get($keyCache), true);
        }

        try {

            $contaBancaria = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $contaBancariaCodigo,
                empresaCodigo: $empresaCodigo
            );

            $contaBancariaDetalhada = [
                'codigo' => $contaBancaria->codigo,
                'nome' => $contaBancaria->nome,
                'chaveAPI' => str_repeat('*', 16),
                'banco' => $contaBancaria->banco,
                'ambiente' => $contaBancaria->ambiente,
                'clientID' => $contaBancaria->clientIDAPI
            ];

            $this->cache->set($keyCache, json_encode($contaBancariaDetalhada), 60 * 60 * 24);

            return $contaBancariaDetalhada;

        }catch (Exception $erro) {
            throw new Exception("Ops, não foi possível buscar a conta bancária. - {$erro->getMessage()}");
        }
    }
}