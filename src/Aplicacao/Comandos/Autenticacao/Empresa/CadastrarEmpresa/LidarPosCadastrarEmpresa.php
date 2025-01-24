<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Autenticacao\Empresa\CadastrarEmpresa;

use Override;
use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\Arquivos;
use App\Dominio\Entidades\JusiziEntity;
use App\Dominio\Entidades\UsuarioSistema;
use App\Dominio\ObjetoValor\ArquivoTemporario;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Mensageria\Mensageria;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\ContaBancaria\Enumerados\Banco;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Comandos\Modelos\NovoModelo\LidarNovoModelo;
use App\Aplicacao\Comandos\Modelos\NovoModelo\ComandoNovoModelo;
use App\Dominio\Repositorios\Autenticacao\RepositorioAutenticacao;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\LidarNovoEvento;
use App\Aplicacao\Comandos\Agenda\Eventos\NovoEvento\ComandoNovoEvento;
use App\Aplicacao\Compartilhado\Mensageria\Enumerados\Evento as EventoMensageria;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\ConsultarInformacoesNaInternet;

final class LidarPosCadastrarEmpresa implements Lidar
{

    public function __construct(
        private RepositorioContaBancaria $repositorioContaBancaria,
        private JusiziEntity $jusiziEntity,
        private Mensageria $mensageria,
        private Ambiente $ambiente,
        private ConsultarInformacoesNaInternet $consultarInformacoesNaInternet,
        private Discord $discord,
        private Container $container,
		private RepositorioAutenticacao $repositorioAutenticacaoComando,
    ){}

    #[Override] public function lidar(Comando $comando): null
    {
		if(!is_a($comando, ComandoPosCadastrarEmpresa::class)){
		    throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $entidadeEmpresarialData = $this->repositorioAutenticacaoComando->obterEmpresaPorCodigo(
                empresaCodigo: $comando->obterEmpresaCodigo()
            );
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($entidadeEmpresarialData);

        }catch (Exception $erro){

            throw new Exception($erro->getMessage());
        }

        try {

            $usuarioSistemaData = $this->repositorioAutenticacaoComando->buscarContaPorCodigo(
                contaCodigo: $entidadeEmpresarial->responsavel->codigo->get(),
            );

            $usuarioSistema = UsuarioSistema::build($usuarioSistemaData);

        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        if($this->repositorioAutenticacaoComando->empresaJaFoiExecutadoPosCadastrar(
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            )
        ){
            return null;
        }

        try {

            $this->repositorioAutenticacaoComando->posCadastrarEmpresaEfetuadaComSucesso(
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

        }catch (Exception $erro){
            throw new Exception($erro->getMessage());
        }

        $gerarLinkParaHabilitarOUsoDaContaBancaria = true;
        $mensagemParaNaoHabilitarAContaBancaria = '';

        try {

            $pessoaEncontrada = $this->consultarInformacoesNaInternet->consultarCPF(
                cpf: $usuarioSistema->documento->get()
            );

            if($pessoaEncontrada->nomeCompleto != $usuarioSistema->nomeCompleto->get()){
                $mensagemParaNaoHabilitarAContaBancaria = 'O nome informado não corresponde ao nome registrado para o CPF informado.';
                $gerarLinkParaHabilitarOUsoDaContaBancaria = false;

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::NovosClientes,
                    mensagem: 'O nome informado não corresponde ao nome registrado para o CPF informado.'
                );
            }

        }catch (Exception $erro){
            $gerarLinkParaHabilitarOUsoDaContaBancaria = false;
            $mensagemParaNaoHabilitarAContaBancaria = $erro->getMessage();

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: $erro->getMessage()
            );
        }

        try {

            $contaBancariaCodigo = new IdentificacaoUnica();
            $this->repositorioContaBancaria->criarPrimeiraContaBancaria(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                contaBancariaCodigo: $contaBancariaCodigo->get(),
                nome: 'Conta Principal - '.$entidadeEmpresarial->apelido->get(),
                banco: Banco::ASAAS->value
            );

            $this->repositorioContaBancaria->novoEvento(
                contaBancariaCodigo: $contaBancariaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                eventoDescricao: 'Conta Principal - '.$entidadeEmpresarial->apelido->get().' criada com sucesso automáticamente na criação da conta.',
            );

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Ops, não foi possível cadastrar a conta bancária principal da empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível cadastrar a conta bancária principal da empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}");
        }

