<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

final class Arquivos
{

    private array $arquivos = [];

    public function __construct(){}

    public function adicionar(ArquivoTemporario $arquivo): void
    {
        $this->arquivos[] = $arquivo;
    }

    public function get(): array
    {
        return $this->arquivos;
    }

    public static function processarArquivosVindoDoUploadFiles(array $uploadedFiles): Arquivos
    {
        $arquivos = new Arquivos();
        if(isset($uploadedFiles['name']) and !empty($uploadedFiles['name']) and is_array($uploadedFiles['name']) and count($uploadedFiles['name']) >= 1){

            foreach ($uploadedFiles['name'] as $key => $fileName) {
                $arquivo = new ArquivoTemporario(
                    fullPath: $uploadedFiles['full_path'][$key],
                    name: $fileName,
                    tmpName: $uploadedFiles['tmp_name'][$key],
                    size: (int)$uploadedFiles['size'][$key],
                    error: (int)$uploadedFiles['error'][$key],
                    type: $uploadedFiles['type'][$key]
                );

                $arquivos->adicionar($arquivo);
            }
        }else{

            $arquivo = new ArquivoTemporario(
                fullPath: $uploadedFiles['full_path'],
                name: $uploadedFiles['name'],
                tmpName: $uploadedFiles['tmp_name'],
                size: (int)$uploadedFiles['size'],
                error: (int)$uploadedFiles['error'],
                type: $uploadedFiles['type']
            );

            $arquivos->adicionar($arquivo);
        }

        return $arquivos;
    }
}