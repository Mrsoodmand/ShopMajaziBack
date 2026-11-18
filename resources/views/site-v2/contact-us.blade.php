@extends('site-v2.layout.master',['MetaTitle' => $PageSeo['title'] ?? 'تماس با ما | شاپ مجازی'])
@section('content')
    <!-- Page Title (Light)-->
    <div class="bg-header py-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{url('/')}}"><i class="ci-home"></i>شاپ مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">تماس با ما</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0 text-white">{{ $PageSeo['h1'] ?? 'تماس با ما' }}</h1>
            </div>
        </div>
    </div>
    <section class="container pt-4 mt-md-4 mb-5">
        <div class="row pt-4">
            <div class="col-md-4 col-sm-6 mb-grid-gutter">
                <div class="card border-0 shadow"><img class="card-img-top" src="{{secure_url('/')}}/upload/office-2.jpg" alt="Orlando">
                    <div class="card-body">
                        <h6>ایران - مشهد</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex pb-3 border-bottom"><i class="ci-location fs-lg mt-2 mb-0 text-primary"></i>
                                <div class="ps-3"><span class="fs-ms text-muted">پیدا کردن ما</span><a class="d-block text-heading fs-sm" href="#">مشهد- برج اداری پاژ1</a></div>
                            </li>
                            <li class="d-flex pt-2 pb-3 border-bottom"><i class="ci-phone fs-lg mt-2 mb-0 text-primary"></i>
                                <div class="ps-3"><span class="fs-ms text-muted">با ما تماس بگیرید</span><a class="d-block text-heading fs-sm" href="tel:+09302736430">09302736430</a></div>
                            </li>
                            <li class="d-flex pt-2"><i class="ci-mail fs-lg mt-2 mb-0 text-primary"></i>
                                <div class="ps-3"><span class="fs-ms text-muted">برای ما بنویس</span><a class="d-block text-heading fs-sm" href="mailto:shopmajazi.com@gmail.com">shopmajazi.com@gmail.com</a></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-sm-6 mb-grid-gutter">
             <div class="card border-0 shadow">
                 <div class="card-header">
                     <h2 class="h4 mt-2">از اینکه برای تماس با شاپ مجازی اقدام کردید، خوشحالیم!</h2>
                 </div>
                 <div class="card-body">
                     <form class="needs-validation mb-3" method="post" action="{{secure_url('/contact-us/submit')}}" id="ContactUs" novalidate>
                         <div class="row g-3">
                             <div class="col-sm-6">
                                 <label class="form-label" for="cf-name">نام شما : <span class="text-danger">*</span></label>
                                 <input class="form-control" name="name" type="text" id="cf-name" required>
                                 <div class="invalid-feedback">لطفا نام را وارد کنید</div>
                             </div>
                             <div class="col-sm-6">
                                 <label class="form-label" for="cf-email">آدرس ایمیل:&nbsp;<span class="text-danger">*</span></label>
                                 <input class="form-control" name="email" type="email" id="cf-email" placeholder="your@email.com" required>
                                 <div class="invalid-feedback">آدرس ایمیل را وارد کنید</div>
                             </div>
                             <div class="col-sm-6">
                                 <label class="form-label" for="cf-phone">تلفن:<span class="text-danger">*</span></label>
                                 <input class="form-control" name="mobile" type="tel" id="cf-phone" placeholder="0912123456" required>
                                 <div class="invalid-feedback">شماره تلفن را وارد کنید</div>
                             </div>
                             <div class="col-sm-6">
                                 <label class="form-label" for="cf-subject">موضوع:</label>
                                 <input class="form-control" name="subject" type="text" id="cf-subject" placeholder="عنوان کوتاه درخواست خود را ارائه دهید">
                             </div>
                             @csrf
                             <div class="col-12">
                                 <label class="form-label" for="cf-message">پیام:&nbsp;<span class="text-danger">*</span></label>
                                 <textarea class="form-control" name="message" id="cf-message" rows="6" placeholder="لطفاً درخواست خود را با جزئیات شرح دهید" required></textarea>
                                 <div class="invalid-feedback">متن را بنویسید</div>
                                 {{-- Captcha (App\Services\Captcha, admin › Captcha): filled in by the script below. --}}
                                 <input type="hidden" name="captcha_token" id="cf-captcha-token">
                                 <input type="hidden" name="captcha_id" id="cf-captcha-id" disabled>
                                 <div id="cf-captcha-number" class="mt-3" style="display:none">
                                     <label class="form-label" for="cf-captcha-answer">اعداد داخل تصویر را وارد کنید:&nbsp;<span class="text-danger">*</span></label>
                                     <div class="d-flex align-items-center gap-2">
                                         <img id="cf-captcha-img" alt="تصویر کد امنیتی" width="176" height="56" style="border-radius:.5rem;background:#f8fafc">
                                         <input class="form-control" name="captcha_answer" id="cf-captcha-answer" type="text" inputmode="numeric" autocomplete="off" dir="ltr" maxlength="5" style="max-width:10rem;letter-spacing:.3em;text-align:center" disabled>
                                         <button type="button" class="btn btn-link btn-sm" id="cf-captcha-refresh">تصویر جدید</button>
                                     </div>
                                 </div>
                                 <p id="cf-captcha-google" class="small text-muted mt-3 mb-0" style="display:none">این فرم با reCAPTCHA گوگل محافظت می‌شود. <a href="https://policies.google.com/privacy" target="_blank" rel="noreferrer">حریم خصوصی</a> · <a href="https://policies.google.com/terms" target="_blank" rel="noreferrer">شرایط</a></p>
                                 <button id="ContactUsBtn" data-text="ارسال پیام" data-form="ContactUs" class="submit-btn btn btn-primary mt-4" type="submit">ارسال پیام</button>
                             </div>
                         </div>
                     </form>
                 </div>
             </div>
            </div>
        </div>
    </section>
