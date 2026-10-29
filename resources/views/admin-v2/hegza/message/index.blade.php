@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">پیام های ارسال شده</span>
                        </h3>

                        <div class="card-toolbar">
                            <form action="">
                                <div class="d-flex align-items-center position-relative my-1 ms-2">

                                    <input type="text" name="search"
                                           data-kt-ecommerce-product-filter="search"
                                           value="{{isset($_GET) && array_key_exists('search',$_GET) ? $_GET['search'] : ''}}"
                                           class="form-control form-control-solid w-250px ps-14"
                                           placeholder="جستجو سرویس">
                                    <button type="submit" class="btn btn-sm btn-primary ms-2">جستجو</button>

                                    <a href="#" class="btn btn-sm btn-primary ms-2">ایجاد</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table align-middle gs-0 gy-4">
                                <thead>
                                <tr class="fw-bolder text-muted bg-light">
                                    <th class="ps-4 rounded-start">عنوان متن</th>
                                    <th class="">وضعیت</th>
                                    <th class="">تاریخ ثبت</th>
                                    <th class="">تاریخ اتمام</th>
                                    <th class=" text-end rounded-end"> جرئیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($list as $user)
                                    <tr>
                                        <td>
                                            {{$user->title}}
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">
                                                {{$user->status}}
                                            </label>
                                        </td>
                                        <td>
                                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($user->created_at))}}</label>
                                        </td>
                                        <td>
                                            {{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($user->created_at))}}
                                        </td>
                                        <td class="text-end">
                                            <a href="{{route('admin.bot-messages-edit',$user->id)}}"
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

