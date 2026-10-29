@extends('site-v2.layout.master',['MetaTitle' => 'شاپ مجازی - بزرگترین ارائه دهنده خدمات مجازی در کشور '])
@section('content')
    <section class="ps-lg-4 pe-lg-3 pt-4">
        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
            <div>
                <div class="rounded-3 px-md-5 text-center text-xl-start home-header" style="">
                    <div class="d-xl-flex justify-content-between align-items-center px-4 px-sm-5 mx-auto m-style-2">
                        <div class="py-3 me-xl-4 mx-auto mx-xl-0 m-style-3">
                            <h2 class="h1 text-light">شاپ مجازی</h2>
                            <p class="text-light pb-4">ارائه دهنده انواع خدمات مجازی</p>
                        </div>
                    </div>
                    <div class="bg-light shadow-lg rounded-3 overflow-hidden m-style-4">
                        <div class="row">
                            <section class="col-lg-12 pt-2 pt-lg-12 pb-4">
                                <div class="pt-2 px-1 pe-lg-0 ps-xl-5">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center pb-3">
                                        <div class="py-1 w-50 m-style-5">
                                            <a target="_blank" class="btn btn-outline-info text-dark btn-sm w-100"
                                               href="{{secure_url('/virtual-numbers')}}">خرید شماره مجازی</a></div>
                                        <div class="py-1 w-50 m-style-5">
                                            <a target="_blank" class="btn btn-outline-info text-dark btn-sm w-100"
                                               href="{{secure_url('/google-voice')}}">خرید شماره دائمی</a>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                                        <div class="py-1 w-50 m-style-5">
                                            <a target="_blank" class="btn btn-outline-info text-dark btn-sm w-100"
                                               href="{{secure_url('apple-id')}}">خرید اپل آیدی</a></div>
                                        <div class="py-1 w-50 m-style-5">
                                            <a target="_blank" class="btn btn-outline-info text-dark btn-sm w-100"
                                               href="{{secure_url('virtual-service/telegram/buy-real-telegram-members')}}">خرید ممبر واقعی تلگرام</a></div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            @include('site-v2.components.why-shopmajazi')
        @elseif(isset($_GET['test']))
            <style>
                .new-image-style{

                    max-height: 200px;
                    width: 100%;
                }
            </style>
            <section class="pb-4 pt-2 pt-md-3 pb-sm-3 bg-secondary">
                <div class="container">
                    <div class="row mt-3 p-1">
                        <div class="col-3">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                        <div class="col-8">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                    </div>
                    <div class="row mt-3 p-1">
                        <div class="col-4">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                        <div class="col-4">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                        <div class="col-4">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                    </div>
                    <div class="row mt-3 p-1">
                        <div class="col-8">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                        <div class="col-4">
                            <img class="m-s-8 new-image-style img-fluid" style=""
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                    </div>
                </div>
            </section>

        @else
            <section class="pb-4 pt-2 pt-md-3 pb-sm-3 bg-secondary">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 px-3 px-md-5 py-5 m-style-6">
                            <div class="mx-auto py-lg-5 m-style-7">
                                <h2 class="pb-3 m-style-8">خرید شماره مجازی(تحویل فوری)</h2>
                                <p class="fs-sm pb-3 text-muted">“شماره مجازی" جزء آن دسته واژه هایی است که امروزه در
                                    کسب و
                                    کارهای
                                    آنلاین و به خصوص بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود افراد می
                                    توانند
                                    با
                                    اهداف شخصی نیز از این شماره ها استفاده نمایند.
                                    "شاپ مجازی" فرصتی برای شما فراهم آورده است تا انواع شماره های مجازی اعم از شماره
                                    مجازی
                                    اینستاگرام، شماره مجازی تلگرام، شماره مجازی واتساپ، شماره مجازی اپل ایدی و ... را از
                                    هر
                                    کشور
                                    مد
                                    نظر خود به صورت لحظه ای با مقرون به صرفه ترین قیمت خریداری کنید.
                                </p>
                                <a target="_blank" class="btn btn-primary btn-shadow"
                                   href="{{secure_url('/virtual-numbers')}}">خرید شماره مجازی</a>
                            </div>
                        </div>
                        <div style="margin: auto;display: block" class="col-md-6 bg-position-center bg-size-cover">
                            <img class="m-s-8"
                                 src="{{secure_url('/upload/virtual-number-min.jpg')}}"
                                 alt="خرید شماره مجازی - شاپ مجازی">
                        </div>
                    </div>
                </div>
            </section>
            @include('site-v2.components.why-shopmajazi')
            <section class="container pb-4 pt-2 pt-md-3 pb-sm-3">
                <div class="row">
                    <div class="col-md-6 bg-position-center bg-size-cover">
                        <img class="m-s-9"
                             width="600" height="313"
                             src="{{secure_url('/upload/google-voice-min.jpg')}}"
                             alt="خرید گوگل ویس - شاپ مجازی">
                    </div>
                    <div class="col-md-6 px-3 px-md-5 py-5 m-style-6">
                        <div class="mx-auto py-lg-5 m-style-7">
                            <h2 class="pb-3 m-style-8">خرید شماره دائمی آمریکا</h2>
                            <p class="fs-sm pb-3 text-muted">امروزه داشتن یک شماره دائمی خارجی برای اکثر کسب و کار ها
                                امری
                                ضروری و
                                مهم هستش،
                                اکثر شماره های مجازی به صورت یکبار مصرف و زماندار هستند ولی شما با خرید شماره دائمی
                                آمریکا(گوگل
                                ویس)، یک شماره دائمی آمریکا رو برای خود تهیه میکنید و میتونید داخل هر اپلیکشین و وبسایتی
                                که
                                قصد
                                همکاری و دریافت خدمات از آنها را دارید بدون مشکل ثبتنام کنید. همچنین میتوانید با افرادی
                                که
                                خارج
                                از کشور هستند تماس یا پیام ارسال کنید. این شماره ها کاملا امن هستند و شخصی جز شما به این
                                شماره ها دسترسی نخواهد داشت</p>
                            <a target="_blank" class="btn btn-primary btn-shadow"
                               href="{{secure_url('/google-voice')}}">خرید
                                شماره
                                دائمی آمریکا</a>
                        </div>
                    </div>
                </div>
            </section>
            <section class="pb-4 pt-2 pt-md-3 pb-sm-3 bg-secondary">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 px-3 px-md-5 py-5 m-style-6">
                            <div class="mx-auto py-lg-5 m-style-7">
                                <h2 class="pb-3 m-style-8">خرید اپل آیدی (تحویل آنی)</h2>
                                <p class="fs-sm pb-3 text-muted">شاپ مجازی ، بزرگترین فروشنده آنلاین اپل آیدی با تحویل
                                    آنی، کیفیت تضمین شده و قیمت بسیار مناسب است. در شاپ مجازی می‌توانید بلافاصله پس از
                                    خرید، اپل ایدی مورد نظرتان را دریافت کنید. کافیست پس از انتخاب محصول، اطلاعات خود را
                                    وارد کنید و پرداخت را انجام دهید. تیم پشتیبانی مجرب ما نیز در تمام مراحل خرید اپل
                                    آیدی آماده راهنمایی شما خواهد بود. علاوه بر این، برای رفع مشکلات نصب و راه
                                    اندازی‌های پس از خریدن اپل ایدی، می‌توانید با ما در ارتباط باشید. برای خرید اپل ایدی
                                    با بالاترین کیفیت و تحویل آنی، شاپ مجازی را انتخاب کنید.
                                </p>
                                <a target="_blank" class="btn btn-primary btn-shadow"
                                   href="{{secure_url('/apple-id')}}">خرید
                                    اپل
                                    آیدی</a>
                            </div>
                        </div>
                        <div class="col-md-6 bg-position-center bg-size-cover m-s-10">
                            <img class="m-s-8" width="600" height="313"
                                 src="{{secure_url('/upload/apple-id-min.jpg')}}"
                                 alt="خرید اپل آیدی - شاپ مجازی">
                        </div>
                    </div>
                </div>
            </section>
        @endif
        <section class="container ps-lg-4 pe-lg-3 pt-4 ">
            <section class="mb-3 pb-3">
                <div class="shadow-lg rounded-3 overflow-hidden">
                    <div class="row content-area">
                        <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                                <div class="post-container">
                                    <div class="post-content" id="content">
                                        <h1>شاپ مجازی - بزرگترین ارائه دهنده خدمات مجازی در کشور</h1>
                                        <p><br/>
                                            <strong>شاپ مجازی</strong> یک مرکز خدمات دیجیتال مارکتینگ می&zwnj;باشد، که
                                            از مهمترین خدمات آن می&zwnj;توان به <strong><a
                                                        href="{{secure_url('/virtual-numbers')}}" target="_blank">خرید
                                                    شماره
                                                    مجازی</a></strong>،
                                            <strong>خرید گوگل ویس</strong> &laquo; خرید شماره مجازی دائمی آمریکا &raquo;،
                                            <strong><a href="{{secure_url('/apple-id')}}" target="_blank">خرید اپل
                                                    آیدی</a></strong> &laquo;ایمیل آماده، ایمیل دلخواه (سفارشی)&raquo;،
                                            <strong>خدمات مجازی</strong> اینستاگرام &laquo; خرید لایک، خرید فالوور، خرید
                                            کامنت &raquo;، خدمات مجازی تلگرام &laquo; خرید ممبر واقعی، خرید ممبر فیک،
                                            تبلیغات ویژه در کانال&zwnj;های تبلیغاتی و گروه&zwnj;های تبلیغاتی با بازدهی
                                            بالا &raquo; اشاره کرد. در بخش&zwnj;های بعدی به صورت مفصل به بررسی هر یک
                                            خواهیم پرداخت.&nbsp;</p>

                                        <p><br/>
                                            یکی دیگر از خدمات <strong>شاپ مجازی</strong> که به صورت پیشرفته و کاملا
                                            اختصاصی به تازگی اضافه شده است، تولید محتوای هدفمند و تخصصی می&zwnj;باشد.
                                            امروزه تولید محتوا یکی از سخت&zwnj;ترین موضوعات در دیجیتال مارکتینگ و سئو می&zwnj;باشد.
                                            چرا که الگوریتم&zwnj;های پیشرفته گوگل مانند الگوریتم MUM و آپدیت جدید
                                            الگوریتم E.A.T کار تولید محتوا را سختتر از هر زمانه دیگری کرده است.
                                            خوشبختانه بر اساس پیگیری&zwnj; های هیئت مدیره شاپ مجازی، توانستیم یک تیم
                                            حرفه&zwnj;ای برای تولید محتوا در چند سطح مختلف به شاپ مجازی اضافه کنیم.&nbsp;
                                        </p>

                                        <p><br/>
                                            <strong>شاپ مجازی</strong> خدمات بسیار زیادی در حوزه فضای مجازی و فضای وب
                                            ارائه می&zwnj;دهد که در حال حاضر امکان استفاده برای عموم ندارد. اما در آینده
                                            نزدیک تمامی این خدمات به صورت کاملا تخصصی و سطح بندی شده در اختیار شما
                                            عزیزان قرار خواهد گرفت. در ادامه به بررسی هر یک از خدمات خواهیم پرداخت. با
                                            این حال اگر شما در مورد هر یک از خدمات شاپ مجازی سوالی و یا چالشی داشتید، می&zwnj;توانید
                                            در بخش چت آنلاین با پشتیبانی مطرح کنید.&nbsp;</p>

                                        <h2><br/>
                                            خرید شماره مجازی&nbsp;</h2>

                                        <p><br/>
                                            امروزه کسب و کارهای مختلف و همچنین افراد مختلف نیاز به شماره مجازی خواهند
                                            داشت. برخی از شبکه&zwnj;های اجتماعی و یا اپلیکیشن&zwnj;های موجود در سطح وب
                                            نیز برای ارائه خدمات به شماره مجازی نیاز دارند. ممکن است شما هم دیده باشید
                                            برخی از سایت&zwnj;های اقدام به فروش شماره مجازی رایگان می&zwnj;کنند. ممکن
                                            است که با هزینه&zwnj;های بسیار کم به شما یک شماره مجازی تحویل دهند. اما مشکل
                                            این شماره های مجازی این است که پس از مدت کوتاهی قطع می&zwnj;شوند. از طرفی
                                            قابلیت دریافت پیامک نیز ندارند و کلیه اطلاعات شما از بین خواهد رفت.&nbsp;
                                        </p>

                                        <p><br/>
                                            <strong>شاپ مجازی</strong> با تهیه زیر ساخت&zwnj;های لازم اقدام به تهیه
                                            شماره&zwnj;های مجازی اختصاصی نموده است که پس از خرید شماره مجازی توسط شما،
                                            آن شماره از چرخه خرید خارج می&zwnj;شود و از طرفی غیر قابل بازیابی برای هیچ
                                            شخصی به جز شما خواهد بود. بنابراین شما با اطمینان می&zwnj;توانید از تمامی
                                            خدمات آن استفاده کنید. شماره&zwnj;های مجازی در شاپ مجازی متعلق به کشورهای
                                            مختلف می&zwnj;باشد. از جمله مهم ترین آن&zwnj;ها می&zwnj;توان به موارد زیر
                                            اشاره کرد.&nbsp;</p>
                                        <p><br/>
                                            1.&nbsp;&nbsp; &nbsp;شماره مجازی دائمی آمریکا<br/>
                                            2.&nbsp;&nbsp; &nbsp;شماره مجازی روسیه&nbsp;<br/>
                                            3.&nbsp;&nbsp; &nbsp;شماره مجازی آلمان&nbsp;<br/>
                                            4.&nbsp;&nbsp; &nbsp;شماره مجازی اوکراین&nbsp;<br/>
                                            5.&nbsp;&nbsp; &nbsp;شماره مجازی بلژیک<br/>
                                            6.&nbsp;&nbsp; &nbsp; شماره مجازی برزیل&nbsp;<br/>
                                            7.&nbsp;&nbsp; &nbsp;شماره مجازی بوسنی و هرزگوین<br/>
                                            8.&nbsp;&nbsp; &nbsp;شماره مجازی کامبوج&nbsp;<br/>
                                            9.&nbsp;&nbsp; &nbsp;و ...&nbsp;</p>

                                        <p><br/>
                                            <strong>شاپ مجازی</strong> شماره مجازی مورد نظر شما را به صورت اختصاصی برای
                                            برنامه&zwnj;های مختلف نیز ارائه می&zwnj;دهد که از برای بررسی بهتر آن&zwnj;ها
                                            می&zwnj;توان به موارد زیر اشاره کرد. البته قبل از هر چیزی به این موضوع نیز
                                            توجه داشته باشید که استفاده از هوش مصنوعی Chat GPT نیز برای ایرانیان با
                                            شماره مجازی امکان پذیر است که شاپ مجازی، امکان خرید شماره مجازی برای Chat
                                            GPT را هم فراهم کرده است.</p>

                                        <p><br/>
                                            1.&nbsp; &nbsp; شماره مجازی تلگرام<br/>
                                            2.&nbsp;&nbsp; &nbsp;شماره مجازی تیک تاک&nbsp;<br/>
                                            3.&nbsp;&nbsp; &nbsp;شمار مجازی توئیتر<br/>
                                            4.&nbsp;&nbsp; &nbsp;شماره مجازی لینکدین&nbsp;<br/>
                                            5.&nbsp;&nbsp; &nbsp;شماره مجازی واتس اپ&nbsp;<br/>
                                            6.&nbsp;&nbsp; &nbsp;شماره مجازی اوپن ای آی &laquo; چت جی پی تی &raquo;&nbsp;<br/>
                                            7.&nbsp;&nbsp; &nbsp;شماره مجازی کلاب هوس&nbsp;<br/>
                                            8.&nbsp;&nbsp; &nbsp;شماره مجازی وایبر&nbsp;<br/>
                                            9.&nbsp;&nbsp; &nbsp;شماره مجازی گوگل&nbsp;<br/>
                                            10.&nbsp;&nbsp; &nbsp;شماره مجازی اینستاگرام&nbsp;<br/>
                                            11.&nbsp;&nbsp; &nbsp;شماره مجازی فیسبوک&nbsp;<br/>
                                            12.&nbsp;&nbsp; &nbsp;شماره مجازی لاین&nbsp;<br/>
                                            13.&nbsp;&nbsp; &nbsp;شماره مجازی یاهو</p>

                                        <h2>خرید گوگل ویس&nbsp;</h2>

                                        <p><br/>
                                            یکی از بهترین و مهمترین خدمات گوگل، گوگل ویس می&zwnj;باشد. متاسفانه این خدمت
                                            برای ایرانیان غیر قابل استفاده می&zwnj;باشد و شما برای استفاده از تمامی
                                            خدمات آن نیاز به شماره مجازی دائمی آمریکا خواهید داشت. شما با گوگل ویس می&zwnj;توانید
                                            مانند یک شهروند آمریکایی به خدمات بسیار زیادی دسترسی داشته باشید. &laquo;
                                            البته بر اساس بررسی&zwnj;های صورت گرفته توسط تیم متخصصین شاپ مجازی، استفاده
                                            از شماره گوگل ویس برای حساب&zwnj;های بانکی مانند پی پال غیر قابل قبول می&zwnj;باشد.
                                            &raquo; گوگل ویس یک سرویس ارتباطی برای تماس&zwnj;های صوتی و یا پیامک&zwnj;های
                                            متنی شما می&zwnj;باشد.&nbsp;</p>

                                        <p><br/>
                                            خدمات و کیفیت ارتباط با گوگل ویس بسیار بیشتر از تماس تلفنی واتساپ و یا گوگل
                                            میت می&zwnj;باشد. از دیگر مزایای گوگل ویس می&zwnj;توان به سرعت بسیار بالای
                                            ارتباط شما و تاخیر صفر ثانیه اشاره کرد. اگر بخواهیم به صورت اجمالی به مزایای
                                            گوگل ویس اشاره کنیم، بهتر است که موارد زیر را به شما معرفی کنیم.&nbsp;</p>

                                        <p><br/>
                                            1.&nbsp;&nbsp; &nbsp;ادغام شماره گوگل ویس با تمامی تماس&zwnj;های دریافتی شما
                                            &laquo; دایورت کردن خطوط مختلف روی آن &raquo;&nbsp;<br/>
                                            2.&nbsp;&nbsp; &nbsp;سازماندهی تماس&zwnj;های دریافتی و مشخص بودن تماس گیرنده&nbsp;<br/>
                                            3.&nbsp;&nbsp; &nbsp;قابلیت فیلتر کردن تماس&zwnj;ها و پیامک&zwnj;های
                                            تبلیغاتی&nbsp;<br/>
                                            4.&nbsp;&nbsp; &nbsp;قابلیت بک آپ گیری به صورت متمرکز &laquo; دسترسی ساده به
                                            پشتیبانی آن &raquo;&nbsp;<br/>
                                            5.&nbsp;&nbsp; &nbsp;تبدیل ایمیل و یا پیام&zwnj;های صوتی به متن برای دسترسی
                                            راحت تر&nbsp;<br/>
                                            6.&nbsp;&nbsp; &nbsp;قابلیت ضبط مکالمات به صورت قانونی و با کیفیت بالا&nbsp;<br/>
                                            7.&nbsp;&nbsp; &nbsp;قابلیت سفارشی سازی و فعال سازی منشی تلفنی&nbsp;<br/>
                                            8.&nbsp;&nbsp; &nbsp;قابل استفاده برای تمامی شبکه&zwnj;های
                                            اجتماعی&nbsp;<br/>
                                            9.&nbsp;&nbsp; &nbsp;و ...&nbsp;</p>

                                        <p><br/>
                                            در صورتی که شما تمایل به شناخت بیشتر این خدمت دارید می&zwnj;توانید از صفحه
                                            اختصاصی آن دیدن کنید و یا با مشاورین ما در مورد دیگر مزایا و رابط کاربری آن
                                            مشاوره رایگان داشته باشید. امروزه خرید گوگل ویس به دلیل تحریم&zwnj;های مختلف
                                            ایران بیش از هر زمان دیگری لازم و ضروری می&zwnj;باشد.&nbsp;</p>

                                        <h2>تیم تولید محتوای تخصصی برای خرید محتوا بلاگ، محصول، رپورتاژ، تخصصی و ...&nbsp;</h2>

                                        <p><br/>
                                            یکی از مهمترین خدماتی که شاپ مجازی برای سایت&zwnj;ها فراهم نموده است، تولید
                                            محتوا با کیفیت می&zwnj;باشد. امروزه تولید محتوای تخصصی بسیار سخت است و
                                            دستمزد تولید کنندگان محتوا نیز بسیار افزایش پیدا کرده است. اما شما با سایت
                                            شاپ مجازی می&zwnj;توانید به خدمات بسیار عالی و با قیمت مناسب دست پیدا کنید.
                                            دقت کنید که در شاپ مجازی ما تایپیست نداریم و هر محتوایی که برای شما تولید می&zwnj;شود،
                                            متناسب با الگوریتم&zwnj;های گوگل و سئو داخلی می&zwnj;باشد. از طرفی محتوای
                                            یونیک هم برای مخاطبین شما ارزشمند است و هم برای گوگل می&zwnj;تواند جذابیت
                                            بسیار بالایی داشته باشد.&nbsp;</p>

                                        <p><br/>
                                            تولید محتوا در شاپ مجازی به صورت بسیار حرفه&zwnj;ای و در چندین سطح مختلف
                                            انجام می&zwnj;شود. شما با هر میزان هزینه&zwnj;ای می&zwnj;توانید محتوای خود
                                            را در بخش تولید محتوای شاپ مجازی ثبت کنید. ساده ترین حالت تولید محتوا معمولا
                                            محتوای بازنویسی می&zwnj;باشد که می&zwnj;تواند برای صفحات کنونیکال شما مورد
                                            استفاده قرار بگیرد. و یا اگر شما در ابتدای مسیر می&zwnj;باشید و بودجه کافی
                                            برای آن ندارید می&zwnj;توانید از این سبک محتوا برای شروع استفاده کنید. اما
                                            زمانی که سایت شما به لحاظ سئو رشد پیدا کرد و یک جایگاه حداقلی در صفحه اول و
                                            دوم به دست آورد، باید از محتوای یونیک با دو تیک سبز استفاده کنید. تیک سبز
                                            خوانایی و تیک سبز سئو به ارزش محتوای شما کمک خواهد کرد.&nbsp;</p>

                                        <p><br/>
                                            سطح سوم تولید محتوا در شاپ مجازی به صورت به تخصصی و متناسب با حوزه کاری شما
                                            می&zwnj;باشد. در این سطح معمولا برای تولید محتوا از منابع رفرنس انگلیسی
                                            استفاده می&zwnj;شود. البته توجه داشته باشید که محتوای شما با توجه به
                                            الگوریتم&zwnj;های مختلف گوگل از جمله الگوریتم MUM و الگوریتم E.A.T &nbsp;نوشته
                                            خواهد شد. از طرفی عکس&zwnj;هایی که در آن استفاده می&zwnj;شود و لینک سازی
                                            داخلی آن نیز با توجه به اعتبار صفحات شما انجام خواهد شد. البته شما می&zwnj;توانید
                                            از بخش آپدیت محتوای شاپ مجازی نیز استفاده کنید که این بخش نیازمند کوئری های
                                            فعال سرچ کنسول شما می&zwnj;باشد.<br/>
                                            &nbsp;</p>

                                        <p><br/>
                                            &nbsp;</p>

                                    </div>
                                    <div style="display: block;width: 100%;height: 25px;background-image: linear-gradient(to bottom, rgb(30 28 28 / 0), rgb(241 243 244) 100%);margin-top: -23px;">

                                    </div>
                                    <div class="show-more-btn">
                                        <button class="btn btn-primary btn-shadow more-btn">مشاهده بیشتر</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table>
                            <tbody>
                            <tr>
                                <th>نام سایت</th>
                                <td>شاپ مجازی</td>
                            </tr>
                            <tr>
                                <th>نوع خدمات</th>
                                <td>انواع خدمات مجازی</td>
                            </tr>
                            <tr>
                                <th>نماد اعتماد</th>
                                <td>دارای نماد اعتماد</td>
                            </tr>
                            <tr>
                                <th>قدمت</th>
                                <td>3 سال</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
        @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
            <section class="bg-secondary py-5">
                <div class="container py-md-4 pt-3 pb-0 py-sm-3">
                    <h2 class="text-center mb-4 mb-md-5">برخی از نظرات کاربران</h2>
                    <div class="swiper myComments tabs">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <blockquote class="mb-2">
                                    <div>
                                        <div class="ps-3 mb-3">
                                            <div class="fs-sm mb-n1 detail-area">سامان واحدی<span
                                                        class="fs-ms text-muted opacity-75 detail-date">1399/11/30</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body fs-md text-muted border-0 shadow-sm rtl text-justify min-height-180">
                                        <div class="mb-2">
                                        </div>
                                        طراحی سایت بسیار راحتی و کاربر پسند و کار کردن باهاش و خرید بسیار راحت هستش.
                                    </div>
                                </blockquote>
                            </div>
                            <div class="swiper-slide">
                                <blockquote class="mb-2">
                                    <div>
                                        <div class="ps-3 mb-3">
                                            <div class="fs-sm mb-n1 detail-area">رضا صبوری <span
                                                        class="fs-ms text-muted opacity-75 detail-date">1400/7/23</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body fs-md text-muted border-0 shadow-sm rtl text-justify min-height-180">
                                        <div class="mb-2">
                                        </div>
                                        من گوگل ویس خریدم ازتون، بسیار راضی هستم، همونطور که گفته بودید برای تمامی
                                        اپلیکشین
                                        ها
                                        کار میکنه و کد دریافت میکنه. دمتون گرم
                                    </div>
                                </blockquote>
                            </div>
                            <div class="swiper-slide">
                                <blockquote class="mb-2">
                                    <div>
                                        <div class="ps-3 mb-3">
                                            <div class="fs-sm mb-n1 detail-area">محمد صحرایی <span
                                                        class="fs-ms text-muted opacity-75 detail-date">1401/5/30</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body fs-md text-muted border-0 shadow-sm rtl text-justify min-height-180">
                                        <div class="mb-2"></div>
                                        دستتون درد نکنه، قیمت سرویس ها نسبت به سایت های رقیب بسیار پایین و کیفت خدمات و
                                        پاسخگویی
                                        بالاست. ممنونم از تیم خوبتون
                                    </div>
                                </blockquote>
                            </div>
                            <div class="swiper-slide">
                                <blockquote class="mb-2">
                                    <div>
                                        <div class="ps-3 mb-3">
                                            <div class="fs-sm mb-n1 detail-area">سارا رضایی<span
                                                        class="fs-ms text-muted opacity-75 detail-date">1401/8/5</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body fs-md text-muted border-0 shadow-sm rtl text-justify min-height-180">
                                        <div class="mb-2">
                                        </div>
                                        خدمات و سرویس های شاپ مجازی در حد بسیار عالی است و دارای تیم فنی و پشتیبانی قوی
                                        می
                                        باشد
                                        که از
                                        مدیریت و کادر مجرب و تکنسین های آن شرکت محترم نهایت تشکر را داشته و موفقیت های
                                        روزافزون
                                        را برای
                                        آن آرزومندم باتشکر
                                    </div>
                                </blockquote>
                            </div>
                            <div class="swiper-slide">
                                <blockquote class="mb-2">
                                    <div>
                                        <div class="ps-3 mb-3">
                                            <div class="fs-sm mb-n1 detail-area">زینب رهنورد<span
                                                        class="fs-ms text-muted opacity-75 detail-date">1401/9/20</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card card-body fs-md text-muted border-0 shadow-sm rtl text-justify min-height-180">
                                        <div class="mb-2">
                                        </div>
                                        من از شما شماره گوگل ویس خریداری کردم، قبلش هم از سایت های دیگه خرید کرده بودم
                                        ولی
                                        اصلا
                                        پشتیبانی نداشتند و چندبار هم به مشکل خوردم، ولی سایت شاپ مجازی، هم قیمت خوبی
                                        داره و
                                        هم
                                        پشتیبانی خوب، حتما پیشنهاد میکنم و به دوستانم شما رو معرفی میکنم
                                    </div>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="pb-3"></div>
    </section>
    @include('site-v2.components.related-blog',['Blogs' => $Blogs])

