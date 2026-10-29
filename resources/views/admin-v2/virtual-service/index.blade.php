@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title">
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
                                <input type="text" data-kt-ecommerce-product-filter="search"
                                       class="form-control form-control-solid w-250px ps-14"
                                       placeholder="جستجو سرویس">
                            </div>
                        </div>
                        <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                            <a href="{{route('admin.service-create')}}" class="btn btn-primary">افزودن</a>
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
                                <th class="text-end min-w-70px">مشاهده</th>
                                <th class="text-end min-w-70px">دسته بندی</th>
                                <th class="text-end min-w-70px">وضعیت</th>
                                <th class="text-end min-w-70px">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                            @foreach($list as $key => $value)
                                @php $Detail = json_decode($value->detail,true) @endphp
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="{{route('admin.service-edit',[$value->id])}}"
                                               class="symbol symbol-50px">
                                                <span class="symbol-label"
                                                      style="background-image:secure_url({{secure_url($Detail['image_gallery'])}});"></span>
                                            </a>
                                            <div class="ms-5">
                                                <a href="{{route('admin.service-edit',[$value->id])}}"
                                                   class="text-gray-800 text-hover-primary fs-5 fw-bolder"
                                                   data-kt-ecommerce-product-filter="product_name">{{$Detail['name']}}</a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder">{{$Detail['enName']}}</span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder ms-3"><a
                                               target="_blank" href="{{secure_url('/virtual-service/'.$value->ServiceCategory->slug.'/'.$value->slug)}}"> مشاهده </a></span>
                                    </td>
                                    <td class="text-end pe-0">
                                        <span class="fw-bolder">{{$value->ServiceCategory->name}}</span>
                                    </td>
                                    <td class="text-end pe-0" data-order="منتشر شده">
                                        @if($value->status == 1)
                                            <div class="badge badge-light-success">منتشر شده</div>
                                        @else
                                            <div class="badge badge-light-danger">غیرفعال</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{route('admin.service-edit',[$value->id])}}" class="btn  btn-bg-light btn-active-color-primary btn-sm">
                                           مشاهده جزئیات
                                        </a>

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
            $('#text-' + id).hide();
            $('#loader-' + id).show();
            $(this).attr('disabled', false);
            $.ajax({
                url: '{{route('admin.FastUpdate')}}',
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
                    $('#text-' + id).show();
                    $('#loader-' + id).hide();
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
