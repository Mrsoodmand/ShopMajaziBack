<div class="table-responsive">
    <table class="table table-row-bordered  table-hover table-row-gray-100 align-middle gs-0 gy-3">
        <thead>
        <tr class="fw-bold text-muted">
            <th class="text-center">شناسه</th>
            <th class="text-center">کاربر</th>
            <th class="text-center">نام سفارش</th>
            <th class="text-center">وضعیت</th>
            <th class="text-center">تاریخ ثبت</th>
            <th class="text-center">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($list) != 0)
            @foreach($list as $item)
                <tr>
                    <td class="text-center">
                        {{$item->id}}
                    </td>
                    <td class="text-center">
                        @if(!is_null($item->User))
                            <a href="{{route('admin.bot-user-single',$item->user_id)}}"
                               class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$item->User->username}}</a>
                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{$item->User->user_id}}</span>
                        @else
                            <a href="#" class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">کاربری یافت
                                نشد</a>
                            <span class="text-muted fw-semibold text-muted d-block fs-7">کاربری یافت نشد</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{$item->name}}
                    </td>
                    <td class="text-center">
                        {{BotOrderStatus($item->status)}}
                    </td>
                    <td class="text-center">
                        <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d',strtotime($item->created_at))}}</label>
                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($item->created_at))}}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{route('admin.bot-order-single',$item->id)}}"
                           class="btn btn-bg-light btn-active-color-primary btn-sm ">
                            مشاهده جزئیات
                        </a>
                    </td>
                </tr>
            @endforeach

        @else
            <tr>
                <td colspan="10" class="text-center">
                    سفارشای یافت نشد
                </td>

            </tr>
        @endif
        </tbody>
    </table>
</div>


