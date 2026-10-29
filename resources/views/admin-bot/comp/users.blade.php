<div class="table-responsive">
    <table class="table table-row-bordered  table-hover table-row-gray-100 align-middle gs-0 gy-3">
        <thead>
        <tr class="fw-bold text-muted">
            <th class="min-w-150px text-center">نام</th>
            <th class="min-w-140px text-center">کد/آیدی تلگرام</th>
            <th class="min-w-120px text-center">موجودی کیف پول</th>
            <th class="min-w-120px text-center">وضعیت</th>
            <th class="min-w-120px text-center">تاریخ عضویت</th>
            <th class="min-w-100px text-center">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($list) != 0)
            @foreach($list as $user)
                <tr>
                    <td class="text-center">
                        <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$user->name}}</label>
                    </td>
                    <td class="text-center">
                        <label class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$user->user_id}}</label>
                        <span class="text-muted fw-semibold text-muted d-block fs-7">   {{$user->username}}</span>
                    </td>
                    <td class="text-center">
                        {{number_format($user->wallet)}} تومان
                    </td>
                    <td class="text-center">
                        <span class="badge badge-light-success">فعال</span>
                    </td>
                    <td class="text-center">
                        <a href="#"
                           class="text-gray-900 fw-bold text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('Y-m-d',strtotime($user->created_at))}}</a>
                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($user->created_at))}}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{route('admin.bot-user-single',[$user->id])}}"
                           class="btn btn-bg-light btn-active-color-primary btn-sm ">
                            مشاهده جزئیات
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" class="text-center">
                    کاربری یافت نشد
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
