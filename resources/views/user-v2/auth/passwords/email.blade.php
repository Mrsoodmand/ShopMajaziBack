@extends('site.layout.master',['title' => 'بازیابی کلمه عبور'])

@section('content')

    <div class="user-area ptb-100 mt-5">
        <div class="container">
            <div class="user-item">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('send-code')}}" id="CurrentForm">
                    {{ csrf_field() }}
                    <h2>بازیابی کلمه عبور</h2>
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="form-group">
                                <input type="tel" name="mobile" class="form-control" placeholder="شماره موبایل">
                                @if ($errors->has('mobile'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>

                    <button type="submit" id="submit_btn" class="btn common-btn">
                        ورود
                        <img src="{{URL::asset('site/assets/images/shape1.png')}}" alt="Shape">
                        <img src="{{URL::asset('site/assets/images/shape2.png')}}" alt="Shape">
                    </button>
                    <h5>حساب کاربری ندارید؟ <a href="{{ url('/user/register') }}">ثبت نام</a></h5>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footer')

    <script src="{{url('js/jquery.form.min.js')}}" type="text/javascript"></script>
    {{-- CSS --}}
    <link rel="stylesheet" href="{{url('user-panel/vendors/bower_components/sweetalert2/dist/sweetalert2.min.css')}}">
    <script src="{{url('user-panel/vendors/bower_components/sweetalert2/dist/sweetalert2.min.js')}}"></script>
    <script src="{{url('user-panel/vendors/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js')}}"></script>
    <script>

        toastr.options = {
            closeButton: !0,
            debug: !1,
            newestOnTop: !1,
            progressBar: !0,
            positionClass: "toast-top-center",
            preventDuplicates: !1,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"}

        $(document).ready(function () {

            // $('button').prop('disabled', false);
            $('#submit_btn').click(function () {
                $('#submit_btn').html('لطفا صبر کنید');
                $('#submit_btn').attr('disabled', true);
                let form = document.querySelector('#CurrentForm')

                $(form).ajaxSubmit({

                    dataType: 'json',
                    beforeSend:function (){

                    },
                    success: function (data) {


                        $('#submit_btn').html('ارسال پیام');
                        $('#submit_btn').attr('disabled', false);
                        toastr.success('کلمه عبور جدید براتون ارسال گردید.', "کاربر گرامی :")
                            setInterval(function (){
                                location.href = "{{url('/')}}";
                            },2000)



                    },
                    error: function (data) {
                        console.log(data.responseJSON.error)
                        data.responseJSON.error.forEach(function (e) {
                            toastr.error(e, "خطا")
                        }),
                        $('#submit_btn').html('ارسال پیام');
                        $('#submit_btn').attr('disabled', false);

                    }
                });

                return false;
            });
        })

    </script>

@endsection
