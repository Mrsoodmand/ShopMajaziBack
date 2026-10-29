@section('seo')
    @php
        $seo['meta_title'] = 'خرید شماره مجازی دائمی آمریکا (گوگل ویس) | بهترین قیمت + تحویل آنی';
        $seo['meta_desc'] = 'خرید شماره مجازی گوگل ویس آمریکا، مناسب برای ایجاد حساب در سایت های خارجی با قابلیت دریافت تماس و پیامک به صورت دائمی | Google Voice';
        $seo['image'] = url($Page->image);
    @endphp
    @include('site-v2.layout.seo')
@endsection
@extends('site-v2.layout.master',['MetaTitle' => $seo['meta_title']])
@section('content')
    @php $Price = json_decode($Page->detail,true);@endphp
    <div class="page-title-overlap pt-4 bg-header">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb text-white">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" target="_blank" href="{{url('/')}}"><i
                                        class="ci-home"></i>شاپ مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">{{$Page->h1}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0">{{$Page->h1}}</h1>
            </div>
        </div>
    </div>
    <section class="container mb-3 pb-3">
        <div class="bg-light shadow-lg rounded-3 overflow-hidden">
            <div class="row">
                <section class="col-lg-6 pt-2 pt-lg-4 pb-4 mb-lg-3">
                    <div class="pt-3 px-4 pe-lg-0 ps-xl-5">
                        <div class="gallery">
                            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                                <div class="d-table scale-up delay-4 mx-auto mx-lg-0 mb-3">
                                    <button class="btn btn-primary slow-scroll m-submit-order ml-5"
                                            style="margin-left: 10px" data-target="CountryArea">خرید شماره گوگل ویس
                                    </button>
                                    <button class="btn btn-info slow-scroll m-faq-button ml-5" data-target="FaqArea">
                                        سوالات متدوال
                                    </button>
                                </div>
                            @endif
                            <div class=" rounded-3 mb-grid-gutter">
                                <img class="header-image" src="{{secure_url($Page->image)}}" alt="Gallery preview"><span
                                        class="gallery-item-caption">{{$Page->name}}</span>
                            </div>
                        </div>
                        <div style="text-align: justify;">
                            {!! $Page->short_detail !!}
                        </div>
                    </div>
                </section>
                <aside class="col-lg-6 ps-xl-5">
                    <hr class="d-lg-none">
                    <div class="bg-white h-100 p-4 ms-auto border-start">
                        <div class="px-lg-2 product-area">
                            @if(count($GoogleVoices) != 0)
                                <div class="rounded p-3 mt-2 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="ps-2 w-100">
                                            <span class="fs-ms text-muted">انتخاب شماره*</span>
                                            <div class="row">
                                                @foreach($GoogleVoices as $key => $googlevoice)
                                                    <div class="col-lg-6 col-12">
                                                        <div class="form-check form-option form-check-justified mb-2">
                                                            <input class="form-check-input" value="{{$googlevoice->id}}"
                                                                   type="radio" name="num_id"
                                                                   id="plan-{{$googlevoice->id}}" {{$key == 0 ? 'checked="checked"': '' }}>
                                                            <label
                                                                    onclick="ChangeName('{{$googlevoice->number}}',{{$googlevoice->id}})"
                                                                    class="form-option-label ltr"
                                                                    for="plan-{{$googlevoice->id}}">{{$googlevoice->number}}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 mt-3 text-center" id="CountryArea">
                                    <span class="text-heading fw-medium me-1">شماره انتخاب شده:</span>
                                    <span style="color: #ef476f;direction: ltr;    display: inline-block;"
                                          id="ProductName"> {{isset($GoogleVoices) ? $GoogleVoices->first()->number : ''}} </span>
                                    <br>
                                    <span id="SelectedFirstOption">مبلغ:</span>
                                    <del class="text-muted fs-lg" id="ProductDiscountPrice"></del>
                                    <span class="" style="color: #ef476f"
                                          id="ProductPrice">{{number_format($Price['custom_price'])}}  </span>
                                    <span> تومان</span>
                                </div>
                                @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
                                    <button onclick="ShowBuyModal()"
                                            class="submit-btn btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
                                            type="submit"><i class="ci-cart fs-lg me-2"></i>خرید
                                    </button>
                                @else
                                    <button onclick="ShowLogin()" data-text="افزودن به سبد خرید"
                                            class="submit-btn btn btn-primary btn-shadow d-block w-100 mt-4 mb-4"
                                            type="submit"><i
                                                class="ci-cart fs-lg me-2"></i>خرید
                                    </button>
                                @endif
                            @else
                                <div class="position-relative bg-size-cover bg-position-center rounded-3 w-100 py-5 mb-grid-gutter"
                                     style="background-image: url({{secure_url('site-v2/img/blog/banner-bg.jpg')}});">
                                    <div class="py-5 px-4 text-center">
                                        <p class="fs-sm text-muted mb-2">با عرض پوزش</p>
                                        <h5 class="mb-3">درحال بروز رسانی شماره ها هستیم، لطفا صبور باشید.</h5>
                                    </div>
                                </div>
                            @endif
                            <table>
                                <tbody>
                                <tr>
                                    <th> 🛒نام سرویس:</th>
                                    <td>buy Google Voice</td>
                                </tr>
                                <tr>
                                    <th>💲قیمت:</th>
                                    <td>{{number_format($Price['custom_price'])}} تومان</td>
                                </tr>
                                <tr>
                                    <th>⏳زمان تحویل</th>
                                    <td> آنی پس از خرید</td>
                                </tr>
                                <tr>
                                    <th>📞قابلیت تماس</th>
                                    <td>دارد</td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="d-flex flex-wrap justify-content-between align-items-center pt-3">
                                <div class="py-2 me-2">
                                </div>
                                <div class="py-2">
                                    <i class="ci-share-alt fs-lg align-middle text-muted me-2"></i>
                                    <a class="btn-social bs-outline bs-facebook bs-sm ms-2"
                                       href="https://www.facebook.com/sharer.php?u={{\Illuminate\Support\Facades\URL::full()}}">
                                        <i class="ci-facebook"></i>
                                    </a>
                                    <a class="btn-social bs-outline bs-whatsapp bs-sm ms-2"
                                       href="https://api.whatsapp.com/send?text={{\Illuminate\Support\Facades\URL::full()}}">
                                        <i class="ci-twitter"></i>
                                    </a>
                                    <a class="btn-social bs-outline bs-twitter bs-sm ms-2"
                                       href="https://twitter.com/intent/tweet?url={{\Illuminate\Support\Facades\URL::full()}}">
                                        <i class="ci-pinterest"></i>
                                    </a>
                                    <a class="btn-social bs-outline bs-instagram bs-sm ms-2" href="#">
                                        <i class="ci-instagram"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
    @include('site-v2.components.why-shopmajazi')
    <section class="container ps-lg-4 pe-lg-3 pt-4 ">
        <section class="mb-3 pb-3">
            <div class="shadow-lg rounded-3 overflow-hidden">
                <div class="row content-area">
                    <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                        <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                            <div class="post-container">
                                <div class="post-content" id="content">
                                    {!! $Page->long_detail !!}
                                </div>
                                <div style="display: block;width: 100%;height: 25px;background-image: linear-gradient(to bottom, rgb(30 28 28 / 0), rgb(241 243 244) 100%);margin-top: -23px;">

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
    @include('site-v2.components.comment',[$Comment,'Type' => 'page' ,'Id' => '6'])
    @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && count($GoogleVoices) != 0)
        <div class="modal fade" id="buy-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-secondary">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                            class="fa-regular fa-cart-shopping"></i>خرید گوگل ویس</label></li>
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
                                        <th>شماره</th>
                                        <th>مبلغ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="ps-2">
                                                    <span id="ProductNameTable"
                                                          class="fw-medium text-heading me-1 ltr"> {{isset($GoogleVoices) ? $GoogleVoices->first()->number : ''}} </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle"
                                            id="ProductPriceTable">{{number_format($Price['custom_price'])}} تومان
                                        </td>
                                        <input type="hidden" name="product_id"
                                               value="{{isset($GoogleVoices) ? $GoogleVoices->first()->id : ''}}"
                                               id="product_id">
                                        <input type="hidden" name="type" value="6" id="type">
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @csrf
                        <div class="modal-footer justify-content-center">
                            @if(CheckWallet() >= $Price['custom_price'])
                                <button type="button" id="second-btn"
                                        class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet"
                                        style="width: 47%">پرداخت با کیف پول
                                </button>
                            @endif
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

        function ChangeName(name, id) {
            $('#ProductName').empty().text(name);
            $('#ProductNameTable').empty().text(name);
            $('#product_id').val(id);
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

        $(document).ready(function () {
            $('button').prop('disabled', false);
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
                        $('#second-btn').remove();
                        $('#ProductNameTable').empty().text(data.name);
                        $('#ProductPriceTable').empty().text(data.price);
                        $('#product_id').val(data.product_id);
                        $('#type').val(data.type);
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
            $('.submit-comment').click(function () {
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
        });
        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        function ShowBuyModal() {
            $('#buy-modal').modal('show')
        }

        $(document).on('click', '.buy-with-wallet', function () {
            var id = $('#product_id').val();
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/6/' + id + '',
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
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                }
            });
        });
        @else
        function ShowLogin() {
            $('#signin-modal').modal('toggle');
            $('#signin-tab .mobile').empty().text('برای خرید گوگل ویس ابتدا وارد حساب کابری خود شوید.').show();
            setTimeout(function () {
                $('#signin-tab .mobile').empty().hide();
            }, 10000)
        }
        @endif
    </script>
@endsection

