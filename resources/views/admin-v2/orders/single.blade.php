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
                                            <td class="fw-bolder text-end">{{AdminOrderStatus($Order->status)}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-solid fa-gears" style="margin-left: 5px"></i>وضعیت
                                                    پرداخت
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{AdminOrderPayStatus($Order->pay_status)}}</td>
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
                                                    <a target="_blank"
                                                       href="{{route('admin.user single',$Order->User->id)}}"
                                                       class="text-gray-600 text-hover-primary">{{$Order->User->name}}</a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                     <span class="svg-icon svg-icon-2 me-2">
																 <i class="fa-light fa-phone me-2"></i>شماره موبایل
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">
                                                <a target="_blank"
                                                   href="{{route('admin.user single',$Order->User->id)}}"
                                                   class="text-gray-600 text-hover-primary">{{$Order->User->mobile}}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-light fa-envelope me-2"></i>
                                                    ایمیل
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{$Order->User->email}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-light fa-file-user me-2"></i>
                                                    پروفایل کاربر
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end"><a target="_blank"
                                                                              href="{{route('admin.user single',$Order->User->id)}}"
                                                                              class="text-gray-600 text-hover-primary">مشاهده</a>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card card-flush py-4 flex-row-fluid">
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">جزئیات تراکنش شماره </span>
                                </h3>
                                <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top"
                                     data-bs-trigger="hover" title="" data-bs-original-title="شماره تراکنش">
                                    <label class="btn btn-sm btn-light btn-active-primary" data-bs-toggle="modal"
                                           data-bs-target="#kt_modal_invite_friends">
                                        {{$Payment->id}}</label>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-bordered mb-0 fs-6 gy-5 min-w-300px">
                                        <tbody class="fw-bold text-gray-600">
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa-light fa-file-invoice me-2"></i>نحوه پرداخت
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">
                                                <label
                                                        class="text-gray-600 text-hover-primary">@if($Payment->gate == 'Wallet')
                                                        کیف پول
                                                    @else
                                                        درگاه بانک
                                                    @endif</label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center"><i
                                                            class="fa-light fa-ballot-check me-2"></i>مبلغ
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{number_format($Payment->price)}} تومان</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center"><i
                                                            class="fa-light fa-ballot-check me-2"></i>کد رهگیری
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end">{{!is_null($Payment->refid) ? $Payment->refid : '---'}}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">
                                                <div class="d-flex align-items-center"><i
                                                            class="fa-light fa-ballot-check me-2"></i>شماره کارت
                                                </div>
                                            </td>
                                            <td class="fw-bolder text-end"
                                                style="direction: ltr">{{!is_null($Payment->card) ? $Payment->card : '---'}}</td>
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
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#chats">گفت و گو</a>
                            </li>
                            @if($Order->type == 4)
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                       href="#appleId">اپل آیدی</a>
                                </li>
                            @endif
                        </ul>
                        <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="modal"
                                data-bs-target="#refundModal">عودت وجه به کیف پول
                        </button>
                        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal"
                                data-bs-target="#kt_modal_new_target">تغییر وضعیت
                        </button>
                        @if($Order->type == 2)
                            <button class="btn btn-success btn-sm send-order" type="button">تکمیل سفارش</button>
                        @endif
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="kt_ecommerce_sales_order_summary" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                @include('admin-v2.orders.page.'.__('order.PageType.'.$Order->type),[$Order,$OrderItems,$History,$Jdf,$OrderChats])
                            </div>
                        </div>
                        <div class="tab-pane fade" id="kt_ecommerce_sales_order_history" role="tab-panel">
                            <div class="d-flex flex-column gap-7 gap-lg-10">
                                <div class="card card-flush py-4 flex-row-fluid">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>تاریخچه سفارش</h2>
                                        </div>
                                    </div>
                                    <div id="kt_ecommerce_customer_addresses" class="card-body pt-0 pb-5">
                                        @if(count($History) != 0)
                                            @foreach($History as $history)
                                                @php
                                                    $detail = json_decode($history->detail ,true)
                                                @endphp
                                                <div class="py-0">
                                                    <div class="py-3 d-flex flex-stack flex-wrap">
                                                        <div class="d-flex align-items-center collapsible rotate collapsed"
                                                             data-bs-toggle="collapse"
                                                             href="#kt_ecommerce_customer_addresses_{{$history->id}}"
                                                             role="button"
                                                             aria-exped="false"
                                                             aria-controls="kt_customer_view_payment_method_{{$history->id}}"
                                                             aria-expanded="false">
                                                            <div class="me-3 rotate-90"><span
                                                                        class="svg-icon svg-icon-3"><svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="24" height="24" viewBox="0 0 24 24"
                                                                            fill="none"><path
                                                                                d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                                fill="currentColor"></path></svg></span>
                                                            </div>
                                                            <div class="me-3">
                                                                <div class="d-flex align-items-center">تغییر وضعیت
                                                                    از {{AdminOrderStatus($detail['old_status'])}}
                                                                    به {{AdminOrderStatus($detail['new_status'])}}
                                                                </div>
                                                                <div
                                                                        class="text-muted">{{$Jdf->jdate('Y-m-d , ساعت: H:i:s',strtotime($history->created_at))}}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="kt_ecommerce_customer_addresses_{{$history->id}}"
                                                         class="fs-6 ps-9 collapse"
                                                         data-bs-parent="#kt_ecommerce_customer_addresses" style="">
                                                        <div class="d-flex flex-column pb-5">
                                                            <div class="fw-bolder text-gray-600">توضیحات</div>
                                                            <div class="text-muted">
                                                                {{$detail['detail']}}
                                                                <hr>
                                                                توسط {{\App\Admin::find($history->admin_id)->full_name}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col-lg-12">
                                                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none">
															<path opacity="0.3"
                                                                  d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z"
                                                                  fill="currentColor"></path>
															<path
                                                                    d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z"
                                                                    fill="currentColor"></path>
														</svg>
													</span>
                                                    <div class="d-flex flex-stack flex-grow-1">
                                                        <div class="fw-bold">
                                                            <div class="fs-6 text-gray-700">تغییر برای این سفارش ثبت
                                                                نشده است.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
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
                                        @include('admin-v2.comp.payments',[$Payments])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="chats" role="tab-panel">
                            <div class="flex-lg-row-fluid">
                                <div class="card card-flush py-4 flex-row-fluid" id="kt_chat_messenger">
                                    <div class="card-body" id="kt_chat_messenger_body">
                                        <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto" data-kt-element="messages"
                                             data-kt-scroll="true"
                                             data-kt-scroll-activate="{default: false, lg: true}"
                                             data-kt-scroll-max-height="auto"
                                             data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                                             data-kt-scroll-wrappers="#kt_content, #kt_chat_messenger_body"
                                             data-kt-scroll-offset="5px"
                                             style="max-height: 191px;">
                                            @if(count($OrderChats) != 0)
                                                @foreach($OrderChats as $chats)
                                                    @if($chats->type == 'user')
                                                        <div class="d-flex justify-content-start mb-10">
                                                            <div class="d-flex flex-column align-items-start">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <div class="symbol symbol-35px symbol-circle">
                                                                        <img alt="Pic"
                                                                             src="{{secure_url('admin-v2')}}/assets/media/avatars/300-25.jpg">
                                                                    </div>
                                                                    <div class="ms-3">
                                                                        <label
                                                                                class="fs-5 fw-bolder text-gray-900 text-hover-primary me-1">{{$Order->User->id}}</label>
                                                                        <span
                                                                                class="text-muted fs-7 mb-1">{{$Jdf->jdate('Y-m-d , ساعت: H:i:s',strtotime($chats->created_at))}}</span>
                                                                    </div>
                                                                </div>
                                                                <div
                                                                        class="p-5 rounded bg-light-info text-dark fw-bold mw-lg-400px text-start"
                                                                        data-kt-element="message-text">{{$chats->text}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="d-flex justify-content-end mb-10">
                                                            <div class="d-flex flex-column align-items-end">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <div class="me-3">
                                                                        <span
                                                                                class="text-muted fs-7 mb-1">{{$Jdf->jdate('Y-m-d , ساعت: H:i:s',strtotime($chats->created_at))}}</span>
                                                                        <label
                                                                                class="fs-5 fw-bolder text-gray-900 text-hover-primary ms-1">شما</label>
                                                                    </div>
                                                                    <div class="symbol symbol-35px symbol-circle">
                                                                        <img alt="Pic"
                                                                             src="{{secure_url('/admin-v2')}}/assets/media/avatars/300-1.jpg">
                                                                    </div>
                                                                </div>
                                                                <div
                                                                        class="p-5 rounded bg-light-primary text-dark fw-bold mw-lg-400px text-end"
                                                                        data-kt-element="message-text">{{$chats->text}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <div class="d-flex justify-content-start mb-10">
                                                    <div class="d-flex flex-column align-items-start">

                                                        <div
                                                                class="p-5 rounded bg-light-info text-dark fw-bold mw-lg-400px text-start"
                                                                data-kt-element="message-text">گفتگوی برای این سفارش ثبت
                                                            نشده است.
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="d-flex justify-content-end mb-10 d-none"
                                                 data-kt-element="template-out">
                                                <div class="d-flex flex-column align-items-end">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="me-3">
                                                            <span class="text-muted fs-7 mb-1">فقط</span>
                                                            <a href="#"
                                                               class="fs-5 fw-bolder text-gray-900 text-hover-primary ms-1">شما</a>
                                                        </div>
                                                        <div class="symbol symbol-35px symbol-circle">
                                                            <img alt="Pic" src="assets/media/avatars/300-1.jpg">
                                                        </div>
                                                    </div>
                                                    <div
                                                            class="p-5 rounded bg-light-primary text-dark fw-bold mw-lg-400px text-end"
                                                            data-kt-element="message-text"></div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-start mb-10 d-none"
                                                 data-kt-element="template-in">
                                                <div class="d-flex flex-column align-items-start">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="symbol symbol-35px symbol-circle">
                                                            <img alt="Pic" src="assets/media/avatars/300-25.jpg">
                                                        </div>
                                                        <div class="ms-3">
                                                            <a href="#"
                                                               class="fs-5 fw-bolder text-gray-900 text-hover-primary me-1">رضا
                                                                علی
                                                                ابادی</a>
                                                            <span class="text-muted fs-7 mb-1">فقط</span>
                                                        </div>
                                                    </div>
                                                    <div
                                                            class="p-5 rounded bg-light-info text-dark fw-bold mw-lg-400px text-start"
                                                            data-kt-element="message-text">Right before vacation season
                                                        we
                                                        have the next
                                                        Bigمعامله for you.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                                        <form action="{{route('admin.send order chat')}}" method="post">
                                            @csrf
                                            <textarea class="form-control form-control-flush mb-3" rows="1"
                                                      data-kt-element="input"
                                                      placeholder="نوشتن پیام" name="message"></textarea>
                                            <input type="hidden" name="order_id"
                                                   value="{{$Order->id}}">
                                            <div class="d-flex flex-stack">
                                                <div class="d-flex align-items-center me-2">
                                                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1"
                                                            type="button"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="بزودی">
                                                        <i class="bi bi-paperclip fs-3"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-icon btn-active-light-primary me-1"
                                                            type="button"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="بزودی">
                                                        <i class="bi bi-upload fs-3"></i>
                                                    </button>
                                                </div>
                                                <button class="btn btn-primary" type="submit" data-kt-element="send">
                                                    ارسال
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($Order->type == 4)
                            <div class="tab-pane fade" id="appleId" role="tab-panel">
                                <div class="flex-lg-row-fluid">
                                    <div class="card card-flush py-4 flex-row-fluid" id="kt_chat_messenger">
                                        <form action="{{route('admin.submit-apple-id')}}" method="post">
                                            <div class="card-body" id="kt_chat_messenger_body">
                                                <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto"
                                                     data-kt-element="messages"
                                                     data-kt-scroll="true"
                                                     data-kt-scroll-activate="{default: false, lg: true}"
                                                     data-kt-scroll-max-height="auto"
                                                     data-kt-scroll-dependencies="#kt_header, #kt_toolbar, #kt_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                                                     data-kt-scroll-wrappers="#kt_content, #kt_chat_messenger_body"
                                                     data-kt-scroll-offset="5px"
                                                     style="max-height: 191px;">

                                                    <div class="row">
                                                        <div class="col-4">
                                                            <label class="required form-label">ایمیل:</label>
                                                            <input type="text" name="appleid" class="form-control mb-2"
                                                                   placeholder="" value="">
                                                            <input type="hidden" name="order_id"
                                                                   class="form-control mb-2"
                                                                   placeholder="" value="{{$Order->id}}">
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="required form-label">تاریخ تولد:</label>
                                                            <input type="text" name="brithday" class="form-control mb-2"
                                                                   placeholder="" value="">
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="required form-label">کلمه عبور:</label>
                                                            <input type="text" name="password" class="form-control mb-2"
                                                                   placeholder="" value="">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label class="required form-label">سوال امنیتی 1:</label>
                                                            <input type="text" name="q1" class="form-control mb-2"
                                                                   placeholder="" value="">

                                                        </div>
                                                        <div class="col-12">
                                                            <label class="required form-label">سوال امنیتی 2:</label>
                                                            <input type="text" name="q2" class="form-control mb-2"
                                                                   placeholder="" value="">
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="required form-label">سوال امنیتی 3:</label>
                                                            <input type="text" name="q3" class="form-control mb-2"
                                                                   placeholder="" value="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer pt-4" id="kt_chat_messenger_footer"
                                                 style="float: left">
                                                @csrf
                                                <button class="btn btn-primary" type="submit"
                                                        data-kt-element="send">
                                                    ثبت اپل آیدی
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php $status = __('order.status'); @endphp
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
                    <form id="CurrentForm" class="form" action="{{route('admin.change order status')}}"
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
    $(document).on('click','.send-order',function(){

    });
    $('#charge-wallet-transaction').on('change', function () {
        var id = $('#charge-wallet-transaction').val();
        var price = $('#charge-wallet-transaction').find(':selected').data('price');
        $('#charge-wallet-price').val(price);
        var text = 'عودت وجه صورتحساب شماره ' + id + ' از شماره سفارش {{$Order->id}}'
            $('#charge-modal-detail').empty().val(text);
        });
</script>
@endsection

