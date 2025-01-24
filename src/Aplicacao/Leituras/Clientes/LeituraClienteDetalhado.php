<?php

declare(strict_types=1);

namespace App\Aplicacao\Leituras\Clientes;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Processos\RepositorioProcessos;

final readonly class LeituraClienteDetalhado
{
    public function __construct(
        private RepositorioProcessos $repositorioProcessos,
        private RepositorioClientes $repositorioClientes,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private Ambiente $ambiente,
        private Cache $cache
    ){}

    public function executar(string $empresaCodigo, string $clienteCodigo): array
    {

       $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/clienteDetalhado/$clienteCodigo";
        if($this->cache->exist($keyCache)){
            return unserialize($this->cache->get($keyCache));
        }

        $cliente = $this->repositorioClientes->buscarClientePorCodigo(
            codigoCliente: $clienteCodigo,
            empresaCodigo: $empresaCodigo
        );

        $clienteDetalhes = [
            'codigo' => $cliente->codigo,
            'nomeCompleto' => $cliente->nomeCompleto,
            'documento' => $cliente->documento,
            'email' => $cliente->email,
            'tipo' => $cliente->tipo,
            'telefone' => $cliente->telefone,
            'endereco' => $cliente->endereco,
            'dataNascimento' => $cliente->dataNascimento,
            'sexo' => $cliente->sexo,
            'nomeMae' => $cliente->nomeMae,
            'cpfMae' => $cliente->cpfMae,
            'paiNome' => $cliente->nomePai,
            'cpfPai' => $cliente->cpfPai,
            'rg' => $cliente->rg,
            'pis' => $cliente->pis,
            'carteiraTrabalho' => $cliente->carteiraTrabalho,
            'telefones' => $cliente->obterTelefones(),
            'emails' => $cliente->obterEmails(),
            'enderecos' => $cliente->obterEnderecos(),
            'familiares' => $cliente->obterFamiliares(),
            'cep' => $cliente->enderecoCep,
            'bairro' => $cliente->enderecoBairro,
            'cidade' => $cliente->enderecoCidade,
            'estado' => $cliente->enderecoEstado,
            'complemento' => $cliente->enderecoComplemento,
            'numero' => $cliente->enderecoNumero,
            'logradouro' => $cliente->endereco,
            'drive' => [],
            'eventos' => [],
            'processos' => [] /*$this->repositorioProcessos->obterProcessosDoClientePorDocumento(
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                documento: $cliente->documento
            )->toArray()*/
        ];

        $this->cache->set(
            key: $keyCache,
            value: serialize($clienteDetalhes),
            expireInSeconds: 60 * 60 * 24 // 24 horas
        );

        return $clienteDetalhes;
    }
}