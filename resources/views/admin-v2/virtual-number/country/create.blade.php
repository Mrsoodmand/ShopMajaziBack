@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ? route('admin.update-Virtual-Countries') :  route('admin.store-Virtual-Countries') }}"
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
                                <input type="text" name="image_gallery" class="form-control mb-2"
                                       placeholder="لینک تصویر" value="{{isset($edit) ? $edit->image : '' }}">
                                <img alt="image" width="150" height="150" style="margin-top:20px;margin-bottom: 20px"
                                     src="{{isset($edit) ? secure_url('/').'/image/svg/'.strtolower($edit->code).'.svg' : '' }}">
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
                                    <option></option>
                                    <option value="1" selected="selected">منتشر شده</option>
                                    <option value="0">غیرفعال</option>
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
                                        data-hide-search="true"
                                        data-placeholder="انتخاب " id="kt_ecommerce_add_product_status_select">
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
                        @if(isset($edit))
                            <a target="_blank" href="{{route('admin.GenerateProduct',$edit->id)}}"
                               class="btn btn-warning">
                                <span class="indicator-label">بروز رسانی کشور</span>
                            </a>
                        @endif
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
                                   href="#seo">پیشرفته</a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane  show active" id="all" role="tab-panel">
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
                                                           value="{{isset($edit) ? $edit->faName : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">نام به انگلیسی</label>
                                                    <input type="text" name="enName" class="form-control mb-2"
                                                           placeholder="نام به انگلیسی"
                                                           value="{{isset($edit) ? $edit->enName : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">لینک</label>
                                                    <input type="text" name="slug" class="form-control mb-2"
                                                           placeholder="لینک"
                                                           value="{{isset($edit) ? $edit->slug : '' }}"/>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کد Pva</label>
                                                    <input type="text" name="pva" class="form-control mb-2"
                                                           placeholder="کد Api"
                                                           value="{{isset($edit) ? $edit->pva : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کد Land</label>
                                                    <input type="text" name="land" class="form-control mb-2"
                                                           placeholder="کد Api"
                                                           value="{{isset($edit) ? $edit->land : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کد Active</label>
                                                    <input type="text" name="active" class="form-control mb-2"
                                                           placeholder="کد Api"
                                                           value="{{isset($edit) ? $edit->active : '' }}"/>
                                                </div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">کد Man </label>
                                                    <input type="text" name="man" class="form-control mb-2"
                                                           placeholder="کد Api"
                                                           value="{{isset($edit) ? $edit->man : '' }}"/>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-5 mt-4">
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="required form-label">ردیف</label>
                                                    <input type="text" name="sort" class="form-control mb-2"
                                                           placeholder="ردیف"
                                                           value="{{isset($edit) ? $edit->sort : '' }}"/>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($edit) ? $edit->long_detail : '' !!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($edit) ? $edit->long_detail : ''}}">
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
                                                          rows="10">{!! isset($edit) ? $edit->seo : '' !!}</textarea>
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
