<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpParser\Node\Stmt\If_;

class VirtualNumberController extends Controller
{
    protected $ViewFolder = 'site-v2';

    public function Price()
    {
        $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
        $Dollar = $Currency->where('id', 1)->first()->sell * 2;
        $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;

        return $data = [
            'dollar' => $Dollar,
            'ruble' => $Ruble
        ];
    }

    /* صفحه شماره مجازی */
    public function index(Request $request)
    {
        $SelectedProduct = $Service = $Orders = $SelectedServiceImage = null;
        $Services = Virtual_Service::orderby('sort', 'ASC')->where('status', 1)->select('id', 'faName', 'image', 'sort', 'special')->get();
        $Currency = Currency::wherein('id', [1, 34])->get();
        $Dollar = $Currency->where('id', 1)->first()->sell * 2;
        $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;

        if (Auth::guard('user')->check()) {
            $Orders = Order::where('type', 1)->where('user_id', UserID()->id)->orderby('id', 'DESC')->where('status', '!=', 'waiting to pay')->take(20)->pluck('id')->toArray();
            $Orders = OrderItem::wherein('order_id', $Orders)->orderby('id', 'DESC')->get();
            if (Session::has('Product_id_Selected')) {
                $SelectedProduct = Virtual_Products::find(Session::get('Product_id_Selected'));
                if (!is_null($SelectedProduct)) {
                    $Products = Virtual_Products::where('service_id', $SelectedProduct->service_id)->select('countryName', 'dollar_price', 'countryImage', 'country_id', 'id', 'type')->where('dollar_price', '!=', 0)->get();
                }
                Session::forget('Product_id_Selected');
            } else {
                $Products = Virtual_Products::where('service_id', 50)->select('countryName', 'dollar_price', 'countryImage', 'country_id', 'id', 'type')->where('dollar_price', '!=', 0)->get();
            }
        } else {
            $Products = Virtual_Products::where('service_id', 50)->select('countryName', 'dollar_price', 'countryImage', 'country_id', 'id', 'type')->where('dollar_price', '!=', 0)->get();
        }
        $Page = Pages::find(1);
        $Comment = Comment::where('type', 'page')->where('post_id', 1)->where('status', 1)->orderby('id', 'DESC')->select('parent_id', 'name', 'text', 'created_at', 'id')->get();

        return view($this->ViewFolder . '.virtual-number.index', compact(
            'Services',
            'Products',
            'SelectedProduct',
            'SelectedServiceImage',
            'Page',
            'Dollar',
            'Orders', 'Comment',
            'Ruble'));
    }

    /* انتخاب شماره از لندینگ Next.js و باز شدن مودال خرید در صفحه خرید */
    public function Checkout($id)
    {
        $Product = Virtual_Products::where('id', intval($id))->where('status', 1)->where('toman_price', '>', 0)->first();
        if (is_null($Product)) {
            return redirect()->route('virtual-number-buy');
        }
        // index() reads this for logged-in users; guests keep it until they log in on that page
        Session::put('Product_id_Selected', $Product->id);
        return redirect()->route('virtual-number-buy');
    }

