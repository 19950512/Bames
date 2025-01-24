<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Webhook\Controladores\Erros;

use App\Infraestrutura\APIs\Webhook\Controladores\Middlewares\Controller;
use DI\Container;

class Erro404Controller extends Controller
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

        header("HTTP/1.0 404 Not Found");
    }
}

