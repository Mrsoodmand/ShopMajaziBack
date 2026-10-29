@extends('site-v2.layout.master')
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="index.html"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="#">حساب</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">روش های پرداخت</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">روش های پرداخت من</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
            @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-8 pt-lg-4 pb-4 mb-3">
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <ul class="nav nav-tabs nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#profile" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <div class="d-none d-lg-block">
                                    <i class="fa-light fa-bags-shopping me-2"></i> سفارشات
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-thin fa-bags-shopping opacity-60 d-block fs-xl mb-2"></i>
                                    </i><span class="fs-ms">سفارشات</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="profile" role="tabpanel">
                            @include('user-v2.layout.orders',[$list])
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
@section('header')
@endsection

@section('footer')

@endsection
