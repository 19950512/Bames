<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Modelos;

use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use Exception;

final class LeituraModeloPreview
{
    public function __construct(
        private RepositorioModelos $repositorioModelos,
        private GerenciadorDeArquivos $gerenciadorDeArquivos
    ){}

    public function executar(string $modeloCodigo, string $empresaCodigo): mixed
    {
        try {
            $modelo = $this->repositorioModelos->obterModeloPorCodigo(
                modeloCodigo: $modeloCodigo,
                empresaCodigo: $empresaCodigo
            );

            $arquivo = $this->gerenciadorDeArquivos->obterArquivo(
                diretorioENomeArquivo: '/modelos/'.$modelo->nomeArquivo,
                empresaCodigo: $empresaCodigo
            );

            // Vamos exibir o conteúdo do arquivo
            return $arquivo;

        }catch (Exception $erro){
            throw new Exception("Ops, não foi possível obter o modelo. - {$erro->getMessage()}");
        }
    }
}