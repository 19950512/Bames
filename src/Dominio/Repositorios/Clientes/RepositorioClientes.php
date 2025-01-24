<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\Clientes;

use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraAtualizarInformacoesDoCliente;
use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraCadastrarNovoCliente;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClientes;

interface RepositorioClientes
{
    public function getTodosClientes(string $empresaCodigo): SaidaFronteiraClientes;
    public function jaExisteUmClienteComEsteEmailOuDocumento(string $email, string $documento, string $empresaCodigo, string $clienteCodigo): bool;
    public function cadastrarNovoCliente(EntradaFronteiraCadastrarNovoCliente $parametros): void;

    public function buscarClientePorCodigo(string $codigoCliente, string $empresaCodigo): SaidaFronteiraClienteDetalhado;

    public function buscarClientePorDocumento(string $documento, string $empresaCodigo): SaidaFronteiraClienteDetalhado;
    public function jaExisteUmClienteComEsteDocumento(string $documento, string $empresaCodigo): bool;

    public function atualizarInformacoesDoCliente(EntradaFronteiraAtualizarInformacoesDoCliente $parametros): void;

    public function salvarEventosDoCliente(string $codigoCliente, string $empresaCodigo, array $eventos): void;
}