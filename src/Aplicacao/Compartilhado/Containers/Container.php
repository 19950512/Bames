<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Containers;

setlocale(LC_TIME, 'pt_BR.UTF-8');
date_default_timezone_set('America/Sao_Paulo');

use Exception;
use DI\ContainerBuilder;

$pathAutoloader = __DIR__ . '/../../../../vendor/autoload.php';

if(!is_file($pathAutoloader)){
    throw new Exception('Instale as dependências do projeto - Composer install');
}

require_once $pathAutoloader;

final class Container
{

    private static self|null $instance = null;

	private \Di\Container|null $container = null;

    private function __construct()
    {
        // Construtor privado para evitar instanciação direta
    }

    public static function getInstance(): self
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get(array | null $args)
    {

		if(is_a($this->container, \Di\Container::class)){
			return $this->container;
		}

        $EVENT_BUS_HOST = $args['EVENT_BUS_HOST'] ?? false;
        $DB_HOST = $args['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'postgres';
        $DB_PORT = $args['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? '5432';

        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions([
            'EVENT_BUS_HOST' => $EVENT_BUS_HOST,
            'DB_HOST' => $DB_HOST,
            'DB_PORT' => $DB_PORT,
        ]);

		try {

			$containerApplication = $this->loader_container(__DIR__ . '/ContainerAplicacao.php');

			$containerEmpresa = $this->loader_container(__DIR__ . '/ContainerEmpresa.php');

            $containerConsultaProcessoPorOAB = $this->loader_container(__DIR__ . '/ContainerConsultaProcessoPorOAB.php');

            $containerProcessos = $this->loader_container(__DIR__ . '/ContainerProcessos.php');

            $containerClientes = $this->loader_container(__DIR__ . '/ContainerClientes.php');

            $containerConsultaInformacoesNaInternet = $this->loader_container(__DIR__ . '/ContainerConsultarInformacoesNaInternet.php');

            $containerWebhook = $this->loader_container(__DIR__ . '/ContainerWebhook.php');

            $containerConsultaProcessosDoCliente = $this->loader_container(__DIR__ . '/ContainerConsultaProcessosDoCliente.php');

            $containerModelos = $this->loader_container(__DIR__ . '/ContainerModelos.php');

            $containerContaBancaria = $this->loader_container(__DIR__ . '/ContainerContaBancaria.php');

            $containerCobranca = $this->loader_container(__DIR__ . '/ContainerCobranca.php');

            $containerBoleto = $this->loader_container(__DIR__ . '/ContainerBoleto.php');

            $containerPlataformasAPICobranca = $this->loader_container(__DIR__ . '/ContainerPlataformasAPICobranca.php');

            $containerPlanoDeContas = $this->loader_container(__DIR__ . '/ContainerPlanoDeContas.php');

            $containerCaixa = $this->loader_container(__DIR__ . '/ContainerCaixa.php');

	        $containerBuilder->addDefinitions([

	            // Application
	            ...$containerApplication,

                // Empresa
                ...$containerEmpresa,

                // Consulta de Processo por OAB
                ...$containerConsultaProcessoPorOAB,

                // Processos
                ...$containerProcessos,

                // Clientes
                ...$containerClientes,

                // Consultar Informações na Internet
                ...$containerConsultaInformacoesNaInternet,

                // Webhook
                ...$containerWebhook,

                // Consulta de Processos do Cliente
                ...$containerConsultaProcessosDoCliente,

                // Modelos
                ...$containerModelos,

                // Conta Bancária
                ...$containerContaBancaria,

                // Cobrança
                ...$containerCobranca,

                // Boleto
                ...$containerBoleto,

                // Plataformas de API de Cobrança
                ...$containerPlataformasAPICobranca,

                // Plano de Contas
                ...$containerPlanoDeContas,

                // Caixa
                ...$containerCaixa,
	        ]);

			$this->container = $containerBuilder->build();

			return $this->container;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function loader_container(string $pathContainer): array
    {
        if(!is_file($pathContainer)){
            throw new Exception('Arquivo de configuração do container não encontrado.');
        }

        return require_once $pathContainer;
    }
}