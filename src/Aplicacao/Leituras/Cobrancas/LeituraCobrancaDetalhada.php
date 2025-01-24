<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Cobrancas;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Boleto;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use Exception;

final class LeituraCobrancaDetalhada
{
    private Cobranca $cobranca;
    public function __construct(
        private RepositorioCobranca $repositorioCobranca,
        private Cache $cache
    ){}

    public function executar(string $empresaCodigo, string $cobrancaCodigo): array
    {

        $keyCache = "$empresaCodigo/cobrancaDetalhada/$cobrancaCodigo";
        if($this->cache->get($keyCache)){
            return json_decode($this->cache->get($keyCache), true);
        }

        try {

            $this->cobranca = $this->repositorioCobranca->buscarCobrancaPorCodigo(
                cobrancaCodigo: $cobrancaCodigo,
                empresaCodigo: $empresaCodigo
            );

            $cobarancaDetalhada = [
                'codigo' => $this->cobranca->cobrancaCodigo,
                'dataVencimento' => $this->cobranca->dataVencimento,
                'contaBancariaCodigo' => $this->cobranca->contaBancariaCodigo,
                'codigoNaPlataformaCobrancaAPI' => $this->cobranca->codigoNaPlataformaCobrancaAPI,
                'clienteCodigo' => $this->cobranca->clienteCodigo,
                'mensagem' => $this->cobranca->mensagem,
                'juros' => $this->cobranca->juros,
                'multa' => $this->cobranca->multa,
                'parcelas' => $this->cobranca->parcela,
                'meioDePagamento' => $this->cobranca->meioDePagamento,
                'eventos' => $this->cobranca->obterEventos(),
                'composicaoDaCobranca' => $this->cobranca->obterComposicaoDaCobranca(),
                'boletos' => array_map(function ($boleto){ // Esse map é só para você saber qual é a estrutura do boleto.
                    if(is_a($boleto, Boleto::class)){
                        return [
                            'pagadorCodigo' => $boleto->pagadorCodigo,
                            'boletoCodigo' => $boleto->boletoCodigo,
                            'cobrancaCodigo' => $boleto->cobrancaCodigo,
                            'boletoCodigoNaPlataformaCobrancaAPI' => $boleto->boletoCodigoNaPlataformaCobrancaAPI,
                            'status' => $boleto->status,
                            'vencimento' => $boleto->vencimento,
                            'nossoNumero' => $boleto->nossoNumero,
                            'codigoDeBarras' => $boleto->codigoDeBarras,
                            'linhaDigitavel' => $boleto->linhaDigitavel,
                            'mensagem' => $boleto->mensagem,
                            'linkBoleto' => $boleto->linkBoleto,
                            'valor' => $boleto->valor,
                        ];
                    }
                }, $this->cobranca->obterBoletos()),
            ];

            $this->cache->set($keyCache, json_encode($cobarancaDetalhada), 60 * 60 * 24);

            return $cobarancaDetalhada;

        }catch (Exception $erro) {
            throw new Exception("Ops, não foi possível buscar a cobrança. - {$erro->getMessage()}");
        }
    }
}