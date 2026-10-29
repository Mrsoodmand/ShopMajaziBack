<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Currency;
use App\Models\GoogleVoice;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Virtual_Products;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    public function StepOne(Request $request)
    {
        try {
            $User = Auth::guard('user')->user();
            if (is_null($User)) {
                return response()->json(['status' => 1, 'error' => ['برای پرداخت، ابتدا وارد حساب کاربری خود شوید.']], 400);
            }
            if ($User->bank_blocked == 0){
                if ($request->has('transaction_id')) {
                    $Payment = Payment::find($request->input('transaction_id'));
                    if (!is_null($Payment)) {
                        if (Auth::guard('user')->user()->id == $Payment->user_id) {
                            return $this->ZarinPalSendToBank($Payment);
                        } else {
                            return back();
                        }
                    } else {
                        return back();
                    }
                }
                else {
                    $Validate = Validator::make($request->all(),
                        [
                            'type' => 'required|numeric',
                            'product_id' => 'required|numeric',
                        ], [
                            'type.required' => 'نوع سفارش را وارد نمایید.',
                            'type.numeric' => 'فرمت نوع سفارش نامعتبر میباشد.',
                            'product_id.required' => 'شناسه محصول را وارد نمایید.',
                            'product_id.numeric' => 'فرمت شناسه محصول نامعتبر میباشد.',
                        ]);
                    if ($Validate->fails()) {
                        return response()->json(['status' => 1, 'error' => $Validate->errors()->all()], 400);
                    }
                    $AllowsType = __('order.type');
                    $AllowsType['order'] = 'سفارش';
                    $AllowsType[7] = 'خرید حجم';
                    $AllowsType[8] = 'تمدید آی پی';
                    $Type = $request->input('type');
                    if (array_key_exists($Type, $AllowsType)) {
                        $product_id = $request->input('product_id');
                        if ($Type == 1) {
                            $Product = Virtual_Products::find($product_id);
                        } elseif ($Type == 5) {
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
                        } elseif ($Type == 2 || $Type == 3 || $Type == 4) {
                            // Only the user's own (or a guest's) unpaid order; VirtualServiceBank re-assigns user_id.
                            $Product = Order::find($product_id);
                            if (!is_null($Product) && ($Product->pay_status == 'Complete' || !in_array($Product->user_id, [0, null, $User->id]))) {
                                $Product = null;
                            }
                        } elseif ($Type == 6) {
                            $Product = GoogleVoice::where('id', $product_id)->where('status', 0)->first();
                        } elseif ($Type == 7) {
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
                        } elseif ($Type == 8) {
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
                            if ($Type == 1) {
                                return $this->VirtualNumberBank($Product);
                            } elseif ($Type == 2) {
                                return $this->VirtualServiceBank($Product, $request);
                            } elseif ($Type == 3) {
                                return $this->NormalAppleId($Product, $request);
                            } elseif ($Type == 4) {
                                return $this->CustomAppleId($Product, $request);
                            } elseif ($Type == 5) {
                                return $this->IpSabetBank($Product, $request, $Price);
                            } elseif ($Type == 6) {
                                return $this->GoogleVoice($Product, $request);
                            } elseif ($Type == 7) {
                                return $this->ExtraVolume($product_id, $request);
                            } elseif ($Type == 8) {
                                return $this->reNewIpPlus($Product, $request, $Price);
                            }
                        } else {
                            return response()->json(['status' => 1, 'error' => ['سفارش یافت نشد.']], 400);
                        }
                    } else {
                        return response()->json(['status' => 1, 'error' => ['نوع سفارش نامعتبر می باشد.']], 400);
                    }
                }
            }else{
                return response()->json(['status' => 1, 'error' => ['امکان پرداخت سفارش با درگاه بانک وجود ندارد، لطفا با پشتیبانی تماس بگیرید']], 400);

            }

        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* شماره مجازی */
    public function VirtualNumberBank($Product)
    {
        if (Auth::guard('user')->check()) {
            if (Auth::guard('user')->user()->bank_blocked == 0) {
                [$Order, $Payment] = $this->createVirtualNumberBankOrder($Product);
                // v4 API: the old SOAP WebGate ('old') stopped answering in 2026 (no authority on any request since March).
                return $this->ZarinPalSendToBank($Payment, $Order, 'new');
            } else {
                return response()->json(['status' => 2, 'error' => [Auth::guard('user')->user()->error]], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای خرید شماره مجازی، ابتدا باید وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* سفارش و پرداخت شماره مجازی در انتظار بانک (also used by Api\StorefrontAccountController) */
    public function createVirtualNumberBankOrder($Product): array
    {
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

        $Order = new Order();
        $Order->user_id = UserID()->id;
        $Order->price = $Price;
        $Order->status = 'waiting to pay';
        $Order->pay_status = 'waiting to pay';
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
        $OrderItem->save();

        /* ثبت پرداختی */
        $Payment = new Payment();
        $Payment->order_id = $Order->id;
        $Payment->user_id = UserID()->id;
        $Payment->type = 1;
        $Payment->status = 'waiting to pay';
        $Payment->gate = 'Bank';
        $Payment->creator = 'System';
        $Payment->price = $Price;
        $Payment->save();

        /* ثبت شناسه پرداخت */
        DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

        return [$Order, $Payment];
    }

    /* سرویس های مجازی */
    public function VirtualServiceBank($Product, $request)
    {

        $Order = Order::find($Product->id);
        $Order->user_id = UserID()->id;
        $Order->status = 'waiting to pay';
        $Order->pay_status = 'waiting to pay';
        $Order->expire_date = Carbon::now()->addMinutes(10);
        $Order->type = 2;
        $Order->save();

        /* ثبت پرداختی */
        $Payment = Payment::find($Order->pay_id);
        $Payment->order_id = $Order->id;
        $Payment->user_id = UserID()->id;
        $Payment->type = 1;
        $Payment->status = 'waiting to pay';
        $Payment->gate = 'Bank';
        $Payment->creator = 'System';
        $Payment->save();

        /* ثبت شناسه پرداخت */
        DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

        return $this->ZarinPalSendToBank($Payment, $Order);
    }

    /* اپل آیدی آماده */
    public function NormalAppleId($OrderId, $request)
    {
        try {
            $Payment = Payment::find($OrderId->pay_id);
            return $this->ZarinPalSendToBank($Payment, $OrderId);
        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* اپل آیدی سفارش */
    public function CustomAppleId($OrderId)
    {
        try {
            $Payment = Payment::find($OrderId->pay_id);
            return $this->ZarinPalSendToBank($Payment, $OrderId);
        } catch (\Exception $exception) {
            return response()->json(['status' => 1, 'error' => [$exception->getMessage()]], 400);
        }
    }

    /* آی پی ثابت */
    public function IpSabetBank($Product, $request, $Price)
    {
        if (Auth::guard('user')->check()) {
            $Vol = $request->input('volume_id');
            $Country = Category::find($Product->category_id);
            /* ثبت سفارش */
            $Order = new Order();
            $Order->user_id = UserID()->id;
            $Order->price = $Price;
            $Order->service = $Country->name;
            $Order->status = 'waiting to pay';
            $Order->pay_status = 'waiting to pay';
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
            $OrderItem->detail = $Vol;
            $OrderItem->save();

            /* ثبت پرداختی */
            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = UserID()->id;
            $Payment->type = 1;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            /* ثبت شناسه پرداخت */
            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

            return $this->ZarinPalSendToBank($Payment, $Order);
        } else {
            return response()->json(['status' => 1, 'error' => ['برای خرید آپی ثابت، ابتدا باید وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* گوگل ویس */
    public function GoogleVoice($Product, $request)
    {
        if (Auth::guard('user')->check()) {

            $Price = Pages::find(4);
            $Price = json_decode($Price->detail, true)['custom_price'];

            /* ثبت سفارش */
            $Order = new Order();
            $Order->user_id = UserID()->id;
            $Order->price = $Price;
            $Order->status = 'waiting to pay';
            $Order->pay_status = 'waiting to pay';
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
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->paid_at = Carbon::now();
            $Payment->save();

            /* ثبت شناسه پرداخت */
            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

            return $this->ZarinPalSendToBank($Payment, $Order);
        } else {
            return response()->json(['status' => 1, 'error' => ['برای شماره گوگل ویس، ابتدا باید وارد حساب کاربری خود شوید.']], 400);

        }
    }

    /* خرید حجم اضافه */
    public function ExtraVolume($Product, $request)
    {
        if (Auth::guard('user')->check()) {
            if (Auth::guard('user')->user()->bank_blocked == 0) {
                $Ip = Ip::where('order_id', $request->input('ip_id'))->first();
                if (!is_null($Ip)) {
                    if (Auth::guard('user')->user()->id == $Ip->user_id) {
//                        if ($Ip->status == 1 || $Ip->status == -1) {
                            $VolumeId = $Product;
                            $Help =
                                [
                                    'ip_id' => $Ip->id,
                                    'volume' => $VolumeId
                                ];
                            $Price = __('order.Volume-Gb.' . $VolumeId) * 1500;
                            $Payment = new Payment();
                            $Payment->order_id = $Ip->order_id;
                            $Payment->user_id = UserID()->id;
                            $Payment->type = 6;
                            $Payment->status = 'waiting to pay';
                            $Payment->gate = 'Bank';
                            $Payment->creator = 'System';
                            $Payment->price = $Price;
                            $Payment->paid_at = Carbon::now();
                            $Payment->help = json_encode($Help);
                            $Payment->save();

                            return $this->ZarinPalSendToBank($Payment);

//                        } else {
//                            return response()->json(['status' => 2, 'error' => ['امکان خرید حجم اضافه برای این آی پی وجود ندارد.']], 400);
//                        }
                    } else {
                        return response()->json(['status' => 2, 'error' => ['این آی پی متلعق به شخص دیگری میباشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 2, 'error' => ['آی پی مورد نظر یافت نشد.']], 400);
                }
            } else {
                return response()->json(['status' => 2, 'error' => [Auth::guard('user')->user()->error]], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای خرید حجم اضافه ابتدا باید وارد حساب کاربری خود شوید.']], 400);
        }

    }

    /* تمدید آی پی */
    public function reNewIpPlus($Product, $request, $Price)
    {

        if (Auth::guard('user')->check()) {
            if (Auth::guard('user')->user()->bank_blocked == 0) {
                $Ip = Ip::where('order_id', $request->input('ip_id'))->first();
                if (!is_null($Ip)) {
                    if (Auth::guard('user')->user()->id == $Ip->user_id) {
//                        if ($Ip->status == 1 || $Ip->status == -1) {
                            $Vol = $request->input('volume_id');
                            $Help =
                                [
                                    'ip_id' => $Ip->id,
                                    'volume' => $Vol,
                                    'product_id' => $Product->id
                                ];
                            $checkPayment = Payment::where('order_id', $Ip->order_id)->where('type',8)->where('status','waiting to pay')->first();
                            if (!is_null($checkPayment)){
                                $Payment = Payment::find($checkPayment->id);
                            }else{
                                $Payment = new Payment();
                            }
                            $Payment->order_id = $Ip->order_id;
                            $Payment->user_id = UserID()->id;
                            $Payment->type = 8;
                            $Payment->status = 'waiting to pay';
                            $Payment->gate = 'Bank';
                            $Payment->creator = 'System';
                            $Payment->price = $Price;
                            $Payment->help = json_encode($Help);
                            $Payment->save();
                            Payment::where('order_id', $Ip->order_id)->where('type',8)->where('status','waiting to pay')->delete();
                            return $this->ZarinPalSendToBank($Payment);

//                        } else {
//                            return response()->json(['status' => 2, 'error' => ['امکان خرید حجم اضافه برای این آی پی وجود ندارد.']], 400);
//                        }
                    } else {
                        return response()->json(['status' => 2, 'error' => ['این آی پی متلعق به شخص دیگری میباشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 2, 'error' => ['آی پی مورد نظر یافت نشد.']], 400);
                }
            } else {
                return response()->json(['status' => 2, 'error' => [Auth::guard('user')->user()->error]], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای خرید حجم اضافه ابتدا باید وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* درگاه پرداخت زیرن پال */
    public function ZarinPalSendToBank($payment = null, $order = null, $type = 'old')
    {
        if ($type == 'new') {
            $Url = $this->zarinPalStartUrl($payment, $order);
            if ($Url) {
                Header('Location: ' . $Url);
            } else {
                echo 'ERR: ' . Payment::find($payment->id)->error;
            }
        } else {
            $MerchantID = config('services.zarinpal.merchant_id');
//            $MerchantID = config('services.zarinpal.merchant_id_alt');
            if (!is_null($payment)) {
                $Description = "بابت تراکنش شماره $payment->id";
                $Amount = $payment->price;
            } else {
                $Amount = $order->price;
                $Description = "بابت سفارش شماره $order->id";
            }
            $Email = (Auth::guard('user')->user()->email != null ? Auth::guard('user')->user()->email : null);
            $Mobile = Auth::guard('user')->user()->mobile;
            $CallbackURL = route('user.service-bank-callback');
            $client = new \SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl', ['encoding => UTF-8']);
            if (Auth::guard('admin')->check()) {
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
                if (!is_null($payment)) {
                    $Pay = $payment;
                } else {
                    $Pay = Payment::where('order_id', $order->id)->first();
                }
                $Pay->authority = $result->Authority;
                $Pay->save();
                Header('Location: https://www.zarinpal.com/pg/StartPay/' . $result->Authority);
                //برای استفاده از زرین گیت باید ادرس به صورت زیر تغییر کند:
                //Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/ZarinGate');
            } else {
                if (!is_null($payment)) {
                    $Pay = Payment::find($payment->id);
                } else {
                    $Pay = Payment::where('order_id', $order->id)->first();
                }
                $Pay->error = $this->ZartinPallErrors[$result->Status];
                $Pay->save();
                echo 'ERR: ' . $this->ZartinPallErrors[$result->Status];
            }
        }
    }

    /*
     * ZarinPal v4: registers the payment (amount in rial = toman × 10, as the callback verifies it) and returns the
     * StartPay URL to send the buyer to, or null with the reason saved on the payment. `$CallbackURL` defaults to the
     * old site's callback route; the Next.js storefront passes the same path on its own origin.
     */
    public function zarinPalStartUrl($payment, $order = null, $CallbackURL = null): ?string
    {
        $MerchantID = config('services.zarinpal.merchant_id');
        $Amount = Auth::guard('admin')->check() ? 30000 : ($order ?? $payment)->price * 10;
        $User = Auth::guard('user')->user();
        $response = ZarinPalMethod('https://api.zarinpal.com/pg/v4/payment/request.json', [
            'merchant_id' => $MerchantID,
            'amount' => $Amount,
            'description' => "بابت تراکنش شماره $payment->id",
            'callback_url' => $CallbackURL ?? route('user.service-bank-callback'),
            'metadata' => ['email' => (string)$User->email, 'mobile' => (string)$User->mobile],
        ]);
        $Pay = Payment::find($payment->id);
        if (is_array($response) && isset($response['data']['code']) && $response['data']['code'] == 100) {
            $Pay->authority = $response['data']['authority'];
            $Pay->save();
            return (DevFakeProviders() ? DevFakeZarinPalPage() : 'https://www.zarinpal.com/pg/StartPay/') . $response['data']['authority'];
        }
        $Pay->error = is_array($response) ? json_encode($response['errors'] ?? $response['error'] ?? $response) : 'ZarinPal: no response';
        $Pay->save();
        return null;
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
}
