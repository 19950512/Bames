<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos\MonitorarProcesso;

use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Aplicacao\Comandos\Processos\MonitorarProcesso\ComandoMonitorarProcesso;
use App\Dominio\ObjetoValor\CNJ;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;

final class LidarMonitorarProcesso implements Lidar
{
    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioConsultaDeProcesso $repositorioConsultaDeProcesso,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoMonitorarProcesso::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigo());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {
            $cnj = new CNJ($comando->obterCNJ());
        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        $this->repositorioConsultaDeProcesso->adicionarProcessoParaMonitoramento(
            processoMonitoramentoCodigo: (new IdentificacaoUnica())->get(),
            processoCNJ: $cnj->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get()
        );

        return null;
    }
}