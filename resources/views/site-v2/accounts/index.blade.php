@section('seo')
    @php
        $Seo = json_decode($Page->seo,true);
            $seo['meta_title'] = $Seo['meta_title'];
            $seo['meta_desc'] = $Seo['meta_desc'];
            $seo['image'] = url($Page->image);
    @endphp
    @include('site-v2.layout.seo')
@endsection

@extends('site-v2.layout.master',['MetaTitle' =>  $Seo['meta_title']])
@section('content')
    <section class="position-relative bg-size-cover bg-position-center-x position-relative bg-header">
        <div class="container position-relative zindex-5 py-4 my-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2" style="    float: left;
    position: absolute;
    left: 0;">
                <nav aria-label="breadcrumb">

                </nav>
            </div>
            <div class="row justify-content-center" style="margin-top: 42px">
                <div class="col-lg-8 col-12">
                    <h1 class="">{{$Page->h1}}</h1>
                    <p class="pb-3 text-white text-justify">شماره مجازی" جزء آن دسته واژه هایی است که امروزه در کسب و
                        کارهای آنلاین و به خصوص
                        بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود افراد می توانند با اهداف شخصی نیز از این
                        شماره ها استفاده نمایند. از همین رو می توان بدون صرف هزینه و زمان زیاد این شماره ها را از "وب
                        سایت شماره مجازی" در سریع ترین زمان ممکن خریداری نمایید بدون آنکه دغدغه ارائه مدارک و تایید آنها
                        را داشته باشید.</p>
                    <div class="d-table scale-up delay-4 mx-auto mx-lg-0">
                        <a class="btn btn-primary" target="_blank" style="margin-left: 10px;margin-bottom: 10px;"
                           href="{{secure_url('user/login')}}">ثبت نام</a>
                        <button class="btn btn-primary slow-scroll" style="margin-left: 10px;margin-bottom: 10px;"
                                data-target="CountryArea">ثبت سفارش
                        </button>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <img class="gallery-item rounded-3" width="390" height="250" src="{{secure_url('/upload/accounts/accounts.svg')}}"
                         alt="{{$Page->h1}}">
                </div>
            </div>
        </div>
    </section>

    <section class="container ps-lg-4 pe-lg-3 pt-4 ">
        <section class="mb-3 pb-3">
            <div class="rounded-3">
                <div class="row content-area" id="CountryArea">
                    @foreach($Product as $item)
                        @php
                            $Detail = json_decode($item->detail,true);
                        @endphp
                        <div class="col-lg-3 col-md-4 col-6 mt-2">
                            <div class="">
                                <div class="card product-card-alt">
                                    <div class="product-thumb">
                                        <a href="{{secure_url('/accounts/'.$item->slug)}}"></a>
                                        <img class="product-image" src="{{secure_url($Detail['image_gallery'])}}" width="250" height="250" alt="{{$item->faName}}">
                                    </div>
                                    <div class="card-body">
                                        <h3 class="product-title fs-sm mb-2 text-center"><a
                                                    href="{{secure_url('/accounts/'.$item->slug)}}">{{$item->faName}}</a></h3>
                                        <ul class="list-unstyled fs-sm">
                                            <li class="justify-content-center d-flex align-items-center"><i
                                                        class="ci-check-circle text-success me-1"></i><span
                                                        class="fs-ms">تحویل بر روی ایمیل شخصی شما.</span></li>
                                            <li class="justify-content-center d-flex align-items-center"><i
                                                        class="ci-check-circle text-success me-1"></i><span
                                                        class="fs-ms">تحویل بین 1 تا 5 روز کاری.</span></li>
                                            <li class="justify-content-center d-flex align-items-center"><i
                                                        class="ci-check-circle text-success me-1"></i><span
                                                        class="fs-ms">پشتیبانی 24 ساعته.</span></li>
                                        </ul>
                                        <a href="{{secure_url('/accounts/'.$item->slug)}}" target="_blank"
                                           class="btn btn-icon btn-sm me-2 btn-info w-100" title="ثبت سفارش"><i
                                                    class="fa-regular fa-cart-shopping mr-2 btn-order"></i>ثبت سفارش</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </section>

    <section class="container ps-lg-4 pe-lg-3 pt-4 ">
        <section class="mb-3 pb-3">
            <div class="shadow-lg rounded-3 overflow-hidden">
                <div class="row content-area">
                    <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                        <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                            <div class="post-container">
                                {{--                                    <div class="gallery row pb-2" lg-uid="lg0">--}}
                                {{--                                        <div class="col-sm-8 offset-lg-2"><img--}}
                                {{--                                                    class="gallery-item rounded-3 mb-grid-gutter"--}}
                                {{--                                                    src="{{secure_url('/site-v2/')}}/img/blog/single/01.jpg"--}}
                                {{--                                                    alt="Gallery image">--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                <div class="post-content" id="content">
                                    {!! $Page->long_detail !!}
                                </div>
                                <div class="content-shadow">

                                </div>
                                <div class="show-more-btn">
                                    <button class="btn btn-primary btn-shadow more-btn"><i
                                                class="ci-book mt-n1 me-2"></i>مشاهده
                                        بیشتر
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table>
                        <tbody>
                        <tr>
                            <th>نام سرویس</th>
                            <td>خرید اکانت های خارجی</td>
                        </tr>
                        <tr>
                            <th>تحویل</th>
                            <td>آنی پس از خرید</td>
                        </tr>
                        <tr>
                            <th>نحوه فعالسازی</th>
                            <td>بر روی ایمیل شخصی</td>
                        </tr>
                        <tr>
                            <th>پشتیبانی</th>
                            <td>24 ساعته</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </section>
@endsection

@section('header')

    <style>
        .product-card-alt {
            background: ghostwhite;
        }

        .product-image {
            width: 93% !important;
            display: block !important;
            margin: auto !important;
            margin-top: 10px !important;
            border-radius: 5px !important;
        }

        .btn-order {
            margin-left: 10px;
        }

        #content {
            height: 500px;
            overflow: hidden;
            text-align: justify
        }

        .content-area table tbody {
            display: flex;
            justify-content: center
        }

        .content-area table tbody tr {
            font-size: 13px;
            text-align: center;
            display: inline-grid;
            margin: 13px 20px;
            width: 15%;
            border-radius: 20px;
            color: #fff;
            background-color: #003644
        }

        .content-area table tbody tr th {
            border-bottom: 1px solid
        }

        .content-area table tbody tr th, table tbody tr td {
            padding: 10px
        }

        @media screen and (max-width: 425px) {
            .content-area table tbody {
                display: block;
                justify-content: center
            }

            .content-area table tbody tr {
                font-size: 12px;
                text-align: center;
                display: inline-grid;
                margin: 13px 20px;
                width: 37%;
                border-radius: 20px;
                background-color: #003644
            }

            .content-area table tbody tr:first-child, table tbody tr:nth-child(3) {
                margin: 1.5% 7%
            }
        }
    </style>
@endsection

@section('footer')
    <script type="text/javascript">
        $('.change-type').click(function () {
            $('#ProductName').empty().text($(this).data('name'));
            $('#ProductPrice').empty().text($(this).data('price'));
        });

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
        });
        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        $(document).on('click', '.buy-with-wallet', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/3/' + id + '',
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
        @endif
    </script>
@endsection
