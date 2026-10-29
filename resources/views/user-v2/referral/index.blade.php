@extends('site-v2.layout.master')
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{secure_url('/')}}"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">حساب کاربری</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">کسب درآمد</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">کسب درآمد</h1>
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
                    <button class="btn btn-success btn-sm d-none d-lg-inline-block copy" data-copy="{{secure_url('user/login?ref='.\Illuminate\Support\Facades\Auth::guard('user')->user()->token)}}">
                        <i class="fa-light fa-copy me-2" style="margin-top: 0px!important;"></i>لینک معرف شما</button>
                </div>
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <h2 class="h3 py-2 text-center text-sm-start">فروش / درآمد شما</h2>
                    <div class="row mx-n2 pt-2">
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">مجموع کسب درآمد شما:</h3>
                                <p class="fs-md text-muted mb-0">{{number_format($SumPayments)}} تومان</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">کاربران زیر مجموعه:</h3>
                                <p class="fs-md text-muted mb-0">{{count($Users)}}</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6 px-2 mb-4">
                            <div class="bg-secondary h-100 rounded-3 p-4 text-center">
                                <h3 class="fs-sm">کسب درآمد (کد رفرال):</h3>
                                <p class="fs-md text-muted mb-0 copy" title="کپی" style="cursor: pointer" data-copy="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->token}}">{{\Illuminate\Support\Facades\Auth::guard('user')->user()->token}}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-2 px-4 ps-lg-0 pe-xl-5">
                    <ul class="nav nav-tabs nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link px-0 active" href="#detail" data-bs-toggle="tab" role="tab"
                               aria-selected="true">
                                <div class="d-none d-lg-block">
                                    <i class="fa-duotone fa-info me-2"></i>
                                   اطلاعات
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-duotone fa-messages opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">اطلاعات</span>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link px-0" href="#notifications" data-bs-toggle="tab" role="tab"
                               aria-selected="true">
                                <div class="d-none d-lg-block">
                                    <i class="fa-duotone fa-messages me-2"></i>
                                    زیر مجموعه ها
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-duotone fa-messages opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">زیر مجموعه ها</span>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link px-0" href="#profile" data-bs-toggle="tab" role="tab"
                               aria-selected="false">
                                <div class="d-none d-lg-block">
                                    <i class="fa-light fa-bags-shopping me-2"></i> تراکنشات
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-thin fa-bags-shopping opacity-60 d-block fs-xl mb-2"></i>
                                    </i><span class="fs-ms">تراکنشات</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="detail" role="tabpanel">
                                <div class="card-body text-center">
                                    <h2 class="h4 pb-3 text-center">با معرفی شاپ مجازی به دوستاتون کسب درآمد کنید!!</h2>
                                    <p class="fs-sm mb-2">با استفاده از این معرف (رفرال) و معرفی شاپ مجازی به دوستات،
                                        <br>
                                        از هر تراکنش آنها <strong class="text-danger">بین 5 تا 15 درصد</strong> پورسانت دریافت کن!</p>
                                    <p class="fs-sm mb-2">کد معرف شما: {{\Illuminate\Support\Facades\Auth::guard('user')->user()->token}}</p>
                                    <p class="fs-sm"> لینک معرف شما: {{secure_url('user/login?ref='.\Illuminate\Support\Facades\Auth::guard('user')->user()->token)}}</p>
                                    <button class="btn btn-primary mt-3 copy" data-copy="{{secure_url('user/login?ref='.\Illuminate\Support\Facades\Auth::guard('user')->user()->token)}}"><i class="fa-light fa-copy"></i> کپی لینک</button>
                                </div>
                        </div>
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            @if(count($Users) != 0)
                                <table class="table table-layout-fixed fs-sm mb-0">
                                    <thead>
                                    <tr>
                                        <th>شماره کاربر</th>
                                        <th>تاریخ عضویت</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($Users as $value)
                                        @php $Number = substr($value->mobile,0,6) @endphp
                                        @php $Number2 = substr($value->mobile,9,3) @endphp
                                        <tr>
                                            <td>{{$Number2.'***'.$Number}}</td>
                                            <td>{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($value->created_at))}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <table class="table table-layout-fixed fs-sm mb-0">
                                    <thead>
                                    <tr>
                                        <th>شماره کاربر</th>
                                        <th>تاریخ عضویت</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>هنوز کسی ثبت نام نکرده!!</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="profile" role="tabpanel">
                            @include('user-v2.layout.payments',['list' => $Payments])
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
    <script>
        $(document).on('click', '.copy', function () {
            $(this).data('copy');
            navigator.clipboard.writeText($(this).data('copy'));
            toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
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
        });
    </script>
@endsection
