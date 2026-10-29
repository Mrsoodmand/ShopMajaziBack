@php
    $Detail = json_decode($Product->detail,true);
    $Files = json_decode($Product->filds,true);
    $Notes = json_decode($Product->notes,true);
    $perUser = floatval($Product->price / 1000);
@endphp
@extends('site-v2.layout.master',['MetaTitle' => $Detail['meta_title'] .' | شاپ مجازی'])
@section('seo')
    @php
        $seo['meta_title'] =$Detail['meta_title'];
        $seo['meta_desc'] = $Detail['meta_desc'];
        $seo['image'] = $Detail['image_gallery'];
        if (array_key_exists('seo',$Detail)){
        $seo['seo'] = $Detail['seo'];
        }
    @endphp
    @include('site-v2.layout.seo')
@endsection

@section('content')
    <div class="page-title-overlap bg-header pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item text-white"><a class="text-nowrap" target="_blank"
                                                                  href="{{secure_url('/')}}"><i class="ci-home"></i>شاپ
                                مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap text-white"><a target="_blank"
                                                                              href="{{secure_url('/virtual-service')}}">خدمات
                                مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap text-white active"
                            aria-current="page">{{$Detail['name']}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0">{{$Detail['name']}}</h1>
            </div>
        </div>
    </div>
    <section class="container mb-3 pb-3">
        <div class="bg-light shadow-lg rounded-3 overflow-hidden">
            <div class="row">
                <!-- Content-->
                <section class="col-lg-6 pt-2 pt-lg-4 pb-4 mb-lg-3">
                    <div class="pt-3 px-4 pe-lg-0 ps-xl-5">
                        <!-- گالری محصولات-->
                        <div class="gallery" lg-uid="lg0">
                            <div class="gallery-item rounded-3 mb-grid-gutter"><img
                                        src="{{array_key_exists('image_gallery',$Detail)  && !is_null($Detail['image_gallery']) ? secure_url($Detail['image_gallery']) : '' }}"
                                        alt="Gallery preview"><span
                                        class="gallery-item-caption">{{$Detail['name']}}</span></div>
                        </div>
                        @if(\Jenssegers\Agent\Facades\Agent::isPhone() == false)
                            <div style="text-align: justify;">
                                {!! $Detail['short_detail'] !!}
                            </div>
                        @endif
                    </div>
                </section>
                <!-- Sidebar-->
                <aside class="col-lg-6 ps-xl-5">
                    <hr class="d-lg-none">
                    <div class="bg-white h-100 p-4 ms-auto border-start">
                        <div class="px-lg-2 product-area">
                            <form action="{{route('submit-factor',3)}}" id="َAddToCartForm" method="post">
                                <div class="rounded p-3 mt-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="ps-2 w-100">
                                            <span class="fs-ms text-muted">نوع پلن*</span>
                                            <div class="d-flex justify-content-center">
                                                <div class="row mt-2 w-100">
                                                    @foreach($Plans as $key => $Plan)
                                                        @php

                                                            $price = $Plan['price'];
                                                            $price = CovertVirtualNumberPrice(number_format($price * $Currency));
                                                            $Discount = $Plan['discount'];
                                                            $DisValue = number_format($Plan['discount'] * $Currency);
                                                             if($key == 0){
                                                                 $showPrice = $price;
                                                                 $Name = $Plan['name'];
                                                             }
                                                        @endphp
                                                        <div class="@if(count($Plans) >= 4) col-lg-6 @endif col-12">
                                                            <div class="form-check form-option form-check-justified mb-2">
                                                                <input class="form-check-input"
                                                                       value="{{Encode($Plan['id'])}}" type="radio"
                                                                       name="plane_id"
                                                                       id="plan-{{$Plan['id']}}" {{$key == 0 ? 'checked="checked"': '' }}>
                                                                <label class="form-option-label"
                                                                       style="height: auto!important;"
                                                                       onclick="ChangeName('{{$price}}','{{$Plan['name']}}','{{!is_null($Plan['discount']) ? '1' :'0'}}','{{!is_null($Plan['discount']) ? $DisValue :'0'}}')"
                                                                       for="plan-{{$Plan['id']}}">{{$Plan['name']}}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @foreach($Files as $item)
                                    <div class="bg-secondary rounded p-3 mt-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="ps-2 w-100">
                                                <span class="fs-ms text-muted mb-2">{{$item['title']}}*</span>
                                                <input type="hidden" required
                                                       name="filed_title[{{$item['filed_name']}}]"
                                                       value="{{$item['title']}}">
                                                <input type="hidden" required name="filed_name[{{$item['filed_name']}}]"
                                                       value="{{$item['filed_name']}}">
                                                @if($item['type'] == 'text')
                                                    <input name="filed_value[{{$item['filed_name']}}]" type="text"
                                                           class="form-control mt-2">
                                                @elseif($item['type'] == 'select_box')
                                                    @php  $values = explode(',', $item['select_value']); @endphp
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <select name="filed_value[{{$item['filed_name']}}]"
                                                                    class="form-select mt-2"
                                                                    id="{{$item['filed_name']}}">
                                                                @foreach($values as $value)
                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <input type="hidden" name="product_id" value="{{$Product->id}}">
                                <input type="hidden" name="type" value="3">
                                <div class="mb-3 mt-3 text-center">
                                    <span class="text-heading fw-medium me-1">پلن انتخاب شده:</span>
                                    <span class="" style="color: #ef476f"
                                          id="ProductName"> {{isset($Name) ? $Name : ''}} </span>
                                    <br>
                                    <span id="SelectedFirstOption">مبلغ:</span>
                                    @if(isset($Discount))
                                        @if(!is_null($Discount))
                                            <del class="text-muted fs-lg" id="ProductDiscountPrice">{{$price}}</del>
                                            <span class="" style="color: #ef476f" id="ProductPrice">{{$DisValue}}</span>
                                            <span> تومان</span>
                                        @else
                                            <del class="text-muted fs-lg" id="ProductDiscountPrice"></del>
                                            <span class="" style="color: #ef476f"
                                                  id="ProductPrice">{{$showPrice}}</span>
                                            <span> تومان</span>
                                        @endif
                                    @else
                                        <del class="text-muted fs-lg" id="ProductDiscountPrice"></del>
                                        <span class="" style="color: #ef476f" id="ProductPrice">{{$showPrice}}</span>
                                        <span> تومان</span>
                                    @endif
                                    <hr>
                                </div>
                                @csrf
                                <button data-form="َAddToCartForm" id="AddToCartBtn" data-text="افزودن به سبد خرید"
                                        class="submit-form btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
                                        type="submit"><i
                                            class="ci-cart fs-lg me-2"></i>خرید
                                </button>
                            </form>
                            <table>
                                <tbody>
                                <tr>
                                    <th> 🛒نام سرویس:</th>
                                    <td>{{$Detail['name']}}</td>
                                </tr>
                                @foreach($Notes as $key => $note)
                                    <tr>
                                        <th>{{$key}}</th>
                                        <td>{{$note}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex flex-wrap justify-content-between align-items-center pt-3">
                                <div class="py-2">
                                    <label class="form-label d-inline-block align-middle my-2 me-3">اشتراک:</label>
                                    <a class="btn-share btn-twitter me-2 my-2"
                                       href="http://www.facebook.com/share.php?u={{URL::current()}}"><i
                                                class="ci-twitter"></i>توییتر</a>
                                    <a class="btn-share btn-facebook my-2"
                                       href="http://www.facebook.com/share.php?u={{URL::current()}}"><i
                                                class="ci-facebook"></i>فیسبوک</a>
                                    <a class="btn-share btn-twitter my-2"
                                       href="https://t.me/share/url?url={{URL::current()}}"><i class="ci-telegram"></i>تلگرام</a>
                                    <label class="btn-share btn-instagram my-2" id="copy"
                                           onclick="copyToClipboard('#copy')" data-copy="{{URL::current()}}"><i
                                                class="icon-copy"></i>کپی لینک</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    @include('site-v2.components.why-shopmajazi')
    @if(array_key_exists('long_detail',$Detail) && !is_null($Detail['long_detail']))
        <!-- Start Content -->
        <section class="container ps-lg-4 pe-lg-3 pt-4 ">
            <section class="mb-3 pb-3">
                <div class="shadow-lg rounded-3 overflow-hidden">
                    <div class="row content-area">
                        <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                                <div class="post-container">
                                    <div class="post-content" id="content">
                                        {!! $Detail['long_detail'] !!}
                                    </div>
                                    <div class="m-a-5">

                                    </div>
                                    <div class="show-more-btn">
                                        <button class="btn btn-primary btn-shadow more-btn">مشاهده بیشتر</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    @endif
{{--    <section class="container mb-4 mb-lg-5">--}}
{{--        @foreach($Related as $key => $related)--}}
{{--            @php--}}
{{--                $Details = json_decode($related->detail,true);--}}
{{--                $Notes = json_decode($related->notes,true);--}}
{{--            @endphp--}}
{{--            @if ($key % 2 == 0)--}}
{{--                <div class="row py-sm-2">--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="d-flex flex-column h-80 bg-size-cover bg-position-center rounded-3 py-4">--}}
{{--                            <img src="{{array_key_exists('image_gallery',$Details)  && !is_null($Details['image_gallery']) ? secure_url($Details['image_gallery']) : '' }}"--}}
{{--                                 alt=" خرید {{$Details['name']}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-9">--}}
{{--                        <div class="d-sm-flex justify-content-between align-items-center overflow-hidden rounded-3">--}}
{{--                            <div class="py-4 my-2 my-md-0 py-md-5 px-4 ms-md-3 text-center text-sm-start">--}}
{{--                                <h2 class="fs-lg fw-light mb-2"><a--}}
{{--                                            href="#"--}}
{{--                                            target="_blank"> خرید {{$Details['name']}}</a></h2>--}}
{{--                                <p class="mb-4">لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده--}}
{{--                                    از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم--}}
{{--                                    است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای--}}
{{--                                    کاربردی می باشد،</p>--}}
{{--                                <div class="row">--}}
{{--                                    @foreach($Notes as $key => $note)--}}
{{--                                        <div class="col-6">--}}
{{--                                            <p class="mb-4">{{$key}}: {{$note}}</p>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
{{--                                </div>--}}
{{--                                <button onclick="location.href = '#'"--}}
{{--                                        class="btn btn-primary btn-shadow btn-sm">ثبت سفارش--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @else--}}
{{--                <div class="row py-sm-2 bg-secondary">--}}
{{--                    <div class="col-md-9">--}}
{{--                        <div class="d-sm-flex justify-content-between align-items-center overflow-hidden rounded-3">--}}
{{--                            <div class="py-4 my-2 my-md-0 py-md-5 px-4 ms-md-3 text-center text-sm-start">--}}
{{--                                <h2 class="fs-lg fw-light mb-2"><a--}}
{{--                                            href="#"--}}
{{--                                            target="_blank"> خرید {{$Details['name']}}</a></h2>--}}
{{--                                <p class="mb-4">لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده--}}
{{--                                    از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم--}}
{{--                                    است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای--}}
{{--                                    کاربردی می باشد،</p>--}}
{{--                                <div class="row">--}}
{{--                                    @foreach($Notes as $key => $note)--}}
{{--                                        <div class="col-6">--}}
{{--                                            <p class="mb-4">{{$key}}: {{$note}}</p>--}}
{{--                                        </div>--}}
{{--                                    @endforeach--}}
{{--                                </div>--}}
{{--                                <button onclick="location.href = '#'"--}}
{{--                                        class="btn btn-primary btn-shadow btn-sm">ثبت سفارش--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-3">--}}
{{--                        <div class="d-flex flex-column h-80 bg-size-cover bg-position-center rounded-3 py-4">--}}
{{--                            <img src="{{array_key_exists('image_gallery',$Details)  && !is_null($Details['image_gallery']) ? secure_url($Details['image_gallery']) : '' }}"--}}
{{--                                 alt=" خرید {{$Details['name']}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--        @endforeach--}}
{{--    </section>--}}
    @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        <div class="modal fade" id="virtual-number" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                            class="fa-regular fa-cart-shopping"></i>خرید اپل آیدی</label></li>
                        </ul>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="post" action="{{route('user.service-bank-payment')}}"
                          class="needs-validation tab-pane fade show active" autocomplete="off" novalidate>
                        <div class="modal-body tab-content py-4">
                            <div class="table-responsive fs-md mb-4">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th>نام سرویس</th>
                                        <th>مبلغ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="ps-2">
                                                    <span id="ProductNameTable" class="fw-medium text-heading me-1">asdasdas</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="ProductPriceTable">asdasd</td>

                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="product_id" value="" id="product_id">
                        <input type="hidden" name="type" value="" id="type">
                        @csrf
                        <div class="modal-footer justify-content-center">
                            <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                                    style="width: 47%" type="submit">پرداخت با درگاه
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('header')
    <style>
        /*.post-container {*/
        /*    margin: 20px 20px 0 0;*/
        /*}*/
        /*.post-thumb {*/
        /*    float: left;*/
        /*    width: 30%;*/
        /*}*/
        /*.post-thumb img {*/
        /*    display: block;*/
        /*}*/
        /*.post-title {*/
        /*    font-weight: bold;*/
        /*    font-size: 200%*/
        /*}*/
        #content {
            height: 500px;
            overflow: hidden;
            text-align: justify;
        }

        .post-content img {
            border-radius: 20px;
        }

        .h-80 {
            width: 80%;
            height: 80%;
        }

        .display-flex {
            display: flex !important;
            justify-content: center;
        }

        .display-flex .mr-3 {
            margin-right: 1rem;
        }

        @media screen and (min-width: 768px) {
            .mb-grid-gutter {
                width: 50%;
                display: block;
                margin: auto;
            }
        }

        .product-area table {
            font-size: .875rem !important;
            padding-right: 0;
            list-style: none;
            width: 100%;
        }

        .product-area table tbody tr {
            padding-bottom: 1rem !important;
            margin-bottom: 1rem !important;
            justify-content: space-between !important;
            border-bottom: 1px solid #e3e9ef !important;
            display: flex !important;
        }

        .product-area table tbody tr th {
            color: #373f50 !important;
            font-weight: 500 !important;
        }

        .product-area table tbody tr td {
            color: #7d879c !important;
        }
    </style>
@endsection

@section('footer')
    <script type="text/javascript">

        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        $(document).on('click', '.buy-with-wallet', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/2/' + id,
                dataType: 'JSON',
                method: 'GET',
                beforeSend: function () {
                    $('.buy-with-wallet').prop('disabled', true);
                    $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
                },
                success: function (data) {
                    success('سفارش شما با موفقیت پرداخت شد. درحال انتقال به صفحه جزئیات سفارش.')
                    setTimeout(function () {
                        location.href = data.url
                    }, 2000)
                },
                error: function (err) {
                    console.log(err)
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                }
            });
        });
        @endif

        $('.submit-form').click(function () {
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
                    $('#second-btn').remove();
                    $('#ProductNameTable').empty().text(data.name);
                    $('#ProductPriceTable').empty().text(data.price);
                    $('#product_id').val(data.product_id);
                    $('#type').val(2);
                    $('#btn-area').before(data.btn);
                    $('#virtual-number').modal('toggle');
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                },
                error: function (err) {
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
                    } else {
                        var Error = err.responseJSON.error;
                        Error.forEach(function (value) {
                            error(value);
                        });
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                }
            });
            return false;
        });

        function ChangeName(price, name, discount, dis_value) {
            $('#ProductName').empty().text(name);
            if (discount == 1) {
                $('#ProductDiscountPrice').empty().text(price);
                $('#ProductPrice').empty().text(dis_value);
            } else {
                $('#ProductDiscountPrice').empty().text(' ');
                $('#ProductPrice').empty().text(price);
            }
        }

        function copyToClipboard(element) {

            navigator.clipboard.writeText(element);
            toastr.success("عملیات کپی با موفقیت انجام شد.", "", {
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

    </script>
@endsection
