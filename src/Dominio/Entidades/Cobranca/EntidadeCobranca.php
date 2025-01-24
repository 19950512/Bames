<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Cobranca;

use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\Entidades\Cobranca\Enumerados\Parcela;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoDesconto;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use DateTime;

final class EntidadeCobranca
{

    public Valor $valorTotalComposicaoDaCobranca;
    public function __construct(
        public IdentificacaoUnica $cobrancaCodigo,
        public IdentificacaoUnica $empresaCodigo,
        public IdentificacaoUnica $contaBancariaCodigo,
        public IdentificacaoUnica $clienteCodigo,
        public NomeCompleto $pagadorNomeCompleto,
        public ComposicaoDaCobranca $composicaoDaCobranca,
        public DateTime $dataVencimento,
        public TextoSimples $mensagem,
        public Valor $multa,
        public Valor $juros,
        public Parcela $parcela,
        public MeioPagamento $meioDePagamento,
        public Valor $valorDescontoAntecipacao,
        public TipoDesconto $tipoDesconto,
        public TipoJuro $tipoJuros,
        public TipoMulta $tipoMulta,
    ){
        $this->valorTotalComposicaoDaCobranca = new Valor(0);
        foreach ($composicaoDaCobranca->obter() as $item) {
            $this->valorTotalComposicaoDaCobranca = $this->valorTotalComposicaoDaCobranca->somar($item->valor->get());
        }
    }

    public static function instanciarEntidadeCobranca(Cobranca $parametros): EntidadeCobranca
    {

        $composicaoDaCobranca = new ComposicaoDaCobranca();

        foreach ($parametros->composicaoDaCobranca as $item) {
            $composicaoDaCobranca->adicionarItem(new ItemDaCobranca(
                descricao: new TextoSimples((string) $item['descricao']),
                planoDeContasCodigo: (int) $item['planoDeContaCodigo'],
                planoDeContaNome: new TextoSimples((string) $item['planoDeContaNome']),
                valor: new Valor((float) $item['valor']),
            ));
        }

        $parcela = Parcela::from($parametros->parcela);

        return new EntidadeCobranca(
            cobrancaCodigo: new IdentificacaoUnica($parametros->cobrancaCodigo),
            empresaCodigo: new IdentificacaoUnica($parametros->empresaCodigo),
            contaBancariaCodigo: new IdentificacaoUnica($parametros->contaBancariaCodigo),
            clienteCodigo: new IdentificacaoUnica($parametros->clienteCodigo),
            pagadorNomeCompleto: new NomeCompleto($parametros->clienteNomeCompleto),
            composicaoDaCobranca: $composicaoDaCobranca,
            dataVencimento: new DateTime($parametros->dataVencimento),
            mensagem: new TextoSimples($parametros->mensagem),
            multa: new Valor($parametros->multa),
            juros: new Valor($parametros->juros),
            parcela: $parcela,
            meioDePagamento: MeioPagamento::from($parametros->meioDePagamento),
            valorDescontoAntecipacao: new Valor($parametros->valorDescontoAntecipacao),
            tipoDesconto: TipoDesconto::from($parametros->tipoDesconto),
            tipoJuros: TipoJuro::from($parametros->tipoJuros),
            tipoMulta: TipoMulta::from($parametros->tipoMulta),
        );
    }
}