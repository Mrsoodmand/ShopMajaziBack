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
                      action="{{route('admin.apple-id-submit')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-3 d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                            <aside style="position: sticky;top: 6rem">
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
                                               value="{{$Setting->image}}">
                                        <img alt="image" id="preview_image" width="150" height="150"
                                             style="margin-top:20px;margin-bottom: 20px"
                                             src="{{$Setting->image }}">
                                        <div class="text-muted fs-7">تصویر بندانگشتی محصول را تنظیم کنید. فقط فایل های
                                            تصویری
                                            *.png، *.jpg و *.jpeg پذیرفته می شوند
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button"
                                            id="submit_btn"
                                            class="btn w-100 btn-primary">
                                        <span class="indicator-label">ذخیره تغییرات</span>
                                        <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                    </button>
                                    <!--end::Button-->
                                </div>
                            </aside>
                        </div>
                        <div class="col-9 d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                            <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#products">اپل
                                        آیدی</a>
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
                                                        <label class="required form-label">تگ H1</label>
                                                        <input type="text" name="h1" class="form-control mb-2"
                                                               placeholder="نام به فارسی" value="{{$Setting->h1}}"/>
                                                    </div>
                                                </div>
                                                @php $Price = json_decode($Setting->detail,true) @endphp
                                                <div class="d-flex flex-wrap gap-5">
                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                        <label class="required form-label">قیمت اپل آیدی سفارش</label>
                                                        <input type="text" name="custom_price" class="form-control mb-2"
                                                               value="{{!is_null($Price) ? $Price['custom_price'] : ''}}"/>
                                                    </div>
                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                        <label class="required form-label">قیمت اپل آیدی آماده</label>
                                                        <input type="text" name="normal_price" class="form-control mb-2"
                                                               value="{{!is_null($Price) ? $Price['normal_price'] : ''}}"/>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                        <label class="form-label">توضحیات کوتاه</label>
                                                        <textarea id="short_detail_content" class="form-control" cols="30"
                                                                  rows="10">{!! $Setting->short_detail !!}</textarea>
                                                        <input id="short_detail_changed" name="short_detail" type="hidden"
                                                               value="{{$Setting->short_detail}}">
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                        <label class="form-label">توضیحات بلند</label>
                                                        <textarea id="detail" class="form-control" cols="30"
                                                                  rows="10">{!! $Setting->long_detail !!}</textarea>
                                                        <input id="changed" name="detail" type="hidden"
                                                               value="{{$Setting->long_detail}}">
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
                                                            افزودن اپل آیدی
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body pt-0">
                                                <div class="row">
                                                    <div id="product-area"></div>
                                                    @if(isset($AppleId) && count($AppleId) != 0)
                                                        @foreach($AppleId as $appleid)
                                                            @php $Data = json_decode($Decrypt->decrypt($appleid->serial,$appleid->code),true)@endphp
                                                            <div class="col-6">
                                                                <div class="border-bottom mt-5">
                                                                    <div class="d-flex flex-wrap gap-5">
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">اپل
                                                                                آیدی</label>
                                                                            <input required type="text" name="appleid[]"
                                                                                   value="{{$Data['appleid']}}"
                                                                                   class="form-control mb-2"
                                                                                   placeholder="اپل آیدی"/>
                                                                            <input required type="hidden"
                                                                                   value="{{$appleid->id}}"
                                                                                   name="id[]"/>
                                                                        </div>
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">کلمه
                                                                                عبور</label>
                                                                            <input required type="text" name="password[]"
                                                                                   value="{{$Data['password']}}"
                                                                                   class="form-control mb-2"
                                                                                   placeholder="کلمه عبور"/>
                                                                        </div>
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">تاریخ
                                                                                تولد</label>
                                                                            <input required type="text" name="brithday[]"
                                                                                   value="{{$Data['brithday']}}"
                                                                                   class="form-control mb-2"
                                                                                   placeholder="تاریخ تولد"/>
                                                                        </div>
                                                                    </div>

                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">سوالات امنیتی
                                                                            1</label>
                                                                        <input required type="text" name="q1[]"
                                                                               value="{{$Data['q1']}}"
                                                                               placeholder="سوالات امنیتی 1"
                                                                               class="form-control mb-2"/>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">سوالات امنیتی
                                                                            2</label>
                                                                        <input required type="text" name="q2[]"
                                                                               value="{{$Data['q2']}}"
                                                                               placeholder="سوالات امنیتی 2"
                                                                               class="form-control mb-2"/>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">سوالات امنیتی
                                                                            3</label>
                                                                        <input required type="text" name="q3[]"
                                                                               value="{{$Data['q3']}}"
                                                                               placeholder="سوالات امنیتی 3"
                                                                               class="form-control mb-2"/>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
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

                                                        <div class="col-6">
                                                            <div class="border-bottom mt-5">
                                                                <div class="d-flex flex-wrap gap-5">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">اپل آیدی</label>
                                                                        <input required type="text" name="appleid[]"
                                                                               class="form-control mb-2"
                                                                               placeholder="اپل آیدی"/>
                                                                        <input required type="hidden" value="0"
                                                                               name="id[]"/>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">کلمه عبور</label>
                                                                        <input required type="text" name="password[]"
                                                                               class="form-control mb-2"
                                                                               placeholder="کلمه عبور"/>
                                                                    </div>
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">تاریخ
                                                                            تولد</label>
                                                                        <input required type="text" name="brithday[]"
                                                                               class="form-control mb-2"
                                                                               placeholder="تاریخ تولد"/>
                                                                    </div>
                                                                </div>
                                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                    <label class="required form-label">سوالات امنیتی
                                                                        1</label>
                                                                    <input required type="text" style="direction: ltr" value="What was the name of your first pet? " name="q1[]"
                                                                           placeholder="سوالات امنیتی 1"
                                                                           class="form-control mb-2"/>
                                                                </div>
                                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                    <label class="required form-label">سوالات امنیتی
                                                                        2</label>
                                                                    <input required type="text" style="direction: ltr" name="q2[]"
                                                                           value="what is your dream job? "
                                                                           placeholder="سوالات امنیتی 2"
                                                                           class="form-control mb-2"/>
                                                                </div>
                                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                    <label class="required form-label">سوالات امنیتی
                                                                        3</label>
                                                                    <input required type="text" style="direction: ltr" name="q3[]"
                                                                           placeholder="سوالات امنیتی 3"
                                                                           value="in what city did your parents meet? "
                                                                           class="form-control mb-2"/>
                                                                </div>
                                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                    <button type="button"
                                                                            onclick="$(this).parent().parent().parent().remove(); "
                                                                            class="btn btn-danger mt-8 w-100 btn-block">
                                                                        <span class="indicator-label">حذف</span>
                                                                    </button>
                                                                </div>
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

                </form>
            </div>
        </div>
    </div>
