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
    <section class="container ps-lg-4 pe-lg-3" id="CountryArea">
        @if(\Illuminate\Support\Facades\Session::has('error'))
            @foreach(\Illuminate\Support\Facades\Session::get('error') as $value)
                <div class="alert alert-danger">
                    {{$value}}
                </div>
            @endforeach
        @endif
        <section class="py-2 rounded-3 px-4 mt-5 shadow-lg mb-3">
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <h2 class="h3 mb-0 me-2 custom-width-10">کشور</h2>
                @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام کشور">
                    </div>
                @endif
            </div>
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="d-flex align-items-center ps-2 shadow-sm mb-2 m-country-area-2">
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام سرویس">
                    </div>
                </div>
            @endif
            <hr>
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach m-country-area-3">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    @foreach($Countries as $country)
                        @if(in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area">
                                <div class="bg-white d-block country-item shadow-sm @if($country->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter get-country"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <span class="service-name-2 ml-3">{{$country->name}}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($Countries as $country)
                        @if(!in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="not-available"></div>
                                <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <span class="service-name-2 ml-3">{{$country->name}}</span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @else
                    @foreach($Countries as $country)
                        @if(in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="bg-white d-block country-item shadow-sm @if($country->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter @if(in_array($country->id,$Access)) get-country @endif"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <span class="service-name-2 ml-3">{{$country->name}} </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @foreach($Countries as $country)
                        @if(!in_array($country->id,$Access))
                            <div class="col-md-3 col-sm-4 col-12 country-search box-area m-country-area-4">
                                <div class="not-available"></div>
                                <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter"
                                     id="country-{{$country->id}}" data-country="{{$country->id}}">
                                    <span class="service-name-2 ml-3">{{$country->name}} </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </section>
        <!-- End Countries -->
        <!-- Products -->
        <section class="py-2 rounded-3 px-4 mt-5 shadow-lg mb-3 m-i-1" id="plans-area">
            <!-- Heading-->
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <!-- Search-->
                <h2 class="h3 mb-0 me-2 custom-width-10">پلن ها</h2>
            </div>
            <hr>
            <!-- Grid-->
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach" id="Service-Area">
                @include('bot.products-json')
            </div>
        </section>

        <!-- End Products -->
        <!-- Products -->
        <section class="py-2 px-4 rounded-3 mt-5 mb-3 shadow-lg m-i-1" id="Volume-Area">
            <!-- Heading-->
            <div class="d-flex align-items-center ps-2 mb-2 m-country-area">
                <!-- Search-->
                <h2 class="h3 mb-0 me-2 custom-width-10">حجم اضافه</h2>
            </div>
            <hr>
            <!-- Grid-->
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    @for($i = 0;$i <= 5;$i++)
                        <div class="col-md-4 col-sm-4 col-12 service-search box-area m-country-area-6">
                            <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-volume volume-area"
                                 data-id="{{$i}}">
                                <div class="number-text">
                                    <span class="volume-name">@lang('order.Volume-Name.'.$i)</span>
                                    <div>
                                        @if($i != 0)
                                            @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                            <span class="price-area-2">  {{number_format($Price)}} تومان</span>
                                        @endif
                                    </div>
                                </div>
                                <button class="btn btn-icon me-2 btn-info service-buy-btn"
                                        data-service="{{$i}}" type="button" data-bs-toggle="tooltip"
                                        title="خرید"><i class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                                </button>
                            </div>
                        </div>
                    @endfor
                @else
                    @for($i = 0;$i <= 5;$i++)
                        <div class="col-md-4 col-sm-4 col-12 service-search box-area mb-4 m-country-area-6">
                            <div class="d-block d-sm-flex p-2 align-items-start text-sm-start buy-volume shadow-sm volume-area volume-id-{{$i}}"
                                 data-id="{{$i}}">
                                <div class="pt-2 ms-2">
                                    <label class="product-title fs-base mb-2">@lang('order.Volume-Name.'.$i)</label>
                                    @if($i == 0)

                                    @elseif($i != 6)
                                        @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                        <label class="btn btn-icon service-fav-btn fs-md">
                                            @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                            <span>{{number_format($Price)}} تومان </span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            <button class="btn btn-icon btn-info me-2 btn-sm w-100 mt-1 volume-area"
                                    data-id="{{$i}}" type="button" data-bs-toggle="tooltip" title="انتخاب"><i
                                        class="fa-regular fa-cart-shopping me-1"></i>انتخاب
                            </button>
                        </div>
                    @endfor
                @endif
            </div>
        </section>
        <div class="pb-3"></div>
    </section>
    <div class="modal fade" id="virtual-number" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="fa-regular fa-cart-shopping"></i>خرید آی پی ثابت</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{route('user.service-bank-payment')}}"
                      class="needs-validation tab-pane fade show active" autocomplete="off" novalidate>
                    <div class="modal-body tab-content py-4">
                        <div class="table-responsive fs-md mb-4">
                            <table class="table table-hover mb-0 text-center m-i-2">
                                <thead>
                                <tr>
                                    <th>کشور</th>
                                    <th>پلن</th>
                                    <th>حجم</th>
                                    <th>مبلغ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && $Session != null)
                                    @php

                                        $Plan = App\Models\Product::where('type', 'pu-ip-sabet')->where('id', $Session['plan_id'])->first();
                                        $Data = json_decode($Plan->detail,true);
                                        if($Data['dis'] == 0){
                                            $Plan_price = $Plan->price;
                                        }else{
                                             $Plan_price = $Data['dis'];
                                        }
                                        $Price = __('order.Volume-Gb.'.$Session['volume_id']) * 1500;
                                        $Price = $Plan_price + $Price;
                                    @endphp
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage"
                                                     src="{{secure_url($Countries->where('id',$Session['country_id'])->first()->image)}}"
                                                     width="39" alt="product-image">
                                                <div class="ps-2">
                                                    <span id="ProductName"
                                                          class="fw-medium text-heading me-1">{{$Countries->where('id',$Session['country_id'])->first()->name}}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="PlanName">{{$Plan->faName}}</td>
                                        <td class="py-3 align-middle"
                                            id="VolumeName">@lang('order.Volume-Name.'.$Session['volume_id'])</td>
                                        <td class="py-3 align-middle" id="ProductPrice">{{number_format($Price)}}
                                            تومان
                                        </td>
                                        <input type="hidden" value="{{$Session['country_id']}}" id="country_id"
                                               name="country_id">
                                        <input type="hidden" value="{{$Plan->id}}" id="plan_id" name="plan_id">
                                        <input type="hidden" value="{{$Session['volume_id']}}" id="volume_id"
                                               name="volume_id">
                                        <input type="hidden" value="5" name="type">
                                        <input type="hidden" value="0" name="product_id">
                                        @csrf
                                    </tr>
                                @else
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage"
                                                     src=""
                                                     width="39" alt="product-image">
                                                <div class="ps-2">
                                                    <span id="ProductName"
                                                          class="fw-medium text-heading me-1"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="PlanName"></td>
                                        <td class="py-3 align-middle" id="VolumeName"></td>
                                        <td class="py-3 align-middle" id="ProductPrice"></td>
                                        <input type="hidden" value="5" name="type">
                                        <input type="hidden" value="0" name="product_id">
                                        <input type="hidden" value="" id="country_id" name="country_id">
                                        <input type="hidden" value="" id="plan_id" name="plan_id">
                                        <input type="hidden" value="" id="volume_id" name="volume_id">
                                        @csrf
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <a href="#" id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3 m-i-3">پرداخت با درگاه
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .reset-pass-btn {
            color: #fe696a;
            cursor: pointer;
        }
    </style>

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
                ref: {{$_GET['ref']}},
                _token: '{{csrf_token()}}'
            },
            success: function (data) {
                var info = data.data
                $('#ProductImage').attr('src', info.image);
                $('#ProductName').empty().text(info.serviceName);
                $('#PlanName').empty().text(info.PlanName);
                $('#VolumeName').empty().text(info.VolumeName);
                $('#ProductPrice').empty().text(info.price + ' تومان ');
                $('#btn-area').attr('href',info.btn)
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
