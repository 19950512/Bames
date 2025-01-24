<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Conversor;

use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Compartilhado\Conversor\Fronteiras\ConteudoPDF;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use Exception;

/**
 * @see Veja as instruçoes em README.md
 */
final class ImplementacaoDoc2PDFShellConversorDeArquivo implements ConversorDeArquivo
{

    public function __construct(
        private Discord $discord,
    ){}

    public function docxToPDF(string $conteudo, string $arquivoNome): ConteudoPDF
    {

        // Vamos salvar o $conteudo que é do docx em um arquivo temporário
        $tempPathDocx = tempnam(sys_get_temp_dir(), 'docx'). '.docx';
        file_put_contents($tempPathDocx, $conteudo);

        // Vamos criar um arquivo temporário para o PDF
        $tempPathPDF = tempnam(sys_get_temp_dir(), 'pdf'). '.pdf';

        // Vamos converter o arquivo docx para PDF
        $convertCommand = "/usr/bin/doc2pdf -f pdf -o $tempPathPDF $tempPathDocx 2>&1";
        $resposta = shell_exec($convertCommand);

        // Vamos verificar se houve algum erro
        if (str_contains($resposta, 'Error')) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::DocxToPDF,
                mensagem: "Erro ao converter o arquivo DOCX para PDF"
            );
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::DocxToPDF,
                mensagem: "```bash\n# $convertCommand\n$resposta\n```"
            );
            throw new Exception('Erro ao converter o arquivo DOCX para PDF');
        }

        // Vamos retornar o conteúdo do PDF
        return new ConteudoPDF(
            conteudo: file_get_contents($tempPathPDF),
        );
    }
}
