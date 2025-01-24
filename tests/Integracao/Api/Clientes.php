<?php

use App\Infraestrutura\Adaptadores\HTTP\ImplementacaoCurlClienteHTTP;

global $jwt;

if(!is_file(__DIR__.'/../../../.env')) {
    return;
}

$CNJdoProcesso = '';

beforeEach(function(){
    
	$this->clientHTTPAuth = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8052'
	]);
    
	$this->clientHTTPApi = new ImplementacaoCurlClienteHTTP([
		'baseURL' => 'http://localhost:8053'
	]);
});

describe('(Clientes):', function() use (&$jwt){

    it('Deverá retornar a lista de substituições do cliente para o modelo de documento.', function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes/substituicoes');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('{{cliente_nome}}')
            ->and($resposta->body)->toHaveKey('{{cliente_email}}')
            ->and($resposta->body)->toHaveKey('{{cliente_telefone}}')
            ->and($resposta->body)->toHaveKey('{{cliente_documento_numero}}')
            ->and($resposta->body)->toHaveKey('{{cliente_documento_tipo}}')
            ->and($resposta->body)->toHaveKey('{{cliente_profissao}}')
            ->and($resposta->body)->toHaveKey('{{cliente_nacionalidade}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_completo}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_numero}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_bairro}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_cidade}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_estado}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_pais}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_cep}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_complemento}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_referencia}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_localizacao_latitude}}')
            ->and($resposta->body)->toHaveKey('{{cliente_endereco_localizacao_longitude}}')
            ->and($resposta->body)->toHaveKey('{{cliente_data_nascimento}}')
            ->and($resposta->body)->toHaveKey('{{cliente_sexo}}')
            ->and($resposta->body)->toHaveKey('{{cliente_mae_nome}}')
            ->and($resposta->body)->toHaveKey('{{cliente_mae_documento}}')
            ->and($resposta->body)->toHaveKey('{{cliente_pai_nome}}')
            ->and($resposta->body)->toHaveKey('{{cliente_pai_documento}}');

    });

    it("Deverá retornar uma lista de clientes da empresa com 1 clientes.", function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nomeCompleto')
            ->and($resposta->body[0])->toHaveKey('documento')
            ->and($resposta->body[0])->toHaveKey('whatsapp')
            ->and($resposta->body)->toHaveCount(1);

    })->group('Integracao', 'Clientes');

    it("Deverá consultar informações de uma pessoa através do CPF e cadastra-la.", function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->post('/clientes/consultarinformacoesnainternet', [
            'documento' => '61908533072'
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Consulta realizada com sucesso');

    })->group('Integracao', 'Clientes');

    it("Deverá retornar uma lista de clientes da empresa com 2 clientes, um deles com o CPF: 619.085.330-72.", function() use (&$jwt){

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body[0])->toHaveKey('codigo')
            ->and($resposta->body[0])->toHaveKey('nomeCompleto')
            ->and($resposta->body[0])->toHaveKey('documento')
            ->and($resposta->body[0])->toHaveKey('whatsapp')

            ->and($resposta->body)->toHaveCount(2)
            ->and(array_filter($resposta->body, function ($cliente) {
                return $cliente['documento'] === '619.085.330-72';
            }))->toHaveCount(1);

        })->group('Integracao', 'Clientes');

    // Vamos criar um teste para atualizar as informações de um cliente.
    it("Deverá atualizar as informações de um cliente e verificar se elas foram atualizada conforme esperado.", function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        $resposta = $this->clientHTTPApi->get('/clientes');

        $cliente = array_filter($resposta->body, function ($cliente) {
            return $cliente['documento'] === '619.085.330-72';
        });

        $cliente = array_shift($cliente);

        $clienteCodigo = $cliente['codigo'];

        $resposta = $this->clientHTTPApi->get('/clientes/detalhes/'.$clienteCodigo);

        $cliente = $resposta->body;

        $nomeCompletoOriginal = $cliente['nomeCompleto'];
        $nomeCompletoNovo = $cliente['nomeCompleto'] . ' Novo';

        $emailOriginal = $cliente['email'];
        $emailNovo = 'email'.((string) random_int(111,999)).'@email.teste';

        $telefoneOriginal = $cliente['telefone'];
        $telefoneNovo = '(11) 9999-9999';

        $enderecoOriginal = $cliente['endereco'];
        $enderecoNovo = $cliente['endereco'] . ' Novo';

        $numeroOriginal = $cliente['numero'];
        $numeroNovo = '123';

        $complementoOriginal = $cliente['complemento'];
        $complementoNovo = $cliente['complemento'] . ' Novo';

        $bairroOriginal = $cliente['bairro'];
        $bairroNovo = $cliente['bairro'] . ' Novo';

        $cidadeOriginal = $cliente['cidade'];
        $cidadeNovo = $cliente['cidade'] . ' Novo';

        $estadoOriginal = $cliente['estado'];
        $estadoNovo = 'SP';

        $cepOriginal = $cliente['cep'];
        $cepNovo = '12345-678';

        $nomeMaeOriginal = $cliente['nomeMae'];
        $nomeMaeNovo = $cliente['nomeMae'] . ' Novo';

        $sexoOriginal = $cliente['sexo'];
        $sexoNovo = 'F';
        if($sexoOriginal === 'F') $sexoNovo = 'M';

        $dataNascimentoOriginal = $cliente['dataNascimento'];
        $dataNascimentoNovo = '01/01/2000';

        $cpfMaeNovo = '841.676.700-97';

        $resposta = $this->clientHTTPApi->put('/clientes', [
            'id' => $cliente['codigo'],
            'nome' => $nomeCompletoNovo,
            'email' => $emailNovo,
            'telefone' => $telefoneNovo,
            'documento' => $cliente['documento'],
            'dataNascimento' => $dataNascimentoNovo,
            'endereco' => $enderecoNovo,
            'enderecoNumero' => $numeroNovo,
            'enderecoComplemento' => $complementoNovo,
            'enderecoBairro' => $bairroNovo,
            'enderecoCidade' => $cidadeNovo,
            'enderecoEstado' => $estadoNovo,
            'enderecoCep' => $cepNovo,
            'nomeMae' => $nomeMaeNovo,
            'cpfMae' => $cpfMaeNovo,
            'sexo' => $sexoNovo
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Cliente atualizado com sucesso');

        $resposta = $this->clientHTTPApi->get('/clientes/detalhes/'.$clienteCodigo);

        $cliente = $resposta->body;

        expect($cliente['nomeCompleto'])->toBe($nomeCompletoNovo)
            ->and($cliente['nomeCompleto'])->not->toBe($nomeCompletoOriginal)

            ->and($cliente['email'])->toBe($emailNovo)
            ->and($cliente['email'])->not->toBe($emailOriginal)

            ->and($cliente['telefone'])->toBe($telefoneNovo)
            ->and($cliente['telefone'])->not->toBe($telefoneOriginal)

            ->and($cliente['endereco'])->toBe($enderecoNovo)
            ->and($cliente['endereco'])->not->toBe($enderecoOriginal)

            ->and($cliente['numero'])->toBe($numeroNovo)
            ->and($cliente['numero'])->not->toBe($numeroOriginal)

            ->and($cliente['complemento'])->toBe($complementoNovo)
            ->and($cliente['complemento'])->not->toBe($complementoOriginal)

            ->and($cliente['bairro'])->toBe($bairroNovo)
            ->and($cliente['bairro'])->not->toBe($bairroOriginal)

            ->and($cliente['cidade'])->toBe($cidadeNovo)
            ->and($cliente['cidade'])->not->toBe($cidadeOriginal)

            ->and($cliente['estado'])->toBe($estadoNovo)
            ->and($cliente['estado'])->not->toBe($estadoOriginal)

            ->and($cliente['cep'])->toBe($cepNovo)
            ->and($cliente['cep'])->not->toBe($cepOriginal)

            ->and($cliente['nomeMae'])->toBe($nomeMaeNovo)
            ->and($cliente['nomeMae'])->not->toBe($nomeMaeOriginal)

            ->and($cliente['sexo'])->toBe($sexoNovo)
            ->and($cliente['sexo'])->not->toBe($sexoOriginal)

            ->and($cliente['dataNascimento'])->toBe($dataNascimentoNovo)
            ->and($cliente['dataNascimento'])->not->toBe($dataNascimentoOriginal);
    })
    ->group('Integracao', 'Clientes');

    it("Deverá consultar os processos do cliente com o CPF: 619.085.330-72.", function() use (&$jwt) {

        $this->clientHTTPApi->configurar([
            'headers' => [
                'Authorization: Bearer ' . $jwt
            ]
        ]);

        // IDENTIFICA O CLIENTE PELO CPF E VERIFICA SE ELE POSSUI NENHUM PROCESSO
        $resposta = $this->clientHTTPApi->get('/clientes');

        $cliente = array_filter($resposta->body, function ($cliente) {
            return $cliente['documento'] === '619.085.330-72';
        });

        $cliente = array_shift($cliente);

        $resposta = $this->clientHTTPApi->get('/clientes/detalhes/'.$cliente['codigo']);

        $cliente = $resposta->body;

        expect($cliente)->toHaveKey('processos')
            ->and($cliente['processos'])->toBeArray()
            ->and($cliente['processos'])->toHaveCount(0);

        // CONSULTA OS PROCESSOS DO CLIENTE
        $resposta = $this->clientHTTPApi->post('/clientes/consultarProcessos', [
            'documento' => '619.085.330-72'
        ]);

        expect($resposta->code)->toBe(200)
            ->and($resposta->body)->toBeArray()
            ->and($resposta->body)->toHaveKey('message')
            ->and($resposta->body['message'])->toBe('Os processos do cliente foram consultados com sucesso');

        /// AGORA O CLIENTE PRECISA TER 10 PROCESSOS
        $resposta = $this->clientHTTPApi->get('/clientes/processos/'.$cliente['codigo']);

        $processos = $resposta->body;

        expect($processos)->toBeArray()
            ->and($processos)->toHaveCount(10);

        $primeiroProcesso = $processos[0];

        expect($primeiroProcesso)->toHaveKey('codigo')
            ->and($primeiroProcesso)->toHaveKey('numeroCNJ')
            ->and($primeiroProcesso)->toHaveKey('dataUltimaMovimentacao')
            ->and($primeiroProcesso)->toHaveKey('quantidadeMovimentacoes')
            ->and($primeiroProcesso)->toHaveKey('demandante')
            ->and($primeiroProcesso)->toHaveKey('demandado')
            ->and($primeiroProcesso)->toHaveKey('ultimaMovimentacaoData')
            ->and($primeiroProcesso)->toHaveKey('ultimaMovimentacaoDescricao');

    })
    ->group('Integracao', 'Clientes');

})->group('Integracao', 'Clientes');