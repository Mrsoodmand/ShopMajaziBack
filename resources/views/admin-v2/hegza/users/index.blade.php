@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">جدید ورودها</span>
                        </h3>
                        <form action="">
                            <div class="card-toolbar">
                                <div class="d-flex align-items-center position-relative my-1 ms-2">
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                         viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                              height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                              fill="currentColor"></rect>
														<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                              fill="currentColor"></path>
													</svg>
												</span>
                                    <input type="text" name="search" data-kt-ecommerce-product-filter="search"
                                           value="{{isset($_GET) && array_key_exists('search',$_GET) ? $_GET['search'] : ''}}"
                                           class="form-control form-control-solid w-250px ps-14"
                                           placeholder="جستجو سرویس">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary ms-2">جستجو</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table align-middle gs-0 gy-4">
                                <thead>
                                <tr class="fw-bolder text-muted bg-light">
                                    <th class="ps-4 rounded-start">نام کاربری</th>
                                    <th class="">شناسه تلگرام</th>
                                    <th class="">کاربران معرفی شده</th>
                                    <th class="">موجودی</th>
                                    <th class="">سرویس تست</th>
                                    <th class="">معرف</th>
                                    <th class="">از طرف</th>
                                    <th class="">سرویس تست</th>
                                    <th class="">تاریخ ثبت نام</th>
                                    <th class=" text-end rounded-end"> جرئیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($list as $user)
                                    <tr>
                                        <td>
                                            <a href="{{route('admin.bot-single-user',$user->id)}}"
                                               class="text-dark fw-bolder text-hover-primary mb-1 fs-6">{{$user->username}}</a>
                                        </td>
                                        <td>
                                            <a href="{{route('admin.bot-single-user',$user->id)}}"
                                               class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$user->user_id}}</a>
                                        </td>
                                        <td>
                                            <a href="{{route('admin.bot-single-user',$user->id)}}"
                                               class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{count($user->referrals)}} نفر </a>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{number_format($user->wallet)}}</label>
                                            <span class="text-muted fw-bold text-muted d-block fs-7">تومان</span>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-{{(!is_null($user->test) ? 'success' :'danger')}} d-block mb-1 fs-6">{{(!is_null($user->test) ? 'دارد' :'ندارد')}}</label>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$user->parent}}</label>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$user->from}}</label>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($user->created_at))}}</label>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{route('admin.bot-single-user',$user->id)}}"
                                               class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                <i class="fa-solid fa-user-pen"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    {!! $list->links('admin-v2.layout.paginate') !!}
                </div>
            </div>
        </div>
    </div>
@endsection

