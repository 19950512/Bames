<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Cobrancas;

use App\Dominio\Repositorios\Cobranca\Fronteiras\Boleto;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;

final class LeituraTodasAsCobrancas
{
    public function __construct(
        private RepositorioCobranca $repositorioCobranca
    ){}

    public function executar(string $empresaCodigo): array
    {
        return array_map(function($cobranca){

            if(is_a($cobranca, Cobranca::class)){
                return [
                    'codigo' => $cobranca->cobrancaCodigo,
                    'dataVencimento' => $cobranca->dataVencimento,
                    'pagadorNomeCompleto' => $cobranca->clienteNomeCompleto,
                    'descricao' => $cobranca->mensagem,
                    'meioDePagamentoName' => $cobranca->meioDePagamento,
                    'valor' => array_sum(array_map(function($composicao){
                        if(isset($composicao['valor'])){
                            return $composicao['valor'];
                        }
                    }, $cobranca->obterComposicaoDaCobranca())),

                    /*
                    'contaBancariaCodigo' => $cobranca->contaBancariaCodigo,
                    'codigoNaPlataformaCobrancaAPI' => $cobranca->codigoNaPlataformaCobrancaAPI,
                    'clienteCodigo' => $cobranca->clienteCodigo,
                    'mensagem' => $cobranca->mensagem,
                    'juros' => $cobranca->juros,
                    'multa' => $cobranca->multa,
                    'parcelas' => $cobranca->parcela,
                    'meioDePagamento' => $cobranca->meioDePagamento,
                    'eventos' => $cobranca->obterEventos(),
                    'composicaoDaCobranca' => $cobranca->obterComposicaoDaCobranca(),
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
                    }, $cobranca->obterBoletos()),
                    */
                ];
            }

        }, $this->repositorioCobranca->buscarTodasAsCobrancas($empresaCodigo)->obterCobrancas());
    }
}