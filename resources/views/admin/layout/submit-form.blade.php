
<script src="{{url('js/jquery.form.min.js')}}" type="text/javascript"></script>
{{-- CSS --}}
<link href="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{URL::asset('admin/vendor/toastr/css/toastr.min.css')}}">

<script src="{{URL::asset('admin/vendor/sweetalert2/dist/sweetalert2.min.js?v=')}}"></script>

<script src="{{URL::asset('admin/vendor/toastr/js/toastr.min.js')}}"></script>

<script>
    $(document).ready(function () {
        $('button').prop('disabled', false);
        $('#submit_btn').click(function () {
            $('#submit_btn').html('لطفا صبر کنید');
            $('#submit_btn').attr('disabled', true);
            let form = document.querySelector('#CurrentForm')
            $(form).ajaxSubmit({
                dataType: 'json',
                beforeSend:function (){},
                success: function (data) {
                    $('#submit_btn').html('ذخیره');
                    swal({
                        title: "عملیات با موفقیت انجام شد",
                        text: data.success,
                        type: "success",
                        showCancelButton: false,
                        confirmButtonText: "باشه!",
                        closeOnConfirm: true});
                    $('#submit_btn').html('ذخیره');
                    $('#submit_btn').attr('disabled', false);
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
                        });
                    });
                    $('#submit_btn').html('ذخیره');
                    $('#submit_btn').attr('disabled', false);

                }
            });
            return false;
        });
    });

</script>
