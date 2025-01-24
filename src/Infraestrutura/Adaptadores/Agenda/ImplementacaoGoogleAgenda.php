<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Agenda;

use Exception;
use App\Aplicacao\Compartilhado\Agenda\Agenda;
use App\Aplicacao\Compartilhado\Cache\Cache;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraCriarEvento;
use App\Aplicacao\Compartilhado\Agenda\Fronteiras\EntradaFronteiraAtualizarEvento;

class ImplementacaoGoogleAgenda implements Agenda
{

    private string $accessToken = '';
    private string $baseURL = 'https://www.googleapis.com/calendar/v3/calendars/primary';

    public function __construct(
        readonly private Ambiente $ambiente,
        readonly private Cache $cache,
        public string $codigoAutorizacao = ''
    ){}

    public function _getAccessToken(): string
    {
        return $this->accessToken;
    }
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
    }

    public function getLoginUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . urlencode($this->ambiente->get('GOOGLE_AGENDA_CLIENT_REDIRECT_URL')) . '&response_type=code&client_id=' . $this->ambiente->get('GOOGLE_AGENDA_CLIENT_ID') . '&access_type=online';
    }

    public function checkCode(): void
    {
        if($this->codigoAutorizacao === ''){
            throw new Exception('Error: Você não forneceu um código de autorização, acesse. ' . $this->getLoginUrl() . ' e forneça o código de autorização.');
        }
    }

    public function listarEventos(): array
    {

        $keyCache = "GoogleAgendaEventos";

        if($this->cache->exist($keyCache)){
            return unserialize($this->cache->get($keyCache));
        }
        
        $url = "{$this->baseURL}/events";
        $accessToken = $this->getAccessToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        $resposta = curl_exec($ch);
        $data = json_decode($resposta, true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200)
            throw new Exception('Error : Failed to get events');
        $eventos =  $data['items'] ?? [];

        $this->cache->set($keyCache, serialize($eventos), 60 * 60 * 1); // 1 hour

        return $eventos;
    }

    public function criarEvento(EntradaFronteiraCriarEvento $parametros): string
    {

        $url = "{$this->baseURL}/events";
        $accessToken = $this->getAccessToken();
        $timezone = $this->getUserCalendarTimezone();
        $data = [
            'summary' => $parametros->titulo,
            'start' => [
                'dateTime' => $parametros->horarioInicio,
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => $parametros->horarioFim,
                'timeZone' => $timezone,
            ],
            'recurrence' => [
                'RRULE:FREQ=DAILY;COUNT=' . $parametros->recorrencia
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ];
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        $resposta = curl_exec($ch);
        $data = json_decode($resposta, true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200)
            throw new Exception('Error : Failed to create event');

        return $data['id'];
    }

    public function atualizarEvento(EntradaFronteiraAtualizarEvento $parametros): void
    {
        $url = "{$this->baseURL}/events/{$parametros->eventoCodigo}";
        $accessToken = $this->getAccessToken();
        $timezone = $this->getUserCalendarTimezone();
        $data = [
            'summary' => $parametros->titulo,
            'start' => [
                'dateTime' => '',
                'timeZone' => $timezone,
            ],
            'end' => [
                'dateTime' => '',
                'timeZone' => $timezone,
            ],
            'recurrence' => [
                'RRULE:FREQ=DAILY;COUNT=' . $parametros->recorrencia
            ],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ];
        $data_string = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200)
            throw new Exception('Error : Failed to update event');
    }

    public function deletarEvento(string $eventoCodigo): void
    {
        $url = "{$this->baseURL}/events/{$eventoCodigo}";
        $accessToken = $this->getAccessToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 204)
            throw new Exception('Error : Failed to delete event');
    }

    private function getAccessToken(): string
    {
        
        $keyCache = "GoogleAgendaAccessToken";
        
        if($this->cache->exist($keyCache)){
            $this->accessToken = $this->cache->get($keyCache);
            return $this->accessToken;
        }
        
        $this->checkCode();
        
        $url = 'https://accounts.google.com/o/oauth2/token';		
        
        $client_id = $this->ambiente->get('GOOGLE_AGENDA_CLIENT_ID');
        $redirect_uri = $this->ambiente->get('GOOGLE_AGENDA_CLIENT_REDIRECT_URL');
        $client_secret = $this->ambiente->get('GOOGLE_AGENDA_CLIENT_SECRET');
		
        $code = $this->codigoAutorizacao;
		$curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_POST, 1);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
		$data = json_decode(curl_exec($ch), true);
		$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
		if($http_code != 200) 
            if(!empty($this->accessToken)){
                return $this->accessToken;
            }
			throw new Exception('Error : Failed to receieve access token');
			
        $this->accessToken = $data['access_token'] ?? '';

        $this->cache->set($keyCache, $this->accessToken, 60 * 60 * 1); // 1 hour
		
        return $this->accessToken;
    }

    private function getUserCalendarTimezone(): string
    {

        $keyCache = "GoogleAgendaTimezone";

        if($this->cache->exist($keyCache)){
            return $this->cache->get($keyCache);
        }

        $url = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone';
        $accessToken = $this->getAccessToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200)
            throw new Exception('Error : Failed to get timezone');

        $this->cache->set($keyCache, $data['value'], 60 * 60 * 24 * 30); // 30 days

        return $data['value'];
    }
}

