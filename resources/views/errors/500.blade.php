@extends('site-v2.layout.master',['MetaTitle' => 'گم شده اید؟؟؟؟'])
@section('content')
    <div class="container py-5 mb-lg-3">
        <div class="row justify-content-center pt-lg-4 text-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                <img class="d-block mx-auto mb-5" src="{{url('/site-v2/img/pages/404.png')}}" width="340" alt="خطای 500">
                <h1 class="h3 text-center">خطای 500</h1>
                <h3 class="h5 fw-normal mb-4 text-center">مشکلی برای صفحه بوجود آمده، لطفا با پشتیبان تماس حاصل فرمایید.</h3>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="row">
                    <div class="col-sm-4 mb-3 offset-lg-4">
                        <a class="card h-100 border-0 shadow-sm" href="{{url('/')}}">
                            <div class="card-body">
                                <div class="d-flex align-items-center"><i class="ci-home text-primary h4 mb-0"></i>
                                    <div class="ps-3">
                                        <h5 class="fs-sm mb-0">صفحه نخست</h5><span class="text-muted fs-ms">بازگشت به صفحه اصلی</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('header')

@endsection

@section('footer')
    <script>
        setTimeout(function (){
            location.href = "{{url('/')}}"
        },5000);
    </script>
@endsection

@section('seo')
    @php
        $seo['meta_title'] = 'گم شده اید؟؟؟؟';
        $seo['meta_desc'] = 'گم شده اید؟؟؟؟';
        $seo['image'] = url('upload/logo/logo-v5.jpg');
        $noindex = true;
    @endphp
    @include('site-v2.layout.seo')
@endsection
