@extends('admin-v2.layout.master')
@section('content')
    @php $data = json_decode($edit->value,true); @endphp
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form id="CurrentForm"
                      action="{{route('admin.bot-setting-update')}}"
                      method="post">
                    @csrf
                    <div class="row">

                        <div class="col-12 col-lg-9">
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
                                                        <label class="required form-label">عنوان</label>
                                                        <input type="text" readonly class="form-control mb-2"
                                                               placeholder="نام به فارسی"
                                                               value="{{isset($edit) ? $edit->title : ''}}"/>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="id" value="{{$edit->id}}">
                                                <div class="d-flex flex-wrap gap-5">
                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                        <label class="required form-label">شماره کارت</label>
                                                        <input type="text" name="card" class="form-control mb-2"value="{{!is_null($data) ? $data['card'] : ''}}"/>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-3">
                            <div style="position: sticky;top: 6rem">
                                <div class="card card-flush py-4">
                                    <div class="card-header">
                                        <div class="card-title">
                                            <h2>وضعیت</h2>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <select class="form-select mb-2" data-control="select2" name="status"
                                                data-hide-search="true" data-placeholder="انتخاب "
                                                id="kt_ecommerce_add_product_status_select" tabindex="0"
                                                aria-hidden="false">
                                            <option {{isset($edit) && $edit->status == 1 ? 'selected' : '' }} value="1">
                                                فعال
                                            </option>
                                            <option {{isset($edit) && $edit->status == -1 ? 'selected' : '' }} value="-1">
                                                غیرفعال
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button"
                                            id="submit_btn"
                                            class="btn w-100 btn-primary">
                                        <span class="indicator-label">ذخیره تغییرات</span>
                                        <span class="indicator-progress">لطفا صبر کنید...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                    </button>
                                    <!--end::Button-->
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
    @include('admin.layout.submit-form')
    <script src="{{asset('plugins/ckeditor/ckeditor.js')}}"></script>
    <script src="{{secure_url('admin/js/all.js')}}" type="text/javascript"></script>
@endsection
