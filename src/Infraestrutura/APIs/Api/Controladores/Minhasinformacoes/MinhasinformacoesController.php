<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Minhasinformacoes;

use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;

class MinhasinformacoesController extends Controller
{

    public function __construct(
        private Container $container
    ){

        parent::__construct(
            container: $this->container
        );
    }

	public function index()
    {

        /*
        $this->response([
            'statusCode' => 402,
            'message' => 'Sua assinatura está vencida ou não foi paga. Por favor, atualize seu pagamento para continuar o acesso.'
        ]);
        */

        if($this->method == 'GET'){
            $usuarioLogado = $this->container->get(EntidadeUsuarioLogado::class);
            $this->response($usuarioLogado->obterInformacoes());
        }

        $this->response([
            'statusCode' => 405,
            'message' => 'Método não permitido'
        ]);
    }
}