<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Erros;

use DI\Container;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;

final class Erro404Controller extends Controller
{

    public function __construct(
		protected Container $container
    ){
        parent::__construct(
            container: $this->container
        );
    }

    public function index()
    {
        header("HTTP/1.0 404 Not Found");
    }
}

