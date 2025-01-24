<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\BotDiscord;

use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;

readonly final class Empresas
{
    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
    ){}
    public function totalClientes(): int
    {
        return $this->repositorioEmpresa->totalClientes();
    }

    public function totalClientesDetalhado(): array
    {
        return array_map(function($empresa){

            return [
                'codigo' => $empresa['business_id'],
                'nome' => $empresa['business_name'],
                'documento' => $empresa['business_document'],
                'email' => $empresa['business_email'],
                'whatsapp' => $empresa['business_whatsapp'],
                'cidade' => $empresa['business_address_city'],
                'estado' => $empresa['business_address_state'],
                'cep' => $empresa['business_address_cep'],
                'cadastro' => date('d/m/Y', strtotime($empresa['autodata'])),
            ];
        }, $this->repositorioEmpresa->totalClientesDetalhado());
    }

}