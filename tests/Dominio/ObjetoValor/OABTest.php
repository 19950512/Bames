<?php

declare(strict_types=1);

use App\Dominio\ObjetoValor\OAB;

test('Deverá ser uma OAB válida - OAB/SP 123.456', function () {
    
    $oab = new OAB('OAB/SP 123.456');

    expect($oab->get())->toBe('OAB/SP 123.456');
    expect($oab->getNumero())->toBe('123.456');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - OAB/SP 123456', function () {
    
    $oab = new OAB('OAB/SP 123456');

    expect($oab->get())->toBe('OAB/SP 123.456');
    expect($oab->getNumero())->toBe('123.456');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - SP 123.456', function () {
    
    $oab = new OAB('SP 123.456');

    expect($oab->get())->toBe('OAB/SP 123.456');
    expect($oab->getNumero())->toBe('123.456');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - SP 123456', function () {
    
    $oab = new OAB('SP 123456');

    expect($oab->get())->toBe('OAB/SP 123.456');
    expect($oab->getNumero())->toBe('123.456');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - OAB/SP 123.456.789', function () {
    
    $oab = new OAB('OAB/SP 123.456.789');

    expect($oab->get())->toBe('OAB/SP 123.456.789');
    expect($oab->getNumero())->toBe('123.456.789');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - OAB/SP 123456789', function () {
    
    $oab = new OAB('OAB/SP 123456789');

    expect($oab->get())->toBe('OAB/SP 123.456.789');
    expect($oab->getNumero())->toBe('123.456.789');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - SP 12.78', function () {

    $oab = new OAB('SP 12.078');

    expect($oab->get())->toBe('OAB/SP 12.078');
    expect($oab->getNumero())->toBe('12.078');
    expect($oab->getUF())->toBe('SP');

})->group('OAB');


test('Deverá ser uma OAB válida - 12.778 RS', function () {

    $oab = new OAB('12.778 RS');

    expect($oab->get())->toBe('OAB/RS 12.778');
    expect($oab->getNumero())->toBe('12.778');
    expect($oab->getUF())->toBe('RS');

})->group('OAB');


test('Deverá ser uma OAB inválida', function () {

    $oab = new OAB('OAB/SP 123.456.789');

    expect($oab->get())->not()->toBe('OAB/SP 123.456.7890');
    expect($oab->getNumero())->not()->toBe('123.456.7890');
    expect($oab->getUF())->not()->toBe('SP0');

})->group('OAB');


test('Deverá ser uma OAB inválida - Estado não informado', function () {

    $oab = new OAB('OAB 123.456.789');

})->throws('Estado da OAB inválido.')->group('OAB');

