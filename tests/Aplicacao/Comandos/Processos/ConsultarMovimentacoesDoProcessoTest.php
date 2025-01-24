<?php

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes\ComandoLidarConsultarMovimentacoes;
use App\Aplicacao\Comandos\Processos\ConsultarMovimentacoes\LidarConsultarMovimentacoes;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\Movimentacao;
use App\Aplicacao\Compartilhado\Processos\Fronteiras\SaidaFronteiraMovimentacoesDoProcesso;
use App\Dominio\Entidades\Empresa\Colaboradores\EntidadeResponsavel;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\Apelido;
use App\Dominio\ObjetoValor\CNJ;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\Email;
use App\Dominio\ObjetoValor\Endereco\Endereco;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\Repositorios\Processos\Fronteiras\ProcessoListagem;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

beforeEach(function(){

    $this->entidadeEmpresarial = new EntidadeEmpresarial(
        codigo: new IdentificacaoUnica(),
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

    $movimentacao1 = new Movimentacao(
        id: "23566902301",
        data: "2024-09-23",
        tipo: "ANDAMENTO",
        tipoPublicacao: "",
        classificacaoPreditaNome: "Confirmada",
        classificacaoPreditaDescricao: "A intimação é um ato de comunicação processual que tem como finalidade cientificar a parte acerca de certo ato ocorrido no processo. A intimação eletrônica ocorre por meios digitais, inclusive e-mail, e a confirmação indica que a intimação atingiu sua finalidade, ou seja, a parte tomou ciência daquele ato para o qual foi intimada.",
        classificacaoPreditaHierarquia: "Movimentações do Serventuário > Escrivão\/Diretor de Secretaria\/Secretário Jurídico > Intimação > Eletrônica > Confirmada",
        conteudo: "MINISTÉRIO PÚBLICO DO ESTADO DO RIO GRANDE DO SUL intimado eletronicamente da(o) Despacho \/ Decisão em 23\/09\/2024",
        textoCategoria: "",
        fonteProcessoFonteId: "733906688",
        fonteFonteId: "18025",
        fonteNome: "Superior Tribunal de Justiça",
        fonteTipo: "TRIBUNAL",
        fonteSigla: "STJ",
        fonteGrau: "3",
        fonteGrauFormatado: "Superior"
    );

    $movimentacao2 = new Movimentacao(
        id: "23566902298",
        data: "2024-09-18",
        tipo: "ANDAMENTO",
        tipoPublicacao: "",
        classificacaoPreditaNome: "Protocolo de Petição",
        classificacaoPreditaDescricao: "Indica que a parte protocolou (anexou) alguma petição no processo.",
        classificacaoPreditaHierarquia: "Movimentações do Serventuário > Escrivão\/Diretor de Secretaria\/Secretário Jurídico > Protocolo de Petição",
        conteudo: "Protocolizada Petição 821943\/2024 (CieMPF - CIÊNCIA PELO MPF) em 18\/09\/2024",
        textoCategoria: "",
        fonteProcessoFonteId: "733906688",
        fonteFonteId: "18025",
        fonteNome: "Superior Tribunal de Justiça",
        fonteTipo: "TRIBUNAL",
        fonteSigla: "STJ",
        fonteGrau: "3",
        fonteGrauFormatado: "Superior"
    );

    $saidaMovimentacoes = new SaidaFronteiraMovimentacoesDoProcesso();
    $saidaMovimentacoes->adicionarMovimentacao($movimentacao1);
    $saidaMovimentacoes->adicionarMovimentacao($movimentacao2);

    $this->consultaDeProcesso = Mockery::mock(ConsultaDeProcesso::class)
        ->shouldReceive('obterMovimentacoesDoProcesso')
        ->andReturn($saidaMovimentacoes)
        ->getMock();

    $this->entidadeUsuarioLogado = new EntidadeUsuarioLogado(
        codigo: new IdentificacaoUnica(),
        empresaCodigo: new IdentificacaoUnica(),
        nomeCompleto: new NomeCompleto('Usuário Teste'),
        email: new Email('email@para.teste'),
        emailVerificado: true
    );

    $this->cache = Mockery::mock(Cache::class)
        ->shouldReceive('exist')
        ->andReturn(true)
        ->getMock()
        ->shouldReceive('delete')
        ->andReturn(null)
        ->getMock()
        ->shouldReceive('get')
        ->andReturn('ll')
        ->getMock()
        ->shouldReceive('set')
        ->andReturn()
        ->getMock();

    $this->repositorioProcessos = Mockery::mock(RepositorioProcessos::class)
        ->shouldReceive('movimentacaoNaoExisteAinda')
        ->andReturn(true)
        ->getMock()
        ->shouldReceive('salvarMovimentacaoDoProcesso')
        ->andReturn()
        ->getMock()
        ->shouldReceive('obterProcessoPorCNJ')
        ->andReturn(new ProcessoListagem(
            codigo: (new IdentificacaoUnica())->get(),
            numeroCNJ: (new CNJ('0341163-87.2024.3.00.0000'))->get(),
            dataUltimaMovimentacao: '2024-09-23',
            quantidadeMovimentacoes: 42,
            demandante: 'Ministério Público do Estado do Rio Grande do Sul',
            demandado: 'Matheus Maydana',
            ultimaMovimentacaoData: '2024-09-23',
            ultimaMovimentacaoDescricao: 'Confirmada',
        ))
        ->getMock()
        ->shouldReceive('atualizarTotalMovimentacoesDoProcesso')
        ->andReturn()
        ->getMock()
        ->shouldReceive('atualizarDataUltimaMovimentacaoDoProcesso')
        ->andReturn()
        ->getMock();

});


test('Deverá ser uma instância do comando '. ComandoLidarConsultarMovimentacoes::class.' e ser um '.Comando::class, function(){
    $comando = new ComandoLidarConsultarMovimentacoes(
        CNJ: '12345678901234567'
    );

    expect($comando)->toBeInstanceOf(ComandoLidarConsultarMovimentacoes::class)
        ->and($comando)->toBeInstanceOf(Comando::class);
})
    ->group('ComandoConsultarMovimentacoesDoProcesso');

test('Deverá ser uma instância do Lidar '. LidarConsultarMovimentacoes::class.' e '.Lidar::class, function(){

    $lidar = new LidarConsultarMovimentacoes(
        entidadeEmpresarial: $this->entidadeEmpresarial,
        entidadeUsuarioLogado: $this->entidadeUsuarioLogado,
        consultaDeProcesso: $this->consultaDeProcesso,
        repositorioProcessos: $this->repositorioProcessos,
        cache: $this->cache,
    );

    expect($lidar)->toBeInstanceOf(LidarConsultarMovimentacoes::class)
        ->and($lidar)->toBeInstanceOf(Lidar::class);
})
    ->group('ComandoConsultarMovimentacoesDoProcesso');


test('A '.LidarConsultarMovimentacoes::class.' deverá retornar null - sem erros.', function(){

    $lidar = new LidarConsultarMovimentacoes(
        entidadeEmpresarial: $this->entidadeEmpresarial,
        entidadeUsuarioLogado: $this->entidadeUsuarioLogado,
        consultaDeProcesso: $this->consultaDeProcesso,
        repositorioProcessos: $this->repositorioProcessos,
        cache: $this->cache,
    );

     $comando = new ComandoLidarConsultarMovimentacoes(
        CNJ: '0341163-87.2024.3.00.0000'
    );

     $comando->executar();

    $resposta = $lidar->lidar($comando);

    expect($resposta)->toBeNull();
})
    ->group('ComandoConsultarMovimentacoesDoProcesso');


