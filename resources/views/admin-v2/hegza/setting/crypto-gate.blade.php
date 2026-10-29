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

                        <div class="col-lg-9 col-12 mt-sm-5">
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
                                            </div>

                                            <div class="card-body mt-5">
                                                <div class="row mt-5">
                                                    <div class="col-6 mb-5">
                                                        <p>
                                                            لیست ارز ها
                                                        </p>
                                                    </div>
                                                    <div class="col-6 mb-5">
                                                        <button type="button" id="cloneButton"
                                                                class="btn w-100 btn-primary">
                                                            <span class="indicator-label">افزودن</span>
                                                        </button>
                                                    </div>

                                                    <div class="col-12" id="crypto-area-table">
                                                        @if(!is_null($data))
                                                            @foreach($data as $item)
                                                                <div class="row mt-5">
                                                                    <div class="col-lg-2 col-6">
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">نوع
                                                                                ارز</label>
                                                                            <input type="text" name="cryptoname[]"
                                                                                   class="form-control mb-2"
                                                                                   value="{{$item['name'] }}"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-2 col-6">
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">شبکه</label>
                                                                            <input type="text" name="network[]"
                                                                                   class="form-control mb-2"
                                                                                   value="{{$item['network'] }}"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-7 col-12">
                                                                        <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                            <label class="required form-label">آدرس
                                                                                ولت</label>
                                                                            <input type="text" name="address[]"
                                                                                   class="form-control mb-2"
                                                                                   value="{{$item['address']}}"/>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-1 col-12 mt-8 mt-8">
                                                                        <button type="button"
                                                                                onclick="$(this).parent().parent().remove()"
                                                                                class="btn w-100 btn-danger">
                                                                            <span class="indicator-label">x</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="row">
                                                                <div class="col-2">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">نوع
                                                                            ارز</label>
                                                                        <input type="text" name="cryptoname[]"
                                                                               class="form-control mb-2"
                                                                               value=""/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-2">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">شبکه</label>
                                                                        <input type="text" name="network[]"
                                                                               class="form-control mb-2"
                                                                               value=""/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-8">
                                                                    <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                                                                        <label class="required form-label">آدرس
                                                                            ولت</label>
                                                                        <input type="text" name="address[]"
                                                                               class="form-control mb-2"
                                                                               value=""/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-12">
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
    <div style="display: none">

        <div class="row mt-3" id="crypto-area">
            <div class="col-lg-2 col-6">
                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                    <label class="required form-label">نوع ارز</label>
                    <input type="text" name="cryptoname[]" class="form-control mb-2"
                           value=""/>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                    <label class="required form-label">شبکه</label>
                    <input type="text" name="network[]" class="form-control mb-2"
                           value=""/>
                </div>
            </div>
            <div class="col-lg-7 col-12">
                <div class="fv-row w-100 flex-md-root fv-plugins-icon-container">
                    <label class="required form-label">آدرس ولت</label>
                    <input type="text" name="address[]" class="form-control mb-2"
                           value=""/>
                </div>
            </div>
            <div class="col-lg-1 col-12 mt-8">
                <button type="button" onclick="$(this).parent().parent().remove()"
                        class="btn w-100 btn-danger">
                    <span class="indicator-label">x</span>
                </button>
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

        $('#cloneButton').click(function () {
            var original = $('#crypto-area');
            var clone = original.clone(); // Clone the element

            $('#crypto-area-table').append(clone); // Append the cloned element to the container
        });


    </script>
@endsection
