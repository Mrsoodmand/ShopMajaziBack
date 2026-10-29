@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.update role') : route('admin.store role')}}" method="post">
                    @csrf
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#products">دسترسی
                                    ها</a>
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
                                                    <label class="required form-label">نام</label>
                                                    <input type="text" name="faName" class="form-control mb-2"
                                                           placeholder="نام به فارسی" value="{{isset($edit) ? $edit->faName : ''}}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام به انگلیسی</label>
                                                    <input type="text" name="name" class="form-control mb-2"
                                                           placeholder="نام به انگلیسی" value="{{isset($edit) ? $edit->name : ''}}"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(isset($edit))
                                <input type="hidden" name="id" class="form-control mb-2" value="{{$edit->id}}"/>
                            @endif
                            <div class="tab-pane fade" id="products" role="tab-panel">
                                <div class="d-flex flex-column gap-7 gap-lg-10">
                                    <div class="card card-flush py-4">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h2> دسترسی ها</h2>
                                                <div style="left: 15px;position: absolute">
                                                    <button type="button" class="AddProduct btn btn-sm btn-primary">
                                                        بروز رسانی
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="py-2">
                                                @foreach($Categories as $category)
                                                    <h5 class="mb-5">{{$category->name}}</h5>
                                                    <div class="row mb-5">
                                                        @foreach($Permissions->where('category_id',$category->id) as $permission)
                                                            <div class="col-4 mb-2">
                                                                <label class="form-check form-check-inline form-check-solid me-5">
                                                                    <input
                                                                            @if(isset($edit))
                                                                                @foreach($edit->permissions as $value)
                                                                                    @if($value->id == $permission->id)
                                                                                        checked="checked"
                                                                            @endif
                                                                            @endforeach
                                                                            @endif

                                                                            class="form-check-input" name="permissions[]" type="checkbox" value="{{$permission->id}}">
                                                                    <span class="fw-bold ps-2 fs-6">{{$permission->faName}}</span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" id="submit_btn" class="btn btn-primary submit_btn">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <!--end::Button-->
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
            $('#product-area').after('<div class="border-bottom mt-5"><div class="d-flex flex-wrap gap-5"><div class="fv-row w-100 flex-md-root fv-plugins-icon-container"><label class="required form-label">اپل آیدی</label> <input required type="text" name="appleid[]" class="form-control mb-2" placeholder="اپل آیدی"/><input required type="hidden" value="0" name="id[]"/></div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">کلمه عبور</label> <input required type="text" name="password[]" class="form-control mb-2" placeholder="کلمه عبور"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">تاریخ تولد</label> <input required type="text" name="brithday[]" class="form-control mb-2" placeholder="تاریخ تولد"/> </div> </div> <div class="d-flex flex-wrap gap-5"> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 1</label> <input required type="text" name="q1[]" placeholder="سوالات امنیتی 1" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 2</label> <input required type="text" name="q2[]" placeholder="سوالات امنیتی 2" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <label class="required form-label">سوالات امنیتی 3</label> <input required type="text" name="q3[]" placeholder="سوالات امنیتی 3" class="form-control mb-2"/> </div> <div class="fv-row w-100 flex-md-root fv-plugins-icon-container"> <button type="button"onclick="$(this).parent().parent().parent().remove(); "class="btn btn-danger mt-8 w-100 btn-block"> <span class="indicator-label">حذف</span> </button> </div> </div> </div>');
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
