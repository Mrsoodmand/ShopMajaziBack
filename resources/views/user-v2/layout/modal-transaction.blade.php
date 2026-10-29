@php $jdf = new \App\lib\Jdf(); @endphp
<div class="modal-header bg-secondary">
    <ul class="nav nav-tabs card-header-tabs" role="tablist">
        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header">جزئیات تراکنش
                شماره {{$Payment->id}}</label></li>
    </ul>
    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body tab-content py-4">
    <ul class="list-unstyled mb-0">
        <li class="border-bottom mb-0">
            <label class="d-flex align-items-center px-4 py-3">
                نوع تراکنش:
                <span class="fs-sm text-muted ms-auto">@lang('payments.type.'.$Payment->type)</span>
            </label>
        </li>
        <li class="border-bottom mb-0">
            <label class="d-flex align-items-center px-4 py-3">
                مبلغ تراکنش:
                <span class="fs-sm text-muted ms-auto">{{number_format($Payment->price)}} تومان</span>
            </label>
        </li>
        <li class="border-bottom mb-0">
            <label class="d-flex align-items-center px-4 py-3">
                وضعیت پرداخت:
                <span class="fs-sm text-muted ms-auto">{{UserPaymentStatus($Payment->status)}}</span>
            </label>
        </li>
        <li class="border-bottom mb-0">
            <label class="d-flex align-items-center px-4 py-3">
                نوع پرداخت:
                <span class="fs-sm text-muted ms-auto">@if($Payment->gate == 'Wallet')
                        کیف پول
                    @elseif($Payment->gate == 'Bank')
                        بانک
                    @else
                        ---
                    @endif</span>
            </label>
        </li>
        @if($Payment->status == 'Complete')
            <li class="border-bottom mb-0">
                <label class="d-flex align-items-center px-4 py-3">
                    تاریخ پرداخت:
                    <span class="fs-sm text-muted ms-auto">{{$jdf->jdate('d F Y',strtotime($Payment->paid_at))}}</span>
                </label>
            </li>
        @endif
        @if($Payment->gate == 'Bank')
            <li class="border-bottom mb-0">
                <label class="d-flex align-items-center px-4 py-3">
                    شماره پیگیری:
                    <span class="fs-sm text-muted ms-auto">@if(!is_null($Payment->refid)) {{$Payment->refid}} @else --- @endif</span>
                </label>
            </li>
        @endif
        @if($Payment->type == 5)
        <li class="border-bottom mb-0">
            <label class="d-flex align-items-center px-4 py-3">
                توضیحات:
                <span class="fs-sm text-muted ms-auto">پورسانت</span>
            </label>
        </li>
        @elseif($Payment->type == 6)
            <li class="border-bottom mb-0">
                <label class="d-flex align-items-center px-4 py-3">
                    توضیحات:
                    <span class="fs-sm text-muted ms-auto">{{$Payment->help}}</span>
                </label>
            </li>
        @endif
    </ul>
</div>
@if($Payment->status == 'waiting to pay')
    <form method="post" action="{{route('user.service-bank-payment')}}">
    <div class="modal-footer justify-content-center">

            @csrf
            <input type="hidden" name="transaction_id" value="{{$Payment->id}}">
            <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                    style="width: 47%" type="submit">پرداخت با درگاه
            </button>
    </div>
    </form>
@endif
