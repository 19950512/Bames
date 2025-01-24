<?php

use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Repositorios\Autenticacao\Fronteiras\SaidaFronteiraBuscarContaPorCodigo;

$contaCodigo = (new IdentificacaoUnica())->get();
beforeEach(function() use ($contaCodigo){
    $this->contaCodigo = $contaCodigo;
    $this->empresaCodigo = (new IdentificacaoUnica())->get();
});


test('Deverá ser uma instância de EntidadeUsuarioLogado', function(){

	$params = new SaidaFronteiraBuscarContaPorCodigo(
		empresaCodigo: $this->empresaCodigo,
        contaCodigo: $this->contaCodigo,
        nomeCompleto: 'Ricardao Silva',
        documento: '84167670097',
        email: 'email@para.teste',
        hashSenha: '$argon2i$v=19$m=65536,t=4,p=1$V0pJdm1qTElDNUoyMnVhYQ$456NYptMUfHf1pnB3kwva2u4YkFmlgaGbvaa0sGuU84',
        oab: 'RS 123456'
	);

	$entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($params);

	expect($entidadeUsuarioLogado)->toBeInstanceOf(EntidadeUsuarioLogado::class);
})
	->group('EntidadeUsuarioLogado');

test('O EntidadeUsuarioLogado deve ser nome inválido', function(){

	$params = new SaidaFronteiraBuscarContaPorCodigo(
		empresaCodigo: $this->empresaCodigo,
        contaCodigo: $this->contaCodigo,
        nomeCompleto: 'Ricardao',
        email: 'email@para.teste',
        documento: '84167670097',
        hashSenha: '$argon2i$v=19$m=65536,t=4,p=1$V0pJdm1qTElDNUoyMnVhYQ$456NYptMUfHf1pnB3kwva2u4YkFmlgaGbvaa0sGuU84',
        oab: 'RS 123456',
	);

	$entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($params);
})
	->throws("Colaborador não possui nome completo válido. (Ricardao - Codigo: {$contaCodigo}) - Nome completo informado está inválido. (Ricardao)")
	->group('EntidadeUsuarioLogado-a');


test('O EntidadeUsuarioLogado deve ser e-mail inválido', function() use ($contaCodigo){

	$params = new SaidaFronteiraBuscarContaPorCodigo(
		empresaCodigo: $this->empresaCodigo,
        contaCodigo: $contaCodigo,
        nomeCompleto: 'Ricardao Silva',
        email: 'emailpara.teste.com',
        documento: '84167670097',
        hashSenha: '$argon2i$v=19$m=65536,t=4,p=1$V0pJdm1qTElDNUoyMnVhYQ$456NYptMUfHf1pnB3kwva2u4YkFmlgaGbvaa0sGuU84',
        oab: 'RS 123456'
	);

	$entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($params);
})
	->throws("Colaborador não possui email válido. (emailpara.teste.com - Codigo: {$contaCodigo}) - O e-mail informado não é válido. (emailpara.teste.com)")
	->group('EntidadeUsuarioLogado');