@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4 text-center">#</th>
            <th class="min-w-125px">نام فارسی</th>
            <th class="min-w-125px">نام انگلیسی</th>
            <th class="min-w-125px">دسته بندی</th>
            <th class="min-w-150px">ایجاد شده توسط</th>
            <th class="min-w-150px">تاریخ ایجاد</th>
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
                    <td id="copy-{{$item->id}}" onclick="myFunction('{{$item->name}}',{{$item->id}})">
                        {{$item->name}}
                    </td>
                    <td>
                        {{$Categories[$item->category_id]}}
                    </td>
                    <td>
                        {{$item->admin_id}}
                    </td>
                    <td>
                        <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($item->created_at))}}</label>
                        <span class="text-muted fw-bold text-muted d-block fs-7">{{$jdf->jdate('H:i',strtotime($item->created_at))}}</span>
                    </td>
                    <td class="text-end" style="padding-left: 20px!important;">
                        <button  class="btn btn-bg-light btn-active-color-info btn-sm me-1 open-modal" id="btn-{{$item->id}}" type="button" data-type="edit" data-id="{{$item->id}}">
                            ویرایش
                        </button>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" class="ps-4 text-center">
                    دسترسی ای یافت نشد.
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<script>
    function myFunction(id,id2) {
        /* Copy the text inside the text field */
        navigator.clipboard.writeText(id);
        /* Alert the copied text */
        $('#copy-'+id2).css('color','green')
    }
</script>
