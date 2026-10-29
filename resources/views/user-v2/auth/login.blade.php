@extends('site-v2.layout.master',['MetaTitle' => 'ورود | شاپ مجازی'])
@section('content')
    <div class="container py-4 py-lg-5 my-4">
        <div class="row">
            <div class="col-md-6 offset-md-3 col-12">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <h2 class="h4 mb-2">ورود/ثبت نام</h2>
                        <hr>
                        <form class="needs-validation mt-3" action="{{secure_url('login/check-user')}}" method="post"
                              id="signin-page-tab">
                            <div class="mb-1">
                                <label class="form-label" for="login-mobile">شماره موبایل</label>
                                <input class="form-control rounded-start mobile-number ltr" maxlength="11" type="tel"
                                       name="mobile" placeholder="09123456789" required="">
                                <div class="invalid-feedback">لطفا شماره موبایل را وارد کنید</div>
                                <div class="invalid-feedback errors mobile"></div>
                            </div>
                            @csrf
                            <hr class="mt-4">
                            <div class="pt-2 text-end">
                                <button class="btn btn-success btn-shadow d-block w-100 login--btn" id="login-user-btn-form"
                                        data-text="ورود یا ثبت نام" data-form="signin-page-tab" type="submit"><i
                                            class="ci-sign-in me-2 ms-n21"></i>ورود یا ثبت نام
                                </button>
                            </div>
                            <div class="text-end pt-4">



                            </div>
                        </form>
                        <form method="post" action="{{secure_url('login/login')}}" class="needs-validation"
                              style="display: none" autocomplete="off" novalidate id="login-page-tab">
                            <div class="mb-1">
                                <label class="form-label" for="su---password">کلمه عبور</label>
                                <div class="password-toggle">
                                    <label class="password-toggle-btn" style="" aria-label="نمایش/پنهان کردن">
                                        <input class="password-toggle-check" type="checkbox"><span
                                                class="password-toggle-indicator"></span>
                                    </label>
                                    <input class="form-control ltr" type="password" name="login_password"
                                           id="su---password" required>
                                </div>
                                <div class="invalid-feedback errors login_password"></div>
                                <div class="invalid-feedback">لطفا کلمه عبور خود را وارد کنید</div>

                                <input class="user-mobile" type="hidden" name="login_mobile" required>
                                <input name="product_id" class="product-id" type="hidden">
                                <input name="type" class="type" type="hidden">
                            </div>
                            <div class="d-flex flex-wrap justify-content-between">
                                <a class="nav-link-inline fs-sm" href="javascript:void(0);"
                                   style="right: 18px;cursor: pointer"
                                   onclick="ChangeTab2('login-page-tab','fast-login-tab')">ورود با رمز یکبار مصرف</a>
                                <a class="nav-link-inline fs-sm" href="javascript:void(0);"
                                   onclick="resetPassword2()">فراموشی رمز؟</a>
                            </div>
                            @csrf
                            <hr class="mt-4">
                            <div class="pt-2 text-end">
                                <a class="nav-link-inline fs-sm"
                                   style="right: 18px;position: absolute;margin-top: 15px;cursor: pointer"
                                   onclick="ChangeTab2('login-page-tab','signin-page-tab')">تغییر شماره</a>
                                <button id="login-user-btn" data-form="login-page-tab"
                                        class="login--btn btn btn-success btn-shadow" type="submit">ورود
                                </button>
                            </div>
                        </form>
                        <form method="post" action="{{secure_url('login/register')}}" class="needs-validation register-tab"
                              style="display: none" autocomplete="off" novalidate id="register-page-tab">
                            <div class="mb-1">
                                <label class="form-label" for="su--name"> نام و نام خانوادگی</label>
                                <input class="form-control" type="text" autocomplete="off" id="su--name"
                                       name="register_name"
                                       placeholder="شاپ مجازی" required>
                                <input name="register_mobile" class="user-mobile" value="" type="hidden">
                                <input name="product_id" class="product-id" type="hidden">
                                <input name="type" class="type" type="hidden">
                                <div class="invalid-feedback">لطفا نام و نام خانوادگی را وارد کنید</div>
                                <div class="invalid-feedback errors register_name"></div>
                            </div>
                            <div class="mb-1">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="su--email">آدرس ایمیل</label>
                                        <input class="form-control ltr" autocomplete="off" type="email"
                                               name="register_email" id="su--email"
                                               placeholder="" required>
                                        <div class="invalid-feedback">لطفا آدر س ایمیل را وارد کنید</div>
                                        <div class="invalid-feedback errors register_email"></div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="su--tel">کد تایید</label>
                                        <label style="float: left" id="time--area">
                                        <span class="register-number" data-seconds-left="120"
                                              style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>
                                        </label>
                                        <input class="form-control ltr GetCode" autocomplete="one-time-code"
                                               type="number" maxlength="5"
                                               name="register_code" id="su-tel" placeholder="" required>
                                        <div class="invalid-feedback errors register_code"></div>
                                        <div id="new-code-request" style="display: none;font-size: 12px!important;"><a
                                                    style="font-size: 12px!important;"
                                                    class="nav-link-inline fs-sm request-new-code" href="javascript:;">درخواست
                                                کد مجدد</a></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-1">
                                <div class="row">
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label" for="su--password">پسورد</label>
                                        <div class="password-toggle">
                                            <label class="password-toggle-btn" aria-label="نمایش/پنهان کردن">
                                                <input class="password-toggle-check" type="checkbox"><span
                                                        class="password-toggle-indicator"></span>
                                            </label>
                                            <input class="form-control ltr" type="password" name="register_password"
                                                   id="su--password" required>
                                        </div>
                                        <div class="invalid-feedback errors register_password"></div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label" for="su--name">کد معرف</label>
                                        <input class="form-control" type="text" autocomplete="off"
                                               @if(\Illuminate\Support\Facades\Session::has('RefCode')) value="{{\Illuminate\Support\Facades\Session::get('RefCode')}}"
                                               @endif id="su--name" name="register_ref" placeholder="" required>
                                    </div>
                                </div>
                            </div>
                            @csrf
                            <hr class="mt-4">
                            <div class="pt-2 text-end">
                                <a class="nav-link-inline fs-sm"
                                   style="right: 18px;position: absolute;margin-top: 15px;cursor: pointer"
                                   onclick="ChangeTab2('register-page-tab','signin-page-tab')">تغییر شماره</a>
                                <button id="register-btn" data-form="register-page-tab" data-text="ثبت نام"
                                        class="login--btn btn btn-success btn-shadow" type="submit">ثبت نام
                                </button>
                            </div>
                        </form>
                        <form method="post" action="{{secure_url('login/fast-login')}}" class="needs-validation tab-pane fade" style="display: none" novalidate id="fast-login-tab">
                            <div class="mb-1">
                                <label class="form-label" for="su-password">رمز یکبار مصرف</label>
                                <div class="password-toggle">
                                    <label class="password-toggle-btn" style="" aria-label="نمایش/پنهان کردن">
                                        <input class="password-toggle-check" type="checkbox"><span
                                                class="password-toggle-indicator"></span>
                                    </label>
                                    <input class="form-control ltr" type="password" name="fast_login_password"
                                           id="su-password" required>
                                </div>
                                <div class="invalid-feedback">لطفا کلمه عبور خود را وارد کنید</div>
                                <div class="invalid-feedback errors login_password"></div>
                                <input class="user-mobile" type="hidden" name="fast_login_mobile" required>
                                <input name="product_id" class="product-id" type="hidden">
                                <input name="type" class="type" type="hidden">
                            </div>
                            <div class="d-flex flex-wrap justify-content-between">
                                <div class="form-check">
                                </div>
                                <a class="nav-link-inline fs-sm" href="javascript:void(0);"
                                   style="right: 18px;position: absolute;cursor: pointer"
                                   onclick="ChangeTab('fast-login-tab','login-page-tab')">ورود با کلمه عبور</a>
                                <a class="nav-link-inline fs-sm" href="javascript:void(0);"
                                   onclick="resetPassword2()">فراموشی رمز؟</a>
                            </div>
                            @csrf
                            <hr class="mt-4">
                            <div class="pt-2 text-end">
                                <a class="nav-link-inline fs-sm" href="javascript:void(0);"
                                   style="right: 18px;position: absolute;margin-top: 15px;cursor: pointer"
                                   onclick="ChangeTab('fast-login-tab','signin-page-tab')">تغییر شماره</a>
                                <button id="fast-login-tab-btn" data-form="fast-login-tab" data-text="ورود"
                                        class="login--btn btn btn-success btn-shadow" type="submit">ورود
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('header')
@endsection

