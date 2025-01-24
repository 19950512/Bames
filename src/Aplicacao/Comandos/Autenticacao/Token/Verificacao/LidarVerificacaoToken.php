<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Token\Verificacao;

use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Aplicacao\Comandos\Autenticacao\Token\Verificacao\ComandoVerificacaoToken;

final readonly class LidarVerificacaoToken implements Lidar
{

    public function __construct(
		private RepositorioAutenticacao $repositorioAutenticacaoComando
    ){}

    #[Override] public function lidar(Comando $comando): EntidadeEmpresarial
    {
       
        if (!is_a($comando, ComandoVerificacaoToken::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $token = $comando->obterToken();
        $contaCodigo = $comando->obterContaCodigo();
        $empresaCodigo = $comando->obterEmpresaCodigo();

        try {

            $tokenSalvo = $this->repositorioAutenticacaoComando->buscarToken(
                token: $token,
                contaCodigo: $contaCodigo,
                empresaCodigo: $empresaCodigo
            );

            if($tokenSalvo !== $token){
                throw new Exception("Token não encontrado.");
            }

            $entidadeEmpresarialData = $this->repositorioAutenticacaoComando->obterEmpresaPorCodigo($empresaCodigo);

            return EntidadeEmpresarial::instanciarEntidadeEmpresarial($entidadeEmpresarialData);

        }catch (Exception $erro){

            throw new Exception("Token não encontrado! - {$erro->getMessage()}");
        }
    }
}