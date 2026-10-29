<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Wallet;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    protected $SmsPvaApiKey = null;
    protected $NumberLandApi = null;

    // Read from .env (see config/services.php) instead of keeping the keys in code.
    public function __construct()
    {
        $this->SmsPvaApiKey = config('services.smspva.key');
        $this->NumberLandApi = config('services.numberland.legacy_key');
    }

    /* پرداخت مستقیم شماره مجازی */
    public function PayVirtualNumberWithBank(Request $request)
    {

        if (Auth::guard('user')->check()) {
            $validation = Validator::make($request->all(),
                [
                    'p_id' => 'required|numeric',
                    'type' => 'required|in:wallet,bank'
                ],
                [
                    'product_id.required' => 'لطفا محصول را درست انتخاب نمایید',
                    'product_id.numeric' => 'خطا کد 1',
                    'type.required' => 'لطفا نحوه پرداخت را انتخاب نمایید',
                    'type.in' => 'خطا در انتخاب نحوه پرداخت',
                ]
            );

            if ($validation->fails()) {

                return exception_error($request, $validation->errors()->all());
            }

            try {

                $Product = Product::find($request->input('p_id'));

                if (!is_null($Product)) {
                    if ($request->input('type') == 'bank') {
                        $ProductPrice = ShowProductPrice($Product);

                        $Order = new Order();
                        $Order->user_id = UserID()->id;
                        $Order->price = $ProductPrice['price'];
                        $Order->status = 'Waiting to pay';
                        $Order->pay_status = 'Waiting to pay';
                        $Order->type = 'virtual-number';
                        $Order->save();

                        $Detail = [
                            'number' => 'در انتظار دریافت',
                            'Sms' => 0,
                            'number_id' => 'در انتظار دریافت',
                            'country_code' => 'در انتظار دریافت',
                            'expire_time' => Carbon::now(),
                            'type' => 'SmsPva'
                        ];

                        $OrderItem = new OrderItem();
                        $OrderItem->order_id = $Order->id;
                        $OrderItem->product_id = $Product->id;
                        $OrderItem->user_id = UserID()->id;
                        $OrderItem->type = 'Virtual-Number';
                        $OrderItem->price = $ProductPrice['price'];
                        $OrderItem->detail = json_encode($Detail);
                        $OrderItem->save();

                        $Payment = new Payment();
                        $Payment->order_id = $Order->id;
                        $Payment->user_id = UserID()->id;
                        $Payment->type = 'Factor';
                        $Payment->status = 'Waiting to pay';
                        $Payment->gate = 'Bank';
                        $Payment->creator = 'System';
                        $Payment->save();

                        DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                        $OrderId = Encode($OrderItem->id);
                        $url = route('user.ShowNumber', [$OrderId]);

                        $OrderPost['OrderId'] = $OrderItem->id;
                        $OrderPost['CallBack'] = 'https://lebascenter.com/';
                        $OrderPost['UserMobile'] = UserID()->mobile;
                        $OrderPost['Price'] = $ProductPrice['price'];

                        return $this->ZarinPalSendToBank($Order);


//                        return response()->json(['msg' => ' فاکتور شما با موفقیت ثبت شد. درحال انتقال به صفحه پرداخت', 'url' => $url], 200);


                    } elseif
                    ($request->input('type') == 'wallet') {


                        $ProductPrice = ShowProductPrice($Product);

                        if (UserID()->wallet >= $ProductPrice['price']) {

                            if ($Product->pva_quantity > 0) {

                                $Number = json_decode($this->GenerateNumber($Product, $request), true);

                                if (is_array($Number) && $Number['response'] == 1) {


                                    // کم کردن مبلغ از کیف پول کاربر
                                    $User = User::find(UserID()->id);
                                    $User->wallet = $User->wallet - $ProductPrice['price'];
                                    $User->save();


                                    // ثبت سفارش
                                    $Order = new Order();
                                    $Order->user_id = UserID()->id;
                                    $Order->price = $ProductPrice['price'];
                                    $Order->status = 'Pending';
                                    $Order->pay_status = 'paid with wallet';
                                    $Order->type = 'virtual-number';
                                    $Order->save();

                                    // ثبت فاکتور پرداخت
                                    $Payment = new Payment();
                                    $Payment->order_id = $Order->id;
                                    $Payment->user_id = UserID()->id;
                                    $Payment->type = 'Factor';
                                    $Payment->status = 'paid with wallet';
                                    $Payment->gate = 'Bank';
                                    $Payment->creator = 'System';
                                    $Payment->save();

                                    $Wallet = new Wallet();
                                    $Wallet->user_id = UserID()->id;
                                    $Wallet->type = 1;
                                    $Wallet->value = $ProductPrice['price'];
                                    $Wallet->detail = "کسر بابت سفارش شماره $Order->id";
                                    $Wallet->save();

                                    if ($User->identifier != 0) {
                                        $Value = $ProductPrice['price'] / 100;
                                        $Value = $Product->commission * $Value;

                                        $Commission = new Commission();
                                        $Commission->from = $User->id;
                                        $Commission->to = $User->identifier;
                                        $Commission->order_id = $Order->id;
                                        $Commission->status = 'pending';
                                        $Commission->value = $Value;
                                        $Commission->save();
                                    }

                                    // ثبت جزئیات سفارش
                                    $Detail = [
                                        'number' => $Number['number'],
                                        'Sms' => 0,
                                        'number_id' => $Number['id'],
                                        'country_code' => $Number['CountryCode'],
                                        'expire_time' => Carbon::now()->addMinutes(9),
                                        'type' => 'SmsPva'
                                    ];
                                    $OrderItem = new OrderItem();
                                    $OrderItem->order_id = $Order->id;
                                    $OrderItem->product_id = $Product->id;
                                    $OrderItem->user_id = UserID()->id;
                                    $OrderItem->type = 'Virtual-Number';
                                    $OrderItem->price = $ProductPrice['price'];
                                    $OrderItem->detail = json_encode($Detail);
                                    $OrderItem->save();


                                    DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                                    $OrderId = Encode($OrderItem->id);
                                    $url = route('user.ShowNumber', [$OrderId]);

                                    if ($request->ajax()) {
                                        return response()->json(['msg' => ' پرداخت با موفقیت انجام شد. در حال انتقال به صفحه دریافت شماره', 'url' => $url], 200);
                                    } else {
                                        return redirect()->route('user.ShowNumber', [$OrderId]);
                                    }

                                } else {
                                    return exception_error($request, 'کاربر گرامی، در دریافت شماره مجازی خطایی رخ داده است. لطفا مجدد تلاش کنید.');
                                }
                            } else {

                                $Number = json_decode($this->NumberLandGenerateNumber($Product), true);
                                if (is_array($Number) && $Number['RESULT'] == 1) {


                                    // کم کردن مبلغ از کیف پول کاربر
                                    $User = User::find(UserID()->id);
                                    $User->wallet = $User->wallet - $Product->toman_price;
                                    $User->save();


                                    // ثبت سفارش
                                    $Order = new Order();
                                    $Order->user_id = UserID()->id;
                                    $Order->price = $ProductPrice['price'];
                                    $Order->status = 'complete';
                                    $Order->pay_status = 'paid with wallet';
                                    $Order->type = 'virtual-number';
                                    $Order->save();

                                    // ثبت فاکتور پرداخت
                                    $Payment = new Payment();
                                    $Payment->order_id = $Order->id;
                                    $Payment->user_id = UserID()->id;
                                    $Payment->type = 'Factor';
                                    $Payment->status = 'paid with wallet';
                                    $Payment->gate = 'Bank';
                                    $Payment->creator = 'System';
                                    $Payment->save();

                                    $Wallet = new Wallet();
                                    $Wallet->user_id = UserID()->id;
                                    $Wallet->type = 1;
                                    $Wallet->value = $ProductPrice['price'];
                                    $Wallet->detail = "کسر بابت سفارش شماره $Order->id";
                                    $Wallet->save();


                                    if ($User->identifier != 0) {
                                        $Value = $ProductPrice['price'] / 100;
                                        $Value = $Product->commission * $Value;

                                        $Commission = new Commission();
                                        $Commission->from = $User->id;
                                        $Commission->to = $User->identifier;
                                        $Commission->order_id = $Order->id;
                                        $Commission->status = 'pending';
                                        $Commission->value = $Value;
                                        $Commission->save();
                                    }

                                    // ثبت جزئیات سفارش
                                    $Detail = [
                                        'number' => $Number['NUMBER'],
                                        'Sms' => 0,
                                        'number_id' => $Number['ID'],
                                        'country_code' => $Number['AREACODE'],
                                        'expire_time' => Carbon::now()->addMinutes(Carbon::parse($Number["TIME"])->minute),
                                        'type' => 'NumberLand'
                                    ];
                                    $OrderItem = new OrderItem();
                                    $OrderItem->order_id = $Order->id;
                                    $OrderItem->product_id = $Product->id;
                                    $OrderItem->user_id = UserID()->id;
                                    $OrderItem->type = 'Virtual-Number';
                                    $OrderItem->price = $ProductPrice['price'];
                                    $OrderItem->detail = json_encode($Detail);
                                    $OrderItem->save();


                                    DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                                    $OrderId = Encode($OrderItem->id);
                                    $url = route('user.ShowNumber', [$OrderId]);

                                    if ($request->ajax()) {
                                        return response()->json(['msg' => ' پرداخت با موفقیت انجام شد. در حال انتقال به صفحه دریافت شماره', 'url' => $url], 200);
                                    } else {
                                        return redirect()->route('user.ShowNumber', [$OrderId]);
                                    }

                                } else {
                                    return exception_error($request, 'کاربر گرامی، در دریافت شماره مجازی خطایی رخ داده است. لطفا مجدد تلاش کنید.');
                                }
                            }

                        } else {
                            return exception_error($request, 'موجودی کیف پول شما کافی نمیباشد.');
                        }
                    } else {
                        return exception_error($request, 'روش پرداخت تامعتبر میباشد');
                    }

                } else {
                    return exception_error($request, 'محصول مورد نظر یافت نشد');
                }
            } catch (\Exception $exception) {
                return exception_error($request, $exception->getMessage());
            }
        } else {
            exception_error($request, 'کاربر گرامی ، ابتدا باید وارد حساب کاربری خود شوید');
        }

    }

    // ارسال درخواست شماره مجازی به SmsPva
    public function GenerateNumber($product)
    {
        $Detail = json_decode($product->detail, true)['Info'];

        $Country = $Detail['pva_country_code'];
        $Service = $Detail['pva_service_code'];

        $params = [
            'metod' => 'get_number',
            'country' => $Country,
            'service' => $Service,
            'apikey' => $this->SmsPvaApiKey,
        ];
        $ResultOfNumber = SmsPvaApiMethod($params);
        return $ResultOfNumber;
    }

    // ارسال درخواست شماره مجازی به NumberLand
    public function NumberLandGenerateNumber($product)
    {
        $Detail = json_decode($product->detail, true)['Info'];
        $Country = $Detail['land_country_code'];
        $Service = $Detail['land_service_code'];

        $params = [
            'apikey' => $this->NumberLandApi,
            'method' => 'getnum',
            'country' => $Country,
            'operator' => 'any',
            'service' => $Service,
        ];
        $ResultOfNumber = SmsNumberLandMethod($params);
        return $ResultOfNumber;
    }

    // دریافت پیامک PVA
    public function CheckSms($product, $id)
    {
        $Detail = json_decode($product->detail, true)['Info'];

        $Country = $Detail['pva_country_code'];
        $Service = $Detail['pva_service_code'];
        $params = [
            'metod' => 'get_sms',
            'country' => $Country,
            'service' => $Service,
            'id' => $id,
            'apikey' => $this->SmsPvaApiKey,
        ];
        $ResultOfNumber = SmsPvaApiMethod($params);

        return $ResultOfNumber;

    }

    // دریافت پیامک NumberLand
    public function CheckNumberLandSms($id)
    {

        $params = [
            'method' => 'checkstatus',
            'id' => $id,
            'apikey' => $this->NumberLandApi,
        ];
        $ResultOfNumber = SmsNumberLandMethod($params);

        return $ResultOfNumber;

    }

    // مشاهده سفارش پس از تکمیل
    public function ShowNumber($id, Request $request)
    {
        $id = Decode($id);

        if ($id != 'error') {

            $OrderItem = OrderItem::find($id);
            $Order = Order::find($OrderItem->order_id);

            if ($Order->status == 'Pending') {
                $NumberDetail = json_decode($OrderItem->detail, true);
                $CurrentTime = Carbon::now();
                $ExpireTime = Carbon::parse($NumberDetail['expire_time']);
                $Diff = $CurrentTime->diff($ExpireTime);
                $MinToSec = $Diff->i * 60;
                $Min = $MinToSec + $Diff->s;

                return view('user.show-number', compact('NumberDetail', 'OrderItem', 'Min'));

            } else {
                return exception_error($request, 'سفارش مورد نطر تکمیل نشده است');
            }
        } else {
            return exception_error($request, 'شماره سفارش نا معتبر میاشد');
        }

    }

    // بررسی پیام
    public function CheckSmsNumber($id, Request $request)
    {
        $id = Decode($id);

        if ($id != 'error') {

            $OrderItem = OrderItem::find($id);
            $Order = Order::find($OrderItem->order_id);

            // وضعیت سفارش را بررسی میکنیم
            if ($Order->status == 'Pending' || $Order->status == 'complete') {

                $Product = Product::find($OrderItem->product_id);
                $OrderDetail = json_decode($OrderItem->detail, true);

                // بررسی میکنیم آیا قبلا کد گرفته است یا ن
                if ($OrderDetail['Sms'] == 0) {

                    // بررسی میکینم آیا شماره منقضی شده است یا نه
                    if (strtotime(Carbon::now()) <= strtotime($OrderDetail['expire_time'])) {

                        if ($OrderDetail['type'] == 'SmsPva') {


                            // بررسی میکنیم آیا پیامی دریافت شده است یا ن
                            $Sms = json_decode($this->CheckSms($Product, $OrderDetail['number_id']), true);

                            if (is_array($Sms) && intval($Sms['response']) == 1) {

                                $Code = $Sms['sms'];
                                $Detail = [
                                    'number' => $OrderDetail['number'],
                                    'Sms' => $Code,
                                    'number_id' => $OrderDetail['number_id'],
                                    'country_code' => $OrderDetail['country_code']
                                ];

                                $OrderItem->detail = json_encode($Detail);
                                $OrderItem->save();

                                $Order->status = 'complete';
                                $Order->save();

                                $Commission = Commission::where('order_id', $Order->id)->first();
                                if (!is_null($Commission)) {
                                    $Commission->status = 'progressing';
                                    $Commission->save();

                                }


                                if ($request->ajax()) {
                                    return response()->json(['code' => $Code, 'msg' => 'کد دریافت شد. با تشکر از خرید شما!'], 200);
                                } else {
                                    Session::flash('success', " کد با موفقیت دریافت شد. کد دریافت شده :  $Code با تشکر از خرید شما ");
                                }


                            } else {
                                return validation_error($request, 'در انتظار دریافت کد');
                            }
                        } elseif ($OrderDetail['type'] == 'NumberLand') {
                            $Sms = json_decode($this->CheckNumberLandSms($OrderDetail['number_id']), true);


                            if (is_array($Sms) && intval($Sms['RESULT']) == 1 && $Sms['CODE'] != 0) {

                                $Code = $Sms['sms'];
                                $Detail = [
                                    'number' => $OrderDetail['number'],
                                    'Sms' => $Code,
                                    'number_id' => $OrderDetail['number_id'],
                                    'country_code' => $OrderDetail['country_code']
                                ];

                                $OrderItem->detail = json_encode($Detail);
                                $OrderItem->save();

                                $Order->status = 'complete';
                                $Order->save();

                                $Commission = Commission::where('order_id', $Order->id)->first();
                                if (!is_null($Commission)) {
                                    $Commission->status = 'progressing';
                                    $Commission->save();

                                }

                                if ($request->ajax()) {
                                    return response()->json(['code' => $Code, 'msg' => 'کد دریافت شد. با تشکر از خرید شما!'], 200);
                                } else {

                                    Session::flash('success', " کد با موفقیت دریافت شد. کد دریافت شده :  $Code با تشکر از خرید شما ");
                                }
                            } else {

                                $Status = [
                                    'wait code' => 'در انتظار دریافت کد',
                                    'number canceled' => 'شماره کنسل شده است .',
                                    'number banned' => 'کاربر گرامی، شماره دریافت شده بن شده است. و مبلغ به کیف پول شما برگشت خواهد شد',
                                    'completed	' => 'کاربر گرامی شماره دریافت شده است.',
                                ];

                                return validation_error($request, [$Status[$Sms['DESCRIPTION']]]);
                            }

                        }

                    } else {

                        $Order->status = 'failed';
//                        $Order->pay_status = 'failed';
                        $Order->detail = 'شماره خریداری شده منقضی شده است و مبلغ به کیف پول برگشت خورده است';
                        $Order->save();

                        $User = User::find($Order->user_id);
                        $User->wallet = $Order->price + $User->wallet;
                        $User->save();

                        $Wallet = new Wallet();
                        $Wallet->user_id = UserID()->id;
                        $Wallet->type = 2;
                        $Wallet->value = $Order->price;
                        $Wallet->detail = "برگشت وجه سفارش شماره $Order->id به کیف پول بابت لغو شدن سفارش ";
                        $Wallet->save();


                        $Commission = Commission::where('order_id', $Order->id)->first();
                        $Commission->status = 'failed';
                        $Commission->save();


                        $url = "http://localhost:8000/";
                        if ($request->ajax()) {
                            return response()->json(['error' => ['شماره دریافت شده ، منضی شده است و مبلغ به کیف پول برگشت خورده است'], 'url' => $url], 400);
                        } else {
                            Session::flash('error', ['شماره دریافت شده ، منقضی شده است و مبلغ به کیف پول شما برگشت خورده است']);
                            return redirect('/');
                        }

                    }
                } else {

                    $Code = $OrderDetail['Sms'];
                    if ($request->ajax()) {
                        return response()->json(['code' => $Code, 'msg' => 'کد دریافت شد. با تشکر از خرید شما!'], 200);
                    } else {
                        Session::flash('success', " کد با موفقیت دریافت شد. کد دریافت شده :  $Code با تشکر از خرید شما ");
                    }
                }
            } else {
                return exception_error($request, 'سفارش مورد نطر تکمیل نشده است');
            }
        } else {
            return exception_error($request, 'شماره سفارش نامعتبر میباشد');
        }
    }

    // -- -- -- -- -- -- -- \\--------------> Social Members Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Social Members Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Social Members Payment Steps <-------------- // -- -- -- -- -- -- -- \\

    public function ServicePaymentStepOne(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'type' => 'required|in:bank,wallet'
            ],
            [
                'type.required' => 'لطفا نحوه پرداخت را مشخص کنید',
                'type.in' => 'نوع پرداخت نا معتبر میباشد.'
            ]
        );
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            if ($request->input('type') == 'wallet') {


                $Total_Price = 0;
                $Products = Session::get('Cart');
                Session::forget('Cart');
$checkPrice = 0;
                foreach ($Products as $product) {

                    $Plan = Plan::find($product['plan_id']);
                    $Price = $Plan->price;
                    $checkPrice += $Price;
                }

                if ($checkPrice <= Auth::guard('user')->user()->wallet){
                    return exception_error($request, ['موجودی شما برای تکمیل این سفارش کافی نیست']);

                }


                // ثبت سفارش
                $Order = new Order();
                $Order->user_id = UserID()->id;
                $Order->status = 'Pending';
                $Order->pay_status = 'paid with wallet';
                $Order->type = 'social-service';
                $Order->save();

                // جزئیات سفارش

                foreach ($Products as $product) {

                    $Plan = Plan::find($product['plan_id']);

                    $OrderDetail['PlansDetail'] = [
                        'name' => $Plan->name,
                        'price' => $Plan->price,
                        'discount' => $Plan->discount,
                    ];
                    $OrderDetail['Options'] = $product['options'];
                    $Price = $Plan->price;

                    if ($Plan->discount != null && $Plan->discount != 0) {
                        $Price = $Plan->discount;
                    }

                    $OrderItem = new OrderItem();
                    $OrderItem->order_id = $Order->id;
                    $OrderItem->user_id = UserID()->id;
                    $OrderItem->product_id = $product['product_id'];
                    $OrderItem->type = 'social-services';
                    $OrderItem->price = $Price;
                    $OrderItem->detail = json_encode($OrderDetail);
                    $OrderItem->save();
                    $Total_Price += $Price;
                }

                // فاکتور سفارش
                $Payment = new Payment();
                $Payment->user_id = UserID()->id;
                $Payment->order_id = $Order->id;
                $Payment->type = 'Factor';
                $Payment->gate = 'Bank';
                $Payment->status = 'paid with wallet';
                $Payment->creator = 'System';
                $Payment->save();

                $Order = Order::find($Order->id);
                $Order->price = $Total_Price;
                $Order->pay_id = $Payment->id;
                $Order->save();

                $Wallet = new Wallet();
                $Wallet->user_id = UserID()->id;
                $Wallet->type = 1;
                $Wallet->value = $Total_Price;
                $Wallet->detail = "کسر بابت سفارش شماره $Order->id";
                $Wallet->save();


                $User = User::find(UserID()->id);
                $User->wallet = $User->wallet - $Total_Price;
                $User->save();


                return redirect()->route('user.single-order', [Encode($Order->id)]);
            }

            elseif ($request->input('type') == 'bank') {
                $User = User::find(UserID()->id);
                $Total_Price = 0;
                $Products = Session::get('Cart');
                Session::forget('Cart');

                // ثبت سفارش
                $Order = new Order();
                $Order->user_id = UserID()->id;
                $Order->status = 'Waiting to pay';
                $Order->pay_status = 'Waiting to pay';
                $Order->type = 'social-service';
                $Order->save();


                // جزئیات سفارش
                $Commissions = 0;
                foreach ($Products as $product) {

                    $Plan = Plan::find($product['plan_id']);

                    $SingleProduct = Product::find($Plan->product_id);

                    $OrderDetail['PlansDetail'] = [
                        'name' => $Plan->name,
                        'price' => $Plan->price,
                        'discount' => $Plan->discount,
                    ];
                    $OrderDetail['Options'] = $product['options'];
                    $Price = $Plan->price;

                    if ($Plan->discount != null && $Plan->discount != 0) {
                        $Price = $Plan->discount;
                    }

                    $CommissionPercent = $Price / 100;
                    $Commissions += $SingleProduct->commission * $CommissionPercent;

                    $OrderItem = new OrderItem();
                    $OrderItem->order_id = $Order->id;
                    $OrderItem->user_id = UserID()->id;
                    $OrderItem->product_id = $product['product_id'];
                    $OrderItem->type = 'social-services';
                    $OrderItem->price = $Price;
                    $OrderItem->detail = json_encode($OrderDetail);
                    $OrderItem->save();
                    $Total_Price += $Price;
                }


                if ($User->identifier != 0) {

                    $Commission = new Commission();
                    $Commission->from = $User->id;
                    $Commission->to = $User->identifier;
                    $Commission->order_id = $Order->id;
                    $Commission->status = 'pending';
                    $Commission->value = $Commissions;
                    $Commission->save();
                }

                // فاکتور سفارش
                $Payment = new Payment();
                $Payment->user_id = UserID()->id;
                $Payment->order_id = $Order->id;
                $Payment->type = 'Factor';
                $Payment->gate = 'Bank';
                $Payment->status = 'Waiting to pay';
                $Payment->creator = 'System';
                $Payment->save();

                $Order = Order::find($Order->id);
                $Order->price = $Total_Price;
                $Order->pay_id = $Payment->id;
                $Order->save();

                return $this->ZarinPalSendToBank($Order);

            } else {

                return back();
            }

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }


    }

    // -- -- -- -- -- -- -- \\--------------> Zarin Pal Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Zarin Pal Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Zarin Pal Payment Steps <-------------- // -- -- -- -- -- -- -- \\


    /* درگاه پرداخت زیرن پال */
    public function ZarinPalSendToBank($order)
    {

        $MerchantID = config('services.zarinpal.merchant_id'); //Required
        $Amount = $order->price; //Amount will be based on Toman - Required
        $Description = "بابت سفارش شماره $order->id"; // Required
        $Email = (Auth::guard('user')->user()->email != null ? Auth::guard('user')->user()->email : null);
        $Mobile = Auth::guard('user')->user()->mobile; // Optional
        $CallbackURL = url('/callback');

//        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);

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


//Redirect to URL You can do it also by creating a form
        if ($result->Status == 100) {

            $payment = Payment::where('order_id', $order->id)->first();
            $payment->authority = $result->Authority;
            $payment->save();

            Header('Location: https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            //برای استفاده از زرین گیت باید ادرس به صورت زیر تغییر کند:
            //Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/ZarinGate');
        } else {
            $payment = Payment::where('order_id', $order->id)->first();
            $payment->error = $this->ZartinPallErrors[$result->Status];
            $payment->save();
            echo 'ERR: ' . $this->ZartinPallErrors[$result->Status];
        }

    }

    /* صفحه برگشت از بانک */
    public function callback(Request $request)
    {
        return $this->CallBackZarinPalSendToBank($request);
    }

    /* بررسی کد تایید زرین پال */
    public function CallBackZarinPalSendToBank($request)
    {
        $payment = Payment::where('authority', $request->input('Authority'))->first();
        $order = Order::find($payment->order_id);
        if (!is_null($payment)) {
            $MerchantID = config('services.zarinpal.merchant_id');
            $Amount = $order->price; //Amount will be based on Toman
            $Authority = $request->input('Authority');


            if ($request->input('Status') == 'OK') {

//                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
                $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);
                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => $MerchantID,
                        'Authority' => $Authority,
                        'Amount' => $Amount,
                    ]
                );


                if ($result->Status == 100) {

                    // Update Order Payment
                    $payment = Payment::find($payment->id);
                    $payment->refId = $result->RefID;
                    $payment->details = 'ZarinPal';
                    $payment->status = 'paid with bank';
//
                    $payment->save();

                    // Update Order Status
                    $order = Order::find($payment->order_id);
                    $order->status = 'Pending';
                    $order->pay_status = 'paid with bank';
                    $order->save();
                    return $this->UpdateTablesAfterVirtualPaySuccess($order);

                } else {

                    // Update Order Payment
                    $payment = Payment::find($payment->id);
                    $payment->refId = $result->RefID;
                    $payment->details = 'ZarinPal';
                    $payment->status = 'paid with bank';
                    $payment->save();

                    if ($order->type == 'virtual-number') {
                        return redirect()->route('user.ShowNumber', [Encode($order->id)]);
                    } else {
                        return redirect()->route('user.single-order', [Encode($order->id)]);
                    }


                }

            } else {

                $payment->status = 'failed';
                $payment->error = 'لغو شده توسط کاربر';
                $payment->save();

                $order->pay_status = 'failed';
                $order->status = 'failed';
                $order->detail = 'لغو شده توسط کاربر';
                $order->save();
                return redirect()->route('user.single-order', Encode($order->id));

            }

        }
        $payment->status = 'failed';
        $payment->error = 'لغو شده توسط کاربر';
        $payment->save();

        $order->pay_status = 'failed';
        $order->status = 'failed';
        $order->details = 'لغو شده توسط کاربر';
        $order->save();
        return redirect()->route('user.single-order', Encode($order->id));

    }

    /* عملیات های بعد از خرید سرویس */
    public function UpdateTablesAfterVirtualPaySuccess($order)
    {
        if ($order->type == 'virtual-number') {
            $OrderItem = OrderItem::where('order_id', $order->id)->first();
            $Product = Product::find($OrderItem->product_id);
            $ProductPrice = ShowProductPrice($Product);

            if ($Product->pva_quantity > 0) {

                $Number = json_decode($this->GenerateNumber($Product), true);

                if (is_array($Number) && $Number['response'] == 1) {

                    if (UserID()->identifier != 0) {
                        $Value = $ProductPrice['price'] / 100;
                        $Value = $Product->commission * $Value;

                        $Commission = new Commission();
                        $Commission->from = UserID()->id;
                        $Commission->to = UserID()->identifier;
                        $Commission->order_id = $order->id;
                        $Commission->status = 'pending';
                        $Commission->value = $Value;
                        $Commission->save();
                    }

                    // ثبت جزئیات سفارش
                    $Detail = [
                        'number' => $Number['number'],
                        'Sms' => 0,
                        'number_id' => $Number['id'],
                        'country_code' => $Number['CountryCode'],
                        'expire_time' => Carbon::now()->addMinutes(9),
                        'type' => 'SmsPva'
                    ];

                    $OrderItem->detail = json_encode($Detail);
                    $OrderItem->save();

                    return redirect()->route('user.ShowNumber', [Encode($order->id)]);


                } else {


                    $User = User::find(UserID()->id);
                    $User->wallet = $User->wallet + $order->price;
                    $User->save();

                    $order->status = 'failed';
                    $order->detail = 'مبلغ سفارش به کیف پول عودت داده شد';
                    $order->save();

                    $Wallet = new Wallet();
                    $Wallet->user_id = UserID()->id;
                    $Wallet->type = 2;
                    $Wallet->value = $order->price;
                    $Wallet->detail = "برگشت وجه سفارش شماره $order->id به کیف پول بابت لغو شدن سفارش ";
                    $Wallet->save();

                    Session::flash('error', ['کاربر گرامی در دریافت شماره خطایی بوجود آمده. مبلغ به کیف پول شما برگشت داده شد. لطفا مجدد تلاش فرمایید']);
                    return redirect('/');

                }
            } else {

                $Number = json_decode($this->NumberLandGenerateNumber($Product), true);
                if (is_array($Number) && $Number['RESULT'] == 1) {


                    // ثبت جزئیات سفارش
                    $Detail = ['number' => $Number['NUMBER'],
                        'Sms' => 0,
                        'number_id' => $Number['ID'],
                        'country_code' => $Number['AREACODE'],
                        'expire_time' => Carbon::now()->addMinutes(Carbon::parse($Number["TIME"])->minute),
                        'type' => 'NumberLand'];

                    $OrderItem->detail = json_encode($Detail);
                    $OrderItem->save();

                    return redirect()->route('user.ShowNumber', [Encode($order->id)]);

                } else {

                    $User = User::find(UserID()->id);
                    $User->wallet = $User->wallet + $order->price;
                    $User->save();

                    $Wallet = new Wallet();
                    $Wallet->user_id = UserID()->id;
                    $Wallet->type = 2;
                    $Wallet->value = $order->price;
                    $Wallet->detail = "برگشت وجه سفارش شماره $order->id به کیف پول بابت لغو شدن سفارش ";
                    $Wallet->save();

                    $order->status = 'failed';
                    $order->detail = 'میلغ سفارش به کیف پول عودت داده شد';
                    $order->save();

                    Session::flash('error', ['کاربر گرامی در دریافت شماره خطایی بوجود آمده. مبلغ به کیف پول شما برگشت داده شد. لطفا مجدد تلاش فرمایید']);
                    return redirect('/');
                }
            }
        } elseif ($order->type == 'social-service') {
            return redirect()->route('user.single-order', [Encode($order->id)]);
        }

    }

    /**/
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

    // -- -- -- -- -- -- -- \\--------------> Charge Account Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Charge Account Payment Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Charge Account Payment Steps <-------------- // -- -- -- -- -- -- -- \\

    public function ChargeWallet(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'amount' => 'required',
            ],
            [
                'amount.required' => 'لطفا مبلغ را وارد نمایید.',
                'amount.numeric' => 'فرمت مبلغ نامعتبر میباشد. لطفا از اعداد استفاده نمایید',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Payment = new Payment();
            $Payment->user_id = UserID()->id;
            $Payment->order_id = intval(str_replace(',', '', $request->input('amount')));
            $Payment->type = 'wallet';
            $Payment->status = 'Waiting to pay';
            $Payment->gate = 'bank';
            $Payment->creator = 'System';
            $Payment->save();

            return $this->ZarinPalSendToBankChargeWallet($Payment);

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* انتقال به بانک برای شارژ حساب */
    public function ZarinPalSendToBankChargeWallet($order)
    {

        $MerchantID = config('services.zarinpal.merchant_id'); //Required
        $Amount = $order->order_id; //Amount will be based on Toman - Required
        $Description = "بابت شارژ کیف پول حساب "; // Required
        $Email = (Auth::guard('user')->user()->email != null ? Auth::guard('user')->user()->email : null);
        $Mobile = Auth::guard('user')->user()->mobile; // Optional
        $CallbackURL = route('user.ChargeWalletCallBack');

//        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);

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


//Redirect to URL You can do it also by creating a form
        if ($result->Status == 100) {

            $payment = Payment::find($order->id);
            $payment->authority = $result->Authority;
            $payment->save();

            Header('Location: https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            //برای استفاده از زرین گیت باید ادرس به صورت زیر تغییر کند:
            //Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/ZarinGate');
        } else {
            $payment = Payment::find($order->id);
            $payment->error = $this->ZartinPallErrors[$result->Status];
            $payment->save();
            echo 'ERR: ' . $this->ZartinPallErrors[$result->Status];
        }

    }

    /* صفحه برگشت از بانک برای شارژ حساب */
    public function ChargeWalletCallBack(Request $request)
    {
        return $this->CallBackZarinPalSendToBankChargeWallet($request);
    }

    /* بررسی کد تایید زرین پال برای شارژ حساب */
    public function CallBackZarinPalSendToBankChargeWallet($request)
    {
        $payment = Payment::where('authority', $request->input('Authority'))->first();

        if (!is_null($payment)) {
            $MerchantID = config('services.zarinpal.merchant_id');
            $Amount = $payment->order_id; //Amount will be based on Toman
            $Authority = $request->input('Authority');


            if ($request->input('Status') == 'OK') {

//                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
                $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);
                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => $MerchantID,
                        'Authority' => $Authority,
                        'Amount' => $Amount,
                    ]
                );


                if ($result->Status == 100) {

                    $Wallet = new Wallet();
                    $Wallet->user_id = UserID()->id;
                    $Wallet->type = 2;
                    $Wallet->value = $payment->order_id;
                    $Wallet->detail = " شارژ کیف پول از طریق درگاه بانک - کد رهگیری : $result->RefID";
                    $Wallet->save();

                    // Update Order Payment
                    $payment = Payment::find($payment->id);
                    $payment->refId = $result->RefID;
                    $payment->details = 'ZarinPal';
                    $payment->status = 'paid with bank';
                    $payment->error = $Wallet->id;
                    $payment->save();

                    $User = User::find($payment->user_id);
                    $User->wallet = $payment->order_id + $User->wallet;
                    $User->save();


                    Session::flash('success', "پرداخت با موفقیت انجام شد. کد رهگیری :$result->RefID ");
                    return redirect()->route('user.wallet-list');

                } else {


                    // Update Order Payment
                    $payment = Payment::find($payment->id);
                    $payment->refId = $result->RefID;
                    $payment->details = 'ZarinPal';
                    $payment->status = 'paid with bank';
                    $payment->save();


                    Session::flash('success', "پرداخت با موفقیت انجام شد. کد رهگیری :$result->RefID ");
                    return redirect()->route('user.wallet-list');


                }

            } else {

                $payment->status = 'failed';
                $payment->error = 'لغو شده توسط کاربر';
                $payment->save();
                Session::flash('error', ['تراکنش توسط کاربر لغو شد']);
                return redirect()->route('user.wallet-list');

            }

        }
        $payment->status = 'failed';
        $payment->error = 'لغو شده توسط کاربر';
        $payment->save();
        Session::flash('error', ['تراکنش توسط کاربر لغو شد']);
        return redirect()->route('user.wallet-list');

    }



    // -- -- -- -- -- -- -- \\--------------> Buy Sim Card Charge Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Buy Sim Card Charge Steps <-------------- // -- -- -- -- -- -- -- \\
    // -- -- -- -- -- -- -- \\--------------> Buy Sim Card Charge Steps <-------------- // -- -- -- -- -- -- -- \\


    public function buyCharge(Request $request)
    {
        $validate = [
            'mobile' => 'required|digits:11',
            'operator' => 'required|in:MTN,RTL,MCI'
        ];
        if ($request->has('operator')) {
            if ($request->input('operator') == 'MTN') {
                $validate ['mtn_charge'] = 'required|in:1000,2000,5000,10000,15000,custom';
                if ($request->has('mtn_charge') && $request->input('mtn_charge') == 'custom') {
                    $validate ['custom_charge_val'] = 'numeric';
                }
            } elseif ($request->input('operator') == 'MCI') {
                $validate ['mci_charge'] = 'required|in:1000,2000,5000,10000,15000';
            } elseif ($request->input('operator') == 'RTL') {
                $validate ['rtl_charge'] = 'required|in:1000,2000,5000,10000,15000,custom';
                if ($request->has('rtl_charge') && $request->input('rtl_charge') == 'custom') {
                    $validate ['custom_charge_val'] = 'numeric';
                }
            }
        }
        $msg =
            [
                'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
                'mobile.digits' => 'فرمت شماره موبایل نا معتبر میباشد.',
                'operator.required' => 'لطفا اپراتور را انتخاب نمایید.',
                'operator.in' => 'نوع اپراتور نا معتبر میباشد',

                'mtn_charge.required' => 'لطفا مبلغ شارژ را انتخاب نمایید',
                'mtn_charge.in' => 'نوع مبلغ نا معتبر میباشد',

                'mci_charge.required' => 'لطفا مبلغ شارژ را انتخاب نمایید',
                'mci_charge.in' => 'نوع مبلغ نا معتبر میباشد',

                'rtl_charge.required' => 'لطفا مبلغ شارژ را انتخاب نمایید',
                'rtl_charge.in' => 'نوع مبلغ نا معتبر میباشد',

                'custom_charge_val.numeric' => 'فرمت مبلغ دلخواه نا معتبر میباشد. لطفا از اعداد استفاده نمایید.',


            ];
        $vali = Validator::make($request->all(), $validate, $msg);
        if ($vali->fails()) {
            return validation_error($request, $vali->errors()->all());
        }

        try {
            if ($request->input('operator') == 'MTN') {
                if ($request->input('mtn_charge') == 'custom') {
                    $price = $request->input('custom_charge_val');
                } else {
                    $price = $request->input('mtn_charge');
                }
            } elseif ($request->input('operator') == 'RTL') {
                if ($request->input('rtl_charge') == 'custom') {
                    $price = $request->input('custom_charge_val');
                } else {
                    $price = $request->input('rtl_charge');
                }
            } elseif ($request->input('operator') == 'MCI') {
                $price = $request->input('mci_charge');
            }

            $Order = new Order();
            $Order->user_id = (Auth::guard('user')->check() ? UserID()->id : $request->input('mobile'));
            $Order->user_type = (Auth::guard('user')->check() ? null : 'guest');
            $Order->price = $price;
            $Order->status = 'Waiting to pay';
            $Order->pay_status = 'Waiting to pay';
            $Order->type = 'buy sim card charge';
            $Order->save();


            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->type = 'Factor';
            $Payment->status = 'Waiting to pay';
            $Payment->gate = 'bank';
            $Payment->save();

            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);
            $charge_type = $request->input('charge_type');
            if ($request->input('charge_type') == 0) {
                $charge_type = 'normal';
            }

            $Detail = [
                'phone_number' => $request->input('mobile'),
                'operator' => $request->input('operator'),
                'charge_type' => $charge_type,
            ];

            $OrderItem = new OrderItem();
            $OrderItem->order_id = $Order->id;
            $OrderItem->type = 'sim card charge';
            $OrderItem->price = $price;
            $OrderItem->detail = json_encode($Detail);
            $OrderItem->save();


//            return $this->ZarinPalSendToBankBuySimCardCharge($Order);
            $url = 'https://shopmajazi.com/send-to-bank/' . Encode($Order->id);
            return response()->json(['url' => $url]);


        } catch
        (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }


    }

    /* درگاه پرداخت زیرن پال */
    public function ZarinPalSendToBankBuySimCardCharge($token)
    {
        $order = Order::find(Decode($token));

//        return $this->GetCharge($order);

        $MerchantID = config('services.zarinpal.merchant_id'); //Required
        $Amount = $order->price; //Amount will be based on Toman - Required
        $Description = "بابت سفارش شماره $order->id"; // Required
        $Email = (Auth::guard('user')->check()  ? Auth::guard('user')->user()->email : null);
        $Mobile = (Auth::guard('user')->check() ? Auth::guard('user')->user()->mobile : $order->user_id); // Optional
        $CallbackURL = url('/buy-charge-callback?order='.$token);

//        $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
        $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);

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


