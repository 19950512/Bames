<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Webhook\Controladores\Asaas;

use App\Aplicacao\Comandos\Webhook\ComandoReceberWebhook;
use App\Aplicacao\Comandos\Webhook\Enums\Parceiro;
use App\Aplicacao\Comandos\Webhook\LidarReceberWebhook;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Infraestrutura\APIs\Webhook\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class AsaasController extends Controller
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

            $headers = getallheaders();
            if(!is_array($headers)){
                $headers = [];
            }

            $comando = new ComandoReceberWebhook(
                payload: $_POST ?? $this->data,
                headers: $headers,
                uri: $_SERVER['REQUEST_URI'],
                metodo: $this->method,
                ip: $_SERVER['REMOTE_ADDR'],
                userAgent: $_SERVER['HTTP_USER_AGENT'],
                parceiro: Parceiro::Asaas
            );

            $comando->executar();

            $this->container->get(LidarReceberWebhook::class)->lidar($comando);

        }catch(Exception $e){

            $this->container->get(Discord::class)->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Controlador Webhook - Asaas - Erro: {$e->getMessage()} - URI: {$_SERVER['REQUEST_URI']}"
            );

        } finally {

            http_response_code(200);
            echo json_encode(['message' => 'ok']);
            return;
        }
    }
}