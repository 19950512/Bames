<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\AtualizarModelo;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\Arquivos;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use Exception;

final readonly class ComandoAtualizarModelo implements Comando
{

    private string $codigoModeloPronto;
    private string $nomeModeloPronto;
    private string $empresaCodigoPronto;
    private Arquivos $arquivosPronto;

    public function __construct(
        private string $codigoModelo,
        private string $nomeModelo,
        private string $empresaCodigo,
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
            $codigoModelo = new IdentificacaoUnica($this->codigoModelo);
        }catch(Exception $erro){
            throw new Exception("O código do modelo informado está inválido. {$erro->getMessage()}");
        }

        try {
            $nomeModelo = new Apelido($this->nomeModelo);
        }catch (Exception $erro){
            throw new Exception("O nome do modelo precisa ser informado adequadamente?.");
        }
        /*
        if(count($this->arquivos->get()) <= 0){
            throw new Exception("É necessário informar ao menos um arquivo.");
        }*/

        if(count($this->arquivos->get()) > 1){
            throw new Exception("É necessário informar apenas um arquivo.");
        }

        $this->arquivosPronto = $this->arquivos;
        $this->codigoModeloPronto = $codigoModelo->get();
        $this->nomeModeloPronto = $nomeModelo->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'codigoModelo' => $this->codigoModelo,
            'empresaCodigo' => $this->empresaCodigo,
            'nomeModelo' => $this->nomeModelo,
            'arquivos' => $this->arquivos,
        ];
    }

    public function obterCodigoModelo(): string
    {
        return $this->codigoModeloPronto;
    }

    public function obterArquivos(): Arquivos
    {
        return $this->arquivosPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterNomeModelo(): string
    {
        return $this->nomeModeloPronto;
    }
}