<?php

declare(strict_types=1);

namespace App\Infraestrutura\Repositorios\ConsultarInformacoesNaInternet;

use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\EntradaFronteiraSalvarRequestPorDocumento;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarCPFRepositorio;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\SaidaFronteiraConsultarProcessosCPFRepositorio;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\RepositorioConsultarInformacoesNaInternet;
use DateInterval;
use DateTime;
use PDO;

readonly class ImplementacaoConsultarInformacoesNaInternet implements RepositorioConsultarInformacoesNaInternet
{
    public function __construct(
        private PDO $pdo,
    ){}

    public function cobrarCustoParaConsultarDocumento(string $documento, float $custo): void
    {
        // TODO: Implement cobrarCustoParaConsultarDocumento() method.
    }

    public function documentoJaFoiConsultadoNosUltimosDias(string $documento): bool
    {
        $agora = new DateTime();
        $intervalo = new DateInterval('P30D'); // 30 days interval
        $agora->sub($intervalo); // Subtract the interval from the current date
        $agoraFormatted = $agora->format('Y-m-d'); // Format the date

        $sql = "SELECT COUNT(*) FROM consultas_informacoes_documento WHERE documento_consultado = :documento AND momento >= :agora";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':documento' => $documento,
            ':agora' => $agoraFormatted
        ]);
        $result = $stmt->fetchColumn();

        return $result > 0;
    }

    public function buscarProcessosDoDocumento(string $documento): SaidaFronteiraConsultarProcessosCPFRepositorio
    {


    }

    public function buscarInformacoesDoDocumento(string $documento): SaidaFronteiraConsultarCPFRepositorio
    {

        $sql = "SELECT * FROM consultas_informacoes_documento WHERE documento_consultado = :documento ORDER BY momento DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':documento' => $documento
        ]);
        $result = $stmt->fetch();

        return new SaidaFronteiraConsultarCPFRepositorio(

        );
    }

    public function salvarRequestPorDocumento(EntradaFronteiraSalvarRequestPorDocumento $parametros): void
    {
        $sql = "INSERT INTO consultas_informacoes_documento (business_id, usuario_id, documento_consultado, momento, mensagem, payload_request, payload_response, request_status) VALUES (:business_id, :usuario_id, :documento_consultado, :momento, :mensagem, :payload_request, :payload_response, :request_status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':business_id' => $parametros->empresaCodigo,
            ':usuario_id' => $parametros->contaCodigo,
            ':documento_consultado' => $parametros->requestID,
            ':momento' => $parametros->momento,
            ':mensagem' => $parametros->descricao,
            ':payload_request' => '',
            ':payload_response' => '',
            ':request_status' => 'PENDING'
        ]);
    }

    public function consultar(string $documento): void
    {
        // TODO: Implement consultar() method.
    }
}
