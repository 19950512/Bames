<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\Eventos\AtualizarEvento;

use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use DateTime;
use Exception;
use Override;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Evento;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento as EventoMensageria;
use App\Dominio\ObjetoValor\Descricao;
use App\Dominio\Entidades\JusiziEntity;
use App\Dominio\ObjetoValor\AccessToken;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Aplicacao\Compartilhado\Agenda\Agenda;
use App\Dominio\Entidades\Agenda\EntidadeEvento;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento;
use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento as EntradaFronteiraAtualizarEventoAgenda;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;

readonly final class LidarAtualizarEvento implements Lidar
{
	public function __construct(
        private RepositorioRequest $repositorioRequest,
        private RepositorioEmail $repositorioEmail,
        private Email $email,
        private JusiziEntity $jusiziEntity,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private AccessToken $accessToken,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private RepositorioAgenda $repositorioAgenda,
        private Agenda $agenda,
        private Mensageria $mensageria,
        private Cache $cache
    ){}

    #[Override] public function lidar(Comando $comando): string
    {

        if (!is_a($comando, ComandoAtualizarEvento::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeEmpresarial->codigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        try {

            $entidadeEventoData = $this->repositorioAgenda->buscarEventoPorCodigo(
                codigo: $comando->obterEventoCodigoPronto(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );
    
            $entidadeEvento = EntidadeEvento::build($entidadeEventoData);
        
        }catch(Exception $erro){
                
            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou buscar o evento com o código: {$comando->obterEventoCodigoPronto()}, mas ocorreu um erro: {$erro->getMessage()}");
            $eventosDoRequest->adicionar($novoEventoRequest);
            
            $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
                comandoPayload: json_encode($comando->getPayload()),
                comando: $comando::class,
                usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
                businessId: $this->entidadeUsuarioLogado->empresaCodigo->get(),
                requestCodigo: $eventosDoRequest->requestCodigo->get(),
                momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                totalEventos: count($eventosDoRequest->get()),
                eventos: $eventosDoRequest->getArray(),
                accessToken: $this->accessToken->get()
            );

            $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);

            throw new Exception("Ops, não foi possível buscar o evento com o código: {$comando->obterEventoCodigoPronto()}");
        }

        $this->cache->delete("{$this->entidadeEmpresarial->codigo->get()}/meuscompromissos/{$this->entidadeUsuarioLogado->codigo->get()}");
        $this->cache->delete("{$this->entidadeEmpresarial->codigo->get()}/compromisso/{$entidadeEvento->codigo->get()}");


        // Aqui acontece o update no objeto.
        $entidadeEvento->titulo = new Descricao($comando->obterTituloPronto());
        $entidadeEvento->descricao = new Descricao($comando->obterDescricaoPronto());
        $entidadeEvento->horarioInicio = new DateTime($comando->obterHorarioEventoInicioPronto());
        $entidadeEvento->horarioFim = new DateTime($comando->obterHorarioEventoFimPronto());
        $entidadeEvento->diaTodo = $comando->obterDiaTodoPronto();
        $entidadeEvento->recorrencia = $comando->obterRecorrenciaPronto();

        try {
            
            $parametrosCriarEvento = new EntradaFronteiraAtualizarEventoAgenda(
                eventoCodigo: $entidadeEvento->codigo->get(),
                titulo: $entidadeEvento->titulo->get(),
                descricao: $entidadeEvento->descricao->get(),
                diaTodo: $entidadeEvento->diaTodo,
                recorrencia: $entidadeEvento->recorrencia,
                horarioInicio: $entidadeEvento->horarioInicio->format('Y-m-d H:i:s'),
                horarioFim: $entidadeEvento->horarioFim->format('Y-m-d H:i:s'),
            );
            
            $this->agenda->atualizarEvento($parametrosCriarEvento);

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, atualizou o evento com o código: {$comando->obterEventoCodigoPronto()}");
            $eventosDoRequest->adicionar($novoEventoRequest);
        
        }catch(Exception $erro){

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou atualizar o evento com o código: {$comando->obterEventoCodigoPronto()}, mas ocorreu um erro: {$erro->getMessage()}");
            $eventosDoRequest->adicionar($novoEventoRequest);
        }

        try {

            $parametrosSalvar = new EntradaFronteiraAtualizarEvento(
                codigo: $entidadeEvento->codigo->get(),
                business_id: $entidadeEvento->empresaCodigo->get(),
                usuario_id: $entidadeEvento->usuarioCodigo->get(),
                titulo: $entidadeEvento->titulo->get(),
                descricao: $entidadeEvento->descricao->get(),
                dataInicio: $entidadeEvento->horarioInicio->format('Y-m-d H:i:s.uP'),
                dataFim: $entidadeEvento->horarioFim->format('Y-m-d H:i:s.uP'),
                momento: $entidadeEvento->criadoEm->format('Y-m-d H:i:s.uP'),
                status: $entidadeEvento->status,
                plataforma_evento_id: $entidadeEvento->agendaID,
                diaTodo: $entidadeEvento->diaTodo,
                recorrencia: $entidadeEvento->recorrencia
            );
            
            $this->repositorioAgenda->atualizarEvento($parametrosSalvar);

        }catch(Exception $erro){

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, tentou atualizar o evento com o código: {$comando->obterEventoCodigoPronto()}, mas ocorreu um erro: {$erro->getMessage()}");
            $eventosDoRequest->adicionar($novoEventoRequest);
        }

        $assuntoEmail = "Evento atualizado com sucesso - {$entidadeEvento->titulo->get()}";

        $mensagemCriadoNaAgenda = "com sucesso";
        if(!empty($entidadeEvento->agendaID)){
            $mensagemCriadoNaAgenda = "com sucesso e o evento foi atualizado na agenda";
        }
        $mensagemEmail = <<<htmlMensagemEmail
        Olá {$this->entidadeUsuarioLogado->nomeCompleto->get()},
        <br />
        <p>O evento {$entidadeEvento->titulo->get()} foi atualizado {$mensagemCriadoNaAgenda}.</p>
        <br />
        <p>Segue abaixo os detalhes do evento agora:</p>
        <br />
        <p><strong>Titulo:</strong> {$entidadeEvento->titulo->get()}</p>
        <p><strong>Descrição:</strong> {$entidadeEvento->descricao->get()}</p>
        <p><strong>Data de Início:</strong> {$entidadeEvento->horarioInicio->format('d/m/Y')} às {$entidadeEvento->horarioInicio->format('H:i:s')}</p>
        <p><strong>Data de Fim:</strong> {$entidadeEvento->horarioFim->format('d/m/Y')} às {$entidadeEvento->horarioFim->format('H:i:s')}</p>
        <p><strong>Recorrência:</strong> {$entidadeEvento->recorrencia}</p>
        <br />
        <p>Se precisar de mais informações, entre em contato conosco.</p>
        <br />
        <p>Cumprimentos,</p>
        <br />
        {$this->jusiziEntity->responsavelNome}<br/>
        {$this->jusiziEntity->responsavelCargo}<br/>
        {$this->jusiziEntity->fantasia}<br/>
        htmlMensagemEmail;

        $this->mensageria->publicar(
            evento: EventoMensageria::EnviarEmail,
            message: json_encode([
                'destinatarioEmail' => $this->entidadeUsuarioLogado->email->get(),
                'destinatarioNome' => $this->entidadeUsuarioLogado->nomeCompleto->get(),
                'assunto' => $assuntoEmail,
                'mensagem' => $mensagemEmail
            ])
        );

        $novoEventoRequest = new Evento("Enviamos um e-mail confirmando a atualização do evento para o e-mail: {$this->entidadeUsuarioLogado->email->get()} do usuário: {$this->entidadeUsuarioLogado->nomeCompleto->get()}");
        $eventosDoRequest->adicionar($novoEventoRequest);

        /*
         TODO: ISSO DEVERÁ ESTÁR NO WORKER DO EMAIL.
        $parametrosEmailEnviado = new FronteiraEntradaSalvarEmailEnviado(
            emailCodigo: $emailCodigo->emailCodigo,
            assunto: $assuntoEmail,
            mensagem: $mensagemEmail,
            destinatarioNome: $this->entidadeUsuarioLogado->nomeCompleto->get(),
            destinatarioEmail: $this->entidadeUsuarioLogado->email->get(),
            empresaID: $this->entidadeUsuarioLogado->empresaCodigo->get(),
            situacao: 'Enviado'
        );

        $this->repositorioEmail->salvarEmailEnviado($parametrosEmailEnviado);
        */

        $parametrosSalvarEventoRequest = new EntradaFronteiraSalvarEventosDoRequest(
            comandoPayload: json_encode($comando->getPayload()),
            comando: $comando::class,
            usuarioId: $this->entidadeUsuarioLogado->codigo->get(),
            businessId: $this->entidadeUsuarioLogado->empresaCodigo->get(),
            requestCodigo: $eventosDoRequest->requestCodigo->get(),
            momento: $eventosDoRequest->momento->format('Y-m-d H:i:s'),
            totalEventos: count($eventosDoRequest->get()),
            eventos: $eventosDoRequest->getArray(),
            accessToken: $this->accessToken->get()
        );

        $this->repositorioRequest->salvarEventosDoRequest($parametrosSalvarEventoRequest);

        return $entidadeEvento->codigo->get();
    }
}