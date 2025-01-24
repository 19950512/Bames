<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Webhook;

use App\Dominio\Repositorios\Webhook\Fronteiras\EntradaFronteiraSalvarWebhook;
use App\Dominio\Repositorios\Webhook\RepositorioWebhook;
use Override;
use PDO;

final class ImplementacaoRepositorioWebhook implements RepositorioWebhook
{

    public function __construct(
        private PDO $pdo
    ){}

    #[Override] public function verificarWebhookRecebido(string $eventID): bool
    {
        $sql = 'SELECT COUNT(webhook_event_id_plataforma) FROM recebimento_webhook WHERE webhook_event_id_plataforma = :webhook_event_id_plataforma';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['webhook_event_id_plataforma' => $eventID]);
        $resultado = $stmt->fetchColumn();
        return $resultado > 0;
    }

    #[Override] public function salvarWebhook(EntradaFronteiraSalvarWebhook $parametros): void
    {
        $sql = 'INSERT INTO recebimento_webhook (webhook_event_id_plataforma, webhook_header, webhook_payload, webhook_ip, webhook_method, webhook_uri, webhook_momento, webhook_parceiro, webhook_user_agent) VALUES (:webhook_event_id_plataforma, :webhook_header, :webhook_payload, :webhook_ip, :webhook_method, :webhook_uri, :webhook_momento, :webhook_parceiro, :webhook_user_agent)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'webhook_event_id_plataforma' => $parametros->eventID,
            'webhook_header' => $parametros->headers,
            'webhook_payload' => $parametros->payload,
            'webhook_ip' => $parametros->ip,
            'webhook_method' => $parametros->metodo,
            'webhook_uri' => $parametros->uri,
            'webhook_momento' => $parametros->momento,
            'webhook_parceiro' => $parametros->parceiro,
            'webhook_user_agent' => $parametros->userAgent
        ]);
    }
}
