<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\ContaBancaria;

use App\Aplicacao\Compartilhado\Cobranca\PlataformaDeCobranca;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\ContaBancaria\Enumerados\Banco;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraContaBancaria;
use DI\Container;
use Exception;

final class EntidadeContaBancaria
{

    private ?PlataformaDeCobranca $plataformaDeCobranca = null;

    public function __construct(
        readonly IdentificacaoUnica $codigo,
        public TextoSimples $nome,
        public TextoSimples $clientIDAPI,
        public TextoSimples $chaveAPI,
        public AmbienteConta $ambiente,
        public Banco $banco,
    ){}

    public static function instanciarEntidadeContaBancaria(SaidaFronteiraContaBancaria $parametros): EntidadeContaBancaria
    {
        $ambiente = AmbienteConta::Sandbox;
        if($parametros->ambiente === 'Producao'){
            $ambiente = AmbienteConta::Producao;
        }
        return new EntidadeContaBancaria(
            codigo: new IdentificacaoUnica($parametros->codigo),
            nome: new TextoSimples($parametros->nome),
            clientIDAPI: new TextoSimples($parametros->clientIDAPI),
            chaveAPI: new TextoSimples($parametros->chaveAPI),
            ambiente: $ambiente,
            banco: Banco::tryFrom($parametros->banco),
        );
    }

    public function comparar(EntidadeContaBancaria $contaBancaria): array
    {
        $diferencas = [];
        if ($this->nome->get() !== $contaBancaria->nome->get()) {
            $diferencas['nome'] = [
                'antigo' => $this->nome->get(),
                'novo' => $contaBancaria->nome->get(),
            ];
        }
        if ($this->clientIDAPI->get() !== $contaBancaria->clientIDAPI->get()) {
            $diferencas['clientIDAPI'] = [
                'antigo' => $this->clientIDAPI->get(),
                'novo' => $contaBancaria->clientIDAPI->get(),
            ];
        }

        if($this->ambiente->value !== $contaBancaria->ambiente->value){
            $diferencas['ambiente'] = [
                'antigo' => $this->ambiente->value,
                'novo' => $contaBancaria->ambiente->value,
            ];
        }

        if ($this->chaveAPI->get() !== $contaBancaria->chaveAPI->get()) {
            $diferencas['chaveAPI'] = [
                'antigo' => $this->chaveAPI->get(),
                'novo' => $contaBancaria->chaveAPI->get(),
            ];
        }

        if ($this->banco->value !== $contaBancaria->banco->value) {
            $diferencas['banco'] = [
                'antigo' => $this->banco->value,
                'novo' => $contaBancaria->banco->value,
            ];
        }

        return $diferencas;
    }

    public function obterPlataformaDeCobranca(Container $container): PlataformaDeCobranca
    {

        if($this->plataformaDeCobranca === null){
            $this->plataformaDeCobranca = match($this->banco){
                Banco::ASAAS => $container->get('ImplementacaoDoBancoASAAS'),
                Banco::Nenhum => $container->get('ImplementacaoNenhumBanco'),
                default => throw new Exception("Ops, não foi possível encontrar a plataforma de cobrança."),
            };
        }

        return $this->plataformaDeCobranca;
    }
}
