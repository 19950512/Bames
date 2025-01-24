<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Empresa;

use App\Aplicacao\Leituras\Empresa\LeituraEmpresaSubstituicoes;
use Exception;
use DI\Container;
use App\Aplicacao\Leituras\Empresa\LeituraEmpresa;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Leituras\Empresa\Usuarios\LeituraTodosUsuarios;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use App\Aplicacao\Comandos\Empresa\LidarDeletarTudoRelacionadoAEmpresa;
use App\Aplicacao\Comandos\Empresa\ComandoDeletarTudoRelacionadoAEmpresa;
use App\Aplicacao\Comandos\Empresa\Usuarios\CadastrarUsuario\LidarCadastrarUsuario;
use App\Aplicacao\Comandos\Empresa\Usuarios\CadastrarUsuario\ComandoCadastrarUsuario;

class EmpresaController extends Controller
{

    public function __construct(
        private Container $container
    ){

        parent::__construct(
            container: $this->container
        );

        if($this->method == 'DELETE'){

            try {

                $empresaCodigoASerDeletada = explode('/', $_SERVER['REQUEST_URI'])[2];
                
                $comandoDeletarTudoRelacionadoAEmpresa = new ComandoDeletarTudoRelacionadoAEmpresa(
                    empresaCodigo: $empresaCodigoASerDeletada
                );
            
                $comandoDeletarTudoRelacionadoAEmpresa->executar();

            }catch (Exception $erro){
                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }

            try {

                $lidarDeletarTudoRelacionadoAEmpresa = $this->container->get(LidarDeletarTudoRelacionadoAEmpresa::class);
    
                $lidarDeletarTudoRelacionadoAEmpresa->lidar($comandoDeletarTudoRelacionadoAEmpresa);

                $this->response([
                    'statusCode' => 201,
                    'message' => 'Empresa deletada com sucesso'
                ]);

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }
    }

    public function substituicoes(): void
    {
        try {

            $this->response($this->container->get(LeituraEmpresaSubstituicoes::class)->executar());

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }
	public function index()
    {
        if($this->method == 'GET'){
            $leituraEmpresa = $this->container->get(LeituraEmpresa::class);
            $this->response($leituraEmpresa->executar());
        }

        $this->response([
            'statusCode' => 405,
            'message' => 'Método não permitido'
        ]);
    }

	public function usuarios()
	{

        if($this->method == 'GET'){

            $leituraTodosUsuarios = $this->container->get(LeituraTodosUsuarios::class);
    
            $this->response($leituraTodosUsuarios->executar());
        }

        if($this->method == 'POST'){

            try {

                $comandoCadastrarUsuario = new ComandoCadastrarUsuario(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    nomeCompleto: $_POST['nome'] ?? '',
                    email: $_POST['email'] ?? '',
                    oab: $_POST['oab'] ?? ''
                );
    
                $comandoCadastrarUsuario->executar();

                $lidarCadastroUsuario = $this->container->get(LidarCadastrarUsuario::class);

                $lidarCadastroUsuario->lidar($comandoCadastrarUsuario);

                $this->response([
                    'statusCode' => 201,
                    'message' => 'Usuário cadastrado com sucesso'
                ]);
            
            }catch (Exception $erro){
            
                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        $this->response([
            'statusCode' => 405,
            'message' => 'Método não permitido'
        ]);
	}

}

