@extends('site-v2.layout.master')
@section('content')
    <section class="ps-lg-4 pe-lg-3 pt-4">
        <section class="container-fluid tns-carousel">
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div>
                    <div class="rounded-3 px-md-5 text-center text-xl-start"
                         style="background-color: #59c879;position: relative;height: 10rem;margin-bottom: 100px;">
                        <div class="d-xl-flex justify-content-between align-items-center px-4 px-sm-5 mx-auto"
                             style="max-width: 1226px;">
                            <div class="py-3 me-xl-4 mx-auto mx-xl-0" style="max-width: 490px;">
                                <h2 class="h1 text-light">شاپ مجازی</h2>
                                <p class="text-light pb-4">ارائه دهنده انواع خدمات مجازی</p>
                            </div>

                        </div>
                        <div class="bg-light shadow-lg rounded-3 overflow-hidden"
                             style="width: 90%;position: absolute;top: 100px;left: 17px;">
                            <div class="row">
                                <section class="col-lg-12 pt-2 pt-lg-12 pb-4">
                                    <div class="pt-2 px-1 pe-lg-0 ps-xl-5">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3">
                                            <div class="py-1 w-50" style="padding: 10px">
                                                <a class="btn btn-outline-info text-dark btn-sm w-100"
                                                   href="{{url('/virtual-number')}}">خرید شماره مجازی</a></div>
                                            <div class="py-1 w-50" style="padding: 10px">
                                                <a class="btn btn-outline-info text-dark btn-sm w-100"
                                                   href="{{url('/google-voice')}}">خرید شماره دائمی</a>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                                            <div class="py-1 w-100" style="padding: 10px">
                                                <a class="btn btn-outline-info text-dark btn-sm w-100"
                                                   href="{{url('static-ip')}}">خرید آی پی ثابت</a></div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="tns-carousel-inner"
                     data-carousel-options="{&quot;items&quot;: 1,&quot;autoplayTimeout&quot;: 8000,&quot;loop&quot;: true,&quot;autoplay&quot;: true, &quot;mode&quot;: &quot;gallery&quot;, &quot;nav&quot;: false, &quot;responsive&quot;: {&quot;0&quot;: {&quot;nav&quot;: true, &quot;controls&quot;: false}, &quot;576&quot;: {&quot;nav&quot;: false, &quot;controls&quot;: true}}}">
                    <div>
                        <div class="rounded-3 px-md-5 text-center text-xl-start" style="background-color: #59c879;">
                            <div class="d-xl-flex justify-content-between align-items-center px-4 px-sm-5 mx-auto"
                                 style="max-width: 1226px;">
                                <div class="py-5 me-xl-4 mx-auto mx-xl-0" style="max-width: 490px;">
                                    <h2 class="h1 text-light"> شماره دائمی آمریکا </h2>
                                    <p class="text-light pb-4">شماره های مجازی دائمی آمریکا با قابلیت ها مختلف از جمله
                                        برقراری تماس و ارسال پیامک </p>
                                    <h5 class="text-light pb-3">خرید شماره دائمی آمریکا</h5>
                                </div>
                                <div><img src="{{url('upload/image/google-voice-v.png')}}" alt="Image"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="rounded-3 px-md-5 text-center text-xl-start" style="background-color: #1a6fb0;">
                            <div class="d-xl-flex justify-content-between align-items-center px-4 px-sm-5 mx-auto"
                                 style="max-width: 1226px;">
                                <div class="py-5 me-xl-4 mx-auto mx-xl-0" style="max-width: 490px;">
                                    <h2 class="h1 text-light">پشتیبانی 24 ساعته</h2>
                                    <p class="text-light pb-4">فروش انواع شماره مجازی، خدمات اینستاگرام و تلگرام و انواع
                                        خدمات مجازی</p>
                                    <h5 class="text-light pb-3">همین الان ثبت نام کنید</h5>
                                </div>
                                <div><img src="{{url('upload/image/support-v3.png')}}" alt="Image"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>
        @if(\Jenssegers\Agent\Facades\Agent::isPhone())

            @include('site-v2.components.why-shopmajazi')

            <section class="pb-4 pt-2 pt-md-3 pb-sm-3 bg-secondary">
                <div class="container">
                    <h2 class="h1 text-dark">خدمات شاپ مجازی</h2>
                    <div class="swiper mySwiper tabs">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide countries country_filter" style="margin-right: 15px">
                                <div class="col-md-6 bg-position-center bg-size-cover">
                                    <img style="border-radius: 10px" src="{{url('image/social-member.jpg')}}" alt="">
                                </div>
                                <div class="col-md-6 px-3 px-md-5 py-5" style="padding-top: 0px!important;">
                                    <div class="mx-auto py-lg-5" style="max-width: 35rem;">
                                        <h2 class="pb-3 mt-3" style="font-size: 0.9rem!important;">ارائه دهنده انواع
                                            خدمات تلگرام
                                            و
                                            اینستاگرام</h2>
                                        <p class="fs-sm pb-3 text-muted">شاپ مجازی ارائه دهنده خدمات تلگرام ماننده فروش
                                            ممبر
                                            فیک و
                                            واقعی
                                            برای کانال یا گروه، بازدید پست و رای برای پست و همچنین ارائه دهنده انواع
                                            خدمات
                                            اینستاگرام
                                            ماننده
                                            فروش فالور فیک و واقعی، فروش لایک و بازدید (ویو پست)، و فروش کامنت با
                                            ارزانترین
                                            قیمت و
                                            بالاترین
                                            کیفیت بهمراه تحویل آنی</p>
                                        <a class="btn btn-primary btn-shadow" href="{{url('/virtual-service')}}">دیدن محصولات</a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide countries country_filter">
                                <div style="margin: auto;display: block"
                                     class="col-md-6 bg-position-center bg-size-cover">
                                    <img style="border-radius: 10px;margin: auto;display: block"
                                         src="{{url('/upload/virtual-number_1.jpg')}}"
                                         alt="خرید شماره مجازی - شاپ مجازی">
                                </div>
                                <div class="col-md-6 px-3 px-md-5 py-5" style="padding-top: 0px!important;">
                                    <div class="mx-auto py-lg-5" style="max-width: 35rem;">
                                        <h2 class="pb-3 mt-3" style="font-size: 0.9rem!important;">خرید شماره
                                            مجازی(تحویل
                                            فوری)</h2>
                                        <p class="fs-sm pb-3 text-muted">“شماره مجازی" جزء آن دسته واژه هایی است که
                                            امروزه
                                            در
                                            کسب و
                                            کارهای
                                            آنلاین و به خصوص بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود
                                            افراد
                                            می
                                            توانند
                                            با
                                            اهداف شخصی نیز از این شماره ها استفاده نمایند.
                                            "شاپ مجازی" فرصتی برای شما فراهم آورده است تا انواع شماره های مجازی اعم از
                                            شماره
                                            مجازی
                                            اینستاگرام، شماره مجازی تلگرام، شماره مجازی واتساپ، شماره مجازی اپل ایدی و
                                            ...
                                            را از
                                            هر
                                            کشور
                                            مد
                                            نظر خود به صورت لحظه ای با مقرون به صرفه ترین قیمت خریداری کنید.
                                        </p>
                                        <a target="_blank" class="btn btn-primary btn-shadow"
                                           href="{{url('/virtual-number')}}">خرید
                                            شماره مجازی</a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide countries country_filter">
                                <div class="col-md-6 bg-position-center bg-size-cover">
                                    <img style="border-radius: 10px" src="{{url('/upload/google-voice.jpg')}}"
                                         alt="خرید گوگل ویس - شاپ مجازی">
                                </div>
                                <div class="col-md-6 px-3 px-md-5 py-5" style="padding-top: 0px!important;">
                                    <div class="mx-auto py-lg-5" style="max-width: 35rem;">
                                        <h2 class="pb-3 mt-3" style="font-size: 0.9rem!important;">خرید شماره دائمی
                                            آمریکا</h2>
                                        <p class="fs-sm pb-3 text-muted">امروزه داشتن یک شماره دائمی خارجی برای اکثر کسب
                                            و
                                            کار ها
                                            امری
                                            ضروری و
                                            مهم هستش،
                                            اکثر شماره های مجازی به صورت یکبار مصرف و زماندار هستند ولی شما با خرید
                                            شماره
                                            دائمی
                                            آمریکا(گوگل
                                            ویس)، یک شماره دائمی آمریکا رو برای خود تهیه میکنید و میتونید داخل هر
                                            اپلیکشین و
                                            وبسایتی
                                            که
                                            قصد
                                            همکاری و دریافت خدمات از آنها را دارید بدون مشکل ثبتنام کنید. همچنین
                                            میتوانید با
                                            افرادی
                                            که
                                            خارج
                                            از کشور هستند تماس یا پیام ارسال کنید. این شماره ها کاملا امن هستند و شخصی
                                            جز
                                            شما به این
                                            شماره ها دسترسی نخواهد داشت</p>
                                        <a target="_blank" class="btn btn-primary btn-shadow"
                                           href="{{url('/google-voice')}}">خرید
                                            شماره
                                            دائمی آمریکا</a>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide countries country_filter">
                                <div style="margin: auto;display: block"
                                     class="col-md-6 bg-position-center bg-size-cover">
                                    <img style="border-radius: 10px;margin: auto;display: block"
                                         src="{{url('/upload/apple-id.jpg')}}"
                                         alt="خرید اپل آیدی - شاپ مجازی">
                                </div>
                                <div class="col-md-6 px-3 px-md-5 py-5" style="padding-top: 0px!important;">
                                    <div class="mx-auto py-lg-5" style="max-width: 35rem;">
                                        <h2 class="pb-3 mt-3" style="font-size: 0.9rem!important;">خرید اپل آیدی (تحویل
                                            آنی)</h2>
                                        <p class="fs-sm pb-3 text-muted">“شماره مجازی" جزء آن دسته واژه هایی است که
                                            امروزه
                                            در
                                            کسب و
                                            کارهای
                                            آنلاین و به خصوص بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود
                                            افراد
                                            می
                                            توانند
                                            با
                                            اهداف شخصی نیز از این شماره ها استفاده نمایند.
                                            "شاپ مجازی" فرصتی برای شما فراهم آورده است تا انواع شماره های مجازی اعم از
                                            شماره
                                            مجازی
                                            اینستاگرام، شماره مجازی تلگرام، شماره مجازی واتساپ، شماره مجازی اپل ایدی و
                                            ...
                                            را از
                                            هر
                                            کشور
                                            مد
                                            نظر خود به صورت لحظه ای با مقرون به صرفه ترین قیمت خریداری کنید.
                                        </p>
                                        <a target="_blank" class="btn btn-primary btn-shadow"
                                           href="{{url('/apple-id')}}">خرید
                                            اپل
                                            آیدی</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @else
            <section class="container-fluid pb-4 pt-2 pt-md-3 pb-sm-3">
                <div class="row">
                    <div class="col-md-7 bg-position-center bg-size-cover">
                        <div class="pt-3">
                            <div class="post-container">
                                <div class="post-content" id="content">
                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:20pt"><strong>اپل آیدی چیست و چرا به اپل آیدی نیاز است؟</strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">شرکت اپل از دیرباز به فکر امنیت کاربران خود بوده است. از طرفی با توجه به این که هر ساله یک نوآوری در تمامی تجهیزات خود از قبل آیفون، آیپاد، آیپد، مک و... دارد. به همین دلیل برای استفاده از آن یک شناسه تعریف کرده است. اگر شما بخواهید از این محصولات اپل استفاده کنید، نیاز به این اپل آیدی دارید.&nbsp; به این نکته توجه داشته باشید که بدون اپل آیدی استفاده شما از این محصولات اپل غیر ممکن خواهد بود.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">شما برای نصب App Store نیاز به خرید اپل آیدی خواهید داشت. این شروع ماجرا است. شما برای دسترسی به بخش بخش این محتوا باید اپل آیدی داشته باشید. از طرفی زمانی که شما تلفن همراه اپل خود را تغییر می‌دهید و به سادگی می‌توانید با اپل آیدی به تمامی بخش‌های آن دسترسی داشته باشید. به این نکته توجه داشته باشید که خرید اپل آیدی با از سایت‌های معتبر مانند شاپ مجازی انجام شود. در غیر این صورت ممکن است که شما با مشکلاتی مواجه شوید.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:center"><img alt="" src="/upload/apple-id/apple-store-min.webp" style="height:333px; width:500px">
                                    </p>

                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:16pt"><strong>آیا باید فقط خرید اپل آیدی کارتی انجام دهیم؟</strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">برخی از افراد فکر می‌کنند که فقط باید اپل آیدی خود را به صورت کارتی خریداری کنند. علت آن نیز این است که امنیت بالاتری دارد. باید به شما بگوییم که این موضوع اشتباه است. چه شما اپل&nbsp; آیدی خود را به صورت کارتی خریداری کنید و چه خرید اپل آیدی فوری انجام دهید برای شما تفاوتی نخواهد داشت. حتی در برخی مواقع اپل آیدی کارت امنیت پایین‌تری نیز دارد. علت آن نیز این است که اگر کسی به اپل آیدی کاری شما دسترسی داشته باشد، به تمامی اطلاعات شما در تلفن همراه شما دسترسی خواهد داشت.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:right">&nbsp;</p>

                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:16pt"><strong>چگونه بعد از خرید اپل آیدی امنیت آن را افزایش دهیم؟</strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">ممکن است برای شما این سوال به وجود آمده باشد که بعد از خرید اپل آیدی بهترین کار چیست؟ بعد از خرید اپل آیدی بهترین کار این است که رمز ورود خود را تغییر دهید. از طرفی شما باید ایمیل ریکاوری خود را نیز به آن اضافه کنید. ما در مجموعه‌ شاپ مجازی تمامی اپل‌ آیدی‌های خود را بدون دخالت شخص و به صورت تصادفی برای شما ارسال می‌کنیم. شما از این جهت می‌توانید خیالی آسوده داشته باشید.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">از طرفی زمانی که شما رمز خود را تغییر دهید و ایمیل ریکاوری آن را نیز فعال کنید، دیگر هیچ کس به غیر از شما به اپل آیدی&nbsp;شما دسترسی نخواهد داشت. اپل آیدی کارتی با اپل آیدی فوری تفاوتی ندارد. همه‌ی آن‌ها یک کار برای شما انجام می‌دهند. در خرید اپل آیدی مهم این است که تحویل فوری وجود داشته باشد. از طرفی شما بیاد اطمینان داشته باشید که اپل آیدی شما تست شده باشد. </span>
                                    </p>

                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:16pt"><strong>خرید اپل آیدی سفارشی چیست؟ </strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">ممکن است که شما بخواهید اپل آیدی شما از ابتدا با مشخصات واقعی شما ساخته شود. در این صورت می‌توانید از خدمات اپل آیدی سفارشی استفاده کنید.&nbsp; برای این خدمات نام و نام خانوادگی خود را وارد می‌کنید. یک ایمیل که تبدیل به اپل آیدی شما می شود را برای ما ارسال میکنید و در نهایت از هر کشوری که بخواهید اپل ایدی شما وریفای و ساخته می‌شود. البته توجه داشته باشید که خرید اپل آیدی سفارشی به صورت فوری انجام می‌شود، اما تحویل آن ممکن است که اندکی طول بکشد. اما تمام تلاش سایت شاپ مجازی بر این است که در کوتاهترین زمان ممکن آن را برای شما انجام دهد.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:center"><span style="font-size:14px"><img alt=""
                                                                                                             src="/upload/apple-id/apple-security-min.webp"
                                                                                                             style="height:333px; width:500px"></span>
                                    </p>

                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:16pt"><strong>خرید اپل آیدی از دیجیکالا بهتر است و یا خرید اپل آیدی از ایران موبایل؟ </strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">باید به شما بگویم که هیچ کدام مناسب نیست، بلکه خرید اپل آیدی از سایت شاپ مجازی برای شما بهترین انتخاب است. چرا که قیمت اپل آیدی در سایت ما بسیار مناسب است و از طرفی خدمات و ویژگی‌هایی که شما در سایت شاپ مجازی پیدا می‌کنید، در هیچ کجای دیگر وجود ندارد. از جمله این خدمات می‌توان به موارد زیر اشاره کرد.</span>
                                    </p>

                                    <ul>
                                        <li dir="RTL" style="text-align: justify;"><span style="font-size:14px">تمامی ایمیل‌های مورد استفاده برای اپل آیدی، ایمیل واقعی می‌باشند و هیچ کدام از آن‌ها اسپم نمی‌باشند. به همین دلیل است که خرید اپل آیدی آنلاین از سایت شاپ مجازی بهترین انتخاب است.</span>
                                        </li>
                                        <li dir="RTL" style="text-align: justify;"><span style="font-size:14px">تمامی خدمات شما به صورت اتوماتیک و بدون دخالت افراد انجام می‌شود. این موضوع می‌تواند امنیت شما را به صورت قابل توجه بالا ببرد. در نهایت به این نکته نیز توجه داشته باشد که توصیه ما به شما این است که پس از خرید رمز آن را تغییر دید و ایمیل ریکاوری مناسب برای آن ثبت کنید. </span>
                                        </li>
                                        <li dir="RTL" style="text-align: justify;"><span style="font-size:14px">در صورتی که شما در هر بخش با مشکل مواجه شدید، همکاران ما در اسرع وقت در خدمت شما خواهند بود. از مهمترین ویژگی‌های خرید از سایت شاپ مجازی این است که پشتیبانان ما در کنار شما خواهند بود. </span>
                                        </li>
                                        <li dir="RTL" style="text-align: justify;"><span style="font-size:14px">خرید اپل آیدی آنلاین از سایت ما بدون تاریخ انقضاء خواهد بود. در صورتی که شما پس از خرید از سایت‌های معمولی می‌توان گفت که اپل آیدی با تاریخ محدود خریداری کرده‌اید. </span>
                                        </li>
                                        <li dir="RTL" style="text-align: justify;"><span style="font-size:14px">در نهایت به این نکته توجه داشته باشید که پس خرید اپل آیدی کارتی و یا خرید اپل آیدی آنلاین ارزان تمامی اطلاعات اپل آیدی از روی سرورهای ما پاک خواهد شد.</span>
                                        </li>
                                    </ul>

                                    <h2 dir="RTL" style="text-align:justify"><span style="font-size:16pt"><strong>بعد از خرید اپل آیدی فوری، چه کاری باید انجام دهیم؟</strong></span>
                                    </h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">سعی کنید تمامی مراحلی که در این بخش گفته می‌شود را به صورت پیوسته انجام دهید، تا در صورتی که خواستید رمز عبور خود را بازیابی کنید و یا تغییراتی برای خرید فضای آپلود انجام دهید مشکلی نداشته باشید. </span>
                                    </p>

                                    <ul>
                                        <li>
                                            <h3 style="text-align:justify"><span style="font-size:14px">بعد از خرید اپل آیدی امریکا رمز آن را تغییر دهید</span>
                                            </h3>
                                        </li>
                                    </ul>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">انجام این کار بسیار ساده است، شما پس از خرید اپل آیدی باید به سایت اپل ساپورت مراجعه کنید. قسمت Security &nbsp;را انتخاب کنید. سپس گزینه &nbsp;Change Passwordانتخاب کنید. از شما در خواست می‌شود که رمز قبلی خود را وارد کنید. سپس باید رمز جدید خود را وارد کنید. به این نکته توجه داشته باشید رمز اپل آیدی خود را یک رمز محکم و چند رشته‌ای انتخاب کنید. </span>
                                    </p>

                                    <ul>
                                        <li>
                                            <h3 style="text-align:justify"><span style="font-size:14px">سعی کنید که مشخصات پیش فرض را تغییر دهید و مشخصات واقعی خود را وارد کنید</span>
                                            </h3>
                                        </li>
                                    </ul>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">زمانی که شما قصد خرید اپل آیدی آنلاین و یا کارتی را دارید بهترین کار این است پس از خرید، اطلاعاتی که به صورت پیش فرض وارد شده است را تغییر دهید. البته باید به شما بگوییم که در این زمان وارد کردن اطلاعات واقعی برای شما مشکل ساز نخواهد بود. شما به دلیل اطلاعات حقیقی خود بلاک نخواهید شد. بلکه برای برخی از امور شخصی این اطلاعات به شما کمک خواهد کرد. تمامی این تنظیمات از بخش Setting تلفن شما انجام خواهد شد. </span>
                                    </p>

                                    <ul>
                                        <li>
                                            <h3 style="text-align:justify"><span style="font-size:14px">سوالات اپل ایدی خود را تغییر دهید</span>
                                            </h3>
                                        </li>
                                    </ul>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">بهتر است که برای افزایش امنیت خود پس از خرید اپل آیدی، سوالات امنیتی که به صورت رندوم انتخاب شده است را تغییر دهید. برای این کار باید وارد صفحه <a
                                                    href="https://appleid.apple.com/" rel="nofollow"
                                                    style="color:#0563c1; text-decoration:underline">سایت اپل آیدی</a> شوید. سپس اپل آیدی و رمز جدید خود را وارد کنید. به دنبال گزینه Security باشید و گزینه Change Questions را انتخاب کنید. حال شما در این بخش می‌تونید سه سوال جدید انتخاب کنید. سپس به هر کدام از این سوالات به دقت پاسخ دهید و در نهایت آن را ذخیره کنید. ما معمولا پیشنهاد می‌دهیم که یک اسکرین شات از این صفحه داشته باشید و آن را در پیام‌های ذخیره شده تلگرام نگهداری کنید. </span>
                                    </p>

                                    <p dir="RTL" style="text-align:center"><span style="font-size:14px"><img alt=""
                                                                                                             src="/upload/apple-id/apple-logo-min.webp"></span>
                                    </p>

                                    <h2 dir="RTL" style="text-align:justify"><strong>آیا بعد از خرید اپل آیدی اضافه
                                            کردن ایمیل ریکاوری در اپل آیدی ضروری است؟</strong></h2>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">اگر شما قرار است که اپل آیدی خود را برای مدت طولانی نگهداری کنید. باید به شما بگوییم که بهترین کار اضافه کردن ایمیل نجات به اپل آیدی می‌باشد. انجام این کار بسیار ساده است. اما می‌تواند برای شما مزایای بسیار زیادی داشته باشد. وارد صفحه اصلی سایت اپل &nbsp;شوید. اپل آیدی و رمز عبور خود را وارد کنید، سپس گزینه Other options انتخاب کنید . ممکن است که از شما در خواست به روز رسانی داشته باشد آن را نادیده بگیرید. در نهایت گزینه Continue را بزنید و به پنل اصلی خود وارد شوید.</span>
                                    </p>

                                    <p dir="RTL" style="text-align:justify"><span style="font-size:14px">در این بخش شما باید گزینه Rescue Email را انتخاب کنید. سپس گزینه اضافه کردن ایمیل جدید را بزنید و آدرس یک ایمیل را وارد کنید. به ایمیل وارد شده بروید و آن را تایید کنید. به همین سادگی ایمیل نجات شما ساخته شد. توجه داشته باشید که انجام این کار برای شما ضروری است و می‌تواند برای شما در آینده بسیار مفید باشد. </span>
                                    </p>
                                </div>
                                <div style="display: block;width: 100%;height: 25px;background-image: linear-gradient(to bottom, rgb(30 28 28 / 0), rgb(241 243 244) 100%);margin-top: -23px;">

                                </div>
                                <div class="show-more-btn">
                                    <button class="btn btn-primary btn-shadow more-btn">مشاهده بیشتر</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 px-2 px-md-3 py-3">
                       <div class="row" style="padding-top: 0px!important;position: sticky;top: 7rem">
                         <h2>خدمات ما</h2>
                           <div class="col-12">
                               <img style="border-radius: 4px;height: 100px;width: 100%;" src="https://shopmajazi.com/image/social-member.jpg" alt="">
                           </div>
                           <div class="col-6 mt-3">
                               <img style="border-radius: 4px;height: 100px;width: 100%;" src="https://shopmajazi.com/image/social-member.jpg" alt="">
                           </div>
                           <div class="col-6 mt-3">
                               <img style="border-radius: 4px;height: 100px;width: 100%;" src="https://shopmajazi.com/image/social-member.jpg" alt="">
                           </div>
                       </div>
                    </div>
                </div>
            </section>
        @endif
    </section>