        $htmlParaLinkDeAtivacaoAConta = '';

        if($gerarLinkParaHabilitarOUsoDaContaBancaria){

            try {

                $tokenVerificacaoEmail = $this->repositorioAutenticacaoComando->buscarTokenEmailVerificacao(
                  empresaCodigo: $entidadeEmpresarial->codigo->get(),
                  usuarioCodigo: $usuarioSistema->codigo->get()
                );

                $htmlParaLinkDeAtivacaoAConta = '<p>Acesse o <a href="https://app.jusizi.com.br/auth/emailverificado?token='.$tokenVerificacaoEmail.'" target="_blank">link</a> para verificar seu e-mail e conseguir acesso ao sistema.</p><br />';

            }catch (Exception $erro){}

        }else{

            $this->repositorioAutenticacaoComando->buscarTokenEmailVerificacao(
              empresaCodigo: $entidadeEmpresarial->codigo->get(),
              usuarioCodigo: $usuarioSistema->codigo->get()
            );

            $this->repositorioAutenticacaoComando->atualizarOMotivoParaNaoAtivarAConta(
              empresaCodigo: $entidadeEmpresarial->codigo->get(),
              usuarioCodigo: $usuarioSistema->codigo->get(),
              mensagem: $mensagemParaNaoHabilitarAContaBancaria,
            );
        }

        $assuntoEmail = 'Bem-vindo ao sistema';
        $mensagemEmail = <<<htmlMensagemEmail
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">
          <meta name="description" content="$assuntoEmail">
          <title>$assuntoEmail</title>
        </head>
        <body>
            <p>Olá {$usuarioSistema->nomeCompleto->get()},</p>
            <br />
            <p>Agradecemos por escolher criar uma conta na {$this->jusiziEntity->fantasia}!</p>
            <br />
            $htmlParaLinkDeAtivacaoAConta
            <p>Na {$this->jusiziEntity->fantasia}, nossa missão é proporcionar serviços excepcionais para escritórios advocatícios e advogados, com foco na integridade, segurança, confiabilidade e disponibilidade das informações em todas as plataformas.<br />Buscamos constantemente oferecer soluções inovadoras e eficientes que atendam às necessidades específicas de nossos clientes no setor jurídico.<br />Nos comprometemos a ser um parceiro confiável e dedicado, garantindo que nossos clientes possam contar conosco para gerenciar seus dados de forma segura e eficaz, permitindo-lhes concentrar-se no que fazem de melhor:<br /><br />buscar a justiça para seus clientes.</p>
            <p>Compreendemos os desafios que os clientes enfrentam diariamente, e é por isso que nos esforçamos para oferecer soluções que facilitem suas vidas e impulsionem seus negócios.</p>
            <br />                
            <p>Dê uma olhada em nossa página do site {$this->ambiente->get('APP_DOMINIO')} para explorar a ampla variedade de ferramentas e recursos que disponibilizamos. Estamos constantemente atualizando e aprimorando nossos produtos para atender às necessidades em constante evolução de nossos clientes.</p>
            <br />
            <p>Se surgir alguma dúvida ou se precisar de assistência, não hesite em contatar nossa equipe de suporte em {$this->jusiziEntity->emailComercial}.<br />Eles estão sempre à disposição para oferecer ajuda e orientação.</p>
            <b
            <p>Estamos empolgados por ter você a bordo e ansiosos para ajudá-lo a alcançar seus objetivos com nossas soluções inovadoras.</p>
            <br />
            <p>Cumprimentos,</p>
            
            {$this->jusiziEntity->responsavelNome}<br/>
            {$this->jusiziEntity->responsavelCargo}<br/>
            {$this->jusiziEntity->fantasia}<br/>
        </body>
        htmlMensagemEmail;

        $this->mensageria->publicar(
            evento: EventoMensageria::EnviarEmail,
            message: json_encode([
                'destinatarioEmail' => $usuarioSistema->email->get(),
                'destinatarioNome' => $usuarioSistema->nomeCompleto->get(),
                'assunto' => $assuntoEmail,
                'mensagem' => $mensagemEmail
            ])
        );

        $valorBonusInicial = (float) $this->ambiente->get('BONUS_EMPRESA_RECENTE_CADASTRADA');
        if($valorBonusInicial > 0){
            $this->repositorioAutenticacaoComando->adicionarBonusEmpresaRecemCadastrada(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                valorCreditos: $valorBonusInicial
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Bônus de boas-vindas adicionado com sucesso para a empresa {$entidadeEmpresarial->apelido->get()}. (Valor: R$ ".number_format($valorBonusInicial, 2, ',', '.').")"
            );
        }

