@extends('admin-v2.layout.master',['MetaTitle' => 'تنظیمات خدمات مجازی'])
@section('content')
    @php
        $Seo = isset($Page) ? (json_decode($Page->seo, true) ?: []) : [];
        // Stored as "/upload/…" by the image picker, or as a full address by older saves.
        $Image = isset($Page) && $Page->image ? (preg_match('#^https?://#', $Page->image) ? $Page->image : url($Page->image)) : '';
    @endphp
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{route('admin.virtual-service-setting-submit')}}"
                      method="post">
                    @csrf
                    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
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
                                    <input type="text" name="image_gallery" id="gallery_image_input_1" class="form-control mb-2"
                                           placeholder="لینک تصویر" value="{{ $Image }}">
                                    <img alt="" id="gallery_image_input_per_1" width="150" height="150"
                                         style="margin-top:20px;margin-bottom: 20px;object-fit: contain;{{ $Image ? '' : 'display:none' }}"
                                         src="{{ $Image }}">
                                    <div class="text-muted fs-7">تصویر شاخص صفحه خدمات مجازی. فقط فایل های تصویری
                                        *.png، *.jpg و *.jpeg پذیرفته می شوند
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="submit_btn" class="btn w-100 btn-primary mt-5">
                                <span class="indicator-label">ذخیره تغییرات</span>
                                <span class="indicator-progress">لطفا صبر کنید...<span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </aside>
                    </div>
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
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
                                                    <label class="required form-label">تگ H1</label>
                                                    <input type="text" name="h1" class="form-control mb-2"
                                                           placeholder="عنوان اصلی صفحه"
                                                           value="{{isset($Page) ? $Page->h1 : '' }}"/>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                    <label class="form-label">توضیحات</label>
                                                    <textarea id="detail" class="form-control" cols="30"
                                                              rows="10">{!! isset($Page) ? $Page->long_detail : '' !!}</textarea>
                                                    <input id="changed" name="detail" type="hidden"
                                                           value="{{isset($Page) ? $Page->long_detail : ''}}">
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
                                                       placeholder="عنوان صفحه در نتایج گوگل"
                                                       value="{{ $Seo['meta_title'] ?? '' }}"/>
                                            </div>
                                            <div class="mb-10">
                                                <label class="required form-label">توضیحات متا</label>
                                                <input type="text" name="meta_desc" class="form-control mb-2"
                                                       placeholder="توضیح کوتاه صفحه در نتایج گوگل"
                                                       value="{{ $Seo['meta_desc'] ?? '' }}"/>
                                            </div>
                                            <div>
                                                <label class="form-label">توضیحات</label>
                                                <textarea name="seo" class="form-control" cols="30"
                                                          rows="10">{{ $Seo['meta'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Tab content-->
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

        // A link typed or pasted into the image field updates the preview too.
        $('#gallery_image_input_1').on('input change', function () {
            var url = $(this).val().trim();
            $('#gallery_image_input_per_1').attr('src', url).toggle(url !== '');
        });
    </script>
@endsection
