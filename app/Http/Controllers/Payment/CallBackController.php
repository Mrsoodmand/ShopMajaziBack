<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\GoogleVoice;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Virtual_Products;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PHPUnit\Exception;

class CallBackController extends Controller
{
    /* */
    public function BankCallBack(Request $request)
    {
        try {
            $payment = Payment::where('authority', $request->input('Authority'))->first();

            // /payment/callback in web.php has no auth middleware; without a user nothing below can run.
            if (!Auth::guard('user')->check()) {
                $Status = false;
                $Data['msg'] = 'برای تکمیل پرداخت ابتدا وارد حساب کاربری خود شوید. اگر مبلغی کسر شده، حداکثر تا ۷۲ ساعت به حساب شما بازمی‌گردد.';
                return view('user-v2.callback', compact('Status', 'Data'));
            }

            if (!is_null($payment)) {

                $order = Order::find($payment->order_id);
                if (Auth::guard('user')->user()->id == $order->user_id) {

                    $MerchantID = config('services.zarinpal.merchant_id');
//                    $MerchantID = config('services.zarinpal.merchant_id_alt');
                    $Authority = $request->input('Authority');
                    if ($request->input('Status') == 'OK') {
                        if (Auth::guard('admin')->check()) {
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
                            if ($response['data']['code'] == 100) {
                                $Ref = $response['data']['ref_id'];

                                $payment->refId = $Ref;
                                $payment->details = 'ZarinPal';
                                $payment->status = 'Complete';
                                $payment->fee = $response['data']['fee'];
                                $payment->card = $response['data']['card_pan'];
                                $payment->paid_at = Carbon::now();
                                $payment->user_id = Auth::guard('user')->user()->id;
                                $payment->save();

                                if ($payment->type == 1) {
                                    $order = Order::find($payment->order_id);
                                    $order->pay_status = 'Complete';
                                    $order->save();
                                    if ($order->type == 1) {
                                        CheckRef(1, $order, Auth::guard('user')->user(), 10);
                                        return $this->VirtualNumberCallBack($order);
                                    } elseif ($order->type == 2) {
                                        CheckRef(3, $order, Auth::guard('user')->user(), 5);
                                        return $this->VirtualServiceCallBack($order, $response);
                                    } elseif ($order->type == 3) {
                                        CheckRef(3, $order, Auth::guard('user')->user(), 10);
                                        return $this->NormalAppleId($order, $response);
                                    } elseif ($order->type == 4) {
                                        CheckRef(4, $order, Auth::guard('user')->user(), 10);
                                        return $this->CustomAppleId($order, $response);
                                    } elseif ($order->type == 5) {
                                        CheckRef(5, $order, Auth::guard('user')->user(), 10);
                                        return $this->IpSabetCallBack($order, $response,$payment);
                                    } elseif ($order->type == 6) {
                                        CheckRef(5, $order, Auth::guard('user')->user(), 10);
                                        return $this->GoogleVoiceCallBack($order, $response);
                                    }
                                } elseif ($payment->type == 6) {
                                    return $this->ExtraVolumeCallBack($payment, $response);
                                } elseif ($payment->type == 8) {
                                    return $this->reNewIpPlusCallBack($payment, $response);
                                }
                            } else {
                                $Status = false;
                                $Data['msg'] = "این تراکنش قبلا وریفای شده است.";
                                $Data['orderId'] = $order->id;
                                $Data['Url'] = route('user.single-order', $order->id);
                                return view('user-v2.callback', compact('Status', 'Data'));
                            }
                        } else {
                            $payment->status = '-4';
                            $payment->help = $response['error']['code'];
                            $payment->error = $response['error']['message'];
                            $payment->detail = 'ZarinPal';
                            $payment->save();

                            if ($payment->type == 1) {
                                $Order = Order::find($payment->order_id);
                                $Order->pay_status = 'failed';
                                $Order->status = 'failed';
                                $Order->save();
                                $Id = $Order->id;
                            }elseif($payment->type == 3) {
                                $Id = $payment->id;
                            }else{
                                $Id = $payment->order_id;
                            }

                            if ($payment->type == 1 && in_array($Order->type, [1, 2, 3, 4, 6]) && $Back = $this->storefrontReturn('failed', $Id)) {
                                return $Back;
                            }
                            $Status = false;
                            $Data['msg'] = $response['error']['message'];
                            $Data['orderId'] = $Id;
                            $Data['Url'] = route('user.single-order',$Id);
                            return view('user-v2.callback', compact('Status', 'Data'));
                        }
                    } else {
                        $payment->status = 'failed';
                        $payment->error = 'لغو شده توسط کاربر';
                        $payment->save();

                        if ($payment->type == 1) {
                            $Order = Order::find($payment->order_id);
                            $Order->pay_status = 'failed';
                            $Order->status = 'failed';
                            $Order->save();
                            $Id = $Order->id;
                        }elseif($payment->type == 3) {
                            $Id = $payment->id;
                        }else{
                            $Id = $payment->order_id;
                        }

                        if ($payment->type == 1 && in_array($Order->type, [1, 2, 3, 4, 6]) && $Back = $this->storefrontReturn('cancelled', $Id)) {
                            return $Back;
                        }
                        $Status = false;
                        $Data['msg'] = 'تراکنش توسط کاربر لغو شد';
                        $Data['orderId'] = $Id;
                        $Data['Url'] = route('user.single-order', $Id);
                        return view('user-v2.callback', compact('Status', 'Data'));
                    }
                } else {
                    $payment->status = 'failed';
                    $payment->error = 'تراکنش توسط شخص دیگر پرداخت شده.';
                    $payment->save();

                    if ($payment->type == 1) {
                        $Order = Order::find($payment->order_id);
                        $Order->pay_status = 'failed';
                        $Order->status = 'failed';
                        $Order->save();
                        $Id = $Order->id;
                    }elseif($payment->type == 3) {
                        $Id = $payment->id;
                    }else{
                        $Id = $payment->order_id;
                    }

                    $Status = false;
                    $Data['msg'] = 'تراکنش توسط شخص دیگر پرداخت شده.';
                    $Data['orderId'] = $Id;
                    $Data['Url'] = route('user.single-order', $Id);
                    return view('user-v2.callback', compact('Status', 'Data'));
                }
            } else {
                $Status = false;
                $Data['msg'] = 'تراکنش مورد نظر یافت نشد. لطفا با پشتیانی تماس حاصل فرمایید.';
                return view('user-v2.callback', compact('Status', 'Data'));
            }
        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            return view('user-v2.callback', compact('Status', 'Data'));

//            Session::flash('error', ['خطایی رخ داده؛ جهت پیگیری با پشتیبانی تماس بگیرید.']);
//            return redirect('/');
        }
    }

