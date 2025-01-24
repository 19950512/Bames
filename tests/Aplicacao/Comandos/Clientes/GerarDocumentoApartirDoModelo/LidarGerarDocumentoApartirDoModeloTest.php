<?php

use App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo\ComandoGerarDocumentoApartirDoModelo;
use App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo\LidarGerarDocumentoApartirDoModelo;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Compartilhado\Conversor\Fronteiras\ConteudoPDF;
use App\Aplicacao\Compartilhado\Data\Data;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Entidades\Empresa\Colaboradores\EntidadeResponsavel;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\Endereco\Endereco;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\LinkParaDownload;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Modelos\Fronteiras\SaidaFronteiraModelo;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use App\Infraestrutura\Adaptadores\Docx\ImplementacaoDocx;
use Mockery\Mock;

beforeEach(function(){

    $empresaCodigo = new IdentificacaoUnica('9115556e-9087-4871-899e-d4af8fd150fb');

    $this->implementacaoR2GerenciadorDeArquivos = Mockery::mock(GerenciadorDeArquivos::class)
        ->shouldReceive('salvarArquivo')
        ->andReturn()
        ->getMock()
        ->shouldReceive('obterArquivo')
        ->andReturn(file_get_contents(__DIR__.'/modelo_cliente.docx'))
        ->getMock()
        ->shouldReceive('linkTemporarioParaDownload')
        ->andReturn('https://r2.cloudflarestorage.com/00815010-1f6e-459b-804b-b67c238492e0_modelos_f9b200ab-d7fa-4123-a68e-e9b9f431054f.docx')
        ->getMock()
    ;

    $this->entidadeEmpresarial = new EntidadeEmpresarial(
        codigo: $empresaCodigo,
        apelido: new Apelido('Empresa Teste'),
        numeroDocumento: new CPF('84167670097'),
        endereco: new Endereco(),
        responsavel: new EntidadeResponsavel(
            codigo: new IdentificacaoUnica(),
            nomeCompleto: new NomeCompleto('Responsável Teste'),
            email: new Email('email@para.teste'),
            oab: new OAB('RS 123456'),
        ),
    );

    $this->repositorioClientes = Mockery::mock(RepositorioClientes::class)
        ->shouldReceive('buscarClientePorCodigo')
        ->andReturn(new SaidaFronteiraClienteDetalhado(
            codigo: (new IdentificacaoUnica())->get(),
            nomeCompleto: 'Cliente Teste Mock',
            tipo: 'Cliente',
            email: 'email@para.teste',
            telefone: '51999999999',
            documento: '84167670097',
            dataNascimento: '1990-01-01',
            endereco: 'Olimpio de Azevedo',
            enderecoNumero: '123',
            enderecoComplemento: 'Casa',
            enderecoBairro: 'Centro',
            enderecoCidade: 'Porto Alegre',
            enderecoEstado: 'RS',
            enderecoCep: '90000000',
            nomeMae: 'Mãe Teste Mock',
            cpfMae: '84167670097',
            sexo: 'M',
            nomePai: 'Pai Teste Mock',
            cpfPai: '84167670097',
            rg: '123456789',
            pis: '123456789',
            carteiraTrabalho: '123456789',
            telefones: [],
            emails: [],
            enderecos: [],
            familiares: [],
        ))
        ->getMock()
        ;

    $this->repositorioModelos = Mockery::mock(RepositorioModelos::class)
        ->shouldReceive('obterModeloPorCodigo')
        ->andReturn(new SaidaFronteiraModelo(
            modeloCodigo: (new IdentificacaoUnica())->get(),
            nome: 'Modelo Teste Mock',
            nomeArquivo: '00815010-1f6e-459b-804b-b67c238492e0_modelos_f9b200ab-d7fa-4123-a68e-e9b9f431054f.docx',
        ))
        ->getMock()
    ;

    $this->data = Mockery::mock(Data::class)
        ->shouldReceive('agora')
        ->andReturn(date('Y-m-d H:i:s'))
        ->getMock();

    $this->docx = new ImplementacaoDocx(
        data: $this->data,
    );

    $this->discord = Mockery::mock(Discord::class)
        ->shouldReceive('enviar')
        ->andReturn()
        ->getMock();

    $this->conversorDeArquivo = Mockery::mock(ConversorDeArquivo::class)
        ->shouldReceive('docxToPDF')
        ->andReturn(new ConteudoPDF(
            conteudo: file_get_contents(__DIR__.'/modelo_cliente.pdf'),
        ))
        ->getMock();

    $this->cache = Mockery::mock(Cache::class)
        ->shouldReceive('exist')
        ->andReturn(true)
        ->getMock()
        ->shouldReceive('set')
        ->andReturn()
        ->getMock()
        ->shouldReceive('delete')
        ->andReturn()
        ->getMock()
        ->shouldReceive('get')
        ->andReturn(serialize(new LinkParaDownload(link: 'https://r2.cloudflarestorage.com/00815010-1f6e-459b-804b-b67c238492e0_modelos_f9b200ab-d7fa-4123-a68e-e9b9f431054f.docx')))
        ->getMock()
        ;

    $this->lidarGerarDocumentoApartirDoModelo = new LidarGerarDocumentoApartirDoModelo(
        gerenciadorDeArquivos: $this->implementacaoR2GerenciadorDeArquivos,
        entidadeEmpresarial: $this->entidadeEmpresarial,
        repositorioClientes: $this->repositorioClientes,
        repositorioModelos: $this->repositorioModelos,
        cache: $this->cache,
        conversorDeArquivo: $this->conversorDeArquivo,
        docx: $this->docx,
        discord: $this->discord,
    );
});

describe('(Clientes - Gerar Documento):', function() {
    it('Deverá ser uma instância de Lidar e LidarGerarDocumentoApartirDoModelo', function() {

        expect($this->lidarGerarDocumentoApartirDoModelo)->toBeInstanceOf(LidarGerarDocumentoApartirDoModelo::class)
            ->and($this->lidarGerarDocumentoApartirDoModelo)->toBeInstanceOf(Lidar::class);
    })
        ->group('lidarGerarDocumentoApartirDoModelo');

    it('Deverá gerar um documento apartir do comando sem erros', function() {

        $comando = new ComandoGerarDocumentoApartirDoModelo(
            modeloID: (new IdentificacaoUnica())->get(),
            clienteID: (new IdentificacaoUnica())->get()
        );

        $comando->executar();

        $linkParaDownload = $this->lidarGerarDocumentoApartirDoModelo->lidar($comando);

        expect($linkParaDownload)->toBeInstanceOf(LinkParaDownload::class)
            ->and($linkParaDownload->get())->toBeString()
            ->and($linkParaDownload->get())->not()->toBeEmpty()
            ->and($linkParaDownload->get())->toContain('r2.cloudflarestorage.com');
    })
        ->group('lidarGerarDocumentoApartirDoModelo');
})
    ->group('ClientesGerarDocumentoApartirDoModelo');