<?php
require_once __DIR__ . '/../../../bootstrap/bot-env.php';
$apiToken = bot_env('TELEGRAM_HEGZA_BOT_TOKEN');

function SendChat($data, $mysqli = null, $chatId = null, $type = null)
{
    $message_id = SendCurl('sendMessage', $data);
    if (!is_null($chatId)) {
        userUpdate($mysqli, $chatId, $type, $message_id['result']['message_id']);
    }
//    die();
}

function SendPhoto($data, $mysqli = null, $chatId = null, $type = null)
{
    $message_id = SendCurl('sendPhoto', $data);
    if (!is_null($chatId)) {
        userUpdate($mysqli, $chatId, $type, $message_id['result']['message_id']);
    }
//    die();
}

function editChat($data)
{
    SendCurl('editMessageText', $data);
}

function sendFile($data)
{
    SendCurl('sendDocument', $data);
}

function popUpChat($data)
{
    SendCurl('answerCallbackQuery', $data);
}

function DeleteMessage($data, $data_2 = null)
{
    SendCurl('deleteMessage', $data);
    if (!is_null($data_2)) {
        SendCurl('deleteMessage', $data_2);
    }
}

function GetChat($data)
{
    SendCurl('getChat', $data);
}

function SendCurl($url, $data)
{

    $apiToken = bot_env('TELEGRAM_HEGZA_BOT_TOKEN');

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.telegram.org/bot' . $apiToken . "/$url",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => http_build_query($data),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

function sendCurlToSite($method, $data)
{
    $url = "https://shopmajazi.com/api/hegza/$method?" . http_build_query($data);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS => array('' => ''),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function userUpdate($mysqli, $tel_id, $type, $message_id)
{
    $user = "UPDATE `telegram_user` SET `type`='$type',`message_id`='$message_id' WHERE `tel_id` ='$tel_id'";
    $mysqli->query($user);
}


function Home($chatId, $message_id)
{
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '🛒خرید سرویس', 'callback_data' => 'type=buy-ip|'],
            ],
            [
                ['text' => '🛍 لیست سفارشات ', 'callback_data' => 'type=order-list|'],
                ['text' => '👤 حساب کاربری', 'callback_data' => 'type=show-profile|'],
            ],
            [
                ['text' => '💳 شارژ کیف پول', 'callback_data' => 'type=charge-wallet|'],
                ['text' => '⚖ قوانین', 'callback_data' => 'type=rules|'],
            ],
            [
                ['text' => '☎️ پشتیبانی', 'url' => 'https://t.me/hegzaSupport'],
                ['text' => '🎁 جاییزه ', 'callback_data' => 'type=referral|'],
            ],
            [
                ['text' => 'سرویس تست', 'callback_data' => 'type=test-server-list|'],
            ],
        ]
    ];
    $encodedKeyboard = json_encode($keyboard);
    $data = [
        'chat_id' => $chatId,
        'message_id' => $message_id,
        'text' => 'سلام دوست عزیزم ✌️
به هگذا خوش اومدید ❤️
میتونید یک ای پی تست از ما بگیرید اگر دوسداشتید خرید کنید 🤓
قیمت ها به تومان هستش 💰
راستی هیچ محدودیتی روی کاربر نداریم.
اگر هم حال داشتی قسمت قوانین رو مطالعه کن.',
        'reply_markup' => $encodedKeyboard,
        'parse_mode' => 'HTML'
    ];
    return $data;
}
