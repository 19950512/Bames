<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\GerenciadorDeArquivos;

use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;

interface GerenciadorDeArquivos
{
    public function salvarArquivo(EntradaFronteiraSalvarArquivo $parametros): void;
    public function listarArquivos(string $diretorio, string $empresaCodigo): array;
    public function obterArquivo(string $diretorioENomeArquivo, string $empresaCodigo): string;
    public function deletarArquivo(string $diretorioENomeArquivo, string $empresaCodigo): void;
    public function linkTemporarioParaDownload(string $diretorioENomeArquivo, string $empresaCodigo, string $expires = '+20 minutes'): string;
    public function linkTemporarioParaUpload(string $diretorioENomeArquivo, string $empresaCodigo, string $expires = '+20 minutes'): string;
}