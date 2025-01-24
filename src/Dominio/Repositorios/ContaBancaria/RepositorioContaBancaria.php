<?php

declare(strict_types=1);

namespace App\Dominio\Repositorios\ContaBancaria;

use App\Dominio\Repositorios\ContaBancaria\Fronteiras\EntradaFronteiraAtualizarContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraTodasAsContasBancarias;

interface RepositorioContaBancaria
{
    public function buscarAPrimeiraContaBancaria(string $empresaCodigo): SaidaFronteiraContaBancaria;
    public function atualizarContaBancaria(EntradaFronteiraAtualizarContaBancaria $parametros): void;
    public function buscarContaBancariaPorCodigo(string $contaBancariaCodigo, string $empresaCodigo): SaidaFronteiraContaBancaria;
    public function buscarTodasAsContasBancarias(string $empresaCodigo): SaidaFronteiraTodasAsContasBancarias;
    public function criarPrimeiraContaBancaria(string $empresaCodigo, string $contaBancariaCodigo, string $nome, string $banco): void;
    public function novoEvento(string $contaBancariaCodigo, string $empresaCodigo, string $eventoDescricao): void;

    public function atualizarOWebhookCodigoDaContaBancaria(string $contaBancariaCodigo, string $webhookCodigo, string $empresaCodigo): void;

    public function existeWebhookConfiguradoParaConta(string $contaBancariaCodigo, string $empresaCodigo): bool;

    public function verificaAuthenticidadeWebhookAsaas(string $contaBancariaCodigo, string $empresaCodigo, string $webhookCodigo): bool;
}