    /* دریافت لیست سرویس ها */
    public function GetNumbers(Request $request)
    {
        $Service = Virtual_Service::find($request->input('id'));
        $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
        $Dollar = $Currency->where('id', 1)->first()->sell * 2;
        $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;
        if (!is_null($Service)) {
            $Products = Virtual_Products::where('service_id', $Service->id)
                ->orderby('country_sort', 'ASC')
                ->select('countryName','toman_price', 'dollar_price', 'countryImage', 'country_id', 'id', 'type')
                ->get();
            foreach ($Products as $product) {
                if ($product->toman_price > 0) {
                    $Data[] = [
                        'name' => $product->countryName,
                        'price' => CovertVirtualNumberPrice(number_format($product->toman_price)),
                        'image' => $product->countryImage,
                        'service_id' => $product->country_id,
                        'id' => $product->id,
                    ];
                }
            }
            return response()->json(['success' => $Data], 200);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }

    /* جزئیات شماره مجازی */
    public function GetSingleNumberDetail(Request $request)
    {
        $Product = Virtual_Products::find($request->input('p_id'));
        if (Auth::guard('user')->check()) {
            if (!is_null($Product)) {
                if ($Product->status == 1) {
                    $Currency = Currency::wherein('id', [1, 34])->select('sell', 'id')->get();
                    $Dollar = $Currency->where('id', 1)->first()->sell * 2;
                    $Ruble = $Currency->where('id', 34)->first()->sell * 2.5;

                    if ($Product->type == 'pva') {
                        $Price = $Dollar * $Product->dollar_price;
                    } else {
                        $Price = $Ruble * $Product->dollar_price;
                    }
                    $Price = CovertVirtualNumberPrice(number_format($Price));

                    if (CheckWallet() >= $Price) {
                        $BTN = '<button type="button" id="second-btn" data-type="virtual-number" class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' . $Product->id . '" style="width: 47%">پرداخت با کیف پول</a>';
                    } else {
                        $BTN = '<a id="second-btn" href="' . url('user/wallet') . '"  class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>';
                    }
                    $Data['serviceName'] = $Product->faName;
                    $Data['id'] = $Product->id;
                    $Data['countryName'] = $Product->countryName;
                    $Data['price'] = $Price;
                    $Data['image'] = url($Product->countryImage);
                    $Data['btn'] = $BTN;
                    return response()->json(['data' => $Data], 200);
                } else {
                    return response()->json(['status' => 2, 'error' => ['سرویس مورد نظر غیر فعال میباشد.']], 400);
                }
            } else {
                return response()->json(['status' => 2, 'error' => ['سرویس مورد نظر یافت نشد.']], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای ادامه خرید، لطفا وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* لغو شماره مجازی */
    public function CancelNumber(Request $request)
    {
        if (Auth::guard('user')->check()) {
            if ($request->has('id')) {
                $Id = intval($request->input('id'));
                if (is_int($Id)) {
                    $Order = Order::find($Id);
                    if (!is_null($Order) && $Order->user_id == UserID()->id) {
                        if ($Order->status == 'pending') {
                            $OrderItem = OrderItem::where('order_id', $Order->id)->first();
                            if (!is_null($OrderItem)) {
                                $Data = json_decode($OrderItem->detail, true);
                                if ($Data['sms'] == null) {
                                    $Product = Virtual_Products::find($OrderItem->product_id);
                                    $Result = CancelNumber($Product->type, $Data['numberId'], $Product->service_code);
                                    if ($Result == true) {
                                        $Order->status = 'failed';
                                        $Order->save();

                                        $Payment = new Payment();
                                        $Payment->user_id = $Order->user_id;
                                        $Payment->order_id = $Order->id;
                                        $Payment->type = 2;
                                        $Payment->status = 'Complete';
                                        $Payment->creator = 'system';
                                        $Payment->help = "برگشت وجه سفارش شماره $Order->id به کیف پول بابت لغو شدن سفارش ";
                                        $Payment->price = $Order->price; // was $Product->price, a column virtual__products does not have
                                        $Payment->save();

                                        $User = User::find(UserID()->id);
                                        $User->wallet = $User->wallet + $Order->price;
                                        $User->save();
                                        return response()->json(['success' => 'شماره لغو و مبلغ به کیف پول شما عودت داده شد.'], 200);
                                    } else {
                                        return response()->json(['status' => 1, 'error' => ["خطایی بوجود آمده"]], 400);
                                    }
                                } else {
                                    $Code = $Data['sms'];
                                    $Order->status = 'complete';
                                    $Order->save();
                                    return response()->json(['status' => 1, 'error' => ["این سفارش کد دریافت کرده است، کد شما:$Code"]], 400);
                                }
                            } else {
                                return response()->json(['status' => 1, 'error' => ['خطایی پیش امده لطفا با پشتیبانی تماس حاصل فرمایید.']], 400);
                            }
                        } else {
                            return response()->json(['status' => 1, 'error' => ['امکان تغییر وضعیت این سفارش نمی باشد.']], 400);
                        }
                    } else {
                        return response()->json(['status' => 1, 'error' => ['سفارش نامعتبر میباشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 1, 'error' => ['اطلاعات ارسال شده صحیح نمیباشد.']], 400);
                }
            } else {
                return response()->json(['status' => 1, 'error' => ['اطلاعات ارسال شده صحیح نمیباشد.']], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای لغو، باید وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* چک کردن کد دریافتی */
    public function CheckNumberSms(Request $request)
    {
        if (Auth::guard('user')->check()) {
            if ($request->has('id')) {
                $Id = intval($request->input('id'));
                if (is_int($Id)) {
                    $Order = Order::find($Id);
                    if (!is_null($Order) && $Order->user_id == Auth::guard('user')->user()->id) {
                        if ($Order->status == 'pending') {
                            $Now = Carbon::now()->format('Y-m-d H:i:s');
                            if ($Order->expire_date > $Now) {
                                $OrderItem = OrderItem::where('order_id', $Order->id)->first();
                                $Detail = json_decode($OrderItem->detail, true);
                                if ($Detail['sms'] == null) {
                                    $Product = Virtual_Products::find($OrderItem->product_id);
                                    $Code = null;
                                    if ($Product->type == 'pva') {
                                        $Result = GetSmsFromPva($Product->service_code, $Product->country_code, $Detail['numberId']);
                                        if (is_array($Result) && array_key_exists('response', $Result) && $Result['response'] == 1) {
                                            $Code = $Result['sms'];

                                        }
                                    } elseif ($Product->type == 'man') {
                                        $Result = GetSmsFromMan($Detail['numberId']);
                                        if (is_array($Result) && array_key_exists('sms_code', $Result) && $Result['sms_code'] != null) {
                                            $Code = $Result['sms_code'];

                                        }
                                    } elseif ($Product->type == 'active') {
                                        $Keys = [
                                            'STATUS_WAIT_CODE', 'STATUS_WAIT_RETRY', 'STATUS_WAIT_RESEND', 'STATUS_CANCEL'
                                        ];
                                        $Result = GetSmsFromActive($Detail['numberId']);
                                        if (!in_array($Result, $Keys)) {
                                            $Code = str_replace('STATUS_OK:', '', $Result);
                                        }
                                    }
                                    if ($Code != null) {
                                        $Detail['sms'] = $Code;
                                        $OrderItem->detail = $Detail;
                                        $OrderItem->save();

                                        $Order->status = 'complete';
                                        $Order->save();
                                        return response()->json(['code' => $Code], 200);
                                    }
                                    return response()->json(['status' => 1, 'error' => ["تلاش دوباره"]], 400);
                                } else {
                                    $Order->status = 'complete';
                                    $Order->save();
                                    return response()->json(['status' => 2, 'code' => $Detail['sms']], 400);
                                }
                            } else {
                                $Order->status = 'failed';
                                $Order->wallet_status = 1;
                                $Order->save();

                                $Payment = new Payment();
                                $Payment->user_id = $Order->user_id;
                                $Payment->order_id = $Order->id;
                                $Payment->type = 2;
                                $Payment->status = 'Complete';
                                $Payment->creator = 'system';
                                $Payment->help = "برگشت وجه سفارش شماره $Order->id به کیف پول بابت لغو شدن سفارش ";
                                $Payment->price = $Order->price;
                                $Payment->save();

                                $User = User::find($Order->user_id);
                                $User->wallet = $User->wallet + $Order->price;
                                $User->save();

                                return response()->json(['status' => 3], 400);
                            }
                        } elseif ($Order->status == 'complete') {
                            $OrderItem = OrderItem::where('order_id', $Order->id)->first();
                            $Code = json_decode($OrderItem->detail, true)['sms'];
                            return response()->json(['status' => 2, 'code' => $Code], 400);
                        } else {
                            return response()->json(['status' => 3, 'error' => ["این سفارش لغو شده است"]], 400);
                        }
                    } else {
                        return response()->json(['status' => 1, 'error' => ['سفارش نامعتبر میباشد.']], 400);
                    }
                } else {
                    return response()->json(['status' => 1, 'error' => ['اطلاعات ارسال شده صحیح نمیباشد.']], 400);
                }
            } else {
                return response()->json(['status' => 1, 'error' => ['اطلاعات ارسال شده صحیح نمیباشد.']], 400);
            }
        } else {
            return response()->json(['status' => 1, 'error' => ['برای دریافت، باید وارد حساب کاربری خود شوید.']], 400);
        }
    }

    /* فیلتر کردن شماره ها */
    public function filterNumbers(Request $request)
    {
        $Service = Virtual_Service::find($request->input('id'));
        $Price = $this->Price();
        $Dollar = $Price['dollar'];
        $Ruble = $Price['ruble'];

        if ($request->input('filter') == 'h-l') {
            $orderBy = 'toman_price';
            $filter = 'DESC';
        } elseif ($request->input('filter') == 'l-h') {
            $orderBy = 'toman_price';
            $filter = 'ASC';
        } elseif ($request->input('filter') == 'n') {
            $orderBy = 'country_sort';
            $filter = 'ASC';
        }
        if (!is_null($Service)) {
            $Products = Virtual_Products::where('service_id', $Service->id)
                ->select('countryName', 'dollar_price', 'countryImage', 'country_id', 'id', 'type','toman_price','country_sort')
                ->orderby($orderBy, $filter)
                ->get();
            foreach ($Products as $product) {
                if ($product->toman_price > 0) {
                    $Data[] = [
                        'id' => $product->id,
                        'name' => $product->countryName,
                        'price' => CovertVirtualNumberPrice(number_format($product->toman_price)),
                        'image' => $product->countryImage,
                        'service_id' => $product->country_id,
                    ];
                }
            }
            return response()->json(['success' => $Data], 200);
        } else {
            return response()->json(['error' => ['کشور مورد نظر یافت نشد']], 400);
        }
    }
}
