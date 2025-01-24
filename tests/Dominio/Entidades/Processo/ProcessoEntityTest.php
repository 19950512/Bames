<?php

use App\Dominio\Entidades\Processo\Fontes\Fontes;
use App\Dominio\Entidades\Processo\ProcessoEntity;
use App\Dominio\ObjetoValor\CNJ;
use App\Dominio\ObjetoValor\IdentificacaoUnica;

test('Deverá ser uma instância de ProcessoEntity', function () {
	$processo = new ProcessoEntity(
		codigo: new IdentificacaoUnica(),
		cnj: new CNJ('0053087-35.2013.8.13.0693'),
		dataUltimaMovimentacao: new DateTime('2021-02-01'),
		quantidadeMovimentacoes: 1,
		dataUltimaVerificacao: new DateTime('2021-02-01'),
		fontes: new Fontes()

	);
	expect($processo)->toBeInstanceOf(ProcessoEntity::class);
})
	->group('ProcessoEntity');