<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Email;

use App\Dominio\Repositorios\Email\Fronteiras\FronteiraEntradaSalvarEmailEnviado;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use PDO;

readonly class ImplementacaoRepositorioEmail implements RepositorioEmail
{
    public function __construct(
        private PDO $pdo,
    ){}

    public function salvarEmailEnviado(FronteiraEntradaSalvarEmailEnviado $params): void
    {
        $sql = "INSERT INTO emails (email_codigo_track, business_id, destinatario_nome, destinatario_email, email_assunto, email_mensagem, email_situacao, momento) VALUES (:email_codigo_track, :business_id, :destinatario_nome, :destinatario_email, :email_assunto, :email_mensagem, :email_situacao, :momento)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'email_codigo_track' => $params->emailCodigo,
            'business_id' => $params->empresaID,
            'destinatario_nome' => $params->destinatarioNome,
            'destinatario_email' => $params->destinatarioEmail,
            'email_assunto' => $params->assunto,
            'email_mensagem' => $params->mensagem,
            'email_situacao' => $params->situacao,
            'momento' => date('Y-m-d H:i:s'),
        ]);
    }
}