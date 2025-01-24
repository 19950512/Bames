<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\VerificarEmail;

use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;

final readonly class LidarVerificarEmail implements Lidar
{

	public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoVerificarEmail::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $token = $comando->obterToken();

        try {

            $contaData = $this->repositorioAutenticacaoComando->buscarContaPorTokenDeVerificacaodeEmail(
                tokenVerificacaoEmail: $token,
            );

            $usuarioSistema = UsuarioSistema::build($contaData);

        }catch (Exception $erro){

            if(str_contains($erro->getMessage(), 'A conta não existe na base de dados com esse')){
                throw new Exception("Não foi possível encontrar a conta com o token de e-mail informado.");
            }

            throw new Exception("Token de verificação expirado.");
        }

        if($usuarioSistema->emailVerificado){
            throw new Exception('O e-mail já está verificado.');
        }

        $this->repositorioAutenticacaoComando->marcarEmailVerificado(
            usuarioCodigo: $usuarioSistema->codigo->get()
        );

        return null;
    }
}