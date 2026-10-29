@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4 text-center">#</th>
            <th class="min-w-125px">نام صفحه</th>
            <th class="min-w-125px">تگ H1</th>
            <th class="ps-4 text-center min-w-200px rounded-end" style="padding-left: 20px!important;">عملیات</th>
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
                        {{$item->title}}
                    </td>
                    <td>
                        {{$item->h1}}
                    </td>
                    <td>
                        <div class="d-flex justify-content-center flex-shrink-0">
                            <a class="btn btn-bg-light btn-active-color-info btn-sm me-1"
                               href="{{route('admin.edit page',$item->id)}}"
                               title="اطلاعات">جزئیات</a>
                        </div>
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
