@php $title = 'تنظمیات ربات'; @endphp
@extends('admin-bot.layout.master',['metaTitle' => $title])
@section('header')

@endsection


@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="toolbar d-flex flex-stack mb-3 mb-lg-5" id="kt_toolbar">
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack flex-wrap">
                <div class="page-title d-flex flex-column me-5 py-2">
                    <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">{{$title}}</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="index.html" class="text-muted text-hover-primary">خانه</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-gray-900">{{$title}}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card ms-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">{{$title}}</span>
                        </h3>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            <table class="table table-row-bordered  table-hover table-row-gray-100 align-middle gs-0 gy-3">
                                <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="">#</th>
                                    <th class="min-w-140px text-center">عنوان</th>
                                    <th class="min-w-120px text-center"> وضعیت</th>
                                    <th class="min-w-100px text-center"> عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($list) != 0)
                                    @foreach($list as $key => $item)
                                        <tr>
                                            <td>
                                                {{$key + 1}}
                                            </td>
                                            <td class="text-center">
                                                <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$item->title}}</label>
                                            </td>
                                            <td class="text-center">
                                                @if($item->status == 1)
                                                    <div class="badge badge-light-success">فعال</div>
                                                @else
                                                    <div class="badge badge-light-danger">غیرفعال</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="javascript:;" data-id="{{$item->id}}" data-text="مشاهده جزئیات" id="btn-{{$item->id}}"
                                                   class="check-message btn btn-bg-light btn-active-color-primary btn-sm ">
                                                    مشاهده جزئیات
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            پیامی یافت نشد.
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-lg fade" id="PaymentModal" tabindex="-1" style="display: none;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="PaymentDetail">

            </div>
        </div>
    </div>
@endsection


@section('footer')

    <script src="{{secure_url('js/jquery.form.min.js')}}" type="text/javascript"></script>
    {{-- CSS --}}
    <link href="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{URL::asset('admin/vendor/toastr/css/toastr.min.css')}}">

    <script src="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.js?v=')}}"></script>

    <script src="{{URL::asset('admin/vendor/toastr/js/toastr.min.js')}}"></script>

    <script>
        $(document).ready(function () {
            $('button').prop('disabled', false);
            $(document).on('click','.submit_btn',function (){
                $('.submit_btn').html('لطفا صبر کنید');
                $('.submit_btn').attr('disabled', true);
                let form = document.querySelector('#CurrentForm')
                $(form).ajaxSubmit({
                    dataType: 'json',
                    beforeSend:function (){},
                    success: function (data) {
                        $('.submit_btn').html('ذخیره');
                        swal({
                            title: "عملیات با موفقیت انجام شد",
                            text: data.success,
                            type: "success",
                            showCancelButton: false,
                            confirmButtonText: "باشه!",
                            closeOnConfirm: true});
                        $('.submit_btn').html('ذخیره');
                        $('.submit_btn').attr('disabled', false);
                        if (data.url != null)
                        {
                            setInterval(function (){location.href = data.url;},2000)
                        }else{
                            setInterval(function (){location.reload();},2000)
                        }
                    },
                    error: function (data) {
                        console.log(data)
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
                        $('.submit_btn').html('ذخیره');
                        $('.submit_btn').attr('disabled', false);

                    }
                });
                return false;
            });
        });

    </script>

    <script>
        $(document).on('click', '.check-message', function () {
            var id = $(this).data('id');
            var text = $(this).data('text');
            var btnId = $(this).attr('id');
            $(this).empty().text('لطفا صبر کنید...');

            $.ajax({
                url: '{{route('admin.bot-settings-edit')}}',
                dataType: 'JSON',
                method: "GET",
                data: {
                    id: id
                }, success: function (data) {
                    $('#PaymentDetail').empty().append(data.data);
                    $('#PaymentModal').modal('toggle');
                    $('#' + btnId).empty().text(text);
                }, error: function (data) {
                    console.log(data)
                }
            });
        });
    </script>

    <script>
        $(document).on('click','.add-card',function (){
            $('#card-area').append('<div class="row mt-2"><div class="col-4"><div class="d-flex flex-column fv-row"><label class="d-flex align-items-center fs-6 fw-semibold mb-2"><span class="required">شماره کارت</span></label><input type="tel" class="form-control" name="card[]" value=""></div></div><div class="col-4"><div class="d-flex flex-column fv-row"><label class="d-flex align-items-center fs-6 fw-semibold mb-2"><span class="required">به نام</span></label><input type="text" class="form-control" name="name[]" value=""></div></div><div class="col-2"><label class="d-flex align-items-center fs-6 fw-semibold mb-2"><span class="required">وضعیت</span></label><select name="card_status[]" id="" class="form-select"><option value="1">فعال</option><option value="-1">غیرفعال</option></select></div><div class="col-2"><button type="button" onclick="$(this).parent().parent().remove()"  class="btn btn-danger w-100 mt-8"><span class="indicator-label">حذف</span></button></div></div>')
        });

    </script>


@endsection
