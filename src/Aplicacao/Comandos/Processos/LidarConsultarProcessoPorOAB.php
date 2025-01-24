<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Processos;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\ObjetoValor\CNPJ;
use App\Dominio\ObjetoValor\CPF;
use App\Dominio\ObjetoValor\DocumentoIdentificacao;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\Envolvido;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\Fonte;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteirasAtualizaORequestPorOABResponseERequest;
use Exception;
use App\Dominio\ObjetoValor\OAB;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use App\Aplicacao\Compartilhado\Processos\ConsultaDeProcesso;
use App\Dominio\Repositorios\RepositorioConsultaDeProcesso\RepositorioConsultaDeProcesso;
use App\Dominio\Repositorios\ConsultaDeProcesso\Fronteiras\EntradaFronteiraSalvarRequestPorOAB;
use http\Env;

final readonly class LidarConsultarProcessoPorOAB implements Lidar
{

    public function __construct(
        private ConsultaDeProcesso $consultaDeProcesso,
        private RepositorioConsultaDeProcesso $repositorioConsultaDeProcesso,
        private RepositorioEmpresa $repositorioEmpresa,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoLidarConsultasProcessoPorOAB::class)) {
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
            $oab = new OAB($comando->obterOAB());
        } catch (Exception $e) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Exceptions,
                mensagem: "OAB inválida. - {$e->getMessage()}"
            );
            throw new Exception("OAB inválida. - {$e->getMessage()}");
        }

        $requestID = new IdentificacaoUnica();

        if($this->repositorioConsultaDeProcesso->OABJaFoiConsultadaNosUltimosDias($oab->get())){

            try {

                $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorOAB(
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaCodigo: $usuarioResponsavel->codigo->get(),
                    requestID: $requestID->get(),
                    oab: $oab->get(),
                    descricao: "OAB já foi consultada nos últimos dias - copiando processos",
                    momento: date('Y-m-d H:i:s'),
                );

                $this->repositorioConsultaDeProcesso->salvarRequestPorOAB($paramsSalvarRequestPorOAB);

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "{$oab->get()} já foi consultada nos últimos dias - copiando processos",
                );

                $this->repositorioConsultaDeProcesso->copiarProcessosDeOABJaConsultada($oab->get(), $entidadeEmpresarial->codigo->get());

                $this->repositorioConsultaDeProcesso->atualizaORequestPorOABParaFinalizado($requestID->get());

                $this->repositorioConsultaDeProcesso->salvaEvento(
                    requestID: $requestID->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    evento: "Consulta de processos pela OAB finalizada",
                );
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::ConsultarProcessosPorOAB,
                    mensagem: "{$oab->get()} já foi consultada nos últimos dias - copiando processos"
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

        $paramsSalvarRequestPorOAB = new EntradaFronteiraSalvarRequestPorOAB(
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            contaCodigo: $usuarioResponsavel->codigo->get(),
            requestID: $requestID->get(),
            oab: $oab->get(),
            descricao: "Aguarde enquanto buscamos informações da OAB",
            momento: date('Y-m-d H:i:s'),
        );

        $this->repositorioConsultaDeProcesso->salvarRequestPorOAB($paramsSalvarRequestPorOAB);
        $this->repositorioConsultaDeProcesso->salvaEvento(
            requestID: $requestID->get(),
            empresaCodigo: $entidadeEmpresarial->codigo->get(),
            evento: "Iniciando consulta de processos pela OAB",
        );

        try {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorOAB,
                mensagem: "Iniciando consulta de processos pela {$oab->get()}"
            );

            $processos = $this->consultaDeProcesso->OAB($oab->get());

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
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorOAB,
                mensagem: "Encontramos " . $processos->quantidadeDeProcessos . " processos, vamos salvar no banco de dados"
            );
            foreach ($processos->get() as $processo) {

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
                    oab_ou_documento_consultada: $oab->get()
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
                                if (CPF::valido($envolvidoDocumento)) {
                                    $envolvidoDocumento = new CPF($envolvidoDocumento);
                                } else {
                                    $envolvidoDocumento = new CNPJ($envolvidoDocumento);
                                }
                            }
                            $fonteFinal->addEnvolvido(new Envolvido(
                                codigo: (new IdentificacaoUnica())->get(),
                                nomeCompleto: (string) $envolvido->nomeCompleto,
                                quantidadeProcessos: (int) $envolvido->quantidadeProcessos,
                                documento: (string) is_a($envolvidoDocumento, DocumentoIdentificacao::class) ? $envolvidoDocumento->get() : '',
                                tipo: (string) $envolvido->tipo,
                                polo: (string) $envolvido->polo,
                                oab: (string) !empty($envolvido->oab) ? (new OAB($envolvido->oab))->get() : '',
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
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorOAB,
                mensagem: "Processos da {$oab->get()} salvos com sucesso"
            );
            return null;

        } catch (Exception $erro) {

            $parametrosAtualizarRequestPorOAB = new EntradaFronteirasAtualizaORequestPorOABResponseERequest(
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                requestID: $requestID->get(),
                descricao: "Falha ao consultar processos pela OAB na plataforma - " . $erro->getMessage(),
                momento: date('Y-m-d H:i:s'),
            );
            $this->repositorioConsultaDeProcesso->atualizaORequestPorOABResponseERequest($parametrosAtualizarRequestPorOAB);

            $this->repositorioConsultaDeProcesso->salvaEvento(
                requestID: $requestID->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Falha ao consultar processos pela OAB na plataforma - " . $erro->getMessage(),
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ConsultarProcessosPorOAB,
                mensagem: "Falha ao consultar processos pela OAB na plataforma - {$erro->getMessage()}"
            );

            throw new Exception("Erro ao consultar processos pela OAB na plataforma. - {$erro->getMessage()}");

        } finally {
            $this->repositorioConsultaDeProcesso->atualizaORequestPorOABParaFinalizado($requestID->get());

            $this->repositorioConsultaDeProcesso->salvaEvento(
                requestID: $requestID->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get(),
                evento: "Consulta de processos pela OAB finalizada",
            );
        }
    }
}