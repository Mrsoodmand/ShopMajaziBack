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
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row" action="{{route('admin.google-voice-submit')}}" method="post">
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
                                       value="{{$Setting->image}}">
                                <img alt="image" id="preview_image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     src="{{$Setting->image }}">
                                <div class="text-muted fs-7">تصویر بندانگشتی محصول را تنظیم کنید. فقط فایل های تصویری
                                    *.png، *.jpg و *.jpeg پذیرفته می شوند
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" id="submit_btn" class="btn w-100 btn-primary">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <!--end::Button-->
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#products">گوگل ویس</a>
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
                                                    <input type="text" name="h1" class="form-control mb-2" placeholder="نام به فارسی" value="{{$Setting->h1}}"/>
                                                </div>
                                            </div>
                                            @php $Price = json_decode($Setting->detail,true) @endphp
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">قیمت گوگل ویس(دلار)</label>
                                                    <input type="text" name="custom_price" class="form-control mb-2"  value="{{!is_null($Price) ? $Price['custom_price'] : ''}}"/>
                                                </div>
                                            </div>
                                            {{-- Sales on the new site (Next.js) come from Numberland; see App\Services\GoogleVoiceNumbers. --}}
                                            <div class="separator my-6"></div>
                                            <h4 class="mb-1">فروش از نامبرلند (سایت جدید)</h4>
                                            <div class="text-muted fs-7 mb-4">شماره‌ها و قیمت پایه از نامبرلند خوانده می‌شود. قیمت فروش = قیمت نامبرلند + درصد سود، گرد شده به ۱۰۰۰ تومان بالاتر.</div>
                                            <div class="d-flex flex-wrap gap-5">
                                                <div class="fv-row w-100 flex-md-root">
                                                    <label class="form-label">وضعیت فروش</label>
                                                    <select name="numberland_enabled" class="form-select mb-2">
                                                        <option value="1" {{ ($Price['numberland_enabled'] ?? 1) == 1 ? 'selected' : '' }}>فعال</option>
                                                        <option value="0" {{ ($Price['numberland_enabled'] ?? 1) == 0 ? 'selected' : '' }}>غیرفعال</option>
                                                    </select>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root">
                                                    <label class="form-label">درصد سود روی قیمت نامبرلند</label>
                                                    <input type="number" min="0" max="500" step="0.5" name="numberland_markup" class="form-control mb-2"
                                                           value="{{ $Price['numberland_markup'] ?? config('services.numberland.gv_markup', 20) }}"/>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضحیات کوتاه</label>
                                                    <textarea id="short_detail"  class="form-control" cols="30" rows="10">{!! $Setting->short_detail !!}</textarea>
                                                    <input id="short_changed" name="short_detail" type="hidden"
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
                                                        افزودن گوگل ویس
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div id="product-area"></div>
                                            @if(isset($GoogleVoice) && count($GoogleVoice) != 0)
                                                @foreach($GoogleVoice as $item)
                                                    @php $Data = json_decode($Decrypt->decrypt($item->serial,$item->code),true);@endphp
                                                    <div class="border-bottom mt-5">
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">شماره گوگل ویس</label>
                                                                <input required type="text" name="googlevoice_number[]" value="{{$Data['number']}}" style="direction: ltr" class="form-control mb-2 ltr" placeholder="شماره گوگل ویس"/>
                                                                <input required type="hidden" value="{{$item->id}}" name="googlevoice_id[]"/>
                                                            </div>
                                                            <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">ایمیل</label>
                                                                <input required type="text" name="googlevoice_email[]" value="{{$Data['email']}}" class="form-control mb-2" placeholder="ایمیل"/>
                                                            </div>
                                                            <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">کلمه عبور</label>
                                                                <input required type="text" name="googlevoice_password[]" value="{{$Data['password']}}" class="form-control mb-2" placeholder="کلمه عبور"/>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-5">
                                                            <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">ایمیل پشتیبانی</label>
                                                                <input required type="text" name="googlevoice_backup[]" value="{{$Data['backup-email']}}" class="form-control mb-2" placeholder="ایمیل پشتیبانی"/>
                                                            </div>
                                                            <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                <label class="required form-label">کد دومرحله ای</label>
                                                                <input required type="text" name="googlevoice_step2[]" value="{{$Data['2fa']}}" placeholder="کد دومرحله ای" class="form-control mb-2"/>
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
                                                <div class="border-bottom mt-5">
                                                    <div class="d-flex flex-wrap gap-5">
                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                            <label class="required form-label">شماره گوگل ویس</label>
                                                            <input required type="text" name="googlevoice_number[]" class="form-control mb-2" style="direction: ltr" placeholder="شماره گوگل ویس"/>
                                                            <input required type="hidden" value="0" name="googlevoice_id[]"/>
                                                        </div>
                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                            <label class="required form-label">ایمیل</label>
                                                            <input required type="text" name="googlevoice_email[]" class="form-control mb-2" placeholder="ایمیل"/>
                                                        </div>
                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                            <label class="required form-label">کلمه عبور</label>
                                                            <input required type="text" name="googlevoice_password[]" class="form-control mb-2" placeholder="کلمه عبور"/>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-5">
                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                            <label class="required form-label">ایمیل پشتیبانی</label>
                                                            <input required type="text" name="googlevoice_backup[]" class="form-control mb-2" placeholder="ایمیل پشتیبانی"/>
                                                        </div>
                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                            <label class="required form-label">کد دومرحله ای</label>
                                                            <input required type="text" name="googlevoice_step2[]" placeholder="کد دومرحله ای" class="form-control mb-2"/>
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
            $('#product-area').after(' <div class="border-bottom mt-5"> <div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">شماره گوگل ویس</label><input required type="text" name="googlevoice_number[]" style="direction: ltr" class="form-control mb-2" value="+1 "/><input required type="hidden" value="0" name="googlevoice_id[]"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">ایمیل</label><input required type="text" name="googlevoice_email[]" class="form-control mb-2" placeholder="ایمیل"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">کلمه عبور</label><input required type="text" name="googlevoice_password[]" class="form-control mb-2" placeholder="کلمه عبور"/></div></div><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">ایمیل ریکاوری</label><input required type="text" name="googlevoice_backup[]" value="shopmajazi.com@gmail.com" class="form-control mb-2" placeholder="ایمیل پشتیبانی"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">کد دومرحله ای</label><input required type="text" name="googlevoice_step2[]" placeholder="کد دومرحله ای" class="form-control mb-2"/></div><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><button type="button" onclick="$(this).parent().parent().parent().remove(); " class="btn btn-danger mt-8 w-100 btn-block"><span class="indicator-label">حذف</span></button></div></div></div>');
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


        var editor = CKEDITOR.replace('short_detail', {
            height: 300,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });

        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#short_changed').val(evt.editor.getData())

        })

         editor = CKEDITOR.replace('detail', {
            height: 800,
            contentsLangDirection: 'rtl',
            filebrowserBrowseUrl: '/admin/ckfinder/browser',
            filebrowserUploadUrl: '/admin/ckfinder/connector?command=QuickUpload&type=Images',
            filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images'
        });

        editor.on('change', function (evt)/* evt yek moteghaier ast ke har esmi ke dos dari mishe gozasht */ {
            $('#changed').val(evt.editor.getData())

        })
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
