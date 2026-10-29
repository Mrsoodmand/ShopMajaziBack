@extends('admin-v2.layout.master',['MetaTitle' => 'لیست سفارشات'])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">لیست سفارشات</span>
                        </h3>
                        <div class="card-toolbar my-1">
                            <!--begin::انتخاب-->
                            <div class="me-6 my-1">
                                <select id="kt_filter_year" name="year" data-control="select2" data-hide-search="true" class="w-125px form-select form-select-solid form-select-sm">
                                    <option value="همه" selected="selected">همه زمان</option>
                                    <option value="thisyear">امسال</option>
                                    <option value="thismonth">This month</option>
                                    <option value="lastmonth">اخر ماه</option>
                                    <option value="last90days">90 روز اخیر</option>
                                </select>
                            </div>
                            <!--end::انتخاب-->
                            <!--begin::انتخاب-->
                            <div class="me-4 my-1">
                                <select id="kt_filter_orders" name="orders" data-control="select2" data-hide-search="true" class="w-125px form-select form-select-solid form-select-sm">
                                    <option value="همه" selected="selected">همه سفارشات</option>
                                    <option value="تایید شده">تایید شده</option>
                                    <option value="رد شده">رد شده</option>
                                    <option value="درحال پردازش">درحال پردازش</option>
                                    <option value="در حمل و نقل">در حمل و نقل</option>
                                </select>
                            </div>
                            <!--end::انتخاب-->
                            <!--begin::جستجو-->
                            <div class="d-flex align-items-center position-relative my-1 me-6">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                <span class="svg-icon svg-icon-3 position-absolute ms-3">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
														<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor" />
													</svg>
												</span>
                                <!--end::Svg Icon-->
                                <input type="text" id="kt_filter_search" class="form-control form-control-solid form-select-sm w-150px ps-9" placeholder="جستجو " />
                            </div>
                            <!--end::جستجو-->
                            <a href="#" class="btn btn-bg-primary btn-active-color-primary btn-sm me-6 my-1">جست جو</a>
                        </div>

                    </div>
                    <div class="card-body py-3">
                        @include('admin-v2.comp.orders',['Orders' => $list])
                    </div>
                </div>
                <div class="mt-5">
                    {!! $list->links('admin-v2.layout.paginate') !!}
                </div>
            </div>
        </div>
    </div>
@endsection

