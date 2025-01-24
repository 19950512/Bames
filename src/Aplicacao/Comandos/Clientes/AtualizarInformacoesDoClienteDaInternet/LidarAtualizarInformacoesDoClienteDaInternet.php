<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoClienteDaInternet;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\AccessToken;
use App\Dominio\ObjetoValor\Evento;
use App\Dominio\ObjetoValor\EventosDoRequest;
use App\Dominio\Repositorios\Clientes\Fronteiras\EntradaFronteiraAtualizarInformacoesDoCliente;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Request\Fronteiras\EntradaFronteiraSalvarEventosDoRequest;
use App\Dominio\Repositorios\Request\RepositorioRequest;
use Exception;
use Override;

readonly final class LidarAtualizarInformacoesDoClienteDaInternet implements Lidar
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

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoAtualizarInformacoesDoClienteDaInternet::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $codigoCliente = $comando->obterCodigoCliente();
        $email = $comando->obterEmail();
        $nomeCompleto = $comando->obterNomeCompleto();
        $telefone = $comando->obterTelefone();
        $documento = $comando->obterDocumento();
        $empresaCodigo = $this->entidadeEmpresarial->codigo->get();
        $dataNascimento = $comando->obterDataNascimento();
        $endereco = $comando->obterEndereco();
        $enderecoNumero = $comando->obterEnderecoNumero();
        $enderecoComplemento = $comando->obterEnderecoComplemento();
        $enderecoBairro = $comando->obterBairro();
        $enderecoCidade = $comando->obterCidade();
        $enderecoEstado = $comando->obterEstado();
        $enderecoCep = $comando->obterCep();
        $nomeMae = $comando->obterNomeDaMae();
        $cpfMae = $comando->obterCpfDaMae();
        $sexo = $comando->obterSexo();
        $cpfPai = $comando->obterCpfPai();
        $nomePai = $comando->obterNomePai();
        $rg = $comando->obterRg();
        $pis = $comando->obterPis();
        $carteiraTrabalho = $comando->obterCarteiraTrabalho();
        $telefones = $comando->obterTelefones();
        $emails = $comando->obterEmails();
        $enderecos = $comando->obterEnderecos();
        $familiares = $comando->obterFamiliares();

        $eventosDoRequest = new EventosDoRequest(
            empresaCodigo: $this->entidadeUsuarioLogado->empresaCodigo,
            usuarioCodigo: $this->entidadeUsuarioLogado->codigo,
            accessToken: $this->accessToken
        );

        try {

            $clienteData = $this->repositorioClientes->buscarClientePorCodigo(
                codigoCliente: $codigoCliente,
                empresaCodigo: $empresaCodigo
            );

            $entidadeClienteAtual = EntidadeCliente::instanciarEntidadeCliente($clienteData);

            $entidadeClienteNovaData = new SaidaFronteiraClienteDetalhado(
                codigo: $codigoCliente,
                nomeCompleto: $nomeCompleto,
                tipo: 'Cliente',
                email: $email,
                telefone: $telefone,
                documento: $documento,
                dataNascimento: $dataNascimento,
                endereco: $endereco,
                enderecoNumero: $enderecoNumero,
                enderecoComplemento: $enderecoComplemento,
                enderecoBairro: $enderecoBairro,
                enderecoCidade: $enderecoCidade,
                enderecoEstado: $enderecoEstado,
                enderecoCep: $enderecoCep,
                nomeMae: $nomeMae,
                cpfMae: $cpfMae,
                sexo: $sexo,
                nomePai: $nomePai,
                cpfPai: $cpfPai,
                rg: $rg,
                pis: $pis,
                carteiraTrabalho: $carteiraTrabalho,
                telefones: $telefones,
                emails: $emails,
                enderecos: $enderecos,
                familiares: $familiares
            );

            $entidadeClienteNova = EntidadeCliente::instanciarEntidadeCliente($entidadeClienteNovaData);

            $diferenca = $entidadeClienteAtual->comparar($entidadeClienteNova);

            if(count($diferenca) <= 0){
                $novoEventoRequest = new Evento("Não houve alterações nos dados do cliente.");
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
                return null;
            }


            if ($this->repositorioClientes->jaExisteUmClienteComEsteEmailOuDocumento(
                email: $entidadeClienteNova->email->get(),
                documento: $entidadeClienteNova->documento->get(),
                empresaCodigo: $empresaCodigo,
                clienteCodigo: $codigoCliente
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

            $eventosInformacoesDoCliente = [];
            foreach($diferenca as $informacao => $mudanca){

                $descricaoDoEvento = "{$this->entidadeUsuarioLogado->nomeCompleto->get()}, alterou o(a) $informacao do cliente de {$mudanca['antigo']} para {$mudanca['novo']}";
                $novoEventoRequest = new Evento($descricaoDoEvento);
                $eventosDoRequest->adicionar($novoEventoRequest);

                $eventosInformacoesDoCliente[] = [
                    'momento' => $eventosDoRequest->momento->format('Y-m-d H:i:s'),
                    'descricao' => $descricaoDoEvento,
                ];

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Clientes,
                    mensagem: "Alteração no cliente: $descricaoDoEvento"
                );
            }

            $parametrosSalvarCliente = new EntradaFronteiraAtualizarInformacoesDoCliente(
                codigoCliente: $entidadeClienteNova->codigo->get(),
                empresaCodigo: $empresaCodigo,
                email: $entidadeClienteNova->email->get(),
                nomeCompleto: $entidadeClienteNova->nomeCompleto->get(),
                telefone: $entidadeClienteNova->telefone->get(),
                documento: $entidadeClienteNova->documento->get(),
                dataNascimento: $dataNascimento,
                sexo: $sexo,
                nomeDaMae: $nomeMae,
                cpfMae: $cpfMae,
                endereco: $endereco,
                enderecoNumero: $enderecoNumero,
                enderecoComplemento: $enderecoComplemento,
                bairro: $enderecoBairro,
                cidade: $enderecoCidade,
                estado: $enderecoEstado,
                cep: $enderecoCep,
                nomeDoPai: $nomePai,
                cpfPai: $cpfPai,
                rg: $rg,
                pis: $pis,
                carteiraTrabalho: $carteiraTrabalho,
                telefones: $telefones,
                familiares: $familiares,
                emails: $emails,
                enderecos: $enderecos
            );

            $this->repositorioClientes->atualizarInformacoesDoCliente($parametrosSalvarCliente);

            $this->cache->delete("$empresaCodigo/clientes");
            $this->cache->delete("$empresaCodigo/clienteDetalhado/$codigoCliente");

            $novoEventoRequest = new Evento("{$this->entidadeUsuarioLogado->nomeCompleto->get()}, alterou informações do cliente com o ID: {$entidadeClienteNova->codigo->get()}, nome: {$nomeCompleto}");
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
            $this->repositorioClientes->salvarEventosDoCliente(
                codigoCliente: $entidadeClienteNova->codigo->get(),
                empresaCodigo: $empresaCodigo,
                eventos: $eventosInformacoesDoCliente
            );

            return null;

        }catch(Exception $erro){

            $novoEventoRequest = new Evento("Erro ao atualizar informações do cliente. {$erro->getMessage()}");
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
                mensagem: "Erro ao atualizar informações do cliente. {$erro->getMessage()}"
            );
            throw new Exception("Erro ao atualizar informações do cliente. {$erro->getMessage()}");
        }
    }
}