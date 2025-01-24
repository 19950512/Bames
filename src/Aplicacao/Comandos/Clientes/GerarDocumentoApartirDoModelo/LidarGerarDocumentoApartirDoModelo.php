<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Clientes\GerarDocumentoApartirDoModelo;

use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Conversor\ConversorDeArquivo;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Aplicacao\Compartilhado\Docx\Docx;
use App\Aplicacao\Compartilhado\Docx\Fronteiras\EntradaFronteiraSubistituirConteudo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Dominio\Entidades\Cliente\EntidadeCliente;
use App\Dominio\Entidades\Empresa\EntidadeEmpresarial;
use App\Dominio\Entidades\Modelo\EntidadeModelo;
use App\Dominio\ObjetoValor\LinkParaDownload;
use App\Dominio\Repositorios\Clientes\RepositorioClientes;
use App\Dominio\Repositorios\Modelos\RepositorioModelos;
use Exception;
use GuzzleHttp\Client;
use Override;

final class LidarGerarDocumentoApartirDoModelo implements Lidar
{

    public function __construct(
        private GerenciadorDeArquivos $gerenciadorDeArquivos,
        private EntidadeEmpresarial $entidadeEmpresarial,
        private RepositorioClientes $repositorioClientes,
        private RepositorioModelos $repositorioModelos,
        private Cache $cache,
        private ConversorDeArquivo $conversorDeArquivo,
        private Docx $docx,
        private Discord $discord,
    ){}

    /*
     * Oque esse algoritmo faz?
     * 1. Recebe um comando para gerar um documento apartir de um modelo
     * 2. Se o documento já foi gerado (Cache), retorna o link para download do documento (PDF) e finaliza
     *
     * 3. Busca o modelo (Docx)
     * 4. Busca o cliente
     * 5. Substitui o conteúdo do modelo com as informações do cliente
     * 6. Salva o novo documento (Docx) no gerenciador de arquivos
     * 7. Converte o documento (Docx) para PDF
     * 8. Salva o novo documento (PDF) no gerenciador de arquivos
     * 9. Retorna o link para download do documento (PDF)
     * 10. Salva o link para download em cache
     * 11. Retorna o link para download
     * */
    #[Override] public function lidar(Comando $comando): LinkParaDownload
    {

        if (!is_a($comando, ComandoGerarDocumentoApartirDoModelo::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $this->entidadeEmpresarial->codigo->get();
        $modeloID = $comando->obterModeloID();
        $clienteID = $comando->obterClienteID();

        $keyCache = $this->entidadeEmpresarial->codigo->get().'_'.$modeloID.'_'.$clienteID.'_PDF_link_para_download';

        if($this->cache->exist($keyCache)){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Documento PDF já foi gerado, retornando link para download apartir do Cache."
            );
            return unserialize($this->cache->get($keyCache));
        }

        try {

            $modeloDados = $this->repositorioModelos->obterModeloPorCodigo(
                modeloCodigo: $modeloID,
                empresaCodigo: $empresaCodigo
            );

            $entidadeModelo = EntidadeModelo::instanciarEntidadeModelo($modeloDados);

        }catch (Exception $erro){
            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Modelo não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Modelo não encontrado. - {$erro->getMessage()}");
        }

        try {
            $clienteDados = $this->repositorioClientes->buscarClientePorCodigo(
                codigoCliente: $clienteID,
                empresaCodigo: $empresaCodigo
            );
            $entidadeCliente = EntidadeCliente::instanciarEntidadeCliente($clienteDados);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Cliente não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Cliente não encontrado. - {$erro->getMessage()}");
        }

        $nomeClienteSemEspacos = str_replace(' ', '_', $entidadeCliente->nomeCompleto->get());
        $nomeModeloSemEspacos = str_replace(' ', '_', $entidadeModelo->nome->get());

        // Vamos ver se o documento PDF existe no gerenciamento de arquivos
        try {

            $diretorioENomeArquivoPDFSalvo = "clientes/{$nomeClienteSemEspacos}/documentos/{$nomeModeloSemEspacos}_".date('dmYH').".pdf";

            // se não existir, obterArquivo vai lançar uma exceção
            $this->gerenciadorDeArquivos->obterArquivo(
                diretorioENomeArquivo: $diretorioENomeArquivoPDFSalvo,
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );

            $linkParaDownloadGerenciadorArquivo = $this->gerenciadorDeArquivos->linkTemporarioParaDownload(
                diretorioENomeArquivo: $diretorioENomeArquivoPDFSalvo,
                empresaCodigo: $this->entidadeEmpresarial->codigo->get()
            );

            $linkParaDownload = new LinkParaDownload(
                link: $linkParaDownloadGerenciadorArquivo
            );

            $this->cache->set(
                key: $keyCache,
                value: serialize($linkParaDownload),
                expireInSeconds: 60 * 20 // 20 minutos
            );

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Documento PDF já foi gerado, retornando link para download."
            );
            return $linkParaDownload;

        }catch (Exception $erro){
            // Se não existir, vamos criar
        }

        try {

            $documento = $this->gerenciadorDeArquivos->obterArquivo(
                diretorioENomeArquivo: "modelos/{$entidadeModelo->nomeArquivo->get()}",
                empresaCodigo: $empresaCodigo
            );

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Documento não encontrado. - {$erro->getMessage()}"
            );
            throw new Exception("Documento não encontrado. - {$erro->getMessage()}");
        }

