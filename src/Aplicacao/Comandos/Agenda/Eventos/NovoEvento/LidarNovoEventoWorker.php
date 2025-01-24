<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento;

use DateTime;
use Override;
use Exception;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\Entidades\JusiziEntity;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Agenda\Agenda;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Agenda\EntidadeEvento;
use App\Dominio\Repositorios\Email\RepositorioEmail;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Agenda\RepositorioAgenda;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\Agenda\Fronteiras\SaidaFronteiraBuscarEvento;
use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraCriarEvento;
use App\Dominio\Repositorios\Agenda\Fronteiras\EntradaFronteiraAdicionarEvento;

readonly final class LidarNovoEventoWorker implements Lidar
{
	public function __construct(
        private RepositorioRequest $repositorioRequest,
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioEmail $repositorioEmail,
        private RepositorioAutenticacao $repositorioAutenticacao,
        private JusiziEntity $jusiziEntity,
        private Mensageria $mensageria,
        private RepositorioAgenda $repositorioAgenda,
        private Agenda $agenda,
        private Cache $cache,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
        if (!is_a($comando, ComandoNovoEventoWorker::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo(
                empresaCodigo: $comando->obterEmpresaCodigo()
            );
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {
            
            $usuarioDados = $this->repositorioAutenticacao->buscarContaPorCodigo(
                contaCodigo: $comando->obterUsuarioCodigo()
            );
            $entidadeUsuarioLogado = EntidadeUsuarioLogado::instanciarEntidadeUsuarioLogado($usuarioDados);

        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        $eventoCodigo = new IdentificacaoUnica();
        
        $paramsEvento = new SaidaFronteiraBuscarEvento(
            codigo: $eventoCodigo->get(),
            business_id: $entidadeEmpresarial->codigo->get(),
            usuario_id: $entidadeUsuarioLogado->codigo->get(),
            plataforma_id: '',
            titulo: $comando->obterTituloPronto(),
            descricao: $comando->obterDescricaoPronto(),
            status: 'pendente',
            dataInicio: $comando->obterHorarioEventoInicioPronto(),
            dataFim: $comando->obterHorarioEventoFimPronto(),
            momento: (new DateTime())->format('Y-m-d H:i:s'),
            diaTodo: $comando->obterDiaTodoPronto(),
            recorrencia: $comando->obterRecorrenciaPronto(),

        );
        $entidadeEvento = EntidadeEvento::build($paramsEvento);

        try {
            
            $parametrosCriarEvento = new EntradaFronteiraCriarEvento(
                titulo: $comando->obterTituloPronto(),
                descricao: $comando->obterDescricaoPronto(),
                diaTodo: $comando->obterDiaTodoPronto(),
                recorrencia: $comando->obterRecorrenciaPronto(),
                horarioInicio: (new DateTime($comando->obterHorarioEventoInicioPronto()))->format('Y-m-d H:i:s.uP'),
                horarioFim: (new DateTime($comando->obterHorarioEventoFimPronto()))->format('Y-m-d H:i:s.uP'),
            );
            
            $eventoID = $this->agenda->criarEvento($parametrosCriarEvento);

            $entidadeEvento->agendaID = $eventoID;
        
        }catch(Exception $erro){
        }

        try {

            $parametrosSalvar = new EntradaFronteiraAdicionarEvento(
                codigo: $entidadeEvento->codigo->get(),
                business_id: $entidadeEvento->empresaCodigo->get(),
                usuario_id: $entidadeEvento->usuarioCodigo->get(),
                titulo: $entidadeEvento->titulo->get(),
                descricao: $entidadeEvento->descricao->get(),
                dataInicio: $entidadeEvento->horarioInicio->format('Y-m-d H:i:s.uP'),
                dataFim: $entidadeEvento->horarioFim->format('Y-m-d H:i:s.uP'),
                momento: $entidadeEvento->criadoEm->format('Y-m-d H:i:s'),
                status: $entidadeEvento->status,
                plataforma_evento_id: $entidadeEvento->agendaID,
                diaTodo: $entidadeEvento->diaTodo,
                recorrencia: $entidadeEvento->recorrencia
            );
            
            $this->repositorioAgenda->adicionarEvento($parametrosSalvar);

        }catch(Exception $erro){

        }

        if(!$comando->obterNotificarPorEmail()){
            return null;
        }

        $keyCache = "{$entidadeEmpresarial->codigo->get()}/meuscompromissos/{$entidadeUsuarioLogado->codigo->get()}";
        $this->cache->delete($keyCache);

        $assuntoEmail = "Evento criado com sucesso - {$comando->obterTituloPronto()}";

        $mensagemCriadoNaAgenda = "com sucesso";
        if(!empty($entidadeEvento->agendaID)){
            $mensagemCriadoNaAgenda = "com sucesso e o evento foi criado na agenda";
        }
        $mensagemEmail = <<<htmlMensagemEmail
        Olá {$entidadeUsuarioLogado->nomeCompleto->get()},
        <br />
        <p>O evento {$entidadeEvento->titulo->get()} foi criado {$mensagemCriadoNaAgenda}.</p>
        <br />
        <p>Segue abaixo os detalhes do evento:</p>
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
            evento: Evento::EnviarEmail,
            message: json_encode([
                'destinatarioEmail' => $entidadeUsuarioLogado->email->get(),
                'destinatarioNome' => $entidadeUsuarioLogado->nomeCompleto->get(),
                'assunto' => $assuntoEmail,
                'mensagem' => $mensagemEmail
            ])
        );

        return null;
    }
}