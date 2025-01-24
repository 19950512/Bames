<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Verificaremail;

use App\Aplicacao\Comandos\Autenticacao\VerificarEmail\ComandoVerificarEmail;
use App\Aplicacao\Comandos\Autenticacao\VerificarEmail\LidarVerificarEmail;
use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Autenticacao\AlterarSenha\LidarAlterarSenha;
use App\Aplicacao\Comandos\Autenticacao\AlterarSenha\ComandoAlterarSenha;
use App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware\Controller;

final class VerificaremailController extends Controller
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

            $comando = new ComandoVerificarEmail(
                token: $_POST['token'] ?? '',
            );

			$comando->executar();

            try {

                $this->container->get(LidarVerificarEmail::class)->lidar($comando);

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
                'message' => 'E-mail verificado com sucesso e apartir de agora, você poderá efetuar o login'
            ]);
            return;

        }catch(Exception $erro){

			header('Content-Type: application/json');
			header('HTTP/1.1 422 Bad Request');

			echo json_encode([
				'message' => $erro->getMessage()
			]);
			return;
        }
    }
}

