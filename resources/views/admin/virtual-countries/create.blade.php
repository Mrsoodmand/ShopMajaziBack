@extends('admin.layout.master')

@section('title') {{isset($edit) ? "ویرایش کشور" : 'افزودن کشور'}} @stop
@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>{{isset($edit) ? "ویرایش کشور" : 'افزودن کشور'}}</h4>

            </div>
            <!-- row -->
            <form id="CurrentForm"
                  action="{{isset($edit) ? route('admin.update-Virtual-Countries') :  route('admin.store-Virtual-Countries') }}"
                  method="post">
                <div class="row">
                    @csrf
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"></h4>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label> نام به فارسی </label>
                                            <input name="faName" id="name" onkeyup="ChangeName()" type="text"
                                                   value="{{isset($edit) ? $edit->faName : ''}}" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>نام به انگلیسی </label>
                                            <input name="enName" type="text"
                                                   value="{{isset($edit) ? $edit->enName : ''}}" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label> لینک </label>
                                            <input name="slug"  style="text-transform: lowercase;" type="text" value="{{isset($edit) ? $edit->slug : ''}}"
                                                   class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label> وی پی ای کد API</label>
                                            <input name="code" type="text" value="{{isset($edit) ? $edit->code : ''}}"
                                                   class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>کد API لند</label>
                                            <input name="land_code" type="text"
                                                   value="{{isset($edit) ? $edit->land_code : ''}}"
                                                   class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label> دسته بندی </label>
                                            <select class="form-control " name="category_id">
                                                @foreach($Category as $category)
                                                    <option
                                                        {{isset($edit)  && $edit->category_id == $category->id ? 'selected' : ''}} value="{{$category->id}}">
                                                        {{$category->name}}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label> آیا جزو سرویس های ویژه میباشد؟ </label>
                                            <select class="form-control " name="special">
                                                <option
                                                    {{isset($edit)  && $edit->special == 1 ? 'selected' : ''}} value="1">
                                                    بله
                                                </option>
                                                <option
                                                    {{isset($edit)  && $edit->special == 0 ? 'selected' : ''}} value="0">
                                                    خیر
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label> ردیف </label>
                                            <input name="sort" type="text"
                                                   value="{{isset($edit) ? $edit->sort : ''}}" class="form-control">
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label> عنوان </label>
                                            <input name="meta_title" type="text"
                                                   value="{{isset($edit) ? $edit->meta_title : ''}}"
                                                   class="form-control replace">
                                        </div>
                                        <div class="form-group col-12">
                                            <label> توضیحات متا </label>
                                            <input name="meta_desc" id="desc" type="text"
                                                   value="{{isset($edit) ? $edit->meta_desc : ''}}"
                                                   class="form-control">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label> توضیحات </label>
                                            <textarea id="detail" class="form-control" cols="30"
                                                      rows="10">{!! isset($edit) ? $edit->detail : '' !!}</textarea>
                                            <input id="changed" name="detail" type="hidden"
                                                   value="{{isset($edit) ? $edit->detail : ''}}">
                                        </div>
                                    </div>

                                    @if(isset($edit))
                                        <input name="id" type="hidden"
                                               value="{{isset($edit) ? $edit->id : ''}}">
                                    @endif

                                    <div class="form-group col-12">
                                        <label> سئو </label>
                                        <textarea name="seo" class="form-control" id="" cols="30"
                                                  rows="10">
                                            {!! isset($edit) && $edit->seo != null ? $edit->seo : '<script type="application/ld+json">{"@@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"آیا شماره ها قابل رهگیری هستند؟","acceptedAnswer":{"@type":"Answer","text":"خیر، شماره ها غیر قابل رهگیری بوده و پس از دریافت کد منقضی میشوند طوری که انگار اصلا شماره ای وجود ندارد ! "}},{"@type":"Question","name":"آیا تحویل شماره آنی هست؟","acceptedAnswer":{"@type":"Answer","text":"بله تحویل شماره آنی و حتی کد آنی هستند و در صورت عدم دریافت شمارهی ا کد مبلغ به حساب شما واریز میگردد."}},{"@type":"Question","name":"آیا این شماره ها دائمی هستند؟","acceptedAnswer":{"@type":"Answer","text":"خیر ، این شماره ها پس از دریافت کد یا دقایقی پس از ایجاد از بین میروند"}},{"@type":"Question","name":"آیا شماره مجازی ریپورت شده اند؟","acceptedAnswer":{"@type":"Answer","text":"خیر. هیچ شماره مجازی ای ریپورت شده نیست."}}]}</script>' !!}</textarea>
                                    </div>

                                    <button type="button" id="submit_btn"
                                            class="btn btn-primary">{{isset($edit) ? 'ویرایش' : 'ثبت'}}</button>


                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="card">
                            <div class="card-header d-block d-sm-flex border-0">
                                <div>
                                    <h4 class="fs-20 text-black"> تصویر</h4>

                                </div>
                                <div class="card-action card-tabs mt-3 mt-sm-0">
                                    <button type="button"
                                            data-ckfinder-field="gallery_image_input_1"
                                            class="btn btn-success waves-effect waves-light">
                                        انتخاب تصویر
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @php $File = basename($edit->image)@endphp
                                <div class="col-sm-12">
                                    <input type="text" class="form-control"
                                           placeholder="Custom icons"
                                           value="{{isset($edit) ? "upload/country/".$File : '' }}"
                                           name="image_gallery"
                                           id="gallery_image_input_1">
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="col-sm-12">
                                    @php $File = basename($edit->image)@endphp
                                    <img alt="image" width="150" height="150" style="margin: auto"
                                         src="{{isset($edit) ? URL::asset('upload/country/'.$File) : '' }}">
                                </div>
                            </div>


                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
@section('header')

    <link href="{{URL::asset('admin/js/all.css')}}" rel="stylesheet" type="text/css">
    <style>
        .card {
            height: auto !important;
        }
    </style>
@stop
@section('footer')
    @include('admin.layout.submit-form')
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{url('admin/js/all.js')}}" type="text/javascript"></script>

    <script>
        var editor = CKEDITOR.replace('detail', {
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
            $("#desc").val(" وب سایت شماره مجازی ارائه دهنده  شماره مجازی از کشورهای "+ replace+" ، روسیه ، امریکا ، آرژانتین ، آلمان ، انگلستان و بیش از 50 کشور و با تنوع بیش از 60 برنامه و اپلیکشین | پشتیبانی 24 ساعته | فقط با 3 کلیک شمارتو بخر. ")
        }
    </script>
@stop
