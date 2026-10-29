@extends('site-v2.layout.master',['MetaTitle' => "تیکت شماره $Ticket->id | پنل کاربری"])
@section('content')
    <div class="page-title-overlap bg-dark pt-4">
        <div class="container d-lg-flex justify-content-between py-2 py-lg-3">
            <div class="order-lg-2 mb-3 mb-lg-0 pt-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-light flex-lg-nowrap justify-content-center justify-content-lg-start">
                        <li class="breadcrumb-item"><a class="text-nowrap" href="{{secure_url('/')}}"><i class="ci-home"></i>خانه</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/home')}}">پروفایل</a>
                        </li>
                        <li class="breadcrumb-item text-nowrap"><a href="{{secure_url('/user/tickets')}}">تیکت ها</a></li>
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">تیکت
                            شماره {{$Ticket->id}}</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">تیکت شماره {{$Ticket->id}}</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
         @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-9">
                <!-- Toolbar-->
                <div class="d-none d-lg-flex justify-content-between align-items-center pt-lg-3 pb-4 pb-lg-5 mb-lg-4">
                    <div class="d-flex w-100 text-light text-center me-3">
                        <div class="fs-ms px-3">
                            <div class="fw-medium">عنوان</div>
                            <div class="opacity-60">{{$Ticket->subject}}</div>
                        </div>
                        <div class="fs-ms px-3">
                            <div class="fw-medium">دپارتمان</div>
                            <div class="opacity-60">@lang('all.Ticket-Type.'.$Ticket->service)</div>
                        </div>
                        <div class="fs-ms px-3">
                            <div class="fw-medium">وضعیت</div>{{UserTicketStatus($Ticket->status)}}
                        </div>
                        <div class="fs-ms px-3">
                            <div class="fw-medium">تاریخ ارسال</div>
                            <div class="opacity-60">{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($Ticket->created_at))}}</div>
                        </div>
                        <div class="fs-ms px-3">
                            <div class="fw-medium">آخرین به روز رسانی</div>
                            <div class="opacity-60">{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($Ticket->updated_at))}}</div>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm reply"><i class="fa-regular fa-reply me-2"></i>پاسخ</button>
                </div>
                <!-- Ticket details (visible on mobile)-->
                <div class="d-flex d-lg-none flex-wrap bg-secondary text-center rounded-3 pt-4 px-4 pb-1 mb-4">
                    <div class="fs-sm px-3 pb-3">
                        <div class="fw-medium">عنوان</div>
                        <div class="text-muted">{{$Ticket->subject}}</div>
                    </div>
                    <div class="fs-sm px-3 pb-3">
                        <div class="fw-medium">دپارتمان</div>
                        <div class="text-muted">@lang('all.Ticket-Type.'.$Ticket->service)</div>
                    </div>
                    <div class="fs-sm px-3 pb-3">
                        <div class="fw-medium">وضعیت</div>
                        {{UserTicketStatus($Ticket->status)}}
                    </div>
                    <div class="fs-sm px-3 pb-3">
                        <div class="fw-medium">تاریخ ارسال</div>
                        <div class="text-muted">{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($Ticket->created_at))}}</div>
                    </div>
                    <div class="fs-sm px-3 pb-3">
                        <div class="fw-medium">آخرین به روز رسانی</div>
                        <div class="text-muted">{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($Ticket->updated_at))}}</div>
                    </div>
                </div>
                <!-- Comment-->
                @foreach($TicketDetail as $items)
                    @if($items->type == 'user')
                        <div class="d-flex align-items-start pb-4 border-bottom mb-3" style="position: relative">
                            <div class="ps-3">
                                <h6 class="fs-md mb-2">شما:</h6>
                                <p class="fs-md mb-1">{{$items->detail}}</p>
                                @if(!is_null($items->file))
                                    <div style="position: absolute;left: 0;bottom: 20px">
                                        <a href="{{secure_url($items->file)}}" target="_blank" style="float: left" class="btn btn-info btn-sm">فایل ضمیمه</a>
                                        <br>
                                    </div>
                                @endif
                                <span class="fs-ms text-muted"><i class="ci-time align-middle me-2"></i>{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($items->created_at))}}</span>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-start pb-4 border-bottom mb-3" style="position: relative">
                            <div class="ps-3">
                                <h6 class="fs-md mb-2">پاسخ پشتیبان:</h6>
                                <p class="fs-md mb-1">{{$items->detail}}</p>
                                @if(!is_null($items->file))
                                    <div style="position: absolute;left: 0;bottom: 20px">
                                        <a href="{{secure_url($items->file)}}" target="_blank" style="float: left" class="btn btn-info btn-sm">فایل ضمیمه</a>
                                        <br>
                                    </div>
                                @endif
                                <span class="fs-ms text-muted"><i class="ci-time align-middle me-2"></i>{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($items->created_at))}}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
                <!-- Leave message-->
                <h3 class="h5 mt-2 pt-4 pb-2" id="reply">پاسخ</h3>
                <form action="{{(isset($Ticket) ? route('user.ticket-update') :route('user.ticket-submit'))}}"
                      id="CurrentForm" method="post" class="needs-validation" novalidate=""
                      enctype="multipart/form-data">
                    <div class="mb-3">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{$Ticket->id}}">
                        <textarea class="form-control" rows="8" placeholder="پیام خود را اینجا بنویسید ..."
                                  name="detail" required=""></textarea>
                        <div class="invalid-tooltip">لطفا پیام را بنویسید</div>
                    </div>
                    <div class="col-12">
                        <input class="form-control" name="file" type="file" id="file-input">
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <button class="btn btn-primary my-2 submit-btn" id="TicketBtn" data-text="ارسال پاسخ"
                                data-form="CurrentForm" type="button">ارسال پاسخ
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
@section('header')
@endsection

@section('footer')
    <script>
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
