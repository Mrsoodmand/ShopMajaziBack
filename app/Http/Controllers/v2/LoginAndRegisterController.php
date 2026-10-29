<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\MobileVerfiy;
use App\Models\Product;
use App\Models\Virtual_Country;
use App\Models\Virtual_Service;
use \App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use const http\Client\Curl\AUTH_GSSNEG;

class LoginAndRegisterController extends Controller
{
    /* بررسی کاربر */
    public function CheckNumber(Request $request)
    {
        if (Auth::guest()) {
            $validate = Validator::make($request->all(), [
                'mobile' => 'required|digits:11'
            ], [
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                'mobile.digits' => 'فرمت شماره موبایل نامعتبر میباشد',
            ]);
            if ($validate->fails()) {
                return validation_error($request, $validate->errors()->getMessages());
            }
            try {
                $Mobile = $request->input('mobile');
                $User = User::where('mobile', $Mobile)->first();
                if (is_null($User)) {
                    $CheckRequest = MobileVerfiy::where('mobile', $Mobile)->where('status', 0)->count();
                    if ($CheckRequest < 5) {
                        $CheckCode = MobileVerfiy::where('mobile', $Mobile)->where('status', 0)->orderBy('id', 'DESC')->first();
                        if (!is_null($CheckCode)) {
                            $Now = Carbon::now()->format('Y-m-d H:i');
                            $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2)->format('Y-m-d H:i');
                            if ($Now < $CheckDate) {
                                $Now = Carbon::now();
                                $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2);
                                $dif = $Now->diff($CheckDate);
                                $mins = $dif->i * 60;
                                $sec = $dif->s + $mins;
                                return response()->json(['type' => 'register', 'header' => '<i class="fa-regular fa-address-card"></i> ثبت نام', 'time' => $sec], 200);
                            } else {
                                $Code = rand(1111, 9999);
                                $BlockUser = new MobileVerfiy();
                                $BlockUser->mobile = $Mobile;
                                $BlockUser->code = $Code;
                                $BlockUser->status = 0;
                                $BlockUser->save();
//                                SendVerifySms($Code, $Mobile);
                                SendVerifySmsSmsIr($Mobile, $Code);
                            }
                        } else {
                            $Code = rand(1111, 9999);
                            $BlockUser = new MobileVerfiy();
                            $BlockUser->mobile = $Mobile;
                            $BlockUser->code = $Code;
                            $BlockUser->status = 0;
                            $BlockUser->save();
//                            SendVerifySms($Code, $Mobile);
                            SendVerifySmsSmsIr($Mobile, $Code);
                        }
                        return response()->json(['type' => 'register', 'header' => '<i class="fa-regular fa-address-card"></i> ثبت نام', 'time' => 120], 200);
                    } else {
                        if ($request->ajax()) {
                            return response()->json(['error' => ['mobile' => 'بعلت درخواست های پی در پی، شماره تا یک ساعت آینده مسدود میشود.']], 400);
                        } else {
                            Session::flash('error', 'بعلت درخواست های پی در پی، شماره تا یک ساعت آینده مسدود میشود.');
                           abort(404);
                        }
                    }
                } else {
                    return response()->json(['type' => 'login',], 200);
                }
            } catch (\Exception $exception) {
                return exception_error($request, ['name' => $exception->getMessages()]);
            }
        }else{
            abort(404);

        }
    }

    /* ثبت نام کاربر */
    public function NewRegister(Request $request)
    {
        if (Auth::guest()) {
            $validate = Validator::make($request->all(), [
                'register_mobile' => 'required|digits:11',
                'register_code' => 'required|digits:4',
                'register_name' => 'required|string|max:40|min:3',
                'register_email' => 'required|email|max:40|min:3',
                'register_password' => 'required|min:6|max:30',
            ], [
                'register_mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                'register_mobile.digits' => 'فرمت شماره موبایل نا معتبر میباشد',
                'register_code.required' => 'لطفا کد تایید را وارد نمایید.',
                'register_code.digits' => 'فرمت کد تایید نامعتبر میباشد',

                'register_name.required' => 'لطفا نام و نام خانوادگی را وارد نمایید.',
                'register_name.string' => 'فرمت نام و نام خانوادگی نامعتبر می باشد.',
                'register_name.max' => 'حداکثر 40 کاراکتر برای نام و نام خانوادگی مجاز میباشد.',
                'register_name.min' => 'حداقل 3 کاراکتر برای نام و نام خانوادگی مجاز میباشد.',

                'register_email.required' => 'لطفا ایمیل را وارد نمایید.',
                'register_email.register_email' => 'فرمت ایمیل نامعتبر می باشد.',
                'register_email.max' => 'حداکثر 40 کاراکتر برای ایمیل مجاز میباشد.',
                'register_email.min' => 'حداقل 3 کاراکتر برای ایمیل مجاز میباشد.',

                'register_password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                'register_password.confirmed' => 'تکرار کلمه عبور اشتباه میباشد.',
                'register_password.max' => 'حداکثر 30 کاراکتر برای کلمه عبور مجاز میباشد.',
                'register_password.min' => 'حداقل 6 کاراکتر برای کلمه عبور مجاز میباشد.',
            ]);
            if ($validate->fails()) {
                return validation_error($request, $validate->errors()->getMessages());
            }
            try {
                $CheckUser = User::where('mobile', $request->input('register_mobile'))->first();
                if (is_null($CheckUser)) {
                    $CheckCode = MobileVerfiy::where('mobile', $request->input('register_mobile'))
                        ->where('code', $request->input('register_code'))->orderby('id', 'DESC')->where('status', 0)->first();
                    if (!is_null($CheckCode)) {
                        $Now = Carbon::now()->format('Y-m-d H:i');
                        $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2)->format('Y-m-d H:i');
                        if ($Now < $CheckDate) {
                            $Code = 0;
                            if ($request->input('register_ref') && $request->input('register_ref') != ' ' && $request->input('register_ref') != null) {
                                $RefUser = User::where('token', $request->register_ref)->first();
                                $Code = $RefUser->id;
                            }
                            $User = new User();
                            $User->name = $request->input('register_name');
                            $User->mobile = $request->input('register_mobile');
                            $User->password = bcrypt($request->input('register_password'));
                            $User->email = $request->input('register_email');
                            $User->parent_id = $Code;
                            $User->token = GenerateApiKey();
                            $User->save();
                            Auth::guard('user')->loginUsingId($User->id);
                            return response()->json(['success' => 'ثبت نام با موفقیت انجام شد.']);
                        } else {
                            if ($request->ajax()) {
                                return response()->json(['error' => ['register_name' => 'مهلت استفاده از کد تمام شده است.']], 400);
                            } else {
                                Session::flash('error', 'مهلت استفاده از کد تمام شده است.');
                                return back();
                            }
                        }
                    } else {
                        if ($request->ajax()) {
                            return response()->json(['error' => ['register_name' => 'کد وارد شده صحیح نمی باشد.']], 400);
                        } else {
                            Session::flash('error', 'کد وارد شده صحیح نمی باشد.');
                            return back();
                        }
                    }
                } else {
                    if ($request->ajax()) {
                        return response()->json(['error' => ['register_name' => 'کاربری با این مشخصات وجود دارد.']], 400);
                    } else {
                        Session::flash('error', 'کاربری با این مشخصات وجود دارد.');
                        return back();
                    }
                }
            } catch (\Exception $exception) {
                if ($request->ajax()) {
                    return response()->json(['error' => ['register_name' => $exception->getMessage()]], 400);
                } else {
                    Session::flash('error', $exception->getMessage());
                    return back();
                }
            }
        }
    }

    /* ورود کاربر */
    public function Login(Request $request)
    {
        if (Auth::guest()) {
            $Validate = Validator::make($request->all(),
                [
                    'login_mobile' => 'required|digits:11',
                    'login_password' => 'required'
                ], [
                    'login_mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                    'login_mobile.digits' => 'فرمت شماره موبایل نا معتبر میباشد',
                    'login_password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                ]);
            if ($Validate->fails()) {
                return validation_error($request, $Validate->errors()->getMessages());
            }
            try {
                $CheckUser = User::where('mobile', $request->input('login_mobile'))->first();
                if (!is_null($CheckUser)) {
                    if (Hash::check($request->input('login_password'), $CheckUser->password)) {
                        Auth::guard('user')->loginUsingId($CheckUser->id);
                        if ($request->has('product_id') && $request->input('product_id') != ' ' && $request->input('product_id') != null) {
                            Session::put('Product_id_Selected', $request->input('product_id'));
                        }
                        return response()->json(['success' => 'عملیات ورود با موفقیت انجام شد.']);
                    } else {
                        if ($request->ajax()) {
                            return response()->json(['error' => ['login_password' => 'کلمه عبور نامعتبر میباشد.']], 400);
                        } else {
                            Session::flash('error', 'کلمه عبور نامعتبر میباشد.');
                            return back();
                        }
                    }
                } else {
                    if ($request->ajax()) {
                        return response()->json(['error' => ['login_password' => 'کاربری با این مشخصات وجود ندارد.']], 400);
                    } else {
                        Session::flash('error', 'کاربری با این مشخصات وجود ندارد.');
                        return back();
                    }
                }
            } catch (\Exception $exception) {
                if ($request->ajax()) {
                    return response()->json(['error' => ['login_password' => $exception->getMessage()]], 400);
                } else {
                    Session::flash('error', $exception->getMessage());
                    return back();
                }
            }
        }
    }

    /* ارسال کد مجدد */
    public function SendNewCode(Request $request)
    {
        if (Auth::guest()) {
            $validate = Validator::make($request->all(), [
                'mobile' => 'required|digits:11'
            ], [
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                'mobile.digits' => 'فرمت شماره موبایل نامعتبر میباشد',
            ]);
            if ($validate->fails()) {
                if ($request->ajax()){
                    return validation_error($request, $validate->errors()->getMessages());
                }else{
                    return response()->json($validate->errors()->getMessages(),400);
                }
            }
            try {
                $Mobile = $request->input('mobile');
                $User = User::where('mobile', $Mobile)->first();
                if (is_null($User)) {
                    $CheckRequest = MobileVerfiy::where('mobile', $Mobile)->where('status', 0)->count();
                    if ($CheckRequest <= 5) {
                        $CheckCode = MobileVerfiy::where('mobile', $Mobile)->where('status', 0)->orderBy('id', 'DESC')->first();
                        $Now = Carbon::now()->format('Y-m-d H:i');
                        $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2)->format('Y-m-d H:i');
                        if ($Now >= $CheckDate) {
                            $Code = rand(1111, 9999);
                            $BlockUser = new MobileVerfiy();
                            $BlockUser->mobile = $Mobile;
                            $BlockUser->code = $Code;
                            $BlockUser->status = 0;
                            $BlockUser->save();
                            SendVerifySmsSmsIr($Mobile, $Code);
                            return response()->json(['success' => '200'], 200);
                        } else {
                            $Now = Carbon::now();
                            $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2);
                            $dif = $Now->diff($CheckDate);
                            $mins = $dif->i;
                            $sec = $dif->s + 2;
                            $Time = $mins . ':' . $sec;
                            if ($request->ajax()) {
                                return response()->json(['error' => ['register_name' => "امکان درخواست مجدد کد تا $Time دقیقه دیگر امکان پذیر می باشد."]], 400);
                            } else {

                                Session::flash('error', "امکان درخواست مجدد کد تا $Time دقیقه دیگر امکان پذیر می باشد.");
                                return back();
                            }
                        }
                    } else {
                        return response()->json(['error' => ['register_code' => 'تلاش بیش از حد، شماره مسدود شده است.']], 400);
                    }
                } else {
                    return response()->json(['type' => 'login'], 200);
                }
            } catch (\Exception $exception) {
                return exception_error($request, ['name' => $exception->getMessage()]);
            }
        }
    }

    /* ریست کردن پسورد */
    public function resetPassword(Request $request)
    {
        if (Auth::guest()) {
            $validate = Validator::make($request->all(), [
                'mobile' => 'required|digits:11'
            ], [
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                'mobile.digits' => 'فرمت شماره موبایل نامعتبر میباشد',
            ]);
            if ($validate->fails()) {
                if ($request->ajax()){
                    return validation_error($request, $validate->errors()->getMessages());
                }else{
                    return response()->json($validate->errors()->getMessages(),400);
                }
            }
            try {
                $Mobile = $request->input('mobile');
                $User = User::where('mobile', $Mobile)->first();
                if (!is_null($User)) {
                    $CheckRequest = MobileVerfiy::where('mobile', $Mobile)->where('type', 1)->count();
                    if ($CheckRequest <= 5) {

                        $Code = rand(111111, 999999);
                        $BlockUser = new MobileVerfiy();
                        $BlockUser->mobile = $Mobile;
                        $BlockUser->code = $Code;
                        $BlockUser->status = 0;
                        $BlockUser->type = 1;
                        $BlockUser->save();

                        $User = User::where('mobile', $Mobile)->first();
                        $User->password = bcrypt($Code);
                        $User->save();

                        $data = [
                            'CODE' => $Code,
                        ];
                        $Code = sendSmsWithSmsir($Mobile, $data, 166193);
                        return response()->json(['success' => '200'], 200);

                    } else {
                        return response()->json(['error' => ['register_code' => 'تلاش بیش از حد، شماره مسدود شده است.']], 400);
                    }
                } else {
                    return response()->json(['type' => 'login'], 200);
                }
            } catch (\Exception $exception) {
                return exception_error($request, ['name' => $exception->getMessage()]);
            }
        }
    }

    /* ارسال کد یکبار مصرف */
    public function fastLoginCode(Request $request)
    {

        if (Auth::guest()) {
            $validate = Validator::make($request->all(), [
                'mobile' => 'required|digits:11'
            ], [
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                'mobile.digits' => 'فرمت شماره موبایل نامعتبر میباشد',
            ]);
            if ($validate->fails()) {
                if ($request->ajax()){
                    return validation_error($request, $validate->errors()->getMessages());
                }else{
                    return response()->json($validate->errors()->getMessages(),400);
                }
            }
            try {
                $Mobile = $request->input('mobile');
                $User = User::where('mobile', $Mobile)->first();
                if (!is_null($User)) {
                    $CheckCode = MobileVerfiy::where('mobile', $Mobile)->where('status', 0)->where('type', 2)->orderBy('id', 'DESC')->first();
                    $Now = Carbon::now()->format('Y-m-d H:i');
                    if (!is_null($CheckCode)){
                        $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2)->format('Y-m-d H:i');

                        if ($Now >= $CheckDate) {
                            $Code = rand(1111, 9999);
                            $BlockUser = new MobileVerfiy();
                            $BlockUser->mobile = $Mobile;
                            $BlockUser->code = $Code;
                            $BlockUser->status = 0;
                            $BlockUser->type = 2;
                            $BlockUser->save();

                            $data = [
                                'CODE' => $Code,
                            ];
                            $Code = sendSmsWithSmsir($Mobile, $data, 927153);
                            return response()->json(['success' => '200'], 200);
                        } else {
                            $Now = Carbon::now();
                            $CheckDate = Carbon::parse($CheckCode->created_at)->addMinutes(2);
                            $dif = $Now->diff($CheckDate);
                            $mins = $dif->i;
                            $sec = $dif->s + 2;
                            $Time = $mins . ':' . $sec;
                            if ($request->ajax()) {
                                return response()->json(['error' => ['register_name' => "امکان درخواست مجدد کد تا $Time دقیقه دیگر امکان پذیر می باشد."]], 400);
                            } else {
                                Session::flash('error', "امکان درخواست مجدد کد تا $Time دقیقه دیگر امکان پذیر می باشد.");
                                return back();
                            }
                        }
                    }else{
                        $Code = rand(11111, 99999);
                        $BlockUser = new MobileVerfiy();
                        $BlockUser->mobile = $Mobile;
                        $BlockUser->code = $Code;
                        $BlockUser->status = 0;
                        $BlockUser->type = 2;
                        $BlockUser->save();

                        $data = [
                            'CODE' => $Code,
                        ];
                        $Code = sendSmsWithSmsir($Mobile, $data, 927153);
                        return response()->json(['success' => '200'], 200);
                    }

                } else {
                    if ($request->ajax()) {
                        return response()->json(['error' => ['register_name' => 'کاربری با این مشخصات یافت نشد.']], 400);
                    } else {
                        Session::flash('error', 'کاربری با این مشخصات یافت نشد.');
                        abort(404);
                    }
                }
            } catch (\Exception $exception) {
                if ($request->ajax()){
                    return exception_error($request, ['name' => $exception->getMessage()]);
                }else{
                    return response()->json($exception->getMessage(),400);
                }
            }
        }else{
            if ($request->ajax()){
                return exception_error($request, ['درخواست نامعتبر']);
            }else{
                return response()->json('درخواست نامعتبر',400);
            }
        }
    }

    /* ورود با رمز یکبارمصرف */
    public function fastLogin(Request $request)
    {
        if (Auth::guest()) {
            $Validate = Validator::make($request->all(),
                [
                    'fast_login_mobile' => 'required|digits:11',
                    'fast_login_password' => 'required'
                ], [
                    'fast_login_mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید.',
                    'fast_login_mobile.digits' => 'فرمت شماره موبایل نا معتبر میباشد',
                    'fast_login_password.required' => 'لطفا کلمه عبور را وارد نمایید.',
                ]);
            if ($Validate->fails()) {
                return validation_error($request, $Validate->errors()->getMessages());
            }
            try {
                $CheckUser = User::where('mobile', $request->input('fast_login_mobile'))->first();
                if (!is_null($CheckUser)) {
                    $CheckCode = MobileVerfiy::where('mobile', $CheckUser->mobile)->where('status', 0)->where('type', 2)->orderBy('id', 'DESC')->first();
                    if (!is_null($CheckCode)) {
                        if ($CheckCode->code == $request->input('fast_login_password')) {
                            Auth::guard('user')->loginUsingId($CheckUser->id);
                            if ($request->has('product_id') && $request->input('product_id') != ' ' && $request->input('product_id') != null) {
                                Session::put('Product_id_Selected', $request->input('product_id'));
                            }
                            $CheckCode->status = 1;
                            $CheckCode->save();
                            return response()->json(['success' => 'عملیات ورود با موفقیت انجام شد.']);
                        } else {
                            if ($request->ajax()) {
                                return response()->json(['error' => ['fast_login_password' => 'کد یکبار مصرف نامعتبر میباشد.']], 400);
                            } else {
                                Session::flash('error', 'کد یکبار مصرف نامعتبر میباشد.');
                                return back();
                            }
                        }
                    } else {
                        if ($request->ajax()) {
                            return response()->json(['error' => ['fast_login_mobile' => 'کد یکبار مصرفی یافت نشد.']], 400);
                        } else {
                            Session::flash('error', 'کد یکبار مصرفی یافت نشد.');
                            return back();
                        }
                    }
                } else {
                    if ($request->ajax()) {
                        return response()->json(['error' => ['fast_login_password' => 'کاربری با این مشخصات وجود ندارد.']], 400);
                    } else {
                        Session::flash('error', 'کاربری با این مشخصات وجود ندارد.');
                        return back();
                    }
                }
            } catch (\Exception $exception) {
                if ($request->ajax()) {
                    return response()->json(['error' => ['fast_login_password' => $exception->getMessage()]], 400);
                } else {
                    Session::flash('error', $exception->getMessage());
                    return back();
                }
            }
        }
    }
}
