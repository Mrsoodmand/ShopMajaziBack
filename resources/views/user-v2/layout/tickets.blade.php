<style>
    .limit{
        display: block;
        width: 11rem;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
</style>
<div class="table-responsive fs-sm mb-4">
    <table class="table table-hover mb-0">
        <thead>
        <tr>
            <th>#</th>
            <th>عنوان</th>
            <th>بخش</th>
            <th>وضعیت</th>
            <th>تاریخ</th>
            <th>جزئیات</th>
        </tr>
        </thead>
        <tbody>
        @if(count($Tickets) != 0)
            @foreach($Tickets as $ticket)
                <tr>
                    <td class="py-3"><a class="nav-link-style fw-medium fs-sm" href="{{secure_url('user/tickets/'.$ticket->id)}}" data-bs-toggle="modal">{{$ticket->id}}</a></td>
                    <td class="py-3">{{$ticket->subject}}</td>
                    <td class="py-3">@lang('all.Ticket-Type.'.$ticket->service)</td>
                    <td class="py-3">{{UserTicketStatus($ticket->status)}}</td>
                    <td class="py-3">{{$Jdf->jdate('d F Y ساعت: H:i',strtotime($ticket->created_at))}}</td>
                    <td class="py-3"><a href="{{secure_url('user/tickets/'.$ticket->id)}}">مشاهده</a></td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" class="py-3 text-center">تیکتی ثبت نشده است.</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
