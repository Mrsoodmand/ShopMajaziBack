<?php

namespace App\Http\Controllers\UserAuth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/user/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('user.guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'mobile' => 'required|unique:users|digits:11',
            'password' => 'required|min:6',
        ],[
            'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
            'mobile.unique' => 'متاسفانه این شماره قبلا ثبت شده است',
            'mobile.digits' => 'فرمت شماره موبایل نامعتبر میباشد',
            'password.required' => 'لطفا کلمه عبور را وارد نمایید',
            'password.min' => 'حداقل 6 کاراکتر برای کلمه عبور',
            'password.confirmed' => 'کلمه عبور مطابقت ندارد',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'mobile' => $data['mobile'],
            'name' => $data['name'],
            'wallet' => 0,
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('user.auth.register');
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('user');
    }
}
