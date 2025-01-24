<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Login;

use Exception;
use DI\Container;
use App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware\Controller;
use App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha\LidarLoginEmailESenha;
use App\Aplicacao\Comandos\Autenticacao\Login\EmailESenha\ComandoLoginEmailESenha;

final class LoginController extends Controller
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

        try {

            $comando = new ComandoLoginEmailESenha(
                email: $_POST['email'] ?? '',
                senha: $_POST['senha'] ?? ''
            );

			$comando->executar();
            
        }catch(Exception $erro){

			header('Content-Type: application/json');
			header('HTTP/1.1 400 Bad Request');
			echo json_encode([
				'message' => $erro->getMessage()
			]);
            return;
        }

        try {

            $lidarCadastrarEmpresa = $this->container->get(LidarLoginEmailESenha::class);

            $access_token = $lidarCadastrarEmpresa->lidar($comando);

            header('Content-Type: application/json');
            header('HTTP/1.1 201 Access Token');

            echo json_encode([
                'access_token' => $access_token->get()
            ]);
            return;

        } catch (Exception $erro) {
            header('Content-Type: application/json');
            header('HTTP/1.1 401 UNAUTHORIZED');
            echo json_encode([
                'message' => $erro->getMessage()
            ]);
        }
    }
}

