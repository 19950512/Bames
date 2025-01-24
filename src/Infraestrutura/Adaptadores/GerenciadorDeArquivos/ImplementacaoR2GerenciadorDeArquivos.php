<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\GerenciadorDeArquivos;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Exception;

require __DIR__ . '/../../../../vendor/autoload.php';

readonly class ImplementacaoR2GerenciadorDeArquivos implements GerenciadorDeArquivos
{

    private string $bucket_name;
    private string $account_id;
    private string $access_key_id;
    private string $access_key_secret;

    private S3Client $s3_client;

    public function __construct(
        private Ambiente $ambiente,
    ){
        $this->bucket_name = $this->ambiente->get('R2_BUCKET_NAME');
        $this->account_id = $this->ambiente->get('R2_ACCOUNT_ID');
        $this->access_key_id = $this->ambiente->get('R2_ACCESS_KEY_ID');
        $this->access_key_secret = $this->ambiente->get('R2_ACCESS_KEY_SECRET');

        $credentials = new Credentials($this->access_key_id, $this->access_key_secret);

        $options = [
            'region' => 'auto',
            'endpoint' => "https://$this->account_id.r2.cloudflarestorage.com",
            'version' => 'latest',
            'credentials' => $credentials
        ];

        $this->s3_client = new S3Client($options);
    }
    public function obterArquivo(string $diretorioENomeArquivo, string $empresaCodigo): string
    {
        $result = $this->s3_client->getObject([
            'Bucket' => $this->bucket_name,
            'Key' => $this->obterDiretorioCompletoENomeArquivo($diretorioENomeArquivo, $empresaCodigo)
        ]);

        if(!isset($result['Body'])){
            throw new Exception('Arquivo não encontrado no Gerenciador de Arquivos.');
        }

        return (string) $result['Body'];
    }

    public function listarArquivos(string $diretorio, string $empresaCodigo): array
    {

        $result = $this->s3_client->listObjects([
            'Bucket' => $this->bucket_name,
            'Prefix' => $this->obterDiretorioCompletoENomeArquivo($diretorio, $empresaCodigo)
        ]);

        $arquivos = [];

        if(!isset($result['Contents'])){
            return $arquivos;
        }

        foreach($result['Contents'] as $arquivo){
            $arquivos[] = $arquivo['Key'];
        }

        return $arquivos;
    }

    public function deletarArquivo(string $diretorioENomeArquivo, string $empresaCodigo): void
    {
        $this->s3_client->deleteObject([
            'Bucket' => $this->bucket_name,
            'Key' => $this->obterDiretorioCompletoENomeArquivo($diretorioENomeArquivo, $empresaCodigo)
        ]);
    }

    public function salvarArquivo(EntradaFronteiraSalvarArquivo $parametros): void
    {
        /*
        $pathArmazenamentoDeArquivos = './../../../ArmazenamentoDeArquivos';
        if(!is_dir($pathArmazenamentoDeArquivos)){
            throw new Exception("Diretório de armazenamento de arquivos não encontrado, crie-o e dê permissão www-data.");
        }

        // se $parametros->diretorioENomeArquivo houver uma barra, então é um diretório crie o diretório
        if(strpos($parametros->diretorioENomeArquivo, '/') !== false){
            $diretorio = explode('/', $parametros->diretorioENomeArquivo);
            $diretorio = array_slice($diretorio, 0, -1);
            $diretorio = implode('/', $diretorio);
            if(!is_dir($pathArmazenamentoDeArquivos.$diretorio)){
                mkdir($pathArmazenamentoDeArquivos.$diretorio, 0777, true);
            }
        }

        file_put_contents($pathArmazenamentoDeArquivos.$parametros->diretorioENomeArquivo, $parametros->conteudo);
        */

        $this->s3_client->putObject([
            'Bucket' => $this->bucket_name,
            'Key' => $this->obterDiretorioCompletoENomeArquivo($parametros->diretorioENomeArquivo, $parametros->empresaCodigo),
            'Body' => $parametros->conteudo,
        ]);
    }

    public function linkTemporarioParaDownload(string $diretorioENomeArquivo, string $empresaCodigo, string $expires = '+20 minutes'): string
    {
        $command = $this->s3_client->getCommand('GetObject', [
            'Bucket' => $this->bucket_name,
            'Key' => $this->obterDiretorioCompletoENomeArquivo($diretorioENomeArquivo, $empresaCodigo)
        ]);

        if (strtotime($expires) === false) {
            throw new Exception('Expires inválido');
        }

        $request = $this->s3_client->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }

    public function linkTemporarioParaUpload(string $diretorioENomeArquivo, string $empresaCodigo, string $expires = '+20 minutes'): string
    {
        $command = $this->s3_client->getCommand('PutObject', [
            'Bucket' => $this->bucket_name,
            'Key' => $this->obterDiretorioCompletoENomeArquivo($diretorioENomeArquivo, $empresaCodigo)
        ]);

        if (strtotime($expires) === false) {
            throw new Exception('Expires inválido');
        }

        $request = $this->s3_client->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }

    private function obterDiretorioCompletoENomeArquivo(string $diretorioENomeArquivo, string $empresaCodigo): string
    {
        if(str_starts_with($diretorioENomeArquivo, '/')){
            $diretorioENomeArquivo = substr($diretorioENomeArquivo, 1);
        }

        return $empresaCodigo.'/'.$diretorioENomeArquivo;
    }
}