//Redirect to URL You can do it also by creating a form
        if ($result->Status == 100) {

            $payment = Payment::where('order_id', $order->id)->first();
            $payment->authority = $result->Authority;
            $payment->save();

            Header('Location: https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
            //برای استفاده از زرین گیت باید ادرس به صورت زیر تغییر کند:
            //Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/ZarinGate');
        } else {
            $payment = Payment::where('order_id', $order->id)->first();
            $payment->error = $this->ZartinPallErrors[$result->Status];
            $payment->save();
            echo 'ERR: ' . $this->ZartinPallErrors[$result->Status];
        }

    }

    /* صفحه برگشت از بانک */
    public function callbackBankBuySimCardCharge(Request $request)
    {

        return $this->BuyChargeCheckCode($request);

    }

    /* بررسی کد تایید زرین پال */
    public function BuyChargeCheckCode($request)
    {
        $payment = Payment::where('authority', $request->input('Authority'))->first();
        $order = Order::find($payment->order_id);
        if (!is_null($payment)) {
            $MerchantID = config('services.zarinpal.merchant_id');
            $Amount = $order->price; //Amount will be based on Toman
            $Authority = $request->input('Authority');


            if ($request->input('Status') == 'OK') {

//                $client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);
                $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);
                $result = $client->PaymentVerification(
                    [
                        'MerchantID' => $MerchantID,
                        'Authority' => $Authority,
                        'Amount' => $Amount,
                    ]
                );


                if ($result->Status == 100) {

                    $Charge =  $this->GetCharge($order);

                    if ($Charge['code'] == 1){
                        // Update Order Payment

                        $payment = Payment::find($payment->id);
                        $payment->refId = $result->RefID;
                        $payment->details = 'ZarinPal';
                        $payment->status = 'paid with bank';
                        $payment->help = json_encode($Charge);
                        $payment->save();

                        // Update Order Status
                        $order = Order::find($payment->order_id);
                        $order->status = 'complete';
                        $order->pay_status = 'paid with bank';
                        $order->save();

                        $Status['status'] = 'success';
                        $Status['refID'] =  $result->RefID;
                        $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();

                        return view('site.buy-charge-callback',compact('Status'));
                    }else{
                        // Update Order Payment
                        $payment = Payment::find($payment->id);
                        $payment->refId = $result->RefID;
                        $payment->details = 'ZarinPal';
                        $payment->status = 'paid with bank';
                        $payment->save();

                        // Update Order Status
                        $order = Order::find($payment->order_id);
                        $order->status = 'failed';
                        $order->pay_status = 'paid with bank';
                        $order->detail = ( $Charge['code'] != '-33' ?  $Charge['msg'] :  'خطایی به وجود آمده لطفا با پشتیبانی تماس حاصل فرمایید.');
                        $order->save();

                        $Status['user'] = 'کاربر گرامی، بعلت خطا فوق عملیات به درستی انجام نشد. لطفا با پشتیبانی تماس حاصل فرمایید.';
                        $Status['msg'] = ( $Charge['code'] != '-33' ?  $Charge['msg'] :  'خطایی به وجود آمده لطفا با پشتیبانی تماس حاصل فرمایید.');

                        if (Auth::guard('user')->check()){
                            $User = User::find( Auth::guard('user')->user()->id);
                            $User->wallet = $User->wallet + $order->price;
                            $User->save();
                            $Status['user'] = 'کاربر گرامی، مبلغ تراکنش به کیف پول شما عودت داده شد.';
                        }

                        $Status['status'] = 'failed';
                        $Status['refID'] =  $result->RefID;
                        $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();

                        return view('site.buy-charge-callback',compact('Status'));
                    }


                } else {
                    $order = Order::find($payment->order_id);
                    if ($order->status == 'complete'){
                        // Update Order Payment
                        $payment = Payment::find($payment->id);
                        $payment->refId = $result->RefID;
                        $payment->details = 'ZarinPal';
                        $payment->status = 'paid with bank';
                        $payment->save();

                        $Status['status'] = 'success';
                        $Status['refID'] =  $result->RefID;
                        $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();
                    }elseif($order->status == 'failed'){
                        $Status['user'] = 'کاربر گرامی، بعلت خطا فوق عملیات به درستی انجام نشد. لطفا با پشتیبانی تماس حاصل فرمایید.';
                        $Status['msg'] = $order->detail ;

                        if (Auth::guard('user')->check()){
                            $Status['user'] = 'کاربر گرامی، مبلغ تراکنش به کیف پول شما عودت داده شد.';
                        }
                        $Status['status'] = 'failed';
                        $Status['refID'] =  $result->RefID;
                        $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();

                    }
                    return view('site.buy-charge-callback',compact('Status'));
                }

            } else {

                $payment->status = 'failed';
                $payment->error = 'لغو شده توسط کاربر';
                $payment->save();

                $order->pay_status = 'failed';
                $order->status = 'failed';
                $order->detail = 'لغو شده توسط کاربر';
                $order->save();



                $Status['status'] = 'failed';
                $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();
                $Status['msg'] = 'تراکنش لغو شده توسط کاربر';


                return view('site.buy-charge-callback',compact('Status'));


            }

        }
        $payment->status = 'failed';
        $payment->error = 'لغو شده توسط کاربر';
        $payment->save();

        $order->pay_status = 'failed';
        $order->status = 'failed';
        $order->details = 'لغو شده توسط کاربر';
        $order->save();

        $Status['status'] = 'failed';
        $Status['orderItem'] = OrderItem::where('order_id',$order->id)->first();
        $Status['msg'] = 'تراکنش لغو شده توسط کاربر';
        return redirect()->route('user.single-order', Encode($order->id));

    }

    /* خرید شارژ */
    public function GetCharge($order)
    {
        $OrderItem = OrderItem::where('order_id', $order->id)->first();
        $OrderDetail = json_decode($OrderItem->detail, true);

        $param = array(
            'amount' => $order->price,
            'mobile' => $OrderDetail['phone_number'],
            'operator' => $OrderDetail['operator'],
            'charge_type' => $OrderDetail['charge_type'],
            'order_id' => $order->id,
//            'callback' => url('/buy-charge-callback?order='.Encode($order->id)),
//            'test_mode'		=> $test_mode,
//            'pay_type' => 'online',
        );

        $parameters = array(
            'username' => "99755853901f9a6f93a4f6e9e0d5759a",
            'password' => "mahdi_930",
            'method' => "topup"
        );

        if (isset($param) && !empty($param)) {
            foreach ($param as $key => $value)
                $parameters[$key] = $value;
        }

        $handle = curl_init("https://inax.ir/webservice.php");
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_ENCODING, "");
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($handle);
        curl_close($handle);

        return json_decode($response,true);


    }

    /* خرید شارژ */
    public function ChargeForMom()
    {

        $param = array(
            'amount' => "5000",
            'mobile' => "09210691006",
            'operator' => "RTL",
            'charge_type' => 'normal',
            'order_id' => time(),
//            'callback' => url('/buy-charge-callback?order='.Encode($order->id)),
//            'test_mode'		=> $test_mode,
//            'pay_type' => 'online',
        );

        $parameters = array(
            'username' => "99755853901f9a6f93a4f6e9e0d5759a",
            'password' => "mahdi_930",
            'method' => "topup"
        );

        if (isset($param) && !empty($param)) {
            foreach ($param as $key => $value)
                $parameters[$key] = $value;
        }

        $handle = curl_init("https://inax.ir/webservice.php");
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_ENCODING, "");
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($handle);
        curl_close($handle);

        dd(json_decode($response,true));


    }

}
