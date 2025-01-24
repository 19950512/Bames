<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Modelos;

use Exception;
use DI\Container;
use App\Dominio\ObjetoValor\Arquivos;
use App\Aplicacao\Compartilhado\Docx\Docx;
use App\Aplicacao\Leituras\Modelos\LeituraModelos;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Comandos\Modelos\NovoModelo\LidarNovoModelo;
use App\Aplicacao\Comandos\Modelos\NovoModelo\ComandoNovoModelo;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Comandos\Modelos\ExcluirModelo\LidarExcluirModelo;
use App\Aplicacao\Comandos\Modelos\PreviewModelo\LidarPreviewModelo;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use App\Aplicacao\Comandos\Modelos\ExcluirModelo\ComandoExcluirModelo;
use App\Aplicacao\Comandos\Modelos\PreviewModelo\ComandoPreviewModelo;
use App\Aplicacao\Comandos\Modelos\DownloadModeloDocx\LidarDownloadModeloDocx;
use App\Aplicacao\Comandos\Modelos\DownloadModeloDocx\ComandoDownloadModeloDocx;

class ModelosController extends Controller
{
    public function __construct(
        private Container $container
    ){

        parent::__construct(
            container: $this->container
        );

        if($this->method == 'DELETE') {

            try {

                $modeloCodigo = explode('/', $_SERVER['REQUEST_URI'])[2];

                $comando = new ComandoExcluirModelo(
                    codigoModelo: $modeloCodigo,
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get()
                );

                $comando->executar();

                $this->container->get(LidarExcluirModelo::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Modelo de documento excluído com sucesso'
                ]);

            } catch (Exception $erro) {
                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }
    }

    public function utils(): void
    {

        try {

            $this->response($this->container->get(Docx::class)->substituicaoUtil());

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }

    }

    public function downloadmodelodocx(): void
    {
        try {

            $modeloCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

            $comando = new ComandoDownloadModeloDocx(
                modeloCodigo: $modeloCodigo,
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
            );

            $comando->executar();

            $linkParaDownload = $this->container->get(LidarDownloadModeloDocx::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Modelo de documento encontrado',
                'link' => $linkParaDownload->get()
            ]);

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function preview(): void
    {

        try {

            $modeloCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

            $comando = new ComandoPreviewModelo(
                modeloCodigo: $modeloCodigo,
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
            );

            $comando->executar();

            $linkParaDownload = $this->container->get(LidarPreviewModelo::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Modelo de documento encontrado',
                'link' => $linkParaDownload->get()
            ]);

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

	public function index(): void
    {

        if($this->method == 'GET'){
            try {

                $this->response($this->container->get(LeituraModelos::class)->executar());

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'PUT'){

            try {

                $arquivos = new Arquivos();
                if(isset($_FILES['files'])){
                    $arquivos = Arquivos::processarArquivosVindoDoUploadFiles($_FILES['files']);
                }

                $comando = new ComandoExcluirModelo(
                    codigoModelo: $_POST['codigo'] ?? '',
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                );

                $comando->executar();

                $this->container->get(LidarExcluirModelo::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Modelo de documento atualizado com sucesso'
                ]);

            }catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'POST'){

            $arquivos = new Arquivos();
            if(isset($_FILES['files'])){
				$arquivos = Arquivos::processarArquivosVindoDoUploadFiles($_FILES['files']);
            }

            try {

                $comando = new ComandoNovoModelo(
                    nomeModelo: $_POST['nome'] ?? '',
                    empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                    usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get(),
                    arquivos: $arquivos,
                );

                $comando->executar();

                $this->container->get(LidarNovoModelo::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Modelo de documento cadastrado com sucesso'
                ]);

            }catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }
    }
}