@endsection

@section('header')
    <link rel="stylesheet" href="{{URL::asset('site-v2/vendor/tiny-slider/dist/tiny-slider.css')}}"/>
    <link rel="stylesheet" href="{{url('/owl')}}/owl.carousel.min.css">
    <link rel="stylesheet" href="{{url('/owl')}}/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>

    <link href="{{URL::asset('NewPanel/vendor/owl-carousel/owl.carousel.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>

    <style>
        .fav-btn {
            position: absolute;
            left: 12px;
            top: 19px;
            padding: 5px 10px 5px 10px !important;
        }

        .swiper-slide {
            width: 88%;
        }

        .buy-btn {
            position: absolute;
            left: 18px;
            top: 19px;
            padding: 5px 10px 5px 10px !important;
        }

        .service-fav-btn {
            position: absolute;
            left: 12px;
            top: 1px;
            padding: 5px 10px 5px 10px !important;
        }

        .service-buy-btn {
            position: absolute;
            left: 11px;
            top: 38px;
            padding: 5px 10px 5px 10px !important;
        }

        .country-item:hover {
            background: beige !important;
            cursor: pointer;
        }

        .m-height-product {
            height: auto !important;
            min-height: 10%;
        }

        .product-area {
            height: 600px;
            overflow: auto
        }

        .country-image {
            width: 40px;
            position: absolute;
            right: 20px;
            top: 14px;
            border-radius: 10px;
        }

        .service-image {
            width: 40px;
            position: absolute;
            right: 20px;
            top: 14px
        }

        .number-text {
            width: 80%;
            text-align: start;
            margin-right: 50px;
        }

        .text-muted {
            text-align: justify;
        }

        @media screen and (max-width: 425px) {
            .m-height-product {
                height: auto !important;
                min-height: 10%;
            }

            .m--1 {
                margin-top: -5px;
            }

            .product-area {
                height: 600px;
                overflow: auto
            }

            .fav-btn {
                top: 10px;
                left: 5px;
            }

            .buy-btn {
                top: 10px;
                left: 25px;
            }

            .service-fav-btn {
                top: 10px;
                left: 5px;
            }

            .service-buy-btn {
                top: 10px;
                left: 25px;
            }

            .country-image {
                width: 40px;
                position: absolute;
                right: 20px;
                top: 9px;
                border-radius: 10px;
            }

            .service-image {
                width: 40px;
                position: absolute;
                right: 20px;
                top: 9px;
            }

            .number-text {
                width: 70%;
                text-align: start;
                margin-right: 50px;
            }
        }
    </style>
@endsection

@section('footer')
    <script src="{{URL::asset('NewPanel/vendor/owl-carousel/owl.carousel.js')}}"></script>
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: "auto",
            spaceBetween: 10,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    </script>
@endsection
