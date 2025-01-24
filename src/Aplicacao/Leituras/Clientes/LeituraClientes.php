<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Clientes;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Clientes\Fronteiras\ClienteInformacoesBasicas;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

final readonly class LeituraClientes
{
    public function __construct(
        private RepositorioProcessos $repositorioProcessos,
        private RepositorioClientes $repositorioClientes,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private Ambiente $ambiente,
        private Cache $cache
    ){}

    public function executar(): array
    {

       $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/clientes";
        if($this->cache->exist($keyCache) and !$this->ambiente->get('APP_DEBUG')){
            return unserialize($this->cache->get($keyCache));
        }

        $clientes = array_map(function($cliente){
            if(is_a($cliente, ClienteInformacoesBasicas::class)){

                return [
                    'codigo' => $cliente->codigo,
                    'nomeCompleto' => $cliente->nomeCompleto,
                    'documento' => $cliente->documento,
                    'whatsapp' => $cliente->whatsapp,
                ];
            }
        }, $this->repositorioClientes->getTodosClientes(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get()
        )->toArray());

        $this->cache->set(
            key: $keyCache,
            value: serialize($clientes),
            expireInSeconds: 60 * 60 * 24 // 24 horas
        );

        return $clientes;
    }
}