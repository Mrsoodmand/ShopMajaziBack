@extends('admin-v2.layout.master')
@section('content')
    @if(isset($edit))
        @php
            $Detail = json_decode($edit->detail,true);
        @endphp
    @endif
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.service-update') :  route('admin.service-store') }}"
                      method="post">
                    @csrf
                    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>تصویر شاخص</h2>
                                    <div style="left: 15px;position: absolute">
                                        <button type="button" data-ckfinder-field="gallery_image_input_1"
                                                class="btn btn-sm btn-primary">افزودن تصویر
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <input type="text" name="image_gallery" id="gallery_image_input_1" class="form-control mb-2"
                                       placeholder="لینک تصویر"
                                       value="{{isset($edit) ? $Detail['image_gallery'] : '' }}">
                                <img alt="image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     src="{{isset($edit) ? $Detail['image_gallery'] : '' }}">
                                <div class="text-muted fs-7">تصویر بندانگشتی محصول را تنظیم کنید. فقط فایل های تصویری
                                    *.png، *.jpg و *.jpeg پذیرفته می شوند
                                </div>
                            </div>
                        </div>
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>وضعیت</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select class="form-select mb-2" data-control="select2" name="status"
                                        data-hide-search="true"
                                        data-placeholder="انتخاب " id="kt_ecommerce_add_product_status_select">
                                    <option {{isset($edit) && $edit->status == 1 ? 'selected':''}} value="1"
                                            selected="selected">منتشر شده
                                    </option>
                                    <option {{isset($edit) && $edit->status == 0 ? 'selected':''}} value="0">غیرفعال
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>محصولات جزییات</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <label class="form-label">دسته بندی ها</label>
                                <select class="form-select mb-2" data-control="select2" name="category_id"
                                        data-hide-search="true" data-placeholder="انتخاب "
                                        id="kt_ecommerce_add_product_status_select">
                                    @foreach($Categories as $item)
                                        <option
                                                {{isset($edit) && $item->id == $edit->country_id ? 'selected' : '' }} value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!--end::Tab content-->
                        <div class="d-flex justify-content-end">
                            <button type="button" id="submit_btn" class="btn btn-primary submit_btn w-100">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                        <div class="d-flex justify-content-end">
                            @if(isset($edit))
                                <br>
                                <a type="button" href="{{secure_url('/virtual-service/'.$edit->ServiceCategory->slug.'/'.$edit->slug)}}" class="btn btn-warning w-100" target="_blank">
                                    <span class="indicator-label">مشاهده صفحه</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    @if(isset($edit))
                        <input type="hidden" name="id" value="{{$edit->id}}">
                    @endif
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#products">محصولات</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#fild">فیلد
                                    ها</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#notes">نکات
                                    مهم</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#faq">سوالات
                                    متداول</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#seo">سئو</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="all" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>عمومی</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام به فارسی</label>
                                                    <input type="text" name="faName" class="form-control mb-2"
                                                           placeholder="نام به فارسی"
                                                           value="{{isset($edit) ? $Detail['name'] : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام به انگلیسی</label>
                                                    <input type="text" name="enName" class="form-control mb-2"
                                                           placeholder="نام به انگلیسی"
                                                           value="{{isset($edit) ? $Detail['enName'] : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">لینک</label>
                                                    <input type="text" name="slug" class="form-control mb-2"
                                                           placeholder="لینک"
                                                           value="{{isset($edit) ? $edit->slug : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کد محصول</label>
                                                    <input type="text" name="code" class="form-control mb-2"
                                                           value="{{isset($edit) && array_key_exists('code',$Detail) ? $Detail['code'] : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">قیمت به ازای 1K</label>
                                                    <input type="text" name="price" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->price : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5 mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">قیمت هر ۱۰۰ عدد (تومان)</label>
                                                    <input type="number" min="0" name="price_per_100" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->price_per_100 : '' }}"/>
                                                    <div class="text-muted fs-7">اگر پر شود، مشتری تعداد دلخواه را با اسلایدر انتخاب می‌کند و قیمت = تعداد ÷ ۱۰۰ × این مبلغ. خالی = همان پلن‌ها.</div>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">حداقل تعداد</label>
                                                    <input type="number" min="100" step="100" name="min_count" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->min_count : 100 }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">حداکثر تعداد</label>
                                                    <input type="number" min="100" step="100" name="max_count" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->max_count : 10000 }}"/>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضحیات کوتاه</label>
                                                    <textarea id="short_detail" name="short_detail" class="form-control"
                                                              cols="30"
                                                              rows="10">{!! isset($edit) ? $Detail['short_detail'] : '' !!}</textarea>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات بلند</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($edit) ? $Detail['detail'] : '' !!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($edit) ? $Detail['detail'] : ''}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="products" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>محصولات</h2>
                                                <div style="left: 15px;position: absolute">
                                                    <button type="button" class="AddProduct btn btn-sm btn-primary">
                                                        افزودن محصول
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0" id="product-area">
                                            @if(isset($edit) && count($Plans) != 0)
                                                @foreach($Plans as $plan)
                                                    <div class="row">
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">نام محصول</label>
                                                            <input type="text" name="dynamic_name[]"
                                                                   class="form-control mb-2" placeholder="نام"
                                                                   value="{{$plan['name']}}"/>
                                                            <input name="dynamic_id[]" type="hidden"
                                                                   value="{{$plan['id']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">تعداد</label>
                                                            <input type="text" name="dynamic_count[]"
                                                                   class="form-control mb-2" placeholder="نام"
                                                                   value="{{$plan['count']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="form-label">وضعیت</label>
                                                            <select class="form-select mb-2" data-control="select2"
                                                                    name="dynamic_status[]" data-hide-search="true"
                                                                    data-placeholder="انتخاب ">
                                                                <option
                                                                        {{isset($edit) && $plan['status'] == 1 ? 'selected' : '' }} value="1">
                                                                    فعال
                                                                </option>
                                                                <option
                                                                        {{isset($edit) && $plan['status'] == 0 ? 'selected' : '' }} value="0">
                                                                    غیر فعال
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">قیمت(دلار)</label>
                                                            <input type="text" name="dynamic_price[]"
                                                                   class="form-control mb-2" placeholder="قیمت"
                                                                   value="{{$plan['price']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">قیمت با
                                                                تخفیف(دلار)</label>
                                                            <input type="text" name="dynamic_discount[]"
                                                                   class="form-control mb-2" placeholder="قیمت با تخفیف"
                                                                   value="{{$plan['discount']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <button type="button"
                                                                    onclick="$(this).parent().parent().remove();$('#hr-{{$plan['id']}}').remove()"
                                                                    class="btn btn-danger mt-9">
                                                                <span class="indicator-label">حذف</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <hr id="hr-{{$plan['id']}}" class="mt-2 mb-2">
                                                @endforeach
                                            @else
                                                <div class="row">
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">نام محصول</label>
                                                        <input type="text" name="dynamic_name[]"
                                                               class="form-control mb-2" placeholder="نام" value=""/>
                                                        <input name="dynamic_id[]" type="hidden" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">تعداد</label>
                                                        <input type="text" name="dynamic_count[]"
                                                               class="form-control mb-2" placeholder="نام" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="form-label">وضعیت</label>
                                                        <select class="form-select mb-2" data-control="select2"
                                                                name="dynamic_status[]" data-hide-search="true"
                                                                data-placeholder="انتخاب ">
                                                            <option value="1">فعال</option>
                                                            <option value="0">غیر فعال</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">قیمت(دلار)</label>
                                                        <input type="text" name="dynamic_price[]"
                                                               class="form-control mb-2"
                                                               placeholder="قیمت" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">قیمت با تخفیف(دلار)</label>
                                                        <input type="text" name="dynamic_discount[]"
                                                               class="form-control mb-2" placeholder="قیمت با تخفیف"
                                                               value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <button type="button"
                                                                onclick="$(this).parent().parent().remove();$('#hr-0').remove()"
                                                                class="btn btn-danger mt-9">
                                                            <span class="indicator-label">حذف</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr id="hr-0" class="mt-2 mb-2">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="fild" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>محصولات</h2>
                                                <div style="left: 15px;position: absolute">
                                                    <button type="button" class="Addfild btn btn-sm btn-primary">
                                                        افزودن فیلد
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0" id="fild-area">
                                            @if(isset($edit) && $edit->filds != null)
                                                @php $Fileds = json_decode($edit->filds,true) @endphp
                                                @foreach($Fileds as $key => $filed)
                                                    <div class="row">
                                                        <div class="col-6 col-lg-4">
                                                            <label class="form-label">نوع</label>
                                                            <select class="form-select mb-2 filed_type"
                                                                    data-control="select2"
                                                                    name="filed[type][]" data-hide-search="true"
                                                                    data-id="{{$key}}"
                                                                    data-placeholder="انتخاب ">
                                                                <option {{$filed['type'] == 'select_box' ? "selected" : ''}} value="select_box">
                                                                    select box
                                                                </option>
                                                                <option {{$filed['type'] == 'text' ? "selected" : ''}} value="text">
                                                                    متن
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">عنوان</label>
                                                            <input type="text" name="filed[title][]"
                                                                   class="form-control mb-2" placeholder="عنوان"
                                                                   value="{{$filed['title']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">Col</label>
                                                            <input type="text" name="filed[col][]"
                                                                   class="form-control mb-2" placeholder="اندازه"
                                                                   value="{{$filed['col']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <label class="required form-label">نام فیلد</label>
                                                            <input type="text" name="filed[filed_name][]"
                                                                   class="form-control mb-2"
                                                                   value="{{$filed['filed_name']}}"/>
                                                        </div>

                                                        <div class="col-6 col-lg-4" id="filed-area-{{$key}}"
                                                             @if($filed['type'] != 'select_box') style="display: none" @endif>
                                                            <label class="required form-label">مقدار سلکت</label>
                                                            <input type="text" name="filed[select_value][]"
                                                                   class="form-control mb-2"
                                                                   value="{{$filed['select_value']}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-4">
                                                            <button type="button"
                                                                    onclick="$(this).parent().parent().remove();$('#hr-0').remove()"
                                                                    class="btn btn-danger mt-9">
                                                                <span class="indicator-label">حذف</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                @endforeach
                                            @else
                                                <div class="row">
                                                    <div class="col-6 col-lg-4">
                                                        <label class="form-label">نوع</label>
                                                        <select class="form-select mb-2 filed_type"
                                                                data-control="select2"
                                                                name="filed[type][]" data-hide-search="true"
                                                                data-id="1000"
                                                                data-placeholder="انتخاب ">
                                                            <option value="select_box">select box</option>
                                                            <option value="text">متن</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">عنوان</label>
                                                        <input type="text" name="filed[title][]"
                                                               class="form-control mb-2" placeholder="عنوان"/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">Col</label>
                                                        <input type="text" name="filed[col][]" class="form-control mb-2"
                                                               placeholder="اندازه"/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <label class="required form-label">نام فیلد</label>
                                                        <input type="text" name="filed[filed_name][]"
                                                               class="form-control mb-2"/>
                                                    </div>
                                                    <div class="col-6 col-lg-4" id="filed-area-1000">
                                                        <label class="required form-label">مقدار سلکت</label>
                                                        <input type="text" placeholder="جدا کننده علامت , میباشد"
                                                               name="filed[select_value][]"
                                                               class="form-control mb-2" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-4">
                                                        <button type="button"
                                                                onclick="$(this).parent().parent().remove();$('#hr-0').remove()"
                                                                class="btn btn-danger mt-9">
                                                            <span class="indicator-label">حذف</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="notes" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>نکات مهم</h2>
                                                <div style="left: 15px;position: absolute">
                                                    <button type="button" class="AddNote btn btn-sm btn-primary">
                                                        افزودن نکته
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0" id="note-area">
                                            @if(isset($edit) && $edit->notes != null)
                                                @php $Fileds = json_decode($edit->notes,true) @endphp
                                                @foreach($Fileds as $key => $filed)
                                                    <div class="row">
                                                        <div class="col-6 col-lg-5">
                                                            <label class="required form-label">عنوان</label>
                                                            <input type="text" name="Notekey[]"
                                                                   class="form-control mb-2" placeholder="عنوان"
                                                                   value="{{$key}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-5">
                                                            <label class="required form-label">Col</label>
                                                            <input type="text" name="notes[]"
                                                                   class="form-control mb-2" placeholder="اندازه"
                                                                   value="{{$filed}}"/>
                                                        </div>
                                                        <div class="col-6 col-lg-2">
                                                            <button type="button"
                                                                    onclick="$(this).parent().parent().remove();$('#hr-0').remove()"
                                                                    class="btn btn-danger mt-9">
                                                                <span class="indicator-label">حذف</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                @endforeach
                                            @else
                                                <div class="row">
                                                    <div class="col-6 col-lg-5">
                                                        <label class="required form-label">عنوان</label>
                                                        <input type="text" name="Notekey[]" class="form-control mb-2"
                                                               placeholder="عنوان" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-5">
                                                        <label class="required form-label">Col</label>
                                                        <input type="text" name="notes[]" class="form-control mb-2"
                                                               placeholder="اندازه" value=""/>
                                                    </div>
                                                    <div class="col-6 col-lg-2">
                                                        <button type="button"
                                                                onclick="$(this).parent().parent().remove();"
                                                                class="btn btn-danger mt-9">
                                                            <span class="indicator-label">حذف</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="faq" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>کلمات کلیدی</h2>
                                            </div>
                                            <div class="card-toolbar">
                                                <button type="button" class="btn btn-warning add-faq">
                                                    افزودن
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="faq-area">
                                                @if(isset($edit) && !is_null($edit->faq) && $edit->faq != 'null')
                                                    @foreach(json_decode($edit->faq,true) as $item)
                                                        <div class="row">
                                                            <div class="col-lg-5">
                                                                <label class="required form-label">سوال</label>
                                                                <input type="text" name="question[]"
                                                                       class="form-control mb-2"
                                                                       placeholder=""
                                                                       value="{{$item['question']}}"/>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <label class="required form-label">جواب</label>
                                                                <input type="text" name="answer[]"
                                                                       class="form-control mb-2"
                                                                       placeholder=""
                                                                       value="{{$item['answer']}}"/>
                                                            </div>
                                                            <div class="col-lg-1">
                                                                <br>
                                                                <button type="button" class="btn btn-md btn-danger mt-2"
                                                                        style="width: 100%"
                                                                        onclick="$(this).parent().parent().remove()">
                                                                    x
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="seo" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>سئو</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="mb-10">
                                                <label class="required form-label">متا تایتل</label>
                                                <input type="text" name="meta_title" class="form-control mb-2"
                                                       placeholder="نام به فارسی"
                                                       value="{{isset($edit) ? $Detail['meta_title'] : '' }}"/>
                                            </div>
                                            <div class="mb-10">
                                                <label class="required form-label">توضحیات متا</label>
                                                <input type="text" name="meta_desc" class="form-control mb-2"
                                                       placeholder="نام به فارسی"
                                                       value="{{isset($edit) ? $Detail['meta_desc'] : '' }}"/>
                                            </div>
                                            <div>
                                                <label class="form-label">توضیحات</label>
                                                <textarea name="seo" class="form-control" cols="30"
                                                          rows="10">{!! isset($edit) ? $Detail['seo'] : '' !!}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')
    <link href="{{URL::asset('admin/js/all.css')}}" rel="stylesheet" type="text/css">

