@extends('site-v2.layout.master',['MetaTitle' => $PageSeo['title'] ?? 'درباره ما | شاپ مجازی'])
@section('content')
    <main class="container-fluid px-0">
        <section class="row g-0">
            <div class="col-md-10 px-3 px-md-5 py-5 offset-lg-1">
                <div class="mx-auto py-lg-5">
                    <h1 class="h3 pb-3 text-center">{{ $PageSeo['h1'] ?? 'درباره ما' }}</h1>
                    <p class="fs-sm pb-3 text-muted" style="text-align: justify;line-height: 30px; color: #585858!important;">
                        مجموعه شاپ مجازی با هدف دور زدن تحریم ها و همچنین ارائه خدمات و سرویس های بین المللی برای رشد و تعالی جامعه ایرانی از سال 1400 کار خود را آغاز کرد.
                        <br>
                        یکی از وظایف مهم شاپ مجازی ارائه بهترین خدمت با بهترین قیمت و ارزان ترین برای مورد استفاده قرار گرفتن در تمامی اقشار جامعه میباشد.
                        <br>
                        تیم ما به صورت شبانه روزی تلاش میکند بتوانذ تحریم ها را از پیش رو برداشته و بهترین قیمت را برای اکانت های مورد نظر ارائه داد. در اوضاعی که کشورهای غربی به ناحق کشور عزیزمان را از سیستم های مالی بین المللی کنار گذاشته اند ما همواره کوشیده ایم دسترسی به سیستم های روز دنیا را برای کاربر ایرانی فراهم کنیم.
                        <br>
                        از تحریم های پی پال گرفته تا تحریم های ویزا و مسترکارت، از نبود شعبات وسترن یونیون و حتی بسته شدن حساب های ایرانیان خارج از کشور...
                        <br>
                        امروز شاپ مجازی با نماد اعتماد و تاییدیه از وزارت صنعت معدن و تجارت بیش از 50 هزار اشتراک موفق را در کمتر از سه سال به دست کاربر رسانده و تاحدودی در ارائه این نوع خدمت موفق بوده است.
                        <br>
                        تیم شاپ مجازی با عضویت جوانان نخبه ایرانی همواره به راه خود برای پیدا کردن روش های ارائه اکانت با کیفیت و ارزان قیمت و همچنین پشتیبانی کامل از آن ادامه خواهد داد.
                    </p>
                </div>
            </div>
        </section>
    </main>
@endsection

@section('header')

@endsection

@section('footer')
    <script>
        $('.submit-btn').click(function () {
            var BtnId = $(this).attr('id');
            var text = $(this).data('text');
            var form = $(this).data('form');
            $('#' + BtnId).html('لطفا صبر کنید...');
            $('#' + BtnId).attr('disabled', true);
            form = document.querySelector('#' + form);
            $(form).ajaxSubmit({
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
                    console.log('error')
                    if (data.responseJSON.message) {
                        error('');
                    } else {
                        data.responseJSON.error.forEach(function (value) {
                            error(value);
                        });
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);

                }
            });

            return false;
        });
    </script>
@endsection
@section('seo')
    @php
        // admin › SEO (App\Services\PageSeo), else the page's own text
        $seo['meta_title'] = $PageSeo['title'] ?? 'درباره ما | شاپ مجازی';
        $seo['meta_desc'] = $PageSeo['description'] ?? 'درباره شاپ مجازی بیشتر بدانید';
        $seo['image'] = $PageSeo['image'] ?? secure_url('upload/logo/logo-v5.jpg');
    @endphp
    @include('site-v2.layout.seo')
@endsection
