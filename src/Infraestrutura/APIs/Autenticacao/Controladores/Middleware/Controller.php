<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Autenticacao\Controladores\Middleware;

use DI\Container;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;

abstract class Controller
{

    private Cache $cache;

    public function __construct(
        protected Container $container
    ){

        $this->cache = $this->container->get(Cache::class);

        if(is_array($_POST) and count($_POST) == 0){
            $json = file_get_contents('php://input');
            $_POST = json_decode(json_decode(json_encode($json), true), true);
        }

        $this->rateLimit();
    }

    private function rateLimit(): void
    {

        $ambiente = $this->container->get(Ambiente::class);

        if($ambiente->get('APP_DEBUG')){
            // SE O AMBIENTE FOR DEBUG / TESTE, NÃO APLICAR RATE LIMIT
            return;
        }

        $usuarioIP = "rate_limit_api_auth_{$_SERVER['REMOTE_ADDR']}";

        $periodoEmSegundos = 10;
        $quantidadeChamadasPermitida = 5;
        $tempoDePunicaoPorRetentativaEmSegundos = 5;

        if(!$this->cache->exist($usuarioIP)){
            $this->cache->set($usuarioIP, '1', $periodoEmSegundos);
        }else{
            $chamadasUsuarioTotal = (int) $this->cache->get($usuarioIP) + 1;
            $tempoRestante = $this->cache->ttl($usuarioIP);
            $this->cache->set($usuarioIP, (string) $chamadasUsuarioTotal, $tempoRestante);

            if($chamadasUsuarioTotal > $quantidadeChamadasPermitida){
                $tempoRestante += $tempoDePunicaoPorRetentativaEmSegundos;
                $this->cache->set($usuarioIP, (string) $chamadasUsuarioTotal, $tempoRestante);
                $this->handleRateLimitExceeded($tempoRestante);
            }
        }
    }

    private function handleRateLimitExceeded(int $periodoEmSegundos): void
    {
        header('Content-Type: application/json');
        header('HTTP/1.1 429 Too Many Requests');

        echo json_encode([
            'message' => 'Muitas requisições, aguarde um momento',
            'try_after' => $periodoEmSegundos . ' seconds'
        ]);
        return;
    }

    abstract public function index(): void;
}