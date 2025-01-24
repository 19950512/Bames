<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Request;

use PDO;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

class ImplementacaoRepositorioRequest implements RepositorioRequest
{
    public function __construct(
        private PDO $pdo
    ){}

    public function salvarEventosDoRequest(EntradaFronteiraSalvarEventosDoRequest $eventosDoRequest): void
    {

        $sql = "INSERT INTO requests (jwt, comando_payload, usuario_id, comando, business_id, request_codigo, momento, total_eventos) VALUES (:jwt, :comando_payload, :usuario_id, :comando, :business_id, :request_codigo, :momento, :total_eventos)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'jwt' => $eventosDoRequest->accessToken,
            'comando_payload' => $eventosDoRequest->comandoPayload,
            'comando' => $eventosDoRequest->comando,
            'usuario_id' => $eventosDoRequest->usuarioId,
            'business_id' => $eventosDoRequest->businessId,
            'request_codigo' => $eventosDoRequest->requestCodigo,
            'momento' => date('Y-m-d H:i:s'),
            'total_eventos' => count($eventosDoRequest->eventos),
        ]);

        foreach ($eventosDoRequest->eventos as $evento) {

            $sql = "INSERT INTO requests_eventos (request_codigo, business_id, evento_momento, evento_descricao) VALUES (:request_codigo, :business_id, :evento_momento, :evento_descricao)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'request_codigo' => $eventosDoRequest->requestCodigo,
                'business_id' => $eventosDoRequest->businessId,
                'evento_momento' => $evento['momento'] ?? '',
                'evento_descricao' => $evento['descricao'] ?? ''
            ]);
        }
    }
}