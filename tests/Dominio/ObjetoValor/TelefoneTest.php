<?php


use App\Dominio\ObjetoValor\Telefone;

test('Deve ser uma instância de Telefone', function(){
    $phone = new Telefone('54984192072');

    expect($phone)->toBeInstanceOf(Telefone::class);
})
	->group('Telefone');

test('Deve ser um número de telefone válido', function(){
    expect((new Telefone('(54)98419-2072'))->get())->toEqual('(54) 98419-2072')
	    ->and((new Telefone('(54)984192072'))->get())->toEqual('(54) 98419-2072')
	    ->and((new Telefone('54984192072'))->get())->toEqual('(54) 98419-2072');
})
	->group('Telefone');
