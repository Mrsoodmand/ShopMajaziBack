<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Ticket;
use App\Models\Ticket_Detail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /* لیست تیکت ها */
    public function index(Request $request)
    {
        $list = Ticket::orderby('status', 'desc');
        if ($request->has('search') && $request->input('search') != ' ') {
            $User = User::where('name', 'Like', '%' . $request->input('search') . '%')
                ->orwhere('mobile', 'LIKE', '%' . $request->input('search') . '%')->get()->pluck('id')->toArray();
            $list->where('subject', 'LIKE', '%' . $request->input('search') . '%');
            $list->orwherein('user_id', [$User]);
        }
        $list = $list->paginate(40);
        $jdf = new Jdf();
        return view('admin-v2.tickets.index',
            compact('list',
                'jdf'));

    }

    /* جزئیات تیکت */
    public function single($id, Request $request)
    {
        $Ticket = Ticket::find($id);
        if (!is_null($Ticket)) {
            $TicketDetails = Ticket_Detail::where('ticket_id', $id)->orderby('id','DESC')->get();
            $Ticket->new_for_admin = false;
            $Ticket->save();
            $jdf = new Jdf();
            return view('admin-v2.tickets.single',
                compact('Ticket',
                    'TicketDetails',
                    'jdf'));
        } else {
            return exception_error($request, 'تیکت مورد نظر یافت نشد');
        }
    }

    /* ارسال پاسخ تیکت */
    public function SendTicket(Request $request)
    {
        $validateitems = [
            'message' => 'required'
        ];
        if ($request->hasFile('file')) {
            $validateitems['file'] = 'max:2048|mimes:png,jpeg,jpg,bmp';
        }

        $validate = Validator::make($request->all(),
            $validateitems,
            [
                'message.required' => 'لطفا متن خود را وارد نمایید',
                'file.max' => 'حجم مجاز اپلود عکس 2 مگابایت میباشد',
                'file.mime' => 'لطفا از فرمت های png,jpeg,jpg,bmp برای ارسال عکس استفاده کنید',
            ]
        );

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }

        try {
            $ticket_id = Decode($request->input('ticket_id'));
            $ticket = Ticket::find($ticket_id);
            $ticket->status = 'Admin-answer';
            $ticket->new_for_user = true;
            $ticket->save();

            $detail = new Ticket_Detail();
            $detail->operator_id = Auth::guard('admin')->user()->id;
            $detail->ticket_id = $ticket->id;
            $detail->detail = $request->input('message');
            $detail->type = 'admin';
            if ($request->hasFile('file')) {
                $detail->file = $this->UploadUserTicketImage($request->input('file'));
            }
            $detail->save();

            return success($request, 'تیکت با موفقیت ارسال شد');

        } catch (\Exception $exception) {
            exception_error($request, $exception->getMessage());
        }
    }
}
