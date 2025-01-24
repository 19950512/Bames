<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Contasbancarias;

use App\Aplicacao\Comandos\ContaBancaria\AtualizarInformacoesContaBancaria\ComandoAtualizarInformacoesContaBancaria;
use App\Aplicacao\Comandos\ContaBancaria\AtualizarInformacoesContaBancaria\LidarAtualizarInformacoesContaBancaria;
use App\Aplicacao\Comandos\ContaBancaria\ConfiguraWebhook\ComandoConfiguraWebhook;
use App\Aplicacao\Comandos\ContaBancaria\VerificaConexaoComPlataformaAPIDeCobrancas\LidarVerificaConexaoComPlataformaAPIDeCobrancas;
use App\Aplicacao\Leituras\ContasBancarias\LeituraContaBancariaDetalhada;
use App\Aplicacao\Leituras\ContasBancarias\LeituraContasBancarias;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class ContasbancariasController extends Controller
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

        if($this->method == 'GET'){
            try {

                $this->response($this->container->get(LeituraContasBancarias::class)->executar(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get()
                ));

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'PUT'){

            try {

                $comando = new ComandoAtualizarInformacoesContaBancaria(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    codigoContaBancaria: (string) ($_POST['codigo'] ?? ''),
                    nomeContaBancaria:(string) ($_POST['nome'] ?? ''),
                    chaveAPIContaBancaria:(string) ($_POST['chaveAPI'] ?? ''),
                    clientIDContaBancaria:(string) ($_POST['clientID'] ?? ''),
                    ambiente: (string) ($_POST['ambiente'] ?? 'Sandbox'),
                );

                $comando->executar();

                $this->container->get(LidarAtualizarInformacoesContaBancaria::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'As informações da conta bancária foram atualizadas com sucesso'
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

	public function detalhes(): void
    {

        if($this->method == 'GET'){

            try {

                $contaBancariaCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

                $contaBancaria = $this->container->get(LeituraContaBancariaDetalhada::class)->executar(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    contaBancariaCodigo: $contaBancariaCodigo
                );

                $this->response($contaBancaria);

            }catch (Exception $erro) {

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

    public function verificaConexaoComPlataformaAPIDeCobrancas(): void
    {

        if($this->method == 'POST'){

            try {

                $comando = new ComandoConfiguraWebhook(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    codigoContaBancaria: (string) ($_POST['codigo'] ?? ''),
                );

                $comando->executar();

                $this->container->get(LidarVerificaConexaoComPlataformaAPIDeCobrancas::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Conexão com a plataforma de cobrança estabelecida com sucesso.'
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