    /*
     * Buyers who paid from the Next.js storefront go back to the page they came from, with the outcome in the query
     * (?pay=ok|refunded|cancelled|failed&order=). ApiStorefrontAccountController@payByCard stores that page in the
     * session; buyers from the old pages have none and keep the old behaviour.
     */
    private function storefrontReturn(string $Result, $OrderId)
    {
        $Path = Session::pull('storefront_return');
        if (!$Path) {
            return null;
        }
        return redirect()->away(StorefrontUrl($Path) . (str_contains($Path, '?') ? '&' : '?') . http_build_query(['pay' => $Result, 'order' => $OrderId]));
    }

    /* شماره مجازی */
    public function VirtualNumberCallBack($Order)
    {
        try {
            $getNumber = false;
            $OrderItem = OrderItem::where('order_id', $Order->id)->first();
            $Product = Virtual_Products::find($OrderItem->product_id);
            if ($Product->type == 'pva') {
                $Num = GetNumber_SmsPva($Product->service_code, $Product->country_code);
                if (!is_null($Num) && $Num['response'] == 1) {
                    $getNumber = true;
                    $Number = [
                        'number' => str_replace('+', '', $Num['CountryCode']) . '' . $Num['number'],
                        'numberId' => $Num['id'],
                        'sms' => null,
                        'type' => 'pva',
                        'status' => 'pending'
                    ];
                }
            } elseif ($Product->type == 'man') {
                $Num = GetNumber_SmsMan($Product->service_code, $Product->country_code);
                if (is_array($Num) && array_key_exists('number', $Num)) {
                    $getNumber = true;
                    $Number = [
                        'number' => $Num['number'],
                        'numberId' => $Num['request_id'],
                        'sms' => null,
                        'type' => 'man',
                        'status' => 'pending'
                    ];
                }
            } elseif ($Product->type == 'active') {
                $Num = GetNumber_Active($Product->service_code, $Product->country_code);
                if (is_array($Num) && array_key_exists('phoneNumber', $Num) && $Num['phoneNumber'] != null) {
                    $getNumber = true;
                    $Number = [
                        'number' => $Num['phoneNumber'],
                        'numberId' => $Num['activationId'],
                        'sms' => null,
                        'type' => 'active',
                        'status' => 'pending'
                    ];
                }
            }
            $Order = Order::find($Order->id);

            $price = number_format($Order->price) . 'تومان';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: شماره مجازی
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
";
            SendTelegramMessage($Message, -1001509696194);

            if ($getNumber) {
                /* ثبت سفارش */
                $Order = Order::find($Order->id);
                $Order->status = 'pending';
                $Order->pay_status = 'Complete';
                $Order->expire_date = Carbon::now()->addMinutes(10);
                $Order->save();

                /* ثبت جزئیات سفارش */
                $OrderItem = OrderItem::where('order_id', $Order->id)->first();
                $OrderItem->detail = json_encode($Number);
                $OrderItem->save();

                Session::flash('VirtualNumberSuccess');
                return $this->storefrontReturn('ok', $Order->id) ?? redirect('virtual-number');
            } else {
                $Order = Order::find($Order->id);
                $Order->status = 'failed';
                $Order->pay_status = 'Complete';
                $Order->expire_date = Carbon::now()->addMinutes(10);
                $Order->detail = 'درحال حاضر، شماره برای سرویس مورد نظر موجود نمی باشد، وجه به کیف پول شما عودت داده شده است.';
                $Order->save();

                $User = User::find(UserID()->id);
                $User->wallet = $User->wallet + $Order->price;
                $User->save();

                Session::flash('error', ['درحال حاضر، شماره برای سرویس مورد نظر موجود نمی باشد، وجه به کیف پول شما عودت داده شده است.']);
                return $this->storefrontReturn('refunded', $Order->id) ?? redirect('virtual-number');
            }
        } catch (\Exception $exception) {
            $Order = Order::find($Order->id);
            $Order->status = 'failed';
            $Order->pay_status = 'failed';
            $Order->detail = $exception->getMessage();
            $Order->save();

            Session::flash('error', ['خطایی رخ داده؛ جهت پیگیری با پشتیبانی تماس بگیرید.']);
            return $this->storefrontReturn('failed', $Order->id) ?? redirect('virtual-number');
        }
    }

