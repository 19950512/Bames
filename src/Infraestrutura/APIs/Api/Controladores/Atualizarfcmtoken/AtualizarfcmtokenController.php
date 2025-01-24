<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Atualizarfcmtoken;

use App\Aplicacao\Comandos\Autenticacao\AtualizarFCMToken\ComandoAtualizarFCMToken;
use App\Aplicacao\Comandos\Autenticacao\AtualizarFCMToken\LidarAtualizarFCMToken;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class AtualizarfcmtokenController extends Controller
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

        if($this->method == 'POST'){

            try {
                $comando = new ComandoAtualizarFCMToken(
                    FCMToken: $_POST['fcmToken'] ?? '',
                );

                $comando->executar();

                $this->container->get(LidarAtualizarFCMToken::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'FCM Token atualizado com sucesso.'
                ]);

            } catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        $this->response([
            'statusCode' => 400,
            'message' => 'Método não permitido.'
        ]);
    }
}
