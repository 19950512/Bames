<?php


use App\Aplicacao\Compartilhado\Data\Data;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\EntradaFronteiraSubistituirConteudo;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\SaidaFronteiraSubistituirConteudo;
use App\Infraestrutura\Adaptadores\Docx\ImplementacaoDocx;

beforeEach(function(){
    $this->data = Mockery::mock(Data::class)
        ->shouldReceive('agora')
        ->andReturn('2021-09-01 00:00:00')
        ->getMock();
});

describe('(Docx - Substituição de informações):', function() {

    it('Deverá ser uma instância de Docx', function(){
        $docx = new ImplementacaoDocx(
            data: $this->data
        );
        expect($docx)->toBeInstanceOf(ImplementacaoDocx::class);
    })
        ->group('ImplementacaoDocx');

    it('Deverá substituir o conteúdo do arquivo modelo_cliente.docx.', function(){

        $docx = new ImplementacaoDocx(
            data: $this->data
        );
        $parametros = new EntradaFronteiraSubistituirConteudo(
            conteudoDoArquivoDocx: file_get_contents(__DIR__.'/modelo_cliente.docx'),
            subistituicoes: [
                'cliente_nome' => 'Cliente Teste Mock',
                'cliente_documento' => '84167670097',
                'cliente_email' => 'email@para.teste',
                'cliente_telefone' => '51999999999',
            ]
        );
        $saida = $docx->substituirConteudo($parametros);

        $zip = new ZipArchive();
        $zip->open($saida->caminho);
        $zip->extractTo(__DIR__.'/word_extracted');
        $zip->close();

        $conteudo = file_get_contents(__DIR__.'/word_extracted/word/document.xml');

        expect($conteudo)->toContain('Cliente Teste Mock')
            ->toContain('84167670097')
            ->toContain('email@para.teste')
            ->toContain('51999999999')
            ->and($saida)->toBeInstanceOf(SaidaFronteiraSubistituirConteudo::class);

    })
        ->group('ImplementacaoDocx');
})
    ->group('Infraestrutura', 'Adaptadores', 'Docx', 'ImplementacaoDocx');