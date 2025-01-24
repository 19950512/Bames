<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Clientes;

use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;

final readonly class LeituraClientesSubstituicoes
{
    public function executar(): array
    {
        $clienteDados = new SaidaFronteiraClienteDetalhado(
            codigo: (new IdentificacaoUnica())->get(),
            nomeCompleto: 'Matheus Maydana',
            tipo: 'Cliente',
            email: 'matheus@email.com',
            telefone: '51999999999',
            documento: '80395162009',
            dataNascimento: '1995-12-05',
            endereco: 'Santo Marchetto',
            enderecoNumero: '42',
            enderecoComplemento: 'Casa',
            enderecoBairro: 'Centro',
            enderecoCidade: 'Porto Alegre',
            enderecoEstado: 'RS',
            enderecoCep: '08500400',
            nomeMae: 'Maria Maydana da Silva',
            cpfMae: '37375998078',
            sexo: 'M',
            nomePai: 'João Maydana da Silva',
            cpfPai: '80395162009',
            rg: '123456789',
            pis: '123456789',
            carteiraTrabalho: '123456789',
            telefones: [],
            emails: [],
            enderecos: [],
            familiares: []
        );

        $clienteFake = EntidadeCliente::instanciarEntidadeCliente($clienteDados);

        return $clienteFake->subistituicoes();
    }
}
