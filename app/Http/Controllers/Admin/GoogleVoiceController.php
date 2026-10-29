<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\GoogleVoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GoogleVoiceController extends Controller
{
    public function index(Request $request)
    {
        $list = GoogleVoice::orderby('id','DESC')->paginate(50);
        $jdf = new Jdf();
        return view('admin-v2.google-voice.index',compact('list','jdf'));
    }

    /* ایجاد اپل آیدی */
    public function create()
    {
        $GoogleVoice = GoogleVoice::where('status', '0')->select('id', 'code', 'serial')->get();
        $Setting = Pages::find(4);
        $Decrypt = new PhpEncrypt();
        return view('admin-v2.google-voice.create', compact('GoogleVoice', 'Setting', 'Decrypt'));
    }

    /* ثبت اپل آیدی آماده */
    public function submit(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'custom_price' => 'required',
                'short_detail' => 'required',
                'detail' => 'required',
//                'googlevoice_id' => 'required',
//                'googlevoice_number' => 'required',
//                'googlevoice_email' => 'required',
//                'googlevoice_password' => 'required',
//                'googlevoice_backup' => 'required',
//                'googlevoice_step2' => 'required',
            ], [

                'h1.required' => 'لطفا تگ را وارد نمایید.',
                'custom_price.required' => 'لطفا قیمت گوگل ویس را وارد نمایید.',
                'short_detail.required' => 'لطفا توضیحات کوتاه را وارد نمایید.',
                'detail.required' => 'لطفا جزئیات کامل را وارد نمایید.',
                'googlevoice_id.required' => 'خطا در شناسه گوگل ویس.',
                'googlevoice_number.required' => 'لطفا شماره گوگل ویس را وارد نمایید.',
                'googlevoice_email.required' => 'لطفا ایمیل گوگل ویس را وارد نمایید.',
                'googlevoice_password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                'googlevoice_backup.required' => 'لطفا ایمیل پشتیبان را وارد نمایید.',
                'googlevoice_step2.required' => 'لطفا کد دو مرحله ای را وارد نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {

            $Seo = Add_Seo($request);
            $Faq = Add_Faq($request);
            $Page = Pages::find(4);
            // Keep other saved keys; the Numberland ones drive the new site (App\Services\GoogleVoiceNumbers).
            $Price = array_merge(json_decode((string)$Page->detail, true) ?: [], [
                'custom_price' => $request->input('custom_price'),
                'numberland_enabled' => $request->input('numberland_enabled', 1) == 0 ? 0 : 1,
                'numberland_markup' => max(0, min(500, (float)$request->input('numberland_markup', config('services.numberland.gv_markup', 20)))),
            ]);
            $Page->h1 = $request->input('h1');
            $Page->short_detail = $request->input('short_detail');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->faq = $Faq;
            $Page->seo = $Seo;
            $Page->detail = json_encode($Price);
            $Page->save();

            $Encrypt = new PhpEncrypt();
            if ($request->has('googlevoice_id')) {
                foreach ($request->input('googlevoice_id') as $key => $value) {
                    $GoogleVoiceData = [
                        'number' => $request->input('googlevoice_number')[$key],
                        'email' => $request->input('googlevoice_email')[$key],
                        'password' => $request->input('googlevoice_password')[$key],
                        'backup-email' => $request->input('googlevoice_backup')[$key],
                        '2fa' => $request->input('googlevoice_step2')[$key],
                    ];
                    $Serial = rand(1111111111111111, 9999999999999999);
                    $EncryptData = $Encrypt->encrypt($Serial, $Serial, json_encode($GoogleVoiceData));
                    if ($request->input('googlevoice_id')[$key] == 0) {
                        $NewGoogleVoice = new GoogleVoice();
                    } else {
                        $NewGoogleVoice = GoogleVoice::find($request->input('googlevoice_id')[$key]);
                    }
                    $NewGoogleVoice->number = $request->input('googlevoice_number')[$key];
                    $NewGoogleVoice->code = $EncryptData;
                    $NewGoogleVoice->serial = $Serial;
                    $NewGoogleVoice->status = 0;
                    $NewGoogleVoice->operator_id = Auth::guard('admin')->user()->id;
                    $NewGoogleVoice->save();

                    $GoogleVoice[] = $NewGoogleVoice->id;
                }
                GoogleVoice::where('status', 0)->wherenotIn('id', $GoogleVoice)->delete();
            }
            return response()->json(['success' => 'گوگل ویس با موفقیت ثبت شد.']);
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
