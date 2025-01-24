<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Agenda;

use Exception;
use DI\Container;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Leituras\Agenda\LeituraMeusCompromissos;
use App\Aplicacao\Leituras\Agenda\LeituraCompromissoPorCodigo;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\LidarNovoEvento;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\ComandoNovoEvento;
use App\Aplicacao\Comandos\Agenda\Eventos\AtualizarEvento\LidarAtualizarEvento;
use App\Aplicacao\Comandos\Agenda\Eventos\AtualizarEvento\ComandoAtualizarEvento;

class AgendaController extends Controller
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
        $this->metodoNaoPermitido();
    }

    private function novoEvento(): void
    {
        try {
                
            $comandoCadastrarEvento = new ComandoNovoEvento(
                titulo: $_POST['titulo'] ?? '',
                descricao: $_POST['descricao'] ?? '',
                diaTodo: (bool) ($_POST['diaTodo'] ?? false),
                recorrencia: (int) ($_POST['recorrencia'] ?? 0),
                horarioEventoInicio: $_POST['horarioEventoInicio'] ?? '',
                horarioEventoFim: $_POST['horarioEventoFim'] ?? '',
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get()
            );

            $comandoCadastrarEvento->executar();
        
        }catch(Exception $erro){
        
            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }

        try {
            $lidarCadastrarEvento = $this->container->get(LidarNovoEvento::class);

            $eventoID = $lidarCadastrarEvento->lidar($comandoCadastrarEvento);

            $this->response([
                'statusCode' => 201,
                'message' => 'Evento cadastrado com sucesso',
                'eventoID' => $eventoID,
            ]);
            
        }catch (Exception $erro){

            $this->response([
                'statusCode' => 422,
                'message' => $erro->getMessage()
            ]);
        }
    }

    private function atualizarEvento(): void
    {

        try {

            $comando = new ComandoAtualizarEvento(
                eventoCodigo: $_POST['eventoID'] ?? '',
                titulo: $_POST['titulo'] ?? '',
                descricao: $_POST['descricao'] ?? '',
                diaTodo: (bool) ($_POST['diaTodo'] ?? false),
                recorrencia: (int) ($_POST['recorrencia'] ?? 0),
                horarioEventoInicio: $_POST['horarioEventoInicio'] ?? '',
                horarioEventoFim: $_POST['horarioEventoFim'] ?? ''
            );

            $comando->executar();
        
        }catch(Exception $erro){
        
            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }

        try {
            $lidar = $this->container->get(LidarAtualizarEvento::class);

            $lidar->lidar($comando);

            $this->response([
                'statusCode' => 201,
                'message' => 'Evento foi atualizado com sucesso',
            ]);
            
        }catch (Exception $erro){

            $this->response([
                'statusCode' => 422,
                'message' => $erro->getMessage()
            ]);
        }
    }

	public function evento(): void
    {
        match($this->method){
            'POST' => $this->novoEvento(),
            'PUT' => $this->atualizarEvento(),
            default => $this->metodoNaoPermitido()
        };
    }

    public function meuscompromissos(): void
    {
        try {
            $this->response($this->container->get(LeituraMeusCompromissos::class)->executar());
        }catch (Exception $erro){
            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }

    public function compromisso(): void
    {
        try {

            $compromissoCodigo = explode('/', $_SERVER['REQUEST_URI'])[3];

            $this->response($this->container->get(LeituraCompromissoPorCodigo::class)->executar(
                compromissoCodigo: $compromissoCodigo
            ));
        }catch (Exception $erro){
            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }
    }
}