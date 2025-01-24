<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Middlewares;

use App\Aplicacao\Compartilhado\Data\Data;
use DI\Container;

abstract class Controller extends Authorization
{

    public $method;

    public array $dados = [];
    public Data $data;

    public function __construct(
        private Container $container
    ){

        $this->data = $this->container->get(Data::class);

        $this->method = $_SERVER['REQUEST_METHOD'] ?? '';

        $json = file_get_contents('php://input');
        if(!empty($json)){
            $_POST = $json;
            if(json_validate($json)){
                $json = json_decode(json_encode($json), true);

                if(is_array($_POST)){
                    $_POST = array_merge($_POST, $json);
                }

                if(is_string($_POST)){
                    $_POST = json_decode($_POST, true);
                }

                $this->dados = is_string($_POST) ? json_decode($_POST, true) : $_POST;

                $_POST = $this->dados;

            }else{
                parse_str($json, $this->dados);
                $this->dados = json_decode(json_encode($this->dados), true);
                $_POST = $this->dados;
            }
        }

        parent::__construct(
            container: $this->container
        );
    }

    public function response(array $data)
    {

        header('Content-Type: application/json; charset=utf-8');
        header('X-Powered-By: Bames - Esse e meu jeito ninja de ser!');

        if(isset($data['statusCode']) and is_numeric($data['statusCode'])){
            header("HTTP/1.0 {$data['statusCode']}");
            unset($data['statusCode']);
        }

        echo json_encode($data);
        exit;
    }

    public function metodoNaoPermitido()
    {
        $this->response([
            'statusCode' => 405,
            'message' => 'Método não permitido'
        ]);
    }
}
