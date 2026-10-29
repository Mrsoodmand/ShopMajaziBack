<div class="mb-13 text-center">
    <h1 class="mb-3">جزئیات تراکنش شماره: {{$Payment->id}}</h1>
</div>
<div class="row g-9 mb-8">
    <div class="col-md-12 fv-row fv-plugins-icon-container">
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">نام کاربر</div>
            <div class="d-flex fw-bolder">{{$Payment->User->name}}</div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">شماره کاربر</div>
            <div class="d-flex fw-bolder">{{$Payment->User->user_id}}</div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">مشاهده کاربر</div>
            <div class="d-flex fw-bolder"><a target="_blank" href="{{route('admin.bot-user-single',[$Payment->User->id])}}">مشاهده</a>
            </div>
        </div>
        <div class="separator separator-dashed"></div>
    </div>
</div>
<div class="row g-9 mb-8">
    <div class="col-md-12 fv-row fv-plugins-icon-container">
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">شماره تراکنش</div>
            <div class="d-flex fw-bolder">{{$Payment->id}}</div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">وضعیت پرداخت</div>
            <div class="d-flex fw-bolder">{{BotPaymentStatus($Payment->status)}}</div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">نوع تراکنش</div>
            <div class="d-flex fw-bolder">{{__('bot.paymentType.'.$Payment->type)}}
            </div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">نوع پرداخت</div>
            <div class="d-flex fw-bolder">{{__('bot.paymentMethod.'.$Payment->method)}}</div>
        </div>
        <div class="separator separator-dashed"></div>
        <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
            <div class="fw-bold">مبلغ</div>
            <div class="d-flex fw-bolder">{{number_format($Payment->price)}} تومان</div>
        </div>
        <div class="separator separator-dashed"></div>
        @if($Payment->method == 'bank')
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">کد رهیگری</div>
                <div class="d-flex fw-bolder">
                    @if(!is_null($Payment->ref_id))
                        {{$Payment->ref_id}}
                    @else
                        ---
                    @endif
                </div>
            </div>
            <div class="separator separator-dashed"></div>
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">شماره کارت</div>
                <div class="d-flex fw-bolder" style="direction: ltr">@if(!is_null($Payment->card))
                        {{$Payment->card}}
                    @else
                        ---
                    @endif</div>
            </div>
            <div class="separator separator-dashed"></div>
        @endif
        @if($Payment->help != null)
            <div class="separator separator-dashed"></div>
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">توضیحات</div>
                <div class="d-flex fw-bolder">{{$Payment->help}}</div>
            </div>
            <div class="separator separator-dashed"></div>
        @endif
        @if($Payment->error != null)
            <div class="separator separator-dashed"></div>
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">خطا</div>
                <div class="d-flex fw-bolder">{{$Payment->error}}</div>
            </div>
            <div class="separator separator-dashed"></div>
        @endif
    </div>
</div>
@if(!is_null($Payment->order_id))
    <div class="row g-9 mb-8">
        <div class="col-md-12 fv-row fv-plugins-icon-container">
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">شماره سفارش</div>
                <div class="d-flex fw-bolder">{{$Payment->Order->id}}</div>
            </div>
            <div class="separator separator-dashed"></div>

            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">نام سفارش</div>
                <div class="d-flex fw-bolder">{{$Payment->Order->name}}</div>
            </div>
            <div class="separator separator-dashed"></div>
            <div class="mt-2 fs-6 d-flex justify-content-between mb-4">
                <div class="fw-bold">مشاهده سفارش</div>
                <div class="d-flex fw-bolder"><a target="_blank"
                                                 href="{{route('admin.bot-order-single',$Payment->Order->id)}}">مشاهده</a>
                </div>
            </div>
        </div>
    </div>
@endif
<div></div>

