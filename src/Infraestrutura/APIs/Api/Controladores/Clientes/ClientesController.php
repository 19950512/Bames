<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Clientes;

use App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoCliente\ComandoAtualizarInformacoesDoCliente;
use App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoCliente\LidarAtualizarInformacoesDoCliente;
use App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente\ComandoCadastrarNovoCliente;
use App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente\LidarCadastrarNovoCliente;
use App\Aplicacao\Comandos\Clientes\ConsultarInformacoesNaInternet\ComandoLidarConsultarInformacoesNaInternet;
use App\Aplicacao\Comandos\Clientes\ConsultarInformacoesNaInternet\LidarConsultarInformacoesNaInternet;
use App\Aplicacao\Comandos\Clientes\ConsultarProcessos\ComandoLidarConsultarProcessosDoCliente;
use App\Aplicacao\Comandos\Clientes\ConsultarProcessos\LidarConsultaProcessosDoCliente;
use App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo\ComandoGerarDocumentoApartirDoModelo;
use App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo\LidarGerarDocumentoApartirDoModelo;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Leituras\Clientes\LeituraClienteDetalhado;
use App\Aplicacao\Leituras\Clientes\LeituraClienteProcessos;
use App\Aplicacao\Leituras\Clientes\LeituraClientes;
use App\Aplicacao\Leituras\Clientes\LeituraClientesSubstituicoes;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use DI\Container;
use Exception;

class ClientesController extends Controller
{
    public function __construct(
        private Container $container
    ){

        parent::__construct(
            container: $this->container
        );
    }

    public function substituicoes(): void
    {
        try {

            $this->response($this->container->get(LeituraClientesSubstituicoes::class)->executar());

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function gerardocumento(): void
    {
        try {

            $comando = new ComandoGerarDocumentoApartirDoModelo(
                modeloID: $_GET['modelo'] ?? '',
                clienteID: $_GET['cliente'] ?? ''
            );

            $comando->executar();

            $linkParaDownload = $this->container->get(LidarGerarDocumentoApartirDoModelo::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Documento gerado com sucesso',
                'link' => $linkParaDownload->get()
            ]);

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function detalhes(): void
    {
        try {

            $clienteCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

            $this->response($this->container->get(LeituraClienteDetalhado::class)->executar(
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                clienteCodigo: $clienteCodigo
            ));

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

                $this->response($this->container->get(LeituraClientes::class)->executar());

            }catch (Exception $erro){

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'PUT'){
            try {

                $comando = new ComandoAtualizarInformacoesDoCliente(
                    codigoCliente: $_POST['id'] ?? '',
                    nomeCompleto: $_POST['nome'] ?? '',
                    email: $_POST['email'] ?? '',
                    telefone: $_POST['telefone'] ?? '',
                    documento: $_POST['documento'] ?? '',
                    dataNascimento: $_POST['dataNascimento'] ?? '',
                    endereco: $_POST['endereco'] ?? '',
                    enderecoNumero: $_POST['enderecoNumero'] ?? '',
                    enderecoComplemento: $_POST['enderecoComplemento'] ?? '',
                    enderecoBairro: $_POST['enderecoBairro'] ?? '',
                    enderecoCidade: $_POST['enderecoCidade'] ?? '',
                    enderecoEstado: $_POST['enderecoEstado'] ?? '',
                    enderecoCep: $_POST['enderecoCep'] ?? '',
                    nomeMae: $_POST['nomeMae'] ?? '',
                    cpfMae: $_POST['cpfMae'] ?? '',
                    sexo: $_POST['sexo'] ?? ''
                );

                $comando->executar();

                $this->container->get(LidarAtualizarInformacoesDoCliente::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Cliente atualizado com sucesso'
                ]);

            }catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }

        if($this->method == 'POST'){

            try {

                $comando = new ComandoCadastrarNovoCliente(
                    nomeCompleto: $_POST['name'] ?? '',
                    email: $_POST['email'] ?? '',
                    telefone: $_POST['telefone'] ?? '',
                    documento: $_POST['documento'] ?? '',
                    logradouro: $_POST['logradouro'] ?? '',
                    numero: $_POST['numero'] ?? '',
                    complemento: $_POST['complemento'] ?? '',
                    bairro: $_POST['bairro'] ?? '',
                    cidade: $_POST['cidade'] ?? '',
                    estado: $_POST['estado'] ?? '',
                    cep: $_POST['cep'] ?? '',
                    nomeMae: $_POST['nomeMae'] ?? '',
                    cpfMae: $_POST['cpfMae'] ?? '',
                    dataNascimento: $_POST['dataNascimento'] ?? '',
                    sexo: $_POST['sexo'] ?? '',
                    familiares: [],
                    nomePai: $_POST['nomePai'] ?? '',
                    cpfPai: $_POST['cpfPai'] ?? '',
                    rg: $_POST['rg'] ?? '',
                    pis: $_POST['pis'] ?? '',
                    carteiraTrabalho: $_POST['carteiraTrabalho'] ?? '',
                    telefones: [],
                    emails: [],
                    enderecos: []
                );

                $comando->executar();

                $this->container->get(LidarCadastrarNovoCliente::class)->lidar($comando);

                $this->response([
                    'statusCode' => 200,
                    'message' => 'Cliente cadastrado com sucesso'
                ]);

            }catch (Exception $erro) {

                $this->response([
                    'statusCode' => 400,
                    'message' => $erro->getMessage()
                ]);
            }
        }
    }

    public function consultarinformacoesnainternet(): void
    {
        try {

            $comando = new ComandoLidarConsultarInformacoesNaInternet(
                documento: $_POST['documento'] ?? '',
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get()
            );

            $comando->executar();

            $this->container->get(LidarConsultarInformacoesNaInternet::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Consulta realizada com sucesso'
            ]);

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function processos(): void
    {

        try {

            $clienteCodigo = explode('/', $_SERVER['REQUEST_URI'])[3] ?? '';

            $this->response($this->container->get(LeituraClienteProcessos::class)->executar($clienteCodigo));

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function consultarProcessos(): void
    {
         try {

            $comando = new ComandoLidarConsultarProcessosDoCliente(
                documento: $_POST['documento'] ?? '',
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get()
            );

            $comando->executar();

            $this->container->get(LidarConsultaProcessosDoCliente::class)->lidar($comando);

            $this->response([
                'statusCode' => 200,
                'message' => 'Os processos do cliente foram consultados com sucesso'
            ]);

        }catch (Exception $erro){

            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }
}