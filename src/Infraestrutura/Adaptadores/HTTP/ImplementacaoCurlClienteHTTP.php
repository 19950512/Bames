<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\HTTP;

use App\Aplicacao\Compartilhado\HTTP\ClienteHTTP;
use App\Aplicacao\Compartilhado\HTTP\RespostaHTTP;
use CURLFile;
use Exception;

class ImplementacaoCurlClienteHTTP implements ClienteHTTP
{
    private string $_baseURL = '';
    private array $_headers = [];
    private array $_certificado = [];

    public function __construct($config = [])
    {
        if(!function_exists('curl_init')){
            throw new Exception('Ops, é preciso instalar o curl.');
        }

        $this->configurar($config);
    }

    public function configurar($config): void
    {

        if(isset($config['baseURL'])){
            $this->_baseURL = $config['baseURL'];
        }
        if(isset($config['headers'])){
            $this->_headers = $config['headers'];
        }
        if(isset($config['certificado'])){
            $this->_certificado = $config['certificado'];
        }
    }

    public function request($data, $method): RespostaHTTP
    {

        if(!isset($data['endpoint']) or empty($data['endpoint'])){
            throw new Exception('Ops, informe o endpoint.');
        }

        $curl = curl_init();

        $bodyData = '';

        if(isset($data['post']) and in_array('Content-Type: application/json', $this->_headers)){
            $bodyData = json_encode($data['post']);
        }else{
            if(isset($data['post']) and is_array($data['post']) and count($data['post']) > 0){
                $bodyData = http_build_query($data['post']);
            }
        }

        if($method == 'UPLOAD'){
            $method = 'POST';
            $postFields = [];

            // Prepara os dados do POST e os arquivos
            foreach ($data['post'] as $key => $filePath) {
                if (is_array($filePath)) {
                    foreach ($filePath as $key2 => $filePath2) {
                        if (is_file($filePath2)) {
                            // Adiciona o arquivo ao postFields como um CURLFile
                            $postFields[$key . '[' . $key2 . ']'] = new CURLFile($filePath2);
                        }
                    }
                } else {
                    // Para outros campos de dados, apenas os adiciona
                    $postFields[$key] = $filePath;
                }
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_baseURL . $data['endpoint']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            try {

                $resultado = curl_exec($ch);

                if(curl_errno($ch)){
                    $resultado = curl_error($ch);
                }

                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $respostaRequest = $resultado;

                if(!empty($resultado)){
                    json_decode($respostaRequest, true);
                    // se for um json. retorna array
                    if(json_last_error() == JSON_ERROR_NONE){
                        $respostaRequest = json_decode($respostaRequest, true);
                    }
                }

                return new RespostaHTTP(
                    code: $httpcode,
                    body: $respostaRequest
                );

            }catch(Exception $erro){

                throw new Exception('lascou - '.$erro->getMessage());
            }
        }

        $opcoes = [
            CURLOPT_URL => $this->_baseURL.$data['endpoint'],
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $bodyData,
            CURLOPT_VERBOSE => false,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => $this->_headers,
            CURLOPT_FAILONERROR => false,
        ];

        if(isset($this->_certificado['key'], $this->_certificado['pass']) AND !empty($this->_certificado['pass']) AND !empty($this->_certificado['key'])){
            if(is_file($this->_certificado['pass']) and is_file($this->_certificado['key'])){
                $opcoes[CURLOPT_SSLCERT] = $this->_certificado['key'];
                $opcoes[CURLOPT_SSLKEY] = $this->_certificado['pass'];
                if(isset($this->_certificado['password']) and !empty($this->_certificado['password'])){

                    if(!str_contains($this->_certificado['password'], '/certificados/')){
                        $opcoes[CURLOPT_SSLCERTPASSWD] = $this->_certificado['password'];
                    }
                }
            }
        }


        curl_setopt_array($curl, $opcoes);

        try {

            $resultado = curl_exec($curl);

            if(curl_errno($curl)){
                $resultado = curl_error($curl);
            }

            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $respostaRequest = $resultado;

            if(!empty($resultado)){
                json_decode($respostaRequest, true);
                // se for um json. retorna array
                if(json_last_error() == JSON_ERROR_NONE){
                    $respostaRequest = json_decode($respostaRequest, true);
                }
            }

            return new RespostaHTTP(
                code: $httpcode,
                body: $respostaRequest
            );

        }catch(Exception $erro){

            throw new Exception('lascou - '.$erro->getMessage());
        }

    }

    public function get($endpoint): RespostaHTTP
    {

        return $this->request(
            data: ['endpoint' => $endpoint],
            method: 'GET'
        );
    }

    public function upload($endpoint, $data = []): RespostaHTTP
    {

        return $this->request(
            data: [
                'endpoint' => $endpoint,
                'post' => $data
            ],
            method: 'UPLOAD'
        );
    }

    public function post($endpoint, $data = []): RespostaHTTP
    {

        return $this->request(
            data: [
                'endpoint' => $endpoint,
                'post' => $data
            ],
            method: 'POST'
        );
    }

    public function delete($endpoint, $data = []): RespostaHTTP
    {

        return $this->request(
            data: [
                'endpoint' => $endpoint,
                'post' => $data
            ],
            method: 'DELETE'
        );
    }

    public function patch($endpoint, $data = []): RespostaHTTP
    {

        return $this->request(
            data: [
                'endpoint' => $endpoint,
                'post' => $data
            ],
            method: 'PATCH'
        );
    }

    public function put($endpoint, $data = []): RespostaHTTP
    {

        return $this->request(
            data: [
                'endpoint' => $endpoint,
                'post' => $data
            ],
            method: 'PUT'
        );
    }
}