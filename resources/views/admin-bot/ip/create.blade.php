@php $title = isset($edit) ? 'ویرایش سرور' :'ایجاد سرور'; @endphp
@extends('admin-bot.layout.master',['metaTitle' => $title])
@section('header')

@endsection


@section('content')
    @if(\Illuminate\Support\Facades\Session::has('error'))
        @foreach(\Illuminate\Support\Facades\Session::get('error') as $item)
            {{$item}}
        @endforeach
    @endif
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
                        <li class="breadcrumb-item text-gray-900">لیست سرور ها</li>
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
                <form action="{{isset($edit) ? route('admin.bot-server-update') : route('admin.bot-server-store')}}"
                      enctype="multipart/form-data" method="post">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2"
                                role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all"
                                       aria-selected="true" role="tab">عمومی</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#price"
                                       aria-selected="true" role="tab">تعرفه ها</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#server"
                                       aria-selected="true" role="tab">سرور ها</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-9">

                            <div class="tab-content mt-8">
                                <div class="tab-pane show active" id="all" role="tab-panel">
                                    <div class="row">
                                        <div class="d-flex flex-column gap-7 gap-lg-10">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <h2>عمومی</h2>
                                                    </div>
                                                </div>
                                                <div class="card-body mt-2 pt-0">
                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <label class="required form-label">نام کشور</label>
                                                            <input type="text" name="name" class="form-control mb-2"
                                                                   value="{{isset($edit) ? $edit->name : ''}}">
                                                            <input type="hidden" name="id" class="form-control mb-2"
                                                                   value="{{isset($edit) ? $edit->id : ''}}">
                                                            <input type="hidden" value="pu-ip-sabet" name="type">
                                                            <div id="name-error" style="display: none"
                                                                 class="text-danger fs-7 mt-3 errors"></div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <label class="required form-label">نام کشور
                                                                <sup>انگلیسی</sup></label>
                                                            <input type="text" name="enName" class="form-control mb-2"
                                                                   placeholder="آلمان"
                                                                   value="{{isset($edit) ? $edit->enName : ''}}">
                                                            <div id="name-error" style="display: none"
                                                                 class="text-danger fs-7 mt-3 errors"></div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <label class="required form-label">وضعیت</label>
                                                            <select name="status" id="" class="form-select mb-2">
                                                                <option {{isset($edit) &&  $edit->status == 1 ? 'selected' : ''}} value="1">
                                                                    فعال
                                                                </option>
                                                                <option {{isset($edit) &&  $edit->status == 0 ? 'selected' : ''}} value="0">
                                                                    غیرفعال
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="price" role="tab-panel">
                                    <div class="row">
                                        <div class="d-flex flex-column gap-7 gap-lg-10">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <h2>تعرفه ها</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <button type="button"
                                                                class="btn btn-sm btn-success my-1 me-3 add-product">
                                                            افزودن
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body mt-2 pt-0" id="price-area">
                                                    @if(isset($edit))
                                                        @foreach($Plans as $item)
                                                            @php
                                                                $detail = json_decode($item->detail,true);
                                                            @endphp
                                                            <div class="row">
                                                                <div class="col-lg-3">
                                                                    <label class="required form-label">نام</label>
                                                                    <input type="text" name="productName[]"
                                                                           class="form-control mb-2"
                                                                           value="{{$item->faName}}">
                                                                </div>
                                                                <div class="col-lg-3">
                                                                    <label class="required form-label">قیمت</label>
                                                                    <input type="text" value="{{$item->price}}"
                                                                           name="productPrice[]"
                                                                           class="form-control mb-2">
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <label class="required form-label">مدت زمان</label>
                                                                    <input type="text" value="{{$detail['day']}}"
                                                                           name="productDays[]"
                                                                           class="form-control mb-2">
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <label class="required form-label">حجم</label>
                                                                    <input type="text" value="{{$detail['volume']}}"
                                                                           name="productVolume[]"
                                                                           class="form-control mb-2">
                                                                </div>
                                                                <div class="col-lg-2">
                                                                    <br>
                                                                    <button type="button"
                                                                            class="btn btn-md btn-danger mt-2"
                                                                            style="width: 100%"
                                                                            onclick="$(this).parent().parent().remove()">
                                                                        حذف
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="row">
                                                            <div class="col-lg-3">
                                                                <label class="required form-label">نام</label>
                                                                <input type="text" name="productName[]"
                                                                       class="form-control mb-2"
                                                                       placeholder="سرویس یک ماهه" value="">
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label class="required form-label">قیمت</label>
                                                                <input type="text" name="productPrice[]"
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <label class="required form-label">مدت زمان</label>
                                                                <input type="text" name="productDays[]"
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <label class="required form-label">حجم</label>
                                                                <input type="text" name="productHajm[]"
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div class="col-lg-2">
                                                                <br>
                                                                <button type="button" class="btn btn-md btn-danger mt-2"
                                                                        style="width: 100%"
                                                                        onclick="$(this).parent().parent().remove()">
                                                                    حذف
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="server" role="tab-panel">
                                    <div class="row">
                                        <div class="d-flex flex-column gap-7 gap-lg-10">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <h2>سرور ها</h2>
                                                    </div>
                                                    <div class="card-toolbar">
                                                        <button type="button"
                                                                class="btn btn-sm btn-success my-1 me-3 add-server">
                                                            افزودن
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body mt-2 pt-0">
                                                    <div class="row" id="server-area">
                                                        @if(isset($edit))
                                                            @foreach($Ip as $item)
                                                                @php $detail = json_decode($item->detail,true); @endphp
                                                                <div class="col-lg-12">
                                                                    <div class="m-0">
                                                                        <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                                             data-bs-toggle="collapse"
                                                                             data-bs-target="#kt_job_5_1"
                                                                             aria-expanded="false">
                                                                            <!--begin::Icon-->
                                                                            <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                                <i class="ki-duotone ki-minus-square toggle-on text-primary fs-1">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                                <i class="ki-duotone ki-plus-square toggle-off fs-1">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                </i>
                                                                            </div>
                                                                            <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                                {{$detail['ip_name']}}</h4>

                                                                        </div>

                                                                        <div id="kt_job_5_1" class="fs-6 ms-1 collapse"
                                                                             style="">
                                                                            <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                                                                <div class="row">
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">نام
                                                                                            سرور</label>
                                                                                        <input type="text"
                                                                                               name="serverName[]"
                                                                                               class="form-control mb-2"
                                                                                               placeholder="سرویس یک ماهه"
                                                                                               value="{{$detail['ip_name']}}">
                                                                                        <input type="hidden"
                                                                                               name="serverId[]"
                                                                                               class="form-control mb-2"
                                                                                               value="{{$item->id}}">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">آی
                                                                                            پی
                                                                                            سرور</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_domain']}}"
                                                                                               name="serverIp[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">دامنه
                                                                                            سرور</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_address']}}"
                                                                                               name="serverDomain[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">پورت
                                                                                            سرور</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_port']}}"
                                                                                               name="serverPort[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">آیدی
                                                                                            inbound</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_inbound']}}"
                                                                                               name="inboundId[]"
                                                                                               class="form-control mb-2"
                                                                                        >
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">پورت
                                                                                            inbound</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['port_inbound']}}"
                                                                                               name="inboundPort[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">آدرس
                                                                                            پنل</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_api_domain']}}"
                                                                                               name="panelAddress[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">محدودیت</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_limit']}}"
                                                                                               name="serverLimit[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>

                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">نام
                                                                                            کاربری</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_user']}}"
                                                                                               name="serverUsername[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">کلمه
                                                                                            عبور</label>
                                                                                        <input type="text"
                                                                                               value="{{$detail['ip_password']}}"
                                                                                               name="serverPassword[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">نوع
                                                                                            کانکشن</label>
                                                                                        <select name="inboundConnection[]"
                                                                                                id=""
                                                                                                class="form-select">
                                                                                            <option value="vless">
                                                                                                vless
                                                                                            </option>
                                                                                            <option value="vmess">
                                                                                                vmess
                                                                                            </option>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="col-lg-3 mb-3">
                                                                                        <label class="required form-label">وضعیت</label>
                                                                                        <select name="serverStatus[]" class="form-select" id="">
                                                                                            <option value="1">فعال</option><option value="0">غیر فعال</option>
                                                                                            <option value="-1">حذف شده</option>
                                                                                            <option value="2">تکمیل شده</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="col-lg-12 mb-3">
                                                                                        <label class="required form-label">نمونه
                                                                                            کد</label>
                                                                                        <input type="text"
                                                                                               value="{{array_key_exists('code',$detail) ? $detail['code'] : ''}}"
                                                                                               name="codeExample[]"
                                                                                               class="form-control mb-2">
                                                                                    </div>

                                                                                    <div class="col-lg-2">
                                                                                        <br>
                                                                                        <button type="button"
                                                                                                class="btn btn-md btn-danger mt-2"
                                                                                                style="width: 100%"
                                                                                                onclick="$(this).parent().parent().parent().parent().parent().remove()">
                                                                                            حذف
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="separator separator-dashed"></div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-lg-12">
                                                                <div class="m-0">
                                                                    <div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed"
                                                                         data-bs-toggle="collapse"
                                                                         data-bs-target="#kt_job_5_1"
                                                                         aria-expanded="false">
                                                                        <!--begin::Icon-->
                                                                        <div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5">
                                                                            <i class="ki-duotone ki-minus-square toggle-on text-primary fs-1">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                            </i>
                                                                            <i class="ki-duotone ki-plus-square toggle-off fs-1">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </div>
                                                                        <h4 class="text-gray-700 fw-bold cursor-pointer mb-0">
                                                                            چه سیستم عامل هایی سازگار هستند</h4>

                                                                    </div>

                                                                    <div id="kt_job_5_1" class="fs-6 ms-1 collapse"
                                                                         style="">
                                                                        <div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10">
                                                                            <div class="row">
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">نام
                                                                                        سرور</label>
                                                                                    <input type="text"
                                                                                           name="serverName[]"
                                                                                           class="form-control mb-2"
                                                                                           placeholder="سرویس یک ماهه"
                                                                                           value="">
                                                                                    <input type="hidden" name="serverId[]" value="0">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">آی
                                                                                        پی
                                                                                        سرور</label>
                                                                                    <input type="text" name="serverIp[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">دامنه
                                                                                        سرور</label>
                                                                                    <input type="text"
                                                                                           name="serverDomain[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">پورت
                                                                                        سرور</label>
                                                                                    <input type="text"
                                                                                           name="serverPort[]"
                                                                                           class="form-control mb-2">
                                                                                </div>

                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">آیدی
                                                                                        inbound</label>
                                                                                    <input type="text"
                                                                                           name="inboundId[]"
                                                                                           class="form-control mb-2"
                                                                                           value="">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">پورت
                                                                                        inbound</label>
                                                                                    <input type="text"
                                                                                           name="inboundPort[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">آدرس
                                                                                        پنل</label>
                                                                                    <input type="text"
                                                                                           name="panelAddress[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">محدودیت</label>
                                                                                    <input type="text"
                                                                                           name="serverLimit[]"
                                                                                           class="form-control mb-2">
                                                                                </div>

                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">نام
                                                                                        کاربری</label>
                                                                                    <input type="text"
                                                                                           name="serverUsername[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">کلمه
                                                                                        عبور</label>
                                                                                    <input type="text"
                                                                                           name="serverPassword[]"
                                                                                           class="form-control mb-2">
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3"><label class="required form-label">نوع کانکشن</label>
                                                                                    <select name="inboundConnection[]"
                                                                                            id=""
                                                                                            class="form-select">
                                                                                        <option value="vless">vless
                                                                                        </option>
                                                                                        <option value="vmess">vmess
                                                                                        </option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-lg-3 mb-3">
                                                                                    <label class="required form-label">وضعیت</label>
                                                                                    <select name="serverStatus[]" class="form-select" id="">
                                                                                        <option value="1">فعال</option>
                                                                                        <option value="0">غیر فعال</option>
                                                                                        <option value="-1">حذف شده</option>
                                                                                        <option value="2">تکمیل شده</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-lg-12 mb-3">
                                                                                    <label class="required form-label">نمونه
                                                                                        کد</label>
                                                                                    <input type="text"
                                                                                           name="codeExample[]"
                                                                                           class="form-control mb-2">
                                                                                </div>

                                                                                <div class="col-lg-2">
                                                                                    <br>
                                                                                    <button type="button"
                                                                                            class="btn btn-md btn-danger mt-2"
                                                                                            style="width: 100%"
                                                                                            onclick="$(this).parent().parent().parent().parent().parent().remove()">
                                                                                        حذف
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="separator separator-dashed"></div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>تصویر شاخص</h2>
                                            </div>
                                        </div>
                                        <div class="card-body text-center pt-0">
                                            <style>
                                                .image-input-placeholder {
                                                    background-image: url('assets/media/svg/files/blank-image.svg');
                                                }

                                                [data-bs-theme="dark"] .image-input-placeholder {
                                                    background-image: url('assets/media/svg/files/blank-image-dark.svg');
                                                }
                                            </style>
                                            <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                                                 data-kt-image-input="true">
                                                <div class="image-input-wrapper w-150px h-150px"></div>
                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                       data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                       title="تعویض آواتار">

                                                    <i class="ki-duotone ki-pencil fs-7">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <input type="file" name="avatar" accept=".png, .jpg, .jpeg"/>
                                                    <input type="hidden" name="avatar_remove"/>
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                      data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                      title="انصراف avatar">
														<i class="ki-duotone ki-cross fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
													</span>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                      data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                      title="حذف آواتار">
														<i class="ki-duotone ki-cross fs-2">
															<span class="path1"></span>
															<span class="path2"></span>
														</i>
													</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-5">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success my-1 me-3 w-100">
                                        ثبت
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="price-example" style="display:none;" class="row">
        <div class="col-lg-3">
            <label class="required form-label">نام</label>
            <input type="text" name="productName[]" class="form-control mb-2"
                   placeholder="سرویس یک ماهه" value="">
        </div>
        <div class="col-lg-3">
            <label class="required form-label">قیمت</label>
            <input type="text" name="productPrice[]"
                   class="form-control mb-2">
        </div>
        <div class="col-lg-2">
            <label class="required form-label">مدت زمان</label>
            <input type="text" name="productDays[]"
                   class="form-control mb-2">
        </div>
        <div class="col-lg-2">
            <label class="required form-label">حجم</label>
            <input type="text" name="productHajm[]"
                   class="form-control mb-2">
        </div>
        <div class="col-lg-2">
            <br>
            <button type="button" class="btn btn-md btn-danger mt-2" style="width: 100%"
                    onclick="$(this).parent().parent().remove()">
                حذف
            </button>
        </div>
    </div>
