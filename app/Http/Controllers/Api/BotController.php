<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\HegzaBot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\HegzaCountry;
use App\Models\Comment;
use App\Models\HegzaIp;
use App\Models\Pages;
use App\Models\HegzaProduct;
use App\Models\HegzaOrder;
use App\Models\HegzaUser;
use App\Models\HegzaPayment;
use App\Models\HegzaMessage;
use App\Models\HegzaSetting;
use App\Models\HegzaInboundClients;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class BotController extends Controller
{
    protected $token = null;

    // Read from .env (see config/services.php) instead of keeping the key in code.
    public function __construct()
    {
        $this->token = config('services.telegram.hegza_token');
    }

   public function testTelegram(Request $request)
    {
        DB::table('hegza_bot')->insert([
            'user_id' => 1,
            'data'=> json_encode($request->all())
        ]);
    }

    /* دریافت کشور ها */
    public function buyIp(Request $request)
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
                    $callbackData = 'type=get-service|country_id=' . $item->id;
                } else {
                    $name = str_replace('آی پی ثابت', '', $item->name) . ' 🚫 ';
                    $callbackData = 'type=get-service|country_id=#';
                }

                $keyboard['inline_keyboard'][$key][] = ['text' => $name, 'callback_data' => $callbackData];
            }
        }
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => 'کشور مورد نظرت رو انتخاب کن',
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

    /* دریافت سرویس ها */
    public function getService(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $countryId = $request->input('country_id');

        $Countries = HegzaCountry::where('type', 'pu-ip-sabet')->where('id', $countryId)->first();
        if (!is_null($Countries)) {
            $Products = HegzaProduct::where('type', 'pu-ip-sabet')->where('category_id', $Countries->id)->get();

            $keyboard['inline_keyboard'][] = [
//                ['text' => ' 💸قیمت', 'callback_data' => '#'],
//                ['text' => ' 🎚حجم', 'callback_data' => '#'],
                ['text' => 'سرویس ها', 'callback_data' => '#'],
            ];

            foreach ($Products as $key => $item) {
                $Data = json_decode($item->detail, true);
                if ($Data['dis'] == 0) {
                    $Plan_price = $item->price;
                } else {
                    $Plan_price = $Data['dis'];
                }
                $callbackData = 'type=get-bandwidth|country_id=' . $Countries->id . '|service_id=' . $item->id;
                $text = $item->faName . ' - ' . $Data['volume'] . ' گیگ -' . number_format($Plan_price) . ' تومان ';
                $keyboard['inline_keyboard'][] = [
//                    ['text' => number_format($Plan_price) . ' تومان ', 'callback_data' => $callbackData],
//                    ['text' => $Data['volume'] . ' گیگ ', 'callback_data' => $callbackData],
                    ['text' => $text, 'callback_data' => $callbackData],
                ];
            }

            $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=buy-ip'];

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => "لطفا سرویس مورد نظرتون رو انتخاب کنید
------------------------
کشور:  $Countries->name",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            $token = config('services.telegram.hegza_token');
            return EditTelegramMessageForCustomBot($data, $token);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

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

    /* مشاهده لیست سفارشات */
    public function ordersList(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $user = HegzaUser::where('user_id', $chatId)->first();
        $Orders = HegzaOrder::where('user_id', $user->id)->orderby('id', 'DESC')->wherenotin('status', [-1, 0])->paginate(10);
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
                    $status = '🟢';
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
                ['text' => str_replace('آی پی ثابت', '', $order->name), 'callback_data' => "type=show-order|id=$order->id"],
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
        $token = config('services.telegram.hegza_token');
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
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $orderId = $request->input('order_id');
        $jdf = new Jdf();
        $token = config('services.telegram.hegza_token');

        $order = HegzaOrder::find($orderId);
        $ip = HegzaIp::where('order_id', $order->id)->first();

        if (!is_null($ip)) {
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];

            deleteTelegramMessageForCustomBot($data, $token);

            $order = HegzaOrder::find($orderId);
            $ip = HegzaIp::where('order_id', $order->id)->first();
            $ipDetail = json_decode($ip->detail, true);
            $country = HegzaCountry::find($order->country_id)->name;
            $total = $order->total_bandwidth;

            $Domain = HegzaIp::find($ip->parent_id);
            $ServerIp = json_decode($Domain->detail, true);
            $IpDetail = json_decode($ip->detail, true);

            $GetData['ip_address'] = $ServerIp['ip_api_domain'];
            $GetData['ip_port'] = $ServerIp['ip_port'];
            $GetData['Session'] = $ServerIp['session'];
            $GetData['uid'] = $ip->UID;
            $GetData['name'] = $IpDetail['user_remark'];
            $GetData['ip_api_domain'] = $ServerIp['ip_api_domain'];
            $GetData['Data'] = $ServerIp;
            $usedVolume = getClientTraffics($GetData);

            if ($usedVolume['success'] == false) {
                $usedVolume = 'نامشخص';
            } else {
                $usedVolume = number_format($usedVolume['hajm']) . ' مگابایت ';
            }

            $expiredDate = $jdf->jdate('Y/m/d ساعت: H:i', strtotime($ip->expire_date));
            $code = $ipDetail['user_code'];
            $Capstion = "🔺اطلاعات سرویس:

🪙شماره سفارش: $orderId

🪙کشور: $country

🔋حجم کل: $total گیگ

⏳حجم مصرف شده: $usedVolume

⏱ تاریخ اتمام اشتراک: $expiredDate

```$code```
";

            $keyboard['inline_keyboard'][] = [
                ['text' => '⏰ تمدید', 'callback_data' => "type=order-renew|id=$order->id"],
                ['text' => '♻️ خرید حجم اضافه', 'callback_data' => "type=order-bandwidth|id=$order->id"],
            ];
            $keyboard['inline_keyboard'][] = [
                ['text' => '⚙️راهنما ', 'callback_data' => "type=help|action=delete"],
                ['text' => '☎ پشتیبانی', 'url' => 'https://t.me/hegzaSupport'],
            ];

            $keyboard['inline_keyboard'][][] = ['text' => '🛍 لیست سفارشات', 'callback_data' => "type=order-list|type2=delete-message"];
            $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => "type=home|type2=delete-message"];
            $data = [
                'chat_id' => $chatId,
                'photo' => 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($ipDetail['user_code']),
                'caption' => $Capstion,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'MarkdownV2'
            ];

            return sendPhotoTelegramMessageForCustomBot($data, $token);
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

    /* نمایش پروفایل */
    public function showProfile(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $User = HegzaUser::where('user_id', $chatId)->first();
        $wallet = number_format($User->wallet) . ' تومان ';
        $Users = HegzaUser::where('parent', $User->id)->count();
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
        $token = config('services.telegram.hegza_token');
        if ($request->has('type')) {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
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
                ['text' => '☎ پشتیبانی', 'url' => 'https://t.me/hegzaSupport'],
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
                ['text' => '☎ پشتیبانی', 'url' => 'https://t.me/hegzaSupport'],
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

    /* ثبت کد رفرال */
    public function referral(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $User = HegzaUser::where('user_id', $chatId)->first();
        $referrals = HegzaUser::where('parent', $User->id)->count();

        $payments = HegzaPayment::where('user_id', $User->id)->where('type', 5)->sum('price');

        $keyboard['inline_keyboard'][] = [
            ['text' => $referrals, 'callback_data' => '#'],
            ['text' => "کاربران معرفی شده", 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][] = [
            ['text' => number_format($payments) . ' تومان ', 'callback_data' => '#'],
            ['text' => "سود های دریافتی", 'callback_data' => '#'],
        ];
        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];
        $url = 'https://t.me/HegzaVpnBot?start=' . $chatId;
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
        $token = config('services.telegram.hegza_token');
        if ($request->input('type2') == 'delete') {
            return EditTelegramMessageForCustomBot($data, $token);
        } else {
            return SendTelegramMessageForCustomBot($data, $token);
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
                        }
                        elseif ($response['data']['code'] == 101) {
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
                                return redirect("https://t.me/HegzaVpnBot");
                            }
                        }
                        else {

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
                                return redirect("https://t.me/HegzaVpnBot");
                            }
                        }
                    }
                    else {

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
                            return redirect("https://t.me/HegzaVpnBot");
                        }
                    }
                }
                else {

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

                        return redirect("https://t.me/HegzaVpnBot");
                    }
                }
            } else {
                return redirect('https://t.me/HegzaVpnBot');
            }
        } catch (\Exception $exception) {
            $Status = false;
            $Data = $exception->getMessage();
            dd($Data);
            return redirect("https://t.me/HegzaVpnBot");
        }
    }

    /* شارژ کیف پول */
    public function chargeWalletCallBack($payment, $request)
    {
        $User = HegzaUser::find($payment->user_id);
        $User->wallet = $User->wallet + $payment->price;
        $User->save();

        $messageId = $request->input('m_id');
        $chatId = $User->user_id;
        $ref = $payment->ref_id;

        $keyboard['inline_keyboard'][][] = ['text' => 'خرید سرویس', 'callback_data' => "type=buy-ip|"];
        $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "✅ شارژ کیف پول با موفقیت انجام شد.
🔰 کد رهگیری: $ref
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];

        $token = config('services.telegram.hegza_token');
        EditTelegramMessageForCustomBot($data, $token);
        return redirect('https://t.me/HegzaVpnBot');
    }

    /* تحویل آی پی */
    public function IpSabetCallBack($Order, $response, $Payment, $request)
    {
        try {
            $messageId = $request->input('m_id');

            $user = HegzaUser::find($Order->user_id);
            $chatId = 0;
            if (!is_null($user)) {
                $chatId = $user->user_id;
            }

            $Product = HegzaProduct::find($Order->service_id);
            $Country = HegzaCountry::find($Product->category_id);

            $Ip = HegzaIp::where('type', 'pu-ip-sabet-user')->where('category_id', $Country->id)->where('status', 0)->first();

            if (!is_null($Ip)) {

                $ProductDetail = json_decode($Product->detail, true);
                $Day = $ProductDetail['day'];
                $ExpireTime = Carbon::now()->addDays($Day);
                $Vol = $Order->total_bandwidth;

                $Ip->expire_date = $ExpireTime;
                $Ip->volume = $Vol;
                $Ip->user_id = $Order->user_id;
                $Ip->product_id = $Product->id;
                $Ip->status = 1;
                $Ip->order_id = $Order->id;
                $Ip->from = 'bot';
                $Ip->save();


                $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                $timeInMilliseconds = $date->valueOf();
                $Volume = $Vol * 1073741824;
                $IpDetail = json_decode($Ip->detail, true);
                $ServerIp = HegzaIp::find($Ip->parent_id);
                $ServerIp = json_decode($ServerIp->detail, true);

                $Data['time'] = $timeInMilliseconds;
                $Data['hajm'] = $Volume;
                $Data['ip_address'] = $ServerIp['ip_address'];
                $Data['ip_port'] = $ServerIp['ip_port'];
                $Data['Session'] = $ServerIp['session'];
                $Data['uid'] = $Ip->UID;
                $Data['name'] = $IpDetail['user_remark'];
                $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
                $Data['Data'] = $ServerIp;
                $Log = updateSanaiePanel($Data);

                $Status = 2;
            } else {
                $Status = 1;
            }


            $Order->status = $Status;
            $Order->save();

            $price = number_format($Payment->price) . ' تومان ';
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
#خرید_آیپی_ثابت
";

            SendTelegramMessage($Message, -4157397982);
            $ref = $Payment->ref_id;
            $order_id = $Payment->order_id;
            if ($Payment->user_id == 0) {

                $Data['orderId'] = $Order->id;
                $Data['refId'] = $response['data']['ref_id'];
                $hashId = Encode($Order->id);
                $Data['Url'] = url("/api/hegza/show-detail/$hashId");
                $Status = 1;
                return view('bot.callback', compact('Status', 'Data'));

            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅ خرید حجم با موفقیت انجام شد.

🔰 کد رهگیری: $ref
❇️شماره سفارش: $order_id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
                return redirect('https://t.me/HegzaVpnBot');
            }

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
        }
    }

    /* تحویل آی پی */
    public function IpSabetCallBackV2($Order, $response, $Payment, $request)
    {
        try {
            $messageId = $request->input('m_id');

            $user = HegzaUser::find($Order->user_id);
            $chatId = 0;
            if (!is_null($user)) {
                $chatId = $user->user_id;
            }

            $Product = HegzaProduct::find($Order->service_id);
            $Country = HegzaCountry::find($Product->category_id);

            $Servers = HegzaIp::where('type', 'pu-ip-sabet')->where('category_id', $Country->id)->where('status', 1)->get();

            if (!is_null($Servers)) {
                $server = null;
                foreach ($Servers as $item){
                    $users = HegzaIp::where('parent_id',$item)->where('status',1)->count();
                    if ($item->ip_limit >= $users){
                        $server = $item;
                        break;
                    }
                }

                if (!is_null($server)){
                    $ip_detail = json_decode($server->detail,true);
                    $Data = loginToSanaeiAccount($ip_detail);
                    if ($Data['login'] == true) {
                        $ip_detail['session'] = $Data['sessionCookie'];
                        $Ip = HegzaIp::find($server->id);
                        $Ip->detail = json_encode($ip_detail);
                        $Ip->save();


                        $ProductDetail = json_decode($Product->detail, true);
                        $Day = $ProductDetail['day'];
                        $ExpireTime = Carbon::now()->addDays($Day);
                        $Vol = $Order->total_bandwidth;


                        $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                        $timeInMilliseconds = $date->valueOf();
                        $Volume = $Vol * 1073741824;
                        $name = $ip_detail['ip_name'].'-'.$Order->id;

                        $items = [
                            'Session' => $Data['sessionCookie'],
                            'apiUrl' => $ip_detail['ip_api_domain'],
                            'serverUrl' => $ip_detail['ip_address'],
                            'inboundId' => $ip_detail['ip_inbound'],
                            'inboundPort' => $ip_detail['port_inbound'],
                            'name' => "$name",
                            'code' => $ip_detail['code'],
                            'type' => $ip_detail['type'],
                            'hajm' => $Volume,
                            'time' => $timeInMilliseconds,
                            'method' => 'new',
                        ];
                        $data = addClientToSanaie($items);
                        if ($data['status'] == 1) {

                            $UserIp_detail = [
                                'user_port' => $ip_detail['ip_port'],
                                'user_remark' => $name,
                                'user_code' => $data['msg'],
                            ];
                            $UserIp = new HegzaIp();
                            $UserIp->type = 'pu-ip-sabet-user';
                            $UserIp->detail = json_encode($UserIp_detail);
                            $UserIp->parent_id = $Ip->id;
                            $UserIp->UID = $data['UID'];
                            $UserIp->category_id = $Country->id;
                            $UserIp->expire_date = $ExpireTime;
                            $UserIp->volume = $Vol;
                            $UserIp->user_id = $Order->user_id;
                            $UserIp->product_id = $Product->id;
                            $UserIp->order_id = $Order->id;
                            $UserIp->from = 'bot';
                            $UserIp->status = 1;
                            $UserIp->save();

                            $Status = 2;
                        } else {
                            $Status = 1;
                        }
                    } else {
                        $Status = 1;
                    }
                }else{
                    $Status = 1;
                }
            } else {
                $Status = 1;
            }
            $Order->status = $Status;
            $Order->save();

            $price = number_format($Payment->price) . ' تومان ';
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
#خرید_آیپی_ثابت
";

            SendTelegramMessage($Message, -4157397982);
            $ref = $Payment->ref_id;
            $order_id = $Payment->order_id;
            if ($Payment->user_id == 0) {

                $Data['orderId'] = $Order->id;
                $Data['refId'] = $response['data']['ref_id'];
                $hashId = Encode($Order->id);
                $Data['Url'] = url("/api/hegza/show-detail/$hashId");
                $Status = 1;
                return view('bot.callback', compact('Status', 'Data'));

            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅ خرید حجم با موفقیت انجام شد.

🔰 کد رهگیری: $ref
❇️شماره سفارش: $order_id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
                return redirect('https://t.me/HegzaVpnBot');
            }

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
        }
    }

    /* تمدید آی پی */
    public function renewOrderCallback($Order, $response, $Payment, $request)
    {
        $User = HegzaUser::find($Payment->user_id);

        $messageId = $request->input('m_id');
        $chatId = $User->user_id;
        $ref = $Payment->ref_id;

        $Ip = HegzaIp::where('order_id', $Payment->order_id)->first();
        $ServerIp = HegzaIp::find($Ip->parent_id);
        $ServerIp = json_decode($ServerIp->detail, true);

        $Service = HegzaProduct::find($Payment->help);
        $ProductDetail = json_decode($Service->detail, true);
        $Day = $ProductDetail['day'];

        $ExpireTime = Carbon::parse($Ip->expire_date)->addDays($Day);
        $date = \Illuminate\Support\Carbon::parse($ExpireTime);
        $timeInMilliseconds = $date->valueOf();

        $Order = HegzaOrder::find($Payment->order_id);
        $Order->total_bandwidth = $Order->total_bandwidth + $ProductDetail['volume'];
        $Order->status = 1;
        $Order->save();

        $Vol = $Order->total_bandwidth;
        $Volume = $Vol * 1073741824;

        $IpDetail = json_decode($Ip->detail, true);

        $Data['time'] = $timeInMilliseconds;
        $Data['hajm'] = $Volume;
        $Data['ip_address'] = $ServerIp['ip_address'];
        $Data['ip_port'] = $ServerIp['ip_port'];
        $Data['Session'] = $ServerIp['session'];
        $Data['uid'] = $Ip->UID;
        $Data['name'] = $IpDetail['user_remark'];
        $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
        $Data['Data'] = $ServerIp;

        updateSanaiePanel($Data);

        $Ip->expire_date = $ExpireTime;
        $Ip->volume = $Vol;
        $Ip->product_id = $Service->id;
        $Ip->status = 1;
        $Ip->save();


        $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
        $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "✅تمدید آی پی با موفقیت انجام شد..

🔰 کد رهگیری: $ref
❇️شماره سفارش: $Order->id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        EditTelegramMessageForCustomBot($data, $token);

        $price = number_format($Payment->price) . ' تومان ';
        $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: تمدید
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
        SendTelegramMessage($Message, -4157397982);

        return redirect('https://t.me/HegzaVpnBot');

    }

    /* خرید حجم */
    public function chargeBandwidthCallback($Order, $response, $Payment, $request)
    {
        $User = HegzaUser::find($Payment->user_id);

        $messageId = $request->input('m_id');
        $chatId = $User->user_id;
        $ref = $Payment->ref_id;

        $Order = HegzaOrder::find($Payment->order_id);
        $Order->total_bandwidth = $Order->total_bandwidth + $Payment->help;
        $Order->save();

        $Volume = $Order->total_bandwidth;

        $Ip = HegzaIp::where('order_id', $Payment->order_id)->first();
        $ServerIp = HegzaIp::find($Ip->parent_id);
        $ServerIp = json_decode($ServerIp->detail, true);

        $date = \Illuminate\Support\Carbon::parse($Ip->expire_date);
        $timeInMilliseconds = $date->valueOf();

        $IpDetail = json_decode($Ip->detail, true);

        $Volume = $Volume * 1073741824;

        $Data['time'] = $timeInMilliseconds;
        $Data['hajm'] = $Volume;
        $Data['ip_address'] = $ServerIp['ip_address'];
        $Data['ip_port'] = $ServerIp['ip_port'];
        $Data['Session'] = $ServerIp['session'];
        $Data['uid'] = $Ip->UID;
        $Data['name'] = $IpDetail['user_remark'];
        $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
        $Data['Data'] = $ServerIp;
        updateSanaiePanel($Data);

        $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
        $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "✅ حجم با موفقیت خریداری شد.

🔰 کد رهگیری: $ref
❇️شماره سفارش: $Order->id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
            'reply_markup' => json_encode($keyboard),
            'parse_mode' => 'HTML'
        ];
        $token = config('services.telegram.hegza_token');
        EditTelegramMessageForCustomBot($data, $token);

        $price = number_format($Payment->price) . ' تومان ';
        $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: خرید حجم
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
        SendTelegramMessage($Message, -4157397982);

        return redirect('https://t.me/HegzaVpnBot');

    }

    /* مشاهده جزئیات */
    public function showDetail($id)
    {
        $decode = Decode($id);
        if ($decode != 'error') {
            $Ip = HegzaIp::where('order_id', $decode)->first();
            $jdf = new Jdf();
            return view('bot.single', compact('Ip', 'jdf'));
        } else {
            return redirect('https://t.me/HegzaVpnBot');
        }
    }

    /*  */
    public function syncOrder(Request $request)
    {
        $chat = $request->input('chatId');
        $user = HegzaUser::where('user_id', $request->input('chatId'))->first();
        $type = true;
        if (is_null($user)) {
            $user = new HegzaUser();
            $user->user_id = $chat;
            $user->username = $request->input('username');
            $user->name = $request->input('username');
            $user->path = 'callback_query';
            $user->wallet = 10000;
            $user->ref = 'hg-' . $chat;
            $user->parent = 0;
            $user->save();

            $type = false;
        }

        $decode = Decode($request->input('id'));
        if ($decode != 'error') {
            $Ip = HegzaIp::find($decode);

            if ($Ip->user_id == 0 || $Ip->user_id == $user->id) {
                $Ip->user_id = $user->id;
                $Ip->save();

                $Order = HegzaOrder::find($Ip->order_id);
                $Order->user_id = $user->id;
                $Order->save();

                if (!$type) {
                    $parent = HegzaUser::where('user_id', $Order->help)->first();
                    if (!is_null($parent)) {
                        $user->parent = $parent->id;
                        $user->save();
                    }
                }

                $Payment = HegzaPayment::where('order_id', $Ip->order_id)->first();
                $Payment->user_id = $user->id;
                $Payment->save();

                $keyboard['inline_keyboard'][] = [['text' => 'سفارشات', 'callback_data' => 'type=order-list']];
                $keyboard['inline_keyboard'][] = [['text' => '📱 منو', 'callback_data' => 'type=home']];

                $data = [
                    'chat_id' => $request->input('chatId'),
                    'text' => "عملیات همگام سازی با موفقیت انجام شد.",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                return SendTelegramMessageForCustomBot($data, $token);
            } else {
                $keyboard['inline_keyboard'][] = [['text' => '📱 منو', 'callback_data' => 'type=home']];
                $data = [
                    'chat_id' => $request->input('chatId'),
                    'text' => "این سفارش متعلق به شما نمی باشد.",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                return SendTelegramMessageForCustomBot($data, $token);
            }
        } else {
            $keyboard['inline_keyboard'][] = [['text' => '📱 منو', 'callback_data' => 'type=home']];
            $data = [
                'chat_id' => $request->input('chatId'),
                'text' => "شماره سفارش نامعتبر می باشد.",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            $token = config('services.telegram.hegza_token');
            return SendTelegramMessageForCustomBot($data, $token);
        }

    }

    /*  */
    public function buyOrderWithWallet(Request $request)
    {
        $Payment = HegzaPayment::find($request->input('id'));
        $Order = HegzaOrder::find($Payment->order_id);
        $user = HegzaUser::find($Order->user_id);
        $messageId = $request->input('message_id');
        $chatId = $user->user_id;
        try {
            if ($user->wallet >= $Payment->price) {

                $Product = HegzaProduct::find($Order->service_id);
                $Country = HegzaCountry::find($Product->category_id);

                $Ip = HegzaIp::where('type', 'pu-ip-sabet-user')->where('category_id', $Country->id)->where('status', 0)->first();

                if (!is_null($Ip)) {

                    $ProductDetail = json_decode($Product->detail, true);
                    $Day = $ProductDetail['day'];
                    $ExpireTime = Carbon::now()->addDays($Day);
                    $Vol = $Order->total_bandwidth;

                    $Ip->expire_date = $ExpireTime;
                    $Ip->volume = $Vol;
                    $Ip->user_id = $Order->user_id;
                    $Ip->product_id = $Product->id;
                    $Ip->status = 1;
                    $Ip->order_id = $Order->id;
                    $Ip->from = 'bot';
                    $Ip->save();

                    $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                    $timeInMilliseconds = $date->valueOf();
                    $Volume = $Vol * 1073741824;
                    $IpDetail = json_decode($Ip->detail, true);
                    $ServerIp = HegzaIp::find($Ip->parent_id);
                    $ServerIp = json_decode($ServerIp->detail, true);

                    $Data['time'] = $timeInMilliseconds;
                    $Data['hajm'] = $Volume;
                    $Data['ip_address'] = $ServerIp['ip_address'];
                    $Data['ip_port'] = $ServerIp['ip_port'];
                    $Data['Session'] = $ServerIp['session'];
                    $Data['uid'] = $Ip->UID;
                    $Data['name'] = $IpDetail['user_remark'];
                    $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
                    $Data['Data'] = $ServerIp;
                    $Log = updateSanaiePanel($Data);
                    $Status = 2;
                } else {
                    $Status = 1;
                }

                $Order->status = $Status;
                $Order->save();

                $Payment->method = 'wallet';
                $Payment->status = 1;
                $Payment->paid_at = Carbon::now();
                $Payment->save();

                $user->wallet = $user->wallet - $Payment->price;
                $user->save();

                $price = number_format($Payment->price) . ' تومان ';
                $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
                SendTelegramMessage($Message, -4157397982);
                $ref = $Payment->ref_id;
                $order_id = $Payment->order_id;

                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅ خرید حجم با موفقیت انجام شد.

🔰 کد رهگیری: $ref
❇️شماره سفارش: $order_id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "موجودی کیف پول شما کمتر از حد مجاز می باشد",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            }

        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;

        }
    }

    /*  */
    public function buyOrderWithWalletV2(Request $request)
    {
        $Payment = HegzaPayment::find($request->input('id'));
        $Order = HegzaOrder::find($Payment->order_id);
        $user = HegzaUser::find($Order->user_id);
        $messageId = $request->input('message_id');
        $chatId = $user->user_id;
        try {
            if ($user->wallet >= $Payment->price) {

                $Product = HegzaProduct::find($Order->service_id);
                $Country = HegzaCountry::find($Product->category_id);

                $Servers = HegzaIp::where('type', 'pu-ip-sabet')->where('category_id', $Country->id)->where('status', 1)->get();

                if (!is_null($Servers)) {
                    $server = null;
                    foreach ($Servers as $item){
                        $users = HegzaIp::where('parent_id',$item)->where('status',1)->count();
                        if ($item->ip_limit >= $users){
                            $server = $item;
                            break;
                        }
                    }

                    if (!is_null($server)){
                        $ip_detail = json_decode($server->detail,true);
                        $Data = loginToSanaeiAccount($ip_detail);
                        if ($Data['login'] == true) {
                            $ip_detail['session'] = $Data['sessionCookie'];
                            $Ip = HegzaIp::find($server->id);
                            $Ip->detail = json_encode($ip_detail);
                            $Ip->save();


                            $ProductDetail = json_decode($Product->detail, true);
                            $Day = $ProductDetail['day'];
                            $ExpireTime = Carbon::now()->addDays($Day);
                            $Vol = $Order->total_bandwidth;


                            $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                            $timeInMilliseconds = $date->valueOf();
                            $Volume = $Vol * 1073741824;

                            $name = $ip_detail['ip_name'];

                            $items = [
                                'Session' => $Data['sessionCookie'],
                                'apiUrl' => $ip_detail['ip_api_domain'],
                                'serverUrl' => $ip_detail['ip_address'],
                                'inboundId' => $ip_detail['ip_inbound'],
                                'inboundPort' => $ip_detail['port_inbound'],
                                'name' => "$name-$Order->id",
                                'code' => $ip_detail['code'],
                                'type' => $ip_detail['type'],
                                'hajm' => $Volume,
                                'time' => $timeInMilliseconds,
                                'method' => 'new',
                            ];
                            $data = addClientToSanaie($items);
                            if ($data['status'] == 1) {

                                $UserIp_detail = [
                                    'user_port' => $ip_detail['ip_port'],
                                    'user_remark' => "order-$Order->id",
                                    'user_code' => $data['msg'],
                                ];
                                $UserIp = new HegzaIp();
                                $UserIp->type = 'pu-ip-sabet-user';
                                $UserIp->detail = json_encode($UserIp_detail);
                                $UserIp->parent_id = $Ip->id;
                                $UserIp->UID = $data['UID'];
                                $UserIp->category_id = $Country->id;
                                $UserIp->expire_date = $ExpireTime;
                                $UserIp->volume = $Vol;
                                $UserIp->user_id = $Order->user_id;
                                $UserIp->product_id = $Product->id;
                                $UserIp->order_id = $Order->id;
                                $UserIp->from = 'bot';
                                $UserIp->status = 1;
                                $UserIp->save();

                                $Status = 2;
                            } else {
                                $Status = 1;
                            }
                        } else {
                            $Status = 1;
                        }
                    }else{
                        $Status = 1;
                    }
                } else {
                    $Status = 1;
                }

                $Order->status = $Status;
                $Order->save();

                $Payment->method = 'wallet';
                $Payment->status = 1;
                $Payment->paid_at = Carbon::now();
                $Payment->save();

                $user->wallet = $user->wallet - $Payment->price;
                $user->save();

                $price = number_format($Payment->price) . ' تومان ';
                $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
                SendTelegramMessage($Message, -4157397982);
                $ref = $Payment->ref_id;
                $order_id = $Payment->order_id;

                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅ خرید حجم با موفقیت انجام شد.

🔰 کد رهگیری: $ref
❇️شماره سفارش: $order_id
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "موجودی کیف پول شما کمتر از حد مجاز می باشد",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            }

        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;

        }
    }

    /* اعلام برای اتمام زمان ای پی*/
    public function checkSuspendedOrders()
    {
        $ThreeDays = Carbon::today()->addDays(3)->format('Y-m-d');

        $HegzaIp = HegzaIp::whereDate('expire_date', '<=', $ThreeDays)
            ->where('status', 1)
            ->where('sms', 0)
            ->orderby('id', 'ASC')
            ->get();
        $token = config('services.telegram.hegza_token');

        foreach ($HegzaIp as $value) {
            $Expire = \Illuminate\Support\Carbon::parse($value->expire_at)->format('Y-m-d H');
            if ($value->sms == 0) {
                try {
                    $order = HegzaOrder::find($value->order_id);
                    $user = HegzaUser::find($value->user_id);

                    $keyboard['inline_keyboard'][][] = ['text' => 'جزئیات سفارش', 'callback_data' => "type=show-order|id=$order->id"];
                    $keyboard['inline_keyboard'][][] = ['text' => 'منو', 'callback_data' => 'type=home'];

                    $Message = "سفارش شماره $value->order_id تا 72 ساعت دیگر منقضی خواهد شد. چنانچه از سرویس رضایت داشتید، جهت تمدید سرویس از جزئیات سفارش اقدام نمایید.";

                    $value->sms = 1;
                    $value->save();

                    $data = [
                        'chat_id' => intval($user->user_id),
                        'text' => "$Message",
                        'reply_markup' => json_encode($keyboard),
                        'parse_mode' => 'HTML'
                    ];

                    return SendTelegramMessageForCustomBot($data, $token);


                } catch (\Exception $exception) {

                }
            }
        }
        dd($HegzaIp);

    }

    /* اعلام منقضی شدن ای پی */
    public function checkFailedOrders()
    {
        $Today = Carbon::today()->format('Y-m-d');
        $Today2 = Carbon::now()->format('Y-m-d H:i');

        $HegzaIp = HegzaIp::whereDate('expire_date', '<=', $Today)
            ->wherein('status', [1, -2])
            ->where('sms', 1)
            ->orderby('id', 'ASC')
            ->get();
        $token = config('services.telegram.hegza_token');

        foreach ($HegzaIp as $value) {
            $keyboard['inline_keyboard'] = null;
            $Expire = \Illuminate\Support\Carbon::parse($value->expire_date)->format('Y-m-d H');
            if ($value->status == 1) {
                if ($Expire <= $Today2) {
                    $User = HegzaUser::find($value->user_id);

                    $Order = HegzaOrder::find($value->order_id);
                    $Order->status = -2;
                    $Order->save();

                    $Ip = HegzaIp::find($value->id);
                    $Ip->status = -2;
                    $Ip->save();

                    $keyboard['inline_keyboard'][][] = ['text' => 'جزئیات سفارش', 'callback_data' => "type=show-order|id=$Order->id"];
                    $keyboard['inline_keyboard'][][] = ['text' => 'منو', 'callback_data' => 'type=buy-ip'];

                    $Message = "سفارش $value->order_id بعلت عدم تمدید به حالت معلق درآمده. جهت تمدید همین آی پی میتوانید از طریق جزئیات سفارش اقدام نمایید.";

                    $data = [
                        'chat_id' => $User->user_id,
                        'text' => "$Message",
                        'reply_markup' => json_encode($keyboard),
                        'parse_mode' => 'HTML'
                    ];
                    SendTelegramMessageForCustomBot($data, $token);
                }
            }
            elseif ($value->status == -2) {
                if (Carbon::parse($value->expire_date)->addDays(1)->format('Y-m-d H') <= $Today2) {
                    $User = HegzaUser::find($value->user_id);

                    $Order = HegzaOrder::find($value->order_id);
                    $Order->status = -1;
                    $Order->save();

                    $Ip = HegzaIp::find($value->id);
                    $Ip->status = -1;
                    $Ip->save();

                    $keyboard['inline_keyboard'][][] = ['text' => 'جزئیات سفارش', 'callback_data' => "type=show-order|id=$Order->id"];
                    $keyboard['inline_keyboard'][][] = ['text' => 'منو', 'callback_data' => 'type=home'];

                    $Message = "سفارش $value->order_id بعلت عدم تمدید لغو شد. ";

                    $data = [
                        'chat_id' => $User->user_id,
                        'text' => "$Message",
                        'reply_markup' => json_encode($keyboard),
                        'parse_mode' => 'HTML'
                    ];
                    SendTelegramMessageForCustomBot($data, $token);
                }
            }
        }

    }

    /**/
    public function Home(Request $request)
    {
        $setting = HegzaSetting::find(3);
        $menuText = json_decode($setting->value, true);
        if (array_key_exists('menu', $menuText) && !is_null($menuText) && $menuText['menu'] != null && $menuText['menu'] != ' ') {
            $text = $menuText['menu'];
        } else {
            $text = 'سلام دوست عزیزم ✌️
به ربات خوش اومدید ❤️
میتونید یک ای پی تست از ما بگیرید اگر دوسداشتید خرید کنید 🤓
قیمت ها به تومان هستش 💰
';
        }
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $token = config('services.telegram.hegza_token');

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
                    ['text' => 'جستجو کد', 'callback_data' => 'type=search-by-code-step-one|'],
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
        if ($request->input('type') == 'send') {
            return SendTelegramMessageForCustomBot($data, $token);
        } else {
            return EditTelegramMessageForCustomBot($data, $token);
        }
    }

    /**/
    public function cartBeCart(Request $request)
    {
        $setting = HegzaSetting::find(1);
        $detail = json_decode($setting->value, true);
        if ($detail['random'] == 1) {
            $count = count($detail['cart']) - 1;
            $rand = rand(0, $count);
            $card = $detail['cart'][$rand]['card'];
        } else {
            foreach ($detail['cart'] as $item) {
                if ($item['status'] == 1) {
                    $card = $item['card'];
                    break;
                }
            }
        }
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $paymentId = $request->input('id');

        $token = config('services.telegram.hegza_token');

        $payment = HegzaPayment::find($paymentId);
        $payments = HegzaPayment::where('user_id', $payment->user_id)->where('status', -3)->update(['status' => -1]);

        $payment->gate = 'ZarinPal';
        $payment->status = -3;
        $payment->card = null;
        $payment->paid_at = Carbon::now();
        $payment->save();

        $Price = number_format($payment->price);

        $text = "لطفا مبلغ $Price را به شماره کارت
        $card
         واریز کرده و عکس رسید را ارسال کنید
        نکته: گزینه برگشت یا منو را تا قبل از ارسال رسید فشار ندهید
        ";

        $user = HegzaUser::where('user_id', $chatId)->first();
        $user->path = 'cart-be-cart';
        $user->save();
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        return EditTelegramMessageForCustomBot($data, $token);
    }

    /**/
    public function sentDocumentForCartBeCart(Request $request)
    {

        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $User = HegzaUser::where('user_id', $chatId)->first();
        $payment = HegzaPayment::where('user_id', $User->id)->where('status', -3)->first();


        $token = config('services.telegram.hegza_token');

        $data = [
            'chat_id' => -4157397982,
            'from_chat_id' => $chatId,
            'message_id' => $messageId,
            'parse_mode' => 'HTML'
        ];
        ForwardTelegramMessageForCustomBot($data, $token);

        $data = [
            'chat_id' => $chatId,
            'text' => 'درخواست شما با موفقیت ثبت شد. لطفا صبور باشید.',
            'parse_mode' => 'HTML'
        ];
        SendTelegramMessageForCustomBot($data, $token);


        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❌ خیر', 'callback_data' => "type=cart-be-cart-setting|answer=no|id=$payment->id"],
                    ['text' => '✅ بله', 'callback_data' => "type=cart-be-cart-setting|answer=yes|id=$payment->id"],
                ], [
                    ['text' => 'شارژ کیف پول', 'callback_data' => "type=cart-be-cart-setting|answer=charge|id=$payment->id"],
                ],

            ]
        ];
        $price = number_format($payment->price) . ' تومان ';
        $type = __('payments.type.' . $payment->type);
        $text = "رسید تراکنش شماره $payment->id
شماره سفارش:$payment->order_id
نوع تراکنش:$type
مبلغ تراکنش:$price
آیا تراکنش با اطلاعات فوق تایید است؟
";
        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => -4157397982,
            'text' => $text,
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        SendTelegramMessageForCustomBot($data, $token);
    }

    /**/
    public function confirmCartBeCart(Request $request)
    {
        $token = config('services.telegram.hegza_token');
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $answer = $request->input('answer');
        $id = $request->input('id');
        $from = $request->input('from');

        $fullName = $from['name'] . ' ' . $from['last'];
        $username = $from['username'];

        $payment = HegzaPayment::find($id);
        $User = HegzaUser::find($payment->user_id);

        if ($answer == 'yes') {

            $Order = HegzaOrder::find($payment->order_id);
            $Product = HegzaProduct::find($Order->service_id);
            $Country = HegzaCountry::find($Product->category_id);

            $Servers = HegzaIp::where('type', 'pu-ip-sabet')->where('category_id', $Country->id)->where('status', 1)->get();

            if (!is_null($Servers)) {
                $server = null;
                foreach ($Servers as $item){
                    $users = HegzaIp::where('parent_id',$item)->where('status',1)->count();
                    if ($item->ip_limit >= $users){
                        $server = $item;
                        break;
                    }
                }

                if (!is_null($server)){
                    $ip_detail = json_decode($server->detail,true);
                    $Data = loginToSanaeiAccount($ip_detail);
                    if ($Data['login'] == true) {
                        $ip_detail['session'] = $Data['sessionCookie'];
                        $Ip = HegzaIp::find($server->id);
                        $Ip->detail = json_encode($ip_detail);
                        $Ip->save();


                        $ProductDetail = json_decode($Product->detail, true);
                        $Day = $ProductDetail['day'];
                        $ExpireTime = Carbon::now()->addDays($Day);
                        $Vol = $Order->total_bandwidth;


                        $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                        $timeInMilliseconds = $date->valueOf();
                        $Volume = $Vol * 1073741824;

                        $name = $ip_detail['ip_name'];

                        $items = [
                            'Session' => $Data['sessionCookie'],
                            'apiUrl' => $ip_detail['ip_api_domain'],
                            'serverUrl' => $ip_detail['ip_address'],
                            'inboundId' => $ip_detail['ip_inbound'],
                            'inboundPort' => $ip_detail['port_inbound'],
                            'name' => "$name-$Order->id",
                            'code' => $ip_detail['code'],
                            'type' => $ip_detail['type'],
                            'hajm' => $Volume,
                            'time' => $timeInMilliseconds,
                            'method' => 'new',
                        ];
                        $data = addClientToSanaie($items);
                        if ($data['status'] == 1) {

                            $UserIp_detail = [
                                'user_port' => $ip_detail['ip_port'],
                                'user_remark' => "order-$Order->id",
                                'user_code' => $data['msg'],
                            ];
                            $UserIp = new HegzaIp();
                            $UserIp->type = 'pu-ip-sabet-user';
                            $UserIp->detail = json_encode($UserIp_detail);
                            $UserIp->parent_id = $Ip->id;
                            $UserIp->UID = $data['UID'];
                            $UserIp->category_id = $Country->id;
                            $UserIp->expire_date = $ExpireTime;
                            $UserIp->volume = $Vol;
                            $UserIp->user_id = $Order->user_id;
                            $UserIp->product_id = $Product->id;
                            $UserIp->order_id = $Order->id;
                            $UserIp->from = 'bot';
                            $UserIp->status = 1;
                            $UserIp->save();

                            $Status = 2;
                        } else {
                            $Status = 1;
                        }
                    } else {
                        $Status = 1;
                    }
                }else{
                    $Status = 1;
                }
            } else {
                $Status = 1;
            }

            $Order->status = $Status;
            $Order->save();

            $payment->method = 'cart-be-cart';
            $payment->status = 1;
            $payment->paid_at = Carbon::now();
            $payment->save();

            $price = number_format($payment->price);

            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: کارت به کارت
💵 مبلغ:$price
تایید شده توسط: $fullName
@$username
";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $Message,
                'parse_mode' => 'HTML'
            ];
            EditTelegramMessageForCustomBot($data, $token);

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => ' جزئیات سفارش', 'callback_data' => "type=show-order|id=$Order->id"],
                    ], [
                        ['text' => 'برگشت', 'callback_data' => "type=home"],
                    ],
                ]
            ];

            $data = [
                'chat_id' => $User->user_id,
                'text' => 'سفارش شماره با موفقیت تایید شد برای مشاهده جزئیات میتوانید بر روی دکمه زیر کلیک کنید',
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);


        } elseif ($answer == 'no') {

            $Message = " عدم تایید تراکنش
شماره تراکنش: $payment->id
تایید شده توسط: $fullName
@$username";
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $Message,
                'parse_mode' => 'HTML'
            ];
            EditTelegramMessageForCustomBot($data, $token);

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => 'پشتیبانی', 'callback_data' => "#"],
                    ], [
                        ['text' => 'برگشت', 'callback_data' => "type=home"],
                    ],

                ]
            ];

            $data = [
                'chat_id' => $User->user_id,
                'text' => 'درخواست کارت به کارت شما تایید نشد. لطفا از طریق پشتیبانی بررسی کنید.',
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);

        } elseif ($answer == 'charge') {

            $User->wallet = $User->wallet + $payment->price;
            $User->save();

            $Message = "شارژ کیف پول کاربر:
شماره تراکنش:$payment->id
تایید شده توسط: $fullName
@$username";

            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $Message,
                'parse_mode' => 'HTML'
            ];
            EditTelegramMessageForCustomBot($data, $token);

            $keyboard = [
                'inline_keyboard' => [
                    [
                        ['text' => 'ثبت سفارش', 'callback_data' => "type=buy"],
                    ], [
                        ['text' => 'منو', 'callback_data' => "type=home"],
                    ],

                ]
            ];

            $data = [
                'chat_id' => $User->user_id,
                'text' => "مبلغ $payment->price بابت تراکنش شماره $payment->id به کیف پول شما واریز شد",
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            return SendTelegramMessageForCustomBot($data, $token);

        }
    }

    /**/
    public function generateUserIp($country, $orderId, $userId)
    {
        $Servers = HegzaIp::where('type', 'pu-ip-sabet')
            ->where('category_id', $country)
            ->get();

        foreach ($Servers as $server) {
            $checkLimit = json_decode($server->detail, true);
            $getUser = HegzaIp::where('parent_id', $server->id)->where('status', 1)->count();
            if ($checkLimit['ip_limit'] > $getUser) {

                $Name = 'order-' . $orderId;
                $items = [
                    'Session' => $checkLimit['session'],
                    'api_domain' => $checkLimit['ip_api_domain'],
                    'ip_address' => $checkLimit['ip_address'],
                    'ip_port' => $checkLimit['ip_port'],
                    'ip_inbound' => $checkLimit['ip_inbound'],
                    'port_inbound' => $checkLimit['port_inbound'],
                    'name' => $Name,
                    'hajm' => 0,
                    'zaman' => 0,
                    'type' => 0,
                ];
                $data = addClientToSanaieOnline($items);

                if ($data['status'] == 1) {

                    $UserIp_detail = [
                        'user_port' => $checkLimit['ip_port'],
                        'user_remark' => $Name,
                        'user_code' => $data['msg'],
                    ];
                    $UserIp = new HegzaIp();
                    $UserIp->status = 1;
                    $UserIp->type = 'pu-ip-sabet-user';
                    $UserIp->detail = json_encode($UserIp_detail);
                    $UserIp->parent_id = $server->id;
                    $UserIp->order_id = $orderId;
                    $UserIp->user_id = $userId;
                    $UserIp->UID = $data['UID'];
                    $UserIp->category_id = $country;
                    $UserIp->save();

                } else {
                    $status = 'pending';
                }
                die();
            }
        }
    }

    /**/
    public function rules(Request $request)
    {
        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $token = config('services.telegram.hegza_token');
        $setting = HegzaSetting::find(5);

        $Messages = null;
        foreach (json_decode($setting->value, true) as $item) {
            $Messages .= "<p>$item</p>";
        }

        $keyboard['inline_keyboard'][][] = ['text' => '🔙 بازگشت', 'callback_data' => 'type=home'];

        $encodedKeyboard = json_encode($keyboard);
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => "$Messages",
            'reply_markup' => $encodedKeyboard,
            'parse_mode' => 'HTML'
        ];
        SendTelegramMessageForCustomBot($data, $token);
    }

    /**/
    public function plisioFunction($Data)
    {
        $amount = $Data['amount'];
        $paymentId = $Data['paymentId'];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.plisio.net/api/v1/invoices/new?source_amount=$amount&order_number=$paymentId&order_name=payment-$paymentId&callback_url=http://test.com/callback&api_key=" . config('services.plisio.key') . "&source_currency=USD",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: lang=en'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }


    public function buyVolumeWallet(Request $request)
    {
        $Payment = HegzaPayment::find($request->input('id'));
        $Order = HegzaOrder::find($Payment->order_id);
        $user = HegzaUser::find($Order->user_id);
        $messageId = $request->input('message_id');
        $chatId = $user->user_id;
        try {
            if ($user->wallet >= $Payment->price) {

                $user->wallet = $user->wallet - $Payment->price;
                $user->save();

                $Order = HegzaOrder::find($Payment->order_id);
                $Order->total_bandwidth = $Order->total_bandwidth + $Payment->help;
                $Order->save();

                $Volume = $Order->total_bandwidth;

                $Ip = HegzaIp::where('order_id', $Payment->order_id)->first();
                $ServerIp = HegzaIp::find($Ip->parent_id);
                $ServerIp = json_decode($ServerIp->detail, true);

                $date = \Illuminate\Support\Carbon::parse($Ip->expire_date);
                $timeInMilliseconds = $date->valueOf();

                $IpDetail = json_decode($Ip->detail, true);

                $Volume = $Volume * 1073741824;

                $Data['time'] = $timeInMilliseconds;
                $Data['hajm'] = $Volume;
                $Data['ip_address'] = $ServerIp['ip_address'];
                $Data['ip_port'] = $ServerIp['ip_port'];
                $Data['Session'] = $ServerIp['session'];
                $Data['uid'] = $Ip->UID;
                $Data['name'] = $IpDetail['user_remark'];
                $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
                $Data['Data'] = $ServerIp;
                updateSanaiePanel($Data);

                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅ حجم با موفقیت خریداری شد.

🔰پرداخت با کیف پول
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);

                $price = number_format($Payment->price) . ' تومان ';
                $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: خرید حجم
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
                SendTelegramMessage($Message, -4157397982);

            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "موجودی کیف پول شما کمتر از حد مجاز می باشد",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            }

        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;

        }

    }


    public function buyRenewWithWallet(Request $request)
    {
        $Payment = HegzaPayment::find($request->input('id'));
        $Order = HegzaOrder::find($Payment->order_id);
        $user = HegzaUser::find($Order->user_id);
        $messageId = $request->input('message_id');
        $chatId = $user->user_id;
        try {
            if ($user->wallet >= $Payment->price) {

                $user->wallet = $user->wallet - $Payment->price;
                $user->save();

                $Ip = HegzaIp::where('order_id', $Payment->order_id)->first();
                $ServerIp = HegzaIp::find($Ip->parent_id);
                $ServerIp = json_decode($ServerIp->detail, true);

                $Service = HegzaProduct::find($Payment->help);
                $ProductDetail = json_decode($Service->detail, true);
                $Day = $ProductDetail['day'];

                $ExpireTime = Carbon::parse($Ip->expire_date)->addDays($Day);
                $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                $timeInMilliseconds = $date->valueOf();

                $Order = HegzaOrder::find($Payment->order_id);
                $Order->total_bandwidth = $Order->total_bandwidth + $ProductDetail['volume'];
                $Order->status = 1;
                $Order->save();

                $Vol = $Order->total_bandwidth;
                $Volume = $Vol * 1073741824;

                $IpDetail = json_decode($Ip->detail, true);

                $Data['time'] = $timeInMilliseconds;
                $Data['hajm'] = $Volume;
                $Data['ip_address'] = $ServerIp['ip_address'];
                $Data['ip_port'] = $ServerIp['ip_port'];
                $Data['Session'] = $ServerIp['session'];
                $Data['uid'] = $Ip->UID;
                $Data['name'] = $IpDetail['user_remark'];
                $Data['ip_api_domain'] = $ServerIp['ip_api_domain'];
                $Data['Data'] = $ServerIp;

                updateSanaiePanel($Data);

                $Ip->expire_date = $ExpireTime;
                $Ip->volume = $Vol;
                $Ip->product_id = $Service->id;
                $Ip->status = 1;
                $Ip->save();


                $keyboard['inline_keyboard'][][] = ['text' => '🔗 مشاهده کانفیگ', 'callback_data' => "type=show-order|id=$Order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "✅تمدید آی پی با موفقیت انجام شد..

🔰 پرداخت با کیف پول
---
یک دنیا تشکر بابت اعتماد شما! ❤️🙏
 ",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);

                $price = number_format($Payment->price) . ' تومان ';
                $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: تمدید
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
#خرید_آیپی_ثابت
";
                SendTelegramMessage($Message, -4157397982);


            } else {
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => "موجودی کیف پول شما کمتر از حد مجاز می باشد",
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                $token = config('services.telegram.hegza_token');
                EditTelegramMessageForCustomBot($data, $token);
            }

        } catch (\Exception $exception) {
            dd($exception->getMessage());
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;

        }
    }


    public function searchByCodeStepOne(Request $request)
    {

        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');

        $User = HegzaUser::where('user_id', $chatId)->first();

        if (!is_null($User)) {

            $User->path = 'search-by-code-step-one';
            $User->save();

            $text = "لطفا آی پی خود را به صورت کامل ارسال کنید.
            ";
            $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];

            $token = $this->token;
            $data = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'reply_markup' => json_encode($keyboard),
                'parse_mode' => 'HTML'
            ];
            EditTelegramMessageForCustomBot($data, $token);
        }

    }


    public function searchByCodeStepTwo(Request $request)
    {

        $chatId = $request->input('chat_id');
        $messageId = $request->input('message_id');
        $code = $request->input('code');
        $User = HegzaUser::where('user_id', $chatId)->first();

        if (!is_null($User)) {

            $User->path = 'start';
            $User->save();

            $HegzaIp = HegzaIp::where('code', $code)
                ->where('status', -4)
                ->first();

            if (!is_null($HegzaIp)) {

                $Products = HegzaProduct::where('category_id', $HegzaIp->category_id)->first();
                $Country = HegzaCountry::find($HegzaIp->category_id);

                $order = new HegzaOrder();
                $order->user_id = $User->id;
                $order->name = $Country->name;
                $order->country_id = $HegzaIp->category_id;
                $order->service_id = $Products->id;
                $order->status = 1;
                $order->save();

                $HegzaIp->user_id = $User->id;
                $HegzaIp->status = 1;
                $HegzaIp->order_id = $order->id;
                $HegzaIp->product_id = $Products->id;
                $HegzaIp->save();

                $text = 'آی پی با موفقیت به لیست سفارشات شما افزوده شد';
                $keyboard['inline_keyboard'][][] = ['text' => 'مشاهده جزئیات', 'callback_data' => "type=show-order|id=$order->id"];
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];
                $token = $this->token;
                $data = [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                SendTelegramMessageForCustomBot($data, $token);

            } else {
                $text = 'کد شما یافت نشد. لطفا با پشتیبانی تماس بگیرید';
                $keyboard['inline_keyboard'][][] = ['text' => '📱 منو', 'callback_data' => 'type=home'];

                $token = $this->token;
                $data = [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                    'text' => $text,
                    'reply_markup' => json_encode($keyboard),
                    'parse_mode' => 'HTML'
                ];
                SendTelegramMessageForCustomBot($data, $token);
            }
        }

    }
}
