<?php

declare(strict_types=1);

namespace App\Aplicacao\Comandos;

interface Lidar
{
	public function lidar(Comando $comando): mixed;
}