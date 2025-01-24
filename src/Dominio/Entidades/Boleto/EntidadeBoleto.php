<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Boleto;

use App\Aplicacao\Compartilhado\Cobranca\Enumerados\CobrancaSituacao;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\URL;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Boleto\Fronteiras\SaidaFronteiraBoleto;
use DateTime;

final class EntidadeBoleto
{
    public function __construct(
        public IdentificacaoUnica $codigo,
        public IdentificacaoUnica $empresaCodigo,
        public IdentificacaoUnica $contaBancariaCodigo,
        public IdentificacaoUnica $cobrancaCodigo,
        public bool $foiAceitoPelaPlataforma,
        public TextoSimples $codigoBoletoNaPlataformaAPICobranca,
        public TextoSimples $seuNumero,
        public TextoSimples $nossoNumero,
        public TextoSimples $codigoDeBarras,
        public TextoSimples $linhaDigitavel,
        public TextoSimples $qrCode,
        public Valor $valor,
        public DateTime $vencimento,
        public Status $status,
        public URL | TextoSimples $link,

    ){}

    public static function instanciarEntidadeBoleto(SaidaFronteiraBoleto $parametros): EntidadeBoleto
    {


        if($parametros->statusBoleto == CobrancaSituacao::AGUARDANDO_PAGAMENTO->value){
            $status = Status::REGISTRADO;
        }else{
            $status = Status::from($parametros->statusBoleto);
        }


        return new EntidadeBoleto(
            codigo: new IdentificacaoUnica($parametros->codigoBoleto),
            empresaCodigo: new IdentificacaoUnica($parametros->empresaCodigo),
            contaBancariaCodigo: new IdentificacaoUnica($parametros->contaBancariaCodigo),
            cobrancaCodigo: new IdentificacaoUnica($parametros->cobrancaCodigo),
            foiAceitoPelaPlataforma: $parametros->foiAceitoPelaPlataforma,
            codigoBoletoNaPlataformaAPICobranca: new TextoSimples($parametros->codigoBoletoNaPlataformaAPICobranca),
            seuNumero: new TextoSimples($parametros->seuNumero),
            nossoNumero: new TextoSimples($parametros->nossoNumero),
            codigoDeBarras: new TextoSimples($parametros->codigoDeBarras),
            linhaDigitavel: new TextoSimples($parametros->linhaDigitavel),
            qrCode: new TextoSimples($parametros->qrCode),
            valor: new Valor($parametros->valor),
            vencimento: new DateTime($parametros->dataVencimento),
            status: $status,
            link: !empty($parametros->linkBoleto) ? new URL($parametros->linkBoleto) : new TextoSimples(''),
        );
    }
}
