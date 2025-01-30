<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Contrato;

use DateTime;
use Exception;
use DI\Container;
use App\Dominio\ObjetoValor\Dia;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\ObjetoValor\Horario;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Contrato\Enumerados\Status;
use App\Dominio\Entidades\Cobranca\Enumerados\Parcela;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoDesconto;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Contrato\Fronteiras\SaidaFronteiraContrato;

final class EntidadeContrato
{

    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        readonly public IdentificacaoUnica $empresaCodigo,
        readonly public EntidadeCliente $cliente,
        readonly public EntidadeContaBancaria $contaBancaria,
        public Status $status,
        public bool $recorrente,
        public DateTime $dataInicio,
        public DateTime $dataCriacao,
        public MeioPagamento $meioPagamento,
        public Dia $diaVencimento,
        public Dia $diaEmissaoCobranca,
        public Horario $horarioEmissaoCobranca,
        public Parcela $parcela,
        public Valor $valor,
        public Valor $multa,
        public Valor $juros,
        public Valor $descontoAntecipacao,
        public TipoDesconto $tipoDescontoAntecipacao,
        public TipoJuro $tipoJuro,
        public TipoMulta $tipoMulta,
    ){}

    public static function instanciarEntidadeContrato(SaidaFronteiraContrato $parametros, Container $container): EntidadeContrato
    {

        try {

            $clienteData = $container->get(RepositorioClientes::class)->buscarClientePorCodigo(
                codigoCliente: $parametros->clienteCodigo,
                empresaCodigo: $parametros->empresaCodigo
            );
    
            $entidadeCliente = EntidadeCliente::instanciarEntidadeCliente($clienteData);
        
        }catch (Exception $erro) {
            throw new Exception("Ops, não foi possível obter o cliente. {$erro->getMessage()}");
        }

        try {
            $contaBancariaDados = $container->get(RepositorioContaBancaria::class)->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $parametros->contaBancariaCodigo,
                empresaCodigo: $parametros->empresaCodigo
            );
            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        } catch (Exception $erro) {
            throw new Exception("Ops, não foi possível obter a conta bancária. {$erro->getMessage()}");
        }

        return new EntidadeContrato(
            codigo: new IdentificacaoUnica($parametros->codigo),
            empresaCodigo: new IdentificacaoUnica($parametros->empresaCodigo),
            cliente: $entidadeCliente,
            contaBancaria: $entidadeContaBancaria,
            status: Status::from($parametros->status),
            recorrente: $parametros->recorrente,
            dataInicio: new DateTime($parametros->dataInicio),
            horarioEmissaoCobranca: new Horario(
                hora: $parametros->horarioEmissaoCobrancaHora,
                minuto: $parametros->horarioEmissaoCobrancaMinuto
            ),
            dataCriacao: new DateTime($parametros->dataCriacao),
            meioPagamento: MeioPagamento::from($parametros->meioPagamento),
            diaVencimento: new Dia($parametros->diaVencimento),
            diaEmissaoCobranca: new Dia($parametros->diaEmissaoCobranca),
            parcela: Parcela::from($parametros->parcela),
            valor: new Valor($parametros->valor),
            multa: new Valor($parametros->multa),
            juros: new Valor($parametros->juros),
            descontoAntecipacao: new Valor($parametros->descontoAntecipacao),
            tipoDescontoAntecipacao: TipoDesconto::from($parametros->tipoDescontoAntecipacao),
            tipoJuro: TipoJuro::from($parametros->tipoJuro),
            tipoMulta: TipoMulta::from($parametros->tipoMulta),
        );
    }
}