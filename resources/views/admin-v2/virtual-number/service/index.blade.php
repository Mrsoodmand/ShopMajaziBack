@extends('admin-v2.layout.master',['MetaTitle' => 'لیست شماره های مجازی'])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <form action="">
                            <div class="d-flex align-items-center position-relative my-1">
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                         viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                              height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                              fill="currentColor"></rect>
														<path
                                                                d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                                fill="currentColor"></path>
													</svg>
												</span>
                                <input type="text" name="search"
                                       value="{{isset($_GET) && array_key_exists('search',$_GET) ? $_GET['search'] : ''}}"
                                       class="form-control form-control-solid w-250px ps-14"
                                       placeholder="جستجو کشور">
                                <button type="submit" class="btn btn-sm btn-primary ms-2">جستجو</button>
                            </div>
                        </form>
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                            <a href="{{route('admin.create-Virtual-Services')}}" class="btn btn-primary">افزودن</a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_محصولات_table">
                            <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input" type="checkbox" data-kt-check="true"
                                               data-kt-check-target="#kt_ecommerce_محصولات_table .form-check-input"
                                               value="1">
                                    </div>
                                </th>
                                <th class="text-end min-w-70px text-left">نام به فارسی</th>
                                <th class="text-end min-w-70px">نام به انگلیسی</th>
                                <th class="text-end min-w-100px">کد Pva</th>
                                <th class="text-end min-w-100px">کد Man</th>
                                <th class="text-end min-w-100px">کد Active</th>
                                <th class="text-end min-w-70px">کد Land</th>
                                <th class="text-end min-w-70px">وضعیت</th>
                                <th class="text-end min-w-70px">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                            @foreach($list as $key => $value)
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{route('admin.edit-Virtual-Services',[$value->id])}}"
                                               target="_blank" class="symbol symbol-50px">
                                                <span class="symbol-label"
                                                      style="background-image:secure_url({{secure_url($value->image)}});"></span>
                                            </a>
                                            <div class="ms-5">
                                                <a href="{{route('admin.edit-Virtual-Services',[$value->id])}}"
                                                   target="_blank"
                                                   class="text-gray-800 text-hover-primary fs-5 fw-bolder"
                                                   data-kt-ecommerce-product-filter="product_name">{{$value->faName}}</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-0" onclick="myFunction('{{$value->enName}}')">
                                        <span class="fw-bolder">{{$value->enName}}</span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder ms-3">{{$value->pva}}</span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder ms-3">{{$value->man}}</span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder ms-3">{{$value->active}}</span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder ms-3">{{$value->land}}</span>
                                    </td>
                                    <td class="text-end pe-0" data-order="منتشر شده">
                                        @if($value->status == 1)
                                            <div class="badge badge-light-success">منتشر شده</div>
                                        @else
                                            <div class="badge badge-light-danger">غیرفعال</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{--                                        <button type="button" data-id="{{$value->id}}"--}}
                                        {{--                                                class="submit_btn btn btn-primary">--}}
                                        {{--                                            <span class="indicator-label" id="text-{{$value->id}}">ذخیره</span>--}}
                                        {{--                                            <span class="indicator-progress" id="loader-{{$value->id}}" >لطفا صبر کنید...<span--}}
                                        {{--                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>--}}
                                        {{--                                        </button>--}}
                                        <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                           data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">عملیات<span
                                                    class="svg-icon svg-icon-5 m-0"><svg xmlns="http://www.w3.org/2000/svg"
                                                                                         width="24" height="24"
                                                                                         viewBox="0 0 24 24" fill="none"><path
                                                            d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                                            fill="currentColor"></path></svg></span></a>
                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4"
                                             data-kt-menu="true">
                                            <div class="menu-item px-3">
                                                <a href="{{route('admin.edit-Virtual-Services',[$value->id])}}"
                                                   target="_blank" class="menu-link px-3"> ویرایش </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-5">
                    {!! $list->links('admin-v2.layout.paginate') !!}
                </div>
            </div>
        </div>
    </div>
    </div>

@endsection

@section('header')
@endsection

@section('footer')
    <script src="{{secure_url('js/jquery.form.min.js')}}" type="text/javascript"></script>
    <link href="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{URL::asset('admin/vendor/toastr/css/toastr.min.css')}}">
    <script src="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.js?v=')}}"></script>
    <script src="{{URL::asset('admin/vendor/toastr/js/toastr.min.js')}}"></script>
    <script>
        $(document).on('click', '.submit_btn', function () {
            var id = $(this).data('id');
            var man = $('#man-' + id).val();
            var pva = $('#pva-' + id).val();
            var land = $('#land-' + id).val();
            var active = $('#active-' + id).val();
            $('text-' + id).hide();
            $('loader-' + id).show();
            $(this).attr('disabled', false);
            $.ajax({
                url: '',
                dataType: 'json',
                method: "GET",
                data: {
                    id: id,
                    man: man,
                    pva: pva,
                    land: land,
                    active: active,
                },
                success: function (data) {
                    swal({
                        title: "عملیات با موفقیت انجام شد",
                        text: data.success,
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "باشه!",
                        closeOnConfirm: true
                    });
                    $('text-' + id).show();
                    $('loader-' + id).hide();
                    $(this).attr('disabled', false);
                }, error: function (error) {
                    data.responseJSON.error.forEach(function (value) {
                        toastr.error(value, "خطا", {
                            positionClass: "toast-top-right",
                            timeOut: 5e3,
                            closeButton: !0,
                            debug: !1,
                            newestOnTop: !0,
                            progressBar: !0,
                            preventDuplicates: !0,
                            onclick: null,
                            showDuration: "300",
                            hideDuration: "1000",
                            extendedTimeOut: "1000",
                            showEasing: "swing",
                            hideEasing: "linear",
                            showMethod: "fadeIn",
                            hideMethod: "fadeOut",
                            tapToDismiss: !1
                        })
                    })
                    $('text-' + id).show();
                    $('loader-' + id).hide();
                    $(this).attr('disabled', false);
                }
            });
        });

        function myFunction(id) {
            /* Copy the text inside the text field */
            navigator.clipboard.writeText(id);

        }
    </script>
@endsection
