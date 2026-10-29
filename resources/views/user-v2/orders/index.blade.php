@extends('site-v2.layout.master',['MetaTitle' => 'لیست سفارشات | پنل کاربری'])
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="index.html"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">پروفایل</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">لیست سفارشات</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">لیست سفارشات</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
         @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center pt-lg-2 pb-4 pb-lg-5 mb-lg-3">
                    <div class="d-flex align-items-center">
                        <label class="d-none d-lg-block fs-sm text-light text-nowrap opacity-75 me-2" for="order-sort">مرتب سازی:</label>
                        <label class="d-lg-none fs-sm text-nowrap opacity-75 me-2" for="order-sort">مرتب سازی:</label>
                        <select class="form-select" id="order-sort">
                            <option>همه</option>
                            <option>تحویل داده شده</option>
                            <option>درحال پردازش</option>
                        </select>
                    </div>
                </div>
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <div class="tab-content">
                            @include('user-v2.layout.orders',['Orders'=>$list])
                    </div>
                </div>
                {{$list->render('vendor.pagination.bootstrap-4')}}
            </section>
        </div>
    </div>
@endsection
@section('header')
@endsection

@section('footer')

@endsection
