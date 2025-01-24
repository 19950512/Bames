<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\PreviewModelo;

use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Docx\Docx;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\ObjetoValor\LinkParaDownload;
use App\Dominio\ObjetoValor\IdentificacaoUnica;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Aplicacao\Leituras\Modelos\LeituraModeloPreview;
use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Leituras\Empresa\LeituraEmpresaSubstituicoes;
use App\Aplicacao\Comandos\Modelos\PreviewModelo\ComandoPreviewModelo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Repositorios\Clientes\Fronteiras\SaidaFronteiraClienteDetalhado;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\EntradaFronteiraSubistituirConteudo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;

final readonly class LidarPreviewModelo implements Lidar
{

    public function __construct(
        private GerenciadorDeArquivos $gerenciadorDeArquivos,
        private Container $container,
        private Cache $cache
    ){}

    public function lidar(Comando $comando): LinkParaDownload
    {

        if (!is_a($comando, ComandoPreviewModelo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();
        $modeloCodigo = $comando->obterModeloCodigo();

        $keyCache = $empresaCodigo . '_modelo_preview_' . $modeloCodigo;

        if($this->cache->exist($keyCache)){
            return new LinkParaDownload(
                link: $this->cache->get($keyCache)
            );
        }

        $clienteDados = new SaidaFronteiraClienteDetalhado(
            codigo: (new IdentificacaoUnica())->get(),
            nomeCompleto: 'Matheus Maydana',
            tipo: 'Cliente',
            email: 'matheus@email.com',
            telefone: '51999999999',
            documento: '80395162009',
            dataNascimento: '1995-12-05',
            endereco: 'Santo Marchetto',
            enderecoNumero: '42',
            enderecoComplemento: 'Casa',
            enderecoBairro: 'Centro',
            enderecoCidade: 'Porto Alegre',
            enderecoEstado: 'RS',
            enderecoCep: '08500400',
            nomeMae: 'Maria Maydana da Silva',
            cpfMae: '37375998078',
            sexo: 'M',
            nomePai: 'João Maydana da Silva',
            cpfPai: '80395162009',
            rg: '123456789',
            pis: '123456789',
            carteiraTrabalho: '123456789',
            telefones: [],
            emails: [],
            enderecos: [],
            familiares: []
        );

        $clienteFake = EntidadeCliente::instanciarEntidadeCliente($clienteDados);

        $diretorioENomeArquivo = "modelos/preview/{$clienteFake->codigo->get()}/{$modeloCodigo}.docx";

        try {

            $conteudopdf = $this->gerenciadorDeArquivos->obterArquivo(
                diretorioENomeArquivo: '/modelos/'.$modeloCodigo.'_preview.pdf',
                empresaCodigo: $empresaCodigo
            );

            $linkParaDownload = $this->gerenciadorDeArquivos->linkTemporarioParaDownload(
                diretorioENomeArquivo: '/modelos/'.$modeloCodigo.'_preview.pdf',
                empresaCodigo: $empresaCodigo
            );

            $this->cache->set(
                key: $keyCache,
                value: $linkParaDownload,
                expireInSeconds: 60 * 20 // 20 minutos
            );

            return new LinkParaDownload(
                link: $linkParaDownload
            );

        }catch(Exception $e){

            // Se não existir o arquivo PDF, vamos criar um novo

            $conteudoArquivo = $this->container->get(LeituraModeloPreview::class)->executar(
                modeloCodigo: $modeloCodigo,
                empresaCodigo: $empresaCodigo
            );

            try {

                $parametrosSubistituirConteudo = new EntradaFronteiraSubistituirConteudo(
                    conteudoDoArquivoDocx: $conteudoArquivo,
                    subistituicoes: [
                        ...$this->container->get(Docx::class)->substituicaoUtil(),
                        ...$this->container->get(LeituraEmpresaSubstituicoes::class)->executar(),
                        ...$clienteFake->subistituicoes(),
                    ]
                );


                $novoDocumento = $this->container->get(Docx::class)->substituirConteudo($parametrosSubistituirConteudo);

            }catch (Exception $erro){
                throw new Exception("Erro ao subistituir o conteúdo do documento. - {$erro->getMessage()}");
            }

            try {

                $parametrosSalvarArquivoGerenciador = new EntradaFronteiraSalvarArquivo(
                    diretorioENomeArquivo: $diretorioENomeArquivo,
                    conteudo: $novoDocumento->conteudo,
                    empresaCodigo: $empresaCodigo
                );

                $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivoGerenciador);

            }catch (Exception $erro){

                throw new Exception("Erro ao salvar o documento no Gerenciador de Arquivos. - {$erro->getMessage()}");
            }

            $conteudoPDFArquivo = $this->container->get(ConversorDeArquivo::class)->docxToPDF(
                conteudo: $novoDocumento->conteudo,
                arquivoNome: 'modelo_cliente.docx'
            );

            // Vamos salvar na gerenciador de arquivos
            $parametrosSalvarArquivo = new EntradaFronteiraSalvarArquivo(
                diretorioENomeArquivo: '/modelos/'.$modeloCodigo.'_preview.pdf',
                conteudo: $conteudoPDFArquivo->conteudo,
                empresaCodigo: $empresaCodigo
            );

            $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivo);

            $linkParaDownload = $this->gerenciadorDeArquivos->linkTemporarioParaDownload(
                diretorioENomeArquivo: '/modelos/'.$modeloCodigo.'_preview.pdf',
                empresaCodigo: $empresaCodigo
            );

            $this->cache->set(
                key: $keyCache,
                value: $linkParaDownload,
                expireInSeconds: 60 * 20 // 20 minutos
            );

            return new LinkParaDownload(
                link: $linkParaDownload
            );
        }

        return null;
    }
}