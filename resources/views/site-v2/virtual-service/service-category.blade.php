@extends('site-v2.layout.master',['MetaTitle' => $Category->meta_title ?: 'خرید خدمات ' . $Category->name . ' | شاپ مجازی'])
@section('seo')
    @php
        $seo['meta_title'] = $Category->meta_title ?: 'خرید خدمات ' . $Category->name . ' | شاپ مجازی';
        $seo['meta_desc'] = $Category->meta_desc ?: \Illuminate\Support\Str::limit(trim(strip_tags($Category->short_detail ?: $Category->long_detail)), 160);
        $seo['image'] = $Category->image ?: null;
    @endphp
    @include('site-v2.layout.seo')
@endsection
@section('content')
    <div class="bg-header py-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" target="_blank" href="{{secure_url('/')}}"><i
                                        class="ci-home"></i>شاپ مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap"><a target="_blank" href="{{secure_url('virtual-service')}}">خدمات
                                مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">
                            خدمات {{$Category->name}}  </li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0"> خدمات {{$Category->name}} </h1>
            </div>
        </div>
    </div>
    <div class="container py-2 my-md-3">
        <h2 class="h3 text-center pb-4">محصولات</h2>
        <div class="row test">
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="swiper mySwiperjar">
                    <div class="swiper-wrapper">
                        @foreach($Products as $product)
                            <div class="swiper-slide">
                                @php $Detail = json_decode($product->detail,true); @endphp
                                <div class="card product-card card-static rtl">
                                    <a class="card-img-top d-block overflow-hidden" target="_blank"
                                       href="{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}"><img
                                                src="{{secure_url($Detail['image_gallery'])}}" alt="{{$product->name}}"></a>
                                    <div class="card-body py-2">
                                        <a class="product-meta d-block fs-xs pb-1" target="_blank"
                                           href="{{secure_url('/virtual-service/'.$Category->slug)}}">{{$Category->name}}</a>
                                        <h3 class="product-title fs-sm">
                                            <a target="_blank"
                                               href="{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}">خرید {{$Detail['name']}}</a>
                                        </h3>
                                        <div class="d-flex">
                                            <button class="btn btn-primary btn-sm w-100"
                                                    onclick="window.open('{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}', '_blank');"
                                                    type="button"><i class="ci-cart fs-sm me-1"></i>ثبت سفارش
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                @foreach($Products as $product)
                    @php $Detail = json_decode($product->detail,true); @endphp
                            <!-- Product-->
                    <div class="col-3">
                        <div class="card product-card card-static rtl">
                            <a class="card-img-top d-block overflow-hidden" target="_blank"
                               href="{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}"><img
                                        src="{{secure_url($Detail['image_gallery'])}}" alt="{{$product->name}}"></a>
                            <div class="card-body py-2">
                                <a class="product-meta d-block fs-xs pb-1" target="_blank"
                                   href="{{secure_url('/virtual-service/'.$Category->slug)}}">{{$Category->name}}</a>
                                <h3 class="product-title fs-sm">
                                    <a target="_blank"
                                       href="{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}">خرید {{$Detail['name']}}</a>
                                </h3>
                                <div class="d-flex">
                                    <button class="btn btn-primary btn-sm w-100"
                                            onclick="window.open('{{secure_url('/virtual-service/'.$Category->slug.'/'.$product->slug)}}', '_blank');"
                                            type="button"><i class="ci-cart fs-sm me-1"></i>ثبت سفارش
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Product-->
                @endforeach
            @endif
        </div>
    </div>
    <section class="container ps-lg-4 pe-lg-3 pt-4">
        <section class="mb-3 pb-3">
            <div class="bg-light shadow-lg rounded-3 overflow-hidden">
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
                                    @if(!is_null($Category))
                                        {!! $Category->long_detail !!}
                                    @endif
                                </div>
                                <div class="show-more-btn">
                                    <a class="btn btn-primary btn-shadow more-btn"><i
                                                class="ci-book mt-n1 me-2"></i>مشاهده
                                        بیشتر
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table>
                        <tbody>
                        <tr>
                            <th>نام سرویس</th>
                            <td>{{$Category->name}}</td>
                        </tr>
                        <tr>
                            <th>تحویل آنی</th>
                            <td>24 ساعته</td>
                        </tr>
                        <tr>
                            <th>قیمت</th>
                            <td>از 5 هزارتومان</td>
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

