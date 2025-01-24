<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Boleto;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Repositorios\Boleto\Fronteiras\SaidaFronteiraBoleto;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use Exception;

final class LeituraBoletoDetalhado
{
    public function __construct(
        private RepositorioBoleto $repositorioBoleto,
        private Cache $cache
    ){}

    public function executar(string $empresaCodigo, string $boletoCodigo): array
    {

        $keyCache = "$empresaCodigo/boletoDetalhado/$boletoCodigo";
        if($this->cache->get($keyCache)){
            return json_decode($this->cache->get($keyCache), true);
        }

        try {

            $boleto = $this->repositorioBoleto->buscarBoletoPorCodigo(
                codigoBoleto: $boletoCodigo,
                empresaCodigo: $empresaCodigo
            );

            if(is_a($boleto, SaidaFronteiraBoleto::class)){

                $boletoDetalhado = [
                    'pagadorCodigo' => $boleto->pagadorCodigo,
                    'boletoCodigo' => $boleto->codigoBoleto,
                    'cobrancaCodigo' => $boleto->cobrancaCodigo,
                    'boletoCodigoNaPlataformaCobrancaAPI' => $boleto->codigoBoletoNaPlataformaAPICobranca,
                    'status' => $boleto->statusBoleto,
                    'vencimento' => $boleto->dataVencimento,
                    'nossoNumero' => $boleto->nossoNumero,
                    'codigoDeBarras' => $boleto->codigoDeBarras,
                    'linhaDigitavel' => $boleto->linhaDigitavel,
                    'mensagem' => $boleto->mensagem,
                    'linkBoleto' => $boleto->linkBoleto,
                    'valor' => $boleto->valor,
                ];

                $this->cache->set($keyCache, json_encode($boletoDetalhado), 60 * 60 * 24);

                return $boletoDetalhado;
            }

            throw new Exception("Ops, não foi possível buscar o boleto.");


        }catch (Exception $erro) {
            throw new Exception("Ops, não foi possível buscar a cobrança. - {$erro->getMessage()}");
        }
    }
}