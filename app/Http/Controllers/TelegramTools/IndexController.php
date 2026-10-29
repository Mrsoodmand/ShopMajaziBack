<?php

namespace App\Http\Controllers\TelegramTools;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Comment;
use App\Models\Currency;
use App\Models\HegzaCountry;
use App\Models\HegzaInboundClients;
use App\Models\HegzaIp;
use App\Models\HegzaMessage;
use App\Models\HegzaOrder;
use App\Models\HegzaPayment;
use App\Models\HegzaProduct;
use App\Models\HegzaSetting;
use App\Models\HegzaUser;
use App\Models\Telegram_Setting;
use App\Models\TelegramOrders;
use App\Models\TelegramPayment;
use App\Models\TelegramUsers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class IndexController extends Controller
{
    protected $token;
    protected $messageId;
    protected $chatId;
    protected $botUrl;
    protected $telegramSupport;
    protected $dollar;

    public function __construct(Request $request)
    {
        $this->chatId = $request->input('chat_id');
        $this->messageId = $request->input('message_id');
        $this->token = config('services.telegram.bot_two_token');
        $this->botUrl = 'https://t.me/RoopsidaServiceBot';
        $this->telegramSupport = 'https://t.me/roopsidaservice_admin';
        $this->dollar = 100000;
    }

    public function checkType(Request $request)
    {

        switch ($request->type) {

            case 'home':
                return $this->home($request);
                break;

            case 'get-service':
                return $this->getService($request);
                break;

            case 'get-telegram-service':
                return $this->getTelegramService($request);
                break;

            case 'get-instagram-service':
                return $this->getInstagramService($request);
                break;

            case 'telegram-premium':
                return $this->telegramPremium($request);
                break;

            case 'telegram-ads':
                return $this->telegramAds($request);
                break;

            case 'set-telegram-id':
                return $this->getTelegramIdForTelegramPremium($request);
                break;

            case 'get-telegram-user-name':
                return $this->submitTelegramPremiumUserName($request);
                break;


            case 'telegram-fake-member':
                return $this->TelegramFakeMember($request);
                break;

            case 'set-telegram-id-for-fake-member':
                return $this->getTelegramIdForFakeTelegramMember($request);
                break;

            case 'set-telegram-fake-member-id':
                return $this->submitTelegramFakeMember($request);
                break;

            // Telegram Real Member
            case 'telegram-real-member':
                return $this->TelegramRealMember($request);
                break;

            case 'set-telegram-id-for-real-member':
                return $this->getTelegramIdForRealTelegramMember($request);
                break;

            case 'set-telegram-real-member-id':
                return $this->submitTelegramRealMember($request);
                break;

            // Real Instagram Follower
            case 'instagram-real-follower':
                return $this->instagramRealFollower($request);
                break;

            case 'set-instagram-id-for-real-follower':
                return $this->stepTwoInstagramRealFollower($request);
                break;

            case 'set-instagram-real-follower-id':
                return $this->submitInstagramRealFollower($request);
                break;

            // Fake Instagram Follower
            case 'instagram-fake-follower':
                return $this->instagramFakeFollower($request);
                break;

            case 'set-instagram-id-for-fake-follower':
                return $this->stepTwoInstagramFakeFollower($request);
                break;

            case 'set-instagram-fake-follower-id':
                return $this->submitInstagramFakeFollower($request);
                break;

            // Instagram Like
            case 'instagram-like':
                return $this->instagramLike($request);
                break;

            case 'set-instagram-id-for-like':
                return $this->stepTwoInstagramLike($request);
                break;

            case 'set-instagram-like-id':
                return $this->submitInstagramLike($request);
                break;

            // Instagram View
            case 'instagram-view':
                return $this->instagramView($request);
                break;

            case 'set-instagram-id-for-view':
                return $this->stepTwoInstagramView($request);
                break;

            case 'set-instagram-view-id':
                return $this->submitInstagramView($request);
                break;

            case 'pay-order-wallet':
                return $this->payOrderWallet($request);
                break;

            case 'show-profile':
                return $this->showProfile($request);
                break;

            case 'show-referral':
                return $this->referral($request);
                break;

            case 'order-list':
                return $this->ordersList($request);
                break;

            case 'show-order':
                return $this->showOrder($request);
                break;
        }
    }

    /**/
    public function Home(Request $request)
    {

        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;


        $text = 'سلام دوست عزیزم ✌️
به ربات خوش اومدید ❤️
ارائه دهنده انواع تلگرام پریمیوم، ممبر فیک و واقعی تلگرام، شماره مجازی تلگرام، تبلیغات قانونی تلگرام
';

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'خدمات', 'callback_data' => 'type=get-service|'],
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
                    ['text' => '☎️ پشتیبانی', 'url' => $this->telegramSupport],
//                    ['text' => '🎁 جاییزه ', 'callback_data' => 'type=referral|'],
                ],
            ],
