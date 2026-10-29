@extends('site-v2.layout.master',['MetaTitle' => $Title])
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="index.html"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">پروفایل</a></li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/orders')}}">لیست سفارشات</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">جزئیات
                            سفارش {{$Order->id}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">جزئیات سفارش {{$Order->id}}</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
        @include('user-v2.layout.menu')
        <!-- Content  -->
            <section class="col-lg-9">
                <div class="card border-0 shadow-lg mt-3">
                    <div class="card-body pb-2" style="border-radius: 10px;background-color: aliceblue">
                        <div class="row">
                            <div class="col-lg-4 col-12">
                                <div class="nav-link">
                                    <div class="d-flex align-items-center">
                                        <div class="ps-3">
                                            <div class="media-tab-subtitle text-muted fs-sm mb-1">نوع سفارش:</div>
                                            <h6 class="media-tab-title fs-md text-nowrap mb-0">  @lang('order.type.'.$Order->type) </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="nav-link">
                                    <div class="d-flex align-items-center">
                                        <div class="ps-3">
                                            <div class="media-tab-subtitle text-muted fs-sm mb-1">وضعیت سفارش:</div>
                                            <h6 class="media-tab-title fs-md text-nowrap mb-0">@lang('order.status.'.$Order->status)</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="nav-link">
                                    <div class="d-flex align-items-center">
                                        <div class="ps-3">
                                            <div class="media-tab-subtitle text-muted fs-sm mb-1">تاریخ ثبت:</div>
                                            <h6 class="media-tab-title fs-md text-nowrap mb-0"> {{$jdf->jdate('d F Y',strtotime($Order->created_at))}}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm mt-3">
                    <ul class="nav nav-tabs nav-justified mt-3" role="tablist">
                        <li class="nav-item"><a class="nav-link px-0 active" href="#profile" data-bs-toggle="tab"
                                                role="tab">
                                <div class="d-none d-lg-block"><i
                                            class="fa-regular fa-circle-info opacity-60 me-2"></i>جزئیات سفارش
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-regular fa-circle-info opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">جزئیات سفارش</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#notifications" data-bs-toggle="tab" role="tab">
                                <div class="d-none d-lg-block"><i
                                            class="fa-regular fa-credit-card opacity-60 me-2"></i>جزئیات تراکنش
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-regular fa-credit-card opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">جزئیات تراکنش</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#payment" data-bs-toggle="tab" role="tab">
                                <div class="d-none d-lg-block"><i class="fa-regular fa-comment opacity-60 me-2"></i>پشتیبانی
                                </div>
                                <div class="d-lg-none text-center">
                                    <i class="fa-regular fa-comment opacity-60 d-block fs-xl mb-2"></i>
                                    <span class="fs-ms">پشتیبانی</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content">
                    <!-- Order Detail -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        @include('user-v2.page.'.$Page,[$Order,$OrderItems])
                    </div>
                    <!-- Transaction Detail-->
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <div class="card border-0 shadow-lg mt-3">
                            <div class="card-header">
                                <h4>تراکنشات</h4>
                            </div>
                            <div class="card-body pb-2">
                                <div class="table-responsive fs-sm mb-4">
                                    @include('user-v2.layout.payments',['list' => $OrderPayment])
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Chat-->
                    <div class="tab-pane fade" id="payment" role="tabpanel">
                        <div class="card border-0 shadow-lg mt-3">
                            <div class="card-header">
                                <h4>گفتگو با پشتیبان</h4>
                            </div>
                            @if(count($OrderChats) != 0)
                                <div class="card-body"
                                     style="height: auto!important;overflow: overlay;max-height: 500px">
                                    @foreach($OrderChats as $chats)
                                        @if($chats->type == 'user')
                                            <div class="d-flex align-items-start pb-4 border-bottom mt-2"
                                                 style="position: relative">
                                                <div class="ps-3">
                                                    <h6 class="fs-md mb-2">شما:</h6>
                                                    <p class="fs-md mb-1">{{$chats->text}}</p>
                                                    <span class="fs-ms text-muted"><i
                                                                class="ci-time align-middle me-2"></i>{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($chats->created_at))}}</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-start pb-4 border-bottom mt-2"
                                                 style="position: relative">
                                                <div class="ps-3">
                                                    <h6 class="fs-md mb-2"> پشتیبان:</h6>
                                                    <p class="fs-md mb-1">{{$chats->text}}</p>
                                                    <span class="fs-ms text-muted"><i
                                                                class="ci-time align-middle me-2"></i>{{$jdf->jdate('Y-m-d ساعت: H:i',strtotime($chats->created_at))}}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @else
                                        <div class="card-body">
                                            <div class="alert alert-info d-flex fs-sm mb-4 mt-5" role="alert">
                                                <div class="alert-icon"><i class="ci-announcement"></i></div>
                                                <div>گفتگویی برای این سفارش ثبت نشده است.</div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <h3 class="h5 mt-2 pt-4 pb-2" id="reply">پاسخ</h3>
                                            <form action="{{route('user.chat-order')}}"
                                                  id="CurrentForm" method="post" class="needs-validation" novalidate=""
                                                  enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{$Order->id}}">
                                                    <textarea class="form-control" rows="8"
                                                              placeholder="پیام خود را اینجا بنویسید ..."
                                                              name="message" required=""></textarea>
                                                    <div class="invalid-tooltip">لطفا پیام را بنویسید</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-8">
                                                        <input class="form-control my-2" name="file" type="file"
                                                               id="file-input">
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center">
                                                            <button class="btn btn-primary my-2 w-100 submit-btn"
                                                                    id="TicketBtn"
                                                                    data-text="ارسال پاسخ"
                                                                    data-form="CurrentForm" type="button">ارسال پاسخ
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
@section('header')
    <link rel="stylesheet" href="{{secure_url('fontawesome/web/css/all.css?v=23')}}">
    
    @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
        <style>
            .mt-6 {
                margin-top: 6rem !important;
            }
        </style>
    @endif
