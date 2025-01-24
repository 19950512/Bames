<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Webhook\Controladores\Middlewares;

use DI\Container;

abstract class Controller
{

    public $method;

    public array $data = [];

    public function __construct(
        private Container $container
    ){

        $this->method = $_SERVER['REQUEST_METHOD'] ?? '';

        if(is_array($_POST) and count($_POST) == 0){
            $json = file_get_contents('php://input');
            $_POST = json_decode(json_decode(json_encode($json), true), true);
        }
    }

    public function response(array $data): void
    {

        header('Content-Type: application/json; charset=utf-8');
        header('X-Powered-By: Bames - Esse e meu jeito ninja de ser!');

        if(isset($data['statusCode']) and is_numeric($data['statusCode'])){
            header("HTTP/1.0 {$data['statusCode']}");
            unset($data['statusCode']);
        }

        echo json_encode($data);
        return;
    }
}
