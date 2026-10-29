@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card card-flush">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">لیست صفحات</span>
                        </h3>
                        <div class="card-toolbar my-1">
                            <div class="d-flex align-items-center position-relative my-1 me-6">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                <span class="svg-icon svg-icon-3 position-absolute ms-3">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                         viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                              height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                              fill="currentColor"></rect>
														<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                              fill="currentColor"></path>
													</svg>
												</span>
                                <!--end::Svg Icon-->
                                <input type="text" id="kt_filter_search"
                                       class="form-control form-control-solid form-select-sm w-150px ps-9"
                                       placeholder="جستجو ">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        @include('admin-v2.comp.page')
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
    <script src="{{url('js/jquery.form.min.js')}}" type="text/javascript"></script>
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
