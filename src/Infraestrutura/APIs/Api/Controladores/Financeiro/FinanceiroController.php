<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Financeiro;

use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\ComandoLancarMovimentacaoNoCaixa;
use App\Aplicacao\Comandos\Financeiro\LancarMovimentacaoNoCaixa\LidarLancarMovimentacaoNoCaixa;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Leituras\Financeiro\LeituraTodasAsMovimntacoesDaConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class FinanceiroController extends Controller
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
        $this->response([
            'statusCode' => 200,
            'message' => 'API Financeiro'
        ]);
    }

    public function movimentacao(): void
    {
        if($this->method == 'POST'){
            try {
                $comando = new ComandoLancarMovimentacaoNoCaixa(
                    valor: (float) $_POST['valor'] ?? 0,
                    descricao: (string) $_POST['descricao'] ?? '',
                    planoDeContaCodigo: (int) $_POST['planoDeContaCodigo'] ?? '',
                    dataMovimentacao: (string) $_POST['dataMovimentacao'] ?? '',
                    contaBancariaCodigo: (string) $_POST['contaBancariaCodigo'] ?? '',
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get()
                );

                $comando->executar();

            }catch (Exception $erro){
                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }

            try {

                $this->container->get(LidarLancarMovimentacaoNoCaixa::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Movimentação lançada com sucesso.'
                ]);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Movimentação lançada com sucesso.'
                ]);

            }catch (Exception $erro){
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

	public function movimentacoes(): void
    {

        if($this->method == 'GET'){

            try {

                // URI= /financeiro/movimentacoes/$contaBancariaCodigo

                $contaBancariaCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

                $this->response($this->container->get(LeituraTodasAsMovimntacoesDaConta::class)->executar($contaBancariaCodigo));

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 4,
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