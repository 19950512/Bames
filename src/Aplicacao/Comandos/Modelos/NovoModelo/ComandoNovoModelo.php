<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\NovoModelo;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\Arquivos;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;
use Override;

final readonly class ComandoNovoModelo implements Comando
{

    private string $nomeModeloPronto;
    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;
    private Arquivos $arquivosPronto;

    public function __construct(
        private string $nomeModelo,
        private string $empresaCodigo,
        private string $usuarioCodigo,
        private Arquivos $arquivos
    ){}

	#[Override] public function executar(): void
    {

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch(Exception $erro){
            throw new Exception("O código do usuário informado está inválido. {$erro->getMessage()}");
        }



        try {
            $nomeModelo = new Apelido($this->nomeModelo);
        }catch (Exception $erro){
            throw new Exception("O nome do modelo precisa ser informado adequadamente.");
        }

        if(count($this->arquivos->get()) <= 0){
            throw new Exception("É necessário informar ao menos um arquivo.");
        }

        if(count($this->arquivos->get()) > 1){
            throw new Exception("É necessário informar apenas um arquivo.");
        }

        $this->arquivosPronto = $this->arquivos;
        $this->nomeModeloPronto = $nomeModelo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'empresaCodigo' => $this->empresaCodigo,
            'nomeModelo' => $this->nomeModelo,
            'usuarioCodigo' => $this->usuarioCodigo,
            'arquivos' => $this->arquivos->get()
        ];
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterUsuarioCodigoPronto(): string
    {
        return $this->usuarioCodigoPronto;
    }

    public function obterNomeModelo(): string
    {
        return $this->nomeModeloPronto;
    }

    public function obterArquivos(): Arquivos
    {
        return $this->arquivosPronto;
    }
}