@endsection

@section('footer')
    @if($Order->type == 5)
        <script>
            $(document).on('change', '.select-volume', function () {
                var selected = $(this).find('option:selected').data('price');
                var UserWallet = {{CheckWallet()}};
                var id = $(this).val();
                if (UserWallet >= selected) {
                    $('#second-btn').remove();
                    $('#btn-area').before('<a id="second-btn" data-type="' + id + '" type="button"class="volume-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' + id + '" style="width: 47%">پرداخت با کیف پول</a>');
                } else {
                    $('#second-btn').remove();
                    $('#btn-area').before('<a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>');
                }
            });

            $(document).on('change', '.select-volume-renew', function () {
                var selected = $(this).find('option:selected').data('price');
                var plan = $('.select-renew-plan').find('option:selected').data('price');
                selected = plan + selected;
                var UserWallet = {{CheckWallet()}};
                var id = $(this).val();
                if (UserWallet >= selected) {
                    $('#second-btn-renew').remove();
                    $('#btn-area-renew').before('<a id="second-btn-renew" data-type="' + id + '" type="button"class="reNew-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' + id + '" style="width: 47%">پرداخت با کیف پول</a>');
                } else {
                    $('#second-btn-renew').remove();
                    $('#btn-area-renew').before('<a id="second-btn-renew" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>');
                }
            });
            $(document).on('change', '.select-renew-plan', function () {
                var selected = $(this).find('option:selected').data('price');
                var plan = $('.select-volume-renew').find('option:selected').data('price');
                selected = parseInt(plan) + parseInt(selected);
                var UserWallet = {{CheckWallet()}};
                var id = $(this).val();
                if (UserWallet >= selected) {
                    $('#second-btn-renew').remove();
                    $('#btn-area-renew').before('<a id="second-btn-renew" data-type="' + id + '" type="button"class="reNew-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet" data-id="' + id + '" style="width: 47%">پرداخت با کیف پول</a>');
                } else {
                    $('#second-btn-renew').remove();
                    $('#btn-area-renew').before('<a id="second-btn-renew" class="login-btn btn btn-info btn-shadow d-block pe-3" style="width: 47%" type="submit">شارژ کیف پول</a>');
                }
            });

            <!-- خرید حجم با کیف پول -->
            $(document).on('click', '.volume-with-wallet', function () {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{secure_url('user/payment/wallet')}}/7/' + id + '',
                    dataType: 'JSON',
                    method: 'GET',
                    data: {
                        ip_id: {{$Order->id}},
                    },
                    beforeSend: function () {
                        $('.buy-with-wallet').prop('disabled', true);
                        $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
                    },
                    success: function (data) {
                        success('حجم با موفقیت خریداری شد.');
                        $('.buy-with-wallet').prop('disabled', false);
                        $('.buy-with-wallet').empty().text('خرید با کیف پول');
                        location.href = data.url;
                    },
                    error: function (err) {
                        var Error = err.responseJSON.error;
                        Error.forEach(function (value) {
                            error(value);
                        });
                        $('.buy-with-wallet').prop('disabled', false);
                        $('.buy-with-wallet').empty().text('خرید با کیف پول');
                    }
                });
            });

            $(document).on('click', '.reNew-with-wallet', function () {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{secure_url('user/payment/wallet')}}/8/1',
                    dataType: 'JSON',
                    method: 'GET',
                    data: {
                        ip_id: {{$Order->id}},
                        plan_id: $('#reNew-plan-id').val(),
                        volume_id: $('#reNew-volume-id').val(),
                    },
                    beforeSend: function () {
                        $('.buy-with-wallet').prop('disabled', true);
                        $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
                    },
                    success: function (data) {
                        success('حجم با موفقیت خریداری شد.');
                        $('.buy-with-wallet').prop('disabled', false);
                        $('.buy-with-wallet').empty().text('خرید با کیف پول');
                        location.href = data.url;
                    },
                    error: function (err) {
                        var Error = err.responseJSON.error;
                        Error.forEach(function (value) {
                            error(value);
                        });
                        $('.buy-with-wallet').prop('disabled', false);
                        $('.buy-with-wallet').empty().text('خرید با کیف پول');
                    }
                });
            });
        </script>
    @endif

    <script>

        $(document).on('click', '.show-code', function () {
            var id = $(this).data('id');
            var input = $(this).data('input');
            var code = $(this).data('code');
            $(this).addClass('hide-code').removeClass('show-code');
            $('#' + input + '-' + id).empty().text(code);
            $('#' + input + '-eye-' + id).removeClass('fa-eye').addClass('fa-eye-slash');
            $(this).attr('data-type', 1);
        });
        $(document).on('click', '.hide-code', function () {
            var id = $(this).data('id');
            var input = $(this).data('input');
            var code = $(this).data('code');
            $(this).removeClass('hide-code').addClass('show-code');
            $('#' + input + '-' + id).empty().text('*****');
            $('#' + input + '-eye-' + id).addClass('fa-eye').removeClass('fa-eye-slash');
            $(this).attr('data-type', 0);
        });

        function myFunction(id) {
            /* Copy the text inside the text field */
            navigator.clipboard.writeText(id);
            /* Alert the copied text */
            toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
                timeOut: 3000,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                positionClass: "toast-top-right",
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
        }

        $(document).ready(function () {
            $('.reply').click(function () {
                $([document.documentElement, document.body]).animate({scrollTop: $("#reply").offset().top - 250}, 2000);
            });
            $('button').prop('disabled', false);
            $('.submit-btn').click(function () {
                var BtnId = $(this).attr('id');
                var text = $(this).data('text');
                var form = $(this).data('form');
                $('#' + BtnId).html('لطفا صبر کنید...');
                $('#' + BtnId).attr('disabled', true);
                form = document.querySelector('#' + form);
                $(form).ajaxSubmit({
                    dataType: 'json',
                    beforeSend: function () {
                        $('#' + BtnId).html('لطفا صبر کنید');
                    },
                    success: function (data) {
                        success(data.success)
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    },
                    error: function (data) {
                        console.log('error')
                        if (data.responseJSON.message) {
                            error('');
                        } else {
                            data.responseJSON.error.forEach(function (value) {
                                error(value);
                            });
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);

                    }
                });

                return false;
            });
        });
    </script>

@endsection
