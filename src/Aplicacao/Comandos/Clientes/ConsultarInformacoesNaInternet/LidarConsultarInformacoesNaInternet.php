<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\ConsultarInformacoesNaInternet;

use App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoClienteDaInternet\ComandoAtualizarInformacoesDoClienteDaInternet;
use App\Aplicacao\Comandos\Clientes\AtualizarInformacoesDoClienteDaInternet\LidarAtualizarInformacoesDoClienteDaInternet;
use App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente\ComandoCadastrarNovoCliente;
use App\Aplicacao\Comandos\Clientes\CadastrarNovoCliente\LidarCadastrarNovoCliente;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use DI\Container;
use Exception;
use App\Dominio\ObjetoValor\CPF;
use App\Aplicacao\Comandos\Lidar;
use App\Dominio\ObjetoValor\CNPJ;
use App\Aplicacao\Comandos\Comando;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\ConsultarInformacoesNaInternet;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Enums\CustosInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\RepositorioConsultarInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\EntradaFronteiraSalvarRequestPorDocumento;

final readonly class LidarConsultarInformacoesNaInternet implements Lidar
{

    public function __construct(
        private ConsultarInformacoesNaInternet $consultarInformacoesNaInternet,
        private RepositorioClientes $repositorioClientes,
        private RepositorioConsultarInformacoesNaInternet $repositorioConsultarInformacoesNaInternet,
        private RepositorioEmpresa $repositorioEmpresa,
        private Container $container
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoLidarConsultarInformacoesNaInternet::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($comando->obterEmpresaCodigo());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }

        try {
            $usuarioResponsavel = $entidadeEmpresarial->responsavel;
        } catch (Exception $erro) {
            throw new Exception("Usuário não encontrado. - {$erro->getMessage()}");
        }

        try {
            $documento = new DocumentoDeIdentificacao($comando->obterDocumento());
        } catch (Exception $e) {
            throw new Exception("Número do documento inválido.");
        }

        $requestID = new IdentificacaoUnica();

        if($entidadeEmpresarial->saldoCreditos->get() <= CustosInformacoesNaInternet::CONSULTA_DOCUMENTO->buscarCusto()){

            $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                contaCodigo: $usuarioResponsavel->codigo->get(),
                requestID: $requestID->get(),
                descricao: "Saldo de créditos insuficiente para consultar documento {$documento->get()}",
                momento: date('Y-m-d H:i:s'),
            );

            $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

            throw new Exception("Seu acesso não está autorizado para essa operação, consulte o suporte.");
        }

        $this->repositorioConsultarInformacoesNaInternet->cobrarCustoParaConsultarDocumento(
            documento: $documento->get(),
            custo: CustosInformacoesNaInternet::CONSULTA_DOCUMENTO->buscarCusto()
        );

        if($this->repositorioConsultarInformacoesNaInternet->documentoJaFoiConsultadoNosUltimosDias($documento->get())){

            $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                contaCodigo: $usuarioResponsavel->codigo->get(),
                requestID: $requestID->get(),
                descricao: "Documento {$documento->get()} já foi consultado nos últimos dias. - copiando as informações para {$usuarioResponsavel->codigo->get()} {$usuarioResponsavel->nomeCompleto->get()} - {$entidadeEmpresarial->codigo->get()} {$entidadeEmpresarial->apelido->get()}",
                momento: date('Y-m-d H:i:s'),
            );

            $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

            $informacoesDoCPF = $this->repositorioConsultarInformacoesNaInternet->buscarInformacoesDoDocumento($documento->get());

        }else{

            try {

                $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaCodigo: $usuarioResponsavel->codigo->get(),
                    requestID: $requestID->get(),
                    descricao: "Consultando informações do documento {$documento->get()}",
                    momento: date('Y-m-d H:i:s'),
                );

                $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

               $informacoesDoCPF = $this->consultarInformacoesNaInternet->consultarCPF($documento->get());

            }catch(Exception $erro){

                $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaCodigo: $usuarioResponsavel->codigo->get(),
                    requestID: $requestID->get(),
                    descricao: "Erro ao consultar informações do documento {$documento->get()} - {$erro->getMessage()}",
                    momento: date('Y-m-d H:i:s'),
                );
                $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

                throw new Exception("Erro ao consultar informações na internet. - {$erro->getMessage()}");

            } finally {

                $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaCodigo: $usuarioResponsavel->codigo->get(),
                    requestID: $requestID->get(),
                    descricao: "Informações do documento {$documento->get()} finalizadas",
                    momento: date('Y-m-d H:i:s'),
                );
                $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);
            }
        }

       if($this->repositorioClientes->jaExisteUmClienteComEsteDocumento(
           documento: $documento->get(),
           empresaCodigo: $entidadeEmpresarial->codigo->get()
       )){

           try {

               $comando = new ComandoAtualizarInformacoesDoClienteDaInternet(
                   codigoCliente: $this->repositorioClientes->buscarClientePorDocumento(
                       documento: $documento->get(),
                       empresaCodigo: $entidadeEmpresarial->codigo->get(),
                   )->codigo,
                   nomeCompleto: $informacoesDoCPF->nomeCompleto,
                   email: $informacoesDoCPF->emails[0] ?? '',
                   telefone: $informacoesDoCPF->telefones[0] ?? '',
                   documento: $documento->get(),
                   dataNascimento: $informacoesDoCPF->dataNascimento,
                   endereco: $informacoesDoCPF->enderecos[0]['logradouro'] ?? '',
                   enderecoNumero: $informacoesDoCPF->enderecos[0]['numero'] ?? '',
                   enderecoComplemento: $informacoesDoCPF->enderecos[0]['complemento'] ?? '',
                   enderecoBairro: $informacoesDoCPF->enderecos[0]['bairro'] ?? '',
                   enderecoCidade: $informacoesDoCPF->enderecos[0]['cidade'] ?? '',
                   enderecoEstado: $informacoesDoCPF->enderecos[0]['estado'] ?? '',
                   enderecoCep: $informacoesDoCPF->enderecos[0]['cep'] ?? '',
                   nomeMae: $informacoesDoCPF->nomeMae,
                   cpfMae: $informacoesDoCPF->cpfMae,
                   sexo: $informacoesDoCPF->sexo,
                   familiares: $informacoesDoCPF->familiares,
                   nomePai: $informacoesDoCPF->nomePai,
                   cpfPai: $informacoesDoCPF->cpfPai,
                   rg: $informacoesDoCPF->rg,
                   pis: $informacoesDoCPF->pis,
                   carteiraTrabalho: $informacoesDoCPF->carteiraTrabalho,
                   telefones: $informacoesDoCPF->telefones,
                   emails: $informacoesDoCPF->emails,
                   enderecos: $informacoesDoCPF->enderecos,
               );

               $comando->executar();

           }catch (Exception $erro) {
               throw new Exception("Erro ao atualizar informações do cliente apartir de consulta de informações na internet. {$erro->getMessage()}");
           }

           try {
                $this->container->get(LidarAtualizarInformacoesDoClienteDaInternet::class)->lidar($comando);
           }catch (Exception $erro) {
               throw new Exception("Erro ao lidar com a atualização de informações do cliente. {$erro->getMessage()}");
           }

       }else{

           try {

                $comando = new ComandoCadastrarNovoCliente(
                    nomeCompleto: $informacoesDoCPF->nomeCompleto,
                    email: $informacoesDoCPF->emails[0] ?? '',
                    telefone: $informacoesDoCPF->telefones[0] ?? '',
                    documento: $documento->get(),
                    logradouro: $informacoesDoCPF->enderecos[0]['logradouro'] ?? '',
                    numero: $informacoesDoCPF->enderecos[0]['numero'] ?? '',
                    complemento: $informacoesDoCPF->enderecos[0]['complemento'] ?? '',
                    bairro: $informacoesDoCPF->enderecos[0]['bairro'] ?? '',
                    cidade: $informacoesDoCPF->enderecos[0]['cidade'] ?? '',
                    estado: $informacoesDoCPF->enderecos[0]['estado'] ?? '',
                    cep: $informacoesDoCPF->enderecos[0]['cep'] ?? '',
                    nomeMae: $informacoesDoCPF->nomeMae,
                    cpfMae: $informacoesDoCPF->cpfMae,
                    dataNascimento: $informacoesDoCPF->dataNascimento,
                    sexo: $informacoesDoCPF->sexo,
                    familiares: $informacoesDoCPF->familiares,
                    nomePai: $informacoesDoCPF->nomePai,
                    cpfPai: $informacoesDoCPF->cpfPai,
                    rg: $informacoesDoCPF->rg,
                    pis: $informacoesDoCPF->pis,
                    carteiraTrabalho: $informacoesDoCPF->carteiraTrabalho,
                    telefones: $informacoesDoCPF->telefones,
                    emails: $informacoesDoCPF->emails,
                    enderecos: $informacoesDoCPF->enderecos,
                );

                $comando->executar();

           }catch (Exception $erro) {
                throw new Exception("Erro ao cadastrar novo cliente apartir de consulta de informações na internet. {$erro->getMessage()}");
           }

            try{

                $this->container->get(LidarCadastrarNovoCliente::class)->lidar($comando);

            }catch (Exception $erro) {
                throw new Exception("Erro ao lidar com o cadastro de novo cliente. {$erro->getMessage()}");
            }
       }

       return null;
    }
}