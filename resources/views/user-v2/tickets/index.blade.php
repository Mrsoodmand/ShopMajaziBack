@extends('site-v2.layout.master',['MetaTitle' => 'تیکت ها | پنل کاربری'])
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
                        <li class="breadcrumb-item text-nowrap active" aria-current="page">تیکت ها</li>
                    </ol>
                </nav>
            </div>
            <div class="order-lg-1 pe-lg-4 text-center text-lg-start">
                <h1 class="h3 text-light mb-0">تیکت ها</h1>
            </div>
        </div>
    </div>
    <div class="container pb-5 mb-2 mb-md-4">
        <div class="row">
            <!-- Sidebar-->
            @include('user-v2.layout.menu')
            <!-- Content  -->
            <section class="col-lg-9">
                <div class="d-none d-lg-flex justify-content-between align-items-center pt-lg-3 pb-4 pb-lg-5 mb-lg-3">
                    <h6 class="fs-base text-light mb-0"> تیکت های شما:</h6>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#CurrentForm"><i
                                class="fa-duotone fa-messages me-2"></i>ارسال تیکت
                    </button>
                </div>
                <div>
                    <div class="tab-content">
                        @include('user-v2.layout.tickets',['Tickets'=>$list])
                    </div>
                </div>
            </section>
        </div>
    </div>
    <form class="needs-validation modal fade" method="post" action="{{(isset($Ticket) ? route('user.ticket-update') :route('user.ticket-submit'))}}" id="CurrentForm" tabindex="-1" novalidate="" style="display: none;" aria-hidden="true">
        @csrf
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ارسال تیکت جدید</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted fs-sm">ما به طور معمول ظرف 2 روز کاری تیکت را پاسخ می دهیم.</p>
                    <div class="row gx-4 gy-3">
                        <div class="col-12">
                            <label class="form-label" for="ticket-subject">موضوع</label>
                            <input class="form-control" name="subject" type="text" id="ticket-subject" required="">
                            <div class="invalid-feedback">لطفا موضوع را پر کنید!</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="ticket-type">بخش مورد نظر</label>
                            @php $Type = __('all.Ticket-Type'); @endphp
                            <select class="form-select" name="service" id="ticket-type" required="">
                                <option value="">انتخاب نوع</option>
                                @foreach($Type as $key => $item)
                                    <option value="{{$key}}">{{$item}}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">لطفاً نوع تیکت را انتخاب کنید!</div>
                        </div>
                        <div class="col-sm-6" id="Orders">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="ticket-description">مسئله خود را شرح دهید</label>
                            <textarea class="form-control" name="detail" id="ticket-description" rows="8" required=""></textarea>
                            <div class="invalid-feedback">لطفا توضیحات تیکت را ارائه دهید!</div>
                        </div>
                        <div class="col-12">
                            <input class="form-control" name="file" type="file" id="file-input">
                        </div>
                    </div>
                </div>
                @csrf
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">بستن</button>
                    <button class="btn btn-primary submit-btn" id="TicketBtn" data-text="ارسال تیکت" data-form="CurrentForm" type="button">ارسال تیکت</button>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('header')
@endsection

@section('footer')
    <script>
        $(document).ready(function () {
            $('button').prop('disabled', false);
            $('.submit-btn').click(function () {
                console.log('btn-1')
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
                        location.href = data.url
                    },
                    error: function (data) {
                        console.log('error')
                        if(data.responseJSON.message){
                            error('')
                        }else{
                            data.responseJSON.error.forEach(function (value) {
                                error(value);
                            })
                        }
                        $('#' + BtnId).html(text);
                        $('#' + BtnId).attr('disabled', false);

                    }
                });

                return false;
            });
        })

        $('#ticket-type').change(function () {
            var val = $(this).val();
            if (val == 1) {
                $('#Orders').append('<label class="form-label" for="ticket-priority">شماره سفارش</label>' +
                    '<select class="form-select" name="order" id="ticket-priority">' +
                        @foreach($Orders as $key => $item)
                            '<option value="{{$item->id}}">{{$item->id}} - @lang('order.type.'.$item->type)</option>' +
                        @endforeach
                            ' </select>' +
                    '<div class="invalid-feedback">لطفا شماره سفارش را انتخاب نمایید.</div>' +
                    '')
            } else {
                $('#Orders').empty();
            }
        });
    </script>

@endsection
