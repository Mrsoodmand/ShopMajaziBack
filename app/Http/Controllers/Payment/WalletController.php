<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\Currency;
use App\Models\GoogleVoice;
use App\Models\History;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Virtual_Products;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /* پرداخت با کیف پول */
    public function PayWithWallet($type, $product_id, Request $request)
    {
        if (!Auth::guard('user')->check()) {
            return response()->json(['status' => 1, 'error' => ['برای پرداخت، باید وارد حساب کاربری خود شوید.']], 400);
        }
        $Lock = WalletPurchaseLock(Auth::guard('user')->id());
        if (!$Lock->get()) {
            return response()->json(['status' => 1, 'error' => ['پرداخت قبلی شما در حال انجام است، لطفا چند لحظه صبر کنید.']], 429);
        }
        try {
            Auth::guard('user')->user()->refresh(); // balance may have changed before we got the lock
            return $this->payWithWalletLocked($type, $product_id, $request);
        } finally {
            $Lock->release();
        }
    }

    // Order (types 2/3/4) the current user may pay: theirs (or a guest order) and not paid yet.
    private function payableOrder($id)
    {
        $Order = Order::find($id);
        if (is_null($Order) || $Order->pay_status == 'Complete') {
            return null;
        }
        return in_array($Order->user_id, [0, null, Auth::guard('user')->id()]) ? $Order : null;
    }

    private function payWithWalletLocked($type, $product_id, Request $request)
    {
        try {
            if (Auth::guard('user')->check()) {
                $User = Auth::guard('user')->user();
                if ($User->wallet_blocked == 0){
                    if (is_int(intval($product_id))) {
                        $AllowsType = __('order.type');
                        $AllowsType['order'] = 'سفارش';
                        $AllowsType[7] = 'خرید حجم';
                        $AllowsType[8] = 'تمدید آی پی';
                        if (array_key_exists($type, $AllowsType)) {
                            if ($type == 1) {
                                $Product = Virtual_Products::find($product_id);
                            } elseif ($type == 2 || $type == 3 || $type == 4) {
                                $Product = $this->payableOrder($product_id);
                            } elseif ($type == 5) {
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
                                $Country = Category::where('id', $request->input('country_id'))->where('type', 'pu-ip-sabet')->first();
                                if (!is_null($Country)) {
                                    $Product = Product::where('id', $request->input('plan_id'))->where('category_id', $Country->id)->first();
                                    if (!is_null($Product)) {
                                        if ($request->input('volume_id') >= 0 && $request->input('volume_id') <= 5) {
                                            $Data = json_decode($Product->detail, true);
                                            if ($Data['dis'] == 0) {
                                                $Plan_price = $Product->price;
                                            } else {
                                                $Plan_price = $Data['dis'];
                                            }
                                            $Price = __('order.Volume-Gb.' . $request->input('volume_id')) * 1500;
                                            $Price = $Plan_price + $Price;
                                        } else {
                                            return response()->json(['status' => 2, 'error' => ['حجم مورد نظر یافت نشد.']], 400);
                                        }
                                    } else {
                                        return response()->json(['status' => 2, 'error' => ['پلن مورد نظر یافت نشد.']], 400);
                                    }
                                } else {
                                    return response()->json(['status' => 2, 'error' => ['کشور مورد نظر یافت نشد.']], 400);
                                }
                            } elseif ($type == 6) {
                                $Product = GoogleVoice::where('id', $product_id)->where('status', 0)->first();
                            } elseif ($type == 7) {
                                $Validate = Validator::make($request->all(),
                                    [
                                        'ip_id' => 'required|numeric',
                                    ],
                                    [
                                        'ip_id.required' => 'لطفا آیدی آی پی را وارد نمایید..',
                                        'ip_id.numeric' => 'فرمت آیدی آی پی نامعتبر میباشد.',
                                    ]);
                                if ($Validate->fails()) {
                                    return response()->json(['status' => 2, 'error' => $Validate->errors()->all()], 400);
                                }
                                if ($product_id < 1 || $product_id > 6) {
                                    return response()->json(['status' => 2, 'error' => ['پلن مورد نظر یافت نشد.']], 400);
                                } else {
                                    $Product = [$product_id];
                                }

                            } elseif ($type == 8) {
                                $Validate = Validator::make($request->all(),
                                    [
                                        'ip_id' => 'required|numeric',
                                        'plan_id' => 'required|numeric',
                                        'volume_id' => 'required|numeric',
                                    ],
                                    [
                                        'ip_id.required' => 'شناسه آی پی',
                                        'plan_id.required' => 'لطفا پلن را انتخاب نماید.',
                                        'volume_id.required' => 'لطفا حجم را انتخاب نمایید.',
                                        'country_id.numeric' => 'فرمت کشور نامعتبر میباشد.',
                                        'plan_id.numeric' => 'فرمت تعرفه نامعتبر میباشد.',
                                        'volume_id.numeric' => 'فرمت حجم نامعتبر میباشد.',
                                    ]);
                                if ($Validate->fails()) {
                                    return response()->json(['status' => 2, 'error' => $Validate->errors()->all()], 400);
                                }
                                $Ip = Ip::where('order_id', $request->input('ip_id'))->first();

                                if (!is_null($Ip)) {
                                    if ($Ip->user_id == Auth::guard('user')->user()->id) {
                                        $Product = Product::where('id', $request->input('plan_id'))->where('category_id', $Ip->category_id)->first();
                                        if (!is_null($Product)) {
                                            if ($request->input('volume_id') >= 0 && $request->input('volume_id') <= 5) {
                                                $Data = json_decode($Product->detail, true);
                                                if ($Data['dis'] == 0) {
                                                    $Plan_price = $Product->price;
                                                } else {
                                                    $Plan_price = $Data['dis'];
                                                }
                                                $Price = __('order.Volume-Gb.' . $request->input('volume_id')) * 1500;
                                                $Price = $Plan_price + $Price;
                                            } else {
                                                return response()->json(['status' => 2, 'error' => ['حجم مورد نظر یافت نشد.']], 400);
                                            }
                                        } else {
                                            return response()->json(['status' => 2, 'error' => ['پلن مورد نظر یافت نشد.']], 400);
                                        }
                                    } else {
                                        return response()->json(['status' => 2, 'error' => ['این سفارش متعلق به شخص دیگری میباشد.']], 400);
                                    }
                                } else {
                                    return response()->json(['status' => 2, 'error' => ['سفارش مورد نظر یافت نشد.']], 400);
                                }
                            } else {
                                $Product = Product::find($product_id);
                            }
                            if (!is_null($Product)) {
                                if ($type == 1) {
                                    $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
                                    $Dollar = $Currency->where('id', 1)->first()->sell * 2;
                                    $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;
                                    if ($Product->type == 'pva') {
                                        $Price = $Dollar * $Product->dollar_price;
                                    } else {
                                        $Price = $Ruble * $Product->dollar_price;
                                    }
                                    $Price = CovertVirtualNumberPrice(number_format($Price));
                                    $Price = str_replace(',', '', $Price);
                                } elseif ($type == 2) {
                                    $Price = $Product->price;
                                } elseif ($type == 3 || $type == 4) {
                                    $Price = $Product->price;
                                } elseif ($type == 5) {
                                    $Data = [
                                        'country_id' => $request->input('country_id'),
                                        'plan_id' => $request->input('plan_id'),
                                        'volume_id' => $request->input('volume_id')
                                    ];
                                } elseif ($type == 6) {
                                    $Price = Pages::find(4);
                                    $Price = json_decode($Price->detail, true)['custom_price'];
                                } elseif ($type == 7) {
                                    $Price = __('order.Volume-Gb.' . $product_id) * 1500;
                                } elseif ($type == 8) {
                                    $Data = [
                                        'ip_id' => $request->input('ip_id'),
                                        'plan_id' => $request->input('plan_id'),
                                        'volume_id' => $request->input('volume_id')
                                    ];
                                } else {
                                    $Price = number_format($Product->price);
                                }

                                if (CheckWallet() >= $Price) {
                                    if ($type == 1) {
                                        return $this->VirtualNumber($Product, $Price);
                                    } elseif ($type == 2) {
                                        return $this->VirtualServiceCallBack($Product);
                                    } elseif ($type == 3) {
                                        return $this->NormalAppleId($Product);
                                    } elseif ($type == 4) {
                                        return $this->CustomAppleId($Product);
                                    } elseif ($type == 5) {
                                        return $this->IpSabet($Data, $Price);
                                    } elseif ($type == 6) {
                                        return $this->GoogleVoice($Product, $Price);
                                    } elseif ($type == 7) {
                                        $IpId = $request->input('ip_id');
                                        return $this->BuyExtraVolume($product_id, $IpId);
                                    } elseif ($type == 8) {
                                        return $this->reNewIpPlus($Data, $Price);
                                    } else {
                                        $Product = Product::find($product_id);
                                        $Price = number_format($Product->price);
                                    }

                                } else {
                                    return response()->json(['status' => 1, 'error' => ['موجودی شما برای پرداخت این سفارش کافی نمی باشد.']], 400);
                                }
                            } else {
                                return response()->json(['status' => 1, 'error' => ['سفارش یافت نشد.']], 400);
                            }
                        } else {
                            return response()->json(['status' => 1, 'error' => ['نوع سفارش نامعتبر می باشد.']], 400);
                        }
                    } else {
                        return response()->json(['status' => 1, 'error' => ['محصول نامعتبر می باشد.']], 400);
                    }
                }else{
                    return response()->json(['status' => 1, 'error' => ['شما مجاز به پرداخت این سفارش با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید']], 400);
                }
            } else {
                return response()->json(['status' => 1, 'error' => ['برای پرداخت، باید وارد حساب کاربری خود شوید.']], 400);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* تکمیل خرید */
    public function WalletCallBack(Request $request)
    {
        if (Session::has('Data')) {
            $Data = Session::get('Data');
            if ($Data['status'] == true) {

                $OrderId = $Data['orderId'];
                $PaymentId = $Data['paymentId'];

                $Status = true;
                $Data['Url'] = route('user.single-order', $OrderId);
                $Data['refId'] = 'wallet';
                $Data['orderId'] = $PaymentId;

                return view('user-v2.callback', compact('Status', 'Data'));
            } else {
                $PaymentId = $Data['paymentId'];
                $Msg = $Data['msg'];
                $Status = false;
                $Data['msg'] = $Msg;
                $Data['orderId'] = $PaymentId;

                return view('user-v2.callback', compact('Status', 'Data'));
            }
        } else {
            return redirect('user/home');
        }
    }

    /* شماره مجازی */
    public function VirtualNumber($Product, $Price)
    {
        try {
            $getNumber = false;
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

            if ($getNumber) {


                /* ثبت سفارش */
                $Order = new Order();
                $Order->user_id = UserID()->id;
                $Order->service = $Product->faName;
                $Order->price = $Price;
                $Order->status = 'pending';
                $Order->pay_status = 'Complete';
                $Order->expire_date = Carbon::now()->addMinutes(10);
                $Order->type = 1;
                $Order->save();

                /* ثبت جزئیات سفارش */
                $OrderItem = new OrderItem();
                $OrderItem->order_id = $Order->id;
                $OrderItem->product_id = $Product->id;
                $OrderItem->user_id = UserID()->id;
                $OrderItem->type = 1;
                $OrderItem->price = $Price;
                $OrderItem->detail = json_encode($Number);
                $OrderItem->save();

                /* ثبت پرداختی */
                $Payment = new Payment();
                $Payment->order_id = $Order->id;
                $Payment->user_id = UserID()->id;
                $Payment->type = 1;
                $Payment->status = 'Complete';
                $Payment->gate = 'Wallet';
                $Payment->creator = 'System';
                $Payment->price = $Price;
                $Payment->paid_at = Carbon::now();
                $Payment->save();

//                $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
//                if ($Product->type == 'pva') {
//                    $Dollar = $Currency->where('id', 1)->first()->sell;
//                    $BuyPrice = $Dollar * $Product->dollar_price;
//                } else {
//                    $Ruble = $Currency->where('id', 34)->first()->sell;
//                    $BuyPrice = $Ruble * $Product->dollar_price;
//                }
//                checkPartnerProfit(1, $Payment,$BuyPrice);


                $User = User::find(UserID()->id);
                DecrementWallet($Price, $User);

                $price = number_format($Order->price) . ' تومان ';
                $OrderUrl = route('admin.order single', $Order->id);
                $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
                $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
                $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
                $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: شماره مجازی
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_شماره_مجازی
";
                SendTelegramMessage($Message, -1001509696194);

                /* ثبت شناسه پرداخت */
                DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                $Data['id'] = $Order->id;
                $Data['countryImage'] = $Product->countryImage;
                $Data['serviceName'] = $Product->faName;
                $Data['number'] = $Number['number'];
                return response()->json(['data' => $Data], 200);
            } else {
                return response()->json(['status' => 1, 'error' => ['در حال حاضر شماره موجود نمیباشد، لطفا کشور یا اوپراتور را تغییر دهید.']], 400);
            }
        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* اپل آیدی آماده */
    public function NormalAppleId($OrderId)
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

            /* ثبت پرداختی */
            $Payment = Payment::find($Order->pay_id);
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 1;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            $User = User::find(UserID()->id);
            DecrementWallet($Order->price, $User);


            /* محاسبه */
            $Data = [
                'orderId' => $Order->id,
                'paymentId' => $Payment->id,
                'userId' => UserID()->id,
                'serviceType' => $Order->type,
                'paymentType' => 1,
                'amount' => $Order->price,
                'buyPrice' => 20000,
            ];
            FactorProfit($Data);

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: اپل آیدی آماده
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_اپل_آیدی_آماده
";
            SendTelegramMessage($Message, -1001509696194);

            $Url = route('user.single-order', $Order->id);
            return response()->json(['success' => 'سفارش با موفقیت پرداخت شد.', 'url' => $Url], 200);

        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* اپل آیدی سفارش */
    public function CustomAppleId($OrderId)
    {
        try {
            $Order = Order::find($OrderId->id);
            $Order->status = 'pending';
            $Order->pay_status = 'Complete';
            $Order->save();

            /* ثبت پرداختی */
            $Payment = Payment::find($Order->pay_id);
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 1;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            $User = User::find(UserID()->id);
            DecrementWallet($Order->price, $User);

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: اپل آیدی سفارشی
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_اپل_آیدی_سفارش
@mrsodmand
";
            SendTelegramMessage($Message, -1001509696194);

            /* محاسبه */
            $Data = [
                'orderId' => $Order->id,
                'paymentId' => $Payment->id,
                'userId' => UserID()->id,
                'serviceType' => $Order->type,
                'paymentType' => 1,
                'amount' => $Order->price,
                'buyPrice' => 35000,
            ];
            FactorProfit($Data);


            $Url = route('user.single-order', $Order->id);
            return response()->json(['success' => 'سفارش با موفقیت پرداخت شد.', 'url' => $Url], 200);

        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* آی پی ثابت */
    public function IpSabet($Data, $Price)
    {

        $Ip = Ip::where('type', 'pu-ip-sabet-user')->where('category_id', $Data['country_id'])->where('status', 0)->first();
        $Product = Product::find($Data['plan_id']);
        if (!is_null($Ip)) {
            $ProductDetail = json_decode($Product->detail, true);
            $Day = $ProductDetail['day'];
            $ExpireTime = Carbon::now()->addDays($Day);
            $ExtraVol = __('order.Volume-Gb.' . $Data['volume_id']);
            $Vol = $ProductDetail['volume'] + $ExtraVol;


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
            $Ip->user_id = Auth::guard('user')->user()->id;
            $Ip->product_id = $Product->id;
            $Ip->status = 1;
            $Ip->save();
            $Status = 'complete';
        } else {
            $Status = 'pending';
        }

        $Country = Category::find($Data['country_id']);

        /* ثبت سفارش */
        $Order = new Order();
        $Order->user_id = UserID()->id;
        $Order->price = $Price;
        $Order->service = $Country->name;
        $Order->status = $Status;
        $Order->pay_status = 'Complete';
        $Order->expire_date = Carbon::now()->addMinutes(10);
        $Order->type = 5;
        $Order->save();

        /* ثبت جزئیات سفارش */
        $OrderItem = new OrderItem();
        $OrderItem->order_id = $Order->id;
        $OrderItem->product_id = $Product->id;
        $OrderItem->user_id = UserID()->id;
        $OrderItem->type = 5;
        $OrderItem->price = $Price;
        $OrderItem->detail = $Data['volume_id'];
        $OrderItem->save();


        $Help =
            [
                'volume' => $ExtraVol,
                'product_id' => $Product->id
            ];

        /* ثبت پرداختی */
        $Payment = new Payment();
        $Payment->order_id = $Order->id;
        $Payment->user_id = UserID()->id;
        $Payment->type = 1;
        $Payment->status = 'Complete';
        $Payment->gate = 'Wallet';
        $Payment->creator = 'System';
        $Payment->price = $Price;
        $Payment->help = json_encode($Help);
        $Payment->paid_at = Carbon::now();
        $Payment->save();

        /* کسر وجه از کیف پول */
        $User = User::find(UserID()->id);
        DecrementWallet($Price, $User);

        if ($Status == 'complete') {
            $UpdateIp = Ip::find($Ip->id);
            $UpdateIp->order_id = $Order->id;
            $UpdateIp->save();
        }
        /* ثبت شناسه پرداخت */
        DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

        $price = number_format($Order->price) . ' تومان ';
        $OrderUrl = route('admin.order single', $Order->id);
        $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
        $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
        $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
        $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: آی پی ثابت
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_آیپی_ثابت
";
        SendTelegramMessage($Message, -1001509696194);


        /* محاسبه */
        $Data = [
            'orderId' => $Order->id,
            'paymentId' => $Payment->id,
            'userId' => UserID()->id,
            'serviceType' => $Order->type,
            'paymentType' => 1,
            'amount' => $Order->price,
            'buyPrice' => 0,
        ];
        FactorProfit($Data);

        Session::flash('Data', [
            'orderId' => $Order->id,
            'paymentId' => $Payment->id,
            'status' => true,
        ]);
        $Url = url("user/payment/wallet/callback");

        return response()->json(['success' => 'سفارش با موفقیت پرداخت شد.', 'url' => $Url], 200);
    }

    /* خرید حجم اضافه */
    public function BuyExtraVolume($VolumeId, $IpId)
    {
        $Ip = Ip::where('order_id', $IpId)->first();
        if (!is_null($Ip)) {
            if (Auth::guard('user')->user()->id == $Ip->user_id) {
                if ($Ip->status == 1) {
                    $Product = Product::find($Ip->product_id);

                    $Volume = __('order.Volume-Gb.' . $VolumeId);
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

                    $Price = __('order.Volume-Gb.' . $VolumeId) * 1500;

                    $User = User::find(UserID()->id);
                    DecrementWallet($Price, $User);

                    /* ثبت پرداختی */
                    $Vol = __('order.Volume-Gb.' . $VolumeId);

                    $Help =
                        [
                            'volume' => $Vol,
                        ];


                    $Payment = new Payment();
                    $Payment->order_id = $Ip->order_id;
                    $Payment->user_id = UserID()->id;
                    $Payment->type = 6;
                    $Payment->status = 'Complete';
                    $Payment->gate = 'Wallet';
                    $Payment->creator = 'System';
                    $Payment->price = $Price;
                    $Payment->paid_at = Carbon::now();
                    $Payment->help = json_encode($Help);
                    $Payment->save();

                    $price = number_format($Payment->price) . ' تومان ';
                    $OrderUrl = route('admin.order single', $Ip->order_id);
                    $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
                    $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
                    $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
                    $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: خرید حجم اضافه
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_حجم_اضافه
";
                    SendTelegramMessage($Message, -1001509696194);

                    /* محاسبه */
                    $Data = [
                        'orderId' => $Ip->order_id,
                        'paymentId' => $Payment->id,
                        'userId' => UserID()->id,
                        'serviceType' => 5,
                        'paymentType' => 6,
                        'amount' => $Price,
                        'buyPrice' => 0,
                    ];
                    FactorProfit($Data);

                    Session::flash('Data', [
                        'orderId' => $Ip->order_id,
                        'paymentId' => $Payment->id,
                        'status' => true,
                    ]);
                    $Url = url("user/payment/wallet/callback");

                    return response()->json(['success' => 'حجم با موفقیت خریداری شد.', 'url' => $Url], 200);

                } else {
                    Session::flash('Data', [
                        'orderId' => $Ip->order_id,
                        'msg' => 'آی پی انتخاب شده فعال نمی باشد.',
                        'status' => false,
                    ]);
                    $Url = url("user/payment/wallet/callback");
                    return response()->json(['status' => 1, 'error' => ['آی پی انتخاب شده فعال نمی باشد.'], 'url' => $Url], 400);
                }
            } else {
                Session::flash('Data', [
                    'orderId' => $Ip->order_id,
                    'msg' => 'آی پی انتخاب شده متعلق به شخص دیگری می باشد.',
                    'status' => false,
                ]);
                $Url = url("user/payment/wallet/callback");
                return response()->json(['status' => 1, 'error' => ['آی پی انتخاب شده متعلق به شخص دیگری می باشد.'], 'url' => $Url], 400);
            }
        } else {
            Session::flash('Data', [
                'orderId' => $Ip->order_id,
                'msg' => 'آی پی مورد نظر یافت نشد.',
                'status' => false,
            ]);
            $Url = url("user/payment/wallet/callback");
            return response()->json(['status' => 1, 'error' => ['آی پی مورد نظر یافت نشد.'], 'url' => $Url], 400);
        }
    }

    /* تمدید آی پی ثابت */
    public function reNewIpPlus($Data, $Price)
    {
        $Order = Order::find($Data['ip_id']);
        if (!is_null($Order)) {
            $Product = Product::find($Data['plan_id']);
            $Ip = Ip::where('order_id', $Order->id)->first();

            $ProductDetail = json_decode($Product->detail, true);
            $Day = $ProductDetail['day'];
            $ExpireTime = Carbon::parse($Ip->expire_date)->addDays($Day);
            $ExtraVol = __('order.Volume-Gb.' . $Data['volume_id']);
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

            $Help =
                [
                    'volume' => $ExtraVol,
                    'product_id' => $Product->id
                ];

            /* ثبت پرداختی */
            $checkPayment = Payment::where('order_id', $Ip->order_id)->where('type', 8)->where('status', 'waiting to pay')->first();
            if (!is_null($checkPayment)) {
                $Payment = Payment::find($checkPayment->id);
            } else {
                $Payment = new Payment();
            }
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 8;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->help = json_encode($Help);
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            $Order->status = 'complete';
            $Order->save();

            Payment::where('order_id', $Ip->order_id)->where('type', 8)->where('status', 'waiting to pay')->delete();


            /* کسر وجه از کیف پول */
            $User = User::find(UserID()->id);
            DecrementWallet($Payment->price, $User);


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
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#تمدید_آی_پی
";
            SendTelegramMessage($Message, -1001509696194);

//            /* محاسبه */
//            $Data = [
//                'orderId' => $Ip->order_id,
//                'paymentId' => $Payment->id,
//                'userId' => UserID()->id,
//                'serviceType' => 5,
//                'paymentType' => 8,
//                'amount' => $Price,
//                'buyPrice' => 0,
//            ];
//            FactorProfit($Data);

            Session::flash('Data', [
                'orderId' => $Order->id,
                'paymentId' => $Payment->id,
                'status' => true,
            ]);
            $Url = url("user/payment/wallet/callback");

            return response()->json(['success' => 'آی پی با موفقیت تمدید شد.', 'url' => $Url], 200);
        } else {
            Session::flash('Data', [
                'orderId' => $Order->id,
                'msg' => 'سفارش مورد نظر یافت نشد.',
                'status' => false,
            ]);
            $Url = url("user/payment/wallet/callback");
            return response()->json(['status' => 1, 'error' => ['آی پی مورد نظر یافت نشد.'], 'url' => $Url], 400);
        }
    }

    /* گوگل ویس */
    public function GoogleVoice($Product, $Price)
    {
        try {
            // Claim the number atomically so two buyers can't both get it.
            $Claimed = GoogleVoice::where('id', $Product->id)->where('status', 0)->update(['status' => 1, 'user_id' => UserID()->id]);
            if (!$Claimed) {
                return response()->json(['status' => 1, 'error' => ['این شماره همین الان فروخته شد، لطفا شماره دیگری انتخاب کنید.']], 400);
            }

            /* ثبت سفارش */
            $Order = new Order();
            $Order->user_id = UserID()->id;
            $Order->price = $Price;
            $Order->status = 'complete';
            $Order->pay_status = 'Complete';
            $Order->expire_date = Carbon::now()->addMinutes(10);
            $Order->type = 6;
            $Order->save();

            $Data = ['product_id' => $Product->id];
            /* ثبت جزئیات سفارش */
            $OrderItem = new OrderItem();
            $OrderItem->order_id = $Order->id;
            $OrderItem->product_id = $Product->id;
            $OrderItem->user_id = UserID()->id;
            $OrderItem->type = 6;
            $OrderItem->price = $Price;
            $OrderItem->detail = json_encode($Data);
            $OrderItem->save();

            /* ثبت پرداختی */
            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 1;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            /* ثبت شناسه پرداخت */
            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

            /* کسر وجه از کیف پول */
            $User = User::find(UserID()->id);
            DecrementWallet($Price, $User);

            GoogleVoice::where('id', $Product->id)->update(['order_id' => $Order->id]);

            $price = number_format($Order->price) . ' تومان ';
            $OrderUrl = route('admin.order single', $Order->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $OrderUrl = "<a href='$OrderUrl'>مشاهده</a>";
            $Message = "
♻️ سفارش جدیدی ثبت شد!
🔴 نوع سفارش: گوگل ویس
🏧 نوع پرداخت: کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_گوگل_ویس
";
            SendTelegramMessage($Message, -1001509696194);

            /* محاسبه */
            $Data = [
                'orderId' => $Order->id,
                'paymentId' => $Payment->id,
                'userId' => UserID()->id,
                'serviceType' => 6,
                'paymentType' => 1,
                'amount' => $Price,
                'buyPrice' => 750000,
            ];
            FactorProfit($Data);

            $Url = route('user.single-order', $Order->id);
            return response()->json(['success' => 'سفارش با موفقیت پرداخت شد.', 'url' => $Url], 200);
        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* سرویس های مجازی */
    public function VirtualServiceCallBack($Order)
    {
        try {
            $OrderItem = OrderItem::where('order_id', $Order->id)->first();

            $Order = Order::find($Order->id);
            $Order->status = 'pending';
            $Order->pay_status = 'Complete';
            $Order->save();

            /* ثبت پرداختی */
            $Payment = Payment::find($Order->pay_id);
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 1;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->paid_at = Carbon::now();
            $Payment->save();


            $OrderItem = OrderItem::where('order_id', $Order->id)->first();
            $Product = Product::find($OrderItem->product_id);
            $NameProduct = $Product->faName;


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
🔴 نوع سرویس: $NameProduct
🏧 نوع پرداخت: ;کیف پول
💵 مبلغ: $price
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده سفارش: $OrderUrl
#خرید_خدمات_مجازی
";
            SendTelegramMessage($Message, -1001509696194);

            /* کسر وجه از کیف پول */
            $User = User::find(UserID()->id);
            DecrementWallet($Payment->price, $User);

            Session::flash('Data', [
                'orderId' => $Order->id,
                'paymentId' => $Payment->id,
                'status' => true,
            ]);
            $Url = url("user/payment/wallet/callback");


            return response()->json(['success' => 'آی پی با موفقیت تمدید شد.', 'url' => $Url], 200);

        } catch (\Exception $exception) {
            Session::flash('Data', [
                'orderId' => $Order->id,
                'msg' => $exception->getMessage(),
                'status' => false,
            ]);
            $Url = url("user/payment/wallet/callback");
            return response()->json(['status' => 1, 'error' => ['خطا در انجام تراکنش'], 'url' => $Url], 400);
        }
    }
}
