<?php

use App\Dominio\ObjetoValor\Email;

test('Deve ser um email valido.', function (){
    $email = new Email(email: 'mattmaydana@gmail.com');
    expect($email->get())->toEqual('mattmaydana@gmail.com')
	    ->and($email)->toBeInstanceOf(Email::class);
})
	->group('Email');

test('Deve lançar um erro, e-mail inválido', function (){
    $email = new Email(email: 'mattm@ydana@gmail.com');
})
	->throws('O e-mail informado não é válido. (mattm@ydana@gmail.com)')
	->group('Email');

test('Deve colocar todas as letras em caixa baixa', function (){
    $email = new Email(email: 'mAttHeuSzmAYDANA@gmail.COM');
    expect($email->get())->toEqual('mattheuszmaydana@gmail.com');
})
	->group('Email');