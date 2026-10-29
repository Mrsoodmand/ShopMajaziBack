@php if (isset($edit)){
    $Title = 'ویرایش مدیر';
}else{
    $Title = 'ایجاد مدیر';
} @endphp
@extends('admin-v2.layout.master',['MetaTitle' => $Title])
@section('content')
    @php
        if (isset($Page)){
            $Seo = json_decode($Page->seo,true);
        }
         if (isset($Setting)){
            $Setting = json_decode($Setting->key,true);
        }
    @endphp
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm" class="form d-flex flex-column flex-lg-row"
                      action="{{isset($edit) ?  route('admin.update admin') : route('admin.store admin') }}"
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
                                        id="kt_ecommerce_add_product_status_select" tabindex="0" aria-hidden="false">
                                    <option selected="" value="1">منتشر شده</option>
                                    <option value="0">غیرفعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-bold mb-n2">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#all">عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab"
                                   href="#seo">دسترسی ها</a>
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
                                            <div class="row">
                                                <div class="col-4">
                                                    <label class="required form-label">نام و نام خانوادگی:</label>
                                                    <input type="text" name="full_name" class="form-control mb-2"
                                                           placeholder="نام به فارسی"
                                                           value="{{isset($edit) ? $edit->full_name : '' }}"/>
                                                </div>
                                                <div class="col-4">
                                                    <label class="required form-label">شماره موبایل:</label>
                                                    <input type="text" name="mobile" class="form-control mb-2"
                                                           placeholder="091********"
                                                           value="{{isset($edit) ? $edit->mobile : '' }}"/>
                                                </div>
                                                <div class="col-4">
                                                    <label class="required form-label">ایمیل:</label>
                                                    <input type="text" name="email" class="form-control mb-2"
                                                           placeholder=""
                                                           value="{{isset($edit) ? $edit->email : '' }}"/>
                                                </div>
                                                <div class="col-4 mt-2">
                                                    <label class="required form-label">کلمه عبور:</label>
                                                    <input type="text" name="password" class="form-control mb-2"
                                                           placeholder="" value=""/>
                                                </div>
                                                @isset($edit)
                                                    <input type="hidden" name="id" class="form-control mb-2" placeholder="" value="{{$edit->id}}"/>
                                                @endif
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
                                                <h2>دسترسی ها</h2>
                                            </div>
                                        </div>
                                        <div class="card-body pt-0">
                                            <div class="row">
                                                <div class="col-12 mt-2 mb-5">
                                                    <label class="required form-label">رول:</label>
                                                    <select class="form-select mb-2 permissions" data-control="select2"
                                                            name="role" data-hide-search="true"
                                                            data-placeholder="انتخاب "
                                                            id="kt_ecommerce_add_product_status_select" tabindex="0"
                                                            aria-hidden="false">
                                                        @foreach($Role as $role)
                                                            <option value="{{$role->id}}"
                                                                    @if(isset($edit) && $edit->role == $role->name) selected @endif>{{$role->faName}}</option>
                                                        @endforeach
                                                    </select>
                                                    <h3>دسترسی ها:</h3>
                                                </div>
                                            </div>
                                            <div id="permissions">

                                                <br>
                                                @php
                                                    if (isset($edit)){
                                                        $Roles = $Role->where('name',$edit->role)->first();
                                                    }
                                                @endphp
                                                @include('admin-v2.comp.roleAndPermissions')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Tab content-->
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
        $('.permissions').change(function () {
            var val = $(this).val();
            $.ajax({
                url: '{{route('admin.change role')}}',
                type: 'get',
                method: 'get',
                dataType: 'json',
                data: {
                    id: val
                },
                success: function (data) {
                    $('#permissions').empty().append(data.data);
                },
                error: function (data) {
                    console.log(data)
                }
            })
        })
    </script>
@endsection
