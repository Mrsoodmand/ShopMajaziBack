@extends('site-v2.layout.master')
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{url('/user')}}"><i class="ci-home"></i>خانه</a>
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
            <section class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center pt-lg-2 pb-4 pb-lg-5 mb-lg-3">
                    <div class="d-flex align-items-center">
                    </div>
                    <a class="btn btn-success btn-sm d-none d-lg-inline-block" href="{{secure_url('/user/wallet')}}"><i
                                class="fa-regular fa-file-invoice-dollar me-2" style="margin-top: 0px!important;"></i>شارژ
                        کیف پول</a>
                </div>
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <h2 class="h3 py-2 text-center text-sm-start">فروش / درآمد شما</h2>
                    <div class="row mx-n2 pt-2">
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">موجودی کیف پول</h3>
                                <p class="fs-md text-muted mb-0">{{number_format(\Illuminate\Support\Facades\Auth::guard('user')->user()->wallet)}}تومان </p>
                                <p class="fs-md text-muted mb-0"><a style="font-size: 12px" href="{{secure_url('user/wallet')}}">افزایش موجودی</a></p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">تیکت های شما</h3>
                                <p class="fs-md text-muted mb-0">{{$TicketsCount}}</p>
                                <p class="fs-md text-muted mb-0"><a style="font-size: 12px" href="{{secure_url('user/tickets')}}">مشاهده تیکت ها</a></p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">کسب درآمد (کد رفرال)</h3>
                                <p class="fs-md text-muted mb-0">{{\Illuminate\Support\Facades\Auth::guard('user')->user()->token}}</p>
                                <p class="fs-md text-muted mb-0"><a style="font-size: 12px" href="{{secure_url('user/referral')}}">اطلاعات بیشتر!</a></p>
                            </div>
                        </div>
                    </div>
                </div>
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
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#notifications" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <div class="d-none d-lg-block">
                                    <i class="fa-duotone fa-messages me-2"></i>
                                    تیکت ها
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-duotone fa-messages opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">تیکت ها</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="profile" role="tabpanel">
                      @include('user-v2.layout.orders',[$Orders])
                        </div>
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            @include('user-v2.layout.tickets',[$Tickets])
                        </div>
                    </div>

                    <div>

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
