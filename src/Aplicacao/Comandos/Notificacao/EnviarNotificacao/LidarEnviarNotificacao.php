<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Notificacao\EnviarNotificacao;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Notificacao\Notificacao;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use Exception;
use Override;

final readonly class EnviarNotificacao implements Lidar
{

    public function __construct(
        private Notificacao $notificacao,
        private RepositorioAutenticacao $repositorioAutenticacao,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private Discord $discord
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
        if (!is_a($comando, ComandoEnviarNotificacao::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $this->notificacao->enviar(
                titulo: $comando->obterTitulo(),
                mensagem: $comando->obterMensagem(),
                fcmToken: $comando->obterFCMToken()
            );

        }catch (Exception $erro){
            $this->repositorioAutenticacao->removerFCMTokenInvalido(
                businessId: $this->entidadeEmpresarial->codigo->get(),
                FCMToken: $comando->obterFCMToken()
            );
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Exceptions,
                mensagem: "Ops, não foi possível enviar a notificação e o FCM Token ({$comando->obterFCMToken()}) foi 'deletado'. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível enviar a notificação. {$erro->getMessage()}");
        } finally {
            return;
        }
    }
}
