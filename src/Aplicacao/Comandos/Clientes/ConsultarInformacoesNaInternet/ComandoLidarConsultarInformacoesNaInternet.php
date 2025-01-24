<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\ConsultarInformacoesNaInternet;

use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use Exception;

final readonly class ComandoLidarConsultarInformacoesNaInternet implements Comando
{

    private string $documentoPronto;
    private string $empresaCodigoPronto;
    private string $usuarioCodigoPronto;

    public function __construct(
        private string $documento,
        private string $empresaCodigo,
        private string $usuarioCodigo,
    ){}

    #[Override] public function executar(): void
    {
        if(empty($this->documento)){
            throw new Exception('O documento precisa ser informado adequadamente.');
        }

        if(empty($this->empresaCodigo)){
            throw new Exception('O código da empresa precisa ser informado adequadamente.');
        }

        if(empty($this->usuarioCodigo)){
            throw new Exception('O código do usuário precisa ser informado adequadamente.');
        }

        try {
            $empresaCodigo = new IdentificacaoUnica($this->empresaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da empresa precisa ser informado adequadamente.");
        }

        try {
            $usuarioCodigo = new IdentificacaoUnica($this->usuarioCodigo);
        }catch (Exception $erro){
            throw new Exception("O código do usuário precisa ser informado adequadamente.");
        }

        if(!CPF::valido($this->documento) && !CNPJ::valido($this->documento)){
            throw new Exception('O documento precisa ser informado adequadamente.');
        }

        if(CPF::valido($this->documento)){
            $documento = new CPF($this->documento);
        }else{
            $documento = new CNPJ($this->documento);
        }

        $this->documentoPronto = $documento->get();
        $this->empresaCodigoPronto = $empresaCodigo->get();
        $this->usuarioCodigoPronto = $usuarioCodigo->get();
    }

    public function obterDocumento(): string
    {
        return $this->documentoPronto;
    }

    public function obterEmpresaCodigo(): string
    {
        return $this->empresaCodigoPronto;
    }

    public function obterUsuarioCodigo(): string
    {
        return $this->usuarioCodigoPronto;
    }

    #[Override]
    public function getPayload(): array
    {
        return [
            'documento' => $this->documento,
            'empresa_codigo' => $this->empresaCodigo,
            'usuario_codigo' => $this->usuarioCodigo
        ];
    }
}
