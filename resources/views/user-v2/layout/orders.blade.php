<div class="table-responsive fs-sm mb-4">
    <table class="table table-hover mb-0">
        <thead>
        <tr>
            <th>#</th>
            <th>نوع سفارش</th>
            <th>وضعیت سفارش</th>
            <th>مبلغ</th>
            <th>تاریخ</th>
            <th>جزئیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Orders) != 0)
            @foreach($Orders as $order)
                <tr>
                    <td class="py-3"><a class="nav-link-style fw-medium fs-sm" href="{{secure_url('user/orders/'.$order->id)}}" data-bs-toggle="modal">{{$order->id}}</a></td>
                    <td class="py-3">@lang('order.type.'.$order->type)</td>
                    <td class="py-3">{{UserOrderStatus($order->status)}}</td>
                    <td class="py-3">{{number_format($order->price)}} تومان </td>
                    <td class="py-3">{{$Jdf->jdate('d F Y  H:i',strtotime($order->created_at))}}</td>
                    <td class="py-3"><a href="{{secure_url('user/orders/'.$order->id)}}">مشاهده</a></td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" class="py-3 text-center">سفارشی ثبت نشده است.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
