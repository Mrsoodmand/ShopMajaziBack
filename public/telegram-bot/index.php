<?php

include 'bot.php';
$chatId = $request['message']['chat']['id'];
include 'db.php';
include 'var.php';
include 'functions.php';

if ($mysqli === false) {
    $data = [
        'chat_id' => $chatId,
        'text' => 'اتصال برقرار نشد',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}

if (array_key_exists('my_chat_member', $request)) {
    if (array_key_exists('new_chat_member', $request['my_chat_member'])) {
        $Status = $request['my_chat_member']['new_chat_member']['status'];
        $chatIdChannel = $request['my_chat_member']['chat']['id'];
        if ($Status == 'member') {
            $data = [
                'chat_id' => $chatIdChannel,
                'text' => 'لطفا ربات را مدیر کنید',
                'parse_mode' => 'HTML'
            ];
            SendChat($data);
            die();
        } else {
            $data = [
                'chat_id' => $chatIdChannel,
                'text' => 'ربات مدیر است',
                'parse_mode' => 'HTML'
            ];
            SendChat($data);
            die();
        }
    }
}
if ($Data['Text'] == 'حذف') {
    if (array_key_exists('reply_to_message', $request['message'])) {
        $Message_id = $request['message']['reply_to_message']['message_id'];
        $data = ['chat_id' => $chatId, 'message_id' => $Message_id];
        DeleteMessage($data);
        die();
    } else {
        $data = [
            'chat_id' => $chatId,
            'text' => 'لطفا پیام را مشخص کنید',
            'parse_mode' => 'HTML'
        ];
        SendChat($data);
    }
}
if ($Data['Text'] == 'حمید' && $chatId == -1001793444735) {
    $Rand = [
        'بلک جیدن داره',
        'پا خورده',
        'کامبک خورده و سوراخ کونش به اندازه چاه نفت باز شده',
        'im looking for your boobs!',
        ' با POT رفتیم TW بگا رفتیم 🔫',
        'با اینکه ازدواج کردم ولی کص میخوام 👙',
        'موتور سوار بی مرز',
        '⭐️حمید؟؟ چقد از کص ضربه میخوری؟؟؟⭐️',
        '⭐لاشیه',
        'میلاد: حمید نمیخوای طلب منو بدی؟؟',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

$AIME = [
    'amie', 'AMIE', 'A M I E', 'Amie', 'ایمی'
];

if (in_array($Data['Text'], $AIME) && $chatId == -1001793444735) {
    $Rand = [
        'Agent of CIA',
        'Mother of venomancers',
        'DONT BACK TALK TO ME!',
        'Saresh ba Konesh bazi MEEyKONE',
        'شکر مادر',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'میلاد' && $chatId == -1001192614627) {
    $Rand = [
        'یعنی زندگی',
        'بکن حمید',
        'استروم کیری خوب',
        'کیر کننده x_sin',
        'کیرت دهن بعضی از BM نما ها',
        'شوهر بهروز',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/guide' && $chatId == -1001793444735) {
    $data = [
        'chat_id' => $chatId,
        'text' => '/tag_all
        /rainbow
        /csgo
        /plato',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/all' && $chatId == -1001793444735) {

    $data = [
        'chat_id' => $chatId,
        'text' => '@mrSoodmand @RealRakhshande @SirSabeti @rouziii @TricksterEAM @sir_p00ria @moein72saa ',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/group_lavat' && $chatId == -1001793444735) {
    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name میگه یک گروپ لواتمون نشه؟؟

        @mrSoodmand @RealRakhshande @SirSabeti @rouziii 🏳️‍🌈 @TricksterEAM @sir_p00ria @pouya_bz98",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/rainbow' && $chatId == -1001793444735) {

    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name
میگه آنلاین کنن رینو🌈  بزنیم
@mrSoodmand @SirSabeti @rouziii 👌 @TricksterEAM @sir_p00ria @RealRakhshande @moein72saa ",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/reddead' && $chatId == -1001793444735) {

    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name
میگه آنلاین Red Dead☠️🟥  بزنیم
@mrSoodmand  @rouziii 👌 @sir_p00ria @RealRakhshande @moein72saa ",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/csgo' && $chatId == -1001793444735) {

    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name
میگه آنلاین کنن CSGO🔪 بزنیم
@mrSoodmand @SirSabeti @rouziii  @TricksterEAM @sir_p00ria @RealRakhshande @pouya_bz98 @moein72saa ",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/dayz' && $chatId == -1001793444735) {

    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name
میگه آنلاین کنن DayZ🔪 بزنیم
@mrSoodmand @SirSabeti @rouziii  @TricksterEAM @sir_p00ria @RealRakhshande @pouya_bz98 @moein72saa ",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/rg' && $chatId == -1001793444735) {

    $name = $Data['request']['message']['from']['first_name'] .' '. $Data['request']['message']['from']['last_name'];

    $data = [
        'chat_id' => $chatId,
        'text' => "$name
میگه آنلاین کنن Rocket League🔪 بزنیم
@mrSoodmand @SirSabeti @rouziii @TricksterEAM @sir_p00ria @RealRakhshande @pouya_bz98 @moein72saa ",
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == '/plato' && $chatId == -1001793444735) {

    $data = [
        'chat_id' => $chatId,
        'text' => '
چه کسانی برای بازی در پلاتو حضور دارند؟
@mrSoodmand @SirSabeti @rouziii @sir_p00ria @RealRakhshande @moein72saa ',
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'خیری' && $chatId == -1001793444735) {
    $Rand = [
        'فشارررررررررررر',
        'چسووووو 💨 (قارپی)',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}


if ($Data['Text'] == 'بوذی' && $chatId == -1001793444735) {
    $Rand = [
        'نامَتو ننویسیم چاقال',
        'چاااااقالل',
        'حاج میتی٬ کیرت تا پشمای سوراخ کونت دهنم',
        'بهلولم',
        'بوذی حساسه',
        'بوذی ناز داره',
        'بوذی وسواسه',
        'اییی چییی که مِخِی مو نِدِروم',
        'خایه مال شخصی میتی های گروه',
        'داداش رینو رو برام ریفاند کن',
        '۱۰۰ میل پول لپتاب دادم ولی باهاش پورن نگا میکنم',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'علی' && $chatId == -1001793444735) {
    $Rand = [
        'خایه مال حـــاج مهدی📿',
        '🏳️‍🌈حامی کونیان ',
        'ایییور اووووررر بالاااا  نه نه  اییییور😂',
        'بیزنس من بزرگ، (بقالی)',
        'کاندوم فروش حقیر',
        'کونی حقیر',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'سودی' && $chatId == -1001793444735) {
    $Rand = [
        'عشق منی',
        'زیبای خفته',
        'سلطططططططططططططططان',
        'شاید عمل کردی ولی بهترینی',
        'عشق قشنگم',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'کونی' && $chatId == -1001793444735) {
    $Rand = [
        'خودتی',
        'کصکش خر',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}if ($Data['Text'] == 'کصکش' && $chatId == -1001793444735) {
    $Rand = [
        'واقعا کصکشه',
        'عمته',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'بهروز' && $chatId == -1001793444735) {
    $Rand = [
        'Ex of Milad 👙',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}
if ($Data['Text'] == 'ایلیا' && $chatId == -1001793444735) {
    $Rand = [
        'من کونی انگلیسی از کانادا هستم',
        'لولتون خراب شده؟؟ الان میام درستش میکنم',
        'بابا اینترنتم خرابه وگرنه کونتو پاره میکردم',
        '',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Data['Text'] == 'ایگل' && $chatId == -1001793444735) {
    $Rand = [
        'کونی منه',
        'عشق منه ',
        'شیمیایی زن منه',
        'قاتل حاج مهدی',
        'چسوی ۳ عالم',
        'راسو',
        'ماماااان شیر خرما بیار بخورم تقویت شم ',
        'ای کونم',
        'نمیتونم تَکون بخورم ( کونم پارست )',
        'دسترسیتو‌از تو پنل برمیدارم',
        'آخ چسیدم',
        'ایناروس',
        'کون نقلی',
        'کون مینیاتوری',
        'فلفل نبین چه ریزه میچسم ببینی چه تیزه',
        'پسر تو مادرزاد کصخولی',
        'یه وَری',
    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data, $Data['Text']);
    die();
}

if ($Data['Text'] == 'مهدی' && $chatId == -1001793444735) {
    $Rand = [
        'مقطول',
        'دارم صدا پا میگیرم',
        'کوووووووننییییییی',
        'شیمیایی شده زیر پتو',

    ];
    $String = array_rand($Rand);
    $data = [
        'chat_id' => $chatId,
        'text' => $Rand[$String],
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
    die();
}

if ($Type == 'private') {
//    $data = [
//        'chat_id' => -1001793444735,
//        'text' => $Data['Text'],
//        'parse_mode' => 'HTML'
//    ];
//    SendChat($data);


    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'شماره مجازی', 'callback_data' => 'VirtualNumber'],
                ['text' => 'اکانت گوگل ویس', 'callback_data' => 'GoogleVoice'],
                ['text' => 'خرید اپل آیدی', 'callback_data' => 'AppleID'],
            ],
        ]
    ];
    $encodedKeyboard = json_encode($keyboard);

    $data = [
        'chat_id' => $chatId,
        'text' => 'لطفا محصول مورد نظر خود را انتخاب کنید',
        'reply_markup' => $encodedKeyboard,
        'parse_mode' => 'HTML'
    ];
    SendChat($data);
}

if (array_key_exists('callback_query', $request)) {
    if ($request['callback_query']['data'] == 'GoogleVoice') {
        $chatId = $request['callback_query']['message']['chat']['id'];

        $data = [
            'chat_id' => $chatId,
            'text' => 'لطفا محصول مورد نظر خود را انتخاب کنید',
            'parse_mode' => 'HTML'
        ];
        SendChat($data);
    }

}

die('sad');


