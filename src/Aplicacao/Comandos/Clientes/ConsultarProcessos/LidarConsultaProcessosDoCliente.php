<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\ConsultarProcessos;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\ConsultarInformacoesNaInternet\Enums\CustosInformacoesNaInternet;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\DocumentoDeIdentificacao;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\Fronteiras\EntradaFronteiraSalvarRequestPorDocumento;
use App\Dominio\Repositorios\Clientes\ConsultarInformacoesNaInternet\RepositorioConsultarInformacoesNaInternet;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarRequestPorOAB;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteirasAtualizaORequestPorOABResponseERequest;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\Envolvido;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\Fonte;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;
use Exception;
use Override;

final class LidarConsultaProcessosDoCliente implements Lidar
{

    public function __construct(
        private ConsultaDeProcesso $consultaDeProcesso,
        private RepositorioConsultaDeProcesso $repositorioConsultaDeProcesso,
        private RepositorioClientes $repositorioCliente,
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioConsultarInformacoesNaInternet $repositorioConsultarInformacoesNaInternet,
        private Discord $discord,
        private Cache $cache
    ){}

    #[Override] public function lidar(Comando $comando): mixed
    {

        if (!is_a($comando, ComandoLidarConsultarProcessosDoCliente::class)) {
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

        $documento = $comando->obterDocumento();

        try {
            $documento = new DocumentoDeIdentificacao($documento);
        }catch (Exception $erro){
            throw new Exception("O documento precisa ser informado adequadamente.");
        }

        $requestID = new IdentificacaoUnica();

        if($entidadeEmpresarial->saldoCreditos->get() <= CustosInformacoesNaInternet::CONSULTA_PROCESSOS_DOCUMENTO->buscarCusto()){

            $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                contaCodigo: $usuarioResponsavel->codigo->get(),
                requestID: $requestID->get(),
                descricao: "Saldo de créditos insuficiente para consultar processos do documento {$documento->get()}",
                momento: date('Y-m-d H:i:s'),
            );

            $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

            throw new Exception("Saldo de créditos insuficiente, consulte o financeiro.");
        }

        $this->repositorioConsultarInformacoesNaInternet->cobrarCustoParaConsultarDocumento(
            documento: $documento->get(),
            custo: CustosInformacoesNaInternet::CONSULTA_PROCESSOS_DOCUMENTO->buscarCusto()
        );

        $clienteDados = $this->repositorioCliente->buscarClientePorDocumento(
            documento: $documento->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get()
        );

        $clienteEntidade = EntidadeCliente::instanciarEntidadeCliente($clienteDados);

        if($this->repositorioConsultaDeProcesso->documentoJaFoiConsultadaNosUltimosDias($documento->get())){

            try {

                $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaCodigo: $usuarioResponsavel->codigo->get(),
                    requestID: $requestID->get(),
                    descricao: "{$documento->get()} já foi consultado nos últimos dias - copiando processos",
                    momento: date('Y-m-d H:i:s'),
                );

                $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "Documento {$documento->get()} já foi consultado nos últimos dias - copiando processos",
                );

                $this->repositorioConsultaDeProcesso->copiarProcessosDoDocumentoJaConsultada($documento->get(), $entidadeEmpresarial->codigo->get());

                $this->cache->delete("{$entidadeEmpresarial->codigo->get()}/clientes");
                $this->cache->delete("{$entidadeEmpresarial->codigo->get()}/cliente/{$clienteEntidade->codigo->get()}/processos");

                $this->repositorioConsultaDeProcesso->atualizaORequestPorOABParaFinalizado($requestID->get());

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "Consulta de processos pela OAB finalizada",
                );
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                    mensagem: "Processos do documento {$documento->get()} copiados com sucesso"
                );

                return null;

            }catch (Exception $erro) {

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Exceptions,
                    mensagem: "Erro ao copiar processos de OAB já consultada. - {$erro->getMessage()}"
                );
                throw new Exception("Erro ao copiar processos de OAB já consultada. - {$erro->getMessage()}");
            }
        }

        $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorDocumento(
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            contaCodigo: $usuarioResponsavel->codigo->get(),
            requestID: $requestID->get(),
            descricao: "Consultando processos do documento {$documento->get()}",
            momento: date('Y-m-d H:i:s'),
        );

        $this->repositorioConsultarInformacoesNaInternet->salvarRequestPorDocumento($paramsSalvarRequestPorOAB);

        $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorOAB(
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            contaCodigo: $usuarioResponsavel->codigo->get(),
            requestID: $requestID->get(),
            oab: $documento->get(),
            descricao: "Aguarde enquanto buscamos informações do Documento",
            momento: date('Y-m-d H:i:s'),
        );

        $this->repositorioConsultaDeProcesso->salvarRequestPorOAB($paramsSalvarRequestPorOAB);
        $this->repositorioConsultaDeProcesso->salvaEvento(
            requestID: $requestID->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            evento: "Iniciando consulta de processos por Documento {$documento->get()}",
        );

        try {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                mensagem: "Iniciando consulta de processos pelo documento {$documento->get()}"
            );

           $processos = $this->consultaDeProcesso->numeroDocumento($documento->get());

            $parametrosAtualizarRequestPorOAB = new EntradaFronteirasAtualizaORequestPorOABResponseERequest(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                requestID: $requestID->get(),
                descricao: $processos->quantidadeDeProcessos . " processos encontrados",
                momento: date('Y-m-d H:i:s'),
                tipo: $processos->tipo,
                status: "IN_PROGRESS",
                quantidadeDeProcessos: $processos->quantidadeDeProcessos,
                payload_request: json_encode($processos->get(), JSON_PRETTY_PRINT),
                payload_response: json_encode($processos->get(), JSON_PRETTY_PRINT)
            );

            $this->repositorioConsultaDeProcesso->atualizaORequestPorOABResponseERequest($parametrosAtualizarRequestPorOAB);

            $this->repositorioConsultaDeProcesso->salvaEvento(
                requestID: $requestID->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Encontramos " . $processos->quantidadeDeProcessos . " processos, vamos salvar no banco de dados",
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                mensagem: "Encontramos " . $processos->quantidadeDeProcessos . " processos, vamos salvar no banco de dados"
            );
            foreach ($processos->get() as $processo) {

                if($this->repositorioConsultaDeProcesso->jaExisteUmProcessoComEsteCNJ(
                    processoCNJ: $processo->cnj,
                    empresaCodigo: $entidadeEmpresarial->codigo->get()
                )){
                    continue;
                }

                $codigoDoProcesso = new IdentificacaoUnica();

                $ultimaMovimentacaoData = '';
                $ultimaMovimentacaoDescricao = '';

                $parametrosProcessoSalvar = new EntradaFronteiraSalvarProcesso(
                    business_id: $entidadeEmpresarial->codigo->get(),
                    processo_codigo: $codigoDoProcesso->get(),
                    processo_numero_cnj: (string)$processo->cnj,
                    processo_data_ultima_movimentacao: (string)$processo->dataUltimaMovimentacao,
                    processo_quantidade_movimentacoes: (string)$processo->quantidadeMovimentacoes,
                    processo_demandante: (string)$processo->demandante,
                    processo_demandado: (string)$processo->demandado,
                    processo_ultima_movimentacao_descricao: $ultimaMovimentacaoDescricao,
                    processo_ultima_movimentacao_data: $ultimaMovimentacaoData,
                    oab_ou_documento_consultada: $documento->get()
                );

                foreach ($processo->fontes as $fonte) {

                    $informacoesComplementares = array_map(function ($item) {
                        return "{$item->tipo} | {$item->valor}";
                    }, $fonte->capaEntity->informacoesComplementares ?? []);

                    $dataUltimaVerificacao = '';
                    if (!empty($fonte->dataUltimaVerificacao)) {
                        $dataUltimaVerificacao = date('Y-m-d H:i:s', strtotime($fonte->dataUltimaVerificacao));
                    }

                    $dataUltimaMovimentacao = '';
                    if (!empty($fonte->dataUltimaMovimentacao)) {
                        $dataUltimaMovimentacao = date('Y-m-d H:i:s', strtotime($fonte->dataUltimaMovimentacao));

                        if(empty($ultimaMovimentacaoData)){
                            $ultimaMovimentacaoData = date('Y-m-d H:i:s', strtotime($fonte->dataUltimaMovimentacao));
                            $ultimaMovimentacaoDescricao = (isset($fonte->capaEntity->assunto) ? $fonte->capaEntity->assunto : '');
                        }
                        if(!empty($ultimaMovimentacaoData) and date('Y-m-d H:i:s', strtotime($fonte->dataUltimaMovimentacao)) > date('Y-m-d H:i:s', strtotime($ultimaMovimentacaoData))){
                            $ultimaMovimentacaoData = date('Y-m-d H:i:s', strtotime($fonte->dataUltimaMovimentacao));
                            $ultimaMovimentacaoDescricao = (isset($fonte->capaEntity->assunto) ? $fonte->capaEntity->assunto : '');
                        }
                    }

                    $fonteFinal = new Fonte(
                        codigo: (new IdentificacaoUnica())->get(),
                        nome: $fonte->nome ?? '',
                        descricao: $fonte->sigla ?? '',
                        link: $fonte->link ?? '',
                        tipo: $fonte->tipo ?? '',
                        dataUltimaVerificacao: $dataUltimaVerificacao,
                        dataUltimaMovimentacao: $dataUltimaMovimentacao,
                        segredoJustica: $fonte->segredoJustica,
                        arquivado: $fonte->arquivado,
                        fisico: $fonte->fisico,
                        sistema: (string)$fonte->sistema,
                        quantidadeEnvolvidos: (int)$fonte->quantidadeEnvolvidos,
                        quantidadeMovimentacoes: (int)$fonte->quantidadeMovimentacoes,
                        grau: (int)$fonte->grau,

                        capaClasse: (string)(isset($fonte->capaEntity->classe) ? $fonte->capaEntity->classe : ''),
                        capaAssunto: (string)(isset($fonte->capaEntity->assunto) ? $fonte->capaEntity->assunto : ''),
                        capaArea: (string)(isset($fonte->capaEntity->area) ? $fonte->capaEntity->area : ''),
                        capaOrgaoJulgador: (string)(isset($fonte->capaEntity->orgaoJulgador) ? $fonte->capaEntity->orgaoJulgador : ''),
                        capaValorCausa: (string)(isset($fonte->capaEntity->causaValor) ? $fonte->capaEntity->causaValor : ''),
                        capaValorMoeda: (string)(isset($fonte->capaEntity->causaMoeda) ? $fonte->capaEntity->causaMoeda : ''),
                        capaDataDistribuicao: (string)(isset($fonte->capaEntity->dataDistribuicao) ? $fonte->capaEntity->dataDistribuicao : ''),

                        tribunalID: (string)$fonte->tribunalEntity->codigoTribunal ?? '',
                        tribunalNome: (string)$fonte->tribunalEntity->nome ?? '',
                        tribunalSigla: (string)$fonte->tribunalEntity->sigla ?? '',

                        informacoesComplementares: $informacoesComplementares
                    );

                    if (is_array($fonte->envolvidos)) {
                        foreach ($fonte->envolvidos as $envolvido) {

                            $envolvidoDocumento = $envolvido->documento ?? '';
                            if (!empty($envolvidoDocumento)) {
                                $envolvidoDocumento = new DocumentoDeIdentificacao($envolvidoDocumento);
                            }
                            $fonteFinal->addEnvolvido(new Envolvido(
                                codigo: (new IdentificacaoUnica())->get(),
                                nomeCompleto: $envolvido->nomeCompleto,
                                quantidadeProcessos: (int) $envolvido->quantidadeProcessos,
                                documento: is_a($envolvidoDocumento, DocumentoIdentificacao::class) ? $envolvidoDocumento->get() : '',
                                tipo: $envolvido->tipo,
                                polo: $envolvido->polo,
                                oab: !empty($envolvido->oab) ? (new OAB($envolvido->oab))->get() : '',
                            ));
                        }
                    }

                    $parametrosProcessoSalvar->addFonte($fonteFinal);
                }

                $parametrosProcessoSalvar->processo_ultima_movimentacao_descricao = $ultimaMovimentacaoDescricao;
                $parametrosProcessoSalvar->processo_ultima_movimentacao_data = $ultimaMovimentacaoData;

                $this->repositorioConsultaDeProcesso->salvarProcesso($parametrosProcessoSalvar);

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "Processo {$codigoDoProcesso->get()} | CNJ: {$processo->cnj} salvo com sucesso",
                );
            }

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                mensagem: "Processos do documento {$documento->get()} salvos com sucesso"
            );

            return null;

        }catch(Exception $erro){

            if(str_contains($erro->getMessage(), 'Esse documento não possui processos')){

                $parametrosAtualizarRequestPorOAB = new EntradaFronteirasAtualizaORequestPorOABResponseERequest(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    requestID: $requestID->get(),
                    descricao: "Esse documento não possui processos",
                    momento: date('Y-m-d H:i:s'),
                );
                $this->repositorioConsultaDeProcesso->atualizaORequestPorOABResponseERequest($parametrosAtualizarRequestPorOAB);

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "Esse documento não possui processos",
                );

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                    mensagem: "Esse documento não possui processos"
                );

                return null;
            }

            $parametrosAtualizarRequestPorOAB = new EntradaFronteirasAtualizaORequestPorOABResponseERequest(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                requestID: $requestID->get(),
                descricao: "Falha ao consultar processos do documento na plataforma - " . $erro->getMessage(),
                momento: date('Y-m-d H:i:s'),
            );
            $this->repositorioConsultaDeProcesso->atualizaORequestPorOABResponseERequest($parametrosAtualizarRequestPorOAB);

            $this->repositorioConsultaDeProcesso->salvaEvento(
                requestID: $requestID->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Falha ao consultar processos do documento na plataforma - " . $erro->getMessage(),
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                mensagem: "Falha ao consultar processos do documento na plataforma - {$erro->getMessage()}"
            );

            throw new Exception("Erro ao consultar processos pela OAB na plataforma. - {$erro->getMessage()}");

        } finally {

            $this->repositorioConsultaDeProcesso->atualizaORequestPorOABParaFinalizado($requestID->get());

            $this->repositorioConsultaDeProcesso->salvaEvento(
                requestID: $requestID->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Consulta de processos pela OAB finalizada",
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorDocumento,
                mensagem: "Consulta de processos pelo documento {$documento->get()} finalizada"
            );

            $this->cache->delete("{$entidadeEmpresarial->codigo->get()}/clientes");
            $this->cache->delete("{$entidadeEmpresarial->codigo->get()}/cliente/{$clienteEntidade->codigo->get()}/processos");
        }
    }
}