<?php

declare(strict_types=1);

namespace App\Infraestrutura\Adaptadores\Ambiente;

use Exception;
use App\Aplicacao\Compartilhado\Ambiente\Ambiente;

class ImplementacaoAmbienteArquivo implements Ambiente
{

    private static string $pathForLOG = 'Shared -> Envoriment';

    private static string $pathForENV = __DIR__.'/../../../../.env';

    private static array $env = [];

    private static function load(): void
    {

        if(!empty(self::$env)){
            return;
        }

        if(!file_exists(self::$pathForENV)){
            $env = <<<envAmbiente
            APP_ENV=Jus Izi
            APP_DEBUG=true
            
            TEST_INTEGRATION_RUN=false
            DISCORD_BOT_TOKEN=
            GOOGLE_AGENDA_CLIENT_REDIRECT_URL= 
            GOOGLE_AGENDA_CLIENT_ID=  
            
            API_BRASIL_UTILIZAR=false
            API_BRASIL_TOKEN= 
            envAmbiente;
        }else{
            $env = file_get_contents(self::$pathForENV);
        }

        if(empty($env)){
            throw new Exception('Arquivo .env está vazio.');
        }

        $env = explode(PHP_EOL, $env);
        
        foreach($env as $key => $value){
            $value = explode('=', $value);
            if(isset($value[1]) and !empty($value[1])){
                self::$env[$value[0]] = $value[1];
            }
        }
    }

    public static function get(string $key): string | bool | int
    {

        self::load();

        if(!array_key_exists($key, self::$env)){
            throw new Exception('A Chave "'.$key.'" não encontrada no arquivo .env.');
        }

        $valor = match(self::$env[$key]){
            'true', 'True' => true,
            'false', 'False' => false,
            default => self::$env[$key]
        };

        if(is_numeric($valor)){
            return (int) $valor;
        }

        return $valor;
    }
}