@endsection


@section('footer')
    <script>
        $(document).on('click', '.add-product', function () {
            var currentTime = new Date().getTime()
            var original = $('#price-example');
            var clone = original.clone(); // Clone the element
            clone.attr('id', 'as-' + currentTime); // Clone the element
            clone.addClass('price-area'); // Clone the element
            $('#price-area').append(clone); // Append the cloned element to the container
            $('.price-area').show();
        });
        var rand = 1;
        $(document).on('click', '.add-server', function () {
            $('#server-area').append('<div id="server-example"  class="col-lg-12"><div class="m-0"><div class="d-flex align-items-center collapsible py-3 toggle mb-0 collapsed" data-bs-toggle="collapse" data-bs-target="#' + rand + '" aria-expanded="false"><div class="btn btn-sm btn-icon mw-20px btn-active-color-primary me-5"><i class="ki-duotone ki-minus-square toggle-on text-primary fs-1"><span class="path1"></span><span class="path2"></span></i><i class="ki-duotone ki-plus-square toggle-off fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i></div><h4 class="text-gray-700 fw-bold cursor-pointer mb-0">سرور شماره ' + rand + '</h4></div><div id="' + rand + '" class="fs-6 ms-1 collapse" style=""><div class="mb-4 text-gray-600 fw-semibold fs-6 ps-10"><div class="row"><div class="col-lg-3 mb-3"><label class="required form-label">نام سرور</label><input type="text" name="serverName[]" class="form-control mb-2" placeholder="سرویس یک ماهه" value=""><input type="hidden" name="serverId[]" value="0"></div><div class="col-lg-3 mb-3"><label class="required form-label">آی پی سرور</label><input type="text" name="serverIp[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">دامنه سرور</label><input type="text" name="serverDomain[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">پورت سرور</label><input type="text" name="serverPort[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">آیدی inbound</label><input type="text" name="inboundId[]" class="form-control mb-2" value=""></div><div class="col-lg-3 mb-3"><label class="required form-label">پورت inbound</label><input type="text" name="inboundPort[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">آدرس پنل</label><input type="text" name="panelAddress[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">محدودیت</label><input type="text" name="serverLimit[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">نام کاربری</label><input type="text" name="serverUsername[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">کلمه عبور</label><input type="text" name="serverPassword[]" class="form-control mb-2"></div><div class="col-lg-3 mb-3"><label class="required form-label">نوع کانکشن</label><select name="inboundConnection[]" id="" class="form-select"><option value="vless">vless</option><option value="vmess">vmess</option></select></div><div class="col-lg-3 mb-3"><label class="required form-label">وضعیت</label><select name="serverStatus[]" class="form-select" id=""><option value="1">فعال</option><option value="0">غیر فعال</option><option value="-1">حذف شده</option><option value="2">تکمیل شده</option></select></div><div class="col-lg-12 mb-3"><label class="required form-label">نمونه کد</label><input type="text" name="codeExample[]" class="form-control mb-2"></div><div class="col-lg-2"><br><button type="button" class="btn btn-md btn-danger mt-2" style="width:100%" onclick="$(this).parent().parent().parent().parent().parent().remove()">حذف</button></div></div></div></div><div class="separator separator-dashed"></div></div></div>'); // Append the cloned element to the container
            $('.server-area').show();
            rand = rand + 1;
        });
    </script>
@endsection
