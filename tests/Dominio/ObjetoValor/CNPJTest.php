<?php

use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;

test('Deve ser uma instancia de CNPJ', function () {

    $CNPJ = new CNPJ('44.497.646/0001-00');

    expect($CNPJ)->toBeInstanceOf(CNPJ::class);
})
	->group('CNPJ');

test('Deve ser uma instancia de DocumentoIdentificacao', function () {

    $CNPJ = new CNPJ('44.497.646/0001-00');

    expect($CNPJ)->toBeInstanceOf(DocumentoIdentificacao::class);
})
	->group('CNPJ');


test('Deve ser CNPJ valido.', function(){
    expect((new CNPJ('44.497.646/0001-00'))->get())->toEqual('44.497.646/0001-00')
	    ->and((new CNPJ('66418197000191'))->get())->toEqual('66.418.197/0001-91')
	    ->and((new CNPJ('03325595000143'))->get())->toEqual('03.325.595/0001-43')
	    ->and((new CNPJ('94.197.832/0001-93'))->get())->toEqual('94.197.832/0001-93');

})
	->group('CNPJ');

test('Deve ser um CNPJ invalido.', function(){
    new CNPJ('94.197.832/00201-94');
})
	->throws('O CNPJ informado não é válido. 94.197.832/00201-94')
	->group('CNPJ');

test('Deve gerar um CNPJ Válido', function(){
    $cnpj = new CNPJ(CNPJ::gerar());
    expect($cnpj)->toBeInstanceOf(CNPJ::class);
})
    ->group('CNPJ');