<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Notificacao\EnviarNotificacao;

use App\Aplicacao\Comandos\Comando;
use Exception;
use Override;

final readonly class ComandoEnviarNotificacao implements Comando
{

    private string $tituloPronto;
    private string $mensagemPronta;
    private string $fcmTokenPronto;

    public function __construct(
        private string $titulo,
        private string $mensagem,
        private string $fcmToken,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->titulo)){
            throw new Exception("O título da notificação não pode ser vazio.");
        }

        if(empty($this->mensagem)){
            throw new Exception("A mensagem da notificação não pode ser vazia.");
        }

        if(empty($this->fcmToken)){
            throw new Exception("O FCM Token não pode ser vazio.");
        }

        $this->tituloPronto = $this->titulo;
        $this->mensagemPronta = $this->mensagem;
        $this->fcmTokenPronto = $this->fcmToken;
    }

    #[Override] public function getPayload(): array
    {
        return [
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'fcmToken' => $this->fcmToken,
        ];
    }

    public function obterTitulo(): string
    {
        return $this->tituloPronto;
    }

    public function obterMensagem(): string
    {
        return $this->mensagemPronta;
    }

    public function obterFCMToken(): string
    {
        return $this->fcmTokenPronto;
    }
}