@section('footer')
    <script>
        $(document).ready(function () {
            $('button').prop('disabled', false);
            $('.login--btn').click(function () {
                var BtnId = $(this).attr('id');
                var text = $(this).data('text');
                var formName = $(this).data('form');
                $('#' + BtnId).html('لطفا صبر کنید...');
                $('#' + BtnId).attr('disabled', true);
                var form = document.querySelector('#' + formName);
                $(form).ajaxSubmit({
                    dataType: 'json',
                    beforeSend: function () {
                        $('#' + BtnId).html('لطفا صبر کنید');
                        $('.errors').hide();
                    },
                    success: function (data) {
                        console.log(formName)
                        if (formName == 'signin-page-tab') {
                            $('#' + formName).hide();
                            $('.user-mobile').val($('.mobile-number').val());
                            $('#' + data.type + '-page-tab').show();
                            if (data.type == 'register') {
                                $('#time--area').empty().append('<span class="register-number" data-seconds-left="' + data.time + '" style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>');
                                $('.register-number').startTimer();
                                var time = data.time * 1000;
                                setTimeout(function () {
                                    $('#new-code-request').show();
                                }, time);
                            }
                        } else if (formName == 'register-page-tab') {
                            success('ثبت نام با موفقیت انجام شد.');
                            location.reload();
                        } else if (formName == 'login-page-tab' || formName == 'fast-login-tab') {
                            console.log('success')
                            success('عملیات ورود با موفقیت انجام شد.');
                            location.reload();
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    },
                    error: function (err) {
                        console.log(err)
                        for (const [key, value] of Object.entries(err.responseJSON.error)) {
                            $('#' + formName + ' .' + key).empty().text(value).show()
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    }
                });
                return false;
            });
        });

        function ChangeTab2(oldTab, newTab) {
            $('#' + oldTab).removeClass('show active');
            $('#' + newTab).show();
            $('#signin-modal .form-control').val('');
            $('#' + oldTab).removeClass('show active');
            $('#' + oldTab).hide();
            $('#' + newTab).show();
            $('#' + newTab).addClass('show active');
            $('#signin-modal .form-control').val('');
            if (newTab == 'fast-login-tab') {
                $.ajax({
                    url: '{{secure_url('/login/fast-login-code')}}',
                    dataType: "JSON",
                    method: 'GET',
                    data: {
                        mobile: $('.user-mobile').val(),
                    },
                    success: function (data) {
                        success('کد یکبار مصرف با موفقیت ارسال شد.');

                    }, error: function (err) {
                        console.log(err)
                        for (const [key, value] of Object.entries(err.responseJSON.error)) {
                            error(value)
                        }
                    }
                });
            }
        }

        function resetPassword2() {
            $.ajax({
                url: '{{secure_url('/login/reset-password-v2')}}',
                dataType: "JSON",
                method: 'GET',
                data: {
                    mobile: $('.user-mobile').val(),
                },
                success: function (data) {
                    success('کلمه عبور جدید با موفقیت ارسال شد.');
                    $('.needs-validation').removeClass('show active');
                    $('.needs-validation').hide();
                    $('#login-page-tab').addClass('show active');
                    $('#login-page-tab').show();
                }, error: function (err) {
                    console.log(err)
                    for (const [key, value] of Object.entries(err.responseJSON.error)) {
                        error(value)
                    }
                }
            });
        }
    </script>

@endsection
