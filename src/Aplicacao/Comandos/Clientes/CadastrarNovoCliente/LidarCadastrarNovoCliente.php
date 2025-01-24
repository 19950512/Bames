<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Cliente\Enums\Sexo;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\Endereco\CEP;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\NomeCompleto;
use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraCadastrarNovoCliente;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;

readonly final class LidarCadastrarNovoCliente implements Lidar
{

    public function __construct(
        private RepositorioRequest $repositorioRequest,
        private RepositorioClientes $repositorioClientes,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private AccessToken $accessToken,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private Discord $discord,
        private Cache $cache
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoCadastrarNovoCliente::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $email = $comando->obterEmail();
        $nomeCompleto = $comando->obterNomeCompleto();
        $telefone = $comando->obterTelefone();
        $documento = $comando->obterDocumento();
        $empresaCodigo = $this->entidadeEmpresarial->codigo->get();

        $logradouro = $comando->obterLogradouro();
        $numero = $comando->obterNumero();
        $complemento = $comando->obterComplemento();
        $bairro = $comando->obterBairro();
        $cidade = $comando->obterCidade();
        $estado = $comando->obterEstado();
        $cep = $comando->obterCep();
        $nomeMae = $comando->obterNomeMae();
        $cpfMae = $comando->obterCpfMae();
        $sexo = $comando->obterSexo();
        $dataNascimento = $comando->obterDataNascimento();
        $familiares = $comando->obterFamiliares();
        $nomePai = $comando->obterNomePai();
        $cpfPai = $comando->obterCpfPai();
        $rg = $comando->obterRg();
        $pis = $comando->obterPis();
        $carteiraTrabalho = $comando->obterCarteiraTrabalho();
        $telefones = $comando->obterTelefones();
        $emails = $comando->obterEmails();
        $enderecos = $comando->obterEnderecos();


        if(!empty($cep)){
            try {
                $cep = (new CEP($cep))->get();
            }catch (Exception $erro){
            }
        }

        if(!empty($nomeMae)){
            try {
                $nomeMae = (new NomeCompleto($nomeMae))->get();
            } catch (Exception $e) {
            }
        }

        if(!empty($cpfMae)){
            try {
                $cpfMae = (new CPF($cpfMae))->get();
            } catch (Exception $e) {
            }
        }

        $sexo = Sexo::get($sexo);

        $clienteID = new IdentificacaoUnica();

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeUsuarioLogado->empresaCodigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        try {

            if ($this->repositorioClientes->jaExisteUmClienteComEsteEmailOuDocumento(
                email: $email,
                documento: $documento,
                empresaCodigo: $empresaCodigo,
                clienteCodigo: $clienteID->get()
            )) {

                $novoEventoRequest = new Evento("Já existe um cliente cadastrado com este e-mail ou documento. (E-mail: $email, Empresa: $empresaCodigo, Nome: $nomeCompleto)");
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

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Clientes,
                    mensagem: "Já existe um cliente cadastrado com este e-mail ou documento. (E-mail: $email, Empresa: $empresaCodigo, Nome: $nomeCompleto)"
                );
                throw new Exception("Já existe um cliente cadastrado com este e-mail ou documento.");
            }

            $parametrosSalvarCliente = new EntradaFronteiraCadastrarNovoCliente(
                clienteID: $clienteID->get(),
                nomeCompleto: $nomeCompleto,
                email: $email,
                telefone: $telefone,
                documento: $documento,
                empresaCodigo: $empresaCodigo,
                logradouro: $logradouro,
                numero: $numero,
                complemento: $complemento,
                bairro: $bairro,
                cidade: $cidade,
                estado: $estado,
                cep: $cep,
                nomeMae: $nomeMae,
                cpfMae: $cpfMae,
                sexo: $sexo->getDescricao(),
                dataNascimento: $dataNascimento,
                familiares: $familiares,
                nomePai: $nomePai,
                cpfPai: $cpfPai,
                rg: $rg,
                pis: $pis,
                carteiraTrabalho: $carteiraTrabalho,
                telefones: $telefones,
                emails: $emails,
                enderecos: $enderecos,
                novoCliente: true,
            );
            $this->repositorioClientes->cadastrarNovoCliente($parametrosSalvarCliente);

            $this->repositorioClientes->salvarEventosDoCliente(
                codigoCliente: $clienteID->get(),
                empresaCodigo: $empresaCodigo,
                eventos: [
                    [
                        'descricao' => "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, cadastrou um novo cliente com o ID: {$clienteID->get()}, nome: {$nomeCompleto}",
                        'momento' => $eventosDoRequest->momento->format('Y-m-d H:i:s')
                    ]
                ]
            );

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, cadastrou um novo cliente com o ID: {$clienteID->get()}, nome: {$nomeCompleto}");
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

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Clientes,
                mensagem: "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, cadastrou um novo cliente com o ID: {$clienteID->get()}, nome: {$nomeCompleto}"
            );

            $this->cache->delete($empresaCodigo . '/clientes');
            return null;

        }catch(Exception $erro){

            $novoEventoRequest = new Evento("Erro ao cadastrar novo cliente. {$erro->getMessage()}");
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

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Clientes,
                mensagem: "Erro ao cadastrar novo cliente. {$erro->getMessage()}"
            );
            throw new Exception("Erro ao cadastrar novo cliente. {$erro->getMessage()}");
        }
    }

}