    /* اپل آیدی آماده */
    public function NormalAppleId($OrderId, $response)
    {
        try {
            $Quantity = OrderItem::where('order_id', $OrderId->id)->first()->detail;
            $AppleId = AppleId::take($Quantity)->where('status', 0)->get();
            foreach ($AppleId as $value) {
                // Conditional update: skip an Apple ID another order claimed in the meantime.
                $Claimed = AppleId::where('id', $value->id)->where('status', 0)
                    ->update(['status' => 1, 'order_id' => $OrderId->id, 'user_id' => UserID()->id]);
                if ($Claimed) {
                    $Ids[] = $value->id;
                }
            }
            if (isset($Ids)) {
                if (count($Ids) < $Quantity) {
                    $Status = 'pending';
                } else {
                    $Status = 'complete';
                }
            } else {
                $Status = 'pending';
            }

            $Order = Order::find($OrderId->id);
            $Order->status = $Status;
            $Order->pay_status = 'Complete';
            $Order->save();

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: اپل آیدی آماده
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_اپل_آیدی_آماده
";
            SendTelegramMessage($Message, -1001509696194);

            // Bought on the Next.js storefront: back to the Apple ID page, which shows the logins.
            if ($Back = $this->storefrontReturn('ok', $Order->id)) {
                return $Back;
            }
            $Status = true;
            $Data['Url'] = route('user.single-order', $Order->id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* اپل آیدی سفارش */
    public function CustomAppleId($OrderId, $response)
    {
        try {
            $Order = Order::find($OrderId->id);
            $Order->status = 'pending';
            $Order->pay_status = 'Complete';
            $Order->save();

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: اپل آیدی سفارشی
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_اپل_آیدی_سفارش
@mrsodmand
";
            SendTelegramMessage($Message, -1001509696194);

            if ($Back = $this->storefrontReturn('ok', $Order->id)) {
                return $Back;
            }
            $Status = true;
            $Data['Url'] = route('user.single-order', $Order->id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* گوگل ویس */
    public function GoogleVoiceCallBack($OrderId, $response)
    {
        // Storefront orders for a Numberland number: buy it now that the bank has paid, then go back to the site.
        $Detail = json_decode(optional(OrderItem::where('order_id', $OrderId->id)->first())->detail, true) ?: [];
        if (isset($Detail['numberland_id'])) {
            $Order = Order::find($OrderId->id);
            if ($Order->status === 'waiting to pay') {
                $Order->status = 'pending';
                $Order->save();
            }
            $Result = app(\App\Services\GoogleVoiceNumbers::class)->deliver($Order);
            return $this->storefrontReturn($Result, $Order->id) ?? redirect('google-voice');
        }

        try {
            $OrderItem = OrderItem::where('order_id', $OrderId->id)->first();
            $ProductId = json_decode($OrderItem->detail, true)['product_id'];
            $GoogleVoice = GoogleVoice::find($ProductId);
            if (!is_null($GoogleVoice) && $GoogleVoice->status == 0) {
                $GoogleVoice->status = 1;
                $GoogleVoice->order_id = $OrderId->id;
                $GoogleVoice->user_id = Auth::guard('user')->user()->id;
                $GoogleVoice->save();
                $Status = 'complete';
            } else {
                $Status = 'pending';
            }

            $Order = Order::find($OrderId->id);
            $Order->status = $Status;
            $Order->pay_status = 'Complete';
            $Order->save();

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: گوگل ویس
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_گوگل_ویس
";
            SendTelegramMessage($Message, -1001509696194);

            $Status = true;
            $Data['Url'] = route('user.single-order', $Order->id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* خرید حجم اضافی */
    public function ExtraVolumeCallBack($Payment, $response)
    {
        $Detail = json_decode($Payment->help, true);
        $Ip = Ip::find($Detail['ip_id']);
        try {

            $Volume = __('order.Volume-Gb.' . $Detail['volume']);
            $Volume = $Ip->volume + $Volume;

            $Ip->volume = $Volume;
            $Ip->save();

            $ServerIp = Ip::find($Ip->parent_id);
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
            $Log = updateSanaiePanel($Data);

            $price = number_format($Payment->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Ip->order_id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: خرید حجم اضافه
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_حجم_اضافه
";
            SendTelegramMessage($Message, -1001509696194);

            $Volume = __('order.Volume-Gb.' . $Detail['volume']);
            $Help =
                [
                    'volume' => $Volume,
                ];

            $Payment->help = json_encode($Help);
            $Payment->save();

            $Status = true;
            $Data['Url'] = route('user.single-order', $Ip->order_id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Payment->id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Payment->id;
            $Data['Url'] = route('user.single-order', $Ip->order_id);
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* آی پی ثابت */
    public function IpSabetCallBack($Order, $response,$Payment)
    {
        try {
            $OrderItem = OrderItem::where('order_id', $Order->id)->first();
            $Product = Product::find($OrderItem->product_id);
            $Country = Category::find($Product->category_id);

            $Ip = Ip::where('type', 'pu-ip-sabet-user')->where('category_id', $Country->id)->where('status', 0)->first();
            if (!is_null($Ip)) {
                $ProductDetail = json_decode($Product->detail, true);
                $Day = $ProductDetail['day'];
                $ExpireTime = Carbon::now()->addDays($Day);
                $ExtraVol = __('order.Volume-Gb.' . $OrderItem->detail);
                $Vol = $ProductDetail['volume'] + $ExtraVol;

                $Ip->expire_date = $ExpireTime;
                $Ip->volume = $Vol;
                $Ip->user_id = Auth::guard('user')->user()->id;
                $Ip->product_id = $Product->id;
                $Ip->status = 1;
                $Ip->order_id = $Order->id;
                $Ip->save();

                $date = \Illuminate\Support\Carbon::parse($ExpireTime);
                $timeInMilliseconds = $date->valueOf();
                $Volume = $Vol * 1073741824;
                $IpDetail = json_decode($Ip->detail, true);
                $ServerIp = Ip::find($Ip->parent_id);
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

                $Status = 'complete';
            } else {
                $Status = 'pending';
            }

            $Order->status = $Status;
            $Order->save();

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_آیپی_ثابت
";
            SendTelegramMessage($Message, -1001509696194);


            $Help =
                [
                    'volume' => $ExtraVol,
                    'product_id' => $Product->id
                ];
            $Payment->help = json_encode($Help);
            $Payment->save();


            $Status = true;
            $Data['Url'] = route('user.single-order', $Order->id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* تمدید آی پی ثابت */
    public function reNewIpPlusCallBack($Payment, $response)
    {
        $Detail = json_decode($Payment->help, true);
        $Ip = Ip::find($Detail['ip_id']);
        $Product = Product::find($Detail['product_id']);
        try {
            $ProductDetail = json_decode($Product->detail, true);
            $Day = $ProductDetail['day'];
            $ExpireTime = Carbon::parse($Ip->expire_date)->addDays($Day);
            $ExtraVol = __('order.Volume-Gb.' . $Detail['volume']);
            $Vol = $ProductDetail['volume'] + $ExtraVol;
            $Vol = $Vol + $Ip->volume;

            $date = \Illuminate\Support\Carbon::parse($ExpireTime);
            $timeInMilliseconds = $date->valueOf();
            $Volume = $Vol * 1073741824;

            $IpDetail = json_decode($Ip->detail, true);

            $ServerIp = Ip::find($Ip->parent_id);
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


            $Ip->expire_date = $ExpireTime;
            $Ip->volume = $Vol;
            $Ip->product_id = $Product->id;
            $Ip->status = 1;
            $Ip->save();

            $price = number_format($Payment->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Ip->order_id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: تمدید آی پی
🏧 نوع پرداخت: درگاه
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#تمدید_آی_پی
";
            SendTelegramMessage($Message, -1001509696194);

            $Help =
                [
                    'volume' => $ExtraVol,
                    'product_id' => $Product->id
                ];
            $Payment->help = json_encode($Help);
            $Payment->save();

            $Status = true;
            $Data['Url'] = route('user.single-order', $Ip->order_id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Payment->id;
            return view('user-v2.callback', compact('Status', 'Data'));

        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Payment->id;
            $Data['Url'] = route('user.single-order', $Ip->order_id);
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }

    /* سرویس های مجازی */
    public function VirtualServiceCallBack($Order, $response)
    {
        try {
            $OrderItem = OrderItem::where('order_id', $Order->id)->first();

            $Order = Order::find($Order->id);
            $Order->status = 'pending';
            $Order->pay_status = 'Complete';
            $Order->save();

            $Details = json_decode($OrderItem->detail, true);
            $orderType = $Details['plan']['plan_name'];

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: $orderType
🏧 نوع پرداخت: درگاه بانک
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_خدمات_مجازی
";
            SendTelegramMessage($Message, -1001509696194);

            if ($Back = $this->storefrontReturn('ok', $Order->id)) {
                return $Back;
            }
            $Status = true;
            $Data['Url'] = route('user.single-order', $Order->id);
            $Data['refId'] = $response['data']['ref_id'];
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        } catch (\Exception $exception) {
            $Status = false;
            $Data['msg'] = $exception->getMessage();
            $Data['orderId'] = $Order->pay_id;
            return view('user-v2.callback', compact('Status', 'Data'));
        }
    }
}
