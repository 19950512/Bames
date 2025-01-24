<?php

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Infraestrutura\Adaptadores\GerenciadorDeArquivos\ImplementacaoR2GerenciadorDeArquivos;

$empresaCodigo = (new IdentificacaoUnica())->get();

beforeEach(function(){
    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('R2_BUCKET_NAME')
        ->andReturn('jusizi')
        ->getMock()
        ->shouldReceive('get')
        ->with('R2_ACCOUNT_ID')
        ->andReturn('ac2eb7e5c09270f176d3958a5550eee0')
        ->getMock()
        ->shouldReceive('get')
        ->with('R2_ACCESS_KEY_ID')
        ->andReturn('65849e361a6177e390b7139ee0db4ced')
        ->getMock()
        ->shouldReceive('get')
        ->with('R2_ACCESS_KEY_SECRET')
        ->andReturn('6e50fc077a7837ab37505d18f6143cf1fcc05c356158cd5b9ad24d2e9142f0b3')
        ->getMock()
        ;

    $this->implementacaoR2GerenciadorDeArquivos = new ImplementacaoR2GerenciadorDeArquivos(
        ambiente: $this->ambiente
    );
});

describe('(R2 Implementação GerenciadorDeArquivos):', function() use (&$empresaCodigo){

    it('Deverá ser uma instância de GerenciadorDeArquivos e ImplementacaoR2GerenciadorDeArquivos', function(){

        expect($this->implementacaoR2GerenciadorDeArquivos)->toBeInstanceOf(ImplementacaoR2GerenciadorDeArquivos::class)
            ->toBeInstanceOf(GerenciadorDeArquivos::class);
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('O bucket deverá retornar uma lista vazia de arquivos, pois não há arquivos.', function() use (&$empresaCodigo){

        $arquivos = $this->implementacaoR2GerenciadorDeArquivos->listarArquivos(
            diretorio: '',
            empresaCodigo: $empresaCodigo
        );
        expect($arquivos)->toBeArray()
            ->toHaveCount(0);
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá salvar um arquivo no bucket.', function() use (&$empresaCodigo){

        $parametrosSalvarAruivo = new EntradaFronteiraSalvarArquivo(
            diretorioENomeArquivo: '/R2-testes/arquivo.txt',
            conteudo: 'Conteúdo do arquivo.',
            empresaCodigo: $empresaCodigo,
        );
        $this->implementacaoR2GerenciadorDeArquivos->salvarArquivo($parametrosSalvarAruivo);

        expect($this->implementacaoR2GerenciadorDeArquivos->listarArquivos(
            diretorio: '',
            empresaCodigo: $empresaCodigo
        ))->toBeArray()
            ->toHaveCount(1);
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá listar os arquivos do bucket e encontrar 1 arquivo.', function() use (&$empresaCodigo){

        $arquivos = $this->implementacaoR2GerenciadorDeArquivos->listarArquivos(
            diretorio: '',
            empresaCodigo: $empresaCodigo
        );

        expect($arquivos)->toBeArray()
            ->toHaveCount(1)
            ->and($arquivos[0])->toContain('arquivo.txt');
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá deletar um arquivo do bucket listar e encontrar 0 arquivos.', function() use (&$empresaCodigo){

        $this->implementacaoR2GerenciadorDeArquivos->deletarArquivo(
            diretorioENomeArquivo: '/R2-testes/arquivo.txt',
            empresaCodigo: $empresaCodigo
        );

        expect($this->implementacaoR2GerenciadorDeArquivos->listarArquivos(
            diretorio: '',
            empresaCodigo: $empresaCodigo
        ))->toBeArray()
            ->toHaveCount(0);
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá retornar uma URL temporária para download do arquivo.', function() use (&$empresaCodigo){

        $parametrosSalvarAruivo = new EntradaFronteiraSalvarArquivo(
            diretorioENomeArquivo: '/R2-testes/arquivo-temporario.txt',
            conteudo: 'Conteúdo do arquivo do TEMPORARIO.',
            empresaCodigo: $empresaCodigo,
        );
        $this->implementacaoR2GerenciadorDeArquivos->salvarArquivo($parametrosSalvarAruivo);

        $url = $this->implementacaoR2GerenciadorDeArquivos->linkTemporarioParaDownload(
            diretorioENomeArquivo: '/R2-testes/arquivo-temporario.txt',
            empresaCodigo: $empresaCodigo
        );

        expect($url)->toBeString()
            ->toContain('r2.cloudflarestorage.com')
            ->toContain('/R2-testes/arquivo-temporario.txt');
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá deletar um arquivo temporário do bucket e listar os arquivo e encontrar 0 arquivos.', function() use (&$empresaCodigo){

        $this->implementacaoR2GerenciadorDeArquivos->deletarArquivo(
            diretorioENomeArquivo: '/R2-testes/arquivo-temporario.txt',
            empresaCodigo: $empresaCodigo
        );

        expect($this->implementacaoR2GerenciadorDeArquivos->listarArquivos(
            diretorio: '',
            empresaCodigo: $empresaCodigo
        ))->toBeArray()
            ->toHaveCount(0);
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');

    it('Deverá retornar uma URL temporária para upload do arquivo.', function() use (&$empresaCodigo){

        $url = $this->implementacaoR2GerenciadorDeArquivos->linkTemporarioParaUpload(
            diretorioENomeArquivo: '/R2-testes/pastinha-do-upload',
            empresaCodigo: $empresaCodigo
        );

        expect($url)->toBeString()
            ->toContain('r2.cloudflarestorage.com/'.$empresaCodigo.'/R2-testes/pastinha-do-upload');
    })
        ->group('ImplementacaoR2GerenciadorDeArquivos');
})
    ->group('GerenciadorDeArquivos', 'ImplementacaoR2GerenciadorDeArquivos');


