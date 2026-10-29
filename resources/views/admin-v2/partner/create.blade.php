@php if (isset($edit)){ $title = ' ویرایش همکار  '; }else{ $title = ' ثبت همکار جدید '; }; $Services = __('order.type') @endphp
@extends('admin-v2.layout.master',['MetaTitle' => $title])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.update partner') :  route('admin.store partner') }}"
                      method="post">
                    @csrf
                    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                        <button type="button" id="submit_btn" class="btn btn-primary">
                            <span class="indicator-label">ذخیره تغییرات</span>
                            <span class="indicator-progress">لطفا صبر کنید...<span
                                        class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>وضعیت</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select class="form-select mb-2" data-control="select2" name="status"
                                        data-hide-search="true" data-placeholder="انتخاب "
                                        id="kt_ecommerce_add_product_status_select">
                                    <option></option>
                                    <option @if(isset($edit) && $edit->status == 1) selected @endif value="1">فعال
                                    </option>
                                    <option @if(isset($edit) && $edit->status == 0) selected @endif value="0">غیرفعال
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
                                    <option {{isset($edit) && $edit->category_id == 5 ? 'selected' : '' }} value="5">
                                        آسیا
                                    </option>
                                    <option {{isset($edit) && $edit->category_id == 6 ? 'selected' : '' }} value="6">
                                        آفریقا
                                    </option>
                                    <option {{isset($edit) && $edit->category_id == 7 ? 'selected' : '' }} value="7">
                                        آمریکا
                                    </option>
                                    <option {{isset($edit) && $edit->category_id == 8 ? 'selected' : '' }} value="8">
                                        اقیانوسیه ( اسپانیا )
                                    </option>
                                    <option {{isset($edit) && $edit->category_id == 9 ? 'selected' : '' }} value="9">
                                        اروپا
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
                                                    <label class="required form-label">نام و نام خانوادگی</label>
                                                    <input type="text" name="name" class="form-control mb-2"
                                                           placeholder="نام به فارسی"
                                                           value="{{isset($edit) ? $edit->name : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">شماره موبایل</label>
                                                    <input type="text" name="mobile" class="form-control mb-2"
                                                           placeholder="نام به انگلیسی"
                                                           value="{{isset($edit) ? $edit->mobile : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">ایمیل</label>
                                                    <input type="text" name="email" class="form-control mb-2"
                                                           value="{{isset($admin) ? $admin->email : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کلمه عبور</label>
                                                    <input type="text" name="password" class="form-control mb-2"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نوع درصد</label>
                                                    <select class="form-select mb-2" data-control="select2"
                                                            name="percent_type" data-hide-search="true"
                                                            data-placeholder="انتخاب ">
                                                        <option @if(isset($edit) && $edit->percent_type == 2) selected
                                                                @endif value="2">از سود
                                                        </option>
                                                        <option @if(isset($edit) && $edit->percent_type == 1) selected
                                                                @endif value="1">کل
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">مقدار درصد</label>
                                                    <input type="text" name="percent" class="form-control mb-2"
                                                           value="{{isset($edit) ? $edit->percent : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نوع سرویس</label>
                                                    <select class="form-select mb-2" multiple data-control="select2"
                                                            name="services[]" data-hide-search="true"
                                                            data-placeholder="انتخاب ">
                                                        @foreach($Services as $key => $service)
                                                            <option  value="{{$key}}">{{$service}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نوع تسویه</label>
                                                    <select class="form-select mb-2" data-control="select2"
                                                            name="deposit_type" data-hide-search="true"
                                                            data-placeholder="انتخاب ">
                                                        <option @if(isset($edit) && $edit->deposit_type == 3) selected
                                                                @endif value="3">ماهانه
                                                        </option>
                                                        <option @if(isset($edit) && $edit->deposit_type == 2) selected
                                                                @endif value="2">هفتگی
                                                        </option>
                                                        <option @if(isset($edit) && $edit->deposit_type == 1) selected
                                                                @endif value="1">روازنه
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($edit) ? $edit->detail : '' !!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($edit) ? $edit->detail : ''}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Tab content-->
                        <div class="d-flex justify-content-end">
                            <button type="button" id="submit_btn" class="btn btn-primary">
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
