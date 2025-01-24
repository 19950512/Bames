<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Processos\Fronteiras;

final class MovimentacaoData
{
    public function __construct(
        public string $id,
        public string $empresaCodigo,
        public string $processoCodigo,
        public string $processoCNJ,
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

    public function obterArray(): array
    {
        return [
            'id' => $this->id,
            'empresaCodigo' => $this->empresaCodigo,
            'processoCodigo' => $this->processoCodigo,
            'processoCNJ' => $this->processoCNJ,
            'data' => $this->data,
            'tipo' => $this->tipo,
            'tipoPublicacao' => $this->tipoPublicacao,
            'classificacaoPreditaNome' => $this->classificacaoPreditaNome,
            'classificacaoPreditaDescricao' => $this->classificacaoPreditaDescricao,
            'classificacaoPreditaHierarquia' => $this->classificacaoPreditaHierarquia,
            'conteudo' => $this->conteudo,
            'textoCategoria' => $this->textoCategoria,
            'fonteProcessoFonteId' => $this->fonteProcessoFonteId,
            'fonteFonteId' => $this->fonteFonteId,
            'fonteNome' => $this->fonteNome,
            'fonteTipo' => $this->fonteTipo,
            'fonteSigla' => $this->fonteSigla,
            'fonteGrau' => $this->fonteGrau,
            'fonteGrauFormatado' => $this->fonteGrauFormatado,
        ];
    }
}