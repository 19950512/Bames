<?php

use App\Dominio\Entidades\Processo\Fontes\EntidadeCapa;
use App\Dominio\Entidades\Processo\Fontes\EntidadeFonte;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\EntidadeEnvolvido;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Natureza;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Polo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Enumerados\Tipo;
use App\Dominio\Entidades\Processo\Fontes\Envolvidos\Envolvidos;
use App\Dominio\Entidades\Processo\Fontes\Fontes;
use App\Dominio\Entidades\Tribunal\EntidadeTribunal;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\Entidades\Processo\Fontes\Enumerados\Grau;
use App\Dominio\ObjetoValor\URL;
use App\Dominio\Entidades\Processo\Fontes\Enumerados\Tipo as TipoFontes;
use App\Dominio\ObjetoValor\Valor;

test('Deverá ser uma instância de Fontes', function () {
	$fontes = new Fontes();
	expect($fontes)->toBeInstanceOf(Fontes::class);
})
	->group('Fontes');

test('Deverá ser um array de fontes', function () {

	$fontes = new Fontes();

	$envolvidos = new Envolvidos();

	$envolvidos->add(
		new EntidadeEnvolvido(
			codigo: new IdentificacaoUnica(),
			nomeCompleto: new NomeCompleto('Nome Teste'),
			quantidadeProcessos: 2,
			tipoNatureza: Natureza::Fisica,
			documento: new CPF('1234567890'),
			tipo: Tipo::Apelado,
			polo: Polo::Ativo
		)
	);

	$fonte = new EntidadeFonte(
		codigo: new IdentificacaoUnica(),
		id: new TextoSimples('123'),
		descricao: new TextoSimples('Fonte Teste'),
		nome: new TextoSimples('Fonte Teste'),
		sigla: new TextoSimples('FT'),
		tipo: TipoFontes::Tribunal,
		dataInicio: new DateTime(),
		dataUltimaMovimentacao: new DateTime(),
		segredoJustica: false,
		arquivado: false,
		fisico: true,
		sistema: new TextoSimples('Sistema Teste'),
		grau: Grau::Primeiro,
		capaEntity: new EntidadeCapa(
			classe: new TextoSimples('Classe Teste'),
			assunto: new TextoSimples('Assunto Teste'),
			assuntoNormalizado: new TextoSimples('Assunto Normalizado Teste'),
			area: new TextoSimples('Área Teste'),
			orgaoJulgador: new TextoSimples('Órgão Julgador Teste'),
			causaValor: new Valor(5000.00),
			causaMoeda: new TextoSimples('BRL'),
			dataDistribuicao: new DateTime(),
			dataArquivamento: new DateTime(),
			informacoesComplementares: []
		),
		url: new URL('http://teste.com'),
		tribunalEntity: new EntidadeTribunal(
			codigo: new IdentificacaoUnica(),
			codigoTribunal: new TextoSimples('Tribunal Teste'),
			nome: new TextoSimples('Tribunal Teste'),
			sigla: new TextoSimples('TT'),
		),
		quantidadeMovimentacoes: 2,
        quantidadeEnvolvidos: 1,
		dataUltimaVerificacao: new DateTime(),
		envolvidos: $envolvidos
	);

	expect($fontes->get())->toBeArray();
})
	->group('Fontes');