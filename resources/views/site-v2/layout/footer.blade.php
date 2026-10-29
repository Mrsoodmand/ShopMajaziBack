<style>
    .reset-pass-btn {
        color: #fe696a;
        cursor: pointer;
    }
</style>
@if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
    <div class="modal fade" id="signin-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="ci-unlocked me-2 mt-n1"></i>ورود یا ثبت نام</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body tab-content py-4">
                    <form method="post" action="{{secure_url('login/check-user')}}"
                          class="needs-validation tab-pane fade show active" novalidate id="signin-tab">
                        <div class="mb-1">
                            <label class="form-label" for="login-mobile">شماره موبایل</label>
                            <input class="form-control ltr login-mobile" type="tel" name="mobile" maxlength="11"
                                   id="login-mobile"
                                   placeholder="09123456789" required>
                            <div class="invalid-feedback">لطفا شماره موبایل را وارد کنید</div>
                        </div>
                        <div class="invalid-feedback errors mobile"></div>
                        @csrf
                        <hr class="mt-4">
                        <div class="pt-2 text-end">
                            <button id="check-user-btn" data-form="signin-tab" data-text="ورود یا ثبت نام"
                                    class="login-btn btn btn-success btn-shadow d-block w-100" type="submit">ورود یا ثبت
                                نام
                            </button>
                        </div>
                    </form>
                    <form method="post" action="{{secure_url('login/register')}}"
                          class="needs-validation tab-pane fade register-tab" novalidate id="register-tab">
                        <div class="mb-1">
                            <label class="form-label" for="su-name"> نام و نام خانوادگی</label>
                            <input class="form-control" type="text" id="su-name" name="register_name"
                                   placeholder="لطفا نام و نام خانوادگی را وارد کنید" required>
                            <input name="register_mobile" class="user-mobile" value="" type="hidden">
                            <input name="product_id" class="product-id" type="hidden">
                            <input name="type" class="type" type="hidden">
                            <div class="invalid-feedback">لطفا نام و نام خانوادگی را وارد کنید</div>
                            <div class="invalid-feedback errors register_name"></div>
                        </div>
                        <div class="mb-1">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label" for="su-email">آدرس ایمیل</label>
                                    <input class="form-control ltr" type="email"
                                           name="register_email" id="su-email"
                                           placeholder="" required>
                                    <div class="invalid-feedback">لطفا آدر س ایمیل را وارد کنید</div>
                                    <div class="invalid-feedback errors register_email"></div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label" for="su-tel">کد تایید</label>
                                    <label style="float: left" id="time-area">
                                        <span class="register-number" data-seconds-left="120"
                                              style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>
                                    </label>
                                    <input class="form-control" type="tel" maxlength="5" autocomplete="one-time-code"
                                           name="register_code" id="GetCode" required>
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
                                    <label class="form-label" for="su-password-1">کلمه عبور</label>
                                    <div class="password-toggle">
                                        <label class="password-toggle-btn" aria-label="نمایش/پنهان کردن">
                                            <input class="password-toggle-check" type="checkbox"><span
                                                    class="password-toggle-indicator"></span>
                                        </label>
                                        <input class="form-control ltr" type="password" name="register_password"
                                               id="su-password-1" required>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label" for="su--name">کد معرف</label>
                                    <input class="form-control" type="text" id="su--name" name="register_ref"
                                           @if(\Illuminate\Support\Facades\Session::has('RefCode')) value="{{\Illuminate\Support\Facades\Session::get('RefCode')}}"
                                           @endif
                                           @if(isset($_GET['ref'])) value="{{$_GET['ref']}}" @endif
                                           placeholder="" required>
                                </div>
                                <div class="invalid-feedback errors" id="register_password"></div>
                            </div>
                        </div>
                        @csrf
                        <hr class="mt-4">
                        <div class="pt-2 text-end">
                            <div class="nav-link-inline fs-sm change-number"
                                 onclick="ChangeTab('register-tab','signin-tab')">تغییر شماره
                            </div>
                            <button id="register-btn" data-form="register-tab" data-text="ثبت نام"
                                    class="login-btn btn btn-success btn-shadow" type="submit">ثبت نام
                            </button>
                        </div>
                    </form>
                    <form method="post" action="{{secure_url('login/login')}}" class="needs-validation tab-pane fade"
                          novalidate id="login-tab">
                        <div class="mb-1">
                            <label class="form-label" for="su-password-2">پسورد</label>
                            <div class="password-toggle">
                                <label class="password-toggle-btn" style="" aria-label="نمایش/پنهان کردن">
                                    <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                </label>
                                <input class="form-control ltr" type="password" name="login_password"
                                       id="su-password-2" required>
                            </div>
                            <div class="invalid-feedback">لطفا کلمه عبور خود را وارد کنید</div>
                            <div class="invalid-feedback errors login_password"></div>
                            <input class="user-mobile" type="hidden" name="login_mobile" required>
                            <input name="product_id" class="product-id" type="hidden">
                            <input name="type" class="type" type="hidden">
                        </div>
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="form-check">
                            </div>
                            <div class="nav-link-inline fs-sm ona-pass-code reset-pass-btn"
                                 onclick="ChangeTab('login-tab','fast-login')">ورود با رمز یکبار مصرف
                            </div>
                            <div class="reset-pass-btn nav-link-inline fs-sm"
                                 onclick="resetPassword()">فراموشی رمز؟
                            </div>
                        </div>
                        @csrf
                        <hr class="mt-4">
                        <div class="pt-2 text-end">
                            <div class="nav-link-inline fs-sm change-number"
                                 onclick="ChangeTab('login-tab','signin-tab')">تغییر شماره
                            </div>
                            <button id="login-user-btn" data-form="login-tab" data-text="ورود "
                                    class="login-btn btn btn-success btn-shadow" type="submit">ورود
                            </button>
                        </div>
                    </form>
                    <form method="post" action="{{secure_url('login/fast-login')}}"
                          class="needs-validation tab-pane fade"
                          novalidate id="fast-login">
                        <div class="mb-1">
                            <label class="form-label" for="su-password-3">رمز یکبار مصرف</label>
                            <div class="password-toggle">
                                <label class="password-toggle-btn" style="" aria-label="نمایش/پنهان کردن">
                                    <input class="password-toggle-check" type="checkbox"><span
                                            class="password-toggle-indicator"></span>
                                </label>
                                <input class="form-control ltr" type="tel" name="fast_login_password"
                                       id="su-password-3" required>
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
                            <div class="nav-link-inline fs-sm ona-pass-code reset-pass-btn"
                                 onclick="ChangeTab('fast-login','login-tab')">ورود با کلمه عبور
                            </div>
                            <div class="nav-link-inline fs-sm reset-pass-btn"
                                 onclick="resetPassword()">فراموشی رمز؟
                            </div>
                        </div>
                        @csrf
                        <hr class="mt-4">
                        <div class="pt-2 text-end">
                            <div class="nav-link-inline fs-sm change-number"
                                 onclick="ChangeTab('fast-login','signin-tab')">تغییر شماره
                            </div>
                            <button id="fast-login-btn" data-form="fast-login" data-text="ورود"
                                    class="login-btn btn btn-success btn-shadow" type="submit">ورود
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
<!-- Footer-->

