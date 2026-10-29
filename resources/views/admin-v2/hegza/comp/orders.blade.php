@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4 text-center">#</th>
            <th class="min-w-125px">کاربر</th>
            <th class="min-w-125px">عنوان سفارش</th>
            <th class="min-w-150px">وضعیت سفارش</th>
            <th class="min-w-150px">تاریخ ثبت</th>
            <th class="ps-4 min-w-200px text-end rounded-end" style="padding-left: 20px!important;">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Orders) != 0)
            @foreach($Orders as $order)
                <tr>
                    <td class="ps-4">
                        {{$order->id}}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="d-flex justify-content-start flex-column">
                                @if(!is_null($order->User))
                                <a href="#"
                                   class="text-dark fw-bolder text-hover-primary mb-1 fs-6">{{$order->User->username}}</a>
                                <span class="text-muted fw-bold text-muted d-block fs-7">{{$order->User->user_id}}</span>
                                @else
                                    <a href="#"
                                       class="text-dark fw-bolder text-hover-primary mb-1 fs-6">کاربری یافت نشد</a>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="ps-4">
                        {{$order->name}}
                    </td>
                    <td>
                        {{BotOrderStatus($order->status)}}
                    </td>
                    <td>
                        <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($order->created_at))}}</label>
                        <span class="text-muted fw-bold text-muted d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($order->created_at))}}</span>
                    </td>
                    <td class="text-end" style="padding-left: 20px!important;">
                        <a href="{{url('api/hegza/show-detail',Encode($order->id))}}"
                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                            <i class="fa-regular fa-circle-info"></i>
                        </a>

                        <a href="{{route('admin.bot-orders-single',$order->id)}}"
                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                            <i class="fa-regular fa-circle-info"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="ps-4 text-center" colspan="20">
                    <img src="{{url('/admin-v2')}}/assets/media/svg/illustrations/easy/2.svg" alt="خرید">
                    <h1 class="fw-bold text-gray-800 text-center lh-lg">
                        <span class="fw-boldest">سفارشی ثبت نشده است.</span>
                    </h1>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
