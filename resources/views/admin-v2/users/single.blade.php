@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-6 mb-xl-9">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <a href="javascript:void(0)"
                                               class="text-gray-800 text-hover-primary fs-2 fw-bolder me-3">{{$user->name}}</a>
                                            @if($user->account_blocked)
                                                <span class="badge badge-light-danger me-auto">غیرفعال</span>
                                            @else
                                                <span class="badge badge-light-success me-auto">فعال</span>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap fw-bold mb-4 fs-5 text-gray-400">{{$user->mobile}}</div>
                                    </div>
                                    <div class="d-flex mb-4">
                                        @can('edit-user')
                                            <a href="{{route('admin.user edit',$user->id)}}"
                                               class="btn btn-sm btn-primary me-3" target="_blank">ویرایش کاربر</a>
                                        @endcan
                                        @can('user-panel')
                                            <a href="{{route('admin.user panel',$user->id)}}"
                                               class="btn btn-sm btn-success me-3" target="_blank">پنل کاربر کاربر</a>
                                        @endcan
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap justify-content-start">
                                    <div class="d-flex flex-wrap">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i
                                                        class="fa-light fa-calendar-days"></i> تاریخ ثبت نام
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="fs-4 fw-bolder">{{$jdf->jdate('d F Y',strtotime($user->created_at))}}</div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i class="fa-duotone fa-wallet"
                                                                                            style="margin-left: 5px"></i>موجودی
                                                کیف پول
                                            </div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{number_format($user->wallet)}} تومان</div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i class="fa-light fa-lock"
                                                                                            style="margin-left: 5px"></i>موجودی
                                                مسدود شده
                                            </div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{number_format($user->blocked_wallet)}}
                                                    تومان
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="fw-bold fs-6 text-gray-400 mb-2"><i
                                                        class="fa-light fa-basket-shopping"
                                                        style="margin-left: 5px"></i>مجموع سفارشات
                                            </div>
                                            <div class="d-flex">
                                                <div class="fs-4 fw-bolder">{{$OrdersCount}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <i class=""></i>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <ul class="nav nav-stretch nav-tabs nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bolder">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#orders">سفارشات</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#transactions">تراکنش
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#tickets">تیکت
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#commission">پورسانت
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#ref_users">کاربران
                                    معرفی شده</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#requests">درخواست
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#comments">کامنت
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#errors">خطاها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#logs">لاگ
                                    ها</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--end::Navbar-->
                <!--begin::Row-->
                <div class="row g-6 g-xl-9">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="orders" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>سفارشات</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        @include('admin-v2.comp.orders',$Orders)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="transactions" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>تراکنش ها</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        @include('admin-v2.comp.payments',$Payments)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tickets" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>تیکت ها</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        @include('admin-v2.comp.tickets',$Payments)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="commission" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>پورسانت های دریافت شده</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        @include('admin-v2.comp.Commission',['Payments' => $Commission])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="ref_users" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>کاربر های معرفی شده</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle gs-0 gy-4">
                                                <thead>
                                                <tr class="fw-bolder text-muted bg-light">
                                                    <th class="ps-4">نام و نام خانوادگی</th>
                                                    <th class="min-w-75px">توضیحات</th>
                                                    <th class="min-w-75px">مشاهده</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(count($Users) != 0)
                                                    @foreach($Users as $user)
                                                        <tr>
                                                            <td class="ps-4">
                                                                <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$user->name}}</label>
                                                                <span class="text-muted fw-bold d-block fs-7">{{$user->mobile}}</span>
                                                            </td>
                                                            <td>
                                                                <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($user->created_at))}}</label>
                                                                <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($user->created_at))}}</span>
                                                            </td>
                                                            <td style="padding-left: 20px!important;">
                                                                <a target="_blank"
                                                                   href="{{secure_url('/admin/user/',$user->id)}}">مشاهده</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="9">
                                                            <a href="javascript:;"
                                                               class="text-dark text-center fw-bolder text-hover-primary d-block mb-1 fs-6">پورسانتی
                                                                دریافت نشده</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

