@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <p>تنظمیات</p>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                <th class="pe-2">#</th>
                                <th class="min-w-150px">عنوان</th>
                                <th class="min-w-150px">وضعیت</th>
                                <th class="text-end min-w-150px">عملیات</th>
                            </tr>
                            </thead>
                            <tbody class="fw-bold text-gray-600">
                            @if(count($list) != 0)
                                @foreach($list as $key => $value)
                                    <tr>
                                        <td>
                                            {{$key + 1}}
                                        </td>
                                        <td class="pe-0">
                                            <span class="fw-bolder">{{$value->title}}</span>
                                        </td>
                                        <td class="pe-0" data-order="منتشر شده">
                                            @if($value->status == 1)
                                                <div class="badge badge-light-success">فعال</div>
                                            @else
                                                <div class="badge badge-light-danger">غیرفعال</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                               data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">عملیات
                                                <span class="svg-icon svg-icon-5 m-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                     viewBox="0 0 24 24" fill="none"><path
                                                            d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z"
                                                            fill="currentColor"></path></svg>
                                            </span>
                                            </a>
                                            <div
                                                    class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4"
                                                    data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="{{route('admin.bot-setting-edit',[$value->id])}}"
                                                       class="menu-link px-3"> ویرایش </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
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
        {{--$(document).on('click', '.submit_btn', function () {--}}
        {{--    var id = $(this).data('id');--}}
        {{--    var man = $('#man-' + id).val();--}}
        {{--    var pva = $('#pva-' + id).val();--}}
        {{--    var land = $('#land-' + id).val();--}}
        {{--    var active = $('#active-' + id).val();--}}
        {{--    $('#text-' + id).hide();--}}
        {{--    $('#loader-' + id).show();--}}
        {{--    $(this).attr('disabled', false);--}}
        {{--    $.ajax({--}}
        {{--        url: '{{route('admin.FastUpdate')}}',--}}
        {{--        dataType: 'json',--}}
        {{--        method: "GET",--}}
        {{--        data: {--}}
        {{--            id: id,--}}
        {{--            man: man,--}}
        {{--            pva: pva,--}}
        {{--            land: land,--}}
        {{--            active: active,--}}
        {{--        },--}}
        {{--        success: function (data) {--}}
        {{--            swal({--}}
        {{--                title: "عملیات با موفقیت انجام شد",--}}
        {{--                text: data.success,--}}
        {{--                type: "success",--}}
        {{--                showCancelButton: false,--}}
        {{--                confirmButtonText: "باشه!",--}}
        {{--                closeOnConfirm: true--}}
        {{--            });--}}
        {{--            $('#text-' + id).show();--}}
        {{--            $('#loader-' + id).hide();--}}
        {{--            $(this).attr('disabled', false);--}}
        {{--        }, error: function (error) {--}}
        {{--            data.responseJSON.error.forEach(function (value) {--}}
        {{--                toastr.error(value, "خطا", {--}}
        {{--                    positionClass: "toast-top-right",--}}
        {{--                    timeOut: 5e3,--}}
        {{--                    closeButton: !0,--}}
        {{--                    debug: !1,--}}
        {{--                    newestOnTop: !0,--}}
        {{--                    progressBar: !0,--}}
        {{--                    preventDuplicates: !0,--}}
        {{--                    onclick: null,--}}
        {{--                    showDuration: "300",--}}
        {{--                    hideDuration: "1000",--}}
        {{--                    extendedTimeOut: "1000",--}}
        {{--                    showEasing: "swing",--}}
        {{--                    hideEasing: "linear",--}}
        {{--                    showMethod: "fadeIn",--}}
        {{--                    hideMethod: "fadeOut",--}}
        {{--                    tapToDismiss: !1--}}
        {{--                })--}}
        {{--            })--}}
        {{--            $('text-' + id).show();--}}
        {{--            $('loader-' + id).hide();--}}
        {{--            $(this).attr('disabled', false);--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}

        function myFunction(id) {
            /* Copy the text inside the text field */
            navigator.clipboard.writeText(id);
        }
    </script>
@endsection
