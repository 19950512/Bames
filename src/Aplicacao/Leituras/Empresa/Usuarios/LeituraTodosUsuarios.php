<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Empresa\Usuarios;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;

final readonly class LeituraTodosUsuarios
{
    public function __construct(
        private EntidadeEmpresarial $entidadeEmpresarial,
        private RepositorioEmpresa $repositorioEmpresa
    ){}

    public function executar(): array
    {
        return array_map(function($usuario){
            return [
                'codigo' => $usuario['acc_id'],
                'nome' => $usuario['acc_nickname'],
                'email' => $usuario['acc_email'],
            ];

        }, $this->repositorioEmpresa->buscarTodosUsuarios($this->entidadeEmpresarial->codigo->get()));
    }
}