@endsection

@section('header')

@endsection

@section('footer')
    <script>
        // Captcha: the mode comes from the backend (admin › Captcha). Google v3 adds a token on submit; the number
        // mode (or Google blocked, with the fallback on) shows a picture to type. See App\Services\Captcha.
        var Captcha = {cfg: @json(app(\App\Services\Captcha::class)->publicConfig()), mode: 'off', google: null};
        Captcha.number = function () {
            Captcha.mode = 'number';
            $('#cf-captcha-google').hide();
            $('#cf-captcha-number').show();
            $('#cf-captcha-id, #cf-captcha-answer').prop('disabled', false);
            $('#cf-captcha-answer').val('');
            $.getJSON('/api/v1/storefront/captcha/number', function (r) {
                $('#cf-captcha-id').val(r.data.id);
                $('#cf-captcha-img').attr('src', r.data.image);
            });
        };
        Captcha.apply = function (cfg) {
            Captcha.cfg = cfg;
            if (cfg.driver === 'number') return Captcha.number();
            if (cfg.driver !== 'google' || !cfg.site_key) { Captcha.mode = 'off'; return; }
            Captcha.mode = 'google';
            $('#cf-captcha-google').show();
            $('head').append('<style>.grecaptcha-badge{visibility:hidden}</style>');
            var s = document.createElement('script');
            s.src = 'https://www.recaptcha.net/recaptcha/api.js?render=' + encodeURIComponent(cfg.site_key);
            s.onload = function () { grecaptcha.ready(function () { Captcha.google = grecaptcha; }); };
            s.onerror = function () { if (cfg.fallback) Captcha.number(); };
            document.head.appendChild(s);
            setTimeout(function () { if (!Captcha.google && cfg.fallback && Captcha.mode === 'google') Captcha.number(); }, 8000);
        };
        Captcha.token = function (done) {
            $('#cf-captcha-token').val('');
            if (Captcha.mode !== 'google' || !Captcha.google) return done();
            Captcha.google.execute(Captcha.cfg.site_key, {action: 'contact'}).then(function (t) { $('#cf-captcha-token').val(t); done(); }, function () { done(); });
        };
        Captcha.apply(Captcha.cfg);
        $('#cf-captcha-refresh').click(Captcha.number);

        $('.submit-btn').click(function () {
            var BtnId = $(this).attr('id');
            var text = $(this).data('text');
            var form = $(this).data('form');
            $('#' + BtnId).html('لطفا صبر کنید...');
            $('#' + BtnId).attr('disabled', true);
            form = document.querySelector('#' + form);
            Captcha.token(function () { $(form).ajaxSubmit({
                dataType: 'json',
                beforeSend: function () {
                    $('#' + BtnId).html('لطفا صبر کنید');
                },
                success: function (data) {
                    success(data.success)
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                error: function (data) {
                    var json = data.responseJSON || {};
                    if (json.data && json.data.captcha) { // a captcha error: show the picture (one try each)
                        if (json.code === 'captcha_number' || json.data.captcha.driver === 'number') Captcha.number(); else Captcha.apply(json.data.captcha);
                    } else if (Captcha.mode === 'number') {
                        Captcha.number();
                    }
                    if (json.error) {
                        json.error.forEach(function (value) {
                            error(value);
                        });
                    } else {
                        error(json.message || 'خطایی رخ داد. دوباره تلاش کنید.');
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);

                }
            }); });

            return false;
        });
    </script>
@endsection
@section('seo')
    @php
        // admin › SEO (App\Services\PageSeo), else the page's own text
        $seo['meta_title'] = $PageSeo['title'] ?? 'تماس با ما | شاپ مجازی';
        $seo['meta_desc'] = $PageSeo['description'] ?? 'جهت تماس با ما میتوانید از طریق راه ها ارتباطی اقدام نمایید';
        $seo['image'] = $PageSeo['image'] ?? secure_url('upload/logo/logo-v5.jpg');
    @endphp
    @include('site-v2.layout.seo')
@endsection
