<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\Processos\Fronteiras;

final readonly class Movimentacao
{
    public function __construct(
        public string $id,
        public string $data,
        public string $tipo,
        public string $tipoPublicacao,
        public string $classificacaoPreditaNome,
        public string $classificacaoPreditaDescricao,
        public string $classificacaoPreditaHierarquia,
        public string $conteudo,
        public string $textoCategoria,
        public string $fonteProcessoFonteId,
        public string $fonteFonteId,
        public string $fonteNome,
        public string $fonteTipo,
        public string $fonteSigla,
        public string $fonteGrau,
        public string $fonteGrauFormatado,
    ){}
}
