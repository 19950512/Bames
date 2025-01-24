<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

final class ArquivoTemporario
{
    public string $message = '';
	public string $extensao;
    public function __construct(
        public readonly string $fullPath,
        public readonly string $name,
        public readonly string $tmpName,
        public readonly int $size,
        public readonly int $error,
        public readonly string $type
    ){
        $this->message = match ($error) {
            UPLOAD_ERR_OK => 'Não há erro, o arquivo foi enviado com sucesso.',
            UPLOAD_ERR_INI_SIZE => 'O arquivo enviado excede a diretiva upload_max_filesize no php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo enviado excede a diretiva MAX_FILE_SIZE especificada no formulário HTML.',
            UPLOAD_ERR_PARTIAL => 'O arquivo enviado foi apenas parcialmente carregado.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Faltando uma pasta temporária.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extenção do PHP parou o upload do arquivo.',
            default => 'Erro desconhecido.'
        };

        $this->extensao = pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function get(): array
    {
        return [
            'fullPath' => $this->fullPath,
            'name' => $this->name,
            'tmpName' => $this->tmpName,
            'size' => $this->size,
            'error' => $this->error,
            'type' => $this->type,
            'message' => $this->message,
            'extensao' => $this->extensao
        ];
    }
}
