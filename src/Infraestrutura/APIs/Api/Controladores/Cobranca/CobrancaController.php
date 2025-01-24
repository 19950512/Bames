<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Cobranca;

use App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma\ComandoBaixarBoletoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BaixarBoletoNaPlataforma\LidarBaixarBoletoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\ConsultarBoleto\ComandoConsultarBoleto;
use App\Aplicacao\Comandos\Cobranca\Boleto\ConsultarBoleto\LidarConsultarboleto;
use App\Aplicacao\Comandos\Cobranca\EmissaoDeCobranca\ComandoEmissaoDeCobranca;
use App\Aplicacao\Comandos\Cobranca\EmissaoDeCobranca\LidarEmissaoDeCobranca;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Leituras\Cobrancas\LeituraCobrancaDetalhada;
use App\Aplicacao\Leituras\Cobrancas\LeituraTodasAsCobrancas;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class CobrancaController extends Controller
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

                $this->response($this->container->get(LeituraTodasAsCobrancas::class)->executar(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get()
                ));

            }catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'POST'){
            try {

                $comando = new ComandoEmissaoDeCobranca(
                    clienteCodigo: (string)($_POST['clienteCodigo'] ?? ''),
                    descricao: (string)($_POST['descricao'] ?? ''),
                    dataVencimento: (string)($_POST['dataVencimento'] ?? ''),
                    contaBancariaCodigo: (string) ($_POST['contaBancariaCodigo'] ?? ''),
                    meioDePagamento: (string) ($_POST['meioDePagamento'] ?? ''),
                    composicaoDaCobranca: (array) ($_POST['composicaoDaCobranca'] ?? []),
                    valorJuros: (float) ($_POST['juros'] ?? 0),
                    valorMulta: (float) ($_POST['multa'] ?? 0),
                    parcelas: (int) ($_POST['parcelas'] ?? 1)
                );

                $comando->executar();

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }

            try {

                $this->container->get(LidarEmissaoDeCobranca::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Cobrança realizada com sucesso'
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

                $cobrancaCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

                $cobranca = $this->container->get(LeituraCobrancaDetalhada::class)->executar(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    cobrancaCodigo: $cobrancaCodigo
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

    public function consultarboleto(): void
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

    public function baixarboleto(): void
    {

        if($this->method == 'GET'){

            try {

                $boletoCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

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