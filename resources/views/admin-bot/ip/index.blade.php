@php $title = 'لیست کشور ها'; @endphp
@extends('admin-bot.layout.master',['metaTitle' => $title])
@section('header')

@endsection


@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="toolbar d-flex flex-stack mb-3 mb-lg-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack flex-wrap">
                <div class="page-title d-flex flex-column me-5 py-2">
                    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">{{$title}}</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.html" class="text-muted text-hover-primary">خانه</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-900">{{$title}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card ms-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{$title}}</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-row-bordered  table-hover table-row-gray-100 align-middle gs-0 gy-3">
                                <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px text-center">نام کشور</th>
                                    <th class="min-w-140px text-center">تعداد سرور</th>
                                    <th class="min-w-120px text-center">وضعیت</th>
                                    <th class="min-w-100px text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($list) != 0)
                                    @foreach($list as $item)
                                        <tr>
                                            <td class="text-center">
                                                <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$item->name}}</label>
                                            </td>
                                            <td class="text-center">
1
                                            </td>
                                            <td class="text-center">
                                                @if($item->status == 1)
                                                <span class="badge badge-light-success">فعال</span>
                                                    @else
                                                <span class="badge badge-light-danger">غیرفعال</span>
                                                    @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{route('admin.bot-server-edit',[$item->id])}}"
                                                   class="btn btn-bg-light btn-active-color-primary btn-sm ">
                                                    مشاهده جزئیات
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            کاربری یافت نشد
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
@endsection


@section('footer')

@endsection
