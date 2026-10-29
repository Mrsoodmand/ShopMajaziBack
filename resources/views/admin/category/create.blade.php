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
                  action="{{isset($edit) ? route('admin.update category') :  route('admin.store category') }}"
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
                                                   value="{{isset($edit) ? $edit->name : ''}}" class="form-control">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>نام به انگلیسی </label>
                                            <input name="enName" type="text"
                                                   value="{{isset($edit) ? $edit->enName : ''}}" class="form-control">
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label> لینک </label>
                                            <input name="slug" style="text-transform: lowercase;" type="text"
                                                   value="{{isset($edit) ? $edit->slug : ''}}"
                                                   class="form-control">
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
                                    <div class="form-group col-12">
                                        <label> توضیحات کوتاه </label>
                                        <textarea name="short_detail" class="form-control" id="" cols="30"
                                                  rows="10">{!! isset($edit) && $edit->short_detail != null ? $edit->short_detail : '' !!}</textarea>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label> توضیحات </label>
                                            <textarea id="detail" class="form-control" cols="30"
                                                      rows="10">{!! isset($edit) ? $edit->long_detail : '' !!}</textarea>
                                            <input id="changed" name="detail" type="hidden"
                                                   value="{{isset($edit) ? $edit->long_detail : ''}}">
                                        </div>
                                    </div>

                                    @if(isset($edit))
                                        <input name="id" type="hidden" value="{{isset($edit) ? $edit->id : ''}}">
                                        <input name="type" type="hidden" value="{{isset($edit) ? $edit->type : ''}}">
                                    @else
                                        <input type="hidden" name="type" value="{{$type}}">
                                    @endif

                                    <div class="form-group col-12">
                                        <label> سئو </label>
                                        <textarea name="seo" class="form-control" id="" cols="30"
                                                  rows="10">{!! isset($edit) && $edit->seo != null ? $edit->seo : '' !!}</textarea>
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
                                           value="{{isset($edit) ? $edit->image : '' }}"
                                           name="image_gallery"
                                           id="gallery_image_input_1">
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="col-sm-12">

                                    <img alt="image" width="150" height="150" style="margin: auto"
                                         src="{{isset($edit) ? URL::asset($edit->image) : '' }}">
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
    </script>
@stop
