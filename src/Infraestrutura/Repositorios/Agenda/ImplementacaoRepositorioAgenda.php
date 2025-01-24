<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\Agenda;

use App\Dominio\Repositorios\Agenda\Fronteiras\CompromissoAgenda;
use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraMeusCompromissos;
use Exception;
use Override;
use PDO;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;
use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraBuscarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAdicionarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento;

class ImplementacaoRepositorioAgenda implements RepositorioAgenda
{

    public function __construct(
        private PDO $pdo,
    ){}

    #[Override] public function buscarMeusCompromissos(string $usuarioCodigo, string $empresaCodigo): SaidaFronteiraMeusCompromissos
    {

        $sql = "SELECT
            evento_codigo,
            evento_plataforma_id,
            titulo,
            descricao,
            dia_todo,
            recorrencia,
            horario_inicio,
            horario_fim,
            momento,
            usuario_id,
            business_id,
            status
        FROM agenda_eventos WHERE usuario_id = :usuario_id and business_id = :empresaCodigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'usuario_id' => $usuarioCodigo,
            'empresaCodigo' => $empresaCodigo
        ]);
        $compromissos = new SaidaFronteiraMeusCompromissos();
        while ($compromisso = $stmt->fetch()) {
            $compromissos->adicionarCompromisso(new CompromissoAgenda(
                codigo: $compromisso['evento_codigo'],
                business_id: $compromisso['business_id'],
                usuario_id: $compromisso['usuario_id'],
                plataforma_id: $compromisso['evento_plataforma_id'],
                titulo: $compromisso['titulo'],
                descricao: $compromisso['descricao'],
                status: $compromisso['status'],
                dataInicio: $compromisso['horario_inicio'],
                dataFim: $compromisso['horario_fim'],
                momento: $compromisso['momento'],
                diaTodo: $compromisso['dia_todo'],
                recorrencia: $compromisso['recorrencia']
            ));
        }

        return $compromissos;
    }

    #[Override] public function buscarEventoPorCodigo(string $codigo, string $empresaCodigo): SaidaFronteiraBuscarEvento
    {
        $sql = "SELECT
            evento_codigo,
            evento_plataforma_id,
            titulo,
            descricao,
            dia_todo,
            recorrencia,
            horario_inicio,
            horario_fim,
            momento,
            usuario_id,
            business_id,
            status
        FROM agenda_eventos WHERE evento_codigo = :codigo and business_id = :empresaCodigo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'codigo' => $codigo,
            'empresaCodigo' => $empresaCodigo
        ]);
        $evento = $stmt->fetch();

        if(!isset($evento['evento_codigo']) OR empty($evento['evento_codigo'])){
            throw new Exception('O evento não existe - '.$codigo);
        }
        
        return new SaidaFronteiraBuscarEvento(
            codigo: $evento['evento_codigo'],
            business_id: $evento['business_id'],
            usuario_id: $evento['usuario_id'],
            plataforma_id: $evento['evento_plataforma_id'],
            titulo: $evento['titulo'],
            descricao: $evento['descricao'],
            status: $evento['status'],
            dataInicio: $evento['horario_inicio'],
            dataFim: $evento['horario_fim'],
            momento: $evento['momento'],
            diaTodo: $evento['dia_todo'],
            recorrencia: $evento['recorrencia']
        );
    }

    #[Override] public function adicionarEvento(EntradaFronteiraAdicionarEvento $parametros): void
    {
        $sql = "INSERT INTO agenda_eventos (
            evento_codigo,
            evento_plataforma_id,
            titulo,
            descricao,
            dia_todo,
            recorrencia,
            horario_inicio,
            horario_fim,
            momento,
            usuario_id,
            business_id,
            status
        ) VALUES (
            :evento_codigo,
            :evento_plataforma_id,
            :titulo,
            :descricao,
            :dia_todo,
            :recorrencia,
            :horario_inicio,
            :horario_fim,
            :momento,
            :usuario_id,
            :business_id,
            :status
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'evento_codigo' => $parametros->codigo,
            'evento_plataforma_id' => $parametros->plataforma_evento_id,
            'titulo' => $parametros->titulo,
            'descricao' => $parametros->descricao,
            'dia_todo' => $parametros->diaTodo ? 'true' : 'false',
            'recorrencia' => $parametros->recorrencia,
            'horario_inicio' => $parametros->dataInicio,
            'horario_fim' => $parametros->dataFim,
            'momento' => date('Y-m-d H:i:s'),
            'usuario_id' => $parametros->usuario_id,
            'business_id' => $parametros->business_id,
            'status' => 'ativo'
        ]);
    }

    #[Override] public function atualizarEvento(EntradaFronteiraAtualizarEvento $parametros): void
    {
        $sql = "UPDATE agenda_eventos SET
            titulo = :titulo,
            descricao = :descricao,
            dia_todo = :dia_todo,
            recorrencia = :recorrencia,
            horario_inicio = :horario_inicio,
            horario_fim = :horario_fim,
            status = :status
        WHERE evento_codigo = :codigo and business_id = :business_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'titulo' => $parametros->titulo,
            'descricao' => $parametros->descricao,
            'dia_todo' => $parametros->diaTodo ? 'true' : 'false',
            'recorrencia' => $parametros->recorrencia,
            'horario_inicio' => $parametros->dataInicio,
            'horario_fim' => $parametros->dataFim,
            'status' => $parametros->status,
            'codigo' => $parametros->codigo,
            'business_id' => $parametros->business_id
        ]);
    }
}