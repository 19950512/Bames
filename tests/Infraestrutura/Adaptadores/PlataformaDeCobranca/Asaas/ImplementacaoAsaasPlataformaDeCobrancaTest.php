<?php


use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\Entidades\JusiziEntity;
use App\Infraestrutura\Adaptadores\PlataformaDeCobranca\Asaas\ImplementacaoAsaasPlataformaDeCobranca;

beforeEach(function () {

    $this->ambiente = Mockery::mock(Ambiente::class)
        ->shouldReceive('get')
        ->with('APP_DEBUG')
        ->andReturn(true)
        ->getMock();

    $this->discord = Mockery::mock(Discord::class)
        ->shouldReceive('enviar')
        ->andReturn()
        ->getMock();

    $this->jusiziEntity = new JusiziEntity(
        fantasia: 'Jus IZI',
        responsavelNome: 'Matheus Maydana',
        emailComercial: 'matheus@objetivasoftware.com.br',
        responsavelCargo: 'CTO - Chief Technology Officer'
    );
    $this->asaas = new ImplementacaoAsaasPlataformaDeCobranca(
        discord: $this->discord,
        ambiente: $this->ambiente
    );
});

test('Deverá ser uma instância de ImplementacaoAsaasPlataformaDeCobranca', function () {
    expect($this->asaas)->toBeInstanceOf(ImplementacaoAsaasPlataformaDeCobranca::class);
})
    ->group('PlataformaDeCobranca', 'Asaas');

test('Deverá emitir um boleto', function () {

    $parametroBoleto = new EntradaFronteiraEmitirBoleto(
        clientIDAPI: 'jajajaja',
        chaveAPI: 'jajajaja',
        valor: 100.00,
        vencimento: date('Y-m-d', strtotime('+1 day')),
        juros: 1,
        tipoJuros: TipoJuro::PERCENTUAL->value,
        tipoCobranca: MeioPagamento::Boleto->value,
        multa: 2,
        tipoMulta: TipoMulta::PERCENTUAL->value,
        mensagem: 'Mensagem de teste',
        desconto: 0,
        parcelas: 1,
        pagadorNomeCompleto: 'Matheus Maydana',
        pagadorDocumentoNumero: '769.741.670-08',
        pagadorEmail: 'email@para.teste',
        pagadorTelefone: '54984192072',
        contaBancariaAmbienteProducao: false,
    );
    expect($this->asaas->emitirBoleto($parametroBoleto))->toBeInstanceOf(SaidaFronteiraEmitirBoleto::class);
})->group('PlataformaDeCobranca', 'Asaas')->skip('Informe seu ClientID e Chave de API para testar');