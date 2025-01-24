<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras;

final class Fonte
{
    public function __construct(
        public string $codigo,
        public string $nome,
        public string $descricao,
        public string $link,
        public string $tipo,
        public string $dataUltimaVerificacao,
        public string $dataUltimaMovimentacao,
        public bool $segredoJustica,
        public bool $arquivado,
        public bool $fisico,
        public string $sistema,
        public int $quantidadeEnvolvidos,
        public int $quantidadeMovimentacoes,
        public int $grau,
        public string $capaClasse,
        public string $capaAssunto,
        public string $capaArea,
        public string $capaOrgaoJulgador,
        public string $capaValorCausa,
        public string $capaValorMoeda,
        public string $capaDataDistribuicao,
        public string $tribunalID,
        public string $tribunalNome,
        public string $tribunalSigla,
        public array $informacoesComplementares = []
    ){}

    public array $envolvidos = [];

    public function addEnvolvido(Envolvido $envolvido): void
    {
        $this->envolvidos[] = $envolvido;
    }
}
