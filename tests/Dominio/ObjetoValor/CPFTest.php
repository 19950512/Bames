<?php

use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;

test('Deve ser uma instância de CPF', function () {

    $CPF = new CPF('841.676.700-97');

    expect($CPF)->toBeInstanceOf(CPF::class);
})
	->group('CPF');

test('Deve ser uma instância de DocumentoIdentificacao', function () {

    $CPF = new CPF('841.676.700-97');

    expect($CPF)->toBeInstanceOf(DocumentoIdentificacao::class);
})
	->group('CPF');

test('Deve ser CPF valido.', function(){
    expect((new CPF('841.676.700-97'))->get())->toEqual('841.676.700-97')
	    ->and((new CPF('84167670097'))->get())->toEqual('841.676.700-97')
	    ->and((new CPF('1234567890'))->get())->toEqual('123.456.789-0')
	    ->and((new CPF('123.456.789-0'))->get())->toEqual('123.456.789-0');

})
	->group('CPF');

test('Deve ser um CPF inválido.', function(){
    new CPF('841.676.700-99');
})
	->throws('O CPF informado não é váliodo. 841.676.700-99')
	->group('CPF');

test('Deve ser um CPF inválido. (11111111111, 22222222222 ... 99999999999)', function(){
	expect(CPF::valido('11111111111'))->toBeFalse()
		->and(CPF::valido('22222222222'))->toBeFalse()
		->and(CPF::valido('33333333333'))->toBeFalse()
		->and(CPF::valido('44444444444'))->toBeFalse()
		->and(CPF::valido('55555555555'))->toBeFalse()
		->and(CPF::valido('66666666666'))->toBeFalse()
		->and(CPF::valido('77777777777'))->toBeFalse()
		->and(CPF::valido('88888888888'))->toBeFalse()
		->and(CPF::valido('99999999999'))->toBeFalse()
		->and(CPF::valido('999999999999'))->toBeFalse()
		->and(CPF::valido(''))->toBeFalse();
})
	->group('CPF');
