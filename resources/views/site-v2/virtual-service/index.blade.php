@extends('site-v2.layout.master')
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
                    <h1 class="">خدمات مجازی</h1>
                    <p class="pb-3 text-white m-text-justify">شماره مجازی" جزء آن دسته واژه هایی است که امروزه در کسب و
                        کارهای آنلاین و به خصوص
                        بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود افراد می توانند با اهداف شخصی نیز از این
                        شماره ها استفاده نمایند. از همین رو می توان بدون صرف هزینه و زمان زیاد این شماره ها را از "وب
                        سایت شماره مجازی" در سریع ترین زمان ممکن خریداری نمایید بدون آنکه دغدغه ارائه مدارک و تایید آنها
                        را داشته باشید.</p>
                    <div class="d-table scale-up delay-4 mx-auto mx-lg-0">
                        <button class="btn btn-primary slow-scroll" style="margin-left: 10px;margin-bottom: 10px;"
                                data-target="CountryArea">ثبت سفارش<i class="ci-arrow-down ms-2 me-n1"></i></button>
                        <a class="btn btn-primary" target="_blank" style="margin-left: 10px;margin-bottom: 10px;"
                           href="{{secure_url('user/login')}}">ثبت نام<i class="ci-arrow-left ms-2 me-n1"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-12">
                    <img class="gallery-item rounded-3" src="{{secure_url('/upload/services/virtual-service.svg')}}"
                         alt="Gallery image">
                </div>
            </div>
        </div>
    </section>
    <div class="container pb-4 pb-sm-5">
        <!-- Categories grid-->
        <div class="row pt-5">
            <!-- Catogory-->
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="swiper mySwiperjar">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="card border-0">
                                <div class="d-block overflow-hidden rounded-3">
                                    <img class="d-block w-100" src="{{secure_url('upload/services/instagram.jpg')}}" alt="خدمات اینستاگرام">
                                </div>
                                <div class="card-body">
                                    <h2 class="h5">خدمات اینستاگرام</h2>
                                    <ul class="list-unstyled fs-sm mb-0">
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram','buy-instagram-story-view'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ویو پست و استوری</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram',''])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید فالور واقعی</a></li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram','buy-instagram-fake-followers'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید فالور فیک</a></li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram','buy-instagram-like'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید لایک</a></li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram',''])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید کامنت</a></li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['instagram','buy-instagram-videos-igtv-reel-views'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ویو Reels</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="card border-0">
                                <div class="d-block overflow-hidden rounded-3">
                                    <img class="d-block w-100" src="{{secure_url('/upload/services/telegram.jpg')}}" alt="خدمات تلگرام">
                                </div>
                                <div class="card-body">
                                    <h2 class="h5">خدمات تلگرام</h2>
                                    <ul class="list-unstyled fs-sm mb-0">
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['telegram','buy-real-telegram-members'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ممبر واقعی تلگرام</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['telegram','buy-fake-telegram-member'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ممبر فیک تلگرام</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['telegram','buy-telegram-last-post-view'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ویو تلگرام(اخرین پست)</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="{{route('virtual-service',['telegram','buy-telegram-vote-and-like'])}}"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید رای تلگرام</a></li>
                                    </ul>
                                </div>
                            </div>

                        </div>
                        <div class="swiper-slide">
                            <div class="card border-0">
                                <div class="d-block overflow-hidden rounded-3">
                                    <img class="d-block w-100" src="{{secure_url('/upload/services/youtube.jpg')}}" alt="خدمات یوتیوب"></div>
                                <div class="card-body">
                                    <h2 class="h5">خدمات یوتیوب</h2>
                                    <ul class="list-unstyled fs-sm mb-0">
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="#"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید سابسکرایب فیک</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="#"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید سابسکرایب واقعی</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="#"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید لایک و دیس لایک</a>
                                        </li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="#"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید کامنت</a></li>
                                        <li class="d-flex align-items-center justify-content-between"><a
                                                    class="nav-link-style"
                                                    href="#"><i
                                                        class="ci-arrow-left-circle me-2"></i>خرید ویو</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-3">
                    <div class="card product-card-alt border-0">
                        <div class="d-block overflow-hidden rounded-3">
                            <img class="d-block product-image w-100" width="250" height="250" src="{{secure_url('upload/services/instagram.jpg')}}" alt="خدمات اینستاگرام">
                        </div>
                        <div class="card-body">
                            <h2 class="h5">خدمات اینستاگرام</h2>
                            <ul class="list-unstyled fs-sm mb-0">
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['instagram','buy-instagram-videos-igtv-reel-views'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                        خرید بازدید ویدیو و Reels اینستاگرام
                                    </a>
                                </li>
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['instagram','buy-instagram-like'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                      خرید لایک اینستاگرام
                                    </a>
                                </li>
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['instagram','buy-instagram-fake-followers'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                        خرید فالوور فیک اینستاگرام
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card product-card-alt border-0">
                        <div class="d-block overflow-hidden rounded-3">
                            <img class="d-block product-image w-100" height="250" width="250" src="{{secure_url('/upload/services/telegram.jpg')}}" alt="خدمات تلگرام">
                        </div>
                        <div class="card-body">
                            <h2 class="h5">خدمات تلگرام</h2>
                            <ul class="list-unstyled fs-sm mb-0">
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['telegram','buy-fake-telegram-member'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                       خرید ممبر فیک (غیر واقعی) تلگرام
                                    </a>
                                </li>
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['telegram','buy-real-telegram-members'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                        خرید ممبر واقعی تلگرام 100
                                    </a>
                                </li>
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['telegram','buy-telegram-vote-and-like'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                        خرید رای تلگرام (vote) و لایک تلگرام
                                    </a>
                                </li>
                                <li class="d-flex align-items-center justify-content-between">
                                    <a class="nav-link-style" href="{{route('virtual-service',['telegram','buy-telegram-last-post-view'])}}">
                                        <i class="ci-arrow-left-circle me-2"></i>
                                        خرید بازدید (ویو) آخرین پست تلگرام
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card product-card-alt border-0">
                        <div class="d-block overflow-hidden rounded-3">
                            <img class="d-block product-image w-100" width="250" height="250" src="{{secure_url('/upload/services/youtube.jpg')}}" alt="خدمات یوتیوب"></div>
                        <div class="card-body">
                            <h2 class="h5">خدمات یوتیوب</h2>
                            <ul class="list-unstyled fs-sm mb-0">
                                <li class="d-flex align-items-center justify-content-between"><a class="nav-link-style"
                                                                                                 href="#"><i
                                                class="ci-arrow-left-circle me-2"></i>خرید سابسکرایب فیک</a></li>
                                <li class="d-flex align-items-center justify-content-between"><a class="nav-link-style"
                                                                                                 href="#"><i
                                                class="ci-arrow-left-circle me-2"></i>خرید سابسکرایب واقعی</a></li>
                                <li class="d-flex align-items-center justify-content-between"><a class="nav-link-style"
                                                                                                 href="#"><i
                                                class="ci-arrow-left-circle me-2"></i>خرید لایک و دیس لایک</a></li>
                                <li class="d-flex align-items-center justify-content-between"><a class="nav-link-style"
                                                                                                 href="#"><i
                                                class="ci-arrow-left-circle me-2"></i>خرید کامنت</a></li>
                                <li class="d-flex align-items-center justify-content-between"><a class="nav-link-style"
                                                                                                 href="#"><i
                                                class="ci-arrow-left-circle me-2"></i>خرید ویو</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
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
                            <td>خدمات مجازی</td>
                        </tr>
                        <tr>
                            <th>تحویل</th>
                            <td>آنی پس از خرید</td>
                        </tr>
                        <tr>
                            <th>قیمت</th>
                            <td>3,000 تومان</td>
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
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=14')}}">

    <style>

        .product-image {
            width: 93% !important;
            display: block !important;
            margin: auto !important;
            margin-top: 10px !important;
            border-radius: 5px !important;
        }
        .product-card-alt {
            background: ghostwhite;
        }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css"/>

@endsection
@section('footer')
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
