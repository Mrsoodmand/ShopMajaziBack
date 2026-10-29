@extends('admin-v2.layout.master',['MetaTitle' => "پنل مدیریت وب سایت شاپ مجازی"])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="row gy-5 g-xl-8">
                    <div class="col-xl-12">
                        @hasanyrole('super-admin')
                        <div class="row gy-5 g-xl-10">
                            <div class="col-sm-6 col-xl-3 mb-xl-10">
                                <div class="card">
                                    <div class="card-body d-flex align-items-start flex-column">
                                        <div class="m-0">
                                            <i class="fa-light fa-users"></i>
                                            <span class="badge badge-success fs-base" style="position: absolute;left: 10px">{{$NewUser}}</span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="m-0">
                                                <span class="fw-bold fs-4 text-gray-400">کلیه کاربران</span>
                                            </div>
                                            <span class="fw-bold fs-3x text-gray-800 lh-1 ls-n2 mt-10">{{$User}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-xl-10">
                                <div class="card">
                                    <div class="card-body d-flex align-items-start flex-column">
                                        <div class="m-0">
                                            <i class="fa-regular fa-basket-shopping-simple"></i>
                                            <span class="badge badge-success fs-base" style="position:absolute;left: 10px">{{$TodayCompleteOrders}}</span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="m-0">
                                                <span class="fw-bold fs-6 text-gray-400">کلیه سفارشات</span>
                                            </div>
                                            <span class="fw-bold fs-3x text-gray-800 lh-1 ls-n2 mt-10">{{$CompleteOrders}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-xl-10">
                                <div class="card">
                                    <div class="card-body d-flex align-items-start flex-column">
                                        <div class="m-0">
                                            <i class="fa-duotone fa-messages"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="m-0">
                                                <span class="fw-bold fs-6 text-gray-400">تیکت های جدید</span>
                                            </div>
                                            <span class="fw-bold fs-3x text-gray-800 lh-1 ls-n2 mt-10">{{count($Ticket)}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3 mb-xl-10">
                                <div class="card">
                                    <div class="card-body d-flex align-items-start flex-column">
                                        <div class="m-0">
                                            <i class="fa-light fa-binoculars"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <div class="m-0">
                                                <span class="fw-bold fs-6 text-gray-400">مغایرت</span>
                                            </div>
                                            <span class="fw-bold fs-3x text-gray-800 lh-1 ls-n2 mt-10">{{count($Moghayerat)}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        @hasanyrole('support|super-admin')
                        <div class="card mb-5 mb-xl-12">
                            <div class="d-flex flex-column flex-lg-row">
                                <div class="flex-lg-row-fluid ms-lg-10 me-lg-10 mt-5">
                                    <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_user_view_overview_tab">سفارشات</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-kt-countup-tabs="true" data-bs-toggle="tab" href="#kt_user_view_overview_security">تیکت ها</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#payments">مغایرت</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#comments">کامنت ها</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="kt_user_view_overview_tab" role="tabpanel">
                                            <div class="card card-flush mb-xl-9">
                                                <div class="card-header">
                                                    <div class="card-title flex-column">
                                                        <h2 class="mb-1">سفارشات</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <a href="{{secure_url('admin/orders')}}" target="_blank" class="btn btn-light-primary btn-sm">لیست سفارشات</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-flush mb-6 mb-xl-9">
                                             @include('admin-v2.comp.orders',['Orders' => $PendingOrders])
                                            </div>
                                        </div>
                                        <div class="tab-pane fade show" id="kt_user_view_overview_security" role="tabpanel">
                                            <div class="card card-flush mb-xl-9">
                                                <div class="card-header">
                                                    <div class="card-title flex-column">
                                                        <h2 class="mb-1">تیکت ها</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <a href="{{secure_url('admin/tickets')}}" target="_blank" class="btn btn-light-primary btn-sm">لیست تیکت ها</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-flush mb-6 mb-xl-9">
                                             @include('admin-v2.comp.tickets',['Tickets' => $Ticket])
                                            </div>
                                        </div>
                                        <div class="tab-pane fade show" id="payments" role="tabpanel">
                                            <div class="card card-flush mb-xl-9">
                                                <div class="card-header">
                                                    <div class="card-title flex-column">
                                                        <h2 class="mb-1">مغایرت ها</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <button type="button" class="btn btn-light-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_add_schedule">افزودن برنامه</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-flush mb-6 mb-xl-9">
                                             @include('admin-v2.comp.payments',['Payments' => $Moghayerat])
                                            </div>
                                        </div>
                                        <div class="tab-pane fade show" id="comments" role="tabpanel">
                                            <div class="card card-flush mb-xl-9">
                                                <div class="card-header">
                                                    <div class="card-title flex-column">
                                                        <h2 class="mb-1">کامنت ها</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <a href="{{secure_url('admin/comment')}}" target="_blank" class="btn btn-light-primary btn-sm">لیست کامنت ها</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card card-flush mb-6 mb-xl-9">
                                                @include('admin-v2.comp.comments',['List' => $comments])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        @endhasanyrole
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('header')
@endsection

@section('footer')
    <script src="{{secure_url('admin-v2/assets/plugins/custom/fullcalendar/fullcalendar.bundle.js')}}"></script>
    <script src="{{secure_url('admin-v2/assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>

@endsection
