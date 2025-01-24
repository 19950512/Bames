<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\Eventos\AtualizarEvento;

use Exception;
use Override;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

final readonly class ComandoAtualizarEvento implements Comando
{

    private string $eventoCodigoPronto;
    private string $tituloPronto;
    private string $descricaoPronto;
    private string $horarioEventoInicioPronto;
    private string $horarioEventoFimPronto;
    private int $recorrenciaPronto;
    private bool $diaTodoPronto;

    public function __construct(
        public string $eventoCodigo,
        public string $titulo,
        public string $descricao,
        public bool $diaTodo,
        public int $recorrencia,
        public string $horarioEventoInicio,
        public string $horarioEventoFim,
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

        if($this->recorrencia < 0){
            throw new Exception('A recorrência do evento não pode ser negativa.');
        }

        try {

            $eventoCodigo = new IdentificacaoUnica($this->eventoCodigo);
        }catch(Exception $erro){
            throw new Exception('O código do evento informado está inválido.');
        }

        $this->eventoCodigoPronto = $eventoCodigo->get();
        $this->tituloPronto = $this->titulo;
        $this->horarioEventoInicioPronto = $this->horarioEventoInicio;
        $this->horarioEventoFimPronto = $this->horarioEventoFim;
        $this->recorrenciaPronto = $this->recorrencia;
        $this->diaTodoPronto = $this->diaTodo;
        $this->descricaoPronto = $this->descricao;
    }

    
	#[Override] public function getPayload(): array
    {
        return [
            'eventoCodigo' => $this->eventoCodigoPronto,
            'titulo' => $this->tituloPronto,
            'descricao' => $this->descricaoPronto,
            'diaTodo' => $this->diaTodoPronto,
            'recorrencia' => $this->recorrenciaPronto,
            'horarioEventoInicio' => $this->horarioEventoInicioPronto,
            'horarioEventoFim' => $this->horarioEventoFimPronto,
        ];
    }

    public function obterEventoCodigoPronto(): string
    {
        return $this->eventoCodigoPronto;
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
}