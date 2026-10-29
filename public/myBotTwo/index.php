<?php

include 'bot.php';
$chatId = $request['message']['chat']['id'];
include 'db.php';
if ($mysqli === false) {
    $data = [
        'chat_id' => $chatId,
        'text' => 'اتصال برقرار نشد',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}
include 'var.php';
include 'functions.php';
if (array_key_exists('callback_query', $request)) {
    $chatId = $request['callback_query']['message']['chat']['id'];

    $message_id = $request['callback_query']['message']['message_id'];
    $callBackData = explode('|', $request['callback_query']['data']);
    $callBackId = $request['callback_query']['id'];

    foreach ($callBackData as $key => $item) {
        list($name, $id) = explode('=', $item);
        $type[$name] = $id;
    }
    if ($type['type'] == 'buy-ip') {

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id
        ];
        return sendCurlToSite('buy-ip', $data);

    }
    elseif ($type['type'] == 'home') {

        if (array_key_exists('type2', $type)) {

            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'method' => 'send'
            ];
            sendCurlToSite('home', $data2);

            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
            ];
            DeleteMessage($data2);

            die();
        } else {
            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'method' => 'edit'
            ];
            return sendCurlToSite('home', $data2);
        }
    }
    elseif ($type['type'] == 'get-service') {

        $callBackId = $request['callback_query']['id'];
        if ($type['country_id'] == '#') {
            $data = [
                'callback_query_id' => $callBackId,
                'show_alert' => true,
                'message_id' => $message_id,
                'text' => 'با عرض پوزش، درحال حاضر این سرویس موجود نمی باشد!',
                'parse_mode' => 'HTML',
            ];
            popUpChat($data);
        } else {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'country_id' => $type['country_id'],
            ];
            return sendCurlToSite('get-service', $data);
        }
    }


    elseif ($type['type'] == 'pay-order-wallet') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال پردازش سفارش',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        return sendCurlToSite($type['type'], $data);
    }
    elseif ($type['type'] == 'referral') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('show-referral', $data);
    }
    elseif ($type['type'] == 'show-profile') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('show-profile', $data);
    }

    // Telegram Service
    elseif ($type['type'] == 'get-telegram-service') {
        $callBackId = $request['callback_query']['id'];

        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات لطفا صبور باشید',
            'parse_mode' => 'HTML',
        ];

        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        return sendCurlToSite('get-telegram-service', $data);

    }
    elseif ($type['type'] == 'get-instagram-service')
    {
        $callBackId = $request['callback_query']['id'];

        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات لطفا صبور باشید',
            'parse_mode' => 'HTML',
        ];

        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        return sendCurlToSite('get-instagram-service', $data);

    }

    // Telegram Ads
    elseif ($type['type'] == 'telegram-ads'){
        $callBackId = $request['callback_query']['id'];

        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات لطفا صبور باشید',
            'parse_mode' => 'HTML',
        ];

        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        return sendCurlToSite('telegram-ads', $data);
    }

    // Telegram Premium
    elseif ($type['type'] == 'telegram-premium') {
        $callBackId = $request['callback_query']['id'];

        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات لطفا صبور باشید',
            'parse_mode' => 'HTML',
        ];

        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        return sendCurlToSite('telegram-premium', $data);

    }
    elseif ($type['type'] == 'set-telegram-id') {
        $callBackId = $request['callback_query']['id'];

        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال انتقال به مرحله بعد، لطفا صبور باشید',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        return sendCurlToSite($type['type'], $data);

    }

    // Telegram Real Member
    elseif ($type['type'] == 'telegram-real-member') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('telegram-real-member', $data);
    }
    elseif ($type['type'] == 'set-telegram-id-for-real-member') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-telegram-id-for-real-member', $data);
    }

    // Telegram Fake Member
    elseif ($type['type'] == 'telegram-fake-member') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('telegram-fake-member', $data);
    }
    elseif ($type['type'] == 'set-telegram-id-for-fake-member') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-telegram-id-for-fake-member', $data);
    }

    // Instagram Real follower
    elseif ($type['type'] == 'instagram-real-follower') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('instagram-real-follower', $data);
    }
    elseif ($type['type'] == 'set-instagram-id-for-real-follower') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-instagram-id-for-real-follower', $data);
    }

    // Instagram Fake follower
    elseif ($type['type'] == 'instagram-fake-follower') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('instagram-fake-follower', $data);
    }
    elseif ($type['type'] == 'set-instagram-id-for-fake-follower') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-instagram-id-for-fake-follower', $data);
    }

    // Instagram Like
    elseif ($type['type'] == 'instagram-like') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('instagram-like', $data);
    }
    elseif ($type['type'] == 'set-instagram-id-for-like') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-instagram-id-for-like', $data);
    }

    // Instagram View
    elseif ($type['type'] == 'instagram-view') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit'
        ];
        return sendCurlToSite('instagram-view', $data);
    }
    elseif ($type['type'] == 'set-instagram-id-for-view') {
        $data = [
            'callback_query_id' => $callBackId,
            'show_alert' => false,
            'message_id' => $message_id,
            'text' => 'درحال دریافت اطلاعات',
            'parse_mode' => 'HTML',
        ];
        popUpChat($data);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'method' => 'edit',
            'id' => $type['id'],
        ];
        return sendCurlToSite('set-instagram-id-for-view', $data);
    }

    // Orders
    elseif ($type['type'] == 'order-list') {
        if (array_key_exists('method', $type)) {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'method' => 'delete'
            ];
        } else {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
            ];
        }
        return sendCurlToSite('order-list', $data);
    }
    elseif ($type['type'] == 'show-order') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'order_id' => $type['id'],
        ];
        return sendCurlToSite('show-order', $data);
    }

    // Charge
    elseif ($type['type'] == 'charge-wallet') {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '💸 شارژ با درگاه', 'callback_data' => 'type=charge-online|'],
                ],[
                    ['text' => '💸 کارت به کارت', 'callback_data' => 'type=cart-be-cart|'],
                ], [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],
                ],
            ]
        ];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => "برای شارژ کیف پول یکی از روش های زیر رو انتخاب کن 👇",
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        editChat($data);
    }
    elseif ($type['type'] == 'charge-online') {

        $user = "UPDATE `t_user` SET `path` = 'charge-wallet' WHERE `user_id` = '$chatId'";
        $mysqli->query($user);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '100,000 تومان', 'callback_data' => 'type=amount|amount=100000'],
                    ['text' => '150,000 تومان', 'callback_data' => 'type=amount|amount=150000'],
                ], [
                    ['text' => '200,000 تومان', 'callback_data' => 'type=amount|amount=200000'],
                    ['text' => '300,000 تومان', 'callback_data' => 'type=amount|amount=300000'],
                ], [
                    ['text' => '500,000 تومان', 'callback_data' => 'type=amount|amount=500000'],
                    ['text' => '600,000 تومان', 'callback_data' => 'type=amount|amount=600000'],
                ], [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=charge-wallet|'],
                ],
            ]
        ];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => "یکی از مبالغ زیر رو انتخاب کن یا
 مبلغ دلخواهت رو وارد کن👇",
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        editChat($data);
    }

    elseif ($type['type'] == 'get-bandwidth') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'country_id' => $type['country_id'],
            'service_id' => $type['service_id'],
        ];
        return sendCurlToSite('get-bandwidth', $data);
    }
    elseif ($type['type'] == 'submit-order') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'country_id' => $type['country_id'],
            'service_id' => $type['service_id'],
            'bandwidth' => $type['bandwidth'],
        ];
        return sendCurlToSite('submit-order', $data);
    }
    elseif ($type['type'] == 'order-change-page') {
        $callBackId = $request['callback_query']['id'];
        if ($type['id'] == '#') {
            $data = [
                'callback_query_id' => $callBackId,
                'show_alert' => true,
                'message_id' => $message_id,
                'text' => 'صفحه مورد نظر یافت نشد.',
                'parse_mode' => 'HTML',
            ];
            return popUpChat($data);
        } else {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'page' => $type['id'],
            ];
            return sendCurlToSite('order-list', $data);
        }
    }

    elseif ($type['type'] == 'charge-crypto') {
        $user = "UPDATE `t_user` SET `path` = 'charge-crypto' WHERE `user_id` = '$chatId'";
        $mysqli->query($user);

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '5 دلار', 'callback_data' => 'type=crypto-amount|amount=5'],
                    ['text' => '10 دلار', 'callback_data' => 'type=crypto-amount|amount=10'],
                ], [
                    ['text' => '15 دلار', 'callback_data' => 'type=crypto-amount|amount=15'],
                    ['text' => '20 دلار', 'callback_data' => 'type=crypto-amount|amount=20'],
                ], [
                    ['text' => '25 دلار', 'callback_data' => 'type=crypto-amount|amount=25'],
                    ['text' => '30 دلار', 'callback_data' => 'type=crypto-amount|amount=30'],
                ], [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=charge-wallet|'],
                ],
            ]
        ];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => "یکی از مبالغ زیر رو انتخاب کن یا
 مبلغ دلخواهت رو وارد کن👇",
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        editChat($data);
    }

    elseif ($type['type'] == 'amount') {

        $user = "UPDATE `t_user` SET `path` = 'charge-wallet' WHERE `user_id` = '$chatId'";
        $mysqli->query($user);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'amount' => $type['amount'],
            'type' => 'edit',
        ];
        return sendCurlToSite('charge-wallet', $data);
    }

    elseif ($type['type'] == 'crypto-amount') {

        $user = "UPDATE `t_user` SET `path` = 'crypto-charge' WHERE `user_id` = '$chatId'";
        $mysqli->query($user);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'amount' => $type['amount'],
            'type' => 'edit',
        ];
        return sendCurlToSite('charge-crypto', $data);
    }
    elseif ($type['type'] == 'order-bandwidth') {

        $data2 = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        DeleteMessage($data2);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        return sendCurlToSite('get-bandwidth-for-order', $data);
    }
    elseif ($type['type'] == 'buy-bandwidth') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'bandwidth' => $type['bandwidth'],
            'id' => $type['id'],
        ];
        return sendCurlToSite('buy-bandwidth', $data);
    }
    elseif ($type['type'] == 'order-renew') {
        $data2 = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        DeleteMessage($data2);

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        return sendCurlToSite('get-renew-items', $data);
    }
    elseif ($type['type'] == 'buy-renew-order') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
            'service_id' => $type['service_id'],
        ];
        return sendCurlToSite('buy-renew-order', $data);
    }
    elseif ($type['type'] == 'rules') {

        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => '⭐️ لطفا یک دقیقه وقت بزارید و قوانین رو مطالعه کنید، ممنونم ازتون

',
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        editChat($data);

    }
    elseif ($type['type'] == 'help') {

        $keyboard['inline_keyboard'][][] = ['text' => '💻 مک ', 'callback_data' => 'type=single-help|platform=mac'];
        $keyboard['inline_keyboard'][][] = ['text' => '📱 آیفون', 'callback_data' => 'type=single-help|platform=iphone'];
        $keyboard['inline_keyboard'][][] = ['text' => '🖥 ویندوز', 'callback_data' => 'type=single-help|platform=windows'];
        $keyboard['inline_keyboard'][][] = ['text' => '📱 اندروید', 'callback_data' => 'type=single-help|platform=android'];
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => 'فایل راهنما رو سیستم عامل مورد نظرت رو انتخاب کن',
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if (array_key_exists('action', $type)) {
            SendChat($data);

            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
            ];
            DeleteMessage($data2);
        } else {
            editChat($data);
        }
    }
    elseif ($type['type'] == 'single-help') {

        $data2 = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];

        if ($type['platform'] == 'mac') {

            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=help|action=delete'];
            $encodedKeyboard = json_encode($keyboard);
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'document' => "https://shopmajazi.com/hegzaBot/help/iosBot.pdf",
                'caption' => "آموزش نصب آی پی در سیستم عامل مکینتاش
                نکته:
درصورت بازگشت، فایل حذف خواهد شد. در صورت نیاز فایل را به قسمت Saved Message خود منتقل کنید.",
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
        } elseif ($type['platform'] == 'iphone') {

            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=help|action=delete'];
            $encodedKeyboard = json_encode($keyboard);
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'document' => "https://shopmajazi.com/hegzaBot/help/iosBot.pdf",
                'caption' => "آموزش نصب آی پی در سیستم عامل IOS
                نکته:
درصورت بازگشت، فایل حذف خواهد شد. در صورت نیاز فایل را به قسمت Saved Message خود منتقل کنید.",
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];

        } elseif ($type['platform'] == 'windows') {

            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=help|action=delete'];
            $encodedKeyboard = json_encode($keyboard);
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => "آموزش نصب آی پی در سیستم عامل اندروید
                نکته:
برای دانلود فایل راهنما ویندوز <a href='https://shopmajazi.com/hegzaBot/help/windowsBot.pdf'>کلیک کنید </a>",
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];

            DeleteMessage($data2);
            return SendChat($data);

        } elseif ($type['platform'] == 'android') {
            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=help|action=delete'];
            $encodedKeyboard = json_encode($keyboard);
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'document' => "https://shopmajazi.com/hegzaBot/help/androidBot.pdf",
                'caption' => "آموزش نصب آی پی در سیستم عامل اندروید
                نکته:
درصورت بازگشت، فایل حذف خواهد شد. در صورت نیاز فایل را به قسمت Saved Message خود منتقل کنید.",
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
        }

        DeleteMessage($data2);
        sendFile($data);

    }
    elseif ($type['type'] == 'test-server-list') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id
        ];
        return sendCurlToSite('test-server-list', $data);
    }
    elseif ($type['type'] == 'get-server-list') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'country_id' => $type['country_id'],
        ];
        return sendCurlToSite('get-server-list', $data);
    }
    elseif ($type['type'] == 'buy-order-wallet') {
        $id = $type['id'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $id,
        ];
        return sendCurlToSite('buy-order-wallet', $data);
    }
    elseif ($type['type'] == 'cart-be-cart') {
        $id = $type['id'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $id,
        ];
        return sendCurlToSite('cart-be-cart', $data);
    }
    elseif ($type['type'] == 'cart-be-cart-setting') {
        $fromName = $request['callback_query']['from']['first_name'];
        $fromLast = $request['callback_query']['from']['last_name'];
        $userName = $request['callback_query']['from']['username'];

        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
            'answer' => $type['answer'],
            'from' => [
                'name' => $fromName,
                'last' => $fromLast,
                'username' => $userName,
            ]
        ];
        sendCurlToSite('confirm-cart-be-cart', $data);
    }
    elseif ($type['type'] == 'buy-volume-wallet') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        sendCurlToSite('buy-volume-wallet', $data);
    }
    elseif ($type['type'] == 'buy-renew-wallet') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'id' => $type['id'],
        ];
        sendCurlToSite('buy-renew-wallet', $data);
    }
    elseif ($type['type'] == 'search-by-code-step-one') {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
        ];
        sendCurlToSite('search-by-code-step-one', $data);
    }



} elseif ($Data['Text'] == '/start') {

    $user = "SELECT `id`, `user_id`, `name`, `username`, `path`, `wallet` FROM `t_user` WHERE `user_id` ='$chatId'";
    $result = $mysqli->query($user);
    $user = $result->fetch_assoc();


    if (is_null($user)) {
        $userData = $request['message']['from'];
        $name = $userData['first_name'] .' '. $userData['last_name'];
        $username = $userData['username'];
        $path = 'callback_query';
        $wallet = 10000;
        $ref = 'hg-' . $chatId;
        $parent = 0;
        $mysqli->query("INSERT INTO `t_user`(`user_id`,`name`,`username`,`path`,`wallet`,`ref`,`parent`) VALUES ('$chatId','$name','$username','$path','$wallet','$ref','$parent')");

        $url = 'https://t.me/RoopsidaServiceBot?start=' . $chatId;
        $data = [
            'chat_id' => $chatId,
            'text' => "دوستاتو به ما معرفی کن
و از هر تراکنش 10% هدیه بگیر
لینک دعوت شما:
$url",
            'parse_mode' => 'HTML'
        ];

        SendChat($data);
    } else {
        $user = "UPDATE `t_user` SET `path` = 'callback_query' WHERE `user_id` = '$chatId'";
        $result = $mysqli->query($user);
    }
    $data2 = [
        'chat_id' => $chatId,
        'message_id' => 0,
        'method' => 'send'
    ];
    return sendCurlToSite('home', $data2);

} elseif ($Data['Text'] == '/buy') {
    $message_id = $request['message']['message_id'];

    $data = [
        'chat_id' => $chatId,
        'message_id' => $message_id,
        'type2' => 'delete'
    ];
    return sendCurlToSite('buy-ip', $data);
}
elseif ($Data['Text'] == '/orders') {
    $message_id = $request['message']['message_id'];

    $data = [
        'chat_id' => $chatId,
        'message_id' => $message_id,
        'type2' => 'delete'
    ];
    return sendCurlToSite('order-list', $data);
}
elseif ($Data['Text'] == '/profile') {
    $message_id = $request['message']['message_id'];
    $data = [
        'chat_id' => $chatId,
        'message_id' => $message_id,
        'type' => 'delete'
    ];
    return sendCurlToSite('show-profile', $data);

}
elseif ($Data['Text'] == '/referral') {
    $message_id = $request['message']['message_id'];
    $data = [
        'chat_id' => $chatId,
        'message_id' => $message_id,
        'type' => 'delete'
    ];
    return sendCurlToSite('referral', $data);
}
else {
    $user = "SELECT `id`, `user_id`, `name`, `username`, `path`, `wallet` FROM `t_user` WHERE `user_id` ='$chatId'";
    $result = $mysqli->query($user);
    $user = $result->fetch_assoc();

    $message_id = $request['message']['message_id'];

    $ref = str_replace('/start ', '', $Data['Text']);

    $callBackData = explode('_', $ref);
    foreach ($callBackData as $key => $item) {
        list($name, $id) = explode('=', $item);
        $type[$name] = $id;
    }

    if (array_key_exists('type', $type)) {
        $message_id = $type['msgId'];
        if ($type['type'] == 'error') {
            $error = $type['error'];
            $data = [
                'chat_id' => $chatId,
                'message_id' => "$message_id",
                'text' => "کاربری یافت نشد.",
                'parse_mode' => 'HTML'
            ];
            SendChat($data);
        }
        elseif ($type['type'] == 'utm') {

            $user = "SELECT `id`, `user_id`, `name`, `username`, `path`, `wallet` FROM `t_user` WHERE `user_id` ='$chatId'";
            $result = $mysqli->query($user);
            $user = $result->fetch_assoc();

            if (is_null($user)) {
                $userData = $request['message']['from'];
                $name = $userData['username'];
                $username = $userData['username'];
                $path = 'callback_query';
                $wallet = 10000;
                $ref = $chatId;
                $parent = 0;
                $from = $type['from'];
                $mysqli->query("INSERT INTO `t_user`(`user_id`,`name`,`username`,`path`,`wallet`,`ref`,`parent`,`from`) VALUES ('$chatId','$name','$username','$path','$wallet','$ref','$parent','$from')");
            }

            $data2 = [
                'chat_id' => $chatId,
                'message_id' => 0,
                'type' => 'send'
            ];
            return sendCurlToSite('home', $data);

        }
        elseif ($type['type'] == 'order') {

            $userData = $request['message']['from'];

            $data = [
                'chatId' => $chatId,
                'id' => $type['id'],
                'name' => $userData['username'],
                'username' => $userData['username'],
            ];

            return sendCurlToSite('sync-order', $data);

        }
    }
    elseif (!is_null($user)) {
        if ($user['path'] == 'charge-wallet') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'amount' => $Data['Text'],
                'type' => 'send',
            ];
            return sendCurlToSite('charge-wallet', $data);
        }
        elseif ($user['path'] == 'callback_query') {
            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'type' => 'send'
            ];
            return sendCurlToSite('home', $data);

        }
        elseif ($user['path'] == 'cart-be-cart') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
            ];
            sendCurlToSite('send-document-cart-be-cart', $data);
        }
        elseif ($user['path'] == 'charge-crypto') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'amount' => $Data['Text'],
                'type' => 'send',
            ];
            sendCurlToSite('charge-crypto', $data);
        }
        elseif ($user['path'] == 'telegram-premium') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('get-telegram-user-name', $data);
        }
        elseif ($user['path'] == 'telegram-fake-member') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-telegram-fake-member-id', $data);
        }
        elseif ($user['path'] == 'telegram-real-member') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-telegram-real-member-id', $data);
        }
        elseif ($user['path'] == 'instagram-real-follower') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-instagram-real-follower-id', $data);
        }
        elseif ($user['path'] == 'instagram-fake-follower') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-instagram-fake-follower-id', $data);
        }
        elseif ($user['path'] == 'instagram-like') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-instagram-like-id', $data);
        }
        elseif ($user['path'] == 'instagram-view') {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'text' => $Data['Text'],
            ];
            sendCurlToSite('set-instagram-view-id', $data);
        }

        else {
            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'type' => 'send'
            ];
            sendCurlToSite('home', $data);
        }
    } else {

        $ref = str_replace('/start ', '', $Data['Text']);

        $parent = "SELECT `id`, `user_id`, `name`, `username`, `path`, `wallet` FROM `t_user` WHERE `user_id` ='$ref'";
        $result = $mysqli->query($parent);
        $parent = $result->fetch_assoc();

        if (!is_null($parent)) {

            $user = "SELECT `id`, `user_id`, `name`, `username`, `path`, `wallet` FROM `t_user` WHERE `user_id` ='$chatId'";
            $result = $mysqli->query($user);
            $user = $result->fetch_assoc();

            if (is_null($user)) {
                $userData = $request['message']['from'];
                $name = $userData['username'];
                $username = $userData['username'];
                $path = 'callback_query';
                $wallet = 10000;
                $ref = $chatId;
                $parent = $parent['id'];
                $mysqli->query("INSERT INTO `t_user`(`user_id`,`name`,`username`,`path`,`wallet`,`ref`,`parent`) VALUES ('$chatId','$name','$username','$path','$wallet','$ref','$parent')");
            }

            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $message_id,
                'type' => 'send'
            ];
            sendCurlToSite('home', $data);

        } else {
            $data = [
                'chat_id' => $chatId,
                'message_id' => "$message_id",
                'text' => "کاربری یافت نشد.",
                'parse_mode' => 'HTML'
            ];
            SendChat($data);
        }
    }
}


die();


