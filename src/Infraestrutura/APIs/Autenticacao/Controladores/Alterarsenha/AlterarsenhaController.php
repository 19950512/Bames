<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Alterarsenha;

use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Autenticacao\AlterarSenha\LidarAlterarSenha;
use App\Aplicacao\Comandos\Autenticacao\AlterarSenha\ComandoAlterarSenha;
use App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware\Controller;

final class AlterarsenhaController extends Controller
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

            $comando = new ComandoAlterarSenha(
                token: $_POST['token'] ?? '',
                senha: $_POST['senha'] ?? '',
                confirmacaoSenha: $_POST['confirmacao_senha'] ?? '',
            );

			$comando->executar();

            try {

                $lidarAlterarSenha = $this->container->get(LidarAlterarSenha::class);

                $lidarAlterarSenha->lidar($comando);

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
                'message' => 'Senha alterada com sucesso'
            ]);
            return;

        }catch(Exception $erro){

			header('Content-Type: application/json');
			header('HTTP/1.1 422 Bad Request');

            $mensagemErro = $erro->getMessage();
            if(str_contains($mensagemErro, 'código informado está inválido')) {
                $mensagemErro = "O código informado está inválido.";
            }

			echo json_encode([
				'message' => $mensagemErro
			]);
			return;
        }
    }
}

