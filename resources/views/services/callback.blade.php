@extends('site-v2.layout.master')
@section('content')

    <div class="container pb-5 mb-sm-4">
        <div class="pt-5">
            <div class="card py-3 mt-sm-3">
                <div class="card-body text-center">
                    @if($Status['status'] == 1)
                        <img src="{{secure_url('/upload/green-check.png')}}" alt="پرداخت با موقیت انجام شد."
                             style="width: 100px;margin-bottom: 10px">
                        <h2 class="h4 pb-3 text-center">از اعتماد به ما سپاس گذاریم.</h2>
                        <p class="fs-sm mb-2">سفارش شما با موفقیت ثبت شد.</p>
                        <p class="fs-sm mb-2">شماره صورتحساب: <span class="fw-medium">{{$Status['orderId']}}</span></p>
                        @if($Status['refId'] == 'wallet')
                            <p class="fs-sm mb-2">نوع پرداخت: <span class="fw-medium">کیف پول</span></p>
                        @else
                            <p class="fs-sm mb-2">نوع پرداخت: <span class="fw-medium">درگاه بانک</span></p>
                            <p class="fs-sm mb-2">کد رهگیری بانکی: <span class="fw-medium">{{$Status['refId']}}</span></p>
                        @endif
                        <a class="btn btn-primary mt-3" href="{{$Status['Url']}}">
                            <i class="fa-light fa-bags-shopping me-2"></i>بازگشت به ربات
                        </a>
                    @else
                        <img src="{{secure_url('/upload/red-check.png')}}" alt="خطا در تراکنش"
                             style="width: 100px;margin-bottom: 10px">
                        <h2 class="h4 pb-3 text-center">خطایی بوجود اومده!!</h2>
                        <p class="fs-sm mb-2">متن خطا: </p>
                        <p class="fs-sm">{{$Status['msg']}}</p>
                        <a class="btn btn-secondary mt-3 me-3" href="{{$Status['Url']}}">برگشت به ربات</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('header')
@endsection

@section('footer')

@endsection
