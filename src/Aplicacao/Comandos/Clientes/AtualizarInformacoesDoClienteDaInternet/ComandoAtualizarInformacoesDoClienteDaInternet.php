<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoClienteDaInternet;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\Telefone;
use Exception;
use Override;

readonly final class ComandoAtualizarInformacoesDoClienteDaInternet implements Comando
{

    private string $codigoClientePronto;
    private string $nomeCompletoPronto;
    private string $emailPronto;
    private string $telefonePronto;
    private string $documentoPronto;
    private string $dataNascimentoPronto;
    private string $enderecoPronto;
    private string $enderecoNumeroPronto;
    private string $enderecoComplementoPronto;
    private string $enderecoBairroPronto;
    private string $enderecoCidadePronto;
    private string $enderecoEstadoPronto;
    private string $enderecoCepPronto;
    private string $nomeMaePronto;
    private string $cpfMaePronto;
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
        private string $codigoCliente,
        private string $nomeCompleto,
        private string $email,
        private string $telefone,
        private string $documento,
        private string $dataNascimento,
        private string $endereco,
        private string $enderecoNumero,
        private string $enderecoComplemento,
        private string $enderecoBairro,
        private string $enderecoCidade,
        private string $enderecoEstado,
        private string $enderecoCep,
        private string $nomeMae,
        private string $cpfMae,
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

    #[Override] public function getPayload(): array
    {
        return [
            'codigoCliente' => $this->codigoCliente,
            'nomeCompleto' => $this->nomeCompleto,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'documento' => $this->documento,
            'dataNascimento' => $this->dataNascimento,
            'endereco' => $this->endereco,
            'enderecoNumero' => $this->enderecoNumero,
            'enderecoComplemento' => $this->enderecoComplemento,
            'enderecoBairro' => $this->enderecoBairro,
            'enderecoCidade' => $this->enderecoCidade,
            'enderecoEstado' => $this->enderecoEstado,
            'enderecoCep' => $this->enderecoCep,
            'nomeMae' => $this->nomeMae,
            'cpfMae' => $this->cpfMae,
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

	#[Override] public function executar(): void
    {

        try {
            $codigoCliente = new IdentificacaoUnica($this->codigoCliente);
        }catch (Exception $erro){
            throw new Exception("O código do cliente informado está inválido. {$erro->getMessage()}");
        }

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

        $this->codigoClientePronto = $codigoCliente->get();
        $this->nomeCompletoPronto = $nomeCompleto->get();
        $this->emailPronto = $email->get();
        $this->telefonePronto = $telefone->get();
        $this->documentoPronto = $documento->get();

        $this->dataNascimentoPronto = $this->dataNascimento;
        $this->enderecoPronto = $this->endereco;
        $this->enderecoNumeroPronto = $this->enderecoNumero;
        $this->enderecoComplementoPronto = $this->enderecoComplemento;
        $this->enderecoBairroPronto = $this->enderecoBairro;
        $this->enderecoCidadePronto = $this->enderecoCidade;
        $this->enderecoEstadoPronto = $this->enderecoEstado;
        $this->enderecoCepPronto = $this->enderecoCep;
        $this->nomeMaePronto = $this->nomeMae;
        $this->cpfMaePronto = $this->cpfMae;
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
    }

    public function obterFamiliares(): array
    {
        return $this->familiaresPronto;
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

    public function obterCodigoCliente(): string
    {
        return $this->codigoClientePronto;
    }

    public function obterDataNascimento(): string
    {
        return $this->dataNascimentoPronto;
    }

    public function obterEndereco(): string
    {
        return $this->enderecoPronto;
    }

    public function obterEnderecoNumero(): string
    {
        return $this->enderecoNumeroPronto;
    }

    public function obterEnderecoComplemento(): string
    {
        return $this->enderecoComplementoPronto;
    }

    public function obterBairro(): string
    {
        return $this->enderecoBairroPronto;
    }

    public function obterCidade(): string
    {
        return $this->enderecoCidadePronto;
    }

    public function obterEstado(): string
    {
        return $this->enderecoEstadoPronto;
    }

    public function obterCep(): string
    {
        return $this->enderecoCepPronto;
    }

    public function obterNomeDaMae(): string
    {
        return $this->nomeMaePronto;
    }

    public function obterCpfDaMae(): string
    {
        return $this->cpfMaePronto;
    }

    public function obterSexo(): string
    {
        return $this->sexoPronto;
    }
}