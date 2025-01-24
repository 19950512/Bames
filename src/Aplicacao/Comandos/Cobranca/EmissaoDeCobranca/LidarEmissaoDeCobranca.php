<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\EmissaoDeCobranca;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Aplicacao\Compartilhado\Entidades\EntidadeUsuarioLogado;
use App\Dominio\Entidades\Boleto\Enumerados\Status;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\Entidades\Cobranca\Enumerados\Parcela;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoDesconto;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\ObjetoValor\Telefone;
use App\Dominio\ObjetoValor\Valor;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraCriarBoleto;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\EntradaFronteiraCriarUmaCobranca;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\PlanoDeContas\RepositorioPlanoDeContas;
use App\Infraestrutura\Adaptadores\PlataformaDeCobranca\ImplementacaoNenhumPlataformaDeCobranca;
use DI\Container;
use Exception;
use Override;

final class LidarEmissaoDeCobranca implements Lidar
{
    public function __construct(
        private RepositorioClientes $repositorioCliente,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private RepositorioCobranca $repositorioCobranca,
        private EntidadeUsuarioLogado $entidadeUsuarioLogado,
        private RepositorioPlanoDeContas $repositorioPlanoDeContas,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private RepositorioBoleto $repositorioBoleto,
        private Discord $discord,
        private Ambiente $ambiente,
        private Container $container
    ){}

    #[Override] public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoEmissaoDeCobranca::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $cobrancaCodigo = new IdentificacaoUnica();
        $descricaoCobranca = $comando->getDescricao();
        $meioDePagamento = $comando->getMeioDePagamento();
        $dataVencimento = $comando->getDataVencimento();
        $clienteCodigo = $comando->getClienteCodigo();
        $composicaoDaCobranca = $comando->getComposicaoDaCobranca();
        $contaBancariaCodigo = $comando->getContaBancariaCodigo();
        $juros = $comando->getValorJuros();
        $multa = $comando->getValorMulta();
        $parcelas = $comando->getParcelas();

        try {

            $parcela = Parcela::from($parcelas);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível identificar a quantidade de parcelas. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível identificar a quantidade de parcelas. ".$erro->getMessage());
        }


        try {
            $juros = new Valor($juros);
        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível identificar o valor dos juros. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível identificar o valor dos juros. ".$erro->getMessage());
        }

        try {
            $multa = new Valor($multa);
        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível identificar o valor da multa. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível identificar o valor da multa. ".$erro->getMessage());
        }

        try {
            $meioDePagamento = MeioPagamento::from($meioDePagamento);
        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível identificar o meio de pagamento. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível identificar o meio de pagamento. ".$erro->getMessage());
        }

