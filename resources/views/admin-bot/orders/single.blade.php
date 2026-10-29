@extends('admin-bot.layout.master',['metaTitle' => "سفارش : $Order->id"])

@section('header')
    <style>
        #kt_body {
            overflow: auto;
        !important;
            padding-right: 0px !important;
        }
    </style>
@endsection


@section('content')

    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">

        <div class="toolbar d-flex flex-stack mb-3 mb-lg-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack flex-wrap">
                <div class="page-title d-flex flex-column me-5 py-2">
                    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">جزئیات سفارش</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
                        <li class="breadcrumb-item text-muted">
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted"><a href="{{route('admin.bot-order-list')}}"
                                                                  class="text-muted text-hover-primary">لیست سفارشات</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-900">جزئیات سفارش</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xxl-8">
                    <div class="card-body pt-9 pb-0">
                        <div class="row">
                            <div class="col-12 col-lg-4 col-md-6">
                                <div class="card-flush py-4 flex-row-fluid">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>جزییات سفارش {{$Order->id}}</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5">
                                                <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            نام سفارش
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$Order->name}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            وضعیت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{BotOrderStatus($Order->status)}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">تاریخ ثبت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$jdf->jdate('Y-m-d',strtotime($Order->created_at))}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            ساعت ثبت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$jdf->jdate('H:i:s',strtotime($Order->created_at))}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 col-md-6">
                                <div class="card-flush py-4 flex-row-fluid">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>جزییات کاربر</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 ">
                                                <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            نام کاربر
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end"><a href="{{url('/admin/bot/user/'.$User->id)}}">
                                                            {{$User->name}}
                                                        </a></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            آیدی تلگرام
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$User->username}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">وضعیت</div>
                                                    </td>
                                                    <td class="fw-bold text-end">
                                                        <span class="badge badge-light-success">فعال</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            تاریخ عضویت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$jdf->jdate('H:i:s',strtotime($User->created_at))}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 col-md-6">
                                <div class="card-flush py-4 flex-row-fluid">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>جزییات تراکنش</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 ">
                                                <tbody class="fw-semibold text-gray-600">
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            شماره تراکنش
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$Payment->id}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            نوع پرداخت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$Payment->method}}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">وضعیت</div>
                                                    </td>
                                                    <td class="fw-bold text-end"><span
                                                                class="badge badge-light-success">فعال</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        <div class="d-flex align-items-center">
                                                            تاریخ ثبت
                                                        </div>
                                                    </td>
                                                    <td class="fw-bold text-end">{{$jdf->jdate('H:i:s',strtotime($Payment->created_at))}}</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5 active" data-bs-toggle="tab"
                                   href="#kt_tab_pane_1">جزئیات آی پی</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_2">جزيیات سفارش</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_3">تراکنشات</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row g-5 g-xxl-8 mt-10">
                    <div class="tab-content " id="myTabContent">
                        <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>جزئیات آی پی</h2>
                                    </div>
                                    @if(!is_null($Ip))
                                        <div class="card-toolbar">
                                            <button data-bs-toggle="modal" data-bs-target="#change-order-status"
                                                    class="btn btn-sm btn-primary my-1 me-3">ویرایش سفارش
                                            </button>
                                            <button data-bs-toggle="modal" data-bs-target="#change-ip-status"
                                                    class="btn btn-sm btn-primary my-1 me-3">ویرایش آی پی
                                            </button>
                                            <button data-bs-toggle="modal" data-bs-target="#change-server"
                                                    class="btn btn-sm btn-warning my-1 me-3">انتقال سرور
                                            </button>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if(!is_null($Ip))
                                        @php
                                            $detail = json_decode($Ip->detail,true);
                                            $serverDetail = json_decode($Ip->Server->detail,true);
                                        @endphp
                                        <div class="row">
                                            <div class="col-12 col-lg-6">
                                                <div class="row">
                                                    <div class="col-12 col-lg-6 col-md-6">
                                                        <label class="required form-label">ریمارک</label>
                                                        <input type="text" dir="ltr" class="form-control mb-2" readonly
                                                               value="{{$detail['user_remark']}}">
                                                    </div>
                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">UID</label>
                                                        <input type="text" class="form-control mb-2" readonly
                                                               value="{{$Ip->UID}}">
                                                    </div>
                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">مجموع حجم</label>
                                                        <div class="input-group mb-5">
                                                            <input type="text" dir="ltr" class="form-control"
                                                                   value="{{number_format($Order->total_bandwidth * 1024)}}"/>
                                                            <span class="input-group-text"
                                                                  id="basic-addon1">مگابایت</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">مجموع حجم</label>
                                                        <div class="input-group mb-5">
                                                            <input type="text" dir="ltr" class="form-control"
                                                                   value="{{$Order->total_bandwidth}}"/>
                                                            <span class="input-group-text"
                                                                  id="basic-addon1">گیگ</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">حجم مصرف شده</label>
                                                        <div class="input-group mb-5">
                                                            <input type="text" dir="ltr" class="form-control"
                                                                   value="{{number_format($UsedVolume,3)}}"/>
                                                            <span class="input-group-text"
                                                                  id="basic-addon1">مگابایت</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">حجم مصرف شده</label>
                                                        <div class="input-group mb-5">
                                                            <input type="text" dir="ltr" class="form-control"
                                                                   value="{{number_format($UsedVolume / 1024 ,3)}}"/>
                                                            <span class="input-group-text"
                                                                  id="basic-addon1">گیگ</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">نام سرور</label>
                                                        <input type="text" dir="ltr" class="form-control mb-2" readonly
                                                               value="{{$serverDetail['ip_name']}}">
                                                    </div>
                                                    <div class="col-12 col-lg-6 col-md-6 ">
                                                        <label class="required form-label">تاریخ پایان اشتراک</label>
                                                        <input type="text" dir="ltr" class="form-control mb-2" readonly
                                                               value="{{$jdf->jdate('Y-m-d H:i',strtotime($Ip->expire_at))}}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-6">
                                                <div class="row">
                                                    <div class="col-12 col-lg-6">
                                                        <label class="required form-label">کد</label>
                                                        <textarea class="form-control mb-2" cols="30"
                                                                  style="text-align: justify"
                                                                  rows="8">{{$detail['user_code']}}</textarea>
                                                    </div>
                                                    <div class="col-12 col-lg-6">
                                                        <label class="required form-label">Qr Code</label>
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{urlencode($detail['user_code'])}}&size=250x250"
                                                             width="220" alt="">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-12">
                                                <input type="text" class="form-control mb-2" readonly
                                                       value="موردی یافت نشد">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>جزئیات سفارش</h2>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-lg-4">
                                            <label class="required form-label">نام کشور</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->Country->name}}">
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="required form-label">نام سرویس</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->Service->faName}}">
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <label class="required form-label">مجموع حجم</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->total_bandwidth}} گیگ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>جزئیات سفارش</h2>
                                    </div>
                                    <div class="card-toolbar">
                                        <button type="button" data-bs-toggle="modal"
                                                data-bs-target="#refundModal" class="btn btn-sm btn-warning my-1 me-3">
                                            عودت وجه
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @include('admin-bot.comp.transaction',['list' => $Payments])
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="kt_tab_pane_4" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>جزئیات تراکنش</h2>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <label class="required form-label">نام سفارش</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->name}}">
                                        </div>
                                        <div class="col-4">
                                            <label class="required form-label">نام کشور</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->country_id}}">
                                        </div>
                                        <div class="col-4">
                                            <label class="required form-label">نام سرویس</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->service_id}}">
                                        </div>
                                        <div class="col-4">
                                            <label class="required form-label">مجموع حجم</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->total_bandwidth}}">
                                        </div>
                                        <div class="col-4">
                                            <label class="required form-label">وضعیت</label>
                                            <input type="text" class="form-control mb-2" readonly
                                                   value="{{$Order->status}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modals -->
        <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content rounded">
                    <div class="modal-header pb-0 border-0 justify-content-end">
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                        </div>
                    </div>
                    <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                        <form id="refundForm" class="form" action="{{route('admin.bot-user-charge')}}"
                              method="post">
                            @csrf
                            <div class="mb-13 text-center">
                                <h1 class="mb-3">تغییر وضعیت تراکنش</h1>
                            </div>
                            <div class="row g-9 mb-8">
                                <div class="col-md-12 fv-row">
                                    <label class="required fs-6 fw-bold mb-2">شماره تراکنش</label>
                                    <select class="form-select form-select-solid" name="payment_id"
                                            id="charge-wallet-transaction"
                                            data-control="select2"
                                            data-hide-search="true">
                                        <option value="0" data-price="0">
                                            تراکنش را انتخاب کنید
                                        </option>
                                        @foreach($Payments->where('type','!=',2) as $payment)
                                            <option value="{{$payment->id}}" data-price="{{$payment->price}}">
                                                شناسه: {{$payment->id}} -
                                                مبلغ: {{number_format($payment->price)}} تومان -
                                                نوع: {{__('payments.type.'.$payment->type)}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-9 mb-8">
                                <div class="col-md-12 fv-row">
                                    <label class="required fs-6 fw-bold mb-2">نوع عملیات</label>
                                    <select class="form-select form-select-solid" name="wallet_action" id="sel1"
                                            data-control="select2"
                                            data-hide-search="true">
                                        <option value="1">شارژ حساب</option>
                                        <option value="0">کسر از حساب</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-column mb-8 fv-row">
                                <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                    <span class="required">علت تغییر وضعیت</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                       title="لطفا علت تغییر وضعیت را یادداشت نمایید"></i>
                                </label>
                                <input type="text" class="form-control form-control-solid" id="charge-modal-detail"
                                       placeholder="علت تغییر وضعیت" name="detail"/>
                                <input type="hidden" value="{{$Order->id}}" name="order_id"/>
                                <input type="hidden" value="{{$Order->user_id}}" name="id"/>
                            </div>
                            <div class="text-center">
                                <button type="reset" id="kt_modal_new_target_cancel"
                                        class="btn btn-light me-3">انصراف
                                </button>
                                <button type="submit" id="btn-refund" data-form="refundForm" data-modal="refundModal" data-text="استرداد وجه"
                                        class="btn btn-primary submit-btn">
                                    <span class="indicator-label">استرداد وجه</span>
                            </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="change-order-status" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content rounded">
                    <div class="modal-header pb-0 border-0 justify-content-end">
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                        </div>
                    </div>
                    <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                        <form id="CurrentForm" class="form" action="{{route('admin.bot-order-change-status')}}"
                              method="post">
                            @csrf
                            <div class="mb-13 text-center">
                                <h1 class="mb-3">تغییر وضعیت سفارش</h1>
                            </div>
                            <div class="row g-9 mb-8">
                                <div class="col-md-12 fv-row">
                                    <label class="required fs-6 fw-bold mb-2">وضعیت</label>
                                    <select class="form-select form-select-solid" name="status" id="sel1"
                                            data-control="select2"
                                            data-hide-search="true">
                                        <option {{$Order->status == 1 ? 'selected' :''}} value="1">تکمیل شده</option>
                                        <option {{$Order->status == 0 ? 'selected' :''}} value="0">در انتظار پرداخت
                                        </option>
                                        <option {{$Order->status == -1 ? 'selected' :''}} value="-1">لغو شده</option>
                                        <option {{$Order->status == -2 ? 'selected' :''}} value="-2">معلق شده</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex flex-column mb-8 fv-row">
                                <!--begin::Tags-->
                                <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                    <span class="required">علت تغییر وضعیت</span>
                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                       title="لطفا علت تغییر وضعیت را یادداشت نمایید"></i>
                                </label>
                                <input type="text" class="form-control form-control-solid"
                                       placeholder="علت تغییر وضعیت"
                                       name="detail"/>
                                <input type="hidden" name="order_id" value="{{$Order->id}}">
                            </div>
                            <div class="text-center">
                                <button type="reset" id="kt_modal_new_target_cancel"
                                        class="btn btn-light me-3">انصراف
                                </button>
                                <button type="submit" id="submit_btn" class="btn btn-primary">
                                    <span class="indicator-label">ثبت</span>
                                    <span class="indicator-progress">لطفا صبر کنید...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if(!is_null($Ip))
            <div class="modal fade" id="change-ip-status">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content rounded">
                        <div class="modal-header pb-0 border-0 justify-content-end">
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                            </div>
                        </div>
                        <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                            <form id="bot-order-change-ip" class="form" action="{{route('admin.bot-order-change-ip')}}"
                                  method="post">
                                @csrf
                                <div class="mb-13 text-center">
                                    <h1 class="mb-3">تغییر وضعیت آی پی</h1>
                                </div>
                                <div class="row mb-8">
                                    <div class="col-md-12">
                                        <label class="required fs-6 fw-bold mb-2">وضعیت آی پی</label>
                                        <select class="form-select form-select-solid" name="status" >
                                            <option {{$Order->status == 1 ? 'selected' :''}} value="1">تکمیل شده
                                            </option>
                                            <option {{$Order->status == 0 ? 'selected' :''}} value="0">در انتظار پرداخت
                                            </option>
                                            <option {{$Order->status == -1 ? 'selected' :''}} value="-1">لغو شده
                                            </option>
                                            <option {{$Order->status == -2 ? 'selected' :''}} value="-2">معلق شده
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-lg-6 mt-3">
                                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                            <span class="required">ریمکارک</span>
                                        </label>
                                        <input type="text" class="form-control form-control-solid"
                                               value="{{$detail['user_remark']}}"
                                               name="remark"/>
                                    </div>
                                    <div class="col-12 col-lg-6 mt-3">
                                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                            <span class="required">UID</span>
                                        </label>
                                        <input type="text" class="form-control form-control-solid"
                                               value="{{$Ip['UID']}}"
                                               name="UID"/>
                                    </div>
                                    <div class="col-12 col-lg-6 mt-3">
                                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                            <span class="required">حجم کل</span>
                                        </label>
                                        <input type="text" class="form-control form-control-solid"
                                               value="{{$Order->total_bandwidth}}"
                                               name="total"/>
                                    </div>
                                    <div class="col-12 col-lg-6 mt-3">
                                        <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                            <span class="required">تاریخ اتمام اشتراک</span>
                                        </label>
                                        <input type="datetime-local" class="form-control form-control-solid"
                                               value="{{$Ip->expire_date}}"
                                               name="expire_date"/>
                                    </div>
                                </div>
                                <input type="hidden" name="id" value="{{$Ip->id}}">
                                <div class="text-center">
                                    <button type="reset" data-bs-dismiss="modal"
                                            class="btn btn-light me-3">انصراف
                                    </button>
                                    <button type="submit" id="bot-order-change-ip-btn" data-modal="change-ip-status"
                                            data-form="bot-order-change-ip"
                                            class="btn btn-primary submit-btn">
                                        <span class="indicator-label">ثبت</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="change-server" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content rounded">
                        <div class="modal-header pb-0 border-0 justify-content-end">
                            <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                            </div>
                        </div>
                        <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                            <form id="change-server-form" class="form" action="{{route('admin.bot-server-ip')}}"
                                  method="post">
                                @csrf
                                <div class="mb-13 text-center">
                                    <h1 class="mb-3">تغییر سرور</h1>
                                </div>
                                <div class="row g-9 mb-8">
                                    <div class="col-md-12 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">سرور کنونی</label>
                                        <input type="text" dir="ltr" class="form-control form-control-solid"
                                               value="{{$serverDetail['ip_name']}}"/>
                                    </div>
                                </div>
                                <div class="d-flex flex-column mb-8 fv-row">
                                    <!--begin::Tags-->
                                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                        <span class="required">سروری که میخواهید کاربر را منتقل کنید</span>
                                        <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip"
                                           title="لطفا علت تغییر وضعیت را یادداشت نمایید"></i>
                                    </label>
                                    <select name="to" id="" class="form-select">
                                        @foreach($Servers as $item)
                                            @php
                                            $itemDetail = json_decode($item->detail,true);
                                            @endphp
                                            <option value="{{$item->id}}">{{$itemDetail['ip_name']}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id" value="{{$Order->id}}">
                                </div>
                                <div class="text-center">
                                    <button type="reset" id="kt_modal_new_target_cancel"
                                            class="btn btn-light me-3">انصراف
                                    </button>
                                    <button type="submit" id="submit_btn_change_server" data-modal="change-server" data-form="change-server-form" class="btn btn-primary submit-btn">
                                        <span class="indicator-label">ثبت</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection


@section('footer')

    <script src="{{url('js/jquery.form.min.js')}}" type="text/javascript"></script>

    <script>
        $(document).on('click', '.check-payment', function () {
            var id = $(this).data('id');
            $('.check-payment').empty().text('لطفا صبر کنید...');

            $.ajax({
                url: '{{route('admin.bot-payment-single')}}',
                dataType: 'JSON',
                method: "GET",
                data: {
                    id: id
                }, success: function (data) {
                    $('#PaymentDetail').empty().append(data.data);
                    $('#PaymentModal').modal('toggle');
                    $('.check-payment').empty().text('مشاهده جزئیات');
                }
            });
        });

        $('#charge-wallet-transaction').on('change', function () {
            var id = $('#charge-wallet-transaction').val();
            var price = $('#charge-wallet-transaction').find(':selected').data('price');
            $('#charge-wallet-price').val(price);
            var text = 'عودت وجه صورتحساب شماره ' + id + ' از شماره سفارش {{$Order->id}}'
            $('#charge-modal-detail').empty().val(text);
        });

    </script>
    <script>

        $(document).ready(function () {
            $('button').prop('disabled', false);
            $('.submit-btn').click(function () {
                var BtnId = $(this).attr('id');
                var text = $(this).text();
                var formName = $(this).data('form');
                var modal = $(this).data('modal');

                $('#' + BtnId).html('لطفا صبر کنید...');
                $('#' + BtnId).attr('disabled', true);

                var form = document.querySelector('#' + formName);

                $(form).ajaxSubmit({
                    dataType: 'json',
                    beforeSend: function () {
                        $('#' + BtnId).html('لطفا صبر کنید');
                    },
                    success: function (data) {
                        toastr.success("عملیات با موفقیت انجام شد");
                        if (modal != null) {
                            $('#' + modal).modal('toggle');
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    },
                    error: function (err) {
                        console.log(err)
                        err.responseJSON.error.forEach(function (value) {
                            toastr.error(value, "خطا");
                        });
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                    }
                });
                return false;
            });
        });

    </script>

@endsection
