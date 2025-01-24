<?php

arch('Todas as "classes" de App\Dominio\Repositorios devem ser interfaces exceto as Fronteiras.')
    ->expect('App\Dominio\Repositories')
    ->toBeInterfaces()
    ->ignoring([
	    'App\Dominio\Repositorios\Autenticacao\Fronteiras',
	    'App\Dominio\Repositorios\Empresa\Fronteiras',
	    'App\Dominio\Repositorios\Token\Fronteiras',
    ]);

function listarFronteiras($basePath)
{
    $repositorios = [];

    // Função recursiva para percorrer diretórios
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            // Formata o caminho para a estrutura de namespace do PHP
            $relativePath = str_replace($basePath, '', $item->getPathname());
            $namespacePath = 'App\\Dominio\\Repositorios' . str_replace('/', '\\', $relativePath) . '\\Fronteiras';

            $repositorios[] = $namespacePath;
        }
    }

    return $repositorios;
}

arch('Todas as "classes" de Dominio\Repositorios devem ter prefixo Repositorio.')
    ->expect('App\Dominio\Repositorios')
    ->toHavePrefix('Repositorio')
    ->ignoring(listarFronteiras(__DIR__.'/../../../../src/Dominio/Repositorios'));