@extends('admin.layout.master',['title' => isset($edit) ? "ویرایش بلاگ" : 'افزودن بلاگ'])

@section('content')
    <div class="content-body" style="min-height: 1100px;">
        <div class="container-fluid">
            <div class="page-titles">
                <h4> {{isset($edit) ? "ویرایش بلاگ" : 'افزودن بلاگ'}}</h4>

            </div>
            @if(\Illuminate\Support\Facades\Session::has('error'))
                @foreach(\Illuminate\Support\Facades\Session::get('error') as $error)
                    <div class="alert alert-danger alert-dismissible fade show">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2"
                             fill="none"
                             stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                            </polygon>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        {{$error}}
                    </div>
                @endforeach
            @endif
            @if(\Illuminate\Support\Facades\Session::has('success'))

                <div class="alert alert-success alert-dismissible fade show">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                         stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2">
                        </polygon>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    {{\Illuminate\Support\Facades\Session::get('success')}}
                </div>

        @endif
            <!-- row -->
            <form id="CurrentForm"
                  action="{{isset($edit) ? route('admin.update blog') :  route('admin.store blog') }}"
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
                                            <a class="nav-link" data-toggle="tab" href="#seo"><i
                                                    class="la la-user ml-2"></i> سئو </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="home" role="tabpanel">
                                            <div class="pt-4">
                                                <div class="basic-form">
                                                    <div class="form-row">
                                                        <div class="form-group col-md-6">
                                                            <label> عنوان </label>
                                                            <input name="title" id="name" onkeyup="ChangeName()"
                                                                   type="text"
                                                                   value="{{isset($edit) ? $edit->title : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <label> لینک </label>
                                                            <input name="slug" style="text-transform: lowercase;"
                                                                   type="text"
                                                                   value="{{isset($edit) ? $edit->slug : ''}}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-12">
                                                        <label> توضیحات کوتاه </label>
                                                        <textarea name="short_detail" class="form-control" id=""
                                                                  cols="30"
                                                                  rows="10">{!! isset($edit) && $edit->short_detail != null ? $edit->short_detail : '' !!}</textarea>
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
                                                                   value="{{isset($edit) ? $edit->meta_title : ''}}"
                                                                   class="form-control replace">
                                                        </div>

                                                        <div class="form-group col-12">
                                                            <label> توضیحات متا </label>
                                                            <input name="meta_desc" id="desc" type="text"
                                                                   value="{{isset($edit) ? $edit->meta_desc : ''}}"
                                                                   class="form-control">
                                                        </div>

                                                        <div class="form-group col-12">
                                                            <label> سئو </label>
                                                            <textarea name="seo" class="form-control" id="" cols="30"
                                                                      rows="10">{!! isset($edit) && $edit->seo != null ? $edit->seo : '' !!}</textarea>
                                                        </div>

                                                    </div>

                                                </div>
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
                                           value="{{isset($edit) ? $edit->image : '' }}"
                                           name="image"
                                           id="gallery_image_input_1">
                                </div>
       