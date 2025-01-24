<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Boleto;


use App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma\ComandoBaixarBoletoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma\LidarBaixarBoletoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoLiquidarManualmente\ComandoBoletoLiquidarManualmente;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoLiquidarManualmente\LidarBoletoLiquidarManualmente;
use App\Aplicacao\Comandos\Cobranca\Boleto\ConsultarBoleto\ComandoConsultarBoleto;
use App\Aplicacao\Comandos\Cobranca\Boleto\ConsultarBoleto\LidarConsultarboleto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Leituras\Boleto\LeituraBoletoDetalhado;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class BoletoController extends Controller
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
            'statusCode' => 405,
            'message' => 'Método não permitido'
        ]);
    }

    public function detalhes(): void
    {

        if($this->method == 'GET'){

            try {

                $boletoCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

                $cobranca = $this->container->get(LeituraBoletoDetalhado::class)->executar(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    boletoCodigo: $boletoCodigo
                );

                $this->response($cobranca);

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

    public function consultarnaplataforma(): void
    {

        if($this->method == 'GET'){

            try {

                $boletoCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

                $comando = new ComandoConsultarBoleto(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    boletoCodigo: $boletoCodigo
                );
                $comando->executar();

                $cobrancaSituacao = $this->container->get(LidarConsultarBoleto::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'O boleto foi consultado com sucesso.',
                    'status' => $cobrancaSituacao->value
                ]);

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

    public function liquidar(): void
    {

        if($this->method == 'POST'){

            try {

                $comando = new ComandoBoletoLiquidarManualmente(
                    empresaCodigo: (string) $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    usuarioCodigo: (string) $this->container->get(EntidadeUsuarioLogado::class)->codigo->get(),
                    boletoCodigo: (string) $_POST['codigo'],
                    dataPagamento: (string) $_POST['data'],
                    valorRecebido: (float) $_POST['valor']
                );

                $comando->executar();

                if($this->container->get(LidarBoletoLiquidarManualmente::class)->lidar($comando)){

                    $this->response([
                        'statusCode' => 200,
                        'message' => 'O boleto foi liquidado com sucesso.',
                    ]);
                }

                $this->response([
                    'statusCode' => 400,
                    'message' => 'Ops, algo deu errado ao liquidar o boleto.'
                ]);

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

    public function cancelar(): void
    {

        if($this->method == 'POST'){

            try {

                $boletoCodigo = $_POST['codigo'];

                $comando = new ComandoBaixarBoletoNaPlataforma(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get(),
                    boletoCodigo: $boletoCodigo
                );
                $comando->executar();

                $this->container->get(LidarBaixarBoletoNaPlataforma::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'O boleto foi baixado com sucesso.',
                ]);

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
}