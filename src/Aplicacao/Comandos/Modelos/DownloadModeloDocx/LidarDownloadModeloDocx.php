<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos\Modelos\DownloadModeloDocx;

use Exception;
use DI\Container;
use App\Aplicacao\Comandos\Lidar;
use App\Aplicacao\Comandos\Comando;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Dominio\ObjetoValor\LinkParaDownload;
use App\Aplicacao\Leituras\Modelos\LeituraModeloPreview;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\GerenciadorDeArquivos;
use App\Aplicacao\Compartilhado\GerenciadorDeArquivos\Fronteiras\EntradaFronteiraSalvarArquivo;

final readonly class LidarDownloadModeloDocx implements Lidar
{

    public function __construct(
        private GerenciadorDeArquivos $gerenciadorDeArquivos,
        private Container $container,
        private Cache $cache
    ){}

    public function lidar(Comando $comando): LinkParaDownload
    {

        if (!is_a($comando, ComandoDownloadModeloDocx::class)) {
            throw new Exception("Ops, não sei lidar com esse comando.");
        }

        $empresaCodigo = $comando->obterEmpresaCodigo();
        $modeloCodigo = $comando->obterModeloCodigo();

        $diretorioENomeArquivo = "modelos/{$modeloCodigo}.docx";

        $conteudoArquivo = $this->container->get(LeituraModeloPreview::class)->executar(
            modeloCodigo: $modeloCodigo,
            empresaCodigo: $empresaCodigo
        );

        $parametrosSalvarArquivoGerenciador = new EntradaFronteiraSalvarArquivo(
            diretorioENomeArquivo: $diretorioENomeArquivo,
            conteudo: $conteudoArquivo,
            empresaCodigo: $empresaCodigo
        );

        $this->gerenciadorDeArquivos->salvarArquivo($parametrosSalvarArquivoGerenciador);

        $linkParaDownload = $this->gerenciadorDeArquivos->linkTemporarioParaDownload(
            diretorioENomeArquivo: $diretorioENomeArquivo,
            empresaCodigo: $empresaCodigo
        );

        return new LinkParaDownload(
            link: $linkParaDownload
        );
    }
}