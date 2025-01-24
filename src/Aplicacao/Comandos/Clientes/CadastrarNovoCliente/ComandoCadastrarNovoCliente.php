<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\Telefone;
use Exception;
use Override;

readonly final class ComandoCadastrarNovoCliente implements Comando
{

    private string $nomeCompletoPronto;
    private string $emailPronto;
    private string $telefonePronto;
    private string $documentoPronto;
    private string $logradouroPronto;
    private string $numeroPronto;
    private string $complementoPronto;
    private string $bairroPronto;
    private string $cidadePronto;
    private string $estadoPronto;
    private string $cepPronto;
    private string $nomeMaePronto;
    private string $cpfMaePronto;
    private string $dataNascimentoPronto;
    private string $sexoPronto;
    private array $familiaresPronto;
    private string $nomePaiPronto;
    private string $cpfPaiPronto;
    private string $rgPronto;
    private string $pisPronto;
    private string $carteiraTrabalhoPronto;
    private array $telefonesPronto;
    private array $emailsPronto;
    private array $enderecosPronto;

    public function __construct(
        private string $nomeCompleto,
        private string $email,
        private string $telefone,
        private string $documento,
        private string $logradouro,
        private string $numero,
        private string $complemento,
        private string $bairro,
        private string $cidade,
        private string $estado,
        private string $cep,
        private string $nomeMae,
        private string $cpfMae,
        private string $dataNascimento,
        private string $sexo,
        private array $familiares,
        private string $nomePai,
        private string $cpfPai,
        private string $rg,
        private string $pis,
        private string $carteiraTrabalho,
        private array $telefones,
        private array $emails,
        private array $enderecos,
    ){}

	#[Override] public function executar(): void
    {
        try {
            $nomeCompleto = new NomeCompleto($this->nomeCompleto);
        }catch (Exception $erro){
            throw new Exception("O nome informado está inválido. {$erro->getMessage()}");
        }

        try {
            $email = new Email($this->email);
        }catch (Exception $erro){
            throw new Exception("O email informado está inválido. {$erro->getMessage()}");
        }

        try {
            $telefone = new Telefone($this->telefone);
        }catch (Exception $erro){
            throw new Exception("O telefone informado está inválido. {$erro->getMessage()}");
        }

        try {
            $documento = new DocumentoDeIdentificacao($this->documento);
        }catch(Exception $erro){
            throw new Exception("O documento informado está inválido. {$erro->getMessage()}");
        }

        $this->logradouroPronto = $this->logradouro;

        $this->numeroPronto = $this->numero;

        $this->complementoPronto = $this->complemento;

        $this->bairroPronto = $this->bairro;

        $this->cidadePronto = $this->cidade;

        $this->estadoPronto = $this->estado;

        $this->cepPronto = $this->cep;

        $this->nomeMaePronto = $this->nomeMae;

        $this->cpfMaePronto = $this->cpfMae;

        $this->dataNascimentoPronto = $this->dataNascimento;

        $this->sexoPronto = $this->sexo;

        $this->familiaresPronto = $this->familiares;

        $this->nomePaiPronto = $this->nomePai;

        $this->cpfPaiPronto = $this->cpfPai;

        $this->rgPronto = $this->rg;

        $this->pisPronto = $this->pis;

        $this->carteiraTrabalhoPronto = $this->carteiraTrabalho;

        $this->telefonesPronto = $this->telefones;

        $this->emailsPronto = $this->emails;

        $this->enderecosPronto = $this->enderecos;

        $this->nomeCompletoPronto = $nomeCompleto->get();
        $this->emailPronto = $email->get();
        $this->telefonePronto = $telefone->get();
        $this->documentoPronto = $documento->get();
    }

    #[Override] public function getPayload(): array
    {
        return [
            'nomeCompleto' => $this->nomeCompleto,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'documento' => $this->documento,
            'logradouro' => $this->logradouro,
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'estado' => $this->estado,
            'cep' => $this->cep,
            'nomeMae' => $this->nomeMae,
            'cpfMae' => $this->cpfMae,
            'dataNascimento' => $this->dataNascimento,
            'sexo' => $this->sexo,
            'familiares' => $this->familiares,
            'nomePai' => $this->nomePai,
            'cpfPai' => $this->cpfPai,
            'rg' => $this->rg,
            'pis' => $this->pis,
            'carteiraTrabalho' => $this->carteiraTrabalho,
            'telefones' => $this->telefones,
            'emails' => $this->emails,
            'enderecos' => $this->enderecos,
        ];
    }

    public function obterNomeCompleto(): string
    {
        return $this->nomeCompletoPronto;
    }

    public function obterEmail(): string
    {
        return $this->emailPronto;
    }

    public function obterTelefone(): string
    {
        return $this->telefonePronto;
    }

    public function obterDocumento(): string
    {
        return $this->documentoPronto;
    }

    public function obterLogradouro(): string
    {
        return $this->logradouroPronto;
    }

    public function obterNumero(): string
    {
        return $this->numeroPronto;
    }

    public function obterComplemento(): string
    {
        return $this->complementoPronto;
    }

    public function obterBairro(): string
    {
        return $this->bairroPronto;
    }

    public function obterCidade(): string
    {
        return $this->cidadePronto;
    }

    public function obterEstado(): string
    {
        return $this->estadoPronto;
    }

    public function obterCep(): string
    {
        return $this->cepPronto;
    }

    public function obterNomeMae(): string
    {
        return $this->nomeMaePronto;
    }

    public function obterCpfMae(): string
    {
        return $this->cpfMaePronto;
    }

    public function obterDataNascimento(): string
    {
        return $this->dataNascimentoPronto;
    }

    public function obterSexo(): string
    {
        return $this->sexoPronto;
    }

    public function obterFamiliares(): array
    {
        return $this->familiaresPronto;
    }

    public function obterNomePai(): string
    {
        return $this->nomePaiPronto;
    }

    public function obterCpfPai(): string
    {
        return $this->cpfPaiPronto;
    }

    public function obterRg(): string
    {
        return $this->rgPronto;
    }

    public function obterPis(): string
    {
        return $this->pisPronto;
    }

    public function obterCarteiraTrabalho(): string
    {
        return $this->carteiraTrabalhoPronto;
    }

    public function obterTelefones(): array
    {
        return $this->telefonesPronto;
    }

    public function obterEmails(): array
    {
        return $this->emailsPronto;
    }

    public function obterEnderecos(): array
    {
        return $this->enderecosPronto;
    }
}