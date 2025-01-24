<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Oab;

use App\Aplicacao\Comandos\Processos\ComandoLidarConsultasProcessoPorOAB;
use App\Aplicacao\Comandos\Processos\LidarConsultarProcessoPorOAB;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\UsuarioSistema;
use Exception;
use DI\Container;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;

class OabController extends Controller
{

    public function __construct(
        private Container $container
    ){
        parent::__construct(
            container: $this->container
        );
    }

	public function index(): void
    {
        $this->response([
            'statusCode' => 400,
            'message' => 'Method not implemented'
        ]);
    }

    public function consultar(): void
    {

        $oab = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

        try {

            $comando = new ComandoLidarConsultasProcessoPorOAB(
                OAB: $oab,
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get()
            );
            $comando->executar();

            $this->container->get(LidarConsultarProcessoPorOAB::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Consulta realizada com sucesso'
            ]);

        }catch (Exception $e){

            $this->response([
                'statusCode' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }
}