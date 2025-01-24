<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Discord;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Discord\Discord;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;

final class ImplementacaoDiscord implements Discord
{

    public function __construct(
        private Ambiente $ambiente
    ){}

    public function enviar(CanalDeTexto $canaldeTexto, string $mensagem): void
    {
        if($this->ambiente->get('APP_DEBUG')){
            return;
        }

        $body = [
            'content' => mb_substr($mensagem, 0, 2000),
            'username' => 'Bames - Notificador'.($this->ambiente->get('APP_DEBUG') ? ' - DEV' : ' - PRODUÇÃO'),
            //'avatar_url' => $this->_imobiliaria->logo->getUrl(),
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $canaldeTexto->obterURL());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        curl_close($ch);
    }
}
