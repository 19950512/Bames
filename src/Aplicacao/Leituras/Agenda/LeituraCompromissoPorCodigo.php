<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Agenda;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Agenda\Fronteiras\CompromissoAgenda;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;

final class LeituraCompromissoPorCodigo
{
    public function __construct(
        private RepositorioAgenda $repositorioAgenda,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private Cache $cache
    ){}

    public function executar(string $compromissoCodigo): array
    {
        $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/compromisso/{$compromissoCodigo}";

        if($this->cache->get($keyCache)){
            return json_decode($this->cache->get($keyCache), true);
        }

        $compromisso = $this->repositorioAgenda->buscarEventoPorCodigo(
            codigo: $compromissoCodigo,
            empresaCodigo: $this->entidadeEmpresarial->codigo->get()
        );

        $this->cache->set($keyCache, json_encode($compromisso->toArray()), 60 * 60 * 24);

        return $compromisso->toArray();
    }
}
