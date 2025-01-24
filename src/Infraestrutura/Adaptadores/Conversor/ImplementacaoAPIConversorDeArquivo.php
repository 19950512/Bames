<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Conversor;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Compartilhado\Conversor\Fronteiras\ConteudoPDF;
use Exception;
use GuzzleHttp\Client;

final class ImplementacaoAPIConversorDeArquivo implements ConversorDeArquivo
{
    private string $token;
    public function __construct(
        private Ambiente $ambiente
    ){
        $this->token = $this->ambiente->get('API_CONVERTER_TOKEN');
    }

    public function docxToPDF(string $conteudo, string $arquivoNome): ConteudoPDF
    {
        $client = new Client();

        $response = $client->request('POST', 'https://v2.convertapi.com/convert/docx/to/pdf', [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer '.$this->token,
            ],
             'multipart' => [
                    [
                        'name' => 'File',
                        'filename' => $arquivoNome,
                        'contents' => $conteudo,
                        'headers' => [
                            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                        ]
                    ]
             ]
        ]);

        $resposta = $response->getBody()->getContents();

        if(!json_validate($resposta)){
            throw new Exception("Erro ao converter o arquivo para PDF. - {$resposta}");
        }

        $resposta = json_decode($resposta, true);

        if(!isset($resposta['Files'])){
            throw new Exception("Erro ao converter o arquivo para PDF. - {$resposta}");
        }

        if(!isset($resposta['Files'][0])){
            throw new Exception("Erro ao converter o arquivo para PDF. - {$resposta}");
        }

        if(!isset($resposta['Files'][0]['FileData'])){
            throw new Exception("Erro ao converter o arquivo para PDF. - {$resposta}");
        }

        $fileData = $resposta['Files'][0]['FileData'];

        return new ConteudoPDF(
            conteudo: $fileData
        );
    }
}
