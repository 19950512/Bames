<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Webhook\Controladores\Escavador;


use App\Aplicacao\Comandos\Webhook\ComandoReceberWebhook;
use App\Aplicacao\Comandos\Webhook\Enums\Parceiro;
use App\Aplicacao\Comandos\Webhook\LidarReceberWebhook;
use App\Infraestrutura\APIs\Webhook\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class EscavadorController extends Controller
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

        try {

            $comando = new ComandoReceberWebhook(
                payload: $_POST,
                headers: getallheaders(),
                uri: $_SERVER['REQUEST_URI'],
                metodo: $this->method,
                ip: $_SERVER['REMOTE_ADDR'],
                userAgent: $_SERVER['HTTP_USER_AGENT'],
                parceiro: Parceiro::Escavador
            );

            $comando->executar();

            if($this->container->get(LidarReceberWebhook::class)->lidar($comando)){
                http_response_code(200);
                echo json_encode(['status' => 'ok']);
                return;
            }

        }catch(Exception $e){

            /*
            Poderia ser feito um tratamento de exceção para retornar um erro 401 mas não, vou deixar o erro 500 mesmo em todos os casos para não dar dicas de segurança
            if($e->getMessage() === 'Ops, não autorizado.'){
                http_response_code(401);
                echo json_encode(['message' => $e->getMessage()]);
                exit();
            }
            */

            http_response_code(500);
            echo json_encode(['message' => $e->getMessage()]);
            return;
        }
    }
}