        try {

            $parametrosSubistituirConteudo = new EntradaFronteiraSubistituirConteudo(
                conteudoDoArquivoDocx: $documento,
                    subistituicoes: [
                        ...$this->docx->substituicaoUtil(),
                        ...$this->docx->substituicaoUtilCaixaAlta(),

                        ...$this->entidadeEmpresarial->substituicoes(),
                        ...$this->entidadeEmpresarial->substituicoesCaixaAlta(),

                        ...$entidadeCliente->subistituicoes(),
                        ...$entidadeCliente->subistituicoesCaixaAlta(),
                    ]
            );
            $novoDocumento = $this->docx->substituirConteudo($parametrosSubistituirConteudo);

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Erro ao subistituir o conteúdo do documento. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao subistituir o conteúdo do documento. - {$erro->getMessage()}");
        }

        $diretorioENomeArquivo = "clientes/{$nomeClienteSemEspacos}/documentos/{$nomeModeloSemEspacos}_".date('dmYH').".docx";

        try {

            $parametrosSalvarArquivoGerenciador = new EntradaFronteiraSalvarArquivo(
                diretorioENomeArquivo: $diretorioENomeArquivo,
                conteudo: $novoDocumento->conteudo,
                empresaCodigo: $empresaCodigo
            );

            $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivoGerenciador);
        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Erro ao salvar o documento no Gerenciador de Arquivos. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao salvar o documento no Gerenciador de Arquivos. - {$erro->getMessage()}");
        }

        try {

            $conteudoPDF = $this->conversorDeArquivo->docxToPDF(
                conteudo: $novoDocumento->conteudo,
                arquivoNome: "{$nomeModeloSemEspacos}.docx"
            );

        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Erro ao converter o documento Docx para PDF (ConversorDeArquivo - API). - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao converter o documento Docx para PDF. - {$erro->getMessage()}");
        }

        $diretorioENomeArquivoNovoPDF = str_replace('.docx', '.pdf', $diretorioENomeArquivo);
        // Vamos salvar na gerenciador de arquivos
        $parametrosSalvarArquivo = new EntradaFronteiraSalvarArquivo(
            diretorioENomeArquivo: $diretorioENomeArquivoNovoPDF,
            conteudo: $conteudoPDF->conteudo,
            empresaCodigo: $this->entidadeEmpresarial->codigo->get()
        );

        $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivo);

        try {

            $linkDoDocumentoParaDownload = $this->gerenciadorDeArquivos->linkTemporarioParaDownload(
                diretorioENomeArquivo: $diretorioENomeArquivoNovoPDF,
                empresaCodigo: $empresaCodigo
            );
        }catch (Exception $erro){

            $this->discord->enviar(
                canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
                mensagem: "Erro ao obter o link do documento para download. - {$erro->getMessage()}"
            );
            throw new Exception("Erro ao obter o link do documento para download. - {$erro->getMessage()}");
        }

        $linkParaDownload = new LinkParaDownload(
            link: $linkDoDocumentoParaDownload
        );

        $this->cache->set(
            key: $keyCache,
            value: serialize($linkParaDownload),
            expireInSeconds: 60 * 20 // 20 minutos
        );

        $this->discord->enviar(
            canaldeTexto: CanalDeTexto::ClienteGerarDocumentoApartirDoModelo,
            mensagem: "Documento PDF gerado com sucesso. ".PHP_EOL."Link para download: {$linkDoDocumentoParaDownload}"
        );

        return $linkParaDownload;
    }
}