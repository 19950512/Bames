<?php

declare(strict_types=1);

namespace App\Dominio\Entidades\Financeiro;

use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\Repositorios\PlanoDeContas\Fronteiras\SaidaFronteiraPlanoDeConta;

final class EntidadePlanoDeConta
{
    public function __construct(
        public int $codigo,
        public TextoSimples $nome,
        public TextoSimples $tipo,
        public TextoSimples $categoria,
        public TextoSimples $descricao,
    ){}

    public static function instanciarEntidadePlanoDeConta(SaidaFronteiraPlanoDeConta $parametros): EntidadePlanoDeConta
    {
        return new EntidadePlanoDeConta(
            codigo: (int) $parametros->planoDeContasCodigo,
            nome: new TextoSimples($parametros->planoDeContasNome),
            tipo: new TextoSimples($parametros->planoDeContasTipo),
            categoria: new TextoSimples($parametros->planoDeContasCategoria),
            descricao: new TextoSimples($parametros->planoDeContasDescricao),
        );
    }
}
