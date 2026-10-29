@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-xl-row p-7">
                            <div class="flex-lg-row-fluid me-xl-15 mb-20 mb-xl-0">
                                @if(\Illuminate\Support\Facades\Session::has('error'))
                                    @foreach(\Illuminate\Support\Facades\Session::get('error') as $error)
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                                                 fill="none"
                                                 stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                                                <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                                                </polygon>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            {{$error}}
                                        </div>
                                    @endforeach
                                @endif
                                @if(\Illuminate\Support\Facades\Session::has('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                                             stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                                            </polygon>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        {{\Illuminate\Support\Facades\Session::get('success')}}
                                    </div>
                                @endif
                                <div class="mb-0">
                                    <div class="d-flex align-items-center mb-12">
                                        <span class="svg-icon svg-icon-4qx svg-icon-success ms-n2 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path opacity="0.3"
                                                                      d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM11.7 17.7L16 14C16.4 13.6 16.4 12.9 16 12.5C15.6 12.1 15.4 12.6 15 13L11 16L9 15C8.6 14.6 8.4 14.1 8 14.5C7.6 14.9 8.1 15.6 8.5 16L10.3 17.7C10.5 17.9 10.8 18 11 18C11.2 18 11.5 17.9 11.7 17.7Z"
                                                                      fill="currentColor"/>
																<path d="M10.4343 15.4343L9.25 14.25C8.83579 13.8358 8.16421 13.8358 7.75 14.25C7.33579 14.6642 7.33579 15.3358 7.75 15.75L10.2929 18.2929C10.6834 18.6834 11.3166 18.6834 11.7071 18.2929L16.25 13.75C16.6642 13.3358 16.6642 12.6642 16.25 12.25C15.8358 11.8358 15.1642 11.8358 14.75 12.25L11.5657 15.4343C11.2533 15.7467 10.7467 15.7467 10.4343 15.4343Z"
                                                                      fill="currentColor"/>
																<path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                        <div class="d-flex flex-column">
                                            <h1 class="text-gray-800 fw-bold">{{$Ticket->subject}}</h1>
                                            <div class="">
                                                <span class="fw-bold text-muted me-6">نام کاربر: <a href="#"
                                                                                                    class="text-muted text-hover-primary">{{$Ticket->User->name}}</a></span>
                                                <span class="fw-bold text-muted me-6">توسط: <a href="#"
                                                                                               class="text-muted text-hover-primary">{{$Ticket->User->mobile}} </a></span>
                                                <span class="fw-bold text-muted">تاریخ ایجاد: <span
                                                            class="fw-bolder text-gray-600 me-1">{{$jdf->jdate('d F Y ساعت:  H:i',strtotime($Ticket->created_at))}}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-15">
                                        <form action="{{route('admin.send ticket')}}" method="post" id="CurrentForm">
                                            <div class="mb-0">
                                                @csrf
                                            <textarea
                                                    class="form-control form-control-solid placeholder-gray-600 fw-bolder fs-4 ps-9 pt-7"
                                                    rows="6" name="message" placeholder="پاسخ به تیکت"></textarea>
                                                <input type="hidden" name="ticket_id" value="{{Encode($Ticket->id)}}">
                                                <button type="submit" id="submit_btn"
                                                        class="btn btn-primary mt-n20 mb-20 position-relative float-end me-7">
                                                    ارسال
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    @foreach($TicketDetails as $item)
                                    <div class="mb-15">
                                        @if($item->type != 'admin')
                                                <div class="mb-9">
                                                    <div class="card card-bordered w-100">
                                                        <div class="card-body">
                                                            <div class="w-100 d-flex flex-stack mb-8">
                                                                <div class="d-flex align-items-center f">
                                                                    <div class="symbol symbol-50px me-5">
                                                                        <div class="symbol-label fs-1 fw-bolder bg-light-success text-success">
                                                                            کاربر
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex flex-column fw-bold fs-5 text-gray-600 text-dark">
                                                                        <div class="d-flex align-items-center">
                                                                            <a href="{{route('admin.user single',$Ticket->User->id)}}"
                                                                               class="text-gray-800 fw-bolder text-hover-primary fs-5 me-3">{{$Ticket->User->name}}</a>
                                                                            <span class="m-0"></span>
                                                                        </div>
                                                                        <span class="text-muted fw-bold fs-6">{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($item->created_at))}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <p class="fw-normal fs-5 text-gray-700 m-0">{{$item->detail}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="overflow-hidden position-relative card-rounded">
                                                    <div class="ribbon ribbon-triangle ribbon-top-start border-success">
                                                        <div class="ribbon-icon mt-n5 ms-n6">
                                                            <i class="bi bi-check2 fs-2 text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="card card-bordered w-100">
                                                        <div class="card-body">
                                                            <div class="w-100 d-flex flex-stack mb-8">
                                                                <div class="d-flex align-items-center f">
                                                                    <div class="symbol symbol-50px me-5">
                                                                        <div class="symbol-label fs-1 fw-bolder bg-light-primary text-primary">
                                                                            مدیر
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex flex-column fw-bold fs-5 text-gray-600 text-dark">
                                                                        <div class="d-flex align-items-center">
                                                                            <a href="javascript:;"
                                                                               class="text-gray-800 fw-bolder text-hover-primary fs-5 me-3">مدیر</a>
                                                                            <span class="m-0"></span>
                                                                        </div>
                                                                        <span class="text-muted fw-bold fs-6">{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($item->created_at))}}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <p class="fw-normal fs-5 text-gray-700 m-0">{{$item->detail}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex-column flex-lg-row-auto w-100 mw-lg-300px mw-xxl-350px">
                              <aside style="position: sticky;top: 6rem;">
                                  <div class="card-rounded bg-primary bg-opacity-5 p-10 mb-15">
                                      <h2 class="text-dark fw-bolder mb-11">مشخصات تیکت</h2>
                                      <div class="d-flex align-items-center mb-10">
                                          <i class="bi bi-file-earmark-text text-primary fs-1 me-5"></i>
                                          <div class="d-flex flex-column">
                                              <h5 class="text-gray-800 fw-bolder">عنوان</h5>
                                              <div class="fw-bold">
                                                  <span class="text-muted">{{$Ticket->subject}}</span>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="d-flex align-items-center mb-10">
                                          <i class="bi bi-chat-square-text-fill text-primary fs-1 me-5"></i>
                                          <div class="d-flex flex-column">
                                              <h5 class="text-gray-800 fw-bolder">وضعیت</h5>
                                              <div class="fw-bold">
                                                  <span class="text-muted">{{TicketStatus($Ticket->status)}} </span>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="d-flex align-items-center mb-10">
                                          <i class="bi bi-twitter text-primary fs-1 me-5"></i>
                                          <div class="d-flex flex-column">
                                              <h5 class="text-gray-800 fw-bolder">تاریخ آخرین پاسخ</h5>
                                              <div class="fw-bold">
                                                  <span class="text-muted">{{$jdf->jdate('d F Y ساعت:  H:i',strtotime($Ticket->updated_at))}}</span>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="card-rounded bg-primary bg-opacity-5 p-10 mb-15">
                                      <!--begin::Title-->
                                      <h1 class="fw-bolder text-dark mb-9">اسناد</h1>
                                      <!--end::Title-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">ادمین
                                              آنگولار</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">پنل ادمین
                                              ریکت</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">داشبورد وی
                                              یو ای</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">تم
                                              طاقچه</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">ادمین
                                              داشبورد</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">فرانت
                                              اند</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">ادمین
                                              سیستم مدیریت محتوا </a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center mb-6">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">ادمین
                                              داشبورد</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                      <!--begin::Item-->
                                      <div class="d-flex align-items-center">
                                          <!--begin::Icon-->
                                          <!--begin::Svg Icon | path: icons/duotune/arrows/arr071.svg-->
                                          <span class="svg-icon svg-icon-2 ms-n1 me-3">
															<svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                 height="24" viewBox="0 0 24 24" fill="none">
																<path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                      fill="currentColor"/>
															</svg>
														</span>
                                          <!--end::Svg Icon-->
                                          <!--end::Icon-->
                                          <!--begin::Subtitle-->
                                          <a href="#" class="fw-bold text-gray-800 text-hover-primary fs-5 m-0">ادمین
                                              اینترانت</a>
                                          <!--end::Subtitle-->
                                      </div>
                                      <!--end::Item-->
                                  </div>
                              </aside>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer')
    @include('admin-v2.layout.submit-form')
@endsection

