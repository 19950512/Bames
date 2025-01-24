<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Api\Controladores\Middlewares;

use App\Dominio\ObjetoValor\AccessToken;
use Exception;
use DI\Container;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Comandos\Autenticacao\Token\Decodificar\LidarDecodificarToken;
use App\Aplicacao\Comandos\Autenticacao\Token\Verificacao\LidarVerificacaoToken;
use App\Aplicacao\Comandos\Autenticacao\Token\Decodificar\ComandoDecodificarToken;
use App\Aplicacao\Comandos\Autenticacao\Token\Verificacao\ComandoVerificacaoToken;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;
use App\Aplicacao\Comandos\Autenticacao\InformacoesContaPorCodigo\LidarInformacoesContaPorCodigo;
use App\Aplicacao\Comandos\Autenticacao\InformacoesContaPorCodigo\ComandoInformacoesContaPorCodigo;

class Authorization
{

    private Ambiente $env;

    public function __construct(
        private Container $container
    ){

        $headers = apache_request_headers();

        $headers = array_change_key_case($headers, CASE_LOWER);

        $authorization = explode(' ', $headers['authorization'] ?? '');

        $this->env = $this->container->get(Ambiente::class);
            
        $token = $authorization[1] ?? '';
        if(empty($token)){
            $this->response(['message' => 'Token inválido', 'statusCode' => 401]);
            return;
        }

        try {

            $comandoDecodificarToken = new ComandoDecodificarToken(
                token: $token
            );

            $comandoDecodificarToken->executar();

            $lidarDecodificarToken = $this->container->get(LidarDecodificarToken::class);

            $tokenDecoded = $lidarDecodificarToken->lidar($comandoDecodificarToken);

        }catch(Exception $erro){

            $this->response(['message' => 'Token inválido', 'statusCode' => 401]);
            return;
        }

        if(!property_exists($tokenDecoded, 'id')){
            $this->response(['message' => 'Token inválido!', 'statusCode' => 401]);
            return;
        }

        try {
            
            $comando = new ComandoInformacoesContaPorCodigo(
                contaCodigo: $tokenDecoded->id
            );
            
            $comando->executar();

            $lidar = $this->container->get(LidarInformacoesContaPorCodigo::class);

            $usuarioSistema = $lidar->lidar($comando);
        
        }catch(Exception $erro){
            
            $this->response(['message' => 'Token inválido!!', 'statusCode' => 401]);
            return;
        }

        if($usuarioSistema->codigo->get() !== $tokenDecoded->id){
            $this->response(['message' => 'Token inválido!!!', 'statusCode' => 401]);
            return;
        }

        if(empty($usuarioSistema->empresaCodigo->get())){
            $this->response(['message' => 'Token inválido!!!!', 'statusCode' => 401]);
            return;
        }

        try {

            $comandoVerificarToken = new ComandoVerificacaoToken(
                token: $token,
                contaCodigo: $usuarioSistema->codigo->get(),
                empresaCodigo: $usuarioSistema->empresaCodigo->get()
            );

            $comandoVerificarToken->executar();

            $lidarVerificaToken = $this->container->get(LidarVerificacaoToken::class);

            $entidadeEmpresarial = $lidarVerificaToken->lidar($comandoVerificarToken);

            if($entidadeEmpresarial->acessoNaoAutorizado){

                $mensagem = 'Desculpe, mas você não tem permissão para acessar esta área.';
                $statusCode = 401;
                if($entidadeEmpresarial->acessoNaoAutorizadoMotivo == 'assinatura_vencida'){
                    $mensagem = 'Sua assinatura está vencida ou não foi paga. Por favor, atualize seu pagamento para continuar o acesso.';
                    $statusCode = 402;
                }

                $this->response([
                    'statusCode' => $statusCode,
                    'message' => $mensagem,
                ]);
                return;
            }

            $entidadeUsuarioLogadoData = new SaidaFronteiraBuscarContaPorCodigo(
                empresaCodigo: $usuarioSistema->empresaCodigo->get(),
                contaCodigo: $usuarioSistema->codigo->get(),
                nomeCompleto: $usuarioSistema->nomeCompleto->get(),
                email: $usuarioSistema->email->get(),
                documento: $usuarioSistema->documento->get(),
                hashSenha: $usuarioSistema->hashSenha,
                oab: $usuarioSistema->oab->get(),
                diretorGeral: $usuarioSistema->diretorGeral,
                emailVerificado: $usuarioSistema->emailVerificado,
            );

            $entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($entidadeUsuarioLogadoData);

            if(!$entidadeUsuarioLogado->emailVerificado){
                $this->response(['message' => 'O e-mail ainda não foi verificado.', 'statusCode' => 401]);
            }

            $this->container->set(EntidadeEmpresarial::class, $entidadeEmpresarial);
            $this->container->set(EntidadeUsuarioLogado::class, $entidadeUsuarioLogado);

            $this->container->set(AccessToken::class, new AccessToken(token: $token));

        }catch(Exception $erro){

            $this->response(['message' => 'Token inválido!!!! - '.$erro->getMessage(), 'statusCode' => 401]);
            return;
        }
    }

    public function response(array $data){

        header('Content-Type: application/json; charset=utf-8');
        header('X-Powered-By: Bames');

        if(isset($data['statusCode']) and is_numeric($data['statusCode'])){
            header("HTTP/1.0 {$data['statusCode']}");
            unset($data['statusCode']);
        }

        echo json_encode($data);
        exit;
    }
}