@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <div class="col-xl-12 mb-5 mb-xl-10">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder text-gray-800">کامنت ها</span>
                                </h3>
                            </div>
                            <div class="card-body pt-4 px-0">
                                <div class="tab-content px-9 hover-scroll-overlay-y pe-7 me-3 mb-2">
                                    <div class="tab-pane fade active show" id="referral">
                                        @include('admin-v2.comp.comments',['List' => $List])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="EditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content rounded">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                    </div>
                </div>
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15" id="Edit-Area">

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="FailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content rounded">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
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
    <link href="{{URL::asset('js/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{URL::asset('js/toastr/css/toastr.min.css')}}">
    <script src="{{URL::asset('js/sweetalert2/dist/sweetalert2.min.js?v=')}}"></script>
    <script src="{{URL::asset('js/toastr/js/toastr.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('button').prop('disabled', false);
            $(document).on('submit', '#ErrorForm', function () {
                $('#ErrorStatus').html('لطفا صبر کنید...');
                $('#ErrorStatus').attr('disabled', true);
                var id = $('#RequestId').val();
                $('#ErrorForm').ajaxSubmit({
                    dataType: 'json',
                    beforeSend: function () {
                    },
                    success: function (data) {
                        toastr["success"](data.msg);
                        $('#Status-' + id).empty().append(data.Html);
                        $('#ErrorStatus').html('ثبت خطا');
                        $('#ErrorStatus').attr('disabled', false);
                        $('#FailModal').modal('toggle');
                    },
                    error: function (data) {
                        console.log(data.responseJSON.error)
                        data.responseJSON.errors.forEach(function (value) {
                            toastr["error"](value);
                        });
                        $('#ErrorStatus').html('ثبت خطا');
                        $('#ErrorStatus').attr('disabled', false);
                    }
                });
                return false;
            });
        });
        function CheckComment(id) {
            $.ajax({
                url: '{{route('admin.comment-data')}}',
                dataType: "JSON",
                data:{
                    id:id
                },
                success:function (data){
                    $('#Edit-Area').empty().append(data.html);
                    $('#EditModal').modal('toggle');
                    $('.submit-btn').click(function () {
                        $(this).html('لطفا صبر کنید...');
                        $(this).attr('disabled', true);
                        let id = $(this).attr('id');
                        let formId = $(this).data('form');
                        let text = $(this).data('text');
                        let form = document.querySelector('#' + formId)
                        $(form).ajaxSubmit({
                            dataType: 'json',
                            beforeSend: function () {
                            },
                            success: function (data) {
                                toastr["success"](data.msg);
                                $('#' + id).html(text);
                                $('#' + id).attr('disabled', false);
                                $('#EditModal').modal('toggle');
                            },
                            error: function (data) {
                                console.log(data.responseJSON.error)
                                data.responseJSON.errors.forEach(function (value) {
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
                                $('#' + id).attr('disabled', false);
                                $('#' + id).html(text);

                            }
                        });

                        return false;
                    });
                }
            });
        }
        function ChangeCommentStatus(id, status) {
            $.ajax({
                url: '{{route('admin.change-comment-status')}}',
                dataType: 'JSON',
                data: {
                    id: id,
                    status: status
                },
                method: "get",
                success: function (data) {

                    $('#Status-' + id).empty().append(data.Html);
                    toastr["success"]('وضعیت با موفقیت تغییر پیدا کرد');
                },
                error: function () {
                    data.responseJSON.errors.forEach(function (value) {
                        toastr["error"](value);
                    });
                }
            })
        }
    </script>
@endsection
