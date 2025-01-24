<?php

use App\Dominio\ObjetoValor\Mascara;

test('Deve ser uma instância de Mascara', function(){
	$mascara = new Mascara('12345678901', '###.###.###-##');
	expect($mascara)->toBeInstanceOf(Mascara::class);
})
	->group('Mascara');

test('Deve ser uma mascara válida', function(){
	expect((new Mascara('12345678901', '###.###.###-##'))->get())->toEqual('123.456.789-01')
		->and((new Mascara('12345678901', '###.###.###-##'))->get())->toEqual('123.456.789-01')
		->and((new Mascara('12345678901', '###.###.###-##-'))->get())->toEqual('123.456.789-01-');

})
	->group('Mascara');