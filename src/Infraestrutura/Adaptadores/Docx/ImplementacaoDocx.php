<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Docx;

use App\Aplicacao\Compartilhado\Data\Data;
use App\Aplicacao\Compartilhado\Docx\Docx;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\EntradaFronteiraSubistituirConteudo;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\SaidaFronteiraSubistituirConteudo;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

final class ImplementacaoDocx implements Docx
{

    public function __construct(
        private Data $data,
    ){}

    public function substituicaoUtil(): array
    {
        return [
            '{{data_agora}}' => (string) $this->data->agora(),
            '{{data_mes_completo}}' => $this->data->mesCompleto((int) date('m')),
            '{{data_mes_abreviado}}' => $this->data->mesAbreviado((int) date('m')),
            '{{data_dia_semana_completo}}' => $this->data->diaSemanaCompleto((int) date('w')),
            '{{data_dia_semana_abreviado}}' => $this->data->diaSemanaAbreviado((int) date('w')),
            '{{data_dia_do_mes}}' => (string) $this->data->diaDoMes(),
            '{{data_dia_do_ano}}' => (string) $this->data->diaDoAno(),
            '{{data_semana_do_ano}}' => (string) $this->data->semanaDoAno(),
            '{{data_ano}}' => (string) $this->data->ano(),
            '{{data_hora}}' => (string) $this->data->hora(),
            '{{data_minuto}}' => (string) $this->data->minuto(),
        ];
    }

    public function substituicaoUtilCaixaAlta(): array
    {
        $substituicoes = $this->substituicaoUtil();

        foreach($substituicoes as $chave => $valor){
            $substituicoes[mb_strtoupper($chave)] = mb_strtoupper($valor);
        }

        return $substituicoes;
    }

    public function substituirConteudo(EntradaFronteiraSubistituirConteudo $parametros): SaidaFronteiraSubistituirConteudo
    {
        // Caminho temporário para o arquivo
        $tempFilePath = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($tempFilePath, $parametros->conteudoDoArquivoDocx);

        // Caminho para o diretório temporário para extrair o docx
        $extractTo = tempnam(sys_get_temp_dir(), 'docx_extract_');
        unlink($extractTo); // Removemos o arquivo, pois o caminho é usado como pasta
        mkdir($extractTo);

        // Extrai o conteúdo do .docx (um arquivo zip)
        $zip = new ZipArchive();
        if ($zip->open($tempFilePath) === true) {
            $zip->extractTo($extractTo);
            $zip->close();
        } else {
            throw new Exception("Falha ao abrir o documento .docx.");
        }

        // Caminho para o arquivo document.xml que contém o texto principal
        $documentXmlPath = $extractTo . '/word/document.xml';

        // Carrega o XML do documento e substitui o texto
        $xmlContent = file_get_contents($documentXmlPath);

        $xmlContent = str_replace(array_keys($parametros->subistituicoes), array_values($parametros->subistituicoes), $xmlContent);

        // Salva a modificação no arquivo XML
        file_put_contents($documentXmlPath, $xmlContent);

        // Compacta tudo de volta para um novo .docx
        $newTempFilePath = tempnam(sys_get_temp_dir(), 'final_new').'.docx';
        $zip = new ZipArchive();

        if ($zip->open($newTempFilePath, ZipArchive::CREATE) === true) {
            // Adiciona todos os arquivos e pastas do diretório temporário de volta ao .docx
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($extractTo),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    // Obtem o caminho relativo para manter a estrutura interna do .docx
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($extractTo) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        } else {
            throw new Exception("Falha ao criar o novo documento .docx.");
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractTo, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($extractTo);

        return new SaidaFronteiraSubistituirConteudo(
            caminho: $newTempFilePath, // Caminho para o novo arquivo .docx
            conteudo: file_get_contents($newTempFilePath) // Conteúdo do novo arquivo .docx
        );
    }
}