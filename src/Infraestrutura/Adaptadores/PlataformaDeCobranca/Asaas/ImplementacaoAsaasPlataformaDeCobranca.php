<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\PlataformaDeCobranca\Asaas;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Cobranca\Enumerados\CobrancaSituacao;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\BoletoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraBaixarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConfigurarWebhook;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraConsultarBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaFronteiraVerificarConexaoComPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\EntradaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraEmitirBoleto;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma;
use App\Aplicacao\Compartilhado\Cobranca\Fronteiras\SaidaObterTodosOsBoletosDoParcelamento;
use App\Aplicacao\Compartilhado\Cobranca\PlataformaDeCobranca;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use Exception;
use GuzzleHttp\Client;
use Override;

final class ImplementacaoAsaasPlataformaDeCobranca implements PlataformaDeCobranca
{

    private string $baseURL = 'https://sandbox.asaas.com/api';

    public function __construct(
        private Discord $discord,
        private Ambiente $ambiente
    ){}

    #[Override] public function configurarWebhook(EntradaFronteiraConfigurarWebhook $parametros): void
    {
        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);

        $headers = [
            'access_token' => $parametros->chaveAPI,
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            'User-Agent' => 'Bames',
        ];


        // Vamos ver se já exise um webhook configurado
        try {

            $response = $client->request('GET', $this->baseURL.'/v3/webhooks', [
                'headers' => $headers,
            ]);

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível verificar o webhook. - $resposta"
                );
                throw new Exception("Ops, não foi possível verificar o webhook. - $resposta");
            }

            $resposta = json_decode($resposta, true);

            if(isset($resposta['error']) and !empty($resposta['error'])){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível verificar o webhook. - $resposta"
                );
                throw new Exception("Ops, não foi possível verificar o webhook. - $resposta");
            }

            if(isset($resposta['data']) and is_array($resposta['data'])){
                foreach($resposta['data'] as $webhooks){
                    if(isset($webhooks['url']) and $webhooks['url'] == $parametros->webhookURL){
                        $this->discord->enviar(
                            canaldeTexto: CanalDeTexto::CobrancasAsaas,
                            mensagem: "Webhook já configurado.".PHP_EOL.json_encode($webhooks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        );
                        return;
                    }
                }
            }

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível verificar o webhook. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível verificar o webhook. - {$erro->getMessage()}");
        }

        try {

            $body = [
                'name' => 'Bames - Webhook',
                'url' => $parametros->webhookURL,
                'email' => 'contato@bames.com.br',
                'enabled' => true,
                'interrupted' => false,
                'apiVersion' => 3,
                'authToken' => $parametros->webhookCodigo,
                'sendType' => 'SEQUENTIALLY',
                'events' => [
                    'PAYMENT_CREATED', // Geração de nova cobrança.
                    'PAYMENT_UPDATED', // Alteração no vencimento ou valor de cobrança existente.
                    'PAYMENT_CONFIRMED', // Cobrança confirmada (pagamento efetuado, porém, o saldo ainda não foi disponibilizado).
                    'PAYMENT_RECEIVED', // Cobrança recebida.
                    'PAYMENT_OVERDUE', // Cobrança vencida.
                    'PAYMENT_DELETED', // Cobrança excluída.
                    'PAYMENT_REFUNDED', // Cobrança estornada.
                    'PAYMENT_BANK_SLIP_VIEWED', // Boleto visualizado.
                    'PAYMENT_CHECKOUT_VIEWED', // Checkout/Fatura visualizado.
                ],
            ];

            $response = $client->request('POST', $this->baseURL.'/v3/webhooks', [
                'body' => json_encode($body),
                'headers' => $headers,
            ]);

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível configurar o webhook. - $resposta"
                );
                throw new Exception("Ops, não foi possível configurar o webhook. - $resposta");
            }

            $resposta = json_decode($resposta, true);

            if(isset($resposta['error']) and !empty($resposta['error'])){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível configurar o webhook. - $resposta"
                );
                throw new Exception("Ops, não foi possível configurar o webhook. - $resposta");
            }

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Webhook configurado com sucesso."
            );
            return;

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível configurar o webhook. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível configurar o webhook. - {$erro->getMessage()}");
        }

    }

    #[Override] public function conexaoEstabelecidaComSucessoComAPlataformaAPICobranca(EntradaFronteiraVerificarConexaoComPlataforma $parametros): true
    {

        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        try {

            $client = new Client([
                'base_uri' => $this->baseURL,
            ]);

            $headers = [
                'access_token' => $parametros->chaveAPI,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                'User-Agent' => 'Bames',
            ];

            $response = $client->request('GET', $this->baseURL.'/v3/customers', [
                'headers' => $headers,
            ]);

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível verificar a conexão com a plataforma. - $resposta"
                );
                throw new Exception("Ops, não foi possível verificar a conexão com a plataforma. - $resposta");
            }

            $resposta = json_decode($resposta, true);

            if(isset($resposta['error']) and !empty($resposta['error'])){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível verificar a conexão com a plataforma. - $resposta"
                );
                throw new Exception("Ops, não foi possível verificar a conexão com a plataforma. - $resposta");
            }

            return true;

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível verificar a conexão com a plataforma. - {$erro->getMessage()}"
            );

            if($erro->getCode() == 401){
                throw new Exception("Credenciais inválida ou acesso não altorizado.");
            }

            throw new Exception("Ops, não foi possível verificar a conexão com a plataforma. - {$erro->getMessage()}");
        }
    }
    public function baixarBoleto(EntradaFronteiraBaixarBoleto $parametros): void
    {

        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        try {

            $client = new Client([
                'base_uri' => $this->baseURL,
            ]);

            $headers = [
                'access_token' => $parametros->chaveAPI,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                'User-Agent' => 'Bames',
            ];

            $response = $client->request('DELETE', $this->baseURL.'/v3/payments/'.$parametros->codigoBoletoNaPlataformaAPICobranca, [
                'headers' => $headers,
            ]);

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível baixar o boleto. - $resposta"
                );
                throw new Exception("Ops, não foi possível baixar o boleto {$parametros->codigoBoletoNaPlataformaAPICobranca}. - $resposta");
            }

            $resposta = json_decode($resposta, true);

            if(!isset($resposta['deleted']) or $resposta['deleted'] == false){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível baixar o boleto {$parametros->codigoBoletoNaPlataformaAPICobranca}. - ".json_encode($resposta)
                );
                throw new Exception("Ops, não foi possível baixar o boleto. - ".json_encode($resposta));
            }

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Boleto {$parametros->codigoBoletoNaPlataformaAPICobranca} baixado com sucesso."
            );
            return;

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível baixar o boleto. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível baixar o boleto {$parametros->codigoBoletoNaPlataformaAPICobranca}. - {$erro->getMessage()}");
        }
    }

    #[Override] public function obterTodosOsBoletosDoParcelamento(EntradaObterTodosOsBoletosDoParcelamento $parametros): SaidaObterTodosOsBoletosDoParcelamento
    {

        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        try {

            $client = new Client([
                'base_uri' => $this->baseURL,
            ]);

            $headers = [
                'access_token' => $parametros->chaveAPI,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                'User-Agent' => 'Bames',
            ];

            $response = $client->request('GET', "$this->baseURL/v3/installments/$parametros->codigoParcelamento/payments", [
                'headers' => $headers,
            ]);

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível obter os boletos do parcelamento. - $resposta"
                );
                throw new Exception("Ops, não foi possível obter os boletos do parcelamento. - $resposta");
            }

            $resposta = json_decode($resposta, true);

            $todosOsBoletosDoParcelamento = new SaidaObterTodosOsBoletosDoParcelamento();

            if(isset($resposta['data']) and is_array($resposta['data'])){

                foreach($resposta['data'] as $boleto){

                    $status = match($boleto['status']){
                        'PENDING' => CobrancaSituacao::AGUARDANDO_PAGAMENTO,
                        default => CobrancaSituacao::DESCONHECIDO,
                    };
                    $pagador = $this->obterClienteDados($boleto['customer'], $parametros->chaveAPI);

                    try {

                        $consultaBoleto = $this->_consultarBoleto(new EntradaFronteiraConsultarBoleto(
                            codigoBoletoNaPlataformaAPICobranca: $boleto['id'],
                            chaveAPI: $parametros->chaveAPI,
                            contaBancariaAmbienteProducao: $parametros->contaBancariaAmbienteProducao
                        ));

                    }catch (Exception $erro){
                        $this->discord->enviar(
                            canaldeTexto: CanalDeTexto::CobrancasAsaas,
                            mensagem: "Ops, não foi possível obter os dados do boleto {$boleto['id']}. - {$erro->getMessage()}"
                        );
                        continue;
                    }

                    $boletoTemporario = new BoletoParcelamento(
                        codigoBoletoNaPlataforma: (string) $boleto['id'],
                        dataVencimento: (string) $boleto['dueDate'],
                        pagadorCodigoNaPlataforma: (string) $boleto['customer'],
                        pagadorNomeCompleto: (string) $pagador['nomeCompleto'],
                        pagadorDocumento: (string) $pagador['documento'],
                        pagadorEmail: (string) $pagador['email'],
                        pagadorTelefone: (string) $pagador['telefone'],
                        nossoNumero: (string) $boleto['nossoNumero'] ?? '',
                        codigoDeBarras: $consultaBoleto->codigoDeBarras,
                        linhaDigitavel: $consultaBoleto->linhaDigitavel,
                        descricao: (string) $boleto['description'],
                        status: $status->value,
                        urlBoleto: (string) $boleto['bankSlipUrl'] ?? '',
                        respostaCompletaDaPlataforma: $consultaBoleto->responsePayload,
                        valor: (float) $boleto['value'] ?? 0,
                        multa: (float) $boleto['fine']['value'] ?? 0,
                        juros: (float) $boleto['interest']['value'] ?? 0,
                        parcela: (int) $boleto['installmentNumber'] ?? 1,
                        codigoCobrancaNaPlataformaAPI: (string) $boleto['installment'] ?? '',
                    );

                    $todosOsBoletosDoParcelamento->adicionarBoleto($boletoTemporario);
                }
            }

            return $todosOsBoletosDoParcelamento;

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível obter os boletos do parcelamento. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível obter os boletos do parcelamento. - {$erro->getMessage()}");
        }
    }

    #[Override] public function consultarBoleto(EntradaFronteiraConsultarBoleto $parametros): SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma
    {
        try {
            return $this->_consultarBoleto($parametros);
        } catch (Exception $e) {
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível consultar o boleto. - {$e->getMessage()}"
            );
            throw new Exception("Ops, não foi possível consultar o boleto. - {$e->getMessage()}");
        }
    }

    #[Override] public function emitirBoleto(EntradaFronteiraEmitirBoleto $parametros): SaidaFronteiraEmitirBoleto
    {
        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        $customerID = $this->obterClienteID(
            nomeCompleto: $parametros->pagadorNomeCompleto,
            email: $parametros->pagadorEmail,
            telefone: $parametros->pagadorTelefone,
            documentoNumero: $parametros->pagadorDocumentoNumero,
            accessToken: $parametros->chaveAPI
        );

        $meioDePagamento = match($parametros->tipoCobranca){
            'Boleto' => 'BOLETO',
            'Cartao','CartaoCredito','CartaoDebito' => 'CREDIT_CARD',
            'Pix' => 'PIX',
            default => 'BOLETO',
        };

        $body = [
            'customer' => $customerID,
            'billingType' => $meioDePagamento,
            'value' => $parametros->valor,
            'dueDate' => date('Y-m-d', strtotime($parametros->vencimento)),
            'totalValue' => $parametros->valor,
            'description' => $parametros->mensagem,
            'interest' => [
                'value' => $parametros->juros
            ],
            'fine' => [
                'value' => $parametros->multa,
                'type' => $parametros->tipoMulta === 'PERCENTUAL' ? 'PERCENTAGE' : 'FIXED',
            ],
        ];

        if($parametros->parcelas > 1){
            $body['installmentCount'] = $parametros->parcelas;
            $body['description'] = "Este boleto refere-se a uma das parcelas da cobrança, que inclui: ".PHP_EOL.$body['description'];
        }

        try {

            $client = new Client([
                'base_uri' => $this->baseURL,
            ]);

            $headers = [
                'access_token' => $parametros->chaveAPI,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                'User-Agent' => 'Bames',
              ];

            $response = $client->request('POST', $this->baseURL.'/v3/payments', [
              'body' => json_encode($body),
              'headers' => $headers,
            ]);

            $respostaAPI = $response->getBody()->getContents();

            if(!json_validate($respostaAPI)){

                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível emitir o boleto. - $respostaAPI"
                );
                throw new Exception("Ops, não foi possível emitir o boleto. - $respostaAPI");
            }

            $resposta = json_decode($respostaAPI, true);

            if(isset($resposta['id']) and !empty($resposta['id'])){
                return new SaidaFronteiraEmitirBoleto(
                    status: $resposta['status'] ?? '',
                    codigoBoletoNaPlataformaAPICobranca: $resposta['id'],
                    codigoPagadorNaPlataformaAPICobranca: $customerID,
                    codigoCobrancaNaPlataformaAPICobranca: $resposta['installment'] ?? '',
                    dataEmissao: $resposta['dateCreated'] ?? '',
                    nossoNumero: $resposta['nossoNumero'] ?? '',
                    linhaDigitavel: $resposta['linhaDigitavel'] ?? '',
                    codigoBarras: $resposta['codigoBarras'] ?? '',
                    urlBoleto: $resposta['bankSlipUrl'] ?? $resposta['invoiceUrl'] ?? '',
                    respostaCompleta: json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                );
            }

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível emitir o boleto. - $resposta"
            );

            throw new Exception("Ops, não foi possível emitir o boleto. - $resposta");

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível emitir o boleto. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível emitir o boleto. - {$erro->getMessage()}");
        }
    }

    private function _consultarBoleto(EntradaFronteiraConsultarBoleto $parametros): SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma
    {

        if($parametros->contaBancariaAmbienteProducao){
            $this->baseURL = 'https://api.asaas.com';
        }

        try {

            $client = new Client([
                'base_uri' => $this->baseURL,
            ]);

            $headers = [
                'access_token' => $parametros->chaveAPI,
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                'User-Agent' => 'Bames',
            ];


            $response = $client->request('GET', $this->baseURL.'/v3/payments/'.$parametros->codigoBoletoNaPlataformaAPICobranca, [
                'headers' => $headers,
            ]);

            $requestPayload = [
                'method' => 'GET',
                'url' => $this->baseURL.'/v3/payments/'.$parametros->codigoBoletoNaPlataformaAPICobranca,
                'headers' => $headers,
            ];

            $resposta = $response->getBody()->getContents();

            if(!json_validate($resposta)){
                $this->discord->enviar(
                    canaldeTexto: CanalDeTexto::CobrancasAsaas,
                    mensagem: "Ops, não foi possível consultar o boleto. - $resposta"
                );
                throw new Exception("Ops, não foi possível consultar o boleto. - $resposta");
            }

            $respostaBoleto = json_decode($resposta, true);
            $respostaIdentificacao = [];

            if(isset($respostaBoleto['billingType']) and $respostaBoleto['billingType'] == 'BOLETO') {

                /// Para ter acesso ao código de barras e linha digitável, é necessário fazer uma nova requisição para identificationField
                $response = $client->request('GET', $this->baseURL . '/v3/payments/' . $parametros->codigoBoletoNaPlataformaAPICobranca . '/identificationField', [
                    'headers' => $headers,
                ]);

                $respostaIdentificacao = $response->getBody()->getContents();

                if (!json_validate($respostaIdentificacao)) {
                    $this->discord->enviar(
                        canaldeTexto: CanalDeTexto::CobrancasAsaas,
                        mensagem: "Ops, não foi possível consultar o boleto. - $respostaIdentificacao"
                    );
                    throw new Exception("Ops, não foi possível consultar o boleto. - $respostaIdentificacao");
                }

                $respostaIdentificacao = json_decode($respostaIdentificacao, true);
            }

            $responsePayload = [
                'identificationField' => $respostaIdentificacao,
                'payment' => $respostaBoleto,
            ];

            /*
             PENDING RECEIVED CONFIRMED OVERDUE REFUNDED RECEIVED_IN_CASH REFUND_REQUESTED REFUND_IN_PROGRESS CHARGEBACK_REQUESTED CHARGEBACK_DISPUTE AWAITING_CHARGEBACK_REVERSAL DUNNING_REQUESTED DUNNING_RECEIVED AWAITING_RISK_ANALYSIS
            */
            $status = match($respostaBoleto['status'] ?? 'Desconhecido'){
                'PENDING' => CobrancaSituacao::AGUARDANDO_PAGAMENTO,
                default => CobrancaSituacao::DESCONHECIDO,
            };

            return new SaidaFronteiraSituacaoAtualDoBoletoNaPlataforma(
                status: $status,
                nossoNumero: $respostaIdentificacao['nossoNumero'] ?? '',
                codigoDeBarras: $respostaIdentificacao['barCode'] ?? '',
                linhaDigitavel: $respostaIdentificacao['identificationField'] ?? '',
                requestPayload: json_encode($requestPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                responsePayload: json_encode($responsePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            );

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::CobrancasAsaas,
                mensagem: "Ops, não foi possível consultar o boleto. - {$erro->getMessage()}"
            );
            throw new Exception("Ops, não foi possível consultar o boleto. - {$erro->getMessage()}");
        }
    }

    private function obterClienteDados(string $clienteID, string $accessToken): array
    {

        $headers = [
            'accept' => 'application/json',
            'access_token' => $accessToken,
            'content-type' => 'application/json',
            'User-Agent' => 'Bames',
          ];

        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);
        $response = $client->request('GET', $this->baseURL.'/v3/customers/'.$clienteID, ['headers' => $headers]);

        $resposta = $response->getBody()->getContents();

        if (!json_validate($resposta)) {
            throw new Exception("Ops, não foi possível buscar o cliente. - $resposta");
        }

        $resposta = json_decode($resposta, true);

        return [
            'nomeCompleto' => $resposta['name'] ?? '',
            'email' => $resposta['email'] ?? '',
            'telefone' => $resposta['mobilePhone'] ?? '',
            'documento' => $resposta['cpfCnpj'] ?? '',
        ];
    }

    private function obterClienteID(string $nomeCompleto, string $email, string $telefone, string $documentoNumero, string $accessToken): string
    {

        $headers = [
            'accept' => 'application/json',
            'access_token' => $accessToken,
            'content-type' => 'application/json',
            'User-Agent' => 'Bames',
          ];

        // Vamos ver se o cliente existe na plataforma
        $client = new Client([
            'base_uri' => $this->baseURL,
        ]);
        $response = $client->request('GET', $this->baseURL.'/v3/customers?cpfCnpj='.$documentoNumero, ['headers' => $headers]);

        $resposta = $response->getBody()->getContents();

        if (!json_validate($resposta)) {
            throw new Exception("Ops, não foi possível buscar o cliente. - $resposta");
        }

        $resposta = json_decode($resposta, true);

        if(isset($resposta['data']) and count($resposta['data']) > 0){
            return $resposta['data'][0]['id'];
        }

        $body = [
            'name' => $nomeCompleto,
            'cpfCnpj' => $documentoNumero,
            'email' => $email,
            'mobilePhone' => $telefone
        ];

        $resposta = $client->request('POST', $this->baseURL.'/v3/customers', [
          'body' => json_encode($body),
          'headers' => $headers,
        ]);

        $resposta = $resposta->getBody()->getContents();

        if (!json_validate($resposta)) {
            throw new Exception("Ops, não foi possível criar o cliente. - $resposta");
        }

        $resposta = json_decode($resposta, true);

        return $resposta['id'];
    }
}