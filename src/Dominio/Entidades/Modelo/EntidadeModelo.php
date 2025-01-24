<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Modelo;

use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraModelo;
use Exception;

class EntidadeModelo
{
    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        public Apelido $nome,
        public TextoSimples $nomeArquivo,
    ){}

    public static function instanciarEntidadeModelo(SaidaFronteiraModelo $params): EntidadeModelo
    {

        try {
            $nome = new Apelido($params->nome);
        }catch (Exception $erro){
            throw new Exception("O Apelido da Entidade Modelo '{$params->nome}' ID: $params->modeloCodigo não está válido. {$erro->getMessage()}");
        }

        $entidadeModelo = new EntidadeModelo(
            codigo: new IdentificacaoUnica($params->modeloCodigo),
            nome: $nome,
            nomeArquivo: new TextoSimples($params->nomeArquivo),
        );

        return $entidadeModelo;

    }

}