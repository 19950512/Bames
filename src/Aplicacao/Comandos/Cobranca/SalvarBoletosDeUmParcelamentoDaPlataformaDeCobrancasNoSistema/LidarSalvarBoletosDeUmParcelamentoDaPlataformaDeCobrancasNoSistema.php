<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Cobranca\SalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema;


use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma\ComandoBoletoFoiAceitoNaPlataforma;
use App\Aplicacao\Comandos\Cobranca\Boleto\BoletoFoiAceitoNaPlataforma\LidarBoletoFoiAceitoNaPlataforma;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\BoletoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Dominio\Entidades\Cobranca\EntidadeCobranca;
use App\Dominio\Entidades\Cobranca\Enumerados\MeioPagamento;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoDesconto;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoJuro;
use App\Dominio\Entidades\Cobranca\Enumerados\TipoMulta;
use App\Dominio\Entidades\ContaBancaria\EntidadeContaBancaria;
use App\Dominio\Entidades\ContaBancaria\Enumerados\AmbienteConta;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI;
use App\Dominio\Repositorios\Boleto\Fronteiras\EntradaFronteiraCriarBoleto;
use App\Dominio\Repositorios\Boleto\RepositorioBoleto;
use App\Dominio\Repositorios\Cobranca\Fronteiras\Cobranca;
use App\Dominio\Repositorios\Cobranca\Fronteiras\EntradaFronteiraCriarUmaCobranca;
use App\Dominio\Repositorios\Cobranca\RepositorioCobranca;
use App\Dominio\Repositorios\ContaBancaria\RepositorioContaBancaria;
use App\Dominio\Repositorios\Empresa\RepositorioEmpresa;
use DI\Container;
use Exception;

final class LidarSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema implements Lidar
{

    public function __construct(
        private RepositorioEmpresa $repositorioEmpresa,
        private RepositorioContaBancaria $repositorioContaBancaria,
        private RepositorioBoleto $repositorioBoleto,
        private RepositorioCobranca $repositorioCobranca,
        private Container $container,
        private Discord $discord,
    ){}

