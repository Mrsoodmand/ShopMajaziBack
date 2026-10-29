@extends('admin-bot.layout.master',['metaTitle' => "پروفایل کاربر : $user->name"])

@section('header')

@endsection


@section('content')

    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">

        <div class="toolbar d-flex flex-stack mb-3 mb-lg-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack flex-wrap">
                <div class="page-title d-flex flex-column me-5 py-2">
                    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">جزئیات کاربر</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
                        <li class="breadcrumb-item text-muted">
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted"><a href="{{route('admin.bot-user-list')}}"
                                                                  class="text-muted text-hover-primary">لیست کاربران</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-900">جزئیات کاربر</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xxl-8">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <label class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{$user->name}}</label>
                                            <a href="#">
                                                <i class="ki-duotone ki-verify fs-1 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                            <a href="#"
                                               class="d-flex align-items-center text-gray-500 text-hover-primary mb-2">
                                                <i class="ki-duotone ki-sms fs-4 me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>آیدی تلگرام: {{$user->username}}</a>
                                            <a href="#"
                                               class="d-flex align-items-center text-gray-500 text-hover-primary mb-2 me-5 ms-5">
                                                <i class="ki-duotone ki-sms fs-4 me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>شناسه تلگرام: {{$user->user_id}}</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap flex-stack">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap">
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="fs-2 fw-bold" data-kt-countup="true"
                                                         data-kt-countup-value="{{$OrdersCount}}">{{$OrdersCount}} عدد
                                                    </div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-500">سفارشات</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="fs-2 fw-bold" data-kt-countup="true"
                                                         data-kt-countup-value="{{number_format($user->wallet)}}">{{number_format($user->wallet)}}
                                                        تومان
                                                    </div>
                                                </div>
                                                <div class="fw-semibold fs-6 text-gray-500">موجودی کیف پول</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="separator"></div>

                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5 active" data-bs-toggle="tab"
                                   href="#kt_tab_pane_1">سفارشات</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_2">تراکنشات</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_3">زیر مجموعه ها</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_4">عملیات حساب</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab"
                                   href="#kt_tab_pane_5">عملیات کیف پول</a>
                            </li>
                        </ul>

                    </div>
                </div>
               <div class="row g-5 g-xxl-8 mt-10">
                   <div class="tab-content " id="myTabContent">
                       <div class="tab-pane fade show active" id="kt_tab_pane_1" role="tabpanel">
                           <div class="card g-5 g-xxl-8">
                               <div class="card-header border-0 pt-5">
                                   <h3 class="card-title align-items-start flex-column">
                                       <span class="card-label fw-bold fs-3 mb-1">سفارشات کاربر</span>
                                   </h3>
                               </div>
                               <div class="card-body">
                                   @include('admin-bot.comp.orders',['list' => $Orders])
                               </div>
                           </div>
                       </div>
                       <div class="tab-pane fade" id="kt_tab_pane_2" role="tabpanel">
                           <div class="card g-5 g-xxl-8">
                               <div class="card-header border-0 pt-5">
                                   <h3 class="card-title align-items-start flex-column">
                                       <span class="card-label fw-bold fs-3 mb-1">تراکنش کاربر</span>
                                   </h3>
                               </div>
                               <div class="card-body">
                                   @include('admin-bot.comp.transaction',['list' => $Payments])
                               </div>
                           </div>
                       </div>
                       <div class="tab-pane fade" id="kt_tab_pane_3" role="tabpanel">
                           <div class="card g-5 g-xxl-8">
                               <div class="card-header border-0 pt-5">
                                   <h3 class="card-title align-items-start flex-column">
                                       <span class="card-label fw-bold fs-3 mb-1">زیر مجموعه ها</span>
                                   </h3>
                               </div>
                               <div class="card-body">
                                   @include('admin-bot.comp.users',['list' => $Users])
                               </div>
                           </div>                       </div>
                       <div class="tab-pane fade" id="kt_tab_pane_4" role="tabpanel">
                           <div class="card g-5 g-xxl-8">
                               <div class="card-header border-0 pt-5">
                                   <h3 class="card-title align-items-start flex-column">
                                       <span class="card-label fw-bold fs-3 mb-1">عملیات حساب</span>
                                   </h3>
                               </div>
                               <div class="card-body">
                                  <div class="row">
                                      <div class="col-lg-3">
                                          <label class="required form-label">نام حساب</label>
                                          <input type="text" name="username" class="form-control mb-2" placeholder="آلمان" value="">
                                          <div id="name-error" style="display: none" class="text-danger fs-7 mt-3 errors"></div>
                                      </div>
                                      <div class="col-lg-3">
                                          <label class="required form-label">نام کاربری</label>
                                          <input type="text" name="username" class="form-control mb-2" placeholder="آلمان" value="">
                                          <div id="name-error" style="display: none" class="text-danger fs-7 mt-3 errors"></div>
                                      </div>
                                      <div class="col-lg-3">
                                          <label class="required form-label">وضعیت</label>
                                         <select class="form-select">
                                             <option value="0">غیرفعال</option>
                                             <option value="1">فعال</option>
                                         </select>
                                      </div>
                                      <div class="col-lg-3">
                                          <label class="required form-label">ثبت معرف</label>
                                          <input type="text" name="username" class="form-control mb-2" placeholder="آلمان" value="">
                                      </div>
                                  </div>
                               </div>
                           </div>
                       </div>
                       <div class="tab-pane fade" id="kt_tab_pane_5" role="tabpanel">
                           <div class="card g-5 g-xxl-8">
                               <div class="card-header border-0 pt-5">
                                   <h3 class="card-title align-items-start flex-column">
                                       <span class="card-label fw-bold fs-3 mb-1">عملیات کیف پول</span>
                                   </h3>
                               </div>
                               <div class="card-body">
                                   <div class="row">
                                       <div class="col-lg-3">
                                           <label class="required form-label">مقدار</label>
                                           <input type="text" name="username" class="form-control mb-2" placeholder="آلمان" value="">
                                           <div id="name-error" style="display: none" class="text-danger fs-7 mt-3 errors"></div>
                                       </div>
                                       <div class="col-lg-3">
                                           <label class="required form-label">نوع عملیات</label>
                                           <select class="form-select">
                                               <option value="0">غیرفعال</option>
                                               <option value="1">فعال</option>
                                           </select>
                                       </div>
                                       <div class="col-lg-3">
                                           <label class="required form-label">توضیحات</label>
                                           <input type="text" name="username" class="form-control mb-2" placeholder="آلمان" value="">
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


@section('footer')
    <script>
        $(document).on('click', '.check-payment', function () {
            var id = $(this).data('id');
            $(this).empty().text('لطفا صبر کنید...');

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
    </script>

@endsection
