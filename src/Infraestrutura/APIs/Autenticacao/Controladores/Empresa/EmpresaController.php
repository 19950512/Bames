<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Empresa;

use Exception;
use DI\Container;
use App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware\Controller;
use App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa\ComandoCadastrarEmpresa;
use App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa\ImplementacaoLidarCadastrarEmpresa;

final class EmpresaController extends Controller
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

			$comando = new ComandoCadastrarEmpresa(
				nomeFantasia: $_POST['nome_fantasia'] ?? '',
                numeroDocumento: $_POST['numero_documento'] ?? '',
                oab: $_POST['oab'] ?? '',
                responsavelNomeCompleto: $_POST['responsavel_nome_completo'] ?? '',
                responsavelEmail: $_POST['responsavel_email'] ?? '',
                responsavelSenha: $_POST['responsavel_senha'] ?? ''
			);

			$comando->executar();

			$lidarCadastrarEmpresa = $this->container->get(ImplementacaoLidarCadastrarEmpresa::class);

			$empresaCodigo = $lidarCadastrarEmpresa->lidar($comando);

            header('Content-Type: application/json');
            header('HTTP/1.1 201 Access Token');

            echo json_encode([
				'message' => 'Empresa cadastrada com sucesso',
                'empresa_codigo' => $empresaCodigo->get()
            ]);
			return;

		}catch (Exception $erro){
			header('Content-Type: application/json');
			header('HTTP/1.1 400 Bad Request');
			echo json_encode([
				'message' => $erro->getMessage()
			]);
			return;
		}
    }
}

