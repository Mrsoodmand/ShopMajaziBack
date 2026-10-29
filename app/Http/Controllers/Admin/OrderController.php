<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\AppleId;
use App\Models\History;
use App\lib\PhpEncrypt;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /* صفحه سفارشات */
    public function index(Request $request)
    {
        $list = Order::orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != null) {
            $User = User::where('mobile', 'LIKE', '%' . $request->input('search') . '%')->get()->pluck('id')->toArray();
            $list->orwherein('user_id', $User);
            $list->orwhere('id', $request->input('search'));
        }
        $list = $list->paginate(50);
        return view('admin-v2.orders.index', compact('list'));
    }

    /* صفحه جزئیات سفارش */
    public function single($id)
    {
        $Jdf = new Jdf();
        $Order = Order::find($id);
        $OrderItems = OrderItem::where('order_id', $id)->get();
        $Payment = Payment::find($Order->pay_id);
        $Payments = Payment::where('order_id', $Order->id)->get();
        $OrderChats = OrderChat::where('order_id', $id)->get();
        $CountOrderChat = $OrderChats->where('type', 'client')->where('new', true)->count();
        $User = User::find($Order->user_id);
        $History = History::where('type', 'order')->where('post_id', $Order->id)->orderby('id', 'DESC')->get();
        DB::table('order_chats')->where('order_id', $Order->id)->where('type', 'client')->where('new', true)->update(['new' => false]);
        return view('admin-v2.orders.single',
            compact('Order',
                'OrderItems',
                'OrderChats',
                'CountOrderChat',
                'Payment',
                'Payments',
                'User',
                'Jdf',
                'History'));
    }

    /* ویرایش وضعیت سفارش */
    public function ChangeOrderStatus(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'status' => 'required',
            'detail' => 'required',
            'order_id' => 'required',
        ], [
            'status.required' => 'لطفا وضعیت را انتخاب نمایید.',
            'status.in' => 'وضعیت نامعتبر میباشد.',
            'detail.required' => 'لطفا علت تغییر وضعیت را بنویسید.',
            'order_id.required' => 'شناسه محصول نامعتبر میباشد.',
        ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            $Order = Order::find($request->input('order_id'));
            if (!is_null($Order)) {
                if ($Order->wallet_status != 1) {
                    $DataChanged = [
                        'old_status' => $Order->status,
                        'new_status' => $request->input('status'),
                        'detail' => $request->input('detail'),
                        'changed_date' => Carbon::now(),
                    ];

                    $Order->status = $request->input('status');
                    $Order->save();

                    $NewHistory = new History();
                    $NewHistory->type = 'order';
                    $NewHistory->post_id = $Order->id;
                    $NewHistory->detail = json_encode($DataChanged);
                    $NewHistory->admin_id = Auth::guard('admin')->user()->id;
                    $NewHistory->save();

                    $status = __('order.status.' . $request->input('status'));
                    $Pat = 138554;
                    $Data = [
                        'NUM' => $Order->id,
                        'STATUS' => $status,
                    ];

                    $User = User::find($Order->user_id);
                    sendSmsWithSmsir($User->mobile, $Data, $Pat);


                    return success($request, 'وضعیت سفارش با موفقیت تغییر پیدا کرد');
                } else {
                    return exception_error($request, 'مبلغ این فاکتور به کیف پول کاربر عودت داده شده است امکان تغییر وضعیت فاکتور وجود ندارد. ');
                }
            } else {
                return exception_error($request, 'سفارش مورد نظر یافت نشد');
            }
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* برگشت وجه به کیف پول کاربر */
    public function ChangeStatusWallet(Request $request)
    {
        $id = $request->input('order_id');
        $Order = Order::find($id);
        if (!is_null($Order)) {
            $paymentId = $request->input('payment_id');
            if ($paymentId != 0) {
                $checkPayment = Payment::where('error', $paymentId)->where('order_id', $Order->id)->first();
                if (is_null($checkPayment)) {
                    /* شارژ کیف پول کاربر */
                    $User = User::find($Order->user_id);
                    $User->wallet = $User->wallet + $Order->price;
                    $User->save();

                    /* ثبت تغییر وضعیت */
                    $DataChanged = [
                        'old_status' => $Order->status,
                        'new_status' => 'back money to wallet',
                        'detail' => $request->input('detail'),
                        'changed_by' => Auth::guard('admin')->user()->id,
                        'changed_date' => Carbon::now(),
                    ];

                    $NewHistory = new History();
                    $NewHistory->type = 'order';
                    $NewHistory->post_id = $Order->id;
                    $NewHistory->detail = json_encode($DataChanged);
                    $NewHistory->admin_id = Auth::guard('admin')->user()->id;
                    $NewHistory->save();

                    /* ثبت تراکنش جدید */
                    $Payment = new Payment();
                    $Payment->user_id = $Order->user_id;
                    $Payment->order_id = $Order->id;
                    $Payment->type = 2;
                    $Payment->status = 'Complete';
                    $Payment->creator = 'system';
                    $Payment->price = $Order->price;
                    $Payment->error = $paymentId;
                    $Payment->help = "عودت وجه شماره تراکنش $paymentId به کیف پول";
                    $Payment->save();

                    return success($request, 'عملیات با موفقیت انجام شد.');
                } else {
                    return exception_error($request, 'وجه این تراکنش قبلا عودت داده شده است.');
                }
            } else {
                return exception_error($request, 'لطفا تراکنش را انتخاب نمایید.');
            }
        } elseif ($Order->wallet_status == 1) {
            return exception_error($request, 'سفارش مورد نظر یافت نشد.');
        }
    }

    /* ارسال پیام سفارش */
    public function SendOrderChat(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'order_id' => 'required|numeric',
                'message' => 'required',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Chat = new OrderChat();
            $Chat->type = 'admin';
            $Chat->order_id = $request->input('order_id');
            $Chat->admin_id = Auth::guard('admin')->user()->id;
            $Chat->text = $request->input('message');
            $Chat->new = true;
            $Chat->save();

            return success($request, 'پیام با موفقیت ارسال شد');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }

    }

    /* تکمیل سفارش اپل آیدی */
    public function completeCustomAppleIdOrder(Request $request)
    {
        $Order = Order::find($request->input('order_id'));
        $Order->status = 'complete';
        $Order->save();

        $Appleid = [
            'appleid' => $request->input('appleid'),
            'password' => $request->input('password'),
            'brithday' => $request->input('brithday'),
            'q1' => $request->input('q1'),
            'q2' => $request->input('q2'),
            'q3' => $request->input('q3'),
        ];

        $Serial = rand(1111111111111111, 9999999999999999);

        $Encrypt = new PhpEncrypt();
        $EncryptData = $Encrypt->encrypt($Serial, $Serial, json_encode($Appleid));

        $NewAppleId = new AppleId();
        $NewAppleId->apple_id = $request->input('appleid');
        $NewAppleId->code = $EncryptData;
        $NewAppleId->serial = $Serial;
        $NewAppleId->status = 1;
        $NewAppleId->type = 2;
        $NewAppleId->order_id = $Order->id;
        $NewAppleId->user_id = $Order->user_id;
        $NewAppleId->operator_id = Auth::guard('admin')->user()->id;
        $NewAppleId->save();

        $Data = [
            'NUM' => $Order->id,
            'STATUS' => 'تکمیل شده',
        ];
        $Pat = 138554;
        $User = User::find($Order->user_id);
        sendSmsWithSmsir($User->mobile, $Data, $Pat);

        $DataChanged = [
            'old_status' => 'درحال بررسی',
            'new_status' => 'تکمیل شده',
            'detail' => 'تکمیل سفارش اپل آیدی آماده',
            'changed_date' => Carbon::now(),
        ];
        $NewHistory = new History();
        $NewHistory->type = 'order';
        $NewHistory->post_id = $Order->id;
        $NewHistory->detail = json_encode($DataChanged);
        $NewHistory->admin_id = Auth::guard('admin')->user()->id;
        $NewHistory->save();


        return back();
    }

    /* تبدیل اپل آیدی اماده به سفارشی */
    public function generateAppleId(Request $request)
    {

    }
}