@endsection
@section('footer')
    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/datatables/datatables.bundle.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>
    <!--end::Page Vendors Javascript-->
    <!--begin::Page سفارشی Javascript(used by this page)-->
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/ecommerce/catalog/save-product.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/widgets.bundle.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/widgets.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/chat/chat.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/upgrade-plan.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/create-app.js"></script>
    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/users-search.js"></script>

    @include('admin.layout.submit-form')
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{secure_url('admin/js/all.js')}}" type="text/javascript"></script>

    <script>
        $(document).on('click', '.AddProduct', function () {
            $('#product-area').append('' +
                '<div class="row">' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">نام محصول</label>' +
                '<input type="text" name="dynamic_name[]" class="form-control mb-2" placeholder="نام" value=""/>' +
                '<input name="dynamic_id[]" type="hidden" value=""/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">تعداد</label>' +
                '<input type="text" name="dynamic_count[]" class="form-control mb-2" placeholder="نام" value=""/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="form-label">وضعیت</label>' +
                '<select class="form-select mb-2" data-control="select2" name="dynamic_status[]" data-hide-search="true" data-placeholder="انتخاب ">' +
                '<option value="1">فعال</option>' +
                '<option value="0">غیر فعال</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">قیمت(دلار)</label>' +
                '<input type="text" name="dynamic_price[]" class="form-control mb-2" placeholder="قیمت" value=""/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">قیمت با تخفیف(دلار)</label>' +
                '<input type="text" name="dynamic_discount[]" class="form-control mb-2" placeholder="قیمت با تخفیف" value=""/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<button type="button" onclick="$(this).parent().parent().remove();" class="btn btn-danger mt-9">' +
                '<span class="indicator-label">حذف</span>' +
                '</button>' +
                '</div>' +
                '</div>');
        });
        var id = 1001;
        $(document).on('click', '.Addfild', function () {
            id++
            $('#fild-area').append('' +
                '<div class="row">' +
                '<div class="col-6 col-lg-4">' +
                '<label class="form-label">نوع</label>' +
                '<select class="form-select mb-2 filed_type" name="filed[type][]" data-id="' + id + '" data-placeholder="انتخاب ">' +
                '<option value="select_box">select box</option>' +
                '<option selected value="text">متن</option>' +
                '</select>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">عنوان</label>' +
                '<input type="text" name="filed[title][]" class="form-control mb-2" placeholder="عنوان"/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">Col</label>' +
                '<input type="text" name="filed[col][]" class="form-control mb-2" placeholder="اندازه"/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<label class="required form-label">نام فیلد</label>' +
                '<input type="text" name="filed[filed_name][]" class="form-control mb-2"/>' +
                '</div>' +
                '<div class="col-6 col-lg-4" id="filed-area-' + id + '">' +
                ' <label class="required form-label">مقدار سلکت</label>' +
                ' <input type="text" name="filed[select_value][]" placeholder="جدا کننده علامت , میباشد" class="form-control mb-2" value=""/>' +
                '</div>' +
                '<div class="col-6 col-lg-4">' +
                '<button type="button" onclick="$(this).parent().parent().remove();" class="btn btn-danger mt-9">' +
                '<span class="indicator-label">حذف</span>' +
                '</button>' +
                '</div>' +
                '</div>');
        });

        $(document).on('click', '.AddNote', function () {
            id++
            $('#note-area').append('' +
                '<div class="row">' +
                '<div class="col-6 col-lg-5">' +
                '<label class="required form-label">عنوان</label>' +
                '<input type="text" name="Notekey[]" class="form-control mb-2" placeholder="عنوان" />' +
                '</div>' +
                '<div class="col-6 col-lg-5">' +
                '<label class="required form-label">Col</label>' +
                '<input type="text" name="notes[]" class="form-control mb-2" placeholder="اندازه"/>' +
                '</div>' +
                '<div class="col-6 col-lg-2">' +
                '<button type="button" onclick="$(this).parent().parent().remove()" class="btn btn-danger mt-9">' +
                '<span class="indicator-label">حذف</span>' +
                '</button>' +
                '</div>' +
                '</div>'
            );
        });

        $(document).on('change', '.filed_type', function () {
            var val = $(this).val();
            var logId = $(this).data('id')
            if (val === 'select_box') {
                $('#filed-area-' + logId).show()
            } else {
                $('#filed-area-' + logId).hide()
            }
        });


        var editor = CKEDITOR.replace('detail', {
            height: 800,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });

        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#changed').val(evt.editor.getData())

        })

        function ChangeName() {
            var text = $("#name").val()
            var replace = $("#name").val().replace('شماره مجازی کشور', "")
            var url = "خرید "
            var all = url + text;
            var replace_all = "وب سایت شماره مجازی ارائه دهنده شماره مجازی +   replace +  اپل آیدی ، اینستاگرام ، واتس آپ ، تلگرام ، تیک تاک و انواع اپلیکشین های دیگر با تنوع بیش از 30 کشور سرویس دهنده.  | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر   .";
            $(".replace").val(all)
            $("#desc").val(" وب سایت شماره مجازی ارائه دهنده  شماره مجازی از کشورهای " + replace + " ، روسیه ، امریکا ، آرژانتین ، آلمان ، انگلستان و بیش از 50 کشور و با تنوع بیش از 60 برنامه و اپلیکشین | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر. ")
        }

        $(document).on('click', '.add-faq', function () {
            $('#faq-area').after(
                '<div class="row"><div class="col-lg-5"><label class="required form-label">سوال</label><input name="question[]" class="form-control mb-2" placeholder=""></div><div class="col-lg-6"><label class="required form-label">جواب</label><input name="answer[]" class="form-control mb-2" placeholder=""></div><div class="col-lg-1"><br><button type="button" class="btn btn-md btn-danger mt-2" style="width:100%" onclick="$(this).parent().parent().remove()">x</button></div></div>'
            )
        })

    </script>
@endsection
