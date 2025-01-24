<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Logout;

use DI\Container;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;

final class LogoutController extends Controller
{

    public function __construct(
		protected Container $container
    ){
        parent::__construct(
            container: $this->container
        );
    }

    public function index(): void
    {
    }
}

