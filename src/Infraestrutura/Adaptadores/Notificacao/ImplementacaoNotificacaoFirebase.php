<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Notificacao;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Notificacao\Notificacao;
use Exception;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class ImplementacaoNotificacaoFirebase implements Notificacao
{
    private string $pathFirebaseCredentials;

    private Messaging $messaging;
    public function __construct(
        private Ambiente $ambiente
    ){
        $this->pathFirebaseCredentials = $this->obterODiretorioDasCredenciais();

        $factory = (new Factory)->withServiceAccount($this->pathFirebaseCredentials);

        $this->messaging = $factory->createMessaging();
    }

    public function obterODiretorioDasCredenciais(): string
    {
        return __DIR__.'/../../../Aplicacao/Compartilhado/Credenciais/firebase-admin-sdk.json';
    }

    public function enviar(string $titulo, string $mensagem, string $fcmToken): void
    {

        $parametros = [
            'title' => $titulo,
            'body' => $mensagem,
        ];

        /*if(!empty($imagemURL)){
            $parametros['image'] = $imagemURL;
        }*/

        $notification = Notification::fromArray($parametros);

        $message = CloudMessage::new();
        $message = $message->withNotification($notification);

        $resposta = $this->messaging->sendMulticast($message, $fcmToken);

        if($resposta->hasFailures()){
            $failures = $resposta->failures()->getItems();
            foreach ($failures as $failure) {
                if(str_contains($failure->error()->getMessage(), 'Requested entity was not found.')){
                    throw new Exception('Ops, não é possível enviar notificação pois o FCM Token informado não foi encontrado.');
                }

                if(str_contains($failure->error()->getMessage(), 'The registration token is not a valid FCM registration token')){
                    throw new Exception('Ops, não é possível enviar notificação pois o FCM Token informado não é válido.');
                }
            }
        }
    }
}
