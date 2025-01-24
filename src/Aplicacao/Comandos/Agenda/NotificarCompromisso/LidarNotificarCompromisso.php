<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\NotificarCompromisso;

use DateTime;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\UsuarioSistema;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Dominio\Entidades\Agenda\EntidadeEvento;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Comandos\Agenda\NotificarCompromisso\Resposta;

final class LidarNotificarCompromisso implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioAgenda $repositorioAgenda,
        private Discord $discord,
        private Mensageria $mensageria,
    ){}

    #[Override] public function lidar(Comando $comando): Resposta
    {
        if (!is_a($comando, ComandoNotificarCompromisso::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();
        $codigoEvento = $comando->obterEventoCodigo();

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo);
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NotificarAgenda,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {

            $entidadeEvento = EntidadeEvento::build(
                parametros: $this->repositorioAgenda->buscarEventoPorCodigo(
                    codigo: $codigoEvento,
                    empresaCodigo: $entidadeEmpresarial->codigo->get()
                )
            );
        }catch(Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NotificarAgenda,
                mensagem: "Evento não encontrado. - {$erro->getMessage()}"
            );

            return Resposta::NAO_ENCONTRADO;
        }

        try {
            $usuarioData = $this->repositorioEmpresa->buscarUsuarioPorCodigo(
                contaCodigo: $entidadeEvento->usuarioCodigo->get()
            );
            $usuarioSistema = UsuarioSistema::build($usuarioData);
        }catch(Exception $erro){
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        $agora = new DateTime();

        if($entidadeEvento->horarioInicio < $agora) {
            
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NotificarAgenda,
                mensagem: "Evento já passou."
            );

            return Resposta::JA_PASSOU;
        }

        // Verificação de evento agendado para "agora" (dentro de 5 minutos)
        if ($agora->diff($entidadeEvento->horarioInicio)->i <= 5) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NotificarAgenda,
                mensagem: "Evento em andamento ou prestes a começar: {$entidadeEvento->titulo} - {$entidadeEvento->horarioInicio->format('d/m/Y H:i')}"
            );

            $assuntoEmail = "{$usuarioSistema->nomeCompleto->get()}, lembrando do seu compromisso - {$entidadeEvento->titulo}";
            $mensagemEmail = <<<html
            <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="description" content="$assuntoEmail">
                    <title>$assuntoEmail</title>
                </head>
                <body>
                    <p>Olá {$usuarioSistema->nomeCompleto->get()},</p>
                    <p>Este é um lembrete do seu compromisso:</p>
                    <p><strong>Evento:</strong> {$entidadeEvento->titulo}</p>
                    <p><strong>Descrição:</strong> {$entidadeEvento->descricao}</p>
                    <p><strong>Horário:</strong> {$entidadeEvento->horarioInicio->format('d/m/Y H:i')}</p>
                    <p>Atenciosamente,</p>
                    <p>Equipe de suporte</p>
                </body>
            </html>
            html;

            $this->mensageria->publicar(
                evento: Evento::EnviarEmail,
                message: json_encode([
                    'destinatarioEmail' => $usuarioSistema->email->get(),
                    'destinatarioNome' => $usuarioSistema->nomeCompleto->get(),
                    'assunto' => $assuntoEmail,
                    'mensagem' => $mensagemEmail
                ])
            );
    
            return Resposta::NOTIFICADO;
        }

        return Resposta::AINDA_NAO_E_HORA;
    }
}
