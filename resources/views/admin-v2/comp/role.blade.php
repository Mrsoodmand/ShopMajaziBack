@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4 text-center">#</th>
            <th class="min-w-125px">نام فارسی</th>
            <th class="min-w-125px">نام انگلیسی</th>
            <th class="min-w-125px">تعداد ادمین</th>
            <th class="min-w-200px">تعداد دسترسی</th>
            <th class="min-w-150px">تاریخ ایجاد</th>
            <th class="min-w-150px">ایحاد شده توسط</th>
            <th class="ps-4 min-w-200px text-end rounded-end" style="padding-left: 20px!important;">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($list) != 0)
            @foreach($list as $item)
                <tr>
                    <td class="ps-4">
                        {{$item->id}}
                    </td>
                    <td>
                        {{$item->faName}}
                    </td>
                    <td>
                        {{$item->name}}
                    </td>
                    <td>
                        0
                    </td>
                    <td>
                        0
                    </td>
                    <td>
                        <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($item->created_at))}}</label>
                        <span class="text-muted fw-bold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($item->created_at))}}</span>
                    </td>
                    <td>
                        {{$item->admin_id}}
                    </td>
                    <td class="text-end" style="padding-left: 20px!important;">
                        <a href="{{route('admin.edit role',$item->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                            <i class="fa-regular fa-circle-info"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" class="ps-4 text-center">
                    آیتمی یافت نشد.
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
