@extends('admin-bot.layout.master',['metaTitle' => 'لیست کاربران'])
@php $title = 'لیست کاربران'; @endphp
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
                        <form action="{{\Illuminate\Support\Facades\URL::current()}}" method="get">
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <input value="{{isset($_GET['search']) ? $_GET['search'] : ''}}" name="search" type="text" data-kt-ecommerce-product-filter="search"
                                           class="form-control form-control-solid w-250px ps-12"
                                           placeholder="نام کاربری">
                                </div>
                                <div class="d-flex align-items-center position-relative my-1">
                                    <input value="{{isset($_GET['tel_id']) ? $_GET['tel_id'] : ''}}" name="tel_id" type="text"
                                           class="form-control form-control-solid w-250px ps-12"
                                           placeholder="آیدی عددی تلگرام">
                                </div>
                                <button type="submit" class="btn btn-sm btn-success"> جستجو</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body py-3">
                        @include('admin-bot.comp.users')
                    </div>
                    <div class="card-footer">
                        {{$list->render('admin-bot.layout.paginate')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('footer')

@endsection
