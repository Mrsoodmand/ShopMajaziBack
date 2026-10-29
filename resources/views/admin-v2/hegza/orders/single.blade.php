@extends('admin-v2.layout.master',['MetaTitle' => "جزئیات سفارش شماره $Order->id"])
@section('content')

    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if(\Illuminate\Support\Facades\Session::has('error'))
                    @foreach(\Illuminate\Support\Facades\Session::get('error') as $error)
                        <div class="d-flex align-items-center bg-danger rounded p-5 mb-3">
                            <div class="flex-grow-1 me-2">
                                <span class="fw-bolder text-white fs-6">{{$error}}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
                <div class="d-flex flex-column gap-7 gap-lg-10">
                    <div class="d-flex flex-column flex-xl-row gap-7 gap-lg-10">
                        <div class="card card-flush py-4 flex-row-fluid">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>جزییات سفارش({{$Order->id}})</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                        <tbody class="fw-bold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-light fa-calendar-days" style="margin-left: 5px"></i>تاریخ
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{$Jdf->jdate('Y-m-d',strtotime($Order->created_at))}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-regular fa-credit-card" style="margin-left: 5px"></i>ساعت:
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{$Jdf->jdate('H:i:s',strtotime($Order->created_at))}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-solid fa-gears" style="margin-left: 5px"></i>وضعیت
                                                    سفارش
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{__('order.bot-status.'.$Order->status)}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card card-flush py-4 flex-row-fluid">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>جزییات مشتری</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                        <tbody class="fw-bold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <span class="svg-icon svg-icon-2 me-2">
																	<i class="fa-light fa-user me-2"></i>نام کاربر
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    @if(!is_null($Order->User))
                                                        <a target="_blank"
                                                           href="{{route('admin.bot-single-user',$Order->User->id)}}"
                                                           class="text-gray-600 text-hover-primary">{{$Order->User->username}}</a>
                                                    @else
                                                        <a target="_blank"
                                                           href="#"
                                                           class="text-gray-600 text-hover-primary">ثبتنام نکرده</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-light fa-file-user me-2"></i>
                                                    پروفایل کاربر
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">
                                                <a target="_blank" href="{{route('admin.bot-single-user',$Order->user_id)}}" class="text-gray-600 text-hover-primary">مشاهده</a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap flex-stack gap-5 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-lg-n2 me-auto">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab"
                                   href="#kt_ecommerce_sales_order_summary">خلاصه سفارش</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#kt_ecommerce_sales_order_history">تاریخچه سفارش</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#transactions">تراکنش ها</a>
                            </li>
                        </ul>
                        <button class="btn btn-warning btn-sm"
                                type="button" data-bs-toggle="modal"
                                data-bs-target="#refundModal"
                        >عودت وجه به کیف پول
                        </button>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_new_target">
                            تغییر وضعیت
                        </button>
                        <button class="btn btn-info btn-sm" type="button" data-bs-toggle="modal"
                                data-bs-target="#change-ip-setting">
                            ویرایش آی پی
                        </button>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="kt_ecommerce_sales_order_summary" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">

                                @include('admin-v2.hegza.orders.page.ip-sabet',[$Order,$Jdf,$Ip])
                            </div>
                        </div>
                        <div class="tab-pane fade" id="transactions" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4 flex-row-fluid">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>تاریخچه سفارش</h2>
                                        </div>
                                    </div>
                                    <div id="kt_ecommerce_customer_addresses" class="card-body pt-0 pb-5">
                                        @include('admin-v2.hegza.comp.payments',[$Payments])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php $status = __('order.bot-status'); @endphp
    <div class="modal fade" id="kt_modal_new_target" tabindex="-1" aria-hidden="true">
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
                    <form id="CurrentForm" class="form" action="{{route('admin.bot-orders-change-status')}}"
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
                                    @foreach($status as $key => $item)
                                        <option {{$Order->status == $key ? 'selected' :''}} value="{{$key}}">
                                            {{$item}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="id" value="{{$Order->id}}">

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

    <div class="modal fade" id="change-ip-setting" tabindex="-1" aria-hidden="true">
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
                    <form id="CurrentForm" class="form" action="{{route('admin.bot-orders-change-status')}}"
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
                                    @foreach($status as $key => $item)
                                        <option {{$Order->status == $key ? 'selected' :''}} value="{{$key}}">
                                            {{$item}}
                                        </option>
                                    @endforeach
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
                    <form id="refundForm" class="form" action="{{route('admin.return back order money to user')}}"
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
                        </div>
                        <div class="text-center">
                            <button type="reset" id="kt_modal_new_target_cancel"
                                    class="btn btn-light me-3">انصراف
                            </button>
                            <button type="submit" id="btn-refund" data-form="refundForm" data-text="استرداد وجه"
                                    class="btn btn-primary submit-form">
                                <span class="indicator-label">استرداد وجه</span>
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
@endsection

@section('footer')
    @include('admin-v2.layout.submit-form')
    <script>
        $('#charge-wallet-transaction').on('change', function () {
            var id = $('#charge-wallet-transaction').val();
            var price = $('#charge-wallet-transaction').find(':selected').data('price');
            $('#charge-wallet-price').val(price);
            var text = 'عودت وجه صورتحساب شماره ' + id + ' از شماره سفارش {{$Order->id}}'
            $('#charge-modal-detail').empty().val(text);
        });    </script>
@endsection

