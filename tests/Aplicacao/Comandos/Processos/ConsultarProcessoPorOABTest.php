<?php

declare(strict_types=1);

use App\Aplicacao\Comandos\Processos\ComandoLidarConsultasProcessoPorOAB;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\JusiziEntity;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Comandos\Processos\LidarConsultarProcessoPorOAB;
use App\Dominio\Repositorios\Empresa\Fronteiras\SaidaFronteiraEmpresa;
use App\Infraestrutura\Adaptadores\Processos\ImplementacaoConsultaDeProcessoEscavador;
use App\Aplicacao\Comandos\Processos\Fronteiras\EntradaFronteiraConsultarProcessoPorOAB;

beforeEach(function(){

    $this->empresaCodigo = (new IdentificacaoUnica())->get();
    $this->usuarioCodigo = (new IdentificacaoUnica())->get();

    $this->discord = Mockery::mock(Discord::class)
        ->shouldReceive('enviar')
        ->andReturn()
        ->getMock();
    
    $this->repositorioRequest = Mockery::mock(RepositorioRequest::class)
        ->shouldReceive('oi')
        ->andReturn('')
        ->getMock();

    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('API_ESCAVADOR_ACCESS_TOKEN')
        ->andReturn('token-aqui')
        ->getMock()
        ->shouldReceive('get')
        ->with('APP_DEBUG')
        ->andReturn(true)
        ->getMock();

    $saidaEmpresa = new SaidaFronteiraEmpresa(
        empresaCodigo: $this->empresaCodigo,
        nome: 'Teste Empresa Mock',
        numeroDocumento: '03623589000172',
        responsavelCodigo: $this->usuarioCodigo,
        responsavelOAB: 'RS 109.291',
        responsavelNomeCompleto: 'Teste Responsável',
        responsavelEmail: 'emal@para.teste',
        acessoNaoAutorizado: false,
        acessoNaoAutorizadoMotivo: '',
        colaboradores: [
            [
                'codigo' => $this->usuarioCodigo,
                'nome_completo' => 'Teste Responsável',
                'email' => 'email@para.teste'
            ]
        ]
    );

    $this->repositorioEmpresa = Mockery::mock(RepositorioEmpresa::class)
        ->shouldReceive('buscarEmpresaPorCodigo')
        ->andReturn($saidaEmpresa)
        ->getMock();

    $this->email = Mockery::mock(Email::class)
        ->shouldReceive('oi')
        ->andReturn('')
        ->getMock();

    $this->repositorioConsultaDeProcesso = Mockery::mock(RepositorioConsultaDeProcesso::class)
        ->shouldReceive('salvarRequestPorOAB')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('atualizaORequestPorOABResponseERequest')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('movimentacaoNaoExisteAinda')
        ->andReturn(true)
        ->getMock()
        ->shouldReceive('OABJaFoiConsultadaNosUltimosDias')
        ->andReturn(false)
        ->getMock()
        ->shouldReceive('salvarProcesso')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('salvaEvento')
        ->andReturn('')
        ->getMock()
        ->shouldReceive('atualizaORequestPorOABParaFinalizado')
        ->andReturn('')
        ->getMock();

    $this->jusiziEntity = new JusiziEntity(
        fantasia: 'Jus IZI',
        responsavelNome: 'Matheus Maydana',
        emailComercial: 'matheus@objetivasoftware.com.br',
        responsavelCargo: 'CTO - Chief Technology Officer'
    );

    $this->consultaDeProcesso = new ImplementacaoConsultaDeProcessoEscavador(
        ambiente: $this->ambiente
    );
    
    $this->consultarProcessoPorOAB = new LidarConsultarProcessoPorOAB(
        consultaDeProcesso: $this->consultaDeProcesso,
        repositorioConsultaDeProcesso: $this->repositorioConsultaDeProcesso,
        repositorioEmpresa: $this->repositorioEmpresa,
        discord: $this->discord
    );
});

test('Deverá ser uma instancia de ConsultarProcessoPorOAB', function(){
    expect($this->consultarProcessoPorOAB)->toBeInstanceOf(LidarConsultarProcessoPorOAB::class);
})->group('ConsultarProcessoPorOAB');

test('Deverá retornar um erro ao tentar executar com Estado da OAB inválido', function(){

    $parametros = new ComandoLidarConsultasProcessoPorOAB(
        OAB: 'OAB123456',
        empresaCodigo: $this->empresaCodigo,
        usuarioCodigo: $this->usuarioCodigo
    );

    $parametros->executar();

    $this->consultarProcessoPorOAB->lidar($parametros);
})
    ->throws('Estado da OAB inválido.')
    ->group('ConsultarProcessoPorOAB');

test('Deverá consultar processos de uma OAB válida - Mas não terá acesso a API da plataforma', function(){

    $parametros = new ComandoLidarConsultasProcessoPorOAB(
        OAB: 'RS 109.291',
        empresaCodigo: $this->empresaCodigo,
        usuarioCodigo: $this->usuarioCodigo
    );

    $parametros->executar();

    expect($this->consultarProcessoPorOAB->lidar($parametros))->toBeNull();
})
    ->throws('Unauthenticated')
    ->group('ConsultarProcessoPorOAB')
    ->skip('Não testar em ambiente de desenvolvimento');