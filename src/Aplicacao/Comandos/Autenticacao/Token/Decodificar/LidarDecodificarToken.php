<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Token\Decodificar;

use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Token;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Aplicacao\Comandos\Autenticacao\Token\Decodificar\ComandoDecodificarToken;

final readonly class LidarDecodificarToken implements Lidar
{

    public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
        private Token $token
    ){}

    #[Override] public function lidar(Comando $comando): object
    {
       
        if (!is_a($comando, ComandoDecodificarToken::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $tokenJWT = $comando->obterToken();

        try {

            return $this->token->decode($tokenJWT);

        }catch (Exception $erro){

            throw new Exception("Token não encontrado!");
        }
    }
}