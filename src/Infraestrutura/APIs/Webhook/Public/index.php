<?php

declare(strict_types=1);

namespace App\Infraestrutura\APIs\Webhook\Public;

require_once __DIR__ . '/../../../../Aplicacao/Compartilhado/Containers/Container.php';

use App\Aplicacao\Compartilhado\Ambiente\Ambiente;
use App\Aplicacao\Compartilhado\Containers\Container;
use App\Aplicacao\Compartilhado\Discord\Enums\CanalDeTexto;
use App\Infraestrutura\Adaptadores\Discord\ImplementacaoDiscord;
use App\Infraestrutura\APIs\Router;

date_default_timezone_set('America/Sao_Paulo');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

set_exception_handler(function(Object $exception){
    header('Content-Type: application/json');
    http_response_code(442);
    $frases = [
        "O coração de um camarão está localizado em sua cabeça.",
        "As abelhas podem reconhecer rostos humanos.",
        "O DNA de uma banana é 60% semelhante ao do ser humano.",
        "Os polvos têm três corações e sangue azul.",
        "A água-viva é 95% composta de água.",
        "Os flamingos nascem cinzas e adquirem sua cor rosa por conta da alimentação.",
        "A Terra não é uma esfera perfeita, ela é um pouco achatada nos polos.",
        "Os elefantes são os únicos animais que não conseguem pular.",
        "O sol libera mais energia em um segundo do que a humanidade usou em toda a sua história.",
        "As aranhas não têm músculos nas pernas, elas as movem através de pressão hidráulica.",
        "O Monte Everest não é o ponto mais distante do centro da Terra, o pico do Monte Chimborazo, no Equador, é.",
        "O maior ser vivo do mundo é um fungo chamado *Armillaria ostoyae*, que cobre mais de 2.400 acres em Oregon, EUA.",
        "Os camelos podem beber até 40 galões de água de uma vez.",
        "As bananas são uma das frutas mais consumidas no mundo, superando até a maçã.",
        "A maior estrutura viva do planeta é uma colônia de corais na Austrália.",
        "A língua de um beija-flor pode medir até o dobro do comprimento do seu corpo.",
        "Os dentes de um tubarão são substituídos aproximadamente a cada 8 dias.",
        "Os pinguins podem saltar até 2 metros de altura quando estão saltando da água para a terra.",
        "As primeiras formas de vida no planeta foram microscópicas e apareceram há cerca de 3,5 bilhões de anos.",
        "A invenção do micro-ondas foi um acidente, quando um engenheiro chamado Percy Spencer percebeu que uma barra de chocolate derreteu enquanto ele testava um radar.",
        "Os koalas dormem entre 18 e 22 horas por dia, devido à baixa quantidade de energia em sua dieta.",
        "O maior animal que já existiu na Terra foi a baleia azul, que pode chegar a até 30 metros de comprimento.",
        "O som viaja 4 vezes mais rápido na água do que no ar.",
        "Existem mais estrelas no universo do que grãos de areia em todas as praias da Terra.",
        "O maior deserto do mundo não é o Saara, é a Antártica, que é classificada como um deserto polar.",
        "Os raios solares podem chegar à Terra em apenas 8 minutos e 20 segundos.",
        "O único continente sem cobras é a Antártida.",
        "A girafa tem o pescoço tão longo porque, apesar de não ter cordas vocais, sua laringe é imensa e precisa de um comprimento grande para emitir sons.",
        "A pizza foi inventada na Itália, mas o primeiro delivery de pizza foi nos Estados Unidos, em 1889.",
        "O café é a segunda bebida mais consumida no mundo, depois da água.",
        "O ponto mais profundo do oceano é a Fossa das Marianas, que tem cerca de 11.000 metros de profundidade.",
        "As sementes de maçã contêm cianeto, um veneno potente, mas a quantidade é tão pequena que não faz mal em quantidades normais.",
        "Os pôneis não são uma raça específica de cavalo, mas sim uma categoria de cavalos menores.",
        "Os morcegos são os únicos mamíferos que podem voar.",
        "Os tubarões existem há mais de 400 milhões de anos, o que significa que eles estavam nadando nas águas do planeta antes mesmo dos dinossauros."
    ];

    class AmbienteControlado implements Ambiente
    {
        public static function get(string $key): string|bool|int
        {
            if($key == 'APP_DEBUG'){
                return false;
            }

            return 'ok';
        }
    }

    $headers = apache_request_headers();

    $discord = new ImplementacaoDiscord(
        ambiente: new AmbienteControlado()
    );

    $mensagem = [];

    $mensagem[] = str_repeat('-', 40);

    $mensagem[] = "Mensagem: **{$exception->getMessage()}**";
    $mensagem[] = "Arquivo: {$exception->getFile()}";
    $mensagem[] = "Linha: {$exception->getLine()}";
    $mensagem[] = "Trace: {$exception->getTraceAsString()}";

    $mensagem[] = str_repeat('-', 40);
    $mensagem[] = "**Authorization**: ".($headers['Authorization'] ?? '');

    $mensagem[] = "POST: ".json_encode($_POST);
    $mensagem[] = "GET: ".json_encode($_GET);
    $mensagem[] = "IP: ".$_SERVER['REMOTE_ADDR'] ?? '';
    $mensagem[] = "Host: ".$_SERVER['HTTP_HOST'] ?? '';

    $mensagem[] = "User-Agent: ".($_SERVER['HTTP_USER_AGENT'] ?? '');
    $mensagem[] = "Referer: " .($_SERVER['HTTP_REFERER'] ?? '');
    $mensagem[] = "URI: ".($_SERVER['REQUEST_URI'] ?? '');
    $mensagem[] = "Método: ".($_SERVER['REQUEST_METHOD'] ?? '');
    $mensagem[] = "Data: ".date('d/m/Y H:i:s');

    $discord->enviar(
        canaldeTexto: CanalDeTexto::Exceptions,
        mensagem: implode("\n", $mensagem)
    );

    echo json_encode([
        'statusCode' => 442,
        'message' => $frases[rand(0, count($frases) - 1)]
    ]);
});

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$containerApp = Container::getInstance();

$pathFileEnv = __DIR__.'/../../../../../.env';
$dbhost = '';
if(is_file($pathFileEnv)){

    $env = file_get_contents($pathFileEnv);
    $env = explode("\n", $env);

    foreach($env as $line){
        $line = explode('=', $line);
        if($line[0] == 'DB_HOST'){
            $dbhost = $line[1];
            break;
        }
    }
}

$container = $containerApp->get([
    'DB_HOST' => $dbhost
]);

$router = new Router(
    request_uri: $_SERVER['REQUEST_URI'] ?? '',
    container: $container,
    apiName: 'Webhook'
);