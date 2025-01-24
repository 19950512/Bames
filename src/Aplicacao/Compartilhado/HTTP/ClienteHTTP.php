<?php

declare(strict_types=1);

namespace App\Aplicacao\Compartilhado\HTTP;

interface ClienteHTTP
{
    public function configurar(array $config): void;
    public function request(array $data, string $method): RespostaHTTP;
    public function get(string $endpoint): RespostaHTTP;
    public function post(string $endpoint, array $data): RespostaHTTP;
    public function delete(string $endpoint, array $data): RespostaHTTP;
    public function upload(string $endpoint, array $data): RespostaHTTP;
    public function patch(string $endpoint, array $data): RespostaHTTP;
    public function put(string $endpoint, array $data): RespostaHTTP;
}