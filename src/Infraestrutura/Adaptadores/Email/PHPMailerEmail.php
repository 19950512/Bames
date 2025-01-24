<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Email;

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Email\Email;
use App\Aplicacao\Compartilhado\Email\Fronteiras\EntradaFronteiraEnviarEmail;
use App\Aplicacao\Compartilhado\Email\Fronteiras\SaidaFronteiraEmailCodigo;
use Exception;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;

readonly class PHPMailerEmail implements Email
{
    public function __construct(
        private Ambiente $ambiente
    ){}

    public function enviar(EntradaFronteiraEnviarEmail $params): SaidaFronteiraEmailCodigo
    {

        if(!$this->ambiente->get('EMAIL_ENVIAR')){
            return new SaidaFronteiraEmailCodigo(sha1((string) time()).'-MOCK');
        }

        $mail = new PHPMailer(true);
        
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->ambiente->get('EMAIL_HOST');                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $this->ambiente->get('EMAIL_USERNAME');                     //SMTP username
            $mail->Password   = $this->ambiente->get('EMAIL_PASSWORD');                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
            $mail->Port       = $this->ambiente->get('EMAIL_PORT');                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($this->ambiente->get('EMAIL_REMETENTE'), $this->ambiente->get('EMAIL_REMETENTE_NOME'));
            $mail->addAddress($params->destinatarioEmail, mb_convert_encoding($params->destinatarioNome, 'ISO-8859-1', 'UTF-8'));     //Add a recipient
            //$mail->addAddress('ellen@example.com');               //Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');
        
            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML

            $tituloSubtituloEmail = mb_convert_encoding($params->assunto, 'ISO-8859-1', 'UTF-8');
            $mail->Subject = $tituloSubtituloEmail;

            $body = $params->mensagem;
            $bodyHTMLess = strip_tags($body);

            $mail->Body    = $body;
            $mail->AltBody = $bodyHTMLess;
        
            ob_start();

            try {
                // Seu código para enviar o email...
                if(!$mail->send()){
                    throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }

                // Pegar a saída do log de depuração
                $log = ob_get_clean();

                $mensagemIDProvedor = $mail->getLastMessageID();
                // Use uma expressão regular para extrair o ID do email
                if (preg_match("/250 Ok (.*)/", $log, $matches)) {
                    $mensagemIDProvedor = trim(trim($matches[1] ?? ''), '<br>');
                }

                //$this->_discord->send(DiscordChannel::email, "Email enviado para {$config->para->get()}");

                return new SaidaFronteiraEmailCodigo($mensagemIDProvedor);

            } catch (Exception $e) {

                //$this->_discord->send(DiscordChannel::email, "Erro ao enviar email: {$e->getMessage()}");

                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }

        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}