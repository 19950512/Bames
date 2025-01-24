<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\HTTP;

use App\Aplicacao\Compartilhado\HTTP\ClienteHTTP;
use App\Aplicacao\Compartilhado\HTTP\RespostaHTTP;

class CurlClientHttpMOCK implements ClienteHTTP
{

    private string $_baseURL = '';
    private array $_headers = [];
    private array $_certificado = [];

    public function __construct($config = [])
    {}

    public function configurar($config): void
    {}

    public function request($data, $method): RespostaHTTP
    {
        return new RespostaHTTP(
            code:  200,
            body:  [
                'r' => 'ok',
            ]
        );
    }


    public function upload($endpoint, $data = []): RespostaHTTP
    {
        return $this->post($endpoint, $data);
    }

    public function get($endpoint): RespostaHTTP
    {
        return new RespostaHTTP(
            code: 200,
            body:  [
                'r' => 'ok',
                'txid' => '',
                'content' => 'adwwad',
                'situacao' => 'EMABERTO',
                'valorNominal' => '19',
                'content' => [
                    [
                        'multa' => ['valor' => '0', 'taxa' => '0'],
                        'mora' => ['valor' => '0', 'taxa' => '0'],
                        'linhaDigitavel' => '74897937700000099891122224595067890312345109-MOCK',
                        'codigoBarras' => '74897937700000099891122224595067890312345109-MOCK',
                        'seuNumero' => 'qwfqwf',
                        'nossoNumero' => '123456789',
                        'dataEmissao' => '20220101',
                        'situacao' => 'EMABERTO',
                        'valorNominal' => '10',
                        'dataVencimento' => '20220101',
                        'pagador' => [
                            'nome' => 'Matheus'
                        ]
                    ]
                ],
                'message' => 'qwfqwf',
                'multa' => ['valor' => '0', 'taxa' => '0'],
                'mora' => ['valor' => '0', 'taxa' => '0'],
                'linhaDigitavel' => '74897937700000099891122224595067890312345109-MOCK',
                'codigoBarras' => '74897937700000099891122224595067890312345109-MOCK',
                'seuNumero' => 'qwfqwf',
                'nossoNumero' => '123456789',
                'dataEmissao' => '20220101',
                'dataVencimento' => '20220101',
                'pagador' => [
                    'nome' => 'Matheus'
                ]
            ]
        );
    }

    public function post($endpoint, $body = []): RespostaHTTP
    {
        return new RespostaHTTP(
            code: 200,
            body: [
                "access_token" => "wqfqwfqfq",
                "scope" => "",
                "expires_in" => 3600,
                "refresh_expires_in" => "",
                "refresh_token" => "",
                "token_type" => "",
                'pagador' => [
                    'nome' => 'Matheus'
                ],
                "id_token" => "",
                "not-before-policy" => "",
                "session_state" => "",
                'linhaDigitavel' => '74897937700000099891122224595067890312345109-MOCK',
                'codigoBarras' => '74897937700000099891122224595067890312345109-MOCK',
                'seuNumero' => 'qwfqwf',
                'nossoNumero' => '123456789',
                'dataEmissao' => '20220101',
                'dataVencimento' => '20220101',
                'messages' => [
                    ['id' => 'wamid.HBgMNTU1NDkzNjYyMjc2FQIAERgSMUU1NTBBMEVFRERBQkZGNTc2AA==-MOCK']
                ]
            ]
        );
    }

    public function patch($endpoint, $body = []): RespostaHTTP
    {
        return new RespostaHTTP(
            code: 200,
            body: ['r' => 'ok', 'statusComando' => 'BAXADO?', 'transactionId' => '123213'],
        );
    }

    public function delete($endpoint, $body = []): RespostaHTTP
    {
        return new RespostaHTTP(
            code:  200,
            body:  [
                'r' => 'ok',
            ]
        );
    }

    public function put($endpoint, $body = []): RespostaHTTP
    {
        return new RespostaHTTP(
            code: 200,
            body: ['r' => 'ok', 'statusComando' => 'BAXADO?'],
        );
    }
}