@extends('site-v2.layout.master',['MetaTitle' => $PageSeo['title'] ?? 'سوالات متداول | شاپ مجازی'])
@section('content')
    <div class="bg-header py-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{url('/')}}"><i class="ci-home"></i>شاپ
                                مجازی</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">سوالات متداول</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 mb-0">{{ $PageSeo['h1'] ?? 'سوالات متدوال' }}</h1>
            </div>
        </div>
    </div>
    @php
        $Titles = [
             'خرید شماره مجازی',
             'خرید گوگل ویس',
             'خرید آی پی ثابت',
             'خرید اپل آیدی',
             'کسب درآمد',
             'ثبت سفارش',
             'نحوه پرداخت',
             'پشتیبانی',
        ];

        $Questions = [
  0 => [
[
    'q' => 'چگونه شماره مجازی خرید کنم؟',
    'a' => 'ابتدا وارد حساب کاربری خود بشوید، و از منو خرید شماره مجازی را انتخاب کرده، پس از انتخاب سرویس و کشور، شماره در اختیار شما قرار خواهد گرفت'
]
],

];
    @endphp
    <div class="container py-5 mt-md-2 mb-2">
        <div class="row">
            <div class="col-lg-3">
                <!-- مقالات مرتبط sidebar-->
                <div class="offcanvas offcanvas-collapse border-end" id="help-sidebar">
                    <div class="offcanvas-cap align-items-center shadow-sm">
                        <h2 class="h5 mb-0">مقالات مرتبط</h2>
                        <button class="btn-close ms-auto" type="button" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body py-grid-gutter py-lg-1" data-simplebar data-simplebar-auto-hide="true">
                        <!-- Links-->
                        <div class="widget widget-links">
                            <h3 class="widget-title d-none d-lg-block">سوالات متداول</h3>
                            <ul class="widget-list">
                                @foreach($Titles as $key => $title)
                                    <li class="widget-list-item slow-scroll" data-target="question-{{$key}}"><a
                                                class="widget-list-link" href="javascript:void(0);"><i
                                                    class="ci-book opacity-60 align-middle mt-n1 me-1"></i>{{$title}}
                                        </a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                @foreach($Titles as $key => $title)
                    <h2 id="question-{{$key}}" class="h4 pb-3 mt-3">{{$title}}</h2>
                    <div class="accordion accordion-flush" id="methods">
                        @if(array_key_exists($key,$Questions))
                            @foreach($Questions[$key] as $keys => $question)
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <a class="accordion-button" href="#methodOne-{{$key}}-{{$keys}}" role="button"
                                           data-bs-toggle="collapse" aria-expanded="false"
                                           aria-controls="methodOne">{{$question['q']}}</a>
                                    </h3>
                                    <div class="accordion-collapse collapse" id="methodOne-{{$key}}-{{$keys}}"
                                         data-bs-parent="#methods">
                                        <div class="card-body fs-md">
                                            {{$question['a']}}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection


@section('seo')
    @php
        // admin › SEO (App\Services\PageSeo), else the page's own text
        $seo['meta_title'] = $PageSeo['title'] ?? 'سوالات متداول | شاپ مجازی';
        $seo['meta_desc'] = $PageSeo['description'] ?? 'در این صفحه به سوالاتی که ممکن است برای شما پیش بیاید، پاسخ داده ایم';
        $seo['image'] = $PageSeo['image'] ?? secure_url('upload/logo/logo-v5.jpg');
    @endphp
    @include('site-v2.layout.seo')
@endsection
