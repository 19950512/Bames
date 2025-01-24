<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Planosdecontas;

use App\Aplicacao\Leituras\PlanoDeContas\LeituraPlanosDeContas;
use App\Aplicacao\Leituras\PlanoDeContas\LeituraPlanosDeContasAgrupados;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;

class PlanosdecontasController extends Controller
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

        if($this->method == 'GET'){
            $this->response($this->container->get(LeituraPlanosDeContas::class)->executar());
        }

        $this->response([
            'statusCode' => 400,
            'message' => 'Method not implemented'
        ]);
    }

    public function agrupados(): void
    {

        if($this->method == 'GET'){
            $this->response($this->container->get(LeituraPlanosDeContasAgrupados::class)->executar());
        }

        $this->response([
            'statusCode' => 400,
            'message' => 'Method not implemented'
        ]);
    }
}