<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Contrato;

use Exception;
use DI\Container;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Comandos\Contrato\NovoContrato\ComandoNovoContrato;
use App\Aplicacao\Comandos\Contrato\NovoContrato\LidarNovoContrato;
use App\Infraestrutura\APIs\Api\Controladores\Middlewares\Controller;

class ContratoController extends Controller
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
        match($this->method){
            'POST' => $this->novoContrato(),
            default => $this->metodoNaoPermitido()
        };
    }

    private function novoContrato(): void
    {
        try {

            $comando = new ComandoNovoContrato(
                empresaCodigo: $this->container->get(EntidadeEmpresarial::class)->codigo->get(),
                usuarioCodigo: $this->container->get(EntidadeUsuarioLogado::class)->codigo->get(),
                contaBancariaCodigo: (string) ($_POST['contaBancariaCodigo'] ?? ''),
                clienteCodigo: (string) ($_POST['clienteCodigo'] ?? ''),
                dataInicio: (string) ($_POST['dataInicio'] ?? ''),
                meioPagamento: (string) ($_POST['meioPagamento'] ?? ''),
                diaVencimento: (int) ($_POST['diaVencimento'] ?? 0),
                diaEmissaoCobranca: (int) ($_POST['diaEmissaoCobranca'] ?? 0),
                horarioEmissaoCobranca: (string) ($_POST['horarioEmissaoCobranca'] ?? ''),
                parcela: (int) ($_POST['parcela'] ?? 0),
                valor: (float) ($_POST['valor'] ?? 0),
                juros: (float) ($_POST['juros'] ?? 0),
                multa: (float) ($_POST['multa'] ?? 0),
                descontoAntecipacao: (float) ($_POST['descontoAntecipacao'] ?? 0),
                recorrente: (bool) ($_POST['recorrente'] ?? false),
                tipoJuros: (string) ($_POST['tipoJuros'] ?? ''),
                tipoMulta: (string) ($_POST['tipoMulta'] ?? ''),
                tipoDescontoAntecipacao: (string) ($_POST['tipoDescontoAntecipacao'] ?? '')
            );

            $comando->executar();
        
        }catch(Exception $erro){
        
            $this->response([
                'statusCode' => 400,
                'message' => $erro->getMessage()
            ]);
        }

        try {

            $this->container->get(LidarNovoContrato::class)->lidar($comando);

            $this->response([
                'statusCode' => 201,
                'message' => 'Contrato cadastrado com sucesso',
            ]);
            
        }catch (Exception $erro){

            $this->response([
                'statusCode' => 422,
                'message' => $erro->getMessage()
            ]);
        }
    }
}