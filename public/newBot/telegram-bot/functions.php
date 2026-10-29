<?php
require_once __DIR__ . '/../../../bootstrap/bot-env.php';

function SendChat($data,$text = null)
{
    if ($text == 'ایگل'){
        $data = [
            'chat_id' => -1001793444735,
            'text' => '',
            'parse_mode' => 'HTML'
        ];
    }
    SendCurl('sendMessage',$data);
}
function DeleteMessage($data,$data_2 = null)
{
    SendCurl('deleteMessage',$data);
    if (!is_null($data_2)){
        SendCurl('deleteMessage',$data_2);
    }
}

function GetChat($data){
    SendCurl('getChat',$data);
}

function SendCurl($url,$data){
    $apiToken = bot_env('TELEGRAM_BOT_TOKEN');
    $curl = curl_init('https://api.telegram.org/bot' . $apiToken . '/'.$url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($curl);
    $code = curl_getinfo($curl);
    curl_close($curl);
}
function lang(){
    $Data = [
            'سلام' => 'hello',
            'کونی' => 'koni',
            'کیری' => 'kiri',
            'عن' => '💩',
        ];
    return $Data;
}