@if(\Jenssegers\Agent\Facades\Agent::isPhone())
    <aside class="col-lg-4">
        <!-- Sidebar-->
        <nav class="offcanvas offcanvas-collapse offcanvas-end border-start ms-lg-auto" id="menu-sidebar"
             style="max-width: 22rem;">
            <div class="offcanvas-cap align-items-center shadow-sm">
                <h2 class="h5 mb-0">منو</h2>
                <button class="btn-close ms-auto" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body py-grid-gutter py-lg-1 px-lg-4">
                <!-- Categories-->
                <div class="widget widget-categories">
                    <div class="accordion" id="shop-categories">
                        <!-- پیشنهادهای ویژه-->
                        <div class="accordion-item border-bottom">
                            <h3 class="accordion-header">
                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/')}}"
                                   target="_blank">
                                    <span class="d-flex align-items-center">
                                        <i class="fa-brands fa-shopify text-danger mt-n1 me-2"></i>شاپ مجازی</span>
                                </a>
                            </h3>
                        </div>
                        <!-- Bakery-->
                        <div class="accordion-item border-bottom">
                            <h3 class="accordion-header ">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#bakery" aria-expanded="false" aria-controls="bakery">
                                    <span class="d-flex align-items-center">
                                           <i class="ci-discount fs-lg text-danger mt-n1 me-2"></i>خدمات ما</span>
                                </button>
                            </h3>
                            <div class="collapse" id="bakery" data-bs-parent="#shop-categories">
                                <div class=" pt-1 pb-4">
                                    <div class="widget widget-links">
                                        <ul class="widget-list">
                                            <li class="widget-list-item">
                                                <a target="_blank" class="widget-list-link"
                                                   href="{{secure_url('/virtual-number')}}">خرید شماره مجازی</a>
                                            </li>
                                            <li class="widget-list-item">
                                                <a target="_blank" class="widget-list-link"
                                                   href="{{secure_url('/apple-id')}}">خرید اپل آیدی</a>
                                            </li>
                                            <li class="widget-list-item">
                                                <a target="_blank" class="widget-list-link"
                                                   href="{{secure_url('virtual-service/telegram/buy-real-telegram-members')}}">خرید
                                                    ممبر واقعی تلگرام</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-bottom">
                            <h3 class="accordion-header ">
                                <a class="nav-link-style d-block fs-md fw-normal py-3"
                                   href="{{secure_url('/contact-us')}}"
                                   target="_blank">
                                    <span class="d-flex align-items-center">
                                        <i class="fa-regular fa-phone-volume text-danger mt-n1 me-2"></i>تماس با ما</span>
                                </a>
                            </h3>
                        </div>
                        <div class="accordion-item border-bottom">
                            <h3 class="accordion-header ">
                                <a class="nav-link-style d-block fs-md fw-normal py-3"
                                   href="{{secure_url('/about-us')}}"
                                   target="_blank">
                                    <span class="d-flex align-items-center">
                                        <i class="fa-regular fa-address-card text-danger mt-n1 me-2"></i>درباره ما</span>
                                </a>
                            </h3>
                        </div>
                        <div class="accordion-item border-bottom">
                            <h3 class="accordion-header ">
                                <a class="nav-link-style d-block fs-md fw-normal py-3" href="{{secure_url('/blogs')}}"
                                   target="_blank">
                                    <span class="d-flex align-items-center">
                                        <i class="fa-regular fa-rss text-danger mt-n1 me-2"></i>وبلاگ ها</span>
                                </a>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="offcanvas-cap d-block  pt-5 pb-4 mb-2">
                <div class="d-flex mb-3"><i class="ci-support h4 mb-0 fw-normal text-primary mt-1 me-1"></i>
                    <div class="ps-2">
                        <div class="text-muted fs-sm">پشتیبانی</div>
                        <a class="nav-link-style fs-md" href="tel:+09302736430">09302736430</a>
                    </div>
                </div>
                <div class="d-flex mb-3"><i class="ci-mail h5 mb-0 fw-normal text-primary mt-1 me-1"></i>
                    <div class="ps-2">
                        <div class="text-muted fs-sm">ایمیل</div>
                        <a target="_blank" class="nav-link-style fs-md" href="mailto:info@shopmajazi.com">info@shopmajazi.com</a>
                    </div>
                </div>
                {{--                <h6 class="pt-2 pb-1">ما را دنبال کنید</h6>--}}
                {{--                <a class="btn-social bs-outline bs-twitter me-2 mb-2" href="#"><i class="ci-twitter"></i></a>--}}
                {{--                <a class="btn-social bs-outline bs-facebook me-2 mb-2" href="#"><i class="ci-facebook"></i></a><a class="btn-social bs-outline bs-instagram me-2 mb-2" href="#"><i class="ci-instagram"></i></a>--}}
                {{--                <a class="btn-social bs-outline bs-youtube me-2 mb-2" href="#"><i class="ci-youtube"></i></a>--}}
            </div>
        </nav>
    </aside>
    <div class="handheld-toolbar">
        <div class="d-table table-layout-fixed w-100">
            @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
                <a class="d-table-cell handheld-toolbar-item" href="{{secure_url('/user/home')}}">
                <span class="handheld-toolbar-icon">
                    <i class="fa-regular fa-user"></i>
                </span>
                    <span class="handheld-toolbar-label">
                  حساب کاربری
                </span>
                </a>
            @else
                <a class="d-table-cell handheld-toolbar-item" href="javascript:void(0)"
                   data-bs-toggle="modal"
                   data-bs-target="#signin-modal">
                <span class="handheld-toolbar-icon">
                     <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                          class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/>
                        <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                        <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"/>
                    </svg>
                </span>
                    <span class="handheld-toolbar-label">
                    ورود / ثبت نام
                </span>
                </a>
            @endif
            <div class="d-table-cell handheld-toolbar-item" aria-label="منو"
                 data-bs-target="#menu-sidebar"
                 data-bs-toggle="offcanvas"
                    {{--                     onclick="window.scrollTo(0, 0)--}}
            ><span
                        class="handheld-toolbar-icon"><i class="ci-menu"></i></span><span
                        class="handheld-toolbar-label">منو</span></div>
            <a class="d-table-cell handheld-toolbar-item" href="{{secure_url('/')}}">
                <span class="handheld-toolbar-icon">
                  <i class="fa-brands fa-shopify"></i>
                </span>
                <span class="handheld-toolbar-label">
               شاپ مجازی
                </span>
            </a>
        </div>
    </div>
    @endif
    </main>
    <footer class="bg-header pt-5" style="border-radius: 10px">
        <div class="px-lg-3 pt-2 pb-4">
            <div class="mx-auto px-3" style="max-width: 80rem;">
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-sm-4">
                        <div class="widget widget-links widget-light pb-2 mb-4">
                            <h3 class="widget-title text-light">شاپ مجازی اولین ارائه دهنده خدمات مجازی</h3>
                            <div class="form-text text-light opacity-50">سایت شاپ مجازی، بزرگترین وب سایت ارائه دهنده
                                خدمات
                                مجازی مانند شماره مجازی، اپل آیدی و... مفتخر می باشد با ارائه بالاترین کیفیت در
                                خدمات و ارزانترین قیمت، نظر شما دوستان عزیز را جلب کند.
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-sm-4">
                        <div class="widget widget-links widget-light pb-2 mb-4">
                            <h3 class="widget-title text-white">خدمات ما</h3>
                            <ul class="widget-list text-white">
                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="{{secure_url('/virtual-numbers')}}">خرید شماره مجازی</a></li>
                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="{{secure_url('/apple-id')}}">خرید اپل آیدی</a>
                                </li>
                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="{{secure_url('/virtual-service/telegram/buy-real-telegram-members')}}">خرید
                                        ممبر واقعی تلگرام</a>
                                </li>
                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="{{secure_url('/virtual-service/telegram/buy-fake-telegram-member')}}">خرید
                                        ممبر فیک تلگرام</a>
                                </li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('accounts/telegram-premium')}}">خرید
                                        تلگرام پرمیوم</a></li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('accounts/trading-view')}}">خرید
                                        اکانت تریدینگ ویو</a></li>
                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="{{url('/static-ip')}}">
                                       خرید آی پی ثابت
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-sm-4">
                        <div class="widget widget-links widget-light pb-2 mb-4">
                            <h3 class="widget-title text-light">لینک های پر کاربرد</h3>
                            <ul class="widget-list">
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('/faq')}}">سوالات متداول</a></li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('about-us')}}">درباره ما</a></li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('/contact-us')}}">تماس باما</a></li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('/blog')}}">وبلاگ</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-sm-4">
                        <div class="widget widget-links widget-light pb-2 mb-4">
                            <h3 class="widget-title text-light">حساب کاربری</h3>
                            <ul class="widget-list">
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('/user/login')}}">ورود / ثبت نام</a>
                                </li>
                                <li class="widget-list-item"><a class="text-white widget-list-link"
                                                                href="{{secure_url('/user/home')}}">حساب کاربری</a></li>
                                {{--                            <li class="widget-list-item"><a class="text-white widget-list-link" href="{{secure_url('/refferal')}}">کسب درآمد</a></li>--}}
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-4 col-sm-4">
                        <div class="widget widget-links widget-light pb-2 mb-4">
                            <h3 class="widget-title text-light">لینک های ویژه</h3>
                            <ul class="widget-list">

                                <li class="widget-list-item">
                                    <a class="text-white widget-list-link"
                                       href="https://khanemohtava.com/content">
                                       سفارش تولید محتوا متنی
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-header">
            <div class="d-sm-flex justify-content-between align-items-center mx-auto px-3" style="max-width: 80rem;">
                <div class="fs-xs text-light opacity-50 text-center text-sm-start py-3">© کلیه حقوق محفوظ است <a
                            class="text-light" href="{{secure_url('/')}}" target="_blank" rel="noopener">شاپ مجازی</a>
                </div>
                <div class="py-3">
                    <a class="btn-social bs-light bs-twitter me-2 mb-2" target="_blank" aria-label="توییتر"
                       href="https://twitter.com/ShopMajazi"><i class="ci-twitter"></i></a>
                    <a class="btn-social bs-light bs-instagram me-2 mb-2" target="_blank" aria-label="اینستاگرام"
                       href="https://www.instagram.com/shopmajazi_com/"><i class="ci-instagram"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <!-- Back To Top Button--><a class="btn-scroll-top" href="#top" aria-label="بالا" data-scroll data-fixed-element><i
                class="btn-scroll-top-icon fa-light fa-circle-up"></i><span
                class="btn-scroll-top-tooltip text-muted fs-sm me-2">بالا</span></a>
    <!-- Vendor scrits: js libraries and plugins-->
    <script src="{{secure_url('site/assets/js/jquery.min.js')}}"></script>
    <script src="{{secure_url('site-v2/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{secure_url('site-v2/vendor/simplebar/dist/simplebar.min.js')}}"></script>
    <script src="{{secure_url('site-v2/vendor/tiny-slider/dist/min/tiny-slider.js?v=2')}}"></script>
    <script src="{{secure_url('site-v2/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js')}}"></script>
    <script src="{{secure_url('admin/vendor/toastr/js/toastr.min.js')}}"></script>
    <!-- Main theme script-->
    @yield('footer-2')
    <script src="{{secure_url('site-v2/js/theme.min.js?v=2')}}"></script>
    <script src="{{secure_url('js/jquery.form.min.js')}}" type="text/javascript"></script>
    <script src="{{secure_url('plugins/timer/jquery.simple.timer.js?v=2')}}"></script>
    <script type="text/javascript">
        !function () {
            var i = "BAYeUK", a = window, d = document;

            function g() {
                var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i,
                    l = localStorage.getItem("goftino_" + i);
                g.async = !0, g.src = l ? s + "?o=" + l : s;
                d.getElementsByTagName("head")[0].appendChild(g);
            }

            "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1);
        }();
    </script>
    <script>
        if ('OTPCredential' in window) {
            window.addEventListener('DOMContentLoaded', e => {
                const input = document.querySelector('input[autocomplete="one-time-code"]');
                if (!input) return;
                const ac = new AbortController();
                navigator.credentials.get({
                    otp: {transport: ['sms']},
                    signal: ac.signal
                }).then(otp => {
                    $('#GetCode').val(otp.code);
                }).catch(err => {
                    console.log(err);
                });
            });
        }

        $(document).on('click', '.slow-scroll', function () {
            var target = $(this).data('target');
            $([document.documentElement, document.body]).animate({scrollTop: $("#" + target).offset().top - 250}, 1000);
        })

        function error(error) {
            toastr.error(error, "خطا", {
                positionClass: "toast-top-right",
                timeOut: 5e3,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                preventDuplicates: !0,
                onclick: null,
                showDuration: "300",
                hideDuration: "1000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                tapToDismiss: !1
            });
        }

        function success(error) {
            toastr.success(error, "", {
                positionClass: "toast-top-right",
                timeOut: 5e3,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                preventDuplicates: !0,
                onclick: null,
                showDuration: "300",
                hideDuration: "1000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                tapToDismiss: !1
            });
        }

        @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())

        $(document).on('click', '.sing-in', function () {
            $('.tab-pane').removeClass('show active');
            $('#signin-tab').addClass('show active');
            $('#signin-tab').show();
            $('#login-mobile').val('');
            $('#login-mobile').prop('placeholder', '09123450000');
            $('#Login-tab-header').empty().append('<i class="ci-unlocked me-2 mt-n1"></i>ورود یا ثبت نام');
        });

        $(document).ready(function () {
            $('button').prop('disabled', false);
            $('.login-btn').click(function () {
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
                        if (formName == 'signin-tab') {
                            if (data.header != null) {
                                $('#Login-tab-header').empty().append(data.header);
                            }
                            $('#signin-tab').hide();
                            $('#' + data.type + '-tab').addClass('active show');
                            $('.user-mobile').val($('#login-mobile').val());
                            if (data.type == 'register') {
                                $('#time-area').empty().append('<span class="register-number" data-seconds-left="' + data.time + '" style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>');
                                $('.register-number').startTimer();
                                var time = data.time * 1000;
                                setTimeout(function () {
                                    $('#new-code-request').show();
                                }, time);
                            }
                        } else if (formName == 'register-tab') {
                            $('#signin-modal').modal('toggle');
                            success('ثبت نام با موفقیت انجام شد.');
                            location.reload();
                        } else if (formName == 'login-tab' || formName == 'fast-login') {
                            $('#signin-modal').modal('toggle');
                            success('عملیات ورود با موفقیت انجام شد.');
                            location.reload();
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    },
                    error: function (err) {
                        console.log(err);
                        for (const [key, value] of Object.entries(err.responseJSON.error)) {
                            $('#' + formName + ' .' + key).empty().text(value).show();
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    }
                });
                return false;
            });
        });

        $(document).on('click', '.request-new-code', function () {
            $.ajax({
                url: '{{secure_url('/login/new-register-code')}}',
                dataType: "JSON",
                method: 'GET',
                data: {
                    mobile: $('.user-mobile').val(),
                },
                success: function (data) {
                    success('کد جدید با موفقیت ارسال شد.');
                    $('#time-area').empty().append('<span class="register-number" data-seconds-left="120" style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>');
                    $('.register-number').startTimer();
                    $('#new-code-request').hide();
                    setTimeout(function () {
                        $('#new-code-request').show();
                    }, 120000);
                }, error: function (err) {
                    for (const [key, value] of Object.entries(err.responseJSON.error)) {
                        error(value)
                    }
                }
            });
        });

        function ChangeTab(oldTab, newTab) {
            $('#' + oldTab).removeClass('show active');
            $('#' + oldTab).hide();
            $('#' + newTab).show();
            $('#' + newTab).addClass('show active');
            $('#signin-modal .form-control').val('');
            if (newTab == 'fast-login') {
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

        function resetPassword() {
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
                    $('#login-tab').addClass('show active');
                    $('#login-tab').show();
                }, error: function (err) {
                    console.log(err)
                    for (const [key, value] of Object.entries(err.responseJSON.error)) {
                        error(value)
                    }
                }
            });
        }

        @endif

    </script>
    @yield('footer')
    <!---start GOFTINO code--->
    <script type="text/javascript">
        !function () {
            var i = "BAYeUK", a = window, d = document;

            function g() {
                var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i,
                    l = localStorage.getItem("goftino_" + i);
                g.async = !0, g.src = l ? s + "?o=" + l : s;
                d.getElementsByTagName("head")[0].appendChild(g);
            }

            "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1);
        }();
        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        window.addEventListener('goftino_ready', function () {
            Goftino.setUser({
                email : '{{\Illuminate\Support\Facades\Auth::guard('user')->user()->email}}',
                name : '{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}',
                phone : '{{\Illuminate\Support\Facades\Auth::guard('user')->user()->mobile}}',
                forceUpdate : true
            });
        });
        @endif
    </script>
    <!---end GOFTINO code--->
    </body>
    </html>