        // CRIAR DATAS DE EVENTOS IMPORTANTES NA AGENDA
        $this->cadastrarEventosDeDatasComemorativas(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema
        );        

        // CRIAR MODELOS DE DOCUMENTOS PADRÃO
        $this->adicionarModelo(
            pathModeloPadrao: __DIR__.'/../../../../Compartilhado/Docx/ModelosPadrao/DECLARACAO_DE_RESIDENCIA.docx',
            nomeModeloArquivo: 'DECLARACAO_DE_RESIDENCIA.docx',
            nomeModelo: 'Declaração de Residência',
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema
        );

        $this->adicionarModelo(
            pathModeloPadrao: __DIR__.'/../../../../Compartilhado/Docx/ModelosPadrao/DECLARACAO_HIPOSSUFICIENCIA_ECONOMICA.docx',
            nomeModeloArquivo: 'DECLARACAO_HIPOSSUFICIENCIA_ECONOMICA.docx',
            nomeModelo: 'Declaração de Hipossuficiência Econômica',
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema
        );

        $this->adicionarModelo(
            pathModeloPadrao: __DIR__.'/../../../../Compartilhado/Docx/ModelosPadrao/HONORARIOS_PENAL.docx',
            nomeModeloArquivo: 'HONORARIOS_PENAL.docx',
            nomeModelo: 'Honorários Advocatícios - Penal',
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema
        );

        /*
         * AQUI O SISTEMA IRIA CONSULTAR OS PROCESSOS DA OAB, POREM ESSA IDEIA NÃO SE PARECEU PROMISSORA E VAMOS DEIXAR PARA CONSULTAR EM OUTRO MOMENTO, EM OUTRO FLUXO, JÁ DEPOIS QUE A PESSOA SE AUTHETICOU.
        try {

            $comandoConsultarProcessoPorOAB = new ComandoLidarConsultasProcessoPorOAB(
                OAB: $entidadeEmpresarial->responsavel->oab->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                usuarioCodigo: $entidadeEmpresarial->responsavel->codigo->get(),
            );

            $comandoConsultarProcessoPorOAB->executar();

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Consultando processos da {$entidadeEmpresarial->responsavel->oab->get()} - Empresa: {$entidadeEmpresarial->apelido->get()} ID: {$entidadeEmpresarial->codigo->get()})"
            );

            $this->lidarConsultarProcessoPorOAB->lidar($comandoConsultarProcessoPorOAB);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Ops, não foi possível consultar os processos da {$entidadeEmpresarial->responsavel->oab->get()} - Empresa: {$entidadeEmpresarial->apelido->get()} ID: {$entidadeEmpresarial->codigo->get()}) {$erro->getMessage()}"
            );
        }
        */

