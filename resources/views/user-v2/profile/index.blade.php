@extends('site-v2.layout.master',['MetaTitle' => 'پروفایل | پنل کاربری'])
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{secure_url('/')}}"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">ناحیه کاربری</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">پروفایل</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">پروفایل</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
         @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-9">
                <!-- Toolbar-->
                <div class="d-none d-lg-flex justify-content-between align-items-center pt-lg-3 pb-4 pb-lg-5 mb-lg-3">
                    <h6 class="fs-base text-light mb-0">اطلاعات حساب کاربری: </h6>
                </div>
                <!-- Profile form-->
                <form>
                    <div class="row gx-4 gy-3">
                        <div class="col-sm-12">
                            <label class="form-label" for="account-fn">نام و نام خانوادگی</label>
                            <input class="form-control" type="text" id="account-fn" value="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="account-email">آدرس ایمیل</label>
                            <input class="form-control ltr" type="email" id="account-email" value="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->email}}" disabled="">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="account-phone">شماره تلفن</label>
                            <input class="form-control ltr" type="text" id="account-phone" value="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->mobile}}" required="" disabled="">
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label" for="su-password">پسورد</label>
                            <div class="password-toggle">
                                <label class="password-toggle-btn" aria-label="نمایش/پنهان کردن">
                                    <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                </label>
                                <input class="form-control ltr" type="password" name="register_password"
                                       id="su-password" required>
                            </div>
                            <div class="invalid-feedback errors" id="register_password"></div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label" for="su-password-confirm">تکرار پسورد</label>
                            <div class="password-toggle">
                                <label class="password-toggle-btn" aria-label="نمایش/پنهان کردن">
                                    <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                </label>
                                <input class="form-control ltr" type="password"
                                       name="register_password_confirmation" id="su-password-confirm" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr class="mt-2 mb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="form-check"></div>
                                <button class="btn btn-primary mt-3 mt-sm-0" type="button">بروزرسانی پروفایل</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
@section('header')
@endsection

@section('footer')

@endsection
