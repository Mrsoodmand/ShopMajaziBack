<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\AppleId;
use App\Models\GoogleVoice;
use App\Models\Ip;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $Jdf = new Jdf();
        $list = Order::where('user_id', UserID()->id)->orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != null && $request->input('search') != "null") {
            $list->where('id', $request->input('search'));
        }
        $list = $list->paginate(20);
        return view('user-v2.orders.index', compact('list', 'Jdf'));
    }

    public function SingleOrder($id)
    {
        $id = intval($id);
        if (is_int($id)) {
            $Order = Order::find($id);
            if (!is_null($Order)) {
                if ($Order->user_id == Auth::guard('user')->user()->id) {
                    if ($Order->type == 1) {
                        return $this->VirtualNumberOrder($Order);
                    } elseif ($Order->type == 2) {
                        return $this->VirtualServiceOrder($Order);
                    } elseif ($Order->type == 3) {
                        return $this->NormalAppleId($Order);
                    } elseif ($Order->type == 4) {
                        return $this->CustomAppleId($Order);
                    } elseif ($Order->type == 5) {
                        return $this->IpSabetOrder($Order);
                    } elseif ($Order->type == 6) {
                        return $this->GoogleVoiceOrder($Order);
                    } elseif ($Order->type == 7) {
                        return $this->AccountsOrder($Order);
                    }
                } else {
                    return back();
                }
            } else {
                return back();
            }
        } else {
            return back();
        }
    }

    /* سفارشات شماره مجازی */
    public function VirtualNumberOrder($Order)
    {
        if ($Order->status == 'pending') {
            return redirect('virtual-number');
        } else {
            $OrderItems = OrderItem::where('order_id', $Order->id)->get();
            $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->where('order_id', $Order->id)->get();
            $OrderChats = OrderChat::where('order_id', $Order->id)->get();
            $Title = "جزئیات سفارش شماره $Order->id";
            $jdf = new Jdf();
            $Page = 'virtual-number';
            return view('user-v2.orders.single', compact(
                'Order',
                'OrderItems',
                'OrderPayment',
                'Title',
                'Page',
                'jdf',
                'OrderChats',
            ));
        }
    }

    /* سفارش سرویس مجازی */
    public function VirtualServiceOrder($Order)
    {
        $OrderItems = OrderItem::where('order_id', $Order->id)->first();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->orderby('id', 'DESC')->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $Product = Product::find($OrderItems->product_id);
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'virtual-service';
        return view('user-v2.orders.single', compact(
            'Order',
            'OrderPayment',
            'Product',
            'OrderItems',
            'OrderChats',
            'Title',
            'Page',
            'jdf',
        ));
    }

    /* اپل آیدی آماده */
    public function NormalAppleId($Order)
    {
        $OrderItems = OrderItem::where('order_id', $Order->id)->get();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->orderby('id', 'DESC')->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $AppleIds = AppleId::where('order_id', $Order->id)->get();
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'normal-apple-id';
        return view('user-v2.orders.single', compact(
            'Order',
            'AppleIds',
            'OrderItems',
            'OrderPayment',
            'Title',
            'Page',
            'jdf',
            'OrderChats',
        ));

    }

    /* اپل آیدی سفارش */
    public function CustomAppleId($Order)
    {
        $OrderItems = OrderItem::where('order_id', $Order->id)->get();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->orderby('id', 'DESC')->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $AppleIds = AppleId::where('order_id', $Order->id)->get();
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'custom-apple-id';
        return view('user-v2.orders.single', compact(
            'Order',
            'AppleIds',
            'OrderItems',
            'OrderPayment',
            'Title',
            'Page',
            'jdf',
            'OrderChats',
        ));

    }

    /* سفارش آی پی ثابت */
    public function IpSabetOrder($Order)
    {
        $Products = [];
        $OrderItems = OrderItem::where('order_id', $Order->id)->first();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->orderby('id', 'DESC')->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $Ip = Ip::where('order_id', $Order->id)->where('user_id', Auth::guard('user')->user()->id)->first();
        if (!is_null($Ip)){
            $Products = Product::where('category_id', $Ip->category_id)->where('type', 'pu-ip-sabet')->get();
        }
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'ip-sabet';
        return view('user-v2.orders.single', compact(
            'Order',
            'Ip',
            'Products',
            'OrderPayment',
            'OrderItems',
            'OrderChats',
            'Title',
            'Page',
            'jdf'
        ));
    }

    /* سفارش گوگل ویس */
    public function GoogleVoiceOrder($Order)
    {
        $OrderItems = OrderItem::where('order_id', $Order->id)->first();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $GoogleVoice = GoogleVoice::where('order_id', $Order->id)->where('user_id', Auth::guard('user')->user()->id)->first();
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'google-voice';
        return view('user-v2.orders.single', compact(
            'Order',
            'GoogleVoice',
            'OrderPayment',
            'OrderItems',
            'OrderChats',
            'Title',
            'Page',
            'jdf'
        ));
    }

    /* سفارش اکانت خارجی */
    public function AccountsOrder($Order)
    {
        $OrderItems = OrderItem::where('order_id', $Order->id)->first();
        $OrderPayment = Payment::where('user_id', Auth::guard('user')->user()->id)->orderby('id', 'DESC')->where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $Order->id)->get();
        $Product = Product::find($OrderItems->product_id);
        $Title = "جزئیات سفارش شماره $Order->id";
        $jdf = new Jdf();
        $Page = 'accounts';
        return view('user-v2.orders.single', compact(
            'Order',
            'OrderPayment',
            'Product',
            'OrderItems',
            'OrderChats',
            'Title',
            'Page',
            'jdf'
        ));
    }

    /* ارسال پیام سفارش */
    public function SendOrderChat(Request $request)
    {

        $validate = Validator::make($request->all(),
            [
                'order_id' => 'required',
                'message' => 'required',
            ]);
        if ($validate->fails()) {

            return validation_error($request, $validate->errors()->all());
        }

        try {
            $id = $request->input('order_id');
            $Order = Order::find($id);
            if (!is_null($Order)) {
                if (Auth::guard('user')->user()->id == $Order->user_id) {
                    $Chat = new OrderChat();
                    $Chat->type = 'user';
                    $Chat->order_id = $id;
                    $Chat->admin_id = null;
                    $Chat->text = $request->input('message');
                    $Chat->new = true;
                    $Chat->save();
                    return success($request, 'پیام با موفقیت ارسال شد');
                } else {
                    return exception_error($request, 'سفارش نامعتبر میباشد');

                }
            } else {
                return exception_error($request, 'سفارش یافت نشد. میباشد');
            }
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }

    }
}
