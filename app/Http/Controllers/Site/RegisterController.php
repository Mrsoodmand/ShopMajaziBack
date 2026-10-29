<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\MobileVerfiy;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /* Step One Register */
    public function Step1(Request $request)
    {

        $validate = Validator::make($request->all(),
            ['mobile' => 'required|digits:11|unique:users'
            ], [
                'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
                'mobile.digits' => 'شماره موبایل نامعتبر است',
                'mobile.unique' => 'این شماره قبلا ثبت شده است',
            ]);

        if ($validate->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validate->errors()->all()], 400);
            } else {
                \Illuminate\Support\Facades\Session::flash('error', $validate->errors()->all());
                abort(404);
            }
        }

        try {
            MobileVerfiy::where('mobile', $request->input('mobile'))->delete();

            if ($request->has('product_id') && $request->input('product_id') != ' ') {
                Session::put('Product_id_Selected', $request->input('id'));
            }

            $code = rand(10000, 99999);
            $Verify = new MobileVerfiy();
            $Verify->mobile = $request->input('mobile');
            $Verify->code = $code;
            $Verify->save();

            $Mobile = $request->input('mobile');

            SendVerifySms($code, $Mobile);

            $data = '
                      <div class="user-item">

                      <div>
                         <div class="alert alert-success alert-dismissible fade show alert-success-register">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                                <polyline points="9 11 12 14 22 4"></polyline>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                            </svg>
                            کد تایید با موفقیت ارسال گردید.
                            </button>
                        </div>
                        </div>
                        <form action="' . url('/user/register/step2') . '" method="post" id="Step2">
                            <h2> شماره موبایل </h2>
                            <div class="form-group" >
                                <input type="tel" name="mobile" readonly class="form-control" value="' . $Mobile . '" placeholder="شماره موبایل">
                                <p id="mobile-register-2" class="Login" style="display: none">  </p>
                            </div>

                            <div class="form-group" >
                                <input type="tel" name="ref_code" readonly class="form-control" value="' . $request->input('ref_code') . '" placeholder="کد معرف">
                                <p id="ref_code-area" class="Login" style="display: none">  </p>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                <div class="col-12 mb-2">
                                        <input type="text" name="name" autocomplete="" class="form-control" placeholder="نام و نام خانوادگی">
                                          <p id="code-register" class="Login" style="display: none">  </p>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <input type="tel" name="code" autocomplete="" class="form-control" placeholder="کد ارسال شده">
                                          <p id="code-register" class="Login" style="display: none">  </p>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <input type="password" name="password" class="form-control" placeholder="کلمه عبور">
                                    </div>
                                    <div class="col-6 mt-2">
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="تکرار کلمه عبور">
                                    </div>
                                </div>
                            </div>
                              <input type="hidden" name="_token" class="form-control" value="' . csrf_token() . '" placeholder="کلمه عبور">
   <div class="row register-error-area">


                        </div>
                            <button type="submit" id="btn-register-2"  class="btn common-btn">
                                ورود
                                <img src="' . url('/site/assets/images/shape1.png') . '" alt="Shape">
                                <img src="' . url('/site/assets/images/shape2.png') . ' alt="Shape">
                            </button>
                            <h5>حساب کاربری دارم <b class="Login" onclick="Login()"> ورود </b></h5>
                        </form>
                    </div>
            ';
            return response()->json(['success' => 'کد تایید ارسال شد. ', 'data' => $data], 200);

        } catch (\Exception $exception) {
            if ($request->ajax()) {
                return response()->json(['error' => [$exception->getMessage()]], 400);
            } else {
                \Illuminate\Support\Facades\Session::flash('error', [$exception->getMessage()]);
                abort(404);
            }
        }
    }

    /* Step Two Register */
    public function Step2(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'mobile' => 'required|digits:11|unique:users',
                'name' => 'required|string',
                'code' => 'required|numeric',
                'password' => 'required|confirmed|min:6'
            ],
            [
                'name.required' => 'لطفا نام را وارد نمایید',
                'name.string' => 'فرمت نام نا معتبر میباشد',
                'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
                'mobile.digits' => 'شماره موبایل نامعتبر است',
                'mobile.unique' => 'این شماره قبلا ثبت شده است',
                'code.required' => 'لطفا کد ارسال شده را وارد نمایید',
                'code.numeric' => 'فرمت کد وارد شده نا معتبر میباشد',
                'password.required' => 'کلمه علمه عبور را وارد نمایید',
                'password.confirmed' => 'عدم تطابق کلمه عبور',
                'password.min6' => 'حداقل 6 کاراکتر برای کلمه عبور',
            ]);
        if ($validate->fails()) {
            if ($request->ajax()) {
                return response()->json(['error' => $validate->errors()->all()], 400);
            } else {
                \Illuminate\Support\Facades\Session::flash('error', $validate->errors()->all());
                return back();
            }
        }
        try {

            $Verify = MobileVerfiy::where('mobile', $request->input('mobile'))
                ->where('code', $request->input('code'))->first();
            if (!is_null($Verify)) {

                $Verify->status = true;
                $Verify->save();

                $id = null;
                if ($request->has('ref_code')) {
                    $id = User::where('token', $request->input('ref_code'))->first()->id;
                }
                $token = $this->GenerateUserToken($request->input('mobile'));

                $User = new User();
                $User->mobile = $request->input('mobile');
                $User->name = $request->input('name');
                $User->password = bcrypt($request->input('password'));
                $User->identifier = $id;
                $User->token = $token;
                $User->save();

                Auth::guard('user')->loginUsingId($User->id);
                return success($request, 'حساب کاربری با موفقیت ایجاد گردید');


            } else {
                return exception_error($request, 'کد وارد شده صحیح نمیباشد');
            }


        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }

    }

    /* Generate Token */
    public function GenerateUserToken($token)
    {

        $token = Encode($token);
        $user = User::where('token', $token)->first();

        if (is_null($user)) {
            return $token;
        } else {
            return $this->GenerateUserToken();
        }

    }

    /* Rest Password */
    public function SendCode(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'mobile' => 'required|digits:11'
            ],
            [
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید',
                'mobile.digits' => 'لطفا شماره موبایل خود را صحیح وارد نمایید',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()->all()]);
        }
        try {

            $user = User::where('mobile', $request->input('mobile'))->first();
            $code = rand(11111, 99999);
            if (!is_null($user)) {
                $check = DB::table('user_password_resets')->where('mobile', $request->input('mobile'))->latest()->first();
                if (!is_null($check)) {
                    $now = new \DateTime(Carbon::now()->addMinutes());
                    $time = new \DateTime($check->created_at);
                    $diff = $now->diff($time);
                    if ($diff->i > 6) {
                        DB::table('user_password_resets')->insertGetId(
                            [
                                'mobile' => $request->input('mobile'),
                                'created_at' => Carbon::now(),
                            ]
                        );
                        $user->password = bcrypt($code);
                        $user->save();
                        $mobile = $request->input('mobile');

                        $client = new \SoapClient('http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl');
                        $user = "mojtaba.gharegoli";
                        $pass = "Aa1050474821@@";
                        $fromNum = "+3000505";
                        $toNum = array("$mobile");
                        $pattern_code = "4v943z4ccn";

                        $url = \env('APP_URL');
                        $input_data = array(
                            "pass" => "$code",
                            "company" => "$url",
                        );
                        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);

                        return response()->json(['success' => 'کلمه عبور با موفقیت ارسال شد', 'mobile' => $request->input('mobile')], 200);

                    } else {
                        $sec = 59 - $diff->s;
                        $min = 5 - $diff->i;

                        return response()->json(['error' => [" کاربر گرامی، درخواست شما قبلا ثبت شده است . برای درخواست مجدد لطفا $min دقیقه و $sec ثانیه دیگر تلاش کنید  "]], 400);

                    }
                } else {

                    DB::table('user_password_resets')->insertGetId(
                        [
                            'mobile' => $request->input('mobile'),
                            'created_at' => Carbon::now(),
                        ]
                    );
                    $user->password = bcrypt($code);
                    $user->save();
                    $mobile = $request->input('mobile');

                    $client = new \SoapClient('http://188.0.240.110/class/sms/wsdlservice/server.php?wsdl');
                    $user = "mahdi-930";
                    $pass = "persianeagle_930";
                    $fromNum = "+3000505";
                    $toNum = array("$mobile");
                    $pattern_code = "4v943z4ccn";

                    $input_data = array(
                        "pass" => "$code",
                        "company" => url('/'),
                    );
                    $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);
                    return response()->json(['success' => 'کلمه عبور با موفقیت ارسال شد', 'mobile' => $request->input('mobile')], 200);
                }
            }

            return response()->json(['error' => ["کاربری با این مشخصات یافت نشد"]], 400);


        } catch (\Exception $exception) {

            return response()->json(['error' => [$exception->getMessage()]], 400);

        }
    }
}
