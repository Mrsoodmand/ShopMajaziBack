@php $jdf = new \App\lib\Jdf(); @endphp

<table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mt-3">
    <thead>
    <tr class="fw-bolder text-muted">
        <th class="ps-4">نوع پست</th>
        <th class="ps-4">نام</th>
        <th class="min-w-150px">موبایل</th>
        <th class="min-w-75px">وضعیت</th>
        <th class="min-w-75px">تاریخ</th>
        <th class="text-center">عملیات</th>
    </tr>
    </thead>
    <tbody>
    @if(count($List) != 0)
        @foreach($List as $comment)
            <tr>
                <td class="ps-4">
                    @if($comment->type == 'page')
                        صفحه  @lang('order.PageTypeFa.'.$comment->post_id)
                    @elseif($comment->type == 'service')
                        صفحه خدمات
                    @endif
                </td>
                <td class="ps-4">
                    {{nl2br($comment->name)}}
                </td>
                <td class="ps-4">
                    {{nl2br($comment->mobile)}}
                </td>
                <td id="Status-{{$comment->id}}">
                    @if($comment->status == 1)
                        <div class="badge badge-light-success">تایید شده.</div>
                    @elseif($comment->status == 0)
                        <div class="badge badge-light-info">درانتظار تایید.</div>
                    @else
                        <div class="badge badge-light-danger">عدم تایید.</div>
                    @endif
                </td>
                <td>
                    <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($comment->created_at))}}</label>
                    <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($comment->created_at))}}</span>
                </td>
                <td>
                    <div class="d-flex justify-content-center flex-shrink-0">
                        <a class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1"
                           onclick="CheckComment({{$comment->id}})" title="اطلاعات"><i class="fa fa-info"></i></a>
                        <a href="javascript:void(0);" onclick="ChangeCommentStatus({{$comment->id}},1)"
                           class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1" title="تایید">
                            <i class="fa-regular fa-check"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="ChangeCommentStatus({{$comment->id}},0)"
                           class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1"
                           title="درحال بررسی">
                            <i class="fa-regular fa-loader"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="ChangeCommentStatus({{$comment->id}},-1)"
                           class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm me-1" title="عدم تایید">
                            <i class="fa-regular fa-xmark"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td class="ps-4 text-center" colspan="20">
                <img src="{{url('/admin-v2')}}/assets/media/svg/illustrations/easy/2.svg" alt="خرید">
                <h1 class="fw-bold text-gray-800 text-center lh-lg">
                    <span class="fw-boldest">کامنتی ثبت نشده است.</span>
                </h1>
            </td>
        </tr>
    @endif
    </tbody>
</table>

