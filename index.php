<?php

define("TG_TOKEN", "5911286116:AAFHP8OzB_D6Sf4SynlKdtI0_Ny56f-mn80");
define("TG_USER_ID", "5268146921");
define("TG_URL", "https://api.telegram.org/bot" . TG_TOKEN);

$content = file_get_contents('php://input');
$content = json_decode($content, true);
inputUpdate($content, true);

$message = isset($content['message']) ? $content['message'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$textMessage = isset($message['text']) ? $message['text'] : "";
$textMessage = trim($textMessage);

function inputUpdate($string, $clear = false){
    $log_file_name = __DIR__ . "/inputMessage.txt";
    $now = date("Y-m-d H:i:s");
    if($clear == false){
        file_put_contents($log_file_name, $now . " " . print_r($string, true)) . "\r\n" . FILE_APPEND;
    } else {
        file_put_contents($log_file_name, '');
        file_put_contents($log_file_name, $now . " " . print_r($string, true)) . "\r\n" . FILE_APPEND;
    }
}

function execurl($action, $params)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot" . TG_TOKEN . "/$action?" . http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getJoke(){

    $api_url = "https://official-joke-api.appspot.com/random_joke";
    $getRandomJoke = json_decode(file_get_contents($api_url), true);
    return   $getRandomJoke['setup'] . "\n\n".
             $getRandomJoke['punchline'] ;
}

function startBot($chatId){
    execurl("sendMessage",
        [
            'chat_id' => $chatId,
            'text' => 'Время поднять настроение! Жми кнопку "Пошутить!"',
            'parse_mode' => 'HTML',
            "reply_markup" => json_encode(
                [
                    'keyboard' => [
                        [
                            [
                                'text' => 'Пошутить!',
                            ],
                            [
                                'text' => 'Стоп!',
                            ],
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                    'remove_keyboard' => false,
                ]),
        ]);
}

function stopBot($chatId){
    execurl("sendMessage",
        [
            'chat_id' => $chatId,
            'text' => 'Бот остановлен! Чтобы снова начать работу - Жми "Запустить"',
            'parse_mode' => 'HTML',
            "reply_markup" => json_encode(
                [
                    'keyboard' => [
                        [
                            [
                                'text' => 'Запустить',
                            ],
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                    'remove_keyboard' => false,
                ]),
        ]);
}

function sendJoke($chatId, $firstJoke){

    if ($firstJoke == false) $textMessage = "Конечно! Вот еще шутка:\n" . getJoke();
    else $textMessage = getJoke();

    execurl("sendMessage",
        [
            'chat_id' => $chatId,
            'text' => $textMessage,
            'parse_mode' => 'HTML',
            "reply_markup" => json_encode(
                [
                    'keyboard' => [
                        [
                            [
                                'text' => 'Еще хочу!',
                            ],
                            [
                                'text' => 'Стоп!',
                            ],
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                    'remove_keyboard' => false,
                ]),
        ]);
}

function sendError($chatId){
    execurl("sendMessage",
        [
        'chat_id' => $chatId,
        'text' => "Я не могу вас понять, пожалуйста используйте кнопки для отправки запросов",
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(
            [
            'keyboard' => [
                [
                    [
                        'text' => 'Пошутить!',
                    ],
                    [
                        'text' => 'Стоп!',
                    ],
                ],
            ],
            'one_time_keyboard' => true,
            'resize_keyboard' => true,
            'remove_keyboard' => false,
        ]),
    ]);
}

if (!$content) {
    exit;
}

switch ($textMessage){
    case "/start":
        startBot($chatId);
        break;
    case "Запустить":
        startBot($chatId);
        break;
    case "Пошутить!":
        sendJoke($chatId, true);
        break;
    case "Еще хочу!":
        sendJoke($chatId, false);
        break;
    case "Стоп!":
        stopBot($chatId);
        break;
    default:
        sendError($chatId);
        break;
}
