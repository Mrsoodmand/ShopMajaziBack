<table class="table table-hover mb-0">
    <thead>
    <tr>
        <th>#</th>
        <th>مبلغ</th>
        <th>نوع تراکنش</th>
        <th>وضعیت</th>
        <th>تاریخ</th>
        <th>توضیحات</th>
    </tr>
    </thead>
    <tbody>
    @if(count($list) != 0)
        @foreach($list as $value)
            <tr>
                <td>{{$value->id}}</td>
                <td>{{number_format($value->price)}} تومان</td>
                <td>@lang('payments.type.'.$value->type)</td>
                <td>{{UserPaymentStatus($value->status)}}</td>
                <td>{{$jdf->jdate('d F Y',strtotime($value->created_at))}}</td>
                <td><a href="javascript:;" onclick="CheckTransaction({{$value->id}})"> مشاهده </a></td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="6" class="text-center">تراکنشی ثبت نشده است!</td>
        </tr>
    @endif
    </tbody>
</table>
<script>
    function CheckTransaction(id) {
        $.ajax({
            url: "{{route('user.check-transaction-detail')}}",
            dataType: "json",
            method: 'GET',
            data: {
                id: id
            },
            success: function (data) {
                $('#Payment-Modal-Body').empty().append(data.success);
                $('#payment-modal').modal('show');
            },
            error: function () {

            }
        })
    }
</script>
<div class="modal fade" id="payment-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" id="Payment-Modal-Body">
        </div>
    </div>
</div>
