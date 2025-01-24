<?php

use App\Aplicacao\Compartilhado\Data\Data;
use App\Aplicacao\Compartilhado\Data\DataDefault;
use App\Aplicacao\Compartilhado\Data\ImplementacaoData;

beforeEach(function () {
    $this->data = new ImplementacaoData();
});
test('Deverá ser uma instância de ImplementacaoData, Data e DataDefault', function () {

    expect($this->data)->toBeInstanceOf(ImplementacaoData::class)
        ->and($this->data)->toBeInstanceOf(Data::class)
        ->and($this->data)->toBeInstanceOf(DataDefault::class);
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Domingo', function () {
    $dia = 0;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Domingo');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Segunda-Feira', function () {
    $dia = 1;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Segunda-Feira');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Terça-Feira', function () {
    $dia = 2;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Terça-Feira');
})
    ->group('Data');


test('Deverá retornar o dia da semana completo, Quarta-Feira', function () {
    $dia = 3;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Quarta-Feira');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Quinta-Feira', function () {
    $dia = 4;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Quinta-Feira');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Sexta-Feira', function () {
    $dia = 5;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Sexta-Feira');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Sábado', function () {
    $dia = 6;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Sábado');
})
    ->group('Data');

test('Deverá retornar o dia da semana completo, Inválido', function () {
    $dia = 7;
    $diaSemanaCompleto = $this->data->diaSemanaCompleto($dia);
    expect($diaSemanaCompleto)->toBe('Inválido');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Dom', function () {
    $dia = 0;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Dom');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Seg', function () {
    $dia = 1;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Seg');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Ter', function () {
    $dia = 2;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Ter');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Qua', function () {
    $dia = 3;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Qua');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Qui', function () {
    $dia = 4;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Qui');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Sex', function () {
    $dia = 5;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Sex');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Sáb', function () {
    $dia = 6;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Sáb');
})
    ->group('Data');

test('Deverá retornar o dia da semana abreviado, Inválido', function () {
    $dia = 7;
    $diaSemanaAbreviado = $this->data->diaSemanaAbreviado($dia);
    expect($diaSemanaAbreviado)->toBe('Inválido');
})
    ->group('Data');

test('Deverá retornar o mês completo, Janeiro', function () {
    $mes = 1;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Janeiro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Fevereiro', function () {
    $mes = 2;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Fevereiro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Março', function () {
    $mes = 3;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Março');
})
    ->group('Data');

test('Deverá retornar o mês completo, Abril', function () {
    $mes = 4;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Abril');
})
    ->group('Data');

test('Deverá retornar o mês completo, Maio', function () {
    $mes = 5;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Maio');
})
    ->group('Data');

test('Deverá retornar o mês completo, Junho', function () {
    $mes = 6;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Junho');
})
    ->group('Data');

test('Deverá retornar o mês completo, Julho', function () {
    $mes = 7;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Julho');
})
    ->group('Data');

test('Deverá retornar o mês completo, Agosto', function () {
    $mes = 8;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Agosto');
})
    ->group('Data');

test('Deverá retornar o mês completo, Setembro', function () {
    $mes = 9;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Setembro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Outubro', function () {
    $mes = 10;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Outubro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Novembro', function () {
    $mes = 11;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Novembro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Dezembro', function () {
    $mes = 12;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Dezembro');
})
    ->group('Data');

test('Deverá retornar o mês completo, Inválido', function () {
    $mes = 13;
    $mesCompleto = $this->data->mesCompleto($mes);
    expect($mesCompleto)->toBe('Inválido');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Jan', function () {
    $mes = 1;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Jan');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Fev', function () {
    $mes = 2;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Fev');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Mar', function () {
    $mes = 3;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Mar');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Abr', function () {
    $mes = 4;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Abr');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Mai', function () {
    $mes = 5;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Mai');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Jun', function () {
    $mes = 6;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Jun');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Jul', function () {
    $mes = 7;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Jul');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Ago', function () {
    $mes = 8;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Ago');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Set', function () {
    $mes = 9;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Set');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Out', function () {
    $mes = 10;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Out');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Nov', function () {
    $mes = 11;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Nov');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Dez', function () {
    $mes = 12;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Dez');
})
    ->group('Data');

test('Deverá retornar o mês abreviado, Inválido', function () {
    $mes = 13;
    $mesAbreviado = $this->data->mesAbreviado($mes);
    expect($mesAbreviado)->toBe('Inválido');
})
    ->group('Data');

test('Deverá retornar o ano atual '.date('Y'), function () {
    $ano = $this->data->ano();
    expect($ano)->toBe((int) date('Y'));
})
    ->group('Data');

test('Deverá retornar a data agora '.date('d/m/Y H:i:s'), function () {
    $dia = $this->data->agora();
    expect($dia)->toBe(date('d/m/Y H:i:s'));
})
    ->group('Data');