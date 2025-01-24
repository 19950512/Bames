<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento;

use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\ComandoNovoEvento;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;

readonly final class LidarNovoEvento implements Lidar
{
	public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioAutenticacao $repositorioAutenticacao,
        private Mensageria $mensageria,
        private Cache $cache,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoNovoEvento::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigo());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {
            
            $usuarioDados = $this->repositorioAutenticacao->buscarContaPorCodigo($comando->obterUsuarioCodigo());
            $entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        $this->mensageria->publicar(
            evento: Evento::NovoEventoAgenda,
            message: json_encode([
                'titulo' => $comando->obterTituloPronto(),
                'descricao' => $comando->obterDescricaoPronto(),
                'horarioEventoInicio' => $comando->obterHorarioEventoInicioPronto(),
                'horarioEventoFim' => $comando->obterHorarioEventoFimPronto(),
                'recorrencia' => $comando->obterRecorrenciaPronto(),
                'diaTodo' => $comando->obterDiaTodoPronto(),
                'empresaCodigo' => $entidadeEmpresarial->codigo->get(),
                'usuarioCodigo' => $entidadeUsuarioLogado->codigo->get(),
                'notificarPorEmail' => $comando->obterNotificarPorEmail()
            ])
        );

        return null;
    }
}