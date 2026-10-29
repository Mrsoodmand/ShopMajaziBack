<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Imports\AppleIdImport;
use Maatwebsite\Excel\Facades\Excel;

class AppleIdController extends Controller
{
    /* لیست اپل آیدی ها */
    public function index(Request $request)
    {
        $list = AppleId::orderby('id', 'DESC');
        if ($request->has('type') && $request->input('type') != 0) {
            $list->where('type', $request->input('type'));
        }
        if ($request->has('apple_id') && $request->input('apple_id') != ' ' && $request->input('apple_id') != null) {
            $list->where('apple_id', 'LIKE', '%' . $request->input('apple_id') . '%');
        }
        $list = $list->paginate(50);
        $jdf = new Jdf();
        return view('admin-v2.apple-id.index', compact('list', 'jdf'));
    }

    /* ایجاد اپل آیدی */
    public function create()
    {
        $AppleId = AppleId::where('type', 1)->where('status', '0')->select('id', 'code', 'serial')->get();
        $Setting = Pages::find(3);
        $Decrypt = new PhpEncrypt();
        return view('admin-v2.apple-id.create', compact('AppleId', 'Setting', 'Decrypt'));
    }

    /* ثبت اپل آیدی آماده */
    public function submit(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'custom_price' => 'required',
                'normal_price' => 'required',
                'short_detail' => 'required',
                'detail' => 'required',
                'appleid' => 'required',
                'password' => 'required',
                'brithday' => 'required',
                'id' => 'required',
                'q1' => 'required',
                'q2' => 'required',
                'q3' => 'required',
            ], [

                'h1.required' => 'لطفا تگ را وارد نمایید.',
                'custom_price.required' => 'لطفا قیمت اپل آیدی سفارشی را وارد نمایید.',
                'normal_price.required' => 'لطفا قیمت اپل آیدی آماده را وارد نمایید.',
                'short_detail.required' => 'لطفا توضیحات کوتاه را وارد نمایید.',
                'detail.required' => 'لطفا جزئیات کامل را وارد نمایید.',
                'appleid.required' => 'لطفا اپل آیدی را وارد نمایید.',
                'id.required' => 'لطفا شناسه اپل را وارد نمایید.',
                'password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                'brithday.required' => 'لطفا تاریخ تولد را وارد نمایید.',
                'q1.required' => 'لطفا سوالات امنیتی 1 را وارد نمایید.',
                'q2.required' => 'لطفا سوالات امنیتی 2 را وارد نمایید.',
                'q3.required' => 'لطفا سوالات امنیتی 3 را وارد نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {

            $Seo = Add_Seo($request);
            $Faq = Add_Faq($request);
            $Price = [
                'custom_price' => $request->input('custom_price'),
                'normal_price' => $request->input('normal_price')
            ];
            $Page = Pages::find(3);
            $Page->h1 = $request->input('h1');
            $Page->short_detail = $request->input('short_detail');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->faq = $Faq;
            $Page->seo = $Seo;
            $Page->detail = json_encode($Price);
            $Page->save();

            $Encrypt = new PhpEncrypt();
            if ($request->has('appleid')) {
                foreach ($request->input('appleid') as $key => $value) {
                    $Appleid = [
                        'appleid' => $value,
                        'password' => $request->input('password')[$key],
                        'brithday' => $request->input('brithday')[$key],
                        'q1' => $request->input('q1')[$key],
                        'q2' => $request->input('q2')[$key],
                        'q3' => $request->input('q3')[$key],
                    ];
                    $Serial = rand(1111111111111111, 9999999999999999);
                    $EncryptData = $Encrypt->encrypt($Serial, $Serial, json_encode($Appleid));
                    if ($request->input('id')[$key] == 0) {
                        $NewAppleId = new AppleId();
                    } else {
                        $NewAppleId = AppleId::find($request->input('id')[$key]);
                    }
                    $NewAppleId->apple_id = $value;
                    $NewAppleId->code = $EncryptData;
                    $NewAppleId->serial = $Serial;
                    $NewAppleId->status = 0;
                    $NewAppleId->type = 1;
                    $NewAppleId->operator_id = Auth::guard('admin')->user()->id;
                    $NewAppleId->save();

                    $AppleIds[] = $NewAppleId->id;
                }

                AppleId::where('status', 0)->where('type', 1)->wherenotIn('id', $AppleIds)->delete();
                $Orders = Order::where('type', 3)->where('status', 'pending')->pluck('id');
                $OrderItems = OrderItem::wherein('order_id', $Orders)->get();
                foreach ($OrderItems as $orderItem) {
                    $GivenProducts = AppleId::where('order_id', $orderItem->order_id)->where('status', 1)->count();

                    $LeftQuantity = intval($orderItem->detail) - $GivenProducts;
                    $GiveAppleId = AppleId::where('status', 0)->take($LeftQuantity)->get();

                    foreach ($GiveAppleId as $item) {
                        $UpdateAppleId = AppleId::find($item->id);
                        $UpdateAppleId->status = 1;
                        $UpdateAppleId->order_id = $orderItem->order_id;
                        $UpdateAppleId->user_id = $orderItem->Order->user_id;
                        $UpdateAppleId->save();

                        $UsedAppleId[] = $UpdateAppleId->id;
                    }
                    $GivenProducts = AppleId::where('order_id', $orderItem->order_id)->where('status', 1)->get();
                    if (count($GivenProducts) >= intval($orderItem->detail) ) {
                        $OrderStatus = 'complete';

                        $Pat = 138554;
                        $Data = [
                            'NUM' => $orderItem->order_id,
                            'STATUS' => 'تکمیل شده',
                        ];

                        $orderUser = Order::find($orderItem->order_id);
                        $User = User::find($orderUser->user_id);
                        sendSmsWithSmsir($User->mobile,$Data,$Pat);

                    } else {
                        $OrderStatus = 'pending';
                    }

                    $Order = Order::find($orderItem->order_id);
                    $Order->status = $OrderStatus;
                    $Order->save();
                }
            }
            if ($request->hasFile('AppleIdFile')) {
                if ($request->file('file')->extension() == 'xlsx') {
                    $array = Excel::toArray(new AppleIdImport, $request->file('AppleIdFile'))[0];
                    foreach ($array as $item) {
                        $Check = AppleId::where('apple_id', $item['apple_id'])->where('type', 'apple-id')->first();
                    }
                }

            }
            return response()->json(['success' => 'اپل آیدی با موفقیت ثبت شد.']);
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ثبت اپل آیدی سفارش */
    public function submit_custom_apple_id(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'appleid' => 'required',
                'password' => 'required',
                'brithday' => 'required',
                'order_id' => 'required',
                'q1' => 'required',
                'q2' => 'required',
                'q3' => 'required',
            ], [

                'appleid.required' => 'لطفا اپل آیدی را وارد نمایید.',
                'order_id.required' => 'لطفا شناسه اپل را وارد نمایید.',
                'password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                'brithday.required' => 'لطفا تاریخ تولد را وارد نمایید.',
                'q1.required' => 'لطفا سوالات امنیتی 1 را وارد نمایید.',
                'q2.required' => 'لطفا سوالات امنیتی 2 را وارد نمایید.',
                'q3.required' => 'لطفا سوالات امنیتی 3 را وارد نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            $Encrypt = new PhpEncrypt();
            $Appleid = [
                'appleid' => $request->input('appleid'),
                'password' => $request->input('password'),
                'brithday' => $request->input('brithday'),
                'q1' => $request->input('q1'),
                'q2' => $request->input('q2'),
                'q3' => $request->input('q3'),
            ];
            $Serial = rand(1111111111111111, 9999999999999999);
            $EncryptData = $Encrypt->encrypt($Serial, $Serial, json_encode($Appleid));
            $Order = Order::find($request->input('order_id'));
            $Order->status = 'complete';
            $Order->save();

            $NewAppleId = new AppleId();
            $NewAppleId->apple_id = $request->input('appleid');
            $NewAppleId->code = $EncryptData;
            $NewAppleId->serial = $Serial;
            $NewAppleId->status = 1;
            $NewAppleId->order_id = $Order->id;
            $NewAppleId->user_id = $Order->user_id;
            $NewAppleId->type = 2;
            $NewAppleId->operator_id = Auth::guard('admin')->user()->id;
            $NewAppleId->save();
            return response()->json(['success' => 'اپل آیدی با موفقیت ثبت شد.']);


        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }
}
