<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\BotUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ArayeshController extends Controller
{

    protected $token = null;

    // Read from .env (see config/services.php) instead of keeping the key in code.
    public function __construct()
    {
        $this->token = config('services.telegram.arayesh_token');
    }

    public function Home($chatId, $message_id)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'رزرو وقت', 'callback_data' => 'type=buy-ip|'],
                ],
                [
                    ['text' => '🛍 نوبت های من', 'callback_data' => 'type=order-list|'],
                    ['text' => '👤 حساب کاربری', 'callback_data' => 'type=show-profile|'],
                ],
                [
                    ['text' => 'زیر مجموعه ها', 'callback_data' => 'type=charge-wallet|'],
                    ['text' => 'شبکه های اجتماعی', 'callback_data' => 'type=rules|'],
                ],
                [
                    ['text' => '☎️ تماس با ما', 'url' => 'https://t.me/hegzaSupport'],
                    ['text' => 'چرا اعتماد به ما؟', 'callback_data' => 'type=referral|'],
                ],
                [
                    ['text' => 'آدرس ما', 'callback_data' => 'type=test-server-list|'],
                ],
            ]
        ];
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $message_id,
            'text' => 'سلام دوست عزیزم ✌️
به ربات خوش اومدید ❤️
در بخش رزرو نوبت، وقتی که میخوای تشریف رو انتخاب کن 🤓
قیمت ها به تومان هستش 💰',
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        return $data;
    }

    /* تایید شماره */
    public function checkNumber(Request $request)
    {
        $token = $this->token;
        $chatId = $request->input('chatId');
        $messageId = $request->input('messageId');

        $data = [
            'number' => PersianNumToEn($request->input('number')),
        ];
        $validate = Validator::make($data, [
            'number' => 'required|digits:11'
        ], [
            'number.required' => 'لظفا شماره را وارد کنید',
            'number.digits' => 'فرمت شماره نامعتبر می باشد.',
        ]);
        if ($validate->fails()) {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $validate->errors()->first(),
                'parse_mode' => 'HTML'
            ];
            return EditTelegramMessageForCustomBot($data, $token);
        }

        if ($request->input('answer') == 'yes') {

            $checkNumber = BotUser::where('mobile', $data['number'])->first();
            if (is_null($checkNumber)) {

                $code = rand(1000, 9999);

                $Sms = [
                    'CODE' => $code,
                ];
                sendSmsWithSmsir($data['number'], $Sms, 100000);

                $user = BotUser::where('user_id', $chatId)->first();

                $user->mobile = $data['number'];
                $user->code = $code;
                $user->path = 'code';
                $user->save();

                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => 'لطفا کد ارسال شده را وارد کنید',
                    'parse_mode' => 'HTML'
                ];
                return EditTelegramMessageForCustomBot($data, $token);

            } else {
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => 'این شماره قبلا ثبت شده است، شماره جدید وارد کنید',
                    'parse_mode' => 'HTML'
                ];
                return EditTelegramMessageForCustomBot($data, $token);
            }
        } elseif ($request->input('answer') == 'no') {

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => 'این شماره قبلا ثبت شده است، شماره جدید وارد کنید',
                'parse_mode' => 'HTML'
            ];
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /* تایید کد */
    public function checkCode(Request $request)
    {
        $token = $this->token;
        $chatId = $request->input('chatId');
        $code = $request->input('code');

        $data = [
            'code' => $request->input('code'),
        ];
        $validate = Validator::make($data, [
            'code' => 'required|digits:4'
        ], [
            'code.required' => 'لظفا کد را وارد کنید',
            'code.digits' => 'فرمت کد نامعتبر می باشد.',
        ]);
        if ($validate->fails()) {
            $data = [
                'chat_id' => $chatId,
                'text' => $validate->errors()->first(),
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        }
        try {

            $checkUser = BotUser::where('user_id', $chatId)->first();
            if (!is_null($checkUser)) {
                if ($checkUser->code == $data['code']) {

                    $checkUser->path = 'start';
                    $checkUser->status = 1;
                    $checkUser->save();

                    $home = $this->Home($chatId, $message_id = null);
                    SendTelegramMessageForCustomBot($home, $token);


                } else {
                    $data = [
                        'chat_id' => $chatId,
                        'text' => 'کد نامعتبر میباشد.',
                        'parse_mode' => 'HTML'
                    ];
                    return SendTelegramMessageForCustomBot($data, $token);
                }
            } else {
                $data = [
                    'chat_id' => $chatId,
                    'text' => 'کاربری با این مشخصات یافت نشد.',
                    'parse_mode' => 'HTML'
                ];
                return SendTelegramMessageForCustomBot($data, $token);
            }
        } catch (\Exception $exception) {
            $data = [
                'chat_id' => $chatId,
                'text' => $exception->getMessage(),
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);
        }
    }

    /* لیست سرویس ها */
    public function serviceList(Request $request)
    {

    }
}
