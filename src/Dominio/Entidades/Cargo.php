<?php

declare(strict_types=1);

namespace App\Dominio\Entidades;

use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\Descricao;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Empresa\Cargos\Fronteiras\SaidaFronteiraBuscarCargoPorCodigo;

class Cargo
{
    public function __construct(
        readonly public IdentificacaoUnica $codigo,
        readonly public IdentificacaoUnica $empresaCodigo,
        public Apelido $nome,
        public Descricao $descricao,
    ){}

    public static function build(SaidaFronteiraBuscarCargoPorCodigo $params): Cargo
    {

        $cargo = new Cargo(
            codigo: new IdentificacaoUnica($params->cargoCodigo),
            empresaCodigo: new IdentificacaoUnica($params->empresaCodigo),
            nome: new Apelido($params->nome),
            descricao: new Descricao($params->descricao),
        );

        return $cargo;
    }
}