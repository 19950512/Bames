<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Clientes;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

final readonly class LeituraClienteProcessos
{
    public function __construct(
        private RepositorioProcessos $repositorioProcessos,
        private RepositorioClientes $repositorioClientes,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private Ambiente $ambiente,
        private Cache $cache
    ){}

    public function executar(String $clienteCodigo): array
    {

       $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/cliente/{$clienteCodigo}/processos";
        if($this->cache->exist($keyCache)){
            return unserialize($this->cache->get($keyCache));
        }

        $clienteData = $this->repositorioClientes->buscarClientePorCodigo(
            codigoCliente: $clienteCodigo,
            empresaCodigo: $this->entidadeEmpresarial->codigo->get()
        );

        $processosDoCliente = $this->repositorioProcessos->obterProcessosDoClientePorDocumento(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
            documento: $clienteData->documento
        )->toArray();

        $this->cache->set(
            key: $keyCache,
            value: serialize($processosDoCliente),
            expireInSeconds: 60 * 60 * 24 // 24 horas
        );

        return $processosDoCliente;
    }
}