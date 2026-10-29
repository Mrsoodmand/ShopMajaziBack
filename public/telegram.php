<?php
require_once __DIR__ . '/../bootstrap/bot-env.php';
$request = file_get_contents('php://input');
#          ↑↑↑↑
$servername = "localhost";
$username = "shomarem_shomare";
$password = bot_env('LEGACY_DB_PASSWORD');
$DB = bot_env('LEGACY_DB_PASSWORD');
$mysqli = new mysqli($servername, $username, $password, $username);
mysqli_set_charset($mysqli, "utf8");
$userquery = $mysqli->query("UPDATE `settings` SET `key`='$request'  WHERE `id`='2'");
$request = json_decode($request, TRUE);
$SplitData = SplitData($request);
$chatId = $request['message']['chat']['id'];

if ($SplitData['type'] == 'private')
{
    $data = [
        'chat_id' => $chatId,
        'text' => '@'.$SplitData['From']['username'],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}else{
    $data = [
        'chat_id' => $SplitData['ChatID'],
        'text' => 'بلک جیدن داره!!',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}

die('asd');
if ($mysqli === false) {
    $data = [
        'chat_id' => $chatId,
        'text' => 'اتصال برقرار نشد',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}


//$userquery = $mysqli->query("SELECT * FROM `admins` WHERE `id` = '1'");


//$user = $userquery->fetch_assoc();

//print_r($user);

// Create connection


if (!$request) {
    $chatId = $request['message']['chat']['id'];
    $message = $request['message']['text'];
    $apiToken = bot_env('TELEGRAM_BOT_TOKEN');

    $data = [
        'chat_id' => $chatId,
        'text' => 'asdasdasd',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);

} elseif (!isset($request['update_id']) || !isset($request['message'])) {
    $chatId = $request['message']['chat']['id'];
    $message = $request['message']['text'];
    $apiToken = bot_env('TELEGRAM_BOT_TOKEN');


    $data = [
        'chat_id' => $chatId,
        'text' => 'step2',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
} else {
//    if ($SplitData['type'] == 'private'){
        $chatId = $request['message']['chat']['id'];
        $message = $request['message']['text'];

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'سفارش', 'callback_data' => 'order', 'url' => 'https://shopmajazi.com/'],
                    ['text' => 'تیکت', 'callback_data' => 'ticket'],
                    ['text' => 'کاربر', 'callback_data' => 'user'],
                ]
            ],
        ];
        $encodedKeyboard = json_encode($keyboard);

        $data = [
            'chat_id' => $chatId,
            'text' => 'لطفا یکی از گزینه های زیر رو انتخاب کنید',
            'parse_mode' => 'HTML',
            'reply_markup' => $encodedKeyboard

        ];

        SendChat($data);
//    }
}

function SendChat($data)
{
    $apiToken = bot_env('TELEGRAM_BOT_TOKEN');


    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/sendMessage');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($curl);
    $code = curl_getinfo($curl);
    curl_close($curl);
}

function SplitData($data){
    $Data['type'] = $data['message']['chat']['type']; // private - SuperGroup - group - Channel
    if ($Data['type'] == 'private'){
        $Data['Chat']['id'] = $data['message']['chat']['username'];
        $Data['From']['id'] = $data['message']['from']['id'];
        $Data['From']['first_name'] = $data['message']['from']['first_name'];
        $Data['From']['last_name'] = $data['message']['from']['last_name'];
        $Data['From']['username'] = $data['message']['from']['username'];
    }elseif($Data['type'] == 'supergroup'){
        $Data['ChatID'] = $data['my_chat_member']['chat']['id'];
        $Data['From']['first_name'] = $data['my_chat_member']['from']['first_name'];

    }

    return $Data;


}
