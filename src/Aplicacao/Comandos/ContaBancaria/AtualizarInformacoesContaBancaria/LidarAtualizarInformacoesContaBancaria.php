<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\ContaBancaria\AtualizarInformacoesContaBancaria;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\EntradaFronteiraAtualizarContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\Fronteiras\SaidaFronteiraContaBancaria;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use Exception;
use Override;

readonly final class LidarAtualizarInformacoesContaBancaria implements Lidar
{
    public function __construct(
        private RepositorioContaBancaria $repositorioContaBancaria,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private Cache $cache
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
        if (!is_a($comando, ComandoAtualizarInformacoesContaBancaria::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $contaBancariaCodigo = $comando->obterCodigoContaBancaria();
        $chaveAPI = $comando->obterChaveAPIContaBancaria();

        try {
            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $contaBancariaCodigo,
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );
            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        } catch (Exception $erro) {
            throw new Exception("Ops, não foi possível obter a conta bancária. {$erro->getMessage()}");
        }


        try {

            $entidadeContaBancariaAtualizadaDados = new SaidaFronteiraContaBancaria(
                codigo: $entidadeContaBancaria->codigo->get(),
                nome: $comando->obterNomeContaBancaria(),
                banco: $entidadeContaBancaria->banco->value,
                ambiente: $comando->obterAmbiente(),
                chaveAPI: $comando->obterChaveAPIContaBancaria(),
                clientIDAPI: $comando->obterClientIDContaBancaria()
            );

            $entidadeContaBancariaAtualizada = EntidadeContaBancaria::instanciarEntidadeContaBancaria($entidadeContaBancariaAtualizadaDados);

        }catch (Exception $erro){
            throw new Exception("Ops, as informações da conta bancária estão inválidas. {$erro->getMessage()}");
        }

        $diferencas = $entidadeContaBancaria->comparar($entidadeContaBancariaAtualizada);

        if(count($diferencas) <= 0){
            return null;
        }

        try {

            $parametrosAtualizarContaBancaria = new EntradaFronteiraAtualizarContaBancaria(
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                nome: $entidadeContaBancariaAtualizada->nome->get(),
                banco: $entidadeContaBancaria->banco->value,
                ambiente: $entidadeContaBancariaAtualizada->ambiente->value,
                chaveAPI: str_contains($entidadeContaBancariaAtualizada->chaveAPI->get(), str_repeat('*',16)) ? $entidadeContaBancaria->chaveAPI->get() : $entidadeContaBancariaAtualizada->chaveAPI->get(),
                clientIDAPI: $entidadeContaBancariaAtualizada->clientIDAPI->get()
            );

            $this->repositorioContaBancaria->atualizarContaBancaria($parametrosAtualizarContaBancaria);

            $keyCache = "{$this->entidadeEmpresarial->codigo->get()}/contaBancariaDetalhada/{$entidadeContaBancaria->codigo->get()}";
            $this->cache->delete($keyCache);

            foreach($diferencas as $propriedade => $diferenca){
                $this->repositorioContaBancaria->novoEvento(
                    contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                    empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                    eventoDescricao: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, atualizou a propriedade {$propriedade} da conta bancária de {$diferenca['antigo']} para {$diferenca['novo']}",
                );
            }

        }catch (Exception $erro){
            throw new Exception("Ops, não foi possível atualizar as informações da conta bancária. {$erro->getMessage()}");
        }

        return null;
    }
}