<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <title>پوریا و صبا </title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{secure_url('/')}}/upload/logo/apple-fav.jpg">
    <link rel="icon" type="image/png" sizes="32x32" href="{{secure_url('/')}}/upload/logo/32x32">
    <link rel="icon" type="image/png" sizes="16x16" href="{{secure_url('/')}}/upload/logo/16x16.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/tiny-slider/dist/tiny-slider.css"/>
    <link rel="stylesheet" media="screen"
          href="{{secure_url('/site-v2')}}/vendor/lightgallery.js/dist/css/lightgallery.min.css"/>
    <link rel="stylesheet" href="{{secure_url('site-v2/css/theme.min.css?v25')}}">
    <link rel="stylesheet" href="{{secure_url('fontawesome/web/css/all.css?v=23')}}">
    <link rel="stylesheet" href="{{secure_url('admin/vendor/toastr/css/toastr.min.css?v=3')}}">
</head>
<body class="handheld-toolbar-enabled">
<main>
    <section class="ps-lg-4 pe-lg-3 pt-4">
        <section class="pb-4 pt-2 pt-md-3 pb-sm-3 bg-secondary">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 bg-position-center bg-size-cover">
                        <img class="m-s-8" style="display: block;margin: auto" width="95%"
                             src="{{url('/upload/IMG_0325_copy.png')}}"
                             alt="خرید شماره مجازی - شاپ مجازی">
                    </div>
                    <div class="col-md-6 bg-position-center bg-size-cover mt-3">
                        @if(isset($_GET['location']))
                            <div class="row">
                                <div class="col-lg-6 col-sm-12 mb-grid-gutter">
                                    <div class="card border-0 shadow">
                                        <div class="card-body">
                                            <h6>ایران، تهران</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex pb-3 border-bottom"><i class="ci-location fs-lg mt-2 mb-0 text-primary"></i>
                                                    <div class="ps-3"><span class="fs-ms text-muted">پیدا کردن ما</span><a class="d-block text-heading fs-sm" href="javascript:;">اقدسیه (موحد دانش)، بالاتر از سفارت چین، کوچه یاسمن، باشگاه تشریفات صاحبقرانیه </a></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-12 mb-grid-gutter">
                                    <a style="width: 100%" target="_blank" class="btn btn-primary btn-shadow"
                                       href="https://maps.app.goo.gl/7E7Wa8CtxRipqh1P8">لوکشین (گوگل مپ)</a>
                                    <a style="width: 100%" target="_blank" class="btn mt-2 btn-primary btn-shadow"
                                       href="https://nshn.ir/d57bvfdIGxROzH">لوکشین (نشان)</a>
                                    <a style="width: 100%" target="_blank" class="btn mt-2 btn-primary btn-shadow"
                                       href="{{url('/')}}/upload/screenshot_2024-04-26_at_2.09.19_pm.png">مشاهده کروکی</a>
                                </div>
                            </div>

                        @endif
                        @if(isset($_GET['form']))
                            <div class="card border-0 shadow mt-2">
                                <div class="card-header">
                                    <h2 class="h4 mt-2">به امید حضور گرمتان.</h2>
                                </div>
                                <div class="card-body">
                                    <form class="needs-validation mb-3" method="post"
                                          action="https://shopmajazi.com/wedding/weddingForm" id="ContactUs"
                                          novalidate="">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <label class="form-label" for="cf-name">نام شما : <span
                                                            class="text-danger">*</span></label>
                                                <input class="form-control" name="name" type="text" id="cf-name"
                                                       required="">
                                                <div class="invalid-feedback">لطفا نام را وارد کنید</div>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="form-label" for="cf-phone">تلفن:<span
                                                            class="text-danger">*</span></label>
                                                <input class="form-control" name="mobile" type="tel" id="cf-phone"
                                                       placeholder="0912123456" required="">
                                                <div class="invalid-feedback">شماره تلفن را وارد کنید</div>
                                            </div>
                                            <div class="col-sm-12">
                                                <label class="form-label" for="cf-phone">وضعیت:<span
                                                            class="text-danger">*</span></label>
                                                <br>
                                                <input type="radio" checked name="status" value="1" id="yes">
                                                <label for="yes">حتما میام</label>
                                                <input type="radio" class="mr-5" style="margin-right: 20px"
                                                       name="status" id="no" value="0">
                                                <label for="no"> متاسفم. نمیتونم بیام :(</label>
                                            </div>
                                            @csrf
                                            <div class="col-12">
                                                <button id="ContactUsBtn" data-text="ثبت فرم" data-form="ContactUs"
                                                        class="submit-btn btn btn-primary mt-4" type="submit">ثبت فرم
                                                </button>

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </section>
</main>

<!-- Vendor scrits: js libraries and plugins-->
<script src="{{secure_url('site/assets/js/jquery.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/simplebar/dist/simplebar.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/tiny-slider/dist/min/tiny-slider.js?v=2')}}"></script>
<script src="{{secure_url('site-v2/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/lightgallery.js/dist/js/lightgallery.min.js')}}"></script>
<script src="{{secure_url('admin/vendor/toastr/js/toastr.min.js')}}"></script>
<!-- Main theme script-->
<script src="{{secure_url('site-v2/js/theme.min.js')}}"></script>
<script src="{{secure_url('js/jquery.form.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('plugins/timer/jquery.simple.timer.js?v=2')}}"></script>
<script>
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
                alert('فرم با موفقیت ثبت شد')
                $('#' + BtnId).html(text);
                $('#' + BtnId).attr('disabled', false);

            },
            error: function (data) {
                console.log('error')
               alert(data.responseJSON.error)
                $('#' + BtnId).html(text);
                $('#' + BtnId).attr('disabled', false);

            }
        });

        return false;
    });
</script>
</body>
</html>



