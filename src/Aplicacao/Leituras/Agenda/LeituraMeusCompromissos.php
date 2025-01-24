<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Agenda;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Agenda\Fronteiras\CompromissoAgenda;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;

final class LeituraMeusCompromissos
{
    public function __construct(
        private RepositorioAgenda $repositorioAgenda,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private Cache $cache
    ){}

    public function executar(): array
    {
        $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/meuscompromissos/{$this->entidadeUsuarioLogado->codigo->get()}";

        if($this->cache->get($keyCache)){
            return json_decode($this->cache->get($keyCache), true);
        }

        $compromissosFormatados = array_map(function($compromisso){

            if(is_a($compromisso, CompromissoAgenda::class)){
                return [
                    'codigo' => $compromisso->codigo,
                    'business_id' => $compromisso->business_id,
                    'usuario_id' => $compromisso->usuario_id,
                    'plataforma_id' => $compromisso->plataforma_id,
                    'titulo' => $compromisso->titulo,
                    'descricao' => $compromisso->descricao,
                    'status' => $compromisso->status,
                    'dataInicio' => $compromisso->dataInicio,
                    'dataFim' => $compromisso->dataFim,
                    'momento' => $compromisso->momento,
                    'diaTodo' => $compromisso->diaTodo,
                    'recorrencia' => $compromisso->recorrencia,
                ];
            }
        }, $this->repositorioAgenda->buscarMeusCompromissos(
            $this->entidadeUsuarioLogado->codigo->get(),
            $this->entidadeEmpresarial->codigo->get()
        )->obterCompromissos());

        $this->cache->set($keyCache, json_encode($compromissosFormatados), 60 * 60 * 24);

        return $compromissosFormatados;
    }
}