{{--    <div class="container py-2 my-md-3">--}}
{{--        <h2 class="h3 text-center pb-4">خرید سریع</h2>--}}
{{--        <section class="mb-3 pb-3">--}}
{{--            <div class="bg-light shadow-lg rounded-3 overflow-hidden">--}}
{{--                <div class="row">--}}
{{--                    @foreach($Products as $product)--}}
{{--                        @php--}}
{{--                            $Detail = json_decode($product->detail,true);--}}
{{--                            $perUser = floatval($product->price / 1000);--}}
{{--                            $perUser = $perUser * $Currency;--}}
{{--                            $perUser = CovertVirtualNumberPrice($perUser);--}}
{{--                        @endphp--}}
{{--                        <div class="col-12">--}}
{{--                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">--}}
{{--                                <div class="widget mb-4 pb-4 border-bottom">--}}
{{--                                    <h3 class="widget-title">{{$Detail['name']}}</h3>--}}
{{--                                    <div class="range-slider">--}}
{{--                                        <div data-price="{{$perUser}}" class="range-slider-ui-{{$product->id}}"--}}
{{--                                             id="range-slider-ui-{{$product->id}}"></div>--}}
{{--                                        <div class="d-flex pb-1">--}}
{{--                                            <div class="w-50 pe-2 me-2">--}}
{{--                                                <input data-totalPrice="" class="form-control price-input"--}}
{{--                                                       id="range-slider-value-min-{{$product->id}}"--}}
{{--                                                       type="hidden">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endforeach--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </section>--}}
{{--    </div>--}}
@endsection

@section('header')
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=13')}}">

    <style>
        #content {
            height: 500px;
            overflow: hidden;
            text-align: justify;
        }

        table tbody {
            display: flex;
            justify-content: center;
        }

        table tbody tr {
            font-size: 13px;
            text-align: center;
            display: inline-grid;
            margin: 13px 20px;
            width: 15%;
            border-radius: 20px;
            background-color: #ebf4ff;
        }

        table tbody tr th {
            border-bottom: 1px solid;
        }

        table tbody tr th, table tbody tr td {
            padding: 10px;
        }

        @media screen and (max-width: 425px) {
            table tbody {
                display: block;
                justify-content: center;
            }

            table tbody tr {
                font-size: 12px;
                text-align: center;
                display: inline-grid;
                margin: 13px 20px;
                width: 37%;
                border-radius: 20px;
                background-color: #ebf4ff;
            }


        }
    </style>
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/simplebar/dist/simplebar.min.css"/>
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/tiny-slider/dist/tiny-slider.css"/>
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/nouislider/distribute/nouislider.min.css"/>
    <link rel="stylesheet" media="screen" href="{{secure_url('/site-v2')}}/vendor/drift-zoom/dist/drift-basic.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>

@endsection

@section('footer-2')
    <script src="{{secure_url('site-v2')}}/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
    <script src="{{secure_url('site-v2')}}/vendor/nouislider/distribute/nouislider.min.js"></script>
    <script src="{{secure_url('site-v2')}}/vendor/drift-zoom/dist/Drift.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

    <script>
        var swiper = new Swiper(".mySwiperjar", {
            slidesPerView: "auto",
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 5000,
                speed: 1000
            },
            breakpoints: {
                360: {
                    slidesPerView: 2,
                },
                480: {
                    slidesPerView: 2,
                },
                720: {
                    slidesPerView: 3,
                }
                , 991: {
                    slidesPerView: 3,
                }, 1140: {
                    slidesPerView: 4,
                },
                1300: {
                    slidesPerView: 4,
                },
                1500: {
                    slidesPerView: 5,
                }
            }
        });
        @foreach($Products as $product)
        var rangeSlider = document.getElementById('range-slider-ui-{{$product->id}}');

        noUiSlider.create(rangeSlider, {
            start: [1000],
            step: 50,
            behaviour: 'snap',
            connect: [true, false],
            tooltips: !0,
            format: {
                to: function (e) {
                    return parseInt(e, 10) + "k"
                },
                from: function (e) {
                    return Number(e)
                }
            },
            range: {
                'min': 0,
                'max': [20000]
            },
        });
        rangeSlider.noUiSlider.on('update', function (values, handle) {
            $('#range-slider-value-min-{{$product->id}}').val(parseInt(values[handle]))
            var price = $('#range-slider-ui-{{$product->id}}').data('price')
            var total = parseInt(values[handle]) * price;
            $('#range-slider-value-min-{{$product->id}}').attr('data-totalPrice', parseInt(total))
        });
        @endforeach



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
    <style>
        .noUi-connect {
            background-color: #003644 !important;
        }

        .noUi-handle {
            border-radius: 100%;
            width: 25%;
        }

        .noUi-handle:after, .noUi-handle:before {

            display: none;
            position: initial;

        }
    </style>
@endsection
