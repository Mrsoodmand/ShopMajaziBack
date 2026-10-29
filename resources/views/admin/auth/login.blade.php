<!DOCTYPE html>
@php $jdf = new \App\lib\Jdf();@endphp
<html direction="rtl" dir="rtl" style="direction: rtl" >
<head><base>
    <title>مدیریت شاپ مجازی</title>
    <meta charset="utf-8" />
    <meta name="description" content="شاپ مجازی، بزرگترین سایت ارائه دهنده خدمات مجازی" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="پنل مدیریت شاپ مجازی" />
    <meta property="og:url" content="https://keenthemes.com/metronic" />
    <meta property="og:site_name" content="پنل مدیریت شاپ مجازی" />
    <link rel="canonical" href="{{url('/')}}" />
    <link rel="shortcut icon" href="{{url('/')}}/site-v2/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{url('/')}}/site-v2/assets/plugins/global/plugins.bundle.rtl.css" rel="stylesheet" type="text/css" />
    <link href="{{url('/')}}/site-v2/assets/css/style.bundle.rtl.css" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="bg-body">
<div class="d-flex flex-column flex-root">
    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-column flex-lg-row-auto w-xl-600px positon-xl-relative" style="background-color: #F2C98A">
            <div class="d-flex flex-column position-xl-fixed top-0 bottom-0 w-xl-600px scroll-y">
                <div class="d-flex flex-row-fluid flex-column text-center p-10 pt-lg-20">
                    <a href="{{url('/')}}" class="py-9 mb-5">
                        <img alt="Logo" src="{{url('/')}}/site-v2/assets/media/logos/logo-2.svg" class="h-60px" />
                    </a>
                    <h1 class="fw-bolder fs-2qx pb-5 pb-md-10" style="color: #986923;">به شاپ مجازی خوش آمدید</h1>
                    <p class="fw-bold fs-2" style="color: #986923;">پنل مدیریت شاپ مجازی
                        <br />امروز: {{$jdf->jdate('Y/m/d')}}</p>
                </div>
                <div class="d-flex flex-row-auto bgi-no-repeat bgi-position-x-center bgi-size-contain bgi-position-y-bottom min-h-100px min-h-lg-350px" style="background-image: url(assets/media/illustrations/sketchy-1/13.png"></div>
            </div>
        </div>
        <div class="d-flex flex-column flex-lg-row-fluid py-10">
            <div class="d-flex flex-center flex-column flex-column-fluid">
                <div class="w-lg-500px p-10 p-lg-15 mx-auto">
                    <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" method="post" action="{{route('admin-login')}}">
                        <div class="text-center mb-10">
                            <h1 class="text-dark mb-3">پنل مدیریت شاپ مجازی</h1>
                        </div>
                        <div class="fv-row mb-10">
                            <label class="form-label fs-6 fw-bolder text-dark">ایمیل</label>
                            <input class="form-control form-control-lg form-control-solid" type="text" name="email" autocomplete="off" />
                        </div>
                        <div class="fv-row mb-10">
                            <div class="d-flex flex-stack mb-2">
                                <label class="form-label fw-bolder text-dark fs-6 mb-0">کلمه عبور</label>
                            </div>
                            <input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="off" />
                        </div>
                        <div class="text-center">
                            <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
                                <span class="indicator-label">ادامه</span>
                                <span class="indicator-progress">لطفا صبر کنید...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>var hostUrl = "assets/";</script>
<script src="{{url('/')}}/site-v2/assets/plugins/global/plugins.bundle.js"></script>
<script src="{{url('/')}}/site-v2/assets/js/scripts.bundle.js"></script>
<script src="{{url('/')}}/site-v2/assets/js/custom/authentication/sign-in/general.js"></script>
</body>
</html>
