<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor;

use Exception;

readonly final class DocumentoDeIdentificacao implements DocumentoIdentificacao
{

    private DocumentoIdentificacao $documento;

    public function __construct(
        private string $documentoNumero
    ){
        if(CPF::valido($documentoNumero)){
            $this->documento = new CPF($documentoNumero);
        }else if(CNPJ::valido($documentoNumero)) {
            $this->documento = new CNPJ($documentoNumero);
        }else{
            throw new Exception('Documento de identificação inválido');
        }
    }

    public function get(): string
    {
        return $this->documento->get();
    }

    public function tipo(): string
    {
        return is_a($this->documento, CPF::class) ? 'CPF' : 'CNPJ';
    }

    static function valido(string $numeroDocumento): bool
    {
        return CPF::valido($numeroDocumento) || CNPJ::valido($numeroDocumento);
    }
}