    public function lidar(Comando $comando): null
    {

        if (!is_a($comando, ComandoSalvarBoletosDeUmParcelamentoDaPlataformaDeCobrancasNoSistema::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();
        $contaBancariaCodigo = $comando->obterCodigoContaBancaria();
        $codigoParcelamentoNaPlataformaDeCobranca = $comando->obterCodigoParcelamentoNaPlataformaDeCobranca();

        try {
            $empresaCodigo = new IdentificacaoUnica($empresaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da empresa precisa ser informado adequadamente.");
        }
        try {
            $contaBancariaCodigo = new IdentificacaoUnica($contaBancariaCodigo);
        }catch (Exception $erro){
            throw new Exception("O código da conta bancária precisa ser informado adequadamente.");
        }

        try {

            $empresaDados = $this->repositorioEmpresa->buscarEmpresaPorCodigo($empresaCodigo->get());
            $entidadeEmpresarial = EntidadeEmpresarial::instanciarEntidadeEmpresarial($empresaDados);

        } catch (Exception $erro) {

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                mensagem: "Empresa não encontrada. - {$erro->getMessage()}"
            );
            throw new Exception("Empresa não encontrada. - {$erro->getMessage()}");
        }


        try {

            $contaBancariaDados = $this->repositorioContaBancaria->buscarContaBancariaPorCodigo(
                contaBancariaCodigo: $contaBancariaCodigo->get(),
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $entidadeContaBancaria = EntidadeContaBancaria::instanciarEntidadeContaBancaria($contaBancariaDados);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                mensagem: "Ops, não foi possível obter a conta bancária do boleto. {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível obter a conta bancária do boleto. {$erro->getMessage()}");
        }

        try {

            $cobrancaDados = $this->repositorioCobranca->buscarCobrancaPorCodigoDaPlataformaAPI(
                cobrancaCodigoPlataforma: $codigoParcelamentoNaPlataformaDeCobranca,
                empresaCodigo: $entidadeEmpresarial->codigo->get()
            );

            $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($cobrancaDados);

        }catch (Exception $erro){

            try {

                // Vamos criar uma cobranca temporaria, mas depois vamos atualiza-la com os dados da plataforma
                $entidadeCobrancaDados = new Cobranca(
                    cobrancaCodigo: (new IdentificacaoUnica())->get(),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    contaBancariaCodigo: $entidadeContaBancaria->codigo->get(),
                    clienteCodigo: '',
                    clienteNomeCompleto: 'Ada Lovelace',
                    dataVencimento: '',
                    mensagem: 'mazoq',
                    meioDePagamento: MeioPagamento::Boleto->value,
                    multa: 0,
                    composicaoDaCobranca: [],
                    juros: 0,
                    valorDescontoAntecipacao: 0,
                    tipoDesconto: TipoDesconto::PERCENTUAL->value,
                    tipoJuros: TipoJuro::PERCENTUAL->value,
                    tipoMulta: TipoMulta::PERCENTUAL->value,
                    parcela: 1,
                );
                $entidadeCobranca = EntidadeCobranca::instanciarEntidadeCobranca($entidadeCobrancaDados);

                $parametrosNovaCobranca = new EntradaFronteiraCriarUmaCobranca(
                    cobrancaCodigo: $entidadeCobranca->cobrancaCodigo->get(),
                    empresaCodigo: $entidadeCobranca->empresaCodigo->get(),
                    contaBancariaCodigo: $entidadeCobranca->contaBancariaCodigo->get(),
                    clienteCodigo: $entidadeCobranca->clienteCodigo->get(),
                    clienteNomeCompleto: $entidadeCobranca->pagadorNomeCompleto->get(),
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
                    parcela: $entidadeCobranca->parcela->value,
                );

                $this->repositorioCobranca->criarUmaCobranca($parametrosNovaCobranca);
            }catch (Exception $erro){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                    mensagem: "Ops, não foi possível obter a cobrança do parcelamento. {$erro->getMessage()}"
                );
                throw new Exception("Ops, não foi possível obter a cobrança do parcelamento. {$erro->getMessage()}");
            }
        }

        try {

            $plataformaDeCobranca = $entidadeContaBancaria->obterPlataformaDeCobranca(
                container: $this->container
            );

        }catch(Exception $e){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                mensagem: "Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage()
            );
            throw new Exception("Ops, não foi possível obter a plataforma de cobrança. ".$e->getMessage());
        }

        $parametroTodosOsBoletosDoParcelamentoParaAPlataforma = new EntradaObterTodosOsBoletosDoParcelamento(
            contaBancariaAmbienteProducao: $entidadeContaBancaria->ambiente == AmbienteConta::Producao,
            chaveAPI: $entidadeContaBancaria->chaveAPI->get(),
            codigoParcelamento: $codigoParcelamentoNaPlataformaDeCobranca
        );

        try {

            $todosOsBoletosDoParcelamento = $plataformaDeCobranca->obterTodosOsBoletosDoParcelamento($parametroTodosOsBoletosDoParcelamentoParaAPlataforma);

            foreach($todosOsBoletosDoParcelamento->getBoletos() as $boleto){

                if(!is_a($boleto, BoletoParcelamento::class)){
                    continue;
                }

                if(!$this->repositorioBoleto->existeUmBoletoNoSistemaComEsseCodigoDePlataformaDeCobranca(
                    codigoBoletoNaPlataformaAPI: $boleto->codigoBoletoNaPlataforma,
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                )){

                    $boletoID = new IdentificacaoUnica();
                    $parametrosSalvarBoleto = new EntradaFronteiraCriarBoleto(
                        empresaCodigo: $entidadeEmpresarial->codigo->get(),
                        boleto_id: $boletoID->get(),
                        cobranca_id: $entidadeCobranca->cobrancaCodigo->get(),
                        cliente_id: $entidadeCobranca->clienteCodigo->get(),
                        conta_bancaria_id: $entidadeContaBancaria->codigo->get(),
                        valor: $boleto->valor,
                        data_vencimento: $boleto->dataVencimento,
                        mensagem: $boleto->descricao,
                        multa: $boleto->multa,
                        juros: $boleto->juros,
                        seu_numero: $boletoID->get(),
                        status: $boleto->status,
                        boletoCodigoNaPlataforma: $boleto->codigoBoletoNaPlataforma,
                        cobrancaCodigoNaPlataforma: $boleto->codigoCobrancaNaPlataformaAPI,
                    );

                    try {

                        $this->repositorioBoleto->criarBoleto($parametrosSalvarBoleto);

                    }catch (Exception $erro){
                        $this->discord->enviar(
                            canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                            mensagem: "Ops, não foi possível salvar o boleto. - {$erro->getMessage()}"
                        );
                        continue;
                    }
                }

                $parametrosAtualizarBoleto = new EntradaAtualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI(
                    codigoBoletoNaPlataformaAPI: $boleto->codigoBoletoNaPlataforma,
                    codigoPagadorIDPlataformaAPI: $boleto->pagadorCodigoNaPlataforma,
                    codigoCobrancaNaPlataformaAPI: $boleto->codigoCobrancaNaPlataformaAPI,
                    respostaCompletaDaPlataforma: json_encode($boleto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    empresaCodigo: $entidadeEmpresarial->codigo->get(),
                    status: $boleto->status,
                    nossoNumero: $boleto->nossoNumero,
                    codigoDeBarras: $boleto->codigoDeBarras,
                    linhaDigitavel: $boleto->linhaDigitavel,
                    urlBoleto: $boleto->urlBoleto,
                    mensagem: $boleto->descricao,
                    valor: $boleto->valor,
                    parcela: $boleto->parcela,
                );

                $this->repositorioBoleto->atualizarInformacoesDoBoletoPorCodigoNaPlataformaAPI($parametrosAtualizarBoleto);

                if(!empty($boleto->codigoDeBarras)){

                    try {

                        $comando = new ComandoBoletoFoiAceitoNaPlataforma(
                            empresaCodigo: $entidadeEmpresarial->codigo->get(),
                            boletoCodigoNaPlataforma: $boleto->codigoBoletoNaPlataforma
                        );
                        $comando->executar();

                        $this->container->get(LidarBoletoFoiAceitoNaPlataforma::class)->lidar($comando);

                    }catch (Exception $erro){
                        $this->discord->enviar(
                            canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                            mensagem: "Ops, não foi possível criar o comando para aceitar o boleto. - {$erro->getMessage()}"
                        );
                        continue;
                    }
                }
            }

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::BoletosSalvarParcelamento,
                mensagem: "Ops, não foi possível obter os boletos do parcelamento. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível obter os boletos do parcelamento. - {$erro->getMessage()}");
        }

        return null;
    }
}