        try {

            $clienteDados = $this->repositorioCliente->buscarClientePorCodigo(
                codigoCliente: $clienteCodigo,
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );

            $entidadeCliente = EntidadeCliente::instanciarEntidadeCliente($clienteDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível encontrar o cliente. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível encontrar o cliente. ".$erro->getMessage());
        }

        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $contaBancariaCodigo,
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );

            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível encontrar a conta bancária. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível encontrar a conta bancária. ".$erro->getMessage());
        }

        $composicaoDaCobranca = array_map(function($item){
            return [
                'descricao' => $item['descricao'],
                'planoDeContaCodigo' => $item['planoDeContaCodigo'],
                'planoDeContaNome' => $this->repositorioPlanoDeContas->buscarPlanoDeContaPorCodigo((int) $item['planoDeContaCodigo'])->planoDeContasNome,
                'valor' => $item['valor']
            ];
        }, $composicaoDaCobranca);

        try {
            $entidadeCobrancaDados = new Cobranca(
                cobrancaCodigo: $cobrancaCodigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                clienteCodigo: $entidadeCliente->codigo->get(),
                clienteNomeCompleto: $entidadeCliente->nomeCompleto->get(),
                dataVencimento: $dataVencimento,
                mensagem: $descricaoCobranca,
                meioDePagamento: $meioDePagamento->value,
                multa: $multa->get(),
                composicaoDaCobranca: $composicaoDaCobranca,
                juros: $juros->get(),
                valorDescontoAntecipacao: 0,
                tipoDesconto: TipoDesconto::PERCENTUAL->value,
                tipoJuros: TipoJuro::PERCENTUAL->value,
                tipoMulta: TipoMulta::PERCENTUAL->value,
                parcela: $parcela->value,
            );
            $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($entidadeCobrancaDados);
        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível criar a cobrança. ".$erro->getMessage()
            );
            throw new Exception("Ops, não foi possível criar a cobrança. ".$erro->getMessage());
        }

        try {

            $parametrosNovaCobranca = new EntradaFronteiraCriarUmaCobranca(
                cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                empresaCodigo: $entidadeCobranca->empresaCodigo->get(),
                contaBancariaCodigo: $entidadeCobranca->contaBancariaCodigo->get(),
                clienteCodigo: $entidadeCobranca->clienteCodigo->get(),
                clienteNomeCompleto: $entidadeCliente->nomeCompleto->get(),
                dataVencimento: $entidadeCobranca->dataVencimento->format('Y-m-d'),
                mensagem: $entidadeCobranca->mensagem->get(),
                meioDePagamento: $entidadeCobranca->meioDePagamento->value,
                composicaoDaCobranca: $entidadeCobranca->composicaoDaCobranca->toArray(),
                multa: $entidadeCobranca->multa->get(),
                juros: $entidadeCobranca->juros->get(),
                valorDescontoAntecipacao: $entidadeCobranca->valorDescontoAntecipacao->get(),
                tipoDesconto: $entidadeCobranca->tipoDesconto->value,
                tipoJuros: $entidadeCobranca->tipoJuros->value,
                tipoMulta: $entidadeCobranca->tipoMulta->value,
                parcela: $parcela->value
            );

            $this->repositorioCobranca->criarUmaCobranca($parametrosNovaCobranca);

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $cobrancaCodigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                descricao: $this->entidadeUsuarioLogado->nomeCompleto->get().", criou a cobrança para o cliente ".$entidadeCliente->nomeCompleto->get()." no valor de ".$entidadeCobranca->valorTotalComposicaoDaCobranca->get()
            );

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível salvar a cobrança. ".$erro->getMessage()
            );

            $this->repositorioCobranca->novoEvento(
                cobrancaCodigo: $cobrancaCodigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                descricao: 'Ops, não foi possível criar a cobrança. '.$erro->getMessage()
            );

            throw new Exception("Ops, não foi possível criar a cobrança. ".$erro->getMessage());
        }

        if($meioDePagamento == MeioPagamento::Boleto || $meioDePagamento == MeioPagamento::Cartao){

            try {

                $this->repositorioCobranca->novoEvento(
                    cobrancaCodigo: $cobrancaCodigo->get(),
                    empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                    descricao: "Enviamos a cobrança para emitir o boleto a plataforma de cobrança. Em breve um retorno."
                );

                $this->emitirBoleto(
                    entidadeCobranca: $entidadeCobranca,
                    entidadeContaBancaria: $entidadeContaBancaria,
                    entidadeCliente: $entidadeCliente,
                );

                $this->repositorioCobranca->novoEvento(
                    cobrancaCodigo: $cobrancaCodigo->get(),
                    empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                    descricao: "Boleto emitido com sucesso, aguardando registro na plataforma de cobrança."
                );

            }catch (Exception $erro){

                $this->repositorioCobranca->novoEvento(
                    cobrancaCodigo: $cobrancaCodigo->get(),
                    empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                    descricao: 'Ops, não foi possível emitir a cobrança na plataforma. '.$erro->getMessage()
                );
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::Cobrancas,
                    mensagem: "Ops, não foi possível emitir a cobrança na plataforma. ".$erro->getMessage()
                );
                throw new Exception("Ops, não foi possível emitir a cobrança na plataforma. ".$erro->getMessage());
            }
        }

        return null;
    }

    private function emitirBoleto(EntidadeCobranca $entidadeCobranca, EntidadeContaBancaria $entidadeContaBancaria, EntidadeCliente $entidadeCliente): void
    {

        if($entidadeCobranca->valorTotalComposicaoDaCobranca->get() <= 0){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível emitir o boleto. O valor da cobrança é menor ou igual a zero."
            );
            throw new Exception("Ops, não foi possível emitir o boleto. O valor da cobrança é menor ou igual a zero.");
        }

        $boletoID = new IdentificacaoUnica();
        $parametrosCriarBoleto = new EntradaFronteiraCriarBoleto(
            empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
            boleto_id: $boletoID->get(),
            cobranca_id: $entidadeCobranca->cobrancaCodigo->get(),
            cliente_id: $entidadeCobranca->clienteCodigo->get(),
            conta_bancaria_id: $entidadeCobranca->contaBancariaCodigo->get(),
            valor: $entidadeCobranca->valorTotalComposicaoDaCobranca->get(),
            data_vencimento: $entidadeCobranca->dataVencimento->format('Y-m-d'),
            mensagem: $entidadeCobranca->composicaoDaCobranca->getMensagem(),
            multa: $entidadeCobranca->multa->get(),
            juros: $entidadeCobranca->juros->get(),
            seu_numero: $boletoID->get(),
            status: Status::NAO_REGISTRADO->value
        );
        $this->repositorioBoleto->criarBoleto($parametrosCriarBoleto);

        try {

            $plataformaDeCobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                container: $this->container
            );

        }catch(Exception $e){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage()
            );
            throw new Exception("Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage());
        }

        if(is_a($plataformaDeCobranca, ImplementacaoNenhumPlataformaDeCobranca::class)){
            $this->repositorioContaBancaria->novoEvento(
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                eventoDescricao: 'Não foi possível emitir o boleto, a conta bancária não possui uma plataforma de cobrança configurada.'
            );
            return;
        }

        try {
            $parametrosBoleto = new EntradaFronteiraEmitirBoleto(
                clientIDAPI: $entidadeContaBancaria->clientIDAPI->get(),
                chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
                valor: $entidadeCobranca->valorTotalComposicaoDaCobranca->get(),
                vencimento: $entidadeCobranca->dataVencimento->format('Y-m-d'),
                juros: $entidadeCobranca->juros->get(),
                tipoJuros: $entidadeCobranca->tipoJuros->name,
                tipoCobranca: $entidadeCobranca->meioDePagamento->name,
                multa: $entidadeCobranca->multa->get(),
                tipoMulta: $entidadeCobranca->tipoMulta->name,
                mensagem: $entidadeCobranca->composicaoDaCobranca->getMensagem(),
                desconto: $entidadeCobranca->valorDescontoAntecipacao->get(),
                parcelas: $entidadeCobranca->parcela->value,
                pagadorNomeCompleto: $entidadeCliente->nomeCompleto->get(),
                pagadorDocumentoNumero: $entidadeCliente->documento->get(),
                pagadorEmail: is_a($entidadeCliente->email, Email::class) ? $entidadeCliente->email->get() : '',
                pagadorTelefone: is_a($entidadeCliente->telefone, Telefone::class) ? $entidadeCliente->telefone->get() : '',
                contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente === AmbienteConta::Producao
            );
            $resposta = $plataformaDeCobranca->emitirBoleto($parametrosBoleto);

            $parametrosBoletoRecemEmitido = new EntradaFronteiraBoletoFoiEmitidoNaPlataformaCobranca(
                business_id: $entidadeCobranca->empresaCodigo->get(),
                boleto_id: $boletoID->get(),
                cobranca_id_plataforma_API_cobranca: $resposta->codigoCobrancaNaPlataformaAPICobranca,
                boleto_id_plataforma_API_cobranca: $resposta->codigoBoletoNaPlataformaAPICobranca,
                boleto_pagador_id_plataforma_API_cobranca: $resposta->codigoPagadorNaPlataformaAPICobranca,
                nosso_numero: $resposta->nossoNumero,
                linha_digitavel: $resposta->linhaDigitavel,
                codigo_barras: $resposta->codigoBarras,
                url: $resposta->urlBoleto,
                status: Status::EMITIDO_AGUARDANDO_REGISTRO->value,
                respostaCompletaDaPlataforma: $resposta->respostaCompleta
            );
            $this->repositorioBoleto->boletoFoiEmitidoNaPlataformaCobranca($parametrosBoletoRecemEmitido);

            $this->repositorioCobranca->atualizarCodigoDaCobrancaNaPlataforma(
                cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                codigoDaCobrancaNaPlataformaDeCobranca: $resposta->codigoCobrancaNaPlataformaAPICobranca
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Boleto emitido com sucesso. Cliente: ".$entidadeCliente->nomeCompleto->get()." - Valor: ".$entidadeCobranca->valorTotalComposicaoDaCobranca->get()
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Informações do boleto: ".PHP_EOL."- Nosso Número: ".$resposta->nossoNumero.PHP_EOL."- Status: ".$resposta->status.PHP_EOL."- Linha Digitável: ".$resposta->linhaDigitavel.PHP_EOL."- Código de Barras: ".$resposta->codigoBarras.PHP_EOL."- URL do Boleto: ".$resposta->urlBoleto
            );

            $this->repositorioContaBancaria->novoEvento(
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                eventoDescricao: 'Boleto emitido com sucesso e aguardando registro na plataforma para o cliente '.$entidadeCliente->nomeCompleto->get().' no valor de '.$entidadeCobranca->valorTotalComposicaoDaCobranca->get().'.'
            );

        } catch (Exception $e) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::Cobrancas,
                mensagem: "Ops, erro ao emitir o boleto. ".$e->getMessage()
            );

            $this->repositorioContaBancaria->novoEvento(
                contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                empresaCodigo: $this->entidadeEmpresarial->codigo->get(),
                eventoDescricao: "Ops, não foi possível emitir o boleto. ".$e->getMessage()
            );

            throw new Exception("Ops, não foi possível emitir o boleto. ".$e->getMessage());
        }
    }
}