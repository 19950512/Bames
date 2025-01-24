<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Processos;

use Exception;
use DI\Container;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Leituras\Processos\LeituraTodosOsProcessos;
use App\Aplicacao\Leituras\Processos\LeituraProcessoDetalhado;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use App\Aplicacao\Comandos\Processos\MonitorarProcesso\LidarMonitorarProcesso;
use App\Aplicacao\Comandos\Processos\MonitorarProcesso\ComandoMonitorarProcesso;
use App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes\LidarConsultarMovimentacoes;
use App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes\ComandoLidarConsultarMovimentacoes;

class ProcessosController extends Controller
{

    private LeituraTodosOsProcessos $leituraProcessos;

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

                $this->response($this->container->get(LeituraTodosOsProcessos::class)->executar());

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        $this->response([
            'statusCode' => 400,
            'message' => 'Método não permitido'
        ]);
    }

    public function detalhes(): void
    {

        try {

            $processoCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

            $this->response($this->container->get(LeituraProcessoDetalhado::class)->executar(
                processoCodigo: $processoCodigo
            ));

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function movimentacoes(): void
    {

        if($this->method == 'GET'){

            try {

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Movimentações'
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
            'message' => 'Método não permitido'
        ]);
    }
    
    public function monitorar(): void
    {

        if($this->method == 'POST'){

            try {

                $comando = new ComandoMonitorarProcesso(
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    CNJ: (string) ($_POST['cnj'] ?? '')
                );

                $comando->executar();

                $this->container->get(LidarMonitorarProcesso::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Este processo agora está sendo monitorado, quando houver novas movimentações você será notificado.'
                ]);

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 422,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        $this->response([
            'statusCode' => 400,
            'message' => 'Método não permitido'
        ]);

    }

    public function consultarMovimentacoes(): void
    {

        if($this->method == 'POST'){

            $comandoConsultarMovimentacoes = new ComandoLidarConsultarMovimentacoes(
                CNJ: $_POST['cnj'] ?? ''
            );

            try {

                $comandoConsultarMovimentacoes->executar();

            }catch(Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }

            try {

                $this->container->get(LidarConsultarMovimentacoes::class)->lidar($comandoConsultarMovimentacoes);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Movimentações consultadas com sucesso',
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
            'message' => 'Método não permitido'
        ]);
    }
}