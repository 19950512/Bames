<?php

declare(strict_types=1);

namespace App\Dominio\Entidades;

use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraBuscarUsuarioPorCodigo;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

final class UsuarioSistema
{

    public array $cargos = [];

    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        readonly public IdentificacaoUnica $empresaCodigo,
        public NomeCompleto $nomeCompleto,
        readonly public Email $email,
        readonly public DocumentoDeIdentificacao $documento,
        readonly public OAB $oab,
        public string $hashSenha,
        public bool $diretorGeral = false,
        public bool $emailVerificado = false,
        public string $tokenParaRecuperarSenha = '',
    ){}

    public static function build(SaidaFronteiraBuscarContaPorCodigo | SaidaFronteiraBuscarUsuarioPorCodigo $contaData): UsuarioSistema
    {
        $usuarioSistema = new UsuarioSistema(
            codigo: new IdentificacaoUnica($contaData->contaCodigo),
            empresaCodigo: new IdentificacaoUnica($contaData->empresaCodigo),
            nomeCompleto: new NomeCompleto($contaData->nomeCompleto),
            email: new Email($contaData->email),
            documento: new DocumentoDeIdentificacao(
                documentoNumero: $contaData->documento
            ),
            oab: new OAB($contaData->oab),
            hashSenha: $contaData->hashSenha,
            diretorGeral: $contaData->diretorGeral,
            emailVerificado: $contaData->emailVerificado,
            tokenParaRecuperarSenha: $contaData->tokenParaRecuperarSenha,
        );

        return $usuarioSistema;
    }

    public function gerarNovaHashDaSenha(string $novaSenha): void
    {
        $this->hashSenha = password_hash($novaSenha, PASSWORD_ARGON2I);
    }

    public function adicionarCargo(Cargo $cargo): void
    {
        $this->cargos[] = $cargo;
    }

    public function possuiCargo(Cargo $cargo): bool
    {
        foreach ($this->cargos as $cargoDoUsuario) {
            if (is_a($cargoDoUsuario, Cargo::class)) {
                if ($cargoDoUsuario->codigo->get() == $cargo->codigo->get()) {
                    return true;
                }
            }
        }

        return false;
    }
}
