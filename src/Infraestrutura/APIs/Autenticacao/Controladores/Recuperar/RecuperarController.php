<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Recuperar;

use App\Aplicacao\Comandos\Autenticacao\RecuperarSenha\ComandoRecuperarSenha;
use App\Aplicacao\Comandos\Autenticacao\RecuperarSenha\LidarRecuperarSenha;
use App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware\Controller;
use DI\Container;
use Exception;

final class RecuperarController extends Controller
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

            $comando = new ComandoRecuperarSenha(
                email: $_POST['email'] ?? '',
            );

			$comando->executar();

            try {

                $lidarRecuperarSenha = $this->container->get(LidarRecuperarSenha::class);

                $lidarRecuperarSenha->lidar($comando);

            }catch (Exception $erro) {

                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');

                echo json_encode([
                    'message' => $erro->getMessage()
                ]);
                return;
            }

            header('Content-Type: application/json');
            header('HTTP/1.1 201');

            echo json_encode([
                'message' => 'Enviamos um e-mail com as instruções para recuperação de senha.'
            ]);
            return;

        }catch(Exception $erro){

			header('Content-Type: application/json');
			header('HTTP/1.1 400 Bad Request');
			echo json_encode([
				'message' => $erro->getMessage()
			]);
			return;
        }
    }
}

