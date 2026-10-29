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
    <div class="row">
        <div class="col-8 offset-2">
            @php
                $Plan = \App\Models\HegzaProduct::find($Ip->product_id);
                $PlanData = json_decode($Plan->detail,true);
                $Country = \App\Models\HegzaCountry::find($Ip->category_id);
            @endphp
            <div class="card border-0 shadow-lg mt-3">
                <div class="card-header">
                    <h4>جزئیات سفارش:</h4>
                </div>
                <div class="card-body pb-2">
                    <div class="row">
                        <div class="col-lg-4 col-6 mb-2">
                            <div class="custom-nav completed">
                                <div class="d-flex align-items-center" style="position: relative">
                                    <div>
                                        <div class="media-tab-subtitle text-muted fs-sm mb-1">نام کشور:</div>
                                        <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Country->name}}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 mb-2">
                            <div class="custom-nav completed">
                                <div class="d-flex align-items-center" style="position: relative">
                                    <div>
                                        <div class="media-tab-subtitle text-muted fs-sm mb-1">پلن:</div>
                                        <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Plan->faName}}
                                            با {{$PlanData['volume']}} گیگ حجم </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 mb-2">
                            <div class="custom-nav completed">
                                <div class="d-flex align-items-center" style="position: relative">
                                    <div>
                                        <div class="media-tab-subtitle text-muted fs-sm mb-1">تاریخ اتمام اشتراک:</div>
                                        <h6 class="media-tab-title fs-md text-nowrap mb-0"> {{!is_null($Ip) ? $jdf->jdate('d F Y',strtotime($Ip->expire_date)): '--'}}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(!is_null($Ip))
                @php
                    $Data = json_decode($Ip->detail,true);
                    $Domain = \App\Models\Ip::find($Ip->parent_id);
                    $ServerIp = json_decode($Domain->detail,true);
                    $IpDetail = json_decode($Ip->detail,true);

                    $GetData['ip_address'] = $ServerIp['ip_api_domain'];
                    $GetData['ip_port'] = $ServerIp['ip_port'];
                    $GetData['Session'] = $ServerIp['session'];
                    $GetData['uid'] = $Ip->UID;
                    $GetData['name'] = $IpDetail['user_remark'];
                    $GetData['ip_api_domain'] = $ServerIp['ip_api_domain'];
                    $GetData['Data'] = $ServerIp;
                    $UsedVolume = getClientTraffics($GetData);
                    if ($UsedVolume['success'] == false){
                        $UsedVolume = 00;
                    }else{
                        $UsedVolume = $UsedVolume['hajm'];
                    }
                @endphp
                <div class="row">
                    <div class="col-12 col-lg-8">

                        <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
                            <div class="alert-icon"><i class="ci-announcement"></i></div>
                            <div>کاربر گرامی، برای تمدید و خرید حجم و مشاهده حجم مصرف شده پس از اتصال به آیپی حتما به
                                ربات پیام بدید، در غیر اینصورت هیچ گونه پشتیبانی انجام نخواهد شد.
                                <br>
                                برای پیام به ربات، حتما از لینکی که در اخیتارتان قرار گرفته استفاده کنید.
                                <br>
                                برای پیام به ربات، <a href="{{url("https://t.me/HegzaVpnBot?start=type=order_id=".Encode($Ip->id))}}"> اینجا را کلیک کنید.</a>
                            </div>
                        </div>
                        <div class="border-0 rounded-3 shadow-lg mt-3" style="position: sticky;top: 6rem">
                            <div class="card-header">
                                <h4>جزئیات آی پی:</h4>
                            </div>
                            <div class="card-body pb-2">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="mt-1">
                                            <div class="media-tab-subtitle text-muted fs-sm mb-1">کد آی پی:</div>
                                            {{--                                <textarea class="form-control" disabled id="" cols="30" rows="10">{{$Data['user_code']}}</textarea>--}}
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-12 mb-3">
                                        <button class="btn btn-info w-100"
                                                onclick="myFunction('{{$Data['user_code']}}')">کپی لینک
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <div class="mt-3">
                                            <div class="media-tab-subtitle text-muted fs-sm mb-1"
                                                 style="text-align: left;direction: ltr;"> QrCode:
                                            </div>
                                            <div class="product-thumb mt-3 mb-3">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?data={{urlencode($Data['user_code'])}}&size=200x20"
                                                     style="margin: auto;display: block" alt="Qr Code">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="border-0 shadow-lg rounded-3 mt-3">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h4 class="">اطلاعات مهم:</h4>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="row">
                                        <div class="col-lg-12 col-12">
                                            <div class="bg-secondary rounded-3 p-4 mb-4 text-center">
                                                کاربر گرامی، برای تمدید و خرید حجم و مشاهده حجم مصرف شده پس از اتصال به
                                                آیپی حتما به ربات پیام بدید، در غیر اینصورت هیچ گونه پشتیبانی انجام
                                                نخواهد شد.
                                                <br>
                                                برای پیام به ربات حتما از دکمه زیر استفاده کنید.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-12 mb-3">
                                        <a href="{{url("https://t.me/HegzaVpnBot?start=type=order_id=".Encode($Ip->id))}}" target="_blank"
                                           class="btn btn-info w-100">
                                            پیام به ربات
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-0 shadow-lg rounded-3 mt-3">
                            <div class="card mt-2">
                                <div class="card-header mt-2">
                                    <h4>آموزش اتصال به آی پی:</h4>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="row">
                                        <div class="col-lg-12 col-12 mb-3">
                                            <a href="{{secure_url('/help/ip-sabet/android.pdf')}}" target="_blank"
                                               class="btn btn-info w-100">
                                                آموزش اتصال برای اندروید
                                            </a>
                                        </div>
                                        <div class="col-lg-12 col-12 mb-3">
                                            <a href="{{secure_url('/help/ip-sabet/ios.pdf')}}" target="_blank"
                                               class="btn btn-info w-100">
                                                آموزش اتصال برای IOS
                                            </a>
                                        </div>
                                        <div class="col-lg-12 col-12 mb-3">
                                            <a href="{{secure_url('/help/ip-sabet/windows.pdf')}}" target="_blank"
                                               class="btn btn-info w-100">
                                                آموزش اتصال برای ویندوز
                                            </a>
                                        </div>
                                        <div class="col-lg-12 col-12 mb-3">
                                            <a href="{{secure_url('/help/ip-sabet/ios.pdf')}}" target="_blank"
                                               class="btn btn-info w-100">
                                                آموزش اتصال برای Mac
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function myFunction(id) {
                        /* Copy the text inside the text field */
                        navigator.clipboard.writeText(id);
                        /* Alert the copied text */
                        toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
                            timeOut: 3000,
                            closeButton: !0,
                            debug: !1,
                            newestOnTop: !0,
                            progressBar: !0,
                            positionClass: "toast-top-right",
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
                </script>
            @else
                @if($Order->status == 'pending')
                    <div class="alert alert-info d-flex fs-sm mb-4 mt-5" role="alert">
                        <div class="alert-icon"><i class="ci-announcement"></i></div>
                        <div> با تشکر از خرید شما، به زودی اطلاعات آی پی برای شما ارسال خواهد شد.</div>
                    </div>
                @elseif($Order->status == 'failed')
                    <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
                        <div class="alert-icon"><i class="ci-announcement"></i></div>
                        <div>این سفارش لغو شده است.</div>
                    </div>
                @endif
            @endif
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
