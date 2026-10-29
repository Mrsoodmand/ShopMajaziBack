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
        $seo['meta_title'] = $PageSeo['title'] ?? 'تماس با ما | شاپ مجازی';
        $seo['meta_desc'] = $PageSeo['description'] ?? 'جهت تماس با ما میتوانید از طریق راه ها ارتباطی اقدام نمایید';
        $seo['image'] = $PageSeo['image'] ?? secure_url('upload/logo/logo-v5.jpg');
    @endphp
    @include('site-v2.layout.seo')
@endsection
