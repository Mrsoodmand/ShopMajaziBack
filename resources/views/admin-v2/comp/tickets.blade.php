@php $jdf = new \App\lib\Jdf(); @endphp
<div class="table-responsive">
    <table class="table align-middle gs-0 gy-4">
        <thead>
        <tr class="fw-bolder text-muted bg-light">
            <th class="ps-4">شماره تیکت</th>
            <th class="ps-4">عنوان</th>
            <th class="min-w-150px">کاربر</th>
            <th class="min-w-150px">بخش</th>
            <th class="min-w-150px">وضعیت</th>
            <th class="min-w-150px">تاریخ ثبت</th>
            <th class="min-w-150px">آخرین بروز رسانی</th>
            <th class="text-end rounded-end" style="padding-left: 20px!important;">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Tickets) != 0)
            @foreach($Tickets as $ticket)
                <tr>
                    <td class="ps-4">
                        {{$ticket->id}}
                    </td>
                    <td class="ps-4">
                        {{$ticket->subject}}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="d-flex justify-content-start flex-column">
                                <a href="#"
                                   class="text-dark fw-bolder text-hover-primary mb-1 fs-6">{{$ticket->User->name}}</a>
                                <span class="text-muted fw-bold text-muted d-block fs-7">{{$ticket->User->mobile}}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="javascript:;" class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">@lang('all.Ticket-Type.'.$ticket->service)</a>
                    </td>
                    <td>
                        {{TicketStatus($ticket->status)}}
                    </td>
                    <td>
                        <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($ticket->created_at))}}</label>
                        <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($ticket->created_at))}}</span>
                    </td>
                    <td>
                        @if(!is_null($ticket->updated_at))
                            <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($ticket->updated_at))}}</label>
                            <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($ticket->updated_at))}}</span>
                        @else
                            ---
                        @endif
                    </td>
                    <td class="text-end" style="padding-left: 20px!important;">
                        <a target="_blank" href="{{route('admin.ticket single',$ticket->id)}}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                            <i class="fa-regular fa-circle-info"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8">
                    <a href="javascript:;"
                       class="text-dark text-center fw-bolder text-hover-primary d-block mb-1 fs-6">در حال حاضر همه چی مرتبه!</a>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
