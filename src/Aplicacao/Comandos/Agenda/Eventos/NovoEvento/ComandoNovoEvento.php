<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento;

use Override;
use Exception;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoNovoEvento implements Comando
{

    private string $tituloPronto;
    private string $descricaoPronto;
    private string $horarioEventoInicioPronto;
    private string $horarioEventoFimPronto;
    private int $recorrenciaPronto;
    private bool $diaTodoPronto;
    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;
    private bool $notificarPorEmailPronto;

    public function __construct(
        public string $titulo,
        public string $descricao,
        public bool $diaTodo,
        public int $recorrencia,
        public string $horarioEventoInicio,
        public string $horarioEventoFim,
        private string $empresaCodigo,
        private string $usuarioCodigo,
        private bool $notificarPorEmail = true
    ){}
	
	#[Override] public function executar(): void
    {

        if(strlen($this->titulo) < 5){
            throw new Exception('O título do evento deve ter no mínimo 5 caracteres.');
        }

        if($this->diaTodo === false){
            if($this->horarioEventoInicio === ''){
                throw new Exception('O horário de início do evento é obrigatório.');
            }
            if($this->horarioEventoFim === ''){
                throw new Exception('O horário de fim do evento é obrigatório.');
            }

            if($this->horarioEventoInicio >= $this->horarioEventoFim){
                throw new Exception('O horário de início do evento não pode ser maior ou igual ao horário de fim.');
            }
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        if(empty($this->usuarioCodigo)){
            throw new Exception('O código do usuário precisa ser informado adequadamente.');
        }

        if($this->recorrencia < 0){
            throw new Exception('A recorrência do evento não pode ser negativa.');
        }


        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da empresa precisa ser informado adequadamente.");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch (Exception $erro){
            throw new Exception("O código do usuário precisa ser informado adequadamente.");
        }

        $this->tituloPronto = $this->titulo;
        $this->horarioEventoInicioPronto = $this->horarioEventoInicio;
        $this->horarioEventoFimPronto = $this->horarioEventoFim;
        $this->recorrenciaPronto = $this->recorrencia;
        $this->diaTodoPronto = $this->diaTodo;
        $this->descricaoPronto = $this->descricao;
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
        $this->notificarPorEmailPronto = $this->notificarPorEmail;
    }

    
	#[Override] public function getPayload(): array
    {
        return [
            'titulo' => $this->tituloPronto,
            'descricao' => $this->descricaoPronto,
            'diaTodo' => $this->diaTodoPronto,
            'recorrencia' => $this->recorrenciaPronto,
            'horarioEventoInicio' => $this->horarioEventoInicioPronto,
            'horarioEventoFim' => $this->horarioEventoFimPronto,
            'empresa_codigo' => $this->empresaCodigo,
            'usuario_codigo' => $this->usuarioCodigo,
            'notificarPorEmail' => $this->notificarPorEmail,
        ];
    }

    public function obterNotificarPorEmail(): bool
    {
        return $this->notificarPorEmailPronto;
    }

    public function obterTituloPronto(): string
    {
        return $this->tituloPronto;
    }

    public function obterHorarioEventoInicioPronto(): string
    {
        return $this->horarioEventoInicioPronto;
    }

    public function obterHorarioEventoFimPronto(): string
    {
        return $this->horarioEventoFimPronto;
    }

    public function obterDescricaoPronto(): string
    {
        return $this->descricaoPronto;
    }

    public function obterRecorrenciaPronto(): int
    {
        return $this->recorrenciaPronto;
    }

    public function obterDiaTodoPronto(): bool
    {
        return $this->diaTodoPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioCodigoPronto;
    }
}