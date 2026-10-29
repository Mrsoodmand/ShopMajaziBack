@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4">شماره تراکنش</th>
            <th class="min-w-75px">مبلغ</th>
            <th class="min-w-75px">تاریخ ثبت</th>
            <th class="min-w-75px">توضیحات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Payments) != 0)
            @foreach($Payments as $payment)
                <tr>
                    <td class="ps-4">
                        {{$payment->id}}
                    </td>
                    <td><a href="javascript:;" class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{number_format($payment->price)}}</a><span class="text-muted fw-bold d-block fs-7">تومان</span></td>
                    <td>
                        <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($payment->created_at))}}</label>
                        <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($payment->created_at))}}</span>
                    </td>
                    <td style="padding-left: 20px!important;">
                      {{$payment->help}}
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="9">
                    <a href="javascript:;"
                       class="text-dark text-center fw-bolder text-hover-primary d-block mb-1 fs-6">پورسانتی دریافت نشده</a>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<script>

</script>

