@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row" action="{{isset($edit) ? route('admin.update category') : route('admin.store category')}}" method="post">
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
                                <input type="text" name="image_gallery" class="form-control mb-2" id="gallery_image_input_1"
                                       placeholder="لینک تصویر"
                                       value="{{isset($edit) && !is_null($edit->image)? $edit->image : '' }}">
                                <img alt="image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     src="{{isset($edit) && !is_null($edit->image)? $edit->image : '' }}">
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
                        <aside style="position: sticky;top: 6rem">
                            <div class="d-flex justify-content-end">
                                <button type="button" id="submit_btn" class="btn w-100 btn-primary">
                                    <span class="indicator-label">ذخیره تغییرات</span>
                                    <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                </button>
                            </div>
                        </aside>
                    </div>
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#seo">سئو</a>
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
                                                    <label class="required form-label">نام فارسی</label>
                                                    <input type="text" name="faName" class="form-control mb-2" placeholder="نام به فارسی" value="{{isset($edit) ? $edit->name : ''}}"/>
                                                    <input type="hidden" name="type" value="{{isset($edit) ? $edit->type : $type}}"/>
                                                    <input type="hidden" name="id" value="{{isset($edit) ? $edit->id : ''}}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام انگلیسی</label>
                                                    <input type="text" name="enName" class="form-control mb-2"  value="{{isset($edit) ? $edit->enName : ''}}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">لینک</label>
                                                    <input type="text" name="slug" class="form-control mb-2"  value="{{isset($edit) ? $edit->slug : ''}}"/>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضحیات کوتاه</label>
                                                    <textarea id="short_detail" name="short_detail" class="form-control" cols="30" rows="10">{!! isset($edit) ? $edit->short_detail : '' !!}</textarea>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات بلند</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($edit) ? $edit->long_detail :''!!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($edit) ? $edit->long_detail :''}}">
                                                </div>
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
                                            <div>
                                                <label class="form-label">توضیحات</label>
                                                <textarea name="seo" class="form-control" cols="30"
                                                          rows="10">{!! isset($edit) ? $edit->meta : '' !!}</textarea>
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
            $('#product-area').after('<div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">اپل آیدی</label> <input required type="text" name="appleid[]" class="form-control mb-2" placeholder="اپل آیدی"/><input required type="hidden" value="0" name="id[]"/></div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">کلمه عبور</label> <input required type="text" name="password[]" class="form-control mb-2" placeholder="کلمه عبور"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">تاریخ تولد</label> <input required type="text" name="brithday[]" class="form-control mb-2" placeholder="تاریخ تولد"/> </div> </div> <div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 1</label> <input required type="text" name="q1[]" placeholder="سوالات امنیتی 1" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 2</label> <input required type="text" name="q2[]" placeholder="سوالات امنیتی 2" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 3</label> <input required type="text" name="q3[]" placeholder="سوالات امنیتی 3" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <button type="button"onclick="$(this).parent().parent().parent().remove(); "class="btn btn-danger mt-8 w-100 btn-block"> <span class="indicator-label">حذف</span> </button> </div> </div> </div>');
        });
        var id = 1001;
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

        });

        $("#gallery_image_input_1").on("input", function() {
            $('#preview_image').attr('src',$(this).val())
        });
        // $('#gallery_image_input_1').autofocus(function (){
        //    $('#preview_image').attr('src',$(this).val())
        // });

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
