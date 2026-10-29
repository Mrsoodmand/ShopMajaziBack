<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Ticket_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /* لیست تیکت ها */
    public function index(Request $request)
    {
        $id = UserID();
        $list = Ticket::where('user_id', $id->id)->orderby('id', 'DESC');
        if ($request->has('search') && $request->input('search') != ' ') {
            $list->where('subject', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(20);
        $Jdf = new Jdf();
        $Orders = Order::where('user_id', UserID()->id)->select('type', 'id', 'user_id')->get();
        return view('user-v2.tickets.index', compact('list', 'Jdf', 'Orders'));
    }

    /* صفحه ایجاد تیکت */
    public function create()
    {
        $Title = 'ایجاد تیکت پشتیبانی';
        return view('user.tickets.single', compact('Title'));
    }

    /* ثبت تیکت */
    public function submit(Request $request)
    {
        $validateitems = [
            'subject' => 'required|max:150',
            'service' => 'required|numeric|in:1,2,3,4,5',
            'detail' => 'required'
        ];
        if ($request->hasFile('file')) {
            $validateitems['file'] = 'max:2048|mimes:png,jpeg,jpg,bmp';
        }
        if ($request->input('service') == 1) {
            $validateitems['order'] = 'required|numeric';
        }
        $validate = Validator::make($request->all(),
            $validateitems,
            [
                'subject.required' => 'لطفا عنوان را وارد نمایید',
                'subject.max' => 'کاراکتر بیش از حد مجازی برای عنوان',
                'detail.required' => 'لطفا متن خود را وارد نمایید',
                'file.max' => 'حجم مجاز اپلود عکس 2 مگابایت میباشد',
                'file.mimes' => 'لطفا از فرمت های png,jpeg,jpg,bmp برای ارسال عکس استفاده کنید',

                'service.required' => 'لطفا دپارتمان مرتبط را انتخاب نمایید.',
                'service.numeric' => 'فرمت دپارتمان نامعتبر میباشد.',
                'service.in' => 'فرمت دپارتمان نامعتبر میباشد. کد 2',

                'order.required' => 'لطفا سفارش را انتخاب نمایید.',
                'order.numeric' => 'فرمت سفارش نامعتبر میباشد.',
            ]
        );
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            if ($request->input('service') == 1) {
                $Order = Order::where('id', $request->input('order'))->where('user_id', UserID()->id)->first();
                if (is_null($Order)) {
                    return exception_error($request, ['شماره سفارش نامعتبر میباشد.']);
                }
            }
            $ticket = new Ticket();
            $ticket->user_id = Auth::guard('user')->user()->id;
            $ticket->subject = strip_tags($request->input('subject'));
            $ticket->service = $request->input('service');
            if ($request->input('service') == 1) {
                $ticket->order_id = $request->input('order');
            }
            $ticket->status = 'User-Ticket';
            $ticket->new_for_admin = true;
            $ticket->save();

            $detail = new Ticket_Detail();
            $detail->user_id = Auth::guard('user')->user()->id;
            $detail->ticket_id = $ticket->id;
            $detail->type = 'user';
            $detail->detail = strip_tags($request->input('detail'));
            if ($request->hasFile('file')) {
                $detail->file = $this->UploadUserTicketImage($request->file('file'));
            }
            $detail->save();

            $URL = route('user.ticket-detail', [$ticket->id]);

            $Subject = $ticket->subject;
            $TicketUrl = route('admin.ticket single', $ticket->id);
            $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
            $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
            $TicketUrl = "<a href='$TicketUrl'>مشاهده</a>";
            $Service = __('all.Ticket-Type.'.$ticket->service);
            $Message = "
💬 تیکت جدیدی ثبت شد!
🔴 عنوان تیکت: $Subject
⚙ دپارتمان: $Service
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده تیکت: $TicketUrl
";
            SendTelegramMessage($Message, -1001509696194);
            return response()->json(['success' => 'تیکت شما با موفقیت ثبت شد . همکارانمان در اولین فرصت پاسخ تیکت شمارا خواهند داد.', 'url' => $URL], 200);
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* جزئیات تیکت */
    public function detail($id)
    {
        $Ticket = Ticket::find($id);
        if ($Ticket->user_id == UserID()->id) {
            $Jdf = new Jdf();
            $Ticket->new_for_user = false;
            $Ticket->save();
            $TicketDetail = Ticket_Detail::where('ticket_id', $id)->orderby('id', 'DESC')->get();
            $Title = " جزئیات تیکت$id";
            return view('user-v2.tickets.single', compact('Title', 'Ticket', 'TicketDetail', 'Jdf'));
        } else {
            return redirect('user/tickets');
        }
    }

    /* پاسخ به تیکت */
    public function update(Request $request)
    {
        $validateitems = [
            'detail' => 'required',
            'ticket_id' => 'required',
        ];
        if ($request->hasFile('file')) {
            $validateitems['file'] = 'max:2048|mimes:png,jpeg,jpg,bmp';
        }

        $validate = Validator::make($request->all(),
            $validateitems,
            [
                'detail.required' => 'لطفا متن خود را وارد نمایید',
                'file.max' => 'حجم مجاز اپلود عکس 2 مگابایت میباشد',
                'file.mimes' => 'لطفا از فرمت های png,jpeg,jpg,bmp برای ارسال عکس استفاده کنید',
            ]
        );
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            $ticket_id = $request->input('ticket_id');
            $ticket = Ticket::find($ticket_id);
            if (!is_null($ticket)) {
                if ($ticket->user_id == UserID()->id) {
                    $ticket->status = 'User-answer';
                    $ticket->new_for_admin = true;
                    $ticket->save();

                    $detail = new Ticket_Detail();
                    $detail->user_id = Auth::guard('user')->user()->id;
                    $detail->ticket_id = $ticket->id;
                    $detail->detail = $request->input('detail');
                    $detail->type = 'user';
                    if ($request->hasFile('file')) {
                        $detail->file = $this->UploadUserTicketImage($request->file('file'));
                    }
                    $detail->save();

                    $URL = route('user.ticket-detail', [$ticket_id]);
                    $Subject = $ticket->subject;
                    $TicketUrl = route('admin.ticket single', $ticket->id);
                    $AdminUrl = route('admin.user single', Auth::guard('user')->user()->id);
                    $AdminUrl = "<a href='$AdminUrl'>مشاهده</a>";
                    $TicketUrl = "<a href='$TicketUrl'>مشاهده</a>";
                    $Service = __('all.Ticket-Type.'.$ticket->service);
                    $Message = "
💬 پاسخ جدیدی ثبت شد!
🔴 عنوان تیکت: $Subject
⚙ دپارتمان: $Service
👤 مشاهده کاربر: $AdminUrl
👀 مشاهده تیکت: $TicketUrl
";
                    SendTelegramMessage($Message, -1001509696194);

                    return response()->json(['success' => 'پاسخ شما با موفقیت ثبت شد . همکارانمان در اولین فرصت پاسخ تیکت شمارا خواهند داد.', 'url' => $URL], 200);
                } else {
                    exception_error($request, 'تیکت مورد نظر یافت نشد.');
                }
            } else {
                exception_error($request, 'تیکت مورد نظر یافت نشد.');
            }
        } catch (\Exception $exception) {
            exception_error($request, $exception->getMessage());
        }
    }
}