@endsection

@section('header')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=13')}}">
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/home.css?v=14')}}">
    <style>
        .min-height-180 {
            min-height: 205px;
        }
    </style>
@endsection

@section('footer')
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        var btn = 0;
        $('.more-btn').click(function () {
            if (btn == 0) {
                btn = 1;
                $(this).empty().text('مشاهده کمتر')
            } else {
                btn = 0;
                $(this).empty().text('مشاهده بیشتر');
            }
            $('#content').toggleClass('height-auto');
        });
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: "auto",
            spaceBetween: 5,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
        var swiper = new Swiper(".myComments", {
            slidesPerView: "auto",
            spaceBetween: 5,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                360: {
                    slidesPerView: 1,
                },
                480: {
                    slidesPerView: 1,
                },
                720: {
                    slidesPerView: 2,
                }
                , 991: {
                    slidesPerView: 2,
                }, 1140: {
                    slidesPerView: 3,
                },
                1300: {
                    slidesPerView: 3,
                },
                1500: {
                    slidesPerView: 4,
                }
            }
        });
    </script>
@endsection

@section('seo')
    @php
        $seo['meta_title'] = 'شاپ مجازی - بزرگترین ارائه دهنده خدمات مجازی در کشور ';
        $seo['meta_desc'] = 'شاپ مجازی ارائه دهنده کاملترین خدمات مجازی برای شبکه های اجتماعی، خرید انواع آی پی ثابت، شماره مجازی، اپل آی دی و.. تضمین کمترین قیمت و بیشترین کیفیت + پشتیبانی ';
        $seo['image'] = secure_url('upload/logo/logo-v5.jpg');
    @endphp
    @include('site-v2.layout.seo')
@endsection
