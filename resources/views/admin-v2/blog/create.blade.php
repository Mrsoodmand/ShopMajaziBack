@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @include('admin-v2.layout.sesstion')
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.update blog') :  route('admin.store blog') }}"
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
                                <input type="text" id="gallery_image_input_1" name="image_gallery"
                                       class="form-control mb-2"
                                       placeholder="لینک تصویر"
                                       value="{{isset($edit) ? $edit->image : '' }}">
                                <img alt="image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     id="gallery_image_input_per_1"
                                     src="{{isset($edit) && !is_null($edit->image) ? secure_url($edit->image) : '' }}">
                                <div class="text-muted fs-7">سایز عکس باید 388*215 باشد
                                </div>
                                <br>
                                <button class="btn btn-sm btn-primary" type="button" id="img-per-1">مشاهده تصویر
                                </button>
                            </div>
                        </div>
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>تصویر شاخص</h2>
                                    <div style="left: 15px;position: absolute">
                                        <button type="button" data-ckfinder-field="gallery_image_input_2"
                                                class="btn btn-sm btn-primary">افزودن تصویر
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <input type="text" id="gallery_image_input_2" name="image_gallery_2"
                                       class="form-control mb-2"
                                       placeholder="لینک تصویر"
                                       value="{{isset($edit) ? $edit->image_big : '' }}">
                                <img alt="image" width="150" height="150" id="gallery_image_input_per_2"
                                     style="margin-top:20px;margin-bottom: 20px"
                                     src="{{isset($edit) && !is_null($edit->image_big) ? secure_url($edit->image_big) : '' }}">
                                <div class="text-muted fs-7">
                                    فقط فایل های تصویری
                                    *.jpg و *.jpeg پذیرفته می شوند
                                    <hr>
                                    سایز عکس باید 800 * 450 باشد
                                </div>
                                <br>
                                <button class="btn btn-sm btn-primary" type="button" id="img-per-2">مشاهده تصویر
                                </button>
                            </div>
                        </div>

                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>نکات مهم آپلود محتوا</h2>
                                </div>
                            </div>
                            <div class="card-body text-center pt-0">
                                <div class="text-muted fs-7">
                                    1- محتوا را در یکبار در notpad کپی کنید و محتوا رو از نوت پد داخل ادیتور Past کنید
                                    <hr>
                                    حجم تمامی عکس ها را در سایت
                                    <a href="https://tinypng.com/" target="_blank"> tinypng</a>
                                    کم و بهینه کنید
                                    <hr>
                                    تمامی عکس ها فرمت Jpg باشد.
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" id="submit_btn"
                                    class="btn btn-primary w-100">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...<span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
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
                                   href="#article">محتوا</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#faq">سوالات
                                    متداول</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#realted_blog">مقالات مرتبط</a>
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
                                                    <label class="required form-label">عنوان</label>
                                                    <input type="text" name="title" class="form-control mb-2"
                                                           placeholder="نام به فارسی"
                                                           value="{{isset($edit) ? $edit->title : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">لینک مطلب</label>
                                                    <input type="text" name="slug" class="form-control mb-2 ChangeName"
                                                           placeholder="نام به انگلیسی"
                                                           value="{{isset($edit) ? $edit->slug : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">تاریخ انتشار</label>
                                                    <input type="text" name="share_date"
                                                           value="{{(isset($edit) ? $jdf->jdate('Y/m/d',strtotime($edit->share_at)) :'')}}"
                                                           id="share_time" class="form-control mb-2 share_time"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">ساعت انتشار</label>
                                                    <input type="time" name="share_time"
                                                           value="{{(isset($edit) ? \Carbon\Carbon::parse($edit->share_at)->format('H:i') :'')}}"
                                                           class="form-control mb-2"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">وضعیت</label>
                                                    <select name="status" id="" class="form-control mb-2">
                                                        <option {{isset($edit) && $edit->status == 2 ? 'selected': ''}} value="2">
                                                            منتشر شود
                                                        </option>
                                                        <option {{isset($edit) && $edit->status == 1 ? 'selected': ''}} value="1">
                                                            بعدا منتشر شود
                                                        </option>
                                                        <option {{isset($edit) && $edit->status == -1 ? 'selected': ''}} value="-1">
                                                            منتشر نشود
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>کد JSON</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="d-flex flex-wrap gap-5 row">
                                                <div class="col-12 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">عنوان</label>
                                                    @php
                                                        if (isset($edit)){
$json = json_encode([
    'title' => $edit->title,
    'image' => url($edit->image),
    'short_detail' => $edit->short_detail,
    'slug' => $edit->slug,
]);
}
                                                    @endphp
                                                    <textarea name="" onclick="copyUserLink('copy')"  class="form-control" id="copy" cols="30"
                                                              rows="10">{{isset($json) ? $json : ''}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="article" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>محتوا</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضحیات کوتاه</label>
                                                    <textarea id="short_detail" name="short_detail" class="form-control"
                                                              cols="30"
                                                              rows="10">{!! isset($edit) ? $edit->short_detail : '' !!}</textarea>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات بلند</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($edit) ? $edit->detail :''!!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($edit) ? $edit->detail :''}}">
                                                </div>
                                            </div>
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
                                                @else
                                                    <div class="row">
                                                        <div class="col-lg-5">
                                                            <label class="required form-label">سوال</label>
                                                            <input type="text" name="question[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="required form-label">جواب</label>
                                                            <input type="text" name="answer[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
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
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="realted_blog" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2>لینک های مرتبط</h2>
                                            </div>
                                            <div class="card-toolbar">
                                                <button type="button" class="btn btn-warning add-realted">
                                                    افزودن
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="realted-area">
                                                @if(isset($edit) && !is_null($edit->faq) && $edit->faq != 'null')
                                                    @foreach(json_decode($edit->faq,true) as $item)
                                                        <div class="row">
                                                            <div class="col-lg-3">
                                                                <label class="required form-label">عنوان</label>
                                                                <input type="text" name="realted_title[]"
                                                                       class="form-control mb-2"
                                                                       placeholder=""
                                                                       value=""/>
                                                            </div>
                                                            <div class="col-lg-5">
                                                                <label class="required form-label">متن</label>
                                                                <input type="text" name="realted_text[]"
                                                                       class="form-control mb-2"
                                                                       placeholder=""
                                                                       value=""/>
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <label class="required form-label">لینک</label>
                                                                <input type="text" name="realted_link[]"
                                                                       class="form-control mb-2"
                                                                       placeholder=""
                                                                       value=""/>
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
                                                @else
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <label class="required form-label">عنوان</label>
                                                            <input type="text" name="realted_title[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-5">
                                                            <label class="required form-label">متن</label>
                                                            <input type="text" name="realted_text[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <label class="required form-label">لینک</label>
                                                            <input type="text" name="realted_link[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
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
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')
    <link href="{{URL::asset('admin/js/all.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://star-pet.co/plugins/datepicker/kamadatepicker.min.css">
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
    <script src="https://star-pet.co/plugins/datepicker/kamadatepicker.min.js"></script>

    <script>

        function copyUserLink(id) {
            var data = $('#' + id).val();
            navigator.clipboard.writeText(data);
           alert('copy shod')
        }

        $(document).on('click', '#img-per-1', function () {
            $('#gallery_image_input_per_1').attr('src', $('#gallery_image_input_1').val())
        });
        $(document).on('click', '#img-per-2', function () {
            $('#gallery_image_input_per_2').attr('src', $('#gallery_image_input_2').val())
        });
        $(document).on('keyup', '.ChangeName', function () {
            var text = $(this).val().replace(/ /g, "-")
            $(this).val(text);
        });

        var GalleryId = 111111;
        $(document).on('click', '.AddProduct', function () {
            $('#product-area').append('<div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">نام محصول</label> <input required type="text" name="product_name[]" class="form-control mb-2" placeholder=""/> <input required type="hidden" value="0" name="product_id[]"/> </div>' + '<div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">تعداد روز</label><input required type="text" name="product_day[]" class="form-control mb-2" placeholder=""/> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">قیمت</label> <input required type="text" name="product_price[]" class="form-control mb-2" placeholder=""/> </div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">قیمت با تخفیف</label><input required type="text" name="product_dis[]" placeholder="" class="form-control mb-2"/></div>                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">حجم</label><input required type="text" name="product_volume[]" placeholder="" class="form-control mb-2"/></div> </div>' + '<div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">درصد تخفیف</label><input required type="text" name="product_percent[]" placeholder="" class="form-control mb-2"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">عکس</label><input required type="text" name="product_image[]" id="image-' + GalleryId + '" placeholder="" class="form-control mb-2"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" data-ckfinder-field="image-' + GalleryId + '" class="btn btn-info mt-8 w-100 btn-block"><span class="indicator-label">انتخاب عکس</span></button></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div></div>');
            GalleryId = GalleryId++
        });
        var id = 1;
        $(document).on('click', '.Addfild', function () {
            id++
            $('#fild-area').append('<div class="py-0 mt-3"><div class="py-3 d-flex flex-stack flex-wrap">' + '<div class="d-flex align-items-center collapsible rotate collapsed" data-bs-toggle="collapse" href="#scroll-' + id + '" role="button" aria-exped="false" aria-controls="kt_customer_view_payment_method" aria-expanded="false"> <div class="me-3 rotate-90"><span class="svg-icon svg-icon-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12.6343 12.5657L8.45001 16.75C8.0358 17.1642 8.0358 17.8358 8.45001 18.25C8.86423 18.6642 9.5358 18.6642 9.95001 18.25L15.4929 12.7071C15.8834 12.3166 15.8834 11.6834 15.4929 11.2929L9.95001 5.75C9.5358 5.33579 8.86423 5.33579 8.45001 5.75C8.0358 6.16421 8.0358 6.83579 8.45001 7.25L12.6343 11.4343C12.9467 11.7467 12.9467 12.2533 12.6343 12.5657Z" fill="currentColor"></path></svg></span> </div> <div class="me-3"> <div class="d-flex align-items-center"> آی پی شماره ' + id + '</div></div></div></div>' + '<div id="scroll-' + id + '" class="fs-6 ps-9 collapse" data-bs-parent="#kt_customer_view_payment_method" style=""><div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">آدرس آی پی</label><input required type="text" name="ip_address[' + id + ']" class="form-control mb-2" placeholder=""/><input required type="hidden" value="0" name="ip_id[' + id + ']"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">آدرس دامنه</label><input required type="text" name="ip_domain[' + id + ']" class="form-control mb-2" placeholder=""/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">تعداد</label><input required type="text" value="0" name="ip_count[' + id + ']" class="form-control mb-2" placeholder=""/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">وضعیت</label><select name="ip_status[' + id + ']" class="form-control " id=""><option value="1">فعال</option><option value="0">غیر فعال</option></select></div></div>' + '<div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" style="position: absolute;left: 20px" data-id="' + id + '" class="AddUser btn btn-primary mt-8 w-100 btn-block"> افزودن کاربر </button></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().parent().parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div>' + '<hr><div id="ips-1" style="padding-left: 10px"></div></div></div></div>');
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
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images',
        });

        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#changed').val(evt.editor.getData())

        })
        kamaDatepicker('share_time');

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
        });

        $(document).on('click', '.add-realted', function () {
            $('#realted-area').after(
                `<div class="row">
                                                        <div class="col-lg-3">
                                                            <label class="required form-label">عنوان</label>
                                                            <input type="text" name="realted_title[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-5">
                                                            <label class="required form-label">متن</label>
                                                            <input type="text" name="realted_text[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <label class="required form-label">لینک</label>
                                                            <input type="text" name="realted_link[]"
                                                                   class="form-control mb-2"
                                                                   placeholder=""
                                                                   value=""/>
                                                        </div>
                                                        <div class="col-lg-1">
                                                            <br>
                                                            <button type="button" class="btn btn-md btn-danger mt-2"
                                                                    style="width: 100%"
                                                                    onclick="$(this).parent().parent().remove()">
                                                                x
                                                            </button>
                                                        </div>
                                                    </div>`
            );
        });
    </script>
@endsection
