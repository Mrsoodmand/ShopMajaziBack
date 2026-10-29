@extends('admin-v2.layout.master')
@section('content')
    @if(isset($edit))
        @php $Detail = json_decode($edit->detail,true); @endphp
    @endif
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @include('admin-v2.layout.sesstion')
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.hegza-ip-update') :  route('admin.hegza-ip-store',$type) }}"
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
                                <input type="text" name="image_gallery" id="gallery_image_input_1"
                                       class="form-control mb-2"
                                       placeholder="لینک تصویر"
                                       value="{{isset($edit) ? $edit->image : '' }}">
                                <img alt="image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     src="{{isset($edit) && !is_null($edit->image) ? secure_url($edit->image) : '' }}">
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
                                            selected="selected">فعال
                                    </option>
                                    <option {{isset($edit) && $edit->status == 0 ? 'selected':''}} value="0">غیرفعال
                                    </option>
                                </select>
                            </div>
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
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#products">تعرفه</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#fild">آی پی ها</a>
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
                                                           value="{{isset($edit) ? $edit->name : '' }}"/>
                                                    <input type="hidden" name="type" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->type : $type }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام به انگلیسی</label>
                                                    <input type="text" name="enName" class="form-control mb-2"
                                                           placeholder="نام به انگلیسی"
                                                           value="{{isset($edit) ? $edit->enName : '' }}"/>
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
                                                <h2>تعرفه ها</h2>
                                                <div style="left: 15px;position: absolute">
                                                    <button type="button" class="AddProduct btn btn-sm btn-primary">
                                                        افزودن تعرفه
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0" id="product-area">
                                            @if(isset($edit) && count($Plans) != 0)
                                                @foreach($Plans as $plan)
                                                    @php $Data = json_decode($plan->detail,true); @endphp
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">نام محصول</label>
                                                                <input required type="text" value="{{$plan->faName}}"
                                                                       name="product_name[]"
                                                                       class="form-control mb-2" placeholder=""/>
                                                                <input required type="hidden" value="{{$plan->id}}"
                                                                       name="product_id[]"/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">تعداد روز</label>
                                                                <input required type="number" value="{{$Data['day']}}"
                                                                       name="product_day[]"
                                                                       class="form-control mb-2" placeholder=""/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت</label>
                                                                <input required type="number" value="{{$plan->price}}"
                                                                       name="product_price[]"
                                                                       class="form-control mb-2" placeholder=""/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت با تخفیف</label>
                                                                <input required type="number" value="{{$Data['dis']}}"
                                                                       name="product_dis[]"
                                                                       placeholder="" class="form-control mb-2"/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">حجم</label>
                                                                <input required type="number"
                                                                       value="{{$Data['volume']}}"
                                                                       name="product_volume[]" placeholder=""
                                                                       class="form-control mb-2"/>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">درصد تخفیف</label>
                                                                <input required type="number"
                                                                       value="{{$Data['percent']}}"
                                                                       name="product_percent[]"
                                                                       placeholder="" class="form-control mb-2"/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">عکس</label>
                                                                <input required type="text" value="{{$Data['image']}}"
                                                                       name="product_image[]"
                                                                       id="image-{{$plan->id}}" placeholder=""
                                                                       class="form-control mb-2"/>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button" data-ckfinder-field="image-{{ $plan->id }}"
                                                                        class="btn btn-info mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">انتخاب عکس</span>
                                                                </button>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().parent().remove(); "
                                                                        class="btn btn-danger mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">حذف</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="card-body pt-0" id="product-area">
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">نام محصول</label>
                                                                <input required="" type="text" value="یک ماهه"
                                                                       name="product_name[]" class="form-control mb-2"
                                                                       placeholder="">
                                                                <input required="" type="hidden" value="0"
                                                                       name="product_id[]">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">تعداد روز</label>
                                                                <input required="" type="number" value="30"
                                                                       name="product_day[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت</label>
                                                                <input required="" type="number" value="100000"
                                                                       name="product_price[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت با تخفیف</label>
                                                                <input required="" type="number" value="0"
                                                                       name="product_dis[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">حجم</label>
                                                                <input required="" type="number" value="30"
                                                                       name="product_volume[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">درصد تخفیف</label>
                                                                <input required="" type="number" value="0"
                                                                       name="product_percent[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">عکس</label>
                                                                <input required="" type="text"
                                                                       value="image/svg/30-days.svg"
                                                                       name="product_image[]" id="image-66723"
                                                                       placeholder="" class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button" data-ckfinder-field="image-66723"
                                                                        class="btn btn-info mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">انتخاب عکس</span>
                                                                </button>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().parent().remove(); "
                                                                        class="btn btn-danger mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">حذف</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">نام محصول</label>
                                                                <input required="" type="text" value="سه ماهه"
                                                                       name="product_name[]" class="form-control mb-2"
                                                                       placeholder="">
                                                                <input required="" type="hidden" value="0"
                                                                       name="product_id[]">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">تعداد روز</label>
                                                                <input required="" type="number" value="90"
                                                                       name="product_day[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت</label>
                                                                <input required="" type="number" value="300000"
                                                                       name="product_price[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت با تخفیف</label>
                                                                <input required="" type="number" value="270000"
                                                                       name="product_dis[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">حجم</label>
                                                                <input required="" type="number" value="90"
                                                                       name="product_volume[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">درصد تخفیف</label>
                                                                <input required="" type="number" value="10"
                                                                       name="product_percent[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">عکس</label>
                                                                <input required="" type="text"
                                                                       value="image/svg/90-days.svg"
                                                                       name="product_image[]" id="image-66724"
                                                                       placeholder="" class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button" data-ckfinder-field="image-66724"
                                                                        class="btn btn-info mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">انتخاب عکس</span>
                                                                </button>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().parent().remove(); "
                                                                        class="btn btn-danger mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">حذف</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">نام محصول</label>
                                                                <input required="" type="text" value="شش ماهه"
                                                                       name="product_name[]" class="form-control mb-2"
                                                                       placeholder="">
                                                                <input required="" type="hidden" value="0"
                                                                       name="product_id[]">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">تعداد روز</label>
                                                                <input required="" type="number" value="180"
                                                                       name="product_day[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت</label>
                                                                <input required="" type="number" value="600000"
                                                                       name="product_price[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت با تخفیف</label>
                                                                <input required="" type="number" value="510000"
                                                                       name="product_dis[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">حجم</label>
                                                                <input required="" type="number" value="180"
                                                                       name="product_volume[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">درصد تخفیف</label>
                                                                <input required="" type="number" value="15"
                                                                       name="product_percent[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">عکس</label>
                                                                <input required="" type="text"
                                                                       value="image/svg/180-days.svg"
                                                                       name="product_image[]" id="image-66725"
                                                                       placeholder="" class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button" data-ckfinder-field="image-66725"
                                                                        class="btn btn-info mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">انتخاب عکس</span>
                                                                </button>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().parent().remove(); "
                                                                        class="btn btn-danger mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">حذف</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">نام محصول</label>
                                                                <input required="" type="text" value="یک ساله"
                                                                       name="product_name[]" class="form-control mb-2"
                                                                       placeholder="">
                                                                <input required="" type="hidden" value="0"
                                                                       name="product_id[]">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">تعداد روز</label>
                                                                <input required="" type="number" value="365"
                                                                       name="product_day[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت</label>
                                                                <input required="" type="number" value="1200000"
                                                                       name="product_price[]" class="form-control mb-2"
                                                                       placeholder="">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">قیمت با تخفیف</label>
                                                                <input required="" type="number" value="960000"
                                                                       name="product_dis[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">حجم</label>
                                                                <input required="" type="number" value="365"
                                                                       name="product_volume[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">درصد تخفیف</label>
                                                                <input required="" type="number" value="20"
                                                                       name="product_percent[]" placeholder=""
                                                                       class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">عکس</label>
                                                                <input required="" type="text"
                                                                       value="image/svg/360-days.svg"
                                                                       name="product_image[]" id="image-66726"
                                                                       placeholder="" class="form-control mb-2">
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button" data-ckfinder-field="image-66726"
                                                                        class="btn btn-info mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">انتخاب عکس</span>
                                                                </button>
                                                            </div>
                                                            <div
                                                                    class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().parent().remove(); "
                                                                        class="btn btn-danger mt-8 w-100 btn-block">
                                                                    <span class="indicator-label">حذف</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                        افزودن آی پی
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0" id="fild-area">
                                            @if(isset($edit) && count($Ip) != 0)
                                                @foreach($Ip->where('type',$edit->type) as $ips)
                                                    @php $detail = json_decode($ips->detail,true) @endphp
                                                    <div class="py-0 mt-3">
                                                        <div class="py-3 d-flex flex-stack flex-wrap">
                                                            <div
                                                                    class="d-flex align-items-center collapsible rotate collapsed"
                                                                    data-bs-toggle="collapse"
                                                                    href="#scroll-{{$ips->id}}"
                                                                    role="button"
                                                                    aria-exped="false"
                                                                    aria-controls="kt_customer_view_payment_method"
                                                                    aria-expanded="false">
                                                                <div class="me-3 rotate-90"><span
                                                                            class="svg-icon svg-icon-3"><svg
                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                width="24"
                                                                                height="24" viewBox="0 0 24 24"
                                                                                fill="none"><path
                                                                                    d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z"
                                                                                    fill="currentColor"></path></svg></span>
                                                                </div>
                                                                <div class="me-3">
                                                                    <div class="d-flex align-items-center">آی پی
                                                                        شماره: {{$detail['ip_address']}}
                                                                    </div>
                                                                    <div class="text-muted">آدرس
                                                                        دامنه:{{$detail['ip_domain']}}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="scroll-{{$ips->id}}"
                                                             class="fs-6 ps-9 collapse"
                                                             data-bs-parent="#kt_customer_view_payment_method" style="">
                                                            <div class="border-bottom mt-5">
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">دامنه:</label>
                                                                        <input required="" type="text" value="{{$detail['ip_address']}}"
                                                                               name="ip_address[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                        <input required="" type="hidden" value="{{$ips->id}}"
                                                                               name="ip_id[{{$ips->id}}]">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">آی پی:</label>
                                                                        <input required="" type="text" value="{{$detail['ip_domain']}}"
                                                                               name="ip_domain[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">پورت</label>
                                                                        <input required="" type="text" value="{{$detail['ip_port']}}"
                                                                               name="ip_port[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">نام</label>
                                                                        <input required="" type="text"
                                                                               value="{{array_key_exists('ip_name',$detail) ? $detail['ip_name'] : ''}}"
                                                                               name="ip_name[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">نام
                                                                            کاربری:</label>
                                                                        <input required="" type="text" value="{{$detail['ip_user']}}"
                                                                               name="ip_user[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">کلمه عبور</label>
                                                                        <input required="" type="text" value="{{$detail['ip_password']}}"
                                                                               name="ip_password[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">وضعیت</label>
                                                                        <select name="ip_status[{{$ips->id}}]" class="form-control " id="">
                                                                            <option @if($detail['ip_status'] == 1) @endif value="1">فعال</option>
                                                                            <option @if($detail['ip_status'] == 0) @endif value="0">غیر فعال</option>
                                                                            <option @if($detail['ip_status'] == -1) @endif value="-1">حذف شده</option>
                                                                            <option @if($detail['ip_status'] == 2) @endif value="2">تکمیل شده</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">آدرس دامنه</label><input required="" type="text" value="{{array_key_exists('ip_api_domain',$detail) ? $detail['ip_api_domain'] : ''}}" name="ip_api_domain[{{$ips->id}}]" class="form-control mb-2" placeholder=""></div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">آیدی
                                                                            اینبوند</label>
                                                                        <input required="" type="text" value="{{$detail['ip_inbound']}}"
                                                                               name="ip_inbound[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">محدودیت</label>
                                                                        <input required="" type="text" value="0"
                                                                               name="ip_limit[{{$ips->id}}]"
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">پورت اینبوند</label><input required="" type="text" value="{{array_key_exists('port_inbound',$detail) ? $detail['port_inbound'] : '443'}}" name="port_inbound[{{$ips->id}}]" class="form-control mb-2" placeholder=""></div>
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">سشن ها</label>
                                                                        <input required="" type="text" value="{{array_key_exists('session',$detail) ? $detail['session'] : ''}}"
                                                                               disabled
                                                                               class="form-control mb-2" placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <button type="button"
                                                                                style="position: absolute;left: 20px"
                                                                                data-id="2"
                                                                                class="AddUser btn btn-primary mt-8 w-100 btn-block">
                                                                            افزودن کاربر
                                                                        </button>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <button type="button"
                                                                                onclick="$(this).parent().parent().parent().parent().parent().remove(); "
                                                                                class="btn btn-danger mt-8 w-100 btn-block">
                                                                            <span class="indicator-label">حذف</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <hr>
                                                                <div id="ips-{{$ips->id}}" style="padding-left: 10px"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
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
                                                       value="{{isset($edit) ? $edit->meta_title : '' }}"/>
                                            </div>
                                            <div class="mb-10">
                                                <label class="required form-label">توضحیات متا</label>
                                                <input type="text" name="meta_desc" class="form-control mb-2"
                                                       placeholder="نام به فارسی"
                                                       value="{{isset($edit) ? $edit->meta_desc : '' }}"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" id="submit_btn"
                                    class="btn btn-primary">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...<span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
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

{{--    @include('admin.layout.submit-form')--}}
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{secure_url('admin/js/all.js')}}" type="text/javascript"></script>

    <script>
        var GalleryId = 111111;
        $(document).on('click', '.AddProduct', function () {
            $('#product-area').append('<div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">نام محصول</label> <input required type="text" name="product_name[]" class="form-control mb-2" placeholder=""/> <input required type="hidden" value="0" name="product_id[]"/> </div>' + '<div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">تعداد روز</label><input required type="text" name="product_day[]" class="form-control mb-2" placeholder=""/> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">قیمت</label> <input required type="text" name="product_price[]" class="form-control mb-2" placeholder=""/> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">قیمت با تخفیف</label><input required type="text" name="product_dis[]" placeholder="" class="form-control mb-2"/></div>                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">حجم</label><input required type="text" name="product_volume[]" placeholder="" class="form-control mb-2"/></div> </div>' + '<div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">درصد تخفیف</label><input required type="text" name="product_percent[]" placeholder="" class="form-control mb-2"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">عکس</label><input required type="text" name="product_image[]" id="image-' + GalleryId + '" placeholder="" class="form-control mb-2"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" data-ckfinder-field="image-' + GalleryId + '" class="btn btn-info mt-8 w-100 btn-block"><span class="indicator-label">انتخاب عکس</span></button></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div></div>');
            GalleryId = GalleryId++
        });
        var id = 100;
        $(document).on('click', '.Addfild', function () {
            id++
            $('#fild-area').append(' <div class="py-0 mt-3"> <div class="py-3 d-flex flex-stack flex-wrap"> <div class="d-flex align-items-center collapsible rotate collapsed" data-bs-toggle="collapse" href="#scroll-' + id + '" role="button" aria-exped="false" aria-controls="kt_customer_view_payment_method" aria-expanded="true"> <div class="me-3 rotate-90"><span class="svg-icon svg-icon-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z" fill="currentColor"></path></svg></span> </div><div class="me-3"> <div class="d-flex align-items-center"> آی پی شماره ' + id + ' </div></div></div></div><div id="scroll-' + id + '" class="fs-6 ps-9 collapse" data-bs-parent="#kt_customer_view_payment_method" style=""> <div class="border-bottom mt-5"> <div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">دامنه:</label> <input required="" type="text" name="ip_address[' + id + ']" class="form-control mb-2" placeholder=""> <input required="" type="hidden" value="0" name="ip_id[' + id + ']"> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">آی پی:</label> <input required="" type="text" name="ip_domain[' + id + ']" class="form-control mb-2" placeholder=""> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">پورت</label> <input required="" type="text" value="0" name="ip_port[' + id + ']" class="form-control mb-2" placeholder=""> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">نام</label> <input required="" type="text" name="ip_name[' + id + ']" class="form-control mb-2" placeholder=""> </div></div><div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">نام کاربری:</label> <input required="" type="text" name="ip_user[' + id + ']" class="form-control mb-2" placeholder=""> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">کلمه عبور</label> <input required="" type="text" value="0" name="ip_password[' + id + ']" class="form-control mb-2" placeholder=""> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">وضعیت</label> <select name="ip_status['+id+']" class="form-control " id=""> <option value="1">فعال</option> <option value="0">غیر فعال</option> <option value="0">حذف شده</option> <option value="0">تکمیل شده</option> </select> </div></div><div class="d-flex flex-wrap gap-5">                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">آدرس دامنه</label><input required="" type="text" value="" name="ip_api_domain[' + id + ']" class="form-control mb-2" placeholder=""></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">آیدی اینبوند</label> <input required="" type="text" name="ip_inbound[' + id + ']" class="form-control mb-2" placeholder=""> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">محدودیت</label> <input required="" type="text" value="0" name="ip_limit[' + id + ']" class="form-control mb-2" placeholder=""> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">پورت اینبوند</label><input required="" type="text" value="443" name="port_inbound[' + id + ']" class="form-control mb-2" placeholder=""></div> </div><div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <button type="button" style="position: absolute;left: 20px" data-id="2" class="AddUser btn btn-primary mt-8 w-100 btn-block"> افزودن کاربر </button> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <button type="button" onclick="$(this).parent().parent().parent().parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"> <span class="indicator-label">حذف</span> </button> </div></div><hr> <div id="ips-1" style="padding-left: 10px"></div></div></div></div>');
        });

        $(document).on('click', '.AddUser', function () {
            var val = $(this).data('id');
            $('#scroll-' + val).append('<div class="d-flex flex-wrap gap-5" style="padding: 10px;border-radius: 10px"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">پورت</label><input required type="text" name="user_port[' + val + '][]" class="form-control mb-2" placeholder=""/><input required type="hidden" value="0" name="user_id[' + val + '][]"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">remark</label><input required type="text" name="user_remark[' + val + '][]" class="form-control mb-2" placeholder=""/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">code</label><input required type="text" name="user_code[' + val + '][]" class="form-control mb-2" placeholder=""/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div>');
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
    </script>
@endsection
