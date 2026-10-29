<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <title>خرید ای پی ثابت</title>
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
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=14')}}">
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/static-ip.css?v=15')}}">
    <style>
        .ml-3 {
            margin-right: 10px;
        }
    </style>
</head>
<body class="handheld-toolbar-enabled">
<main class="">
    <div class="container pb-5 mb-sm-4">
        <div class="pt-5">
            <div class="card py-3 mt-sm-3">
                <div class="card-body text-center">
                    @if($Status == 1)
                        <img src="{{secure_url('/upload/green-check.png')}}" alt="پرداخت با موقیت انجام شد." style="width: 100px;margin-bottom: 10px">
                        <h2 class="h4 pb-3 text-center">از اعتماد به ما سپاس گذاریم.</h2>
                        <p class="fs-sm mb-2">سفارش شما با موفقیت ثبت شد.</p>
                        <p class="fs-sm mb-2">شماره صورتحساب: <span class="fw-medium">{{$Data['orderId']}}</span></p>

                        <p class="fs-sm mb-2">نوع پرداخت: <span class="fw-medium">درگاه بانک</span></p>
                        <p class="fs-sm mb-2">کد رهگیری بانکی: <span class="fw-medium">{{$Data['refId']}}</span></p>
{{--                        <a class="btn btn-secondary mt-3 me-3" href="https://t.me/HegzaVpnBot">ربات تلگرام</a>--}}
                        <a class="btn btn-primary mt-3" href="{{$Data['Url']}}">
                            <i class="fa-light fa-bags-shopping me-2"></i>مشاهده جزئیات سفارش
                        </a>
                    @else
                        <img src="{{secure_url('/upload/red-check.png')}}" alt="خطا در تراکنش"
                             style="width: 100px;margin-bottom: 10px">
                        <h2 class="h4 pb-3 text-center">خطایی بوجود اومده!!</h2>
                        <p class="fs-sm mb-2">متن خطا: </p>
                        <p class="fs-sm">{{$Data['msg']}}</p>
                        <a class="btn btn-secondary mt-3 me-3" href="https://t.me/HegzaVpnBot">ربات تلگرام</a>
                        <a class="btn btn-primary mt-3" href="https://t.me/hegzaSupport"><i
                                    class="fa-duotone fa-messages opacity-60 me-2"></i>پشتیبانی تلگرام</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Back To Top Button-->
<a class="btn-scroll-top" href="#top" aria-label="بالا" data-scroll data-fixed-element>
    <i class="btn-scroll-top-icon fa-light fa-circle-up"></i><span class="btn-scroll-top-tooltip text-muted fs-sm me-2">بالا</span>
</a>
<!-- Vendor scrits: js libraries and plugins-->
<script src="{{secure_url('site/assets/js/jquery.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/simplebar/dist/simplebar.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/tiny-slider/dist/min/tiny-slider.js?v=2')}}"></script>
<script src="{{secure_url('site-v2/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js')}}"></script>
<script src="{{secure_url('site-v2/vendor/lightgallery.js/dist/js/lightgallery.min.js')}}"></script>
<script src="{{secure_url('admin/vendor/toastr/js/toastr.min.js')}}"></script>

<script src="{{secure_url('site-v2/js/theme.min.js')}}"></script>
<script src="{{secure_url('js/jquery.form.min.js')}}" type="text/javascript"></script>
<script src="{{secure_url('plugins/timer/jquery.simple.timer.js?v=2')}}"></script>

<script>
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

    $(document).on('click', '.get-country', function () {
        var country_id = $(this).data('country');
        $('.country-item').removeClass('selected-country');
        $(this).addClass('selected-country');
        $('.buy-service').removeClass('selected-country');
        $('.plans').removeClass('selected-country');
        $('.volume-area').removeClass('selected-country');
        $('#country_id').val(country_id);
        $('#plan_id').val('');
        $('#volume_id').val('');
        $.ajax({
            url: '{{secure_url('/api/hegza/product-detail')}}',
            dataType: 'json',
            method: "GET",
            data: {id: country_id},
            success: function (data) {
                $('#Volume-Area').slideUp();
                $('#plans-area').slideDown();
                $('#Service-Area').empty().append(data.data);
                $([document.documentElement, document.body]).animate({scrollTop: $("#Service-Area").offset().top - 150}, 2000);
            },
            error: function () {
            }
        });
    });
    $(document).on('click', '.buy-service', function () {
        var plan_id = $(this).data('id');
        $('#plan_id').val(plan_id);
        $('#volume_id').val('');
        $('.buy-service').removeClass('selected-country');
        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
        $(this).addClass('selected-country');
        $('.service-id-' + plan_id).addClass('selected-country');
        @else
        $('.service-id-' + plan_id).addClass('selected-country');
        @endif
        $('.volume-area').removeClass('selected-country');
        $('#Volume-Area').slideDown();
        $([document.documentElement, document.body]).animate({scrollTop: $("#Volume-Area").offset().top - 150}, 2000);
    });
    $(document).on('click', '.volume-area', function () {
        var volume_id = $(this).data('id');
        $('#volume_id').val(volume_id);
        $('.volume-area').removeClass('selected-country');
        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
        $(this).addClass('selected-country');
        @else
        $('.volume-id-' + volume_id).addClass('selected-country');
        @endif
        $.ajax({
            url: "{{secure_url('/api/hegza/submit-order')}}",
            dataType: "JSON",
            method: "POST",
            data: {
                country_id: $('#country_id').val(),
                plan_id: $('#plan_id').val(),
                volume_id: $('#volume_id').val(),
                _token: '{{csrf_token()}}'
            },
            success: function (data) {
                var info = data.data
                $('#ProductImage').attr('src', info.image);
                $('#ProductName').empty().text(info.serviceName);
                $('#PlanName').empty().text(info.PlanName);
                $('#VolumeName').empty().text(info.VolumeName);
                $('#ProductPrice').empty().text(info.price + ' تومان ');
                $('#btn-area').attr('href', info.btn)
                $('#second-btn').remove();
                $('#virtual-number').modal('toggle');
            },
            error: function (err) {
                console.log(err);
                var errors = err.responseJSON;
                if (errors.status == 1) {
                    err.responseJSON.error.forEach(function (value, key) {
                        error(value)
                    });
                    $('#signin-modal').modal('toggle');
                } else if (errors.status == 2) {
                    for (const [key, value] of Object.entries(err.responseJSON.error)) {
                        error(value)
                    }
                }
            }
        });
    });
</script>
</body>
</html>
