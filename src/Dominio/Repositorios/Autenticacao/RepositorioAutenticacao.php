<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Autenticacao;

use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaConta;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\EntradaFronteiraNovaEmpresa;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

interface RepositorioAutenticacao
{
    public function empresaJaFoiExecutadoPosCadastrar(string $empresaCodigo): bool;
    public function buscarContaPorTokenDeVerificacaodeEmail(string $tokenVerificacaoEmail): SaidaFronteiraBuscarContaPorCodigo;
    public function buscarContaPorCodigo(string $contaCodigo): SaidaFronteiraBuscarContaPorCodigo;
    public function posCadastrarEmpresaEfetuadaComSucesso(string $empresaCodigo): void;
    public function buscarTokenEmailVerificacao(string $empresaCodigo, string $usuarioCodigo): string;
    public function atualizarOMotivoParaNaoAtivarAConta(string $empresaCodigo, string $usuarioCodigo, string $mensagem): void;
    public function obterOMotivoDoBloqueioDaConta(string $empresaCodigo, string $usuarioCodigo): string;
    public function adicionarBonusEmpresaRecemCadastrada(string $empresaCodigo, float $valorCreditos): void;
    public function marcarEmailVerificado(string $usuarioCodigo): void;
    public function removerFCMTokenInvalido(string $businessId, string $FCMToken): void;
    public function oFCMTokenJaEstaCadastrado(string $entidadeEmpresarial, string $usuarioCodigo, string $FCMToken): bool;
    public function salvarNovoFCMToken(string $entidadeEmpresarial, string $usuarioCodigo, string $FCMToken): void;
    public function buscarContaPorEmail(string $email): SaidaFronteiraBuscarContaPorCodigo;
    public function buscarContaPorTokenRecuperacaoDeSenha(string $tokenRecuperarSenha): SaidaFronteiraBuscarContaPorCodigo;
    public function atualizarSenhaDoUsuarioSistema(string $contaUsuarioHASHSenha, string $contaUsuarioCodigo, string $empresaCodigo): void;
    public function jaExisteContaComEsseEmail(string $email): bool;
    public function jaExisteUmaContaComEstaOAB(string $oab): bool;
    public function contaExistePorEmailESenha(string $email, string $senha): bool;
    public function obterEmpresaPorCodigo(string $empresaCodigo): SaidaFronteiraEmpresa;
    public function empresaExistePorCodigo(string $empresaCodigo): bool;
    public function jaExisteEmpresaComEsseDocumento(string $email): bool;
    public function cadastrarNovaEmpresa(EntradaFronteiraNovaEmpresa $params): void;
    public function novaConta(EntradaFronteiraNovaConta $params): void;
    public function buscarToken(string $token, string $contaCodigo, string $empresaCodigo): string;
    public function novoToken(string $token, string $contaCodigo, string $empresaCodigo): void;
    public function salvaTokenParaRecuperacaoDeSenha(string $tokenRecuperarSenha, string $empresaCodigo, string $contaCodigo, string $contaEmail): void;

}