//            'resize_keyboard' => true,
//            'one_time_keyboard' => false
        ];

        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'home';
        $user->save();

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'

        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* دریافت سرویس ها */
    public function getService(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $text = 'لیست خدمات 👇🏻';

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'خدمات اینستاگرام', 'callback_data' => 'type=get-instagram-service|'],
                    ['text' => 'خدمات تلگرام', 'callback_data' => 'type=get-telegram-service|'],
                ],
                [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'],
                ],
            ]
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* خدمات تلگرام */
    public function getTelegramService(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $text = 'خدمات تلگرام';

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'خرید تلگرام پریمیوم', 'callback_data' => 'type=telegram-premium|'],
                ],
                [
                    ['text' => 'خرید ممبر فیک تلگرام', 'callback_data' => 'type=telegram-fake-member|'],
                    ['text' => 'خرید ممبر واقعی تلگرام', 'callback_data' => 'type=telegram-real-member|'],
                ],
                [
                    ['text' => 'خرید تبلیغات تلگرام', 'callback_data' => 'type=telegram-ads|'],
                ],
                [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'],
                ],
            ]
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* تلگرام پریمیوم */
    public function telegramPremium(Request $request)
    {

        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;


        $Services = __('botServices.telegram-premium-name');
        $price = __('botServices.telegram-premium-price');

        $text = 'تعرفه های تلگرام پریمیوم';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-telegram-id|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-telegram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }

    }

    /* ثبت آیدی تلگرام کاربر */
    public function getTelegramIdForTelegramPremium(Request $request)
    {

        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی تلگرام خود یا شخصی که قصد تهیه تلگرام پریمیوم رو دارید. وارد کنید.';
        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'telegram-premium';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /**/
    public function submitTelegramPremiumUserName(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.telegram-premium-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.telegram-premium-price')[$id] * $dollar;

            if ($user->id == 1) {
                $price = 2000;
            }

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'telegram-premium';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();


            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: تلگرام پریمیوم
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /* ممبر فیک تلگرام - مرحله یک */
    public function TelegramFakeMember(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.telegram-fake-member-name');
        $price = __('botServices.telegram-fake-member-price');

        $text = 'نوع سرویس: ممبر فیک تلگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-telegram-id-for-fake-member|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-telegram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ممبر فیک تلگرام - مرحله دو */
    public function getTelegramIdForFakeTelegramMember(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی کانال یا گروهی که قصد خرید ممبر فیک برای آنرا دارید، وارد کنید';
        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'telegram-fake-member';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ممبر فیک تلگرام - مرحله سه */
    public function submitTelegramFakeMember(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.telegram-fake-member-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.telegram-fake-member-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'telegram-fake-member';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید ممبر فیک تلگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /* ممبر فیک تلگرام - مرحله یک */
    public function TelegramRealMember(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.telegram-real-member-name');
        $price = __('botServices.telegram-real-member-price');

        $text = 'نوع سرویس: ممبر واقعی تلگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-telegram-id-for-real-member|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-telegram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ممبر فیک تلگرام - مرحله دو */
    public function getTelegramIdForRealTelegramMember(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی کانال یا گروهی که قصد خرید ممبر واقعی برای آنرا دارید، وارد کنید';
        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'telegram-real-member';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ممبر فیک تلگرام - مرحله سه */
    public function submitTelegramRealMember(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.telegram-real-member-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.telegram-real-member-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'telegram-real-member';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید ممبر واقعی تلگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }

    /* تبلیغات تلگرام */
    public function telegramAds(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $text = 'برای جلوگیری از بروز هر گونه اشتباه در فرآیند سفارش ، لطفا به پشتیبانی پیام داده و اطلاعات درخواستی را با ادمین هماهنگ کنید.';

        $keyboard['inline_keyboard'][] = [
            ['text' => 'پیام به پشتیبان', 'url' => $this->telegramSupport],
        ];
        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-telegram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* خدمات اینستاگرام */
    public function getInstagramService(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $text = 'خدمات اینستاگرام';

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'فالوور واقعی اینستاگرام', 'callback_data' => 'type=instagram-real-follower|'],
                    ['text' => 'فالوور فیک اینستاگرام', 'callback_data' => 'type=instagram-fake-follower|'],

                ],
                [
                    ['text' => 'ویو واقعی اینستاگرام', 'callback_data' => 'type=instagram-view|'],
                    ['text' => 'لایک واقعی اینستاگرام', 'callback_data' => 'type=instagram-like|'],
                ],
                [
                    ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'],
                ],
            ]
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }


    /* فالور ایرانی اینستاگرام - مرحله یک */
    public function instagramRealFollower(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.instagram-real-follower-name');
        $price = __('botServices.instagram-real-follower-price');

        $text = 'نوع سرویس: فالور ایرانی اینستاگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-instagram-id-for-real-follower|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-instagram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* فالور ایرانی اینستاگرام - مرحله دو */
    public function stepTwoInstagramRealFollower(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی پیج اینستاگرام که قصد خرید فالوور واقعی برای آن را دارید، وارد کنید.';

        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'instagram-real-follower';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ممبر فیک تلگرام - مرحله سه */
    public function submitInstagramRealFollower(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;
            $dollar = $this->dollar;
            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.instagram-real-follower-name')[$id];
            $price = __('botServices.instagram-real-follower-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'instagram-real-follower';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید فالور واقعی اینستاگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }


    /* فالور فیک اینستاگرام - مرحله یک */
    public function instagramFakeFollower(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.instagram-fake-follower-name');
        $price = __('botServices.instagram-fake-follower-price');

        $text = 'نوع سرویس: فالور ایرانی اینستاگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-instagram-id-for-fake-follower|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-instagram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* فالور فیک اینستاگرام - مرحله دو */
    public function stepTwoInstagramFakeFollower(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی پیج اینستاگرام که قصد خرید فالوور فیک برای آن را دارید، وارد کنید.';

        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'instagram-fake-follower';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* فالور فیک اینستاگرام - مرحله سه */
    public function submitInstagramFakeFollower(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.instagram-fake-follower-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.instagram-fake-follower-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'instagram-fake-follower';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید فالور فیک اینستاگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }


    /* لایک اینستاگرام - مرحله یک */
    public function instagramLike(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.instagram-like-name');
        $price = __('botServices.instagram-like-price');

        $text = 'نوع سرویس: فالور ایرانی اینستاگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-instagram-id-for-like|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-instagram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* لایک اینستاگرام - مرحله دو */
    public function stepTwoInstagramLike(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی پیج اینستاگرام که قصد خرید لایک برای آن را دارید، وارد کنید.';

        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'instagram-like';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* لایک اینستاگرام - مرحله سه */
    public function submitInstagramLike(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.instagram-like-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.instagram-like-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'instagram-like';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید لایک اینستاگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }


    /* لایک اینستاگرام - مرحله یک */
    public function instagramView(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $dollar = $this->dollar;

        $Services = __('botServices.instagram-view-name');
        $price = __('botServices.instagram-view-price');

        $text = 'نوع سرویس: فالور ایرانی اینستاگرام
یکی از گزینه های زیر را انتخاب نمایید:';

        foreach ($Services as $key => $item) {
            $ServicePrice = number_format($price[$key] * $dollar);
            $keyboard['inline_keyboard'][] = [
                ['text' => "$item - $ServicePrice تومان ", 'callback_data' => "type=set-instagram-id-for-view|id=$key"],
            ];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '🔙 بازگشت', 'callback_data' => 'type=get-instagram-service|']
        ];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* لایک اینستاگرام - مرحله دو */
    public function stepTwoInstagramView(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $id = $request->id;
        $token = $this->token;

        $text = 'لطفا آیدی پیج اینستاگرام که قصد خرید بازدید برای آن را دارید، وارد کنید.';

        $Data = [
            'id' => $id,
        ];
        $user = TelegramUsers::where('user_id', $chatId)->first();
        $user->path = 'instagram-view';
        $user->value = json_encode($Data);
        $user->save();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* لایک اینستاگرام - مرحله سه */
    public function submitInstagramView(Request $request)
    {
        try {
            $Text = $request->text;

            $chatId = $this->chatId;
            $messageId = $this->messageId;
            $token = $this->token;

            $user = TelegramUsers::where('user_id', $chatId)->first();

            $Detail = [
                'username' => $Text,
                'type' => json_decode($user->value, true)['id']
            ];

            $id = json_decode($user->value, true)['id'];
            $type = __('botServices.instagram-view-name')[$id];
            $dollar = $this->dollar;
            $price = __('botServices.instagram-view-price')[$id] * $dollar;

            $order = new TelegramOrders();
            $order->user_id = $user->id;
            $order->type = 'instagram-view';
            $order->detail = json_encode($Detail);
            $order->status = 0;
            $order->price = $price;
            $order->save();

            $totalPrice = number_format($price);

            $url = $this->payOrderOnline($request, $order);
            $keyboard['inline_keyboard'][] = [
                ['text' => 'پرداخت از آنلاین', 'url' => $url],
            ];

            if ($user->wallet >= $price) {
                $keyboard['inline_keyboard'][] = [
                    ['text' => 'پرداخت از کیف پول', 'callback_data' => "type=pay-order-wallet|id=$order->id"],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],

            ];

            $encodedKeyboard = json_encode($keyboard);
            $text = "اطلاعات سفارش:
نوع سفارش: خرید ویو اینستاگرام
------------
✅ جزئیات سفارش:
نام کاربری: $Text
نوع اشتراک: $type
------------
💸 مبلغ سرویس:
مبلغ کل: $totalPrice تومان
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        } catch (\Exception $e) {
            dd($e);
        }
    }


    /* پرداخت سرویس با کیف پول */
    public function payOrderWallet(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $orderId = $request->id;

        $user = TelegramUsers::where('user_id', $chatId)->first();
        $order = TelegramOrders::find($orderId);

        if ($user->wallet >= $order->price) {

            switch ($order->type) {
                case 'telegram-premium':
                    $this->telegramPremiumOrder($order);
                    break;
                case 'telegram-fake-member':
                    $this->telegramFakeMemberOrder($order);
                    break;
            }

            $user->decrement('wallet', $order->price);
            $user->save();

            $keyboard['inline_keyboard'][] = [
                ['text' => 'مشاهده جزئیات سفارش', 'callback_data' => "type=show-order|id=$order->id"],
            ];

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],
            ];
            $encodedKeyboard = json_encode($keyboard);

            $text = "سفارش با موفقیت ثبت شد.";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return EditTelegramMessageForCustomBot($data, $token);

        } else {

            $keyboard['inline_keyboard'][] = [
                ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],
            ];
            $encodedKeyboard = json_encode($keyboard);

            $text = "عدم موجودی کافی برای پرداخت این سفارش، لطفا کیف پول خود را شارژ نمایید.";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => $encodedKeyboard,
                'parse_mode' => 'HTML'
            ];
            return EditTelegramMessageForCustomBot($data, $token);
        }

    }

    /* پرداخت سرویس با آنلاین */
    public function payOrderOnline(Request $request, $order)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $orderId = $order->id;

        $user = TelegramUsers::where('user_id', $chatId)->first();
        $order = TelegramOrders::find($orderId);

        $gate = 'zibal';
        if ($gate == 'zibal') {

            if ($user->id == 1) {
                $price = 2000 * 10;
            } else {
                $price = $order->price * 10;
            }

            $parameters = array(
                "callbackUrl" => url('/') . "/api/new-services/ziba-call-back?m=$messageId",
                "amount" => $price,
                "orderId" => $order->id,
                "description" => "پرداخت سفارش شما: $order->id",//optional
            );

            $response = $this->postToZibal('request', $parameters);

            if ($response->result == 100) {

                $order->track_id = $response->trackId;
                $order->gate = 'zibal';
                $order->save();


                $startGateWayUrl = "https://gateway.zibal.ir/start/" . $response->trackId;
                return $startGateWayUrl;
            } else {
                echo "result: " . $response->result . "<br>";
                echo "message: " . $response->message;
            }

        }


    }

    /* */
    public function zibalCallback(Request $request)
    {
        $messageId = $request->m;
        $lock = Cache::lock('Order-' . $_GET['orderId'], 60);
        $lock->release();
        if (true) {
            if (isset($_GET['success']) && $_GET['success'] == 1) {
                try {
                    $Orders = TelegramOrders::where('id', $_GET['orderId'])->first();
                    if (!is_null($Orders)) {
                        if ($Orders->status == 0) {
                            if (isset($_GET['trackId'])) {
                                $Track_id = intval($_GET['trackId']);
                                $parameters = array(
                                    "trackId" => $Track_id,
                                );
                                $response = $this->postToZibal('verify', $parameters);
                                if ($response->result == 100) {
                                    $price = $response->amount / 10;
//                                    if ($price == $Orders->price) {

                                    switch ($Orders->type) {
                                        case 'telegram-premium':
                                            $this->telegramPremiumOrder($Orders);
                                            break;
                                        case 'telegram-fake-member':
                                            $this->telegramFakeMemberOrder($Orders);
                                            break;
                                    }

                                    $Orders->cart_num = $request->card;
                                    $Orders->ref = $request->ref_id;
                                    $Orders->save();

                                    $keyboard['inline_keyboard'][] = [
                                        ['text' => 'مشاهده جزئیات سفارش', 'callback_data' => "type=show-order|id=$Orders->id"],
                                    ];

                                    $keyboard['inline_keyboard'][] = [
                                        ['text' => '🔙 بازگشت', 'callback_data' => 'type=home|'],
                                    ];
                                    $encodedKeyboard = json_encode($keyboard);
                                    $User = TelegramUsers::find($Orders->user_id);
                                    $text = "سفارش شماره $Orders->id  با موفقیت پرداخت شد.";
                                    $token = $this->token;
                                    $data = [
                                        'chat_id' => $User->user_id,
                                        'message_id' => $messageId,
                                        'text' => $text,
                                        'reply_markup' => $encodedKeyboard,
                                        'parse_mode' => 'HTML'
                                    ];
                                    SendTelegramMessageForCustomBot($data, $token);

                                    $Status['status'] = 1;
                                    $Status['refId'] = $request->ref_id;
                                    $Status['Url'] = $this->botUrl;
                                    $Status['orderId'] = $Orders->id;
                                    $Status['msg'] = "با تشکر از اعتماد شما، سفارش با موفقیت ثبت و بزودی تایید می شود.";

                                    $lock->release();
                                    return view('services.callback', compact('Status'));

//                                    } else {
//                                        $Status['status'] = -1;
//                                        $Status['Url'] = $this->botUrl;
//                                        $Status['msg'] = "مبلغ تراکنش کمتر از مبلغ فاکتور میباشد.";
//                                        return view('services.callback', compact('Status'));
//                                    }
                                } elseif ($response->result == 201) {
                                    $Status['status'] = 1;
                                    $Status['msg'] = "با تشکر از اعتماد شما، سفارش با موفقیت ثبت و بزودی تایید می شود.";
                                    $Status['orderID'] = $Orders->id;
                                    $Status['refId'] = $Orders->ref_id;
                                    return view('services.callback', compact('Status'));
                                } else {
                                    $Status['status'] = -1;
                                    $Status['msg'] = "خطا در انجام تراکنش کد:  $response->result";
                                    return view('services.callback', compact('Status'));
                                }
                            } else {
                                $Status['status'] = -1;
                                $Status['Url'] = $this->botUrl;
                                $Status['msg'] = 'شماره تراکنش نامعتبر میباشد.';
                                return view('services.callback', compact('Status'));
                            }
                        } else {
                            $Status['status'] = -1;
                            $Status['Url'] = $this->botUrl;
                            $Status['msg'] = 'وضعیت سفارش نامعتبر می باشد.';
                            return view('services.callback', compact('Status'));
                        }
                    } else {
                        $Status['status'] = -1;
                        $Status['Url'] = $this->botUrl;
                        $Status['msg'] = 'سفارش مورد نظر یافت نشد.';
                        return view('services.callback', compact('Status'));
                    }
                } catch (\Exception $exception) {
                    $Status['status'] = -1;
                    $Status['Url'] = $this->botUrl;
                    $Status['msg'] = $exception->getMessage();
                    return view('services.callback', compact('Status'));
                }
            } else {
                $Status['status'] = -1;
                $Status['msg'] = 'خطا در انجام تراکنش';
                $Status['Url'] = $this->botUrl;
                Session::flash('error', ['این تراکنش در حال انجام میباشد کد خطا 603']);
                return view('services.callback', compact('Status'));
            }
        } else {
            $Status['status'] = -1;
            $Status['msg'] = 'این تراکنش در حال انجام می باشد.';
            $Status['Url'] = $this->botUrl;
            Session::flash('error', ['این تراکنش در حال انجام میباشد کد خطا 603']);
            return view('services.callback', compact('Status'));
        }
    }

    /* تکمیل سفارش تلگرام پریمیوم */
    public function telegramPremiumOrder($order)
    {

        $order->status = 1;
        $order->save();

    }

    public function telegramFakeMemberOrder($order)
    {

        $order->status = 1;
        $order->save();

    }

    /**/
    public function postToZibal($path, $parameters)
    {
        $parameters['merchant'] = '64aeaec5cbbc27000ec9327d';
        $url = 'https://gateway.zibal.ir/v1/' . $path;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    /* نمایش پروفایل */
    public function showProfile(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $User = TelegramUsers::where('user_id', $chatId)->first();
        $wallet = number_format($User->wallet) . ' تومان ';
        $Users = TelegramUsers::where('parent', $User->id)->count();
        $message = "اطلاعات حساب کاربری شما:";
        $keyboard['inline_keyboard'][] = [
            ['text' => $User->user_id, 'callback_data' => '#'],
            ['text' => 'شناسه تلگرام', 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][] = [
            ['text' => $wallet, 'callback_data' => '#'],
            ['text' => '💸 موجودی کیف پول:', 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][] = [
            ['text' => "👥 تعداد کاربران زیر مجموعه: $Users نفر ", 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $message,
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        if ($request->input('method') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ثبت کد رفرال */
    public function referral(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $User = TelegramUsers::where('user_id', $chatId)->first();
        $referrals = TelegramUsers::where('parent', $User->id)->count();

        $payments = TelegramPayment::where('user_id', $User->id)->where('type', 5)->sum('price');

        $keyboard['inline_keyboard'][] = [
            ['text' => $referrals, 'callback_data' => '#'],
            ['text' => "کاربران معرفی شده", 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][] = [
            ['text' => number_format($payments) . ' تومان ', 'callback_data' => '#'],
            ['text' => "سود های دریافتی", 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $url = $this->botUrl . '?start=' . $chatId;
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "
دوستاتو به ما معرفی کن
و از هر تراکنش 10% هدیه بگیر
لینک دعوت شما:
$url",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];

        if ($request->input('method') == 'send') {
            return EditTelegramMessageForCustomBot($data, $token);
        } else {
            return SendTelegramMessageForCustomBot($data, $token);
        }
    }

    /* مشاهده لیست سفارشات */
    public function ordersList(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;

        $user = TelegramUsers::where('user_id', $chatId)->first();
        $Orders = TelegramOrders::where('user_id', $user->id)->orderby('id', 'DESC')->wherenotin('status', [-1, 0])->paginate(10);
        $keyboard['inline_keyboard'][] = [
            ['text' => 'وضعیت', 'callback_data' => "#"],
            ['text' => 'نام سفارش', 'callback_data' => "#"],
            ['text' => 'شناسه', 'callback_data' => "#"],
        ];
        foreach ($Orders as $order) {
            switch ($order->status) {
                case 2:
                    $status = '🟢';
                    break;
                case 1:
                    $status = '🟡';
                    break;
                case -2:
                    $status = '🟣';
                    break;
                case -1:
                    $status = '🔴';
                    break;
                case 0:
                    $status = '🟡';
                    break;
                default:
                    $status = null;
                    break;
            }
            $keyboard['inline_keyboard'][] = [
                ['text' => $status, 'callback_data' => "type=show-order|id=$order->id"],
                ['text' => __('botServices.type.' . $order->type), 'callback_data' => "type=show-order|id=$order->id"],
                ['text' => $order->id, 'callback_data' => "type=show-order|id=$order->id"],
            ];
        }
        $current = $Orders->currentPage();
        if ($Orders->nextPageUrl() != null) {
            $next = $current + 1;
        } else {
            $next = '#';
        }
        if ($Orders->previousPageUrl() != null) {
            $pre = $current - 1;
        } else {
            $pre = '#';
        }
        $keyboard['inline_keyboard'][] = [
            ['text' => 'بعدی', 'callback_data' => "type=order-change-page|id=$next"],
            ['text' => 'قبلی', 'callback_data' => "type=order-change-page|id=$pre"],
        ];

        $page = " صفحه " . $Orders->currentPage() . ' از ' . $Orders->lastPage();
        $keyboard['inline_keyboard'][][] = ['text' => $page, 'callback_data' => "#"];
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => "type=home"];

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "لیست سفارشات شما:
راهنما:
🟢: تکمیل شده
🟣: معلق شده
🟡: درحال بررسی
🔴: لغو شده",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        if ($request->has('type2')) {
            $data2 = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];
            deleteTelegramMessageForCustomBot($data2, $token);
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* جزئیات سفارش */
    public function showOrder(Request $request)
    {
        $chatId = $this->chatId;
        $messageId = $this->messageId;
        $token = $this->token;
        $orderId = $request->input('order_id');
        $jdf = new Jdf();

        $order = TelegramOrders::find($orderId);
        $status = __('botServices.order-status-color.' . $order->status) . ' ' . __('botServices.order-status-name.' . $order->status);
        if (!is_null($order)) {
            $detail = json_decode($order->detail, true);
            if ($order->type == 'telegram-premium') {

                $Services = __('botServices.telegram-premium-name.' . $detail['type']);
                $date = '1403-10-03';
                $price = number_format($order->price);
                $Capstion = "🔺اطلاعات سفارش:

🔺نوع سفارش: تلگرام پریمیوم

🪙شماره سفارش: $orderId

مدت زمان: $Services

تاریخ حدودی اتمام: $date

وضعیت: $status

مبلغ سفارش: $price

";
            } elseif ($order->type == 'telegram-fake-member') {
                $Services = __('botServices.telegram-fake-member-name.' . $detail['type']);
                $price = number_format($order->price);
                $Capstion = "🔺اطلاعات سفارش:

🔺نوع سفارش: ممبر فیک تلگرام

🪙شماره سفارش: $orderId

تعداد: $Services

وضعیت: $status

مبلغ سفارش: $price
";
            } elseif ($order->type == 'telegram-real-member') {
                $Services = __('botServices.telegram-fake-member-name.' . $detail['type']);
                $price = number_format($order->price);
                $Capstion = "🔺اطلاعات سفارش:

🔺نوع سفارش: ممبر واقعی تلگرام

🪙شماره سفارش: $orderId

تعداد: $Services

وضعیت: $status

مبلغ سفارش: $price
";
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '⚙️راهنما ', 'callback_data' => "type=help|action=delete"],
                ['text' => '☎ پشتیبانی', 'url' => $this->telegramSupport],
            ];

            $keyboard['inline_keyboard'][][] = ['text' => '🛍 لیست سفارشات', 'callback_data' => "type=order-list|type2=delete-message"];
            $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => "type=home|type2=delete-message"];
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $Capstion,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];

            return EditTelegramMessageForCustomBot($data, $token);
        } else {
            $keyboard['inline_keyboard'][][] = ['text' => '🛍 لیست سفارشات', 'callback_data' => "type=order-list"];
            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "موردی یافت نشد",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /*------------------------------------------------------------------------------------*/
    /* دریافت پهنا */
    public function getBandwidth(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $countryId = $request->input('country_id');
        $serviceId = $request->input('service_id');

        $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->where('id', $countryId)->first();
        if (!is_null($Countries)) {
            $Products = HegzaProduct::find($serviceId);
            $keyboard['inline_keyboard'][] = [
//                ['text' => ' 💸قیمت', 'callback_data' => '#'],
                ['text' => ' 🎚حجم ها', 'callback_data' => '#'],
            ];

            for ($i = 0; $i <= 5; $i++) {
                if ($i == 0) {
                    $name = 'هیچکدام';
                    $price = '';
                } else {
                    $name = __('order.Volume-Name.' . $i) . ' - ';
                    $price = number_format(__('order.Volume-Gb.' . $i) * 2500) . ' تومان ';
                }
                $band = __('order.Volume-Gb.' . $i);

                $callbackData = "type=submit-order|country_id=$countryId|service_id=$serviceId|bandwidth=$band";
                $text = $name . '' . $price;
                $keyboard['inline_keyboard'][] = [
//                    ['text' => $price, 'callback_data' => $callbackData],
                    ['text' => $text, 'callback_data' => $callbackData],
                ];
            }

            $keyboard['inline_keyboard'][] = [
                ['text' => '📱 منو', 'callback_data' => 'type=home'],
                ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=get-service|country_id=' . $Countries->id],
            ];
            $Data = json_decode($Products->detail, true);
            $faName = $Products->faName . ' ' . $Data['volume'] . ' گیگ ';

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "لطفا حجم مورد نظرتون رو انتخاب کنید
------------------------
کشور:  $Countries->name
 تعرفه: $faName",

                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            $token = config('services.telegram.hegza_token');
            return EditTelegramMessageForCustomBot($data, $token);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

    /* ثبت سفارش */
    public function submitOrder(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $countryId = $request->input('country_id');
        $serviceId = $request->input('service_id');
        $bandWidth = $request->input('bandwidth');

        $card = HegzaSetting::find(1);
        $crypto = HegzaSetting::find(2);
        $bank = HegzaSetting::find(7);


        $Country = HegzaCountry::find($countryId);
        $Plan = HegzaProduct::find($serviceId);
        $Data = json_decode($Plan->detail, true);
        if ($Data['dis'] == 0) {
            $Plan_price = $Plan->price;
        } else {
            $Plan_price = $Data['dis'];
        }

        $Price = $bandWidth * 1500;
        $Price = $Plan_price + $Price;

        $User = HegzaUser::where('user_id', $chatId)->first();
        $newOrder = new HegzaOrder();
        $newOrder->user_id = $User->id;
        $newOrder->name = $Country->name;
        $newOrder->country_id = $countryId;
        $newOrder->service_id = $serviceId;
        $newOrder->bandwidth = $bandWidth;
        $newOrder->total_bandwidth = $bandWidth + $Data['volume'];
        $newOrder->status = 0;
        $newOrder->save();

        $Payment = new HegzaPayment();
        $Payment->order_id = $newOrder->id;
        $Payment->user_id = $User->id;
        $Payment->type = 1;
        $Payment->price = $Price;
        $Payment->save();


        $Country = HegzaCountry::find($countryId);
        $Products = HegzaProduct::find($serviceId);

        $name = $Products->faName . ' - ' . $Data['volume'] . ' گیگ حجم ';

        if ($bank->status == 1) {
            $url = $this->ZarinPalSendToBank($Payment, $chatId, $messageId);
            $keyboard['inline_keyboard'][][] = ['text' => 'خرید با درگاه', 'url' => $url];
        }
        if ($User->wallet >= $Price) {
            $keyboard['inline_keyboard'][][] = ['text' => 'خرید با کیف پول', 'callback_data' => "type=buy-order-wallet|id=$Payment->id"];
        }
        if ($card->status == 1) {
            $keyboard['inline_keyboard'][][] = ['text' => 'کارت به کارت', 'callback_data' => "type=cart-be-cart|id=$Payment->id"];
        }
//        if ($crypto->status == 1) {
//            $keyboard['inline_keyboard'][][] = ['text' => 'پرداخت با ارز دیجیتال', 'callback_data' => "type=buy-order-wallet|id=$Payment->id"];
//        }

        $Price = number_format($Price) . ' تومان ';
        $keyboard['inline_keyboard'][] = [
            ['text' => '📱 منو', 'callback_data' => 'type=home'],
            ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=get-bandwidth|country_id=' . $Country->id . '|service_id=' . $Products->id],
        ];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "سرویس انتخاب شده شما:👇

کشور: $Country->name
سرویس: $name
حجم اضافه: $bandWidth گیگ
مجموع:  $Price",

            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        return EditTelegramMessageForCustomBot($data, $token);
    }


    /* شارژ کیف پول */
    public function chargeWallet(Request $request)
    {
        $token = config('services.telegram.hegza_token');

        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $Validate = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:20000|max:2500000'
        ], [
            'amount.required' => 'مبلغ را وارد کنید',
            'amount.numeric' => 'فرمت مبلغ نامعتبر است',
            'amount.min' => 'حداقل واریزی 20 هزار تومان می باشد',
            'amount.max' => 'حداکثر واریزی 2.5 میلیون تومان می باشد.',
        ]);
        if ($Validate->fails()) {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $Validate->errors()->first(),
                'parse_mode' => 'HTML'
            ];
            if ($request->input('type') == 'edit') {
                return EditTelegramMessageForCustomBot($data, $token);
            } else {
                return SendTelegramMessageForCustomBot($data, $token);
            }
        }
        try {
            $User = HegzaUser::where('user_id', $chatId)->first();

            $Payment = new HegzaPayment();
            $Payment->order_id = 0;
            $Payment->user_id = $User->id;
            $Payment->type = 2;
            $Payment->price = $request->input('amount');
            $Payment->save();

            $url = $this->ZarinPalSendToBank($Payment, $chatId, $messageId);

            $amount = number_format($request->input('amount')) . ' تومان ';
            $keyboard['inline_keyboard'][][] = ['text' => 'پرداخت', 'url' => $url];
            $keyboard['inline_keyboard'][] = [
                ['text' => '📱 منو', 'callback_data' => 'type=home'],
                ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=charge-online'],
            ];
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "جهت پرداخت بر روی لینک زیر کلیک کنید
مبلغ قابل پرداخت: $amount",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            if ($request->input('type') == 'edit') {
                return EditTelegramMessageForCustomBot($data, $token);
            } else {
                return SendTelegramMessageForCustomBot($data, $token);
            }

        } catch (\Exception $exception) {

        }
    }

    /**/
    public function cryptoCharge(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $User = HegzaUser::where('user_id', $chatId)->first();

        $Payment = new HegzaPayment();
        $Payment->order_id = 0;
        $Payment->user_id = $User->id;
        $Payment->type = 6;
        $Payment->price = $request->input('amount');
        $Payment->save();

        $Data = [
            'paymentId' => $Payment->id,
            'amount' => $request->input('amount')
        ];
        $url = json_decode($this->plisioFunction($Data), true);
        if ($url['status'] == 'success') {
            $url = $url['data'];

            $Payment->track_id = $url['txn_id'];
            $Payment->save();

            $amount = number_format($request->input('amount')) . ' دلار ';
            $keyboard['inline_keyboard'][][] = ['text' => 'پرداخت', 'url' => $url['invoice_url']];
            $keyboard['inline_keyboard'][] = [
                ['text' => '📱 منو', 'callback_data' => 'type=home'],
                ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=charge-crypto'],
            ];
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "جهت پرداخت بر روی لینک زیر کلیک کنید
مبلغ قابل پرداخت: $amount",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            $token = config('services.telegram.hegza_token');

            if ($request->input('type') == 'edit') {
                return EditTelegramMessageForCustomBot($data, $token);
            } else {
                return SendTelegramMessageForCustomBot($data, $token);
            }
        }
    }

    /* دریافت حجم */
    public function getBandwidthForOrder(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $orderId = $request->input('id');
        for ($i = 1; $i <= 5; $i++) {
            if ($i == 0) {
                $name = 'هیچکدام';
            } else {
                $name = __('order.Volume-Name.' . $i) . ' - ' . __('order.Volume-Gb.' . $i) * 2500;
            }
            $band = __('order.Volume-Gb.' . $i);

            $callbackData = "type=buy-bandwidth|id=$orderId|bandwidth=$band";
            $keyboard['inline_keyboard'][][] = ['text' => $name, 'callback_data' => $callbackData];
        }

        $keyboard['inline_keyboard'][] = [
            ['text' => '📱 منو', 'callback_data' => 'type=home'],
            ['text' => '🔙 مرحله قبل', 'callback_data' => "type=show-order|id=$orderId"],
        ];

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "لطفا سرویس مورد نظرتون رو انتخاب کنید
------------------------
شماره سفارش: $orderId",

            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        return SendTelegramMessageForCustomBot($data, $token);

    }

    /* خرید حجم */
    public function buyBandwidth(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $bandWidth = $request->input('bandwidth');
        $orderId = $request->input('id');

        $User = HegzaUser::where('user_id', $chatId)->first();

        $Price = $bandWidth * 2500;
        $Payment = new HegzaPayment();
        $Payment->order_id = $orderId;
        $Payment->user_id = $User->id;
        $Payment->type = 3;
        $Payment->price = $Price;
        $Payment->help = $bandWidth;
        $Payment->save();

        $url = $this->ZarinPalSendToBank($Payment, $chatId, $messageId);
        $Price = number_format($Price) . ' تومان ';
        $keyboard['inline_keyboard'][][] = ['text' => 'خرید با درگاه', 'url' => $url];
        if ($User->wallet >= $Price) {
            $keyboard['inline_keyboard'][][] = ['text' => 'خرید با کیف پول', 'callback_data' => "type=buy-volume-wallet|id=$Payment->id"];
        }
        $keyboard['inline_keyboard'][] = [
            ['text' => '📱 منو', 'callback_data' => 'type=home'],
            ['text' => '🔙 مرحله قبل', 'callback_data' => "type=order-bandwidth|id=$orderId"],
        ];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "حجم انتخاب شده:
شماره سفارش: $orderId
حجم: $bandWidth گیگ
مبلغ قابل پرداخت: $Price",

            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        return EditTelegramMessageForCustomBot($data, $token);
    }

    /* نمایش تعرفه ها */
    public function getRenewItems(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $orderId = $request->input('id');

        $Order = HegzaOrder::find($orderId);
        $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->where('id', $Order->country_id)->first();

        if (!is_null($Countries)) {
            $Products = HegzaProduct::where('type', 'pu-ip-sabet')->where('category_id', $Order->country_id)->get();

            foreach ($Products as $key => $item) {
                $Data = json_decode($item->detail, true);
                if ($Data['dis'] == 0) {
                    $Plan_price = $item->price;
                } else {
                    $Plan_price = $Data['dis'];
                }
                $Star = $Order->service_id == $item->id ? '⭐️' : '';
                $name = $Star . ' ' . $item->faName . ' - ' . $Data['volume'] . ' گیگ ' . ' - ' . number_format($Plan_price) . ' تومان ';
                $callbackData = 'type=buy-renew-order|id=' . $orderId . '|service_id=' . $item->id;
                $keyboard['inline_keyboard'][][] = ['text' => $name, 'callback_data' => $callbackData];
            }

            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => "type=show-order|id=$orderId"];

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "لطفا سرویس مورد نظرتون رو انتخاب کنید:

نکته: کاربر عزیز، پس از تمدید آی پی حجم باقی مانده از دوره قبل، به دوره جدید هم منتقل خواهد. پس نگران نباشید

کشور:  $Countries->name
راهنما:
⭐️ = سرویس فعال شما",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            $token = config('services.telegram.hegza_token');
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

    /* تمدید آی پی */
    public function buyRenewIp(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $orderId = $request->input('id');
        $serviceId = $request->input('service_id');

        $Order = HegzaOrder::find($orderId);
        $Service = HegzaProduct::find($serviceId);
        $User = HegzaUser::find($Order->user_id);

        $Data = json_decode($Service->detail, true);
        if ($Data['dis'] == 0) {
            $Price = $Service->price;
        } else {
            $Price = $Data['dis'];
        }

        $Payment = new HegzaPayment();
        $Payment->order_id = $orderId;
        $Payment->user_id = $User->id;
        $Payment->type = 4;
        $Payment->price = $Price;
        $Payment->help = $Service->id;
        $Payment->save();

        $name = $Service->faName . ' - ' . $Data['volume'] . ' گیگ ';
        $url = $this->ZarinPalSendToBank($Payment, $chatId, $messageId);

        $keyboard['inline_keyboard'][][] = ['text' => 'خرید با درگاه', 'url' => $url];
        if ($User->wallet >= $Price) {
            $keyboard['inline_keyboard'][][] = ['text' => 'خرید با کیف پول', 'callback_data' => "type=buy-renew-wallet|id=$Payment->id"];
        }
        $keyboard['inline_keyboard'][] = [
            ['text' => '📱 منو', 'callback_data' => 'type=home'],
            ['text' => '🔙 مرحله قبل', 'callback_data' => "type=order-renew|id=$orderId"],
        ];
        $Price = number_format($Price) . ' تومان ';
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "تعرفه انتخاب شده:
شماره سفارش: $orderId
نوع تعرفه: $name
مبلغ قابل پرداخت: $Price",

            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        return EditTelegramMessageForCustomBot($data, $token);

    }

    /* خطاهای زرین پال */
    public $ZartinPallErrors = [

        -1 => 'اطلاعات ارسال شده ناقص میباشد',
        -2 => ' آی پی و يا مرچنت كد پذيرنده صحيح نيست.',
        -3 => 'با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد.',
        -4 => 'سطح تاييد پذيرنده پايين تر از سطح نقره اي است.',
        -11 => 'درخواست مورد نظر يافت نشد.',
        -12 => 'امكان ويرايش درخواست ميسر نمي باشد',
        -21 => 'هيچ نوع عمليات مالي براي اين تراكنش يافت نشد.',
        -22 => 'تراكنش نا موفق ميباشد.',
        -23 => 'رقم تراكنش با رقم پرداخت شده مطابقت ندارد.',
        -34 => 'سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است',
        -40 => 'اجازه دسترسي به متد مربوطه وجود ندارد',
        -41 => 'غيرمعتبر ميباشد. AdditionalData اطلاعات ارسال شده مربوط به',
        -42 => 'مدت زمان معتبر طول عمر شناسه پرداخت بايد بين 30 دقيه تا 45 روز مي باشد.',
        -54 => 'درخواست مورد نظر آرشيو شده است.',
        -100 => 'عمليات با موفقيت انجام گرديده است.',
        -101 => 'تراكنش انجام شده است. PaymentVerification عمليات پرداخت موفق بوده و قبلا',

    ];

    /* ارسال سرور های تست */
    public function testServersList(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->get();
        $Access = [];
        foreach ($Countries as $country) {
            $IP = HegzaIp::where('category_id', $country->id)->where('type', 'pu-ip-sabet-user')->where('status', 0)->first();
            if (!is_null($IP)) {
                $Access[] = $country->id;
            }
        }
        $newCountries = $Countries->chunk(2);
        foreach ($newCountries as $key => $chunk) {
            foreach ($chunk as $item) {
                if (in_array($item->id, $Access)) {
                    $name = str_replace('آی پی ثابت', '', $item->name);
                    $callbackData = 'type=get-server-list|country_id=' . $item->id;
                } else {
                    $name = str_replace('آی پی ثابت', '', $item->name) . ' 🚫 ';
                    $callbackData = 'type=get-server-list|country_id=#';
                }

                $keyboard['inline_keyboard'][$key][] = ['text' => $name, 'callback_data' => $callbackData];
            }
        }
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => 'کشوری که میخوای تست کنی رو انتخاب کن
نکته:
مهلت سرور تست 12 ساعت می باشد
حجم آیپی محدود می باشد
لطفا و حتما فایل راهنما رو مطالعه کنید',
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        if ($request->type2 == 'delete') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* ارسال سرور های تست */
    public function getServersList(Request $request)
    {
        $token = config('services.telegram.hegza_token');

        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $countryId = $request->input('country_id');
        $User = HegzaUser::where('user_id', $chatId)->first();

        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        deleteTelegramMessageForCustomBot($data, $token);

        if (is_null($User->test)) {
            $Ip = HegzaIp::where('category_id', $countryId)->first();
            $ip_detail = json_decode($Ip->detail, true);

            $NameTwo = $chatId;
            $Name = $NameTwo - rand(1111, 9999);
            $items = [
                'Session' => $ip_detail['session'],
                'api_domain' => $ip_detail['ip_api_domain'],
                'ip_address' => $ip_detail['ip_address'],
                'ip_port' => $ip_detail['ip_port'],
                'ip_inbound' => $ip_detail['ip_inbound'],
                'port_inbound' => $ip_detail['port_inbound'],
                'name' => $Name,
            ];
            $data = addClientToSanaie($items);
            $code = $data['msg'];
            $date = Carbon::now()->addHours(12);
            $timeInMilliseconds = $date->valueOf();
            $Volume = 0.1 * 1073741824;

            $Data['time'] = $timeInMilliseconds;
            $Data['hajm'] = $Volume;
            $Data['ip_address'] = $ip_detail['ip_address'];
            $Data['ip_port'] = $ip_detail['ip_port'];
            $Data['Session'] = $ip_detail['session'];
            $Data['uid'] = $data['UID'];
            $Data['name'] = $Name;
            $Data['ip_api_domain'] = $ip_detail['ip_api_domain'];
            $Data['Data'] = $ip_detail;

            updateSanaiePanel($Data);

            $User->test = $code;
            $User->save();

            $Capstion = "🔺اطلاعات سرویس تست:

```$code```
";

            $keyboard['inline_keyboard'][] = [
                ['text' => '⚙️راهنما ', 'callback_data' => "type=help|action=delete"],
                ['text' => '☎ پشتیبانی', 'url' => $this->telegramSupport],
            ];

            $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => "type=home|type2=delete-message"];
            $data = [
                'chat_id' => $chatId,
                'photo' => 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($code),
                'caption' => $Capstion,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'MarkdownV2'
            ];

            return sendPhotoTelegramMessageForCustomBot($data, $token);
        } else {
            $code = $User->test;
            $Capstion = "🔺اطلاعات سرویس تست:

```$code```
";

            $keyboard['inline_keyboard'][] = [
                ['text' => '⚙️راهنما ', 'callback_data' => "type=help|action=delete"],
                ['text' => '☎ پشتیبانی', 'url' => $this->telegramSupport],
            ];

            $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => "type=home|type2=delete-message"];
            $data = [
                'chat_id' => $chatId,
                'photo' => 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($code),
                'caption' => $Capstion,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'MarkdownV2'
            ];

            $token = config('services.telegram.hegza_token');
            return sendPhotoTelegramMessageForCustomBot($data, $token);
        }

    }


    /* send telegram message */
    public function telegramSendMessage()
    {
        $token = config('services.telegram.hegza_token');

        $message = HegzaMessage::where('status', 0)->first();
        $second = 0;
        if (!is_null($message)) {
            $users = HegzaUser::where('user_id', '!=', null)->where('message', 0)->select('id', 'user_id')->take(100)->get();
            if (count($users) != 0) {
                if ($message->short_detail == 'all') {

                    $keyboard['inline_keyboard'][][] = ['text' => 'سرویس تست', 'callback_data' => 'type=test-server-list'];
                    $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];

                    foreach ($users as $item) {
                        $second = $second + 1;
                        if ($second <= 15) {
                            $item = HegzaUser::find($item->id);
                            $item->message = 1;
                            $item->save();
                            $data = [
                                'chat_id' => $item->user_id,
                                'text' => $message->long_detail,
                                'reply_markup' => json_encode($keyboard),
                                'parse_mode' => 'HTML'
                            ];
                            SendTelegramMessageForCustomBot($data, $token);
                            sleep(1);
                        } else {
                            $second = 0;
                            sleep(2);
                        }
                    }
                } else {
                    $Ip = HegzaIp::find($message->short_detail);
                    $users = HegzaIp::where('parent', $Ip->id)->where('status', 2)->pluck('user_id')->toArray();
                    $users = HegzaUser::wherein('id', $users)->where('message', 0)->select('id', 'user_id')->take(250)->get();
                    if (count($users) != 0) {
                        foreach ($users as $item) {
                            $second = $second + 1;
                            if ($second < 28) {
                                $item = HegzaUser::find($item->id);
                                $item->message = 1;
                                $item->save();

                                return SendTelegramMessageForCustomBot($data, $token);
                            } else {
                                $second = 0;
                                sleep(2);
                            }
                        }
                    } else {
                        DB::table('hegza_user')->update(['message' => 0]);
                        $message->status = "1";
                        $message->save();
                    }
                }
            } else {
                DB::table('hegza_user')->update(['message' => 0]);
                $message->status = 1;
                $message->save();
            }
        }
    }

    /**/
    public function report()
    {
        $totalUsers = HegzaUser::count();
        $totalOrders = HegzaOrder::where('status', 2)->count();
        $totalTransactions = number_format(HegzaPayment::where('status', 1)->where('user_id', '!=', 17)->sum('price'));

        $todayUsers = HegzaUser::whereDate('created_at', Carbon::today())->count();
        $todayOrders = HegzaOrder::whereDate('created_at', Carbon::today())->where('status', 2)->count();
        $todayTransactions = number_format(HegzaPayment::where('status', 1)->wherein('type', [1, 2, 3, 4])->whereDate('created_at', Carbon::today())->sum('price'));

        $yesterdayUsers = HegzaUser::whereDate('created_at', Carbon::yesterday())->count();
        $yesterdayOrders = HegzaOrder::whereDate('created_at', Carbon::yesterday())->where('status', 2)->count();
        $yesterdayTransactions = number_format(HegzaPayment::where('status', 1)->wherein('type', [1, 2, 3, 4])->whereDate('created_at', Carbon::yesterday())->sum('price'));

        $keyboard['inline_keyboard'][][] = ['text' => 'کل اطلاعات ها', 'callback_data' => "type=show-order|id=#"];
        $keyboard['inline_keyboard'][] = [
            ['text' => "👥: $totalUsers", 'callback_data' => "type=show-order|id=#"],
            ['text' => "🛍: $totalOrders", 'callback_data' => "type=show-order|id=#"]
        ];
        $keyboard['inline_keyboard'][][] = ['text' => "💸: $totalTransactions", 'callback_data' => "type=show-order|id=#"];

        $keyboard['inline_keyboard'][][] = ['text' => 'اطلاعات دیروز', 'callback_data' => "type=show-order|id=#"];
        $keyboard['inline_keyboard'][] = [
            ['text' => "👥: $yesterdayUsers", 'callback_data' => "type=show-order|id=#"],
            ['text' => "🛍: $yesterdayOrders", 'callback_data' => "type=show-order|id=#"]
        ];
        $keyboard['inline_keyboard'][][] = ['text' => "💸: $yesterdayTransactions", 'callback_data' => "type=show-order|id=#"];

        $keyboard['inline_keyboard'][][] = ['text' => 'اطلاعات امروز', 'callback_data' => "type=show-order|id=#"];
        $keyboard['inline_keyboard'][] = [
            ['text' => "👥: $todayUsers", 'callback_data' => "type=show-order|id=#"],
            ['text' => "🛍: $todayOrders", 'callback_data' => "type=show-order|id=#"]
        ];

        $keyboard['inline_keyboard'][][] = ['text' => "💸: $todayTransactions", 'callback_data' => "type=show-order|id=#"];

        $data = [
            'chat_id' => -4157397982,
            'text' => "اطلاعات ",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.token');;

        SendTelegramMessageForCustomBot($data, $token);
    }

    /**/
    public function showPage(Request $request)
    {
        if ($request->has('ref')) {
            $user = HegzaUser::where('user_id', $request->input('ref'))->first();
            if (!is_null($user)) {
                $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->get();
                $Access = [];
                foreach ($Countries as $country) {
                    $IP = HegzaIp::where('category_id', $country->id)->where('type', 'pu-ip-sabet-user')->where('status', 0)->first();
                    if (!is_null($IP)) {
                        $Access[] = $country->id;
                    }
                }
                $id = $request->input('ref');
                $Products = HegzaProduct::where('type', 'pu-ip-sabet')->where('category_id', $Countries->first()->id)->get();
                return view('bot.index', compact('Countries', 'Products', 'Access', 'id'));

            } else {
                abort(404);
            }
        } else {
            abort(404);
        }
    }

    /* جزئیات تعرفه کشور */
    public function IpSabetProductDetailJson(Request $request)
    {
        $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->where('id', $request->input('id'))->first();
        if (!is_null($Countries)) {
            $Products = HegzaProduct::where('type', 'pu-ip-sabet')->where('category_id', $Countries->id)->get();
            $Data = view('bot.products-json', compact('Products'))->render();
            return response()->json(['data' => $Data], 200);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

    /* ثبت درخواست */
    public function SubmitOrderFromSite(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'country_id' => 'required|numeric',
                'plan_id' => 'required|numeric',
                'volume_id' => 'required|numeric',
            ],
            [
                'country_id.required' => 'لطفا کشور را انتخاب نمایید.',
                'plan_id.required' => 'لطفا پلن را انتخاب نماید.',
                'volume_id.required' => 'لطفا حجم را انتخاب نمایید.',
                'country_id.numeric' => 'فرمت کشور نامعتبر میباشد.',
                'plan_id.numeric' => 'فرمت تعرفه نامعتبر میباشد.',
                'volume_id.numeric' => 'فرمت حجم نامعتبر میباشد.',
            ]);
        if ($Validate->fails()) {
            return response()->json(['status' => 2, 'error' => $Validate->errors()->all()], 400);
        }
        try {
            $Country = HegzaCountry::where('id', $request->input('country_id'))->where('type', 'pu-ip-sabet')->first();
            if (!is_null($Country)) {
                $Plan = HegzaProduct::where('id', $request->input('plan_id'))->first();
                if (!is_null($Plan)) {
                    if ($request->input('volume_id') >= 0 && $request->input('volume_id') <= 5) {


                        $Data = json_decode($Plan->detail, true);
                        if ($Data['dis'] == 0) {
                            $Plan_price = $Plan->price;
                        } else {
                            $Plan_price = $Data['dis'];
                        }
                        $Price = __('order.Volume-Gb.' . $request->input('volume_id')) * 1500;
                        $Price = $Plan_price + $Price;

                        $Data['serviceName'] = $Country->name;
                        $Data['PlanName'] = $Plan->faName;
                        $Data['VolumeName'] = __('order.Volume-Name.' . $request->input('volume_id'));
                        $Data['price'] = number_format($Price);
                        $Data['image'] = url($Country->image);

                        if ($request->input('ref') == 292680769) {
                            $Price = 3000;
                        }

                        $newOrder = new HegzaOrder();
                        $newOrder->user_id = 0;
                        $newOrder->name = $Country->name;
                        $newOrder->country_id = $Country->id;
                        $newOrder->service_id = $Plan->id;
                        $newOrder->bandwidth = __('order.Volume-Gb.' . $request->input('volume_id'));
                        $newOrder->total_bandwidth = __('order.Volume-Gb.' . $request->input('volume_id')) + $Data['volume'];
                        $newOrder->status = 0;
                        $newOrder->help = $request->input('ref');
                        $newOrder->save();

                        $Payment = new HegzaPayment();
                        $Payment->order_id = $newOrder->id;
                        $Payment->user_id = 0;
                        $Payment->type = 1;
                        $Payment->price = $Price;
                        $Payment->save();

                        $url = $this->ZarinPalSendToBank($Payment, 0, 0, $request->input('ref'));
                        $Data['btn'] = $url;
                        return response()->json(['data' => $Data], 200);

                    } else {
                        return response()->json(['status' => 2, 'error' => ['حجم مورد نظر یافت نشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 2, 'error' => ['پلن مورد نظر یافت نشد.']], 400);
                }
            } else {
                return response()->json(['status' => 2, 'error' => ['کشور مورد نظر یافت نشد.']], 400);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => 2, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* درگاه پرداخت زیرن پال */
    public function ZarinPalSendToBank($payment, $userId, $messageId, $ref = 0)
    {
        $setting = HegzaSetting::find(7);
        $detail = json_decode($setting->value, true);
        if (array_key_exists('zarinpal', $detail) && $detail['zarinpal'] != null) {
            $MerchantID = $detail['zarinpal'];

            $Description = "بابت تراکنش شماره $payment->id";
            $Amount = $payment->price;

            $Email = null;
            $Mobile = $userId;
            $CallbackURL = url("api/hegza/zarinpal-callback?m_id=$messageId&ref=$ref");
            $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);
            if (Auth::guard('admin')->check() || $userId == 292680769) {
                $Amount = 3000;
            }
            $result = $client->PaymentRequest(
                [
                    'MerchantID' => $MerchantID,
                    'Amount' => $Amount,
                    'Description' => $Description,
                    'Email' => $Email,
                    'Mobile' => $Mobile,
                    'CallbackURL' => $CallbackURL,
                ]
            );

            if ($result->Status == 100) {
                $Pay = $payment;
                $Pay->track_id = $result->Authority;
                $Pay->method = 'bank';
                $Pay->save();
                $url = 'https://www.zarinpal.com/pg/StartPay/' . $result->Authority;
                return $url;
            } else {

                $Pay = HegzaPayment::find($payment->id);
                $Pay->error = $this->ZartinPallErrors[$result->Status];
                $Pay->save();
                echo 'ERR: ' . $this->ZartinPallErrors[$result->Status];
            }
        }

        $url = 'error';
        return $url;

    }

    /* برگشت از بانک زرین پال */
    public function zarinPalCallback(Request $request)
    {
        try {
            $messageId = $request->input('m_id');
            $payment = HegzaPayment::where('track_id', $request->input('Authority'))->first();
            if (!is_null($payment)) {
                $user = HegzaUser::find($payment->user_id);
                $chatId = 0;
                if (!is_null($user)) {
                    $chatId = $user->user_id;
                }
                $order = HegzaOrder::find($payment->order_id);

                $setting = HegzaSetting::find(7);
                $detail = json_decode($setting->value, true);

                $MerchantID = $detail['zarinpal'];

                $Authority = $request->input('Authority');
                if ($request->input('Status') == 'OK') {
                    if ($user->user_id == '292680769') {
                        $Amount = 30000;
                    } else {
                        $Amount = $payment->price * 10;
                    }
                    $result = [
                        'merchant_id' => $MerchantID,
                        'authority' => $Authority,
                        'amount' => $Amount,
                    ];
                    $response = ZarinPalMethod('https://api.zarinpal.com/pg/v4/payment/verify.json', $result);

                    if (array_key_exists('data', $response)) {
                        if ($response['data']['code'] == 100 || $response['data']['code'] == 101) {
                            $Ref = $response['data']['ref_id'];

                            $payment->ref_id = $Ref;
                            $payment->gate = 'ZarinPal';
                            $payment->status = 1;
                            $payment->card = $response['data']['card_pan'];
                            $payment->paid_at = Carbon::now();
                            $payment->save();

                            if ($payment->order_id != 0) {
                                $order = HegzaOrder::find($payment->order_id);
                                $order->status = 1;
                                $order->save();

                                if ($payment->user_id != 0 && $user->parent != 0) {
                                    $parentUser = HegzaUser::find($user->parent);

                                    $percent = $payment->price / 100;
                                    $percent = $percent * $parentUser->percent;

                                    $hegzaReferral = new HegzaPayment();
                                    $hegzaReferral->user_id = $user->parent;
                                    $hegzaReferral->order_id = $user->order_id;
                                    $hegzaReferral->price = $percent;
                                    $hegzaReferral->type = 5;
                                    $hegzaReferral->status = 1;
                                    $hegzaReferral->save();

                                    $parentUser = HegzaUser::find($user->parent);
                                    $parentUser->wallet = $parentUser->wallet + $percent;
                                    $parentUser->save();
                                }
                            }

                            if ($payment->user_id == 0 && $request->input('ref') != 0) {

                                $parentUser = HegzaUser::where('user_id', $request->input('ref'))->first();
                                if (!is_null($parentUser)) {

                                    $percent = $payment->price / 100;
                                    $percent = $percent * $parentUser->percent;

                                    $hegzaReferral = new HegzaPayment();
                                    $hegzaReferral->user_id = 0;
                                    $hegzaReferral->order_id = $payment->order_id;
                                    $hegzaReferral->price = $percent;
                                    $hegzaReferral->type = 5;
                                    $hegzaReferral->status = 1;
                                    $hegzaReferral->save();

                                    $parentUser->wallet = $parentUser->wallet + $percent;
                                    $parentUser->save();
                                }
                            }
                            if ($payment->type == 1) {
                                return $this->IpSabetCallBackV2($order, $response, $payment, $request);
                            } elseif ($payment->type == 2) {
                                return $this->chargeWalletCallBack($payment, $request);
                            } elseif ($payment->type == 3) {
                                return $this->chargeBandwidthCallback($order, $response, $payment, $request);
                            } elseif ($payment->type == 4) {
                                return $this->renewOrderCallback($order, $response, $payment, $request);
                            }
                        } elseif ($response['data']['code'] == 101) {
                            if ($payment->user_id == 0) {
                                $Data['msg'] = "این تراکنش قبلا وریفای شده است.";
                                $Status = -1;
                                return view('bot.callback', compact('Status', 'Data'));
                            } else {
                                $error = "این تراکنش قبلا وریفای شده است.";

                                $keyboard['inline_keyboard'][] = [
                                    ['text' => 'منو', 'callback_data' => 'type=home'],
                                    ['text' => 'مرحله قبل', 'callback_data' => 'type=submit-order|country_id=' . $order->country_id . '|service_id=' . $order->service_id . '|bandwidth=' . $order->bandwidth],
                                ];
                                $data = [
                                    'chat_id' => $chatId,
                                    'message_id' => $messageId,
                                    'text' => "⚠️ خطا در پرداخت
علت خطا:
$error",
                                    'reply_markup' => json_encode($keyboard),
                                    'parse_mode' => 'HTML'
                                ];
                                $token = config('services.telegram.hegza_token');
                                EditTelegramMessageForCustomBot($data, $token);
                                return redirect("https://t.me/RoopsidaServiceBot");
                            }
                        } else {

                            if ($payment->user_id == 0) {
                                $Data['msg'] = "خطا در پرداخت";
                                $Status = -1;
                                return view('bot.callback', compact('Status', 'Data'));

                            } else {
                                $keyboard['inline_keyboard'][] = [
                                    ['text' => '📱 منو', 'callback_data' => 'type=home'],
                                    ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=submit-order|country_id=' . $order->country_id . '|service_id=' . $order->service_id . '|bandwidth=' . $order->bandwidth],
                                ];
                                $data = [
                                    'chat_id' => $chatId,
                                    'message_id' => $messageId,
                                    'text' => "⚠️ خطا در پرداخت",
                                    'reply_markup' => json_encode($keyboard),
                                    'parse_mode' => 'HTML'
                                ];
                                $token = config('services.telegram.hegza_token');
                                EditTelegramMessageForCustomBot($data, $token);
                                return redirect("https://t.me/RoopsidaServiceBot");
                            }
                        }
                    } else {

                        if ($payment->user_id == 0) {
                            $Data['msg'] = $response['error']['message'];
                            $Status = -1;
                            return view('bot.callback', compact('Status', 'Data'));

                        } else {
                            $payment->status = -4;
                            $payment->error = $response['error']['message'];
                            $payment->gate = 'ZarinPal';
                            $payment->save();

                            $Order = HegzaOrder::find($payment->order_id);
                            $Order->status = -1;
                            $Order->save();

                            $error = $response['error']['message'];
                            $keyboard['inline_keyboard'][] = [
                                ['text' => '📱 منو', 'callback_data' => 'type=home'],
                                ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=submit-order|country_id=' . $order->country_id . '|service_id=' . $order->service_id . '|bandwidth=' . $order->bandwidth],
                            ];
                            $data = [
                                'chat_id' => $chatId,
                                'message_id' => $messageId,
                                'text' => "⚠️ خطا در پرداخت
علت خطا:
$error",
                                'reply_markup' => json_encode($keyboard),
                                'parse_mode' => 'HTML'
                            ];
                            $token = config('services.telegram.hegza_token');
                            EditTelegramMessageForCustomBot($data, $token);
                            return redirect("https://t.me/RoopsidaServiceBot");
                        }
                    }
                } else {

                    if ($payment->user_id == 0) {
                        $Data['msg'] = "لغو شده توسط کاربر";
                        $Status = -1;
                        return view('bot.callback', compact('Status', 'Data'));
                    } else {
                        $payment->status = -1;
                        $payment->error = 'لغو شده توسط کاربر';
                        $payment->save();

                        $Order = HegzaOrder::find($payment->order_id);
                        $Order->status = -1;
                        $Order->save();

                        $error = 'لغو شده توسط کاربر';
                        $url = 'https://www.zarinpal.com/pg/StartPay/' . $payment->track_id;
                        $keyboard['inline_keyboard'][][] = ['text' => 'پرداخت مجدد', 'url' => $url];
                        $keyboard['inline_keyboard'][][] = ['text' => 'شارژ کیف پول', 'callback_data' => 'type=buy-ip'];
                        $keyboard['inline_keyboard'][] = [
                            ['text' => '📱 منو', 'callback_data' => 'type=home'],
                            ['text' => '🔙 مرحله قبل', 'callback_data' => 'type=submit-order|country_id=' . $order->country_id . '|service_id=' . $order->service_id . '|bandwidth=' . $order->bandwidth],
                        ];
                        $data = [
                            'chat_id' => $chatId,
                            'message_id' => $messageId,
                            'text' => "⚠️ خطا در پرداخت
علت خطا:
$error",
                            'reply_markup' => json_encode($keyboard),
                            'parse_mode' => 'HTML'
                        ];
                        $token = config('services.telegram.hegza_token');
                        EditTelegramMessageForCustomBot($data, $token);

                        return redirect("https://t.me/RoopsidaServiceBot");
                    }
                }
            } else {
                return redirect('https://t.me/RoopsidaServiceBot');
            }
        } catch (\Exception $exception) {
            $Status = false;
            $Data = $exception->getMessage();
            dd($Data);
            return redirect("https://t.me/RoopsidaServiceBot");
        }
    }
}