        return null;
    }

    private function criarEventoNaAgenda(
        EntidadeEmpresarial $entidadeEmpresarial,
        UsuarioSistema $usuarioSistema,
        string $titulo,
        string $descricao,
        string $horarioEventoInicio,
        string $horarioEventoFim,
        bool $diaTodo,
        int $recorrencia = 1
    ): void
    {

        try {

            $comando = new ComandoNovoEvento(
                titulo: $titulo,
                descricao: $descricao,
                horarioEventoInicio: $horarioEventoInicio,
                horarioEventoFim: $horarioEventoFim,
                diaTodo: $diaTodo,
                recorrencia: $recorrencia,
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                usuarioCodigo: $usuarioSistema->codigo->get(),
                notificarPorEmail: false
            );
            $comando->executar();
            $this->container->get(LidarNovoEvento::class)->lidar($comando);
        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::NovosClientes,
                mensagem: "Ops, não foi possível criar o evento $titulo para a empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}"
            );
        }
    }

    private function adicionarModelo(string $pathModeloPadrao, string $nomeModeloArquivo, string $nomeModelo, EntidadeEmpresarial $entidadeEmpresarial, UsuarioSistema $usuarioSistema): void
    {

        if(is_file($pathModeloPadrao)){
            try {
                $arquivos = new Arquivos();
                $arquivos->adicionar(new ArquivoTemporario(
                    fullPath: $pathModeloPadrao,
                    name: $nomeModeloArquivo,
                    tmpName: $pathModeloPadrao,
                    size: filesize($pathModeloPadrao),
                    error: UPLOAD_ERR_OK,
                    type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ));
                $comando = new ComandoNovoModelo(
                    nomeModelo: $nomeModelo,
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    usuarioCodigo: $usuarioSistema->codigo->get(),
                    arquivos: $arquivos
                );
                $comando->executar();

                $this->container->get(LidarNovoModelo::class)->lidar($comando);

            }catch (Exception $erro){

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::NovosClientes,
                    mensagem: "Ops, não foi possível criar os modelos de documentos padrão para a empresa {$entidadeEmpresarial->apelido->get()}. {$erro->getMessage()}"
                );
            }
        }
    }

    private function cadastrarEventosDeDatasComemorativas(EntidadeEmpresarial $entidadeEmpresarial, UsuarioSistema $usuarioSistema): void
    {

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Trabalho',
            descricao: 'Feliz Dia do Trabalho!',
            horarioEventoInicio: date('Y').'-05-01 01:00:00',
            horarioEventoFim: date('Y').'-05-01 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia da Independência',
            descricao: 'Feliz Dia da Independência!',
            horarioEventoInicio: date('Y').'-09-07 01:00:00',
            horarioEventoFim: date('Y').'-09-07 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia das Crianças',
            descricao: 'Feliz Dia das Crianças!',
            horarioEventoInicio: date('Y').'-10-12 01:00:00',
            horarioEventoFim: date('Y').'-10-12 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Advogado',
            descricao: 'Feliz Dia do Advogado!',
            horarioEventoInicio: date('Y').'-08-11 01:00:00',
            horarioEventoFim: date('Y').'-08-11 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia da Mulher Advogada',
            descricao: 'Feliz Dia da Mulher Advogada!',
            horarioEventoInicio: date('Y').'-12-15 01:00:00',
            horarioEventoFim: date('Y').'-12-15 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Cliente',
            descricao: 'Feliz Dia do Cliente!',
            horarioEventoInicio: date('Y').'-09-15 01:00:00',
            horarioEventoFim: date('Y').'-09-15 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Contador',
            descricao: 'Feliz Dia do Contador!',
            horarioEventoInicio: date('Y').'-04-25 01:00:00',
            horarioEventoFim: date('Y').'-04-25 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Reveillon',
            descricao: 'Feliz Reveillon!',
            horarioEventoInicio: date('Y').'-12-31 01:00:00',
            horarioEventoFim: date('Y').'-12-31 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia de Ano Novo e Dia Internacional da Paz',
            descricao: 'Feliz Ano Novo e Dia Internacional da Paz!',
            horarioEventoInicio: date('Y').'-01-01 01:00:00',
            horarioEventoFim: date('Y').'-01-01 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Natal',
            descricao: 'Feliz Natal!',
            horarioEventoInicio: date('Y').'-12-25 01:00:00',
            horarioEventoFim: date('Y').'-12-25 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia Internacional da Mulher',
            descricao: 'Feliz Dia Internacional da Mulher!',
            horarioEventoInicio: date('Y').'-03-08 01:00:00',
            horarioEventoFim: date('Y').'-03-08 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Estagiário',
            descricao: 'Feliz Dia do Estagiário!',
            horarioEventoInicio: date('Y').'-08-18 01:00:00',
            horarioEventoFim: date('Y').'-08-18 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Estudante',
            descricao: 'Feliz Dia do Estudante!',
            horarioEventoInicio: date('Y').'-08-11 01:00:00',
            horarioEventoFim: date('Y').'-08-11 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia da Justiça',
            descricao: 'Feliz Dia da Justiça!',
            horarioEventoInicio: date('Y').'-08-12 01:00:00',
            horarioEventoFim: date('Y').'-08-12 23:59:00',
            diaTodo: true,
        );

        // Dia do Advogado Criminalista - 11 de agosto
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Advogado Criminalista',
            descricao: 'Feliz Dia do Advogado Criminalista!',
            horarioEventoInicio: date('Y').'-08-11 01:00:00',
            horarioEventoFim: date('Y').'-08-11 23:59:00',
            diaTodo: true,
        );

        // Dia do Advogado Trabalhista - 28 de agosto
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Advogado Trabalhista',
            descricao: 'Feliz Dia do Advogado Trabalhista!',
            horarioEventoInicio: date('Y').'-08-28 01:00:00',
            horarioEventoFim: date('Y').'-08-28 23:59:00',
            diaTodo: true,
        );

        // Dia do Advogado Tributarista - 26 de agosto
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Advogado Tributarista',
            descricao: 'Feliz Dia do Advogado Tributarista!',
            horarioEventoInicio: date('Y').'-08-26 01:00:00',
            horarioEventoFim: date('Y').'-08-26 23:59:00',
            diaTodo: true,
        );

        // Dia Nacional da Defensoria Pública - 19 de maio
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia Nacional da Defensoria Pública',
            descricao: 'Feliz Dia Nacional da Defensoria Pública!',
            horarioEventoInicio: date('Y').'-05-19 01:00:00',
            horarioEventoFim: date('Y').'-05-19 23:59:00',
            diaTodo: true,
        );

        // Dia do Juiz do Trabalho - 3 de dezembro
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Juiz do Trabalho',
            descricao: 'Feliz Dia do Juiz do Trabalho!',
            horarioEventoInicio: date('Y').'-12-03 01:00:00',
            horarioEventoFim: date('Y').'-12-03 23:59:00',
            diaTodo: true,
        );

        // Dia do Juiz de Direito - 11 de agosto
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Juiz de Direito',
            descricao: 'Feliz Dia do Juiz de Direito!',
            horarioEventoInicio: date('Y').'-08-11 01:00:00',
            horarioEventoFim: date('Y').'-08-11 23:59:00',
            diaTodo: true,
        );

        // Dia do Juiz Federal - 15 de março
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Juiz Federal',
            descricao: 'Feliz Dia do Juiz Federal!',
            horarioEventoInicio: date('Y').'-03-15 01:00:00',
            horarioEventoFim: date('Y').'-03-15 23:59:00',
            diaTodo: true,
        );

        // Dia Nacional do Ministério Público - 14 de dezembro
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia Nacional do Ministério Público',
            descricao: 'Feliz Dia Nacional do Ministério Público!',
            horarioEventoInicio: date('Y').'-12-14 01:00:00',
            horarioEventoFim: date('Y').'-12-14 23:59:00',
            diaTodo: true,
        );

        // Dia do Procurador do Estado - 10 de janeiro
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia do Procurador do Estado',
            descricao: 'Feliz Dia do Procurador do Estado!',
            horarioEventoInicio: date('Y').'-01-10 01:00:00',
            horarioEventoFim: date('Y').'-01-10 23:59:00',
            diaTodo: true,
        );

        // Criação da OAB - 18 de novembro
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Criação da OAB',
            descricao: 'Feliz Aniversário da Criação da OAB!',
            horarioEventoInicio: date('Y').'-11-18 01:00:00',
            horarioEventoFim: date('Y').'-11-18 23:59:00',
            diaTodo: true,
        );

        // Criação do Ministério da Justiça - 24 de maio
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Criação do Ministério da Justiça',
            descricao: 'Feliz Aniversário da Criação do Ministério da Justiça!',
            horarioEventoInicio: date('Y').'-05-24 01:00:00',
            horarioEventoFim: date('Y').'-05-24 23:59:00',
            diaTodo: true,
        );


        /*
        // ESTES EVENTOS COMENTADOS O DIA MUDA DE ACORDO COM O ANO, ENTÃO NÃO É POSSÍVEL CRIAR ELES AUTOMATICAMENTE SEM UM ALGORITMO QUE CALCULE O DIA CORRETO.
        // POR ISSO ELES FORAM COMENTADOS, MAS PODEM SER DESCOMENTADOS E AJUSTADOS COM UM ALGORITMO QUE CALCULE O DIA CORRETO.
        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia dos Pais',
            descricao: 'Feliz Dia dos Pais!',
            horarioEventoInicio: date('Y').'-08-10 01:00:00',
            horarioEventoFim: date('Y').'-08-10 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Dia das Mães',
            descricao: 'Feliz Dia das Mães!',
            horarioEventoInicio: date('Y').'-05-09 01:00:00',
            horarioEventoFim: date('Y').'-05-09 23:59:00',
            diaTodo: true,
        );

        $this->criarEventoNaAgenda(
            entidadeEmpresarial: $entidadeEmpresarial,
            usuarioSistema: $usuarioSistema,
            titulo: 'Carnaval',
            descricao: 'Feliz Carnaval!',
            horarioEventoInicio: date('Y').'-03-01 01:00:00',
            horarioEventoFim: date('Y').'-03-01 23:59:00',
            diaTodo: true,
        );
        */
    }
}