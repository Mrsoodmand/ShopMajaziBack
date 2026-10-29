<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="enamad" content="838208" />

    <title>{{isset($MetaTitle) ? $MetaTitle : 'شاپ مجازی'}}</title>
    @yield('seo')
    <link rel="apple-touch-icon" sizes="180x180" href="{{secure_url('/')}}/upload/logo/apple-fav.jpg">
    <link rel="icon" type="image/png" sizes="32x32" href="{{secure_url('/')}}/upload/logo/32x32.jpg">
    <link rel="icon" type="image/png" sizes="16x16" href="{{secure_url('/')}}/upload/logo/16x16.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/tiny-slider/dist/tiny-slider.css?v1"/>
    <link rel="stylesheet" media="screen"
          href="{{secure_url('/site-v2')}}/vendor/lightgallery.js/dist/css/lightgallery.min.css"/>
    <link rel="stylesheet" href="{{secure_url('site-v2/css/theme.min.css?v25')}}">
    <link rel="stylesheet" href="{{secure_url('admin/vendor/toastr/css/toastr.min.css?v=4')}}">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-M9NKWXDK');</script>
    <!-- End Google Tag Manager -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "lbb2ig9kcg");
    </script>
    @yield('header')
</head>
<body class="handheld-toolbar-enabled">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M9NKWXDK"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="bg-light shadow-sm navbar-sticky">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menu-sidebar"
                    aria-label="منو"><span
                        class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand d-none d-sm-block me-4 order-lg-1 mt-1" href="{{secure_url('/')}}"><img
                        src="{{secure_url('/upload/logo/logo.svg')}}" width="142" height="38" alt="ShopMajazi.com"></a><a
                    class="navbar-brand d-sm-none me-2 order-lg-1" href="{{secure_url('/')}}" style="min-width: 8.625rem">
                <img src="{{secure_url('/upload/logo/logo.svg')}}" width="74" height="38" style="    width: 83%;
" alt="ShopMajazi.com"></a>
            <div class="navbar-toolbar d-flex align-items-center order-lg-3">
                <div class="navbar-tool dropdown ms-2">
                    @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
                        <button class="login-btn btn btn-primary navbar-tool ms-1 me-n1 sing-in" data-bs-toggle="modal"
                                data-bs-target="#signin-modal"><span class="navbar-tool-tooltip">حساب کاربری</span>ورود
                            یا ثبت نام
                        </button>
                    @else
                        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                            <a href="{{secure_url('/user/home')}}" target="_blank"
                               class="login-btn btn btn-primary navbar-tool ms-1 me-n1 sing-in"><span
                                        class="navbar-tool-tooltip">حساب کاربری</span>حساب کاربری
                            </a>
                        @else
                            <a class="navbar-tool-text ms-n1"
                               href="javascript:void(0);"><small>{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}</small>{{number_format(CheckWallet())}}
                                تومان </a>
                        @endif
                    @endif
                    @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
                        <div class="dropdown-menu dropdown-menu-end">
                            <div style="min-width: 14rem;">
                                <h6 class="dropdown-header">{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}
                                    عزیز، خوش آمدید</h6>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/orders')}}"><i
                                            class="fa-light fa-bags-shopping opacity-60 me-2"></i>داشبورد</a>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/orders')}}"><i
                                            class="fa-light fa-bags-shopping opacity-60 me-2"></i>سفارشات</a>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/referral')}}"><i
                                            class="fa-light fa-bags-shopping opacity-60 me-2"></i>کسب درآمد</a>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/tickets')}}"><i
                                            class="fa-duotone fa-messages opacity-60 me-2"></i>تیکت ها</a>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/profile')}}"><i
                                            class="fa-light fa-user opacity-60 me-2"></i>پروفایل</a>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/wallet')}}"><i
                                            class="fa-regular fa-file-invoice-dollar opacity-60 me-2"></i>افزایش موجودی</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item d-flex align-items-center" href="{{secure_url('user/logout')}}"><i
                                            class="ci-sign-out opacity-60 me-2"></i>خروج</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="collapse navbar-collapse me-auto order-lg-2" id="navbarCollapse">
                <!-- Search-->
                <div class="input-group d-lg-none my-3"><i
                            class="ci-search position-absolute top-50 start-0 translate-middle-y text-muted fs-base ms-3"></i>
                    <input class="form-control rounded-start" type="text" placeholder="جستجو برای محصول">
                </div>
                <!-- Primary menu-->
                <ul class="navbar-nav navbar-mega-nav" style="    position: inherit!important;">
                    <li><a href="{{secure_url('/virtual-numbers')}}" target="_blank" class="nav-item nav-link">خرید شماره مجازی</a></li>
                    <li><a href="{{secure_url('/apple-id')}}" target="_blank" class="nav-item nav-link">خرید اپل آیدی</a></li>
                    <li class="nav-item dropdown"><a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">خدمات مجازی</a>
                        <div class="dropdown-menu p-0">
                            <div class="d-flex flex-wrap flex-sm-nowrap px-2">
                                <div class="mega-dropdown-column pt-1 pt-lg-4 pb-4 px-2 px-lg-3">
                                    <div class="widget widget-links mb-4">
                                        <h6 class="fs-base mb-3 text-primary">خدمات تلگرام</h6>
                                        <ul class="widget-list">
                                            <li class="widget-list-item"><a class="widget-list-link" href="{{secure_url('/virtual-service/telegram/buy-real-telegram-members')}}">خرید ممبر واقعی تلگرام</a></li>
                                            <li class="widget-list-item"><a class="widget-list-link" href="{{secure_url('/virtual-service/telegram/buy-fake-telegram-member')}}">خرید ممبر فیک تلگرام</a></li>
                                            <li class="widget-list-item"><a class="widget-list-link" href="{{secure_url('/virtual-service/telegram/buy-telegram-last-post-view')}}">خرید ویو تلگرام</a></li>
                                            <li class="widget-list-item"><a class="widget-list-link" href="{{secure_url('/virtual-service/telegram/buy-telegram-vote-and-like')}}">خرید رای و لایک تلگرام</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="{{secure_url('/accounts')}}" data-bs-toggle="dropdown">خرید اکانت های خارجی</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{url('accounts/telegram-premium')}}">خرید تلگرام پرمیوم</a></li>
                            <li><a class="dropdown-item" href="{{url('/accounts/trading-view')}}">خرید اکانت تریدینگ ویو</a></li>
                        </ul>
                    </li>
                    <li><a href="{{secure_url('/blog')}}" target="_blank" class="nav-item nav-link">وبلاگ</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
 