@endsection
@section('header')
    <link href="{{URL::asset('admin/js/all.css')}}" rel="stylesheet" type="text/css">

@endsection
@section('footer')
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/datatables/datatables.bundle.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/plugins/custom/formrepeater/formrepeater.bundle.js"></script>--}}
    <!--end::Page Vendors Javascript-->
    <!--begin::Page سفارشی Javascript(used by this page)-->
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/ecommerce/catalog/save-product.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/widgets.bundle.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/widgets.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/apps/chat/chat.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/upgrade-plan.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/create-app.js"></script>--}}
    {{--    <script src="{{secure_url('/admin-v2')}}/assets/js/custom/utilities/modals/users-search.js"></script>--}}

    @include('admin.layout.submit-form')
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{secure_url('admin/js/all.js')}}" type="text/javascript"></script>

    <script>
        $(document).on('click', '.AddProduct', function () {
            $('#product-area').after('<div class="col-6"><div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">اپل آیدی</label><input required type="text" name="appleid[]" class="form-control mb-2" placeholder="اپل آیدی"> <input required type="hidden" value="0" name="id[]"></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">کلمه عبور</label><input required type="text" name="password[]" class="form-control mb-2" placeholder="کلمه عبور"></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">تاریخ تولد</label><input required type="text" name="brithday[]" class="form-control mb-2" placeholder="تاریخ تولد"></div></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">سوالات امنیتی 1</label><input required type="text" style="direction:ltr" value="What is the First name of your best friend in high school? " name="q1[]" placeholder="سوالات امنیتی 1" class="form-control mb-2"></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">سوالات امنیتی 2</label><input required type="text" style="direction:ltr" name="q2[]" value="what is your dream job? " placeholder="سوالات امنیتی 2" class="form-control mb-2"></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">سوالات امنیتی 3</label><input required type="text" style="direction:ltr" name="q3[]" placeholder="سوالات امنیتی 3" value="in what city did your parents meet? " class="form-control mb-2"></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().parent().remove()" class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div></div>')
        });
        var id = 1001;
        $(document).on('click', '.AddNote', function () {
            id++;
            $('#note-area').append('' + '<div class="row">' + '<div class="col-6 col-lg-5">' + '<label class="required form-label">عنوان</label>' + '<input type="text" name="Notekey[]" class="form-control mb-2" placeholder="عنوان" />' + '</div>' + '<div class="col-6 col-lg-5">' + '<label class="required form-label">Col</label>' + '<input type="text" name="notes[]" class="form-control mb-2" placeholder="اندازه"/>' + '</div>' + '<div class="col-6 col-lg-2">' + '<button type="button" onclick="$(this).parent().parent().remove()" class="btn btn-danger mt-9">' + '<span class="indicator-label">حذف</span>' + '</button>' + '</div>' + '</div>');
        });

        $(document).on('change', '.filed_type', function () {
            var val = $(this).val();
            var logId = $(this).data('id');
            if (val === 'select_box') {
                $('#filed-area-' + logId).show()
            } else {
                $('#filed-area-' + logId).hide()
            }
        });


        var editor = CKEDITOR.replace('short_detail_content', {
            height: 500,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });
        editor.on('change', function (evt) {

            $('#short_detail_changed').val(evt.editor.getData());

        });

        editor = CKEDITOR.replace('detail', {
            height: 800,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });
        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#changed').val(evt.editor.getData());
        });


        $("#gallery_image_input_1").on("input", function () {
            $('#preview_image').attr('src', $(this).val())
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
