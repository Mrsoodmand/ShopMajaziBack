<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Product;
use App\Models\Plan;
use App\Models\Currency;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function Factor($id, Request $request)
    {
        if ($id == 1) {
            return $this->SubmitAppleId($request);
        }elseif ($id == 2){
            return $this->SubmitVirtualService($request);
        }elseif ($id == 3){
            return $this->submitExternalAccount($request);
        }
    }

    /* ثبت سفارش اپل آیدی */
    public function SubmitAppleId($request)
    {
        if (Auth::guard('user')->check()) {
            $Validate = Validator::make($request->all(),
                ['type' => 'required|in:1,2'],
                [
                    'type.required' => 'لطفا نوع اپل آیدی را انتخاب نمایید.',
                    'type.in' => 'نوع اپل آیدی نامعتبر میباشد.'
                ]);
            if ($Validate->fails()) {
                return validation_error($request, $Validate->errors()->all());
            }
            try {
                $Page = Pages::find(3);
                $Price = json_decode($Page->detail, true);
                if ($request->input('type') == 1) {
                    $Validate = Validator::make($request->all(),
                        ['quantity' => 'required|min:1|max:10|numeric'],
                        [
                            'quantity.required' => 'لطفا مقدار را وارد نمایید.',
                            'quantity.min' => 'حداقل مقدار مجازی 1 عدد میباشد.',
                            'quantity.max' => 'حداکثر مقدار مجاز 10 عدد میباشد.',
                            'quantity.numeric' => 'فرمت تعداد نامعتبر میباشد.',
                        ]
                    );
                    if ($Validate->fails()) {
                        return validation_error($request, $Validate->errors()->all());
                    }
                    $Price = $Price['normal_price'] * $request->input('quantity');
                    $Order = new Order();
                    $Order->type = 3;
                    $Order->status = 'waiting to pay';
                    $Order->pay_status = 'waiting to pay';
                    $Order->price = $Price;
                    if (Auth::guard('user')->check()) {
                        $Order->user_id = UserID()->id;
                    } else {
                        $Order->user_id = 0;
                        $Order->user_number = $request->input('mobile');
                    }
                    $Order->expire_date = Carbon::now()->addMinutes(60);
                    $Order->save();

                    /* ثبت جزئیات سفارش */
                    $OrderItem = new OrderItem();
                    $OrderItem->order_id = $Order->id;
                    $OrderItem->product_id = null;
                    $OrderItem->user_id = null;
                    $OrderItem->type = 3;
                    $OrderItem->price = $Price;
                    $OrderItem->detail = $request->input('quantity');
                    $OrderItem->save();

                    /* ثبت پرداختی */
                    $Payment = new Payment();
                    $Payment->order_id = $Order->id;
                    if (Auth::guard('user')->check()) {
                        $Payment->user_id = UserID()->id;
                    } else {
                        $Payment->user_id = 0;
                    }
                    $Payment->user_id = Auth::guard('user')->user()->id;
                    $Payment->type = 1;
                    $Payment->status = 'waiting to pay';
                    $Payment->gate = 'Bank';
                    $Payment->creator = 'System';
                    $Payment->price = $Price;
                    $Payment->save();

                    /* ثبت شناسه پرداخت */
                    DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                    if (CheckWallet() >= $Price) {
                        $BTN = '<button type="button" id="second-btn" data-type="virtual-number" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' . $Order->id . '"  style="width: 47%">پرداخت با کیف پول</a>';
                    } else {
                        $BTN = '<a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                    }
                    return response()->json(['success' => 'سفارش با موفقیت ثبت شد.', 'name' => 'اپل آیدی آماده', 'price' => number_format($Price) . " تومان ", 'btn' => $BTN, 'product_id' => $Order->id, 'type' => 3]);
                } else {
                    $Validate = Validator::make($request->all(),
                        [
                            'name' => 'required|string|min:3|max:50',
                            'family' => 'required|string|min:3|max:50',
                            'email' => 'required|email|min:3|max:50',
                            'b_day' => 'required|string|max:30',
                        ],
                        [
                            'name.required' => 'لطفا فیلد نام را وارد نمایید.',
                            'name.string' => 'فرمت نام، نامعتبر میباشد.',
                            'name.min' => 'حداقل 3 کاراکتر مجاز برای نام',
                            'name.max' => 'حداکثر 50 کاراکتر مجاز برای نام',

                            'family.required' => 'لطفا فیلد نام خانوادگی را وارد نمایید.',
                            'family.string' => 'فرمت نام خانوادگی، نامعتبر میباشد.',
                            'family.min' => 'حداقل 3 کاراکتر مجاز برای نام خانوادگی',
                            'family.max' => 'حداکثر 50 کاراکتر مجاز برای نام خانوادگی',

                            'email.required' => 'لطفا فیلد ایمیل را وارد نمایید.',
                            'email.email' => 'فرمت ایمیل، نامعتبر میباشد.',
                            'email.min' => 'حداقل 3 کاراکتر مجاز برای ایمیل',
                            'email.max' => 'حداکثر 50 کاراکتر مجاز برای ایمیل',

                            'b_day.required' => 'لطفا فیلد تاریخ تولد را وارد نمایید.',
                            'b_day.string' => 'فرمت تاریخ تولد، نامعتبر میباشد.',
                            'b_day.max' => 'حداکثر 30 کاراکتر مجاز برای تاریخ تولد',
                        ]
                    );
                    if ($Validate->fails()) {
                        return validation_error($request, $Validate->errors()->all());
                    }
                    $Price = $Price['custom_price'];
                    $Order = new Order();
                    $Order->type = 4;
                    $Order->status = 'waiting to pay';
                    $Order->pay_status = 'waiting to pay';
                    $Order->price = $Price;
                    if (Auth::guard('user')->check()) {
                        $Order->user_id = UserID()->id;
                    } else {
                        $Order->user_id = 0;
                        $Order->user_number = $request->input('mobile');
                    }
                    $Order->expire_date = Carbon::now()->addMinutes(60);
                    $Order->save();

                    $Data = [
                        'name' => $request->input('name'),
                        'family' => $request->input('family'),
                        'email' => $request->input('email'),
                        'b_day' => $request->input('b_day'),
                    ];
                    /* ثبت جزئیات سفارش */
                    $OrderItem = new OrderItem();
                    $OrderItem->order_id = $Order->id;
                    $OrderItem->product_id = null;
                    $OrderItem->user_id = null;
                    $OrderItem->type = 3;
                    $OrderItem->price = $Price;
                    $OrderItem->detail = json_encode($Data);
                    $OrderItem->save();

                    /* ثبت پرداختی */
                    $Payment = new Payment();
                    $Payment->order_id = $Order->id;
                    $Payment->type = 1;
                    $Payment->status = 'waiting to pay';
                    $Payment->gate = 'Bank';
                    $Payment->creator = 'System';
                    $Payment->price = $Price;
                    $Payment->save();

                    /* ثبت شناسه پرداخت */
                    DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                    if (Auth::guard('user')->user()->wallet >= $Price) {
                        $BTN = '<button type="button" id="second-btn" data-type="virtual-number" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' . $Order->id . '"  style="width: 47%">پرداخت با کیف پول</a>';
                    } else {
                        $BTN = '<a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                    }
                    return response()->json(['success' => 'سفارش با موفقیت ثبت شد.', 'name' => 'اپل آیدی سفارشی', 'price' => number_format($Price) . " تومان ", 'btn' => $BTN, 'product_id' => $Order->id, 'type' => 4]);
                }
            } catch (\Exception $exception) {
                return response()->json(['status' => 2, 'error' => [$exception->getMessage()]], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['لطفا ابتدا وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* ثبت سفارش خدمات مجازی */
    public function SubmitVirtualService($request)
    {
        if (Auth::guard('user')->check() && $request->filled('count')) {
            return $this->SubmitVirtualServiceCount($request);
        }
        if (Auth::guard('user')->check()) {
            try {

                $Product = $request->input('product_id') ;
                $Plane = Decode($request->input('plane_id')) ;
                if ($Product != 'error') {
                    if ($Plane != 'error') {
                        $CheckProduct = Product::find($Product);
                        $CheckPlan = Plan::where('product_id', $Product)->where('id', $Plane)->where('status', 1)->first();
                        if (!is_null($CheckPlan)) {
                            $ProductDetail = json_decode($CheckProduct->detail, true);
                            $Price = $CheckPlan->price;
                            $Currency = CalculatorSellPrice(Currency::find(20));

                            $Price = $Currency * $Price;
                            if ($CheckPlan->discount != null && $CheckPlan->discount != 0) {
                                $Price = $CheckPlan->discount  * $Currency ;
                            }


                            $Files = json_decode($CheckProduct->filds, true);
                            foreach ($Files as $value) {
                                $Title = $value['title'];
                                if ($value['type'] == 'text') {
                                    $Validate['filed_value.' . $value['filed_name']] = 'required|max:150|min:3';
                                    $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                                    $Message['filed_value.' . $value['filed_name'] . '.max'] = "حداکثر کاراکتر مجاز 150 کاراکتر برای $Title میباشد ";
                                    $Message['filed_value.' . $value['filed_name'] . '.min'] = "حداقل کاراکتر مجاز 3 کاراکتر برای $Title میباشد ";
                                } elseif ($value['type'] == 'select_box') {
                                    $SelectData = $value['select_value'];
                                    $Validate['filed_value.' . $value['filed_name']] = "required|in:$SelectData";
                                    $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                                    $Message['filed_value.' . $value['filed_name'] . '.in'] = "مقدار وارد شده برای $Title نامعتبر میباشد ";
                                }
                            }
                            $Validator = Validator::make($request->all(), $Validate, $Message);
                            if ($Validator->fails()) {
                                return validation_error($request, $Validator->errors()->all());
                            }

                            foreach ($request->input('filed_name') as $key => $value) {
                                $Detail[] = [
                                    'filed_name' => $value,
                                    'filed_type' => $request->input('filed_title')[$key],
                                    'filed_value' => $request->input('filed_value')[$key],
                                ];
                            }
                            $Detail['plan'] = [
                                'plan_id' => $CheckPlan->id,
                                'plan_name' => $CheckPlan->name,
                            ];

                            $perUser = floatval($CheckProduct->price / 1000);
                            $price = $perUser * $CheckPlan->count;
                            $Price = str_replace(',','',CovertVirtualNumberPrice(number_format($price * $Currency)));

                            $Order = new Order();
                            $Order->type = 2;
                            $Order->status = 'waiting to pay';
                            $Order->pay_status = 'waiting to pay';
                            $Order->price = $Price;
                            if (Auth::guard('user')->check()) {
                                $Order->user_id = UserID()->id;
                            } else {
                                $Order->user_id = 0;
                                $Order->user_number = $request->input('mobile');
                            }
                            $Order->expire_date = Carbon::now()->addMinutes(60);
                            $Order->save();

                            /* ثبت جزئیات سفارش */
                            $OrderItem = new OrderItem();
                            $OrderItem->order_id = $Order->id;
                            $OrderItem->product_id = $CheckProduct->id;
                            $OrderItem->user_id = null;
                            $OrderItem->type = 2;
                            $OrderItem->price = $Price;
                            $OrderItem->detail = json_encode($Detail);
                            $OrderItem->save();

                            /* ثبت پرداختی */
                            $Payment = new Payment();
                            $Payment->order_id = $Order->id;
                            if (Auth::guard('user')->check()) {
                                $Payment->user_id = UserID()->id;
                            } else {
                                $Payment->user_id = 0;
                            }
                            $Payment->user_id = Auth::guard('user')->user()->id;
                            $Payment->type = 1;
                            $Payment->status = 'waiting to pay';
                            $Payment->gate = 'Bank';
                            $Payment->creator = 'System';
                            $Payment->price = $Price;
                            $Payment->save();
$Name = $ProductDetail['name'];
                            /* ثبت شناسه پرداخت */
                            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                            if (CheckWallet() >= $Price) {
                                $BTN = '<button type="button" id="second-btn" data-type="virtual-service" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' . $Order->id . '"  style="width: 47%">پرداخت با کیف پول</a>';
                            } else {
                                $BTN = '<a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                            }
                            return response()->json(['success' => 'سفارش با موفقیت ثبت شد.', 'name' => $Name, 'price' => number_format($Price) . " تومان ", 'btn' => $BTN, 'product_id' => $Order->id, 'type' => 3],200);
                        }
                        return exception_error($request, 'پلن مورد نظر یافت نشد');
                    } else {
                        return exception_error($request, 'شناسه پلن نا معبتر میباشد');
                    }
                } else {
                    return exception_error($request, 'شناسه محصول نا معبتر میباشد');
                }
            } catch (\Exception $exception) {
                report($exception);
                return exception_error($request, 'خطایی در ثبت سفارش رخ داد، لطفا دوباره تلاش کنید.');
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['لطفا ابتدا وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* ثبت سفارش خدمات مجازی با تعداد دلخواه: قیمت = تعداد ÷ ۱۰۰ × price_per_100 */
    private function SubmitVirtualServiceCount($request)
    {
        try {
            $CheckProduct = Product::where('id', intval($request->input('product_id')))
                ->where('type', 'service')->where('status', 1)->whereNotNull('price_per_100')->first();
            if (is_null($CheckProduct)) {
                return exception_error($request, 'این سرویس امکان انتخاب تعداد دلخواه ندارد.');
            }

            $Min = (int)$CheckProduct->min_count;
            $Max = (int)$CheckProduct->max_count;
            $Validate = [
                'count' => "required|integer|min:$Min|max:$Max",
            ];
            $Message = [
                'count.required' => 'لطفا تعداد را انتخاب نمایید.',
                'count.integer' => 'تعداد نامعتبر میباشد.',
                'count.min' => 'حداقل تعداد ' . number_format($Min) . ' عدد میباشد.',
                'count.max' => 'حداکثر تعداد ' . number_format($Max) . ' عدد میباشد.',
            ];
            $Files = json_decode($CheckProduct->filds, true) ?? [];
            foreach ($Files as $value) {
                $Title = $value['title'];
                $Key = 'filed_value.' . $value['filed_name'];
                if ($value['type'] == 'text') {
                    $Validate[$Key] = 'required|max:150|min:3';
                    $Message[$Key . '.required'] = "لطفا $Title را وارد نمایید";
                    $Message[$Key . '.max'] = "حداکثر کاراکتر مجاز 150 کاراکتر برای $Title میباشد ";
                    $Message[$Key . '.min'] = "حداقل کاراکتر مجاز 3 کاراکتر برای $Title میباشد ";
                } elseif ($value['type'] == 'select_box') {
                    $Validate[$Key] = 'required|in:' . $value['select_value'];
                    $Message[$Key . '.required'] = "لطفا $Title را وارد نمایید";
                    $Message[$Key . '.in'] = "مقدار وارد شده برای $Title نامعتبر میباشد ";
                }
            }
            $Validator = Validator::make($request->all(), $Validate, $Message);
            if ($Validator->fails()) {
                return validation_error($request, $Validator->errors()->all());
            }

            $Count = intval($request->input('count'));
            if ($Count % 100 != 0) {
                return exception_error($request, 'تعداد باید مضربی از ۱۰۰ باشد.');
            }

            // Fields come from the product definition, never from the request (names/titles can't be spoofed).
            $Detail = [];
            foreach ($Files as $value) {
                $Detail[] = [
                    'filed_name' => $value['filed_name'],
                    'filed_type' => $value['title'],
                    'filed_value' => $request->input('filed_value')[$value['filed_name']],
                ];
            }
            $Detail['plan'] = [
                'plan_id' => null,
                'plan_name' => number_format($Count) . ' عدد',
                'count' => $Count,
            ];

            $Price = intval($Count / 100 * $CheckProduct->price_per_100);
            $User = Auth::guard('user')->user();

            $Order = new Order();
            $Order->type = 2;
            $Order->status = 'waiting to pay';
            $Order->pay_status = 'waiting to pay';
            $Order->price = $Price;
            $Order->user_id = $User->id;
            $Order->expire_date = Carbon::now()->addMinutes(60);
            $Order->save();

            $OrderItem = new OrderItem();
            $OrderItem->order_id = $Order->id;
            $OrderItem->product_id = $CheckProduct->id;
            $OrderItem->user_id = null;
            $OrderItem->type = 2;
            $OrderItem->price = $Price;
            $OrderItem->detail = json_encode($Detail);
            $OrderItem->save();

            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = $User->id;
            $Payment->type = 1;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Price;
            $Payment->save();

            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

            $Name = (json_decode($CheckProduct->detail, true)['name'] ?? $CheckProduct->faName) . ' - ' . number_format($Count) . ' عدد';
            return response()->json([
                'success' => 'سفارش با موفقیت ثبت شد.',
                'name' => $Name,
                'price' => number_format($Price) . ' تومان ',
                'amount' => $Price,
                'wallet' => CheckWallet() >= $Price,
                'product_id' => $Order->id,
                'type' => 2,
            ], 200);
        } catch (\Exception $exception) {
            report($exception);
            return exception_error($request, 'خطایی در ثبت سفارش رخ داد، لطفا دوباره تلاش کنید.');
        }
    }

    /* ثبت سفارش اکانت خارجی */
    public function submitExternalAccount($request)
    {
        if (Auth::guard('user')->check()) {
            try {

                $Product = $request->input('product_id') ;
                $Plane = Decode($request->input('plane_id')) ;

                if ($Product != 'error') {
                    if ($Plane != 'error') {
                        $CheckProduct = Product::find($Product);
                        $CheckPlan = Plan::where('product_id', $Product)->where('id', $Plane)->where('status', 1)->first();

                        if (!is_null($CheckPlan)) {
                            $ProductDetail = json_decode($CheckProduct->detail, true);

                            $Price = $CheckPlan->price;

                            $Currency = CalculatorSellPrice(Currency::find(20));

                            $Price = $Currency * $Price;

                            if ($CheckPlan->discount != null && $CheckPlan->discount != 0) {
                                $Price = $CheckPlan->discount  * $Currency ;
                            }

                            $Price = str_replace(',','',CovertVirtualNumberPrice(number_format($Price )));


                            $Files = json_decode($CheckProduct->filds, true);
                            foreach ($Files as $value) {
                                $Title = $value['title'];
                                if ($value['type'] == 'text') {
                                    $Validate['filed_value.' . $value['filed_name']] = 'required|max:150|min:3';
                                    $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                                    $Message['filed_value.' . $value['filed_name'] . '.max'] = "حداکثر کاراکتر مجاز 150 کاراکتر برای $Title میباشد ";
                                    $Message['filed_value.' . $value['filed_name'] . '.min'] = "حداقل کاراکتر مجاز 3 کاراکتر برای $Title میباشد ";
                                } elseif ($value['type'] == 'select_box') {
                                    $SelectData = $value['select_value'];
                                    $Validate['filed_value.' . $value['filed_name']] = "required|in:$SelectData";
                                    $Message['filed_value.' . $value['filed_name'] . '.required'] = "لطفا $Title را وارد نمایید";
                                    $Message['filed_value.' . $value['filed_name'] . '.in'] = "مقدار وارد شده برای $Title نامعتبر میباشد ";
                                }
                            }
                            $Validator = Validator::make($request->all(), $Validate, $Message);
                            if ($Validator->fails()) {
                                return validation_error($request, $Validator->errors()->all());
                            }

                            foreach ($request->input('filed_name') as $key => $value) {
                                $Detail[] = [
                                    'filed_name' => $value,
                                    'filed_type' => $request->input('filed_title')[$key],
                                    'filed_value' => $request->input('filed_value')[$key],
                                ];
                            }
                            $Detail['plan'] = [
                                'plan_id' => $CheckPlan->id,
                                'plan_name' => $CheckPlan->name,
                            ];

                            $Order = new Order();
                            $Order->type = 7;
                            $Order->status = 'waiting to pay';
                            $Order->pay_status = 'waiting to pay';
                            $Order->price = $Price;
                            if (Auth::guard('user')->check()) {
                                $Order->user_id = UserID()->id;
                            } else {
                                $Order->user_id = 0;
                                $Order->user_number = $request->input('mobile');
                            }
                            $Order->expire_date = Carbon::now()->addMinutes(60);
                            $Order->product_id = $CheckProduct->id;
                            $Order->save();

                            /* ثبت جزئیات سفارش */
                            $OrderItem = new OrderItem();
                            $OrderItem->order_id = $Order->id;
                            $OrderItem->product_id = $CheckProduct->id;
                            $OrderItem->user_id = null;
                            $OrderItem->type = 7;
                            $OrderItem->price = $Price;
                            $OrderItem->detail = json_encode($Detail);
                            $OrderItem->save();

                            /* ثبت پرداختی */
                            $Payment = new Payment();
                            $Payment->order_id = $Order->id;
                            if (Auth::guard('user')->check()) {
                                $Payment->user_id = UserID()->id;
                            } else {
                                $Payment->user_id = 0;
                            }
                            $Payment->user_id = Auth::guard('user')->user()->id;
                            $Payment->type = 1;
                            $Payment->status = 'waiting to pay';
                            $Payment->gate = 'Bank';
                            $Payment->creator = 'System';
                            $Payment->price = $Price;
                            $Payment->save();
                            $Name = $ProductDetail['name'];
                            /* ثبت شناسه پرداخت */
                            DB::table('orders')->where('id', $Order->id)->update(['pay_id' => $Payment->id]);

                            if (CheckWallet() >= $Price) {
                                $BTN = '<button type="button" id="second-btn" data-type="virtual-service" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' . $Order->id . '"  style="width: 47%">پرداخت با کیف پول</a>';
                            } else {
                                $BTN = '<a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                            }
                            return response()->json(['success' => 'سفارش با موفقیت ثبت شد.', 'name' => $Name, 'price' => number_format($Price) . " تومان ", 'btn' => $BTN, 'product_id' => $Order->id, 'type' => 3],200);
                        }
                        return exception_error($request, 'پلن مورد نظر یافت نشد');
                    } else {
                        return exception_error($request, 'شناسه پلن نا معبتر میباشد');
                    }
                } else {
                    return exception_error($request, 'شناسه محصول نا معبتر میباشد');
                }
            } catch (\Exception $exception) {
                exception_error($request, $exception->getMessage());
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['لطفا ابتدا وارد حساب کاربری خود شوید.']], 400);

        }
    }

}
