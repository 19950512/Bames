<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Empresa\Usuarios\CadastrarUsuario;

use Exception;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\ObjetoValor\Email;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoCadastrarUsuario implements Comando
{
    private string $emailPronto;
    private string $oabPronto;
    private string $nomeCompletoPronto;
    private string $empresaID;

    public function __construct(
        private string $empresaCodigo,
        private string $nomeCompleto,
        private string $email,
        private string $oab,
    ){}

    public function executar(): void
    {

        if(empty($this->nomeCompleto)){
            throw new Exception('O nome completo precisa ser informado adequadamente.');
        }

        if(empty($this->email)){
            throw new Exception('O e-mail precisa ser informado adequadamente.');
        }

        try {
            $email = new Email($this->email);
        }catch (Exception $erro){
            throw new Exception("O e-mail informado está inválido. {$erro->getMessage()}");
        }

        try {
            $nomeCompleto = new NomeCompleto($this->nomeCompleto);
        }catch(Exception $erro){
            throw new Exception("O nome completo informado está inválido. {$erro->getMessage()}");
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch(Exception $erro){
            throw new Exception("O código da empresa informado está inválido. {$erro->getMessage()}");
        }

        try {
            $oab = new OAB($this->oab);
        }catch(Exception $erro){
            throw new Exception("OAB informada está inválida. {$erro->getMessage()}");
        }
		
        $this->emailPronto = $email->get();
        $this->empresaID = $empresaCodigo->get();
        $this->nomeCompletoPronto = $nomeCompleto->get();
        $this->oabPronto = $oab->get();
    }

    public function getPayload(): array
    {
        return [
            'empresa_codigo' => $this->empresaCodigo,
            'nome_completo' => $this->nomeCompletoPronto,
            'email' => $this->emailPronto,
            'oab' => $this->oabPronto,
        ];
    }

    public function obterEmail(): string
    {
        return $this->emailPronto;
    }

    public function obterNomeCompleto(): string
    {
        return $this->nomeCompletoPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaID;
    }

    public function obterOAB(): string
    {
        return $this->oabPronto;
    }
}