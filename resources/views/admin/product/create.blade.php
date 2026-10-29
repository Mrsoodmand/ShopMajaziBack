@extends('admin.layout.master')

@section('title') {{isset($edit) ? "ویرایش دسته بندی" : 'افزودن دسته بندی'}} @stop
@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4>{{isset($edit) ? "ویرایش دسته بندی" : 'افزودن دسته بندی'}}</h4>

            </div>
            <!-- row -->
            <form id="CurrentForm"
                  action="{{isset($edit) ? route('admin.update product') :  route('admin.store product') }}"
                  method="post">
                <div class="row">
                    @csrf
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"></h4>
                            </div>
                            <div class="card-body">
                                <div class="default-tab">
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#home"><i
                                                    class="la la-home ml-2"></i> اصلی </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#products"><i
                                                    class="la la-user ml-2"></i> محصولات </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#seo"><i
                                                    class="la la-user ml-2"></i> سئو </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#filed"><i
                                                    class="la la-user ml-2"></i> فیلد ها </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#Notes"><i
                                                    class="la la-user ml-2"></i> نکات مهم </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="home" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="basic-form">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label> نام به فارسی </label>
                                                            <input name="faName" id="name" onkeyup="ChangeName()"
                                                                   type="text"
                                                                   value="{{isset($edit) ? $detail->name : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label>نام به انگلیسی </label>
                                                            <input name="enName" type="text"
                                                                   value="{{isset($edit) ? $detail->enName : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label> لینک </label>
                                                            <input name="slug" style="text-transform: lowercase;"
                                                                   type="text"
                                                                   value="{{isset($edit) ? $edit->slug : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label> دسته بندی </label>
                                                            <select class="form-control form-control"
                                                                    name="category_id">
                                                                @foreach($Categories as $category)
                                                                    <option
                                                                        value="{{$category->id}}" {{(isset($edit) && $category->id == $edit->country_id ? 'selected ':'' )}} > {{$category->name}} </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label> پورسانت </label>
                                                            <input name="commission" style="text-transform: lowercase;"
                                                                   type="text"
                                                                   value="{{isset($edit) ? $edit->commission : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-12">
                                                        <label> توضیحات کوتاه </label>
                                                        <textarea name="short_detail" class="form-control" id=""
                                                                  cols="30"
                                                                  rows="10">{!! isset($edit) && $detail->short_detail != null ? $detail->short_detail : '' !!}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-12">
                                                            <label> توضیحات </label>
                                                            <textarea id="detail" class="form-control" cols="30"
                                                                      rows="10">{!! isset($edit) ? $detail->detail : '' !!}</textarea>
                                                            <input id="changed" name="detail" type="hidden"
                                                                   value="{{isset($edit) ? $detail->detail : ''}}">
                                                        </div>
                                                    </div>

                                                    @if(isset($edit))
                                                        <input name="id" type="hidden"
                                                               value="{{isset($edit) ? $edit->id : ''}}">
                                                    @endif


                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="products" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="card-header d-sm-flex d-block border-0 pb-0">
                                                    <div>
                                                        <h4 class="fs-20 text-black"> افزودن </h4>

                                                    </div>
                                                    <div class="card-action card-tabs mt-3 mt-sm-0">
                                                        <button onclick="AddDynamicProductRow()"
                                                                type="button" class="btn light btn-success btn-xxs">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="basic-form" id="DynamicProductRow">
                                                    @if(isset($edit))
                                                        @foreach($Plans as $key => $price)
                                                            <div class="form-row mt-2">
                                                                <div class="form-group col-md-3">
                                                                    <label> نام به فارسی </label>
                                                                    <input name="dynamic_name[]" type="text"
                                                                           value="{{isset($edit) ? $price['name'] : ''}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-3">
                                                                    <label>نام به انگلیسی </label>
                                                                    <input name="dynamic_enName[]" type="text"
                                                                           value="{{isset($edit) ? $price['en_name'] : ''}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <label> قیمت اصلی </label>
                                                                    <input name="dynamic_price[]" type="text"
                                                                           value="{{isset($edit) ? $price['price'] : ''}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <label>قیمت با تخفیف </label>
                                                                    <input name="dynamic_discount[]" type="text"
                                                                           value="{{isset($edit) ? $price['discount'] : ''}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <label> حذف </label>
                                                                    <button type="button"
                                                                            onclick="$(this).parent().parent().remove()"
                                                                            class="btn btn-danger">حذف <span
                                                                            class="btn-icon-right"><i
                                                                                class="fa fa-close"></i></span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                    @else
                                                        <div class="form-row mt-2">
                                                            <div class="form-group col-md-3">
                                                                <label> نام به فارسی </label>
                                                                <input name="dynamic_name[]" type="text"
                                                                       value="{{isset($edit) ? $edit->name : ''}}"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-3">
                                                                <label>نام به انگلیسی </label>
                                                                <input name="dynamic_enName[]" type="text"
                                                                       value="{{isset($edit) ? $edit->enName : ''}}"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <label> قیمت اصلی </label>
                                                                <input name="dynamic_price[]" type="text"
                                                                       value="{{isset($edit) ? $edit->enName : ''}}"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <label>قیمت با تخفیف </label>
                                                                <input name="dynamic_discount[]" type="text"
                                                                       value="{{isset($edit) ? $edit->enName : ''}}"
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <label> حذف </label>
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().remove()"
                                                                        class="btn btn-danger">حذف <span
                                                                        class="btn-icon-right"><i
                                                                            class="fa fa-close"></i></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="seo" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="basic-form">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-12">
                                                            <label> عنوان </label>
                                                            <input name="meta_title" type="text"
                                                                   value="{{isset($edit) ? $detail->meta_title : ''}}"
                                                                   class="form-control replace">
                                                        </div>

                                                        <div class="form-group col-12">
                                                            <label> توضیحات متا </label>
                                                            <input name="meta_desc" id="desc" type="text"
                                                                   value="{{isset($edit) ? $detail->meta_desc : ''}}"
                                                                   class="form-control">
                                                        </div>

                                                        <div class="form-group col-12">
                                                            <label> سئو </label>
                                                            <textarea name="seo" class="form-control" id="" cols="30"
                                                                      rows="10">{!! isset($edit) && $detail->seo != null ? $detail->seo : '' !!}</textarea>
                                                        </div>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="filed" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="card-header d-sm-flex d-block border-0 pb-0">
                                                    <div>
                                                        <h4 class="fs-20 text-black"> افزودن </h4>

                                                    </div>
                                                    <div class="card-action card-tabs mt-3 mt-sm-0">
                                                        <button onclick="AddFiled()"
                                                                type="button" class="btn light btn-success btn-xxs">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="basic-form" id="fild-area-1">
                                                    @if(isset($edit) && $edit->filds != null)
                                                        @php $Fileds = json_decode($edit->filds,true) @endphp
                                                        @foreach($Fileds as $key => $fild)

                                                            @php $key++;
                                                            @endphp
                                                            <div class="form-row mt-2">
                                                                <div class="form-group col-md-6">
                                                                    <label> نوع </label>
                                                                    <select name="filed[type][]" id="filed_type"
                                                                            data-id="{{$key}}"
                                                                            class="form-control filed_type">
                                                                        <option
                                                                            {{($fild['type'] == 'select_box' ? 'selected': '')}} value="select_box">
                                                                            Select Box
                                                                        </option>
                                                                        <option
                                                                            {{($fild['type'] == 'text' ? 'selected': '')}} value="text">
                                                                            Text
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label> عنوان </label>
                                                                    <input name="filed[title][]" type="text"
                                                                           value="{{$fild['title']}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label> Col </label>
                                                                    <input name="filed[col][]" type="text"
                                                                           value="{{$fild['col']}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label> نام فیلد </label>
                                                                    <input name="filed[filed_name][]" type="text"
                                                                           value="{{$fild['filed_name']}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-6"
                                                                     id="filed-area-{{$key}}">
                                                                    <label> مقدار سلکت </label>
                                                                    <input name="filed[select_value][]" type="text"
                                                                           value="{{$fild['select_value']}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label> حذف </label>
                                                                    <br>
                                                                    <button type="button"
                                                                            onclick="$(this).parent().parent().remove()"
                                                                            class="btn light btn-danger"> حذف
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        @endforeach
                                                    @else
                                                        <div class="form-row mt-2">
                                                            <div class="form-group col-md-6">
                                                                <label> نوع </label>
                                                                <select name="filed[type][]" id="filed_type" data-id="0"
                                                                        class="form-control filed_type">
                                                                    <option value="select_box"> Select Box</option>
                                                                    <option value="text"> Text</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label> عنوان </label>
                                                                <input name="filed[title][]" type="text"
                                                                       value=""
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label> Col </label>
                                                                <input name="filed[col][]" type="text"
                                                                       value=""
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                <label> نام فیلد </label>
                                                                <input name="filed[filed_name][]" type="text"
                                                                       value=""
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-6" id="filed-area-0">
                                                                <label> مقدار سلکت </label>
                                                                <input name="filed[select_value][]" type="text" value=""
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-6">

                                                                <label> حذف </label>
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().remove()"
                                                                        class="btn light btn-danger"> حذف
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <input type="hidden" value="{{isset($key) ? $key : 0}}" id="filed_id">
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="Notes" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="card-header d-sm-flex d-block border-0 pb-0">
                                                    <div>
                                                        <h4 class="fs-20 text-black"> افزودن </h4>

                                                    </div>
                                                    <div class="card-action card-tabs mt-3 mt-sm-0">
                                                        <button onclick="AddNotes()"
                                                                type="button" class="btn light btn-success btn-xxs">
                                                            +
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="basic-form" id="Notes-area-1">
                                                    @if(isset($edit) && $edit->notes != null)
                                                        @php $Fileds = json_decode($edit->notes,true) @endphp
                                                        @foreach($Fileds as $key => $fild)
                                                            <div class="form-row mt-2">

                                                                <div class="form-group col-md-8">
                                                                    <label> عنوان </label>
                                                                    <input name="notes[]" type="text"
                                                                           value="{{$fild}}"
                                                                           class="form-control">
                                                                </div>
                                                                <div class="form-group col-md-2">
                                                                    <label> حذف </label>
                                                                    <br>
                                                                    <button type="button"
                                                                            onclick="$(this).parent().parent().remove()"
                                                                            class="btn light btn-danger"> حذف
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        @endforeach
                                                    @else
                                                        <div class="form-row mt-2">

                                                            <div class="form-group col-md-8">
                                                                <label> عنوان </label>
                                                                    <input name="notes[]" type="text"
                                                                       value=""
                                                                       class="form-control">
                                                            </div>
                                                            <div class="form-group col-md-2">
                                                                <label> حذف </label>
                                                                <br>
                                                                <button type="button"
                                                                        onclick="$(this).parent().parent().remove()"
                                                                        class="btn light btn-danger"> حذف
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <input type="hidden" value="{{isset($key) ? $key : 0}}" id="filed_id">
                                            </div>
                                        </div>
                                    </div>
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
                                    <button type="button" class="btn light btn-success"
                                            data-ckfinder-field="gallery_image_input_1"
                                            class="btn btn-success waves-effect waves-light">
                                        انتخاب تصویر
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="col-sm-12">
                                    <input type="text" class="form-control"
                                           placeholder="Custom icons"
                                           value="{{isset($edit) ? $detail->image_gallery : '' }}"
                                           name="image_gallery"
                                           id="gallery_image_input_1">
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="col-sm-12">

                                    <img alt="image" width="150" height="150" style="margin: auto"
                                         src="{{isset($edit) ? URL::asset($detail->image_gallery) : '' }}">
                                </div>
                            </div>


                            <button type="button" id="submit_btn"
                                    class="btn btn-primary">{{isset($edit) ? 'ویرایش' : 'ثبت'}}</button>
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


        function AddFiled() {
            var id = $('#filed_id').val();
            id++
            $('#fild-area-1').append(
                '               <div class="form-row mt-2">' +
                '         <div class="form-group col-md-6">' +
                '         <label> نوع </label>' +
                '     <select name="filed[type][]" data-id="' + id + '" class="form-control filed_type">' +
                '         <option value="select_box"> Select Box</option>' +
                '         <option value="text"> Text</option>' +
                '     </select>' +
                ' </div>' +
                ' <div class="form-group col-md-6">' +
                ' <label> عنوان </label>' +
                '<input name="filed[title][]" type="text"' +
                'value=""' +
                ' class="form-control">' +
                ' </div>' +
                ' <div class="form-group col-md-6">' +
                ' <label> Col </label>' +
                '<input name="filed[col][]" type="text"' +
                'value=""' +
                ' class="form-control">' +
                ' </div>' +
                ' <div class="form-group col-md-6">' +
                ' <label> نام فیلد </label>' +
                '<input name="filed[filed_name][]" type="text"' +
                'value=""' +
                ' class="form-control">' +
                ' </div>' +
                ' <div class="form-group col-md-6" id="filed-area-' + id + '">' +
                ' <label> مقدار سلکت </label>' +
                ' <input name="filed[select_value][]" type="text" value=""  class="form-control">' +
                ' </div>' +
                ' <div class="form-group col-md-6" id="filed-area-' + id + '">' +
                ' <label> مقدار سلکت </label>' +
                ' <button type="button" onclick="$(this).parent().parent().remove()" class="btn light btn-danger"> حذف</button>' +
                ' </div>' +
                '</div>'
            )
            $('.filed_type').change(function () {
                var val = $(this).val()
                var id = $(this).data('id')

                if (val === 'select_box') {
                    $('#filed-area-' + id).append('' +
                        '   <label> مقدار سلکت </label>' +
                        '<input name="filed[select_value]" type="text" value=""  class="form-control">'
                    )
                } else {
                    $('#filed-area-' + id).empty()
                }
            });

        }

        function AddNotes() {
            $('#Notes-area-1').append(
                '<div class="form-row mt-2">' +
                '<div class="form-group col-md-8">' +
                ' <label> عنوان </label>' +
                '    <input name="notes[]" type="text"' +
                ' value=""' +
                ' class="form-control">' +
                ' </div>' +
                '  <div class="form-group col-md-2">' +
                ' <label> حذف </label>' +
                '<br>' +
                '                            <button type="button" onclick="$(this).parent().parent().remove()" class="btn light btn-danger"> حذف</button>' +
                '</div>' +
                ' </div>')


            $('.filed_type').change(function () {
                var val = $(this).val()
                var id = $(this).data('id')

                if (val === 'select_box') {
                    $('#filed-area-' + id).append('' +
                        '   <label> مقدار سلکت </label>' +
                        '<input name="filed[select_value]" type="text" value=""  class="form-control">'
                    )
                } else {
                    $('#filed-area-' + id).empty()
                }
            });

        }

        $('.filed_type').change(function () {
            var val = $(this).val()
            var id = $(this).data('id')

            if (val === 'select_box') {
                $('#filed-area-' + id).append('' +
                    '   <label> مقدار سلکت </label>' +
                    '<input name="filed[select_value]" type="text" value=""  class="form-control">'
                )
            } else {
                $('#filed-area-' + id).empty()
            }
        });

        function AddDynamicProductRow() {
            $('#DynamicProductRow').append('' +
                ' <div class="form-row mt-2">' +
                '        <div class="form-group col-md-3">' +
                '        <label> نام به فارسی </label>' +
                '   <input name="dynamic_name[]" type="text"' +
                '          class="form-control">' +
                '    </div>' +
                '    <div class="form-group col-md-3">' +
                '        <label>نام به انگلیسی </label>' +
                '       <input name="dynamic_enName[]" type="text"' +
                '                class="form-control">' +
                '    </div>' +
                '    <div class="form-group col-md-2">' +
                '        <label> قیمت اصلی </label>' +
                '       <input name="dynamic_price[]" type="text"' +
                '             class="form-control">' +
                '    </div>' +
                '    <div class="form-group col-md-2">' +
                '        <label>قیمت با تخفیف </label>' +
                '       <input name="dynamic_discount[]" type="text"' +
                '              class="form-control">' +
                '    </div>' +
                '    <div class="form-group col-md-2">' +
                '      <label> حذف </label>' +
                '   <button type="button" class="btn btn-danger" onclick="$(this).parent().parent().remove()">حذف <span class="btn-icon-right"><i class="fa fa-close"></i></span>' +
                '</button>' +
                '    </div>' +
                '</div>'
            );
        }
    </script>
@stop
