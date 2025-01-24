<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Cobranca\Fronteiras;

final class Cobranca
{

    private array $boletos = [];

    private array $eventos = [];
    public function __construct(
        public string $cobrancaCodigo,
        public string $empresaCodigo,
        public string $contaBancariaCodigo,
        public string $clienteCodigo,
        public string $clienteNomeCompleto,
        public string $dataVencimento,
        public string $mensagem,
        public string $meioDePagamento,
        public float $multa,
        public array $composicaoDaCobranca,
        public float $juros,
        public float $valorDescontoAntecipacao,
        public string $tipoDesconto,
        public string $tipoJuros,
        public string $tipoMulta,
        public int $parcela,
        public string $codigoNaPlataformaCobrancaAPI = ''
    ){}

    public function adicionarEvento(string $momento, string $descricao): void
    {
        $this->eventos[] = [
            'momento' => date('d/m/Y', strtotime($momento)).' às '.date('H:i', strtotime($momento)),
            'descricao' => $descricao
        ];
    }

    public function obterEventos(): array
    {
        return $this->eventos;
    }

    public function adicionarBoleto(Boleto $boleto): void
    {
        $this->boletos[] = $boleto;
    }

    public function obterBoletos(): array
    {
        return $this->boletos;
    }

    public function obterComposicaoDaCobranca(): array
    {
        return array_map(function($item){
            return [
                'descricao' => $item['descricao'],
                'planoDeContaCodigo' => $item['planoDeContaCodigo'],
                'planoDeContaNome' => $item['planoDeContaNome'],
                'valor' => $item['valor']
            ];
        }, $this->composicaoDaCobranca);
    }
}
