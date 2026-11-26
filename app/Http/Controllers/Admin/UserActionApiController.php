<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Ticket_Detail;
use App\Services\OrderDelivery;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Actions on admin › user and admin › order that the React screens post as JSON:
 * disable / enable a customer account, open a ticket to a customer, and finish a custom Apple ID order.
 */
class UserActionApiController extends Controller
{
    use AdminApi;

    /** Departments as the storefront stores them (tickets.service; Api\StorefrontPanelController::DEPTS). */
    const DEPTS = [1 => 'orders', 2 => 'sales', 3 => 'technical', 4 => 'accounting', 5 => 'other'];

    /**
     * Disabled (users.status = 1): no sign-in anywhere, and a session already open is ended on the next request
     * (App\Http\Middleware\SignOutDisabledUser). Written to the user's history with the reason.
     */
    public function status(Request $request, $id)
    {
        $data = $request->validate([
            'active' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
        ], $this->messages());
        $reason = trim((string) ($data['reason'] ?? ''));
        if (!$data['active'] && $reason === '') {
            return response()->json(['error' => ['دلیل غیرفعال کردن را بنویسید؛ در تاریخچه کاربر می‌ماند.'], 'errors' => ['reason' => ['دلیل غیرفعال کردن را بنویسید.']]], 422);
        }
        $user = User::find($id);
        if (!$user) {
            return $this->missing('این کاربر پیدا نشد.');
        }
        $active = (bool) $data['active'];
        if ($active === ((int) $user->status !== 1)) {
            return response()->json(['success' => $active ? 'حساب از قبل فعال است.' : 'حساب از قبل غیرفعال است.']);
        }

        DB::transaction(function () use ($user, $active, $reason) {
            $user->status = $active ? 0 : 1;
            $user->save();
            $history = new History();
            $history->type = 'user';
            $history->post_id = $user->id;
            $history->detail = json_encode(array_filter(['account' => $active ? 'enabled' : 'disabled', 'detail' => $reason]));
            $history->admin_id = Auth::guard('admin')->id();
            $history->save();
        });

        return response()->json(['success' => $active ? 'حساب کاربر فعال شد.' : 'حساب کاربر غیرفعال شد و از سایت خارج می‌شود.']);
    }

    /**
     * A ticket started by support: shows in the customer's /account › tickets as answered by support (unread for
     * them), and texts them with the «پاسخ تیکت» pattern when it has a template id.
     */
    public function ticket(Request $request, $id)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'dept' => 'required|in:' . implode(',', self::DEPTS),
            'order_id' => 'nullable|integer',
            'message' => 'required|string|max:10000',
            'file' => 'nullable|file|mimes:png,jpg,jpeg,webp,pdf|max:2048',
            'close' => 'nullable|boolean',
        ], $this->messages() + [
            'subject.required' => 'عنوان تیکت را بنویسید.',
            'dept.required' => 'دپارتمان را انتخاب کنید.',
            'message.required' => 'متن پیام را بنویسید.',
            'file.mimes' => 'فقط تصویر (PNG، JPG، WebP) یا PDF پیوست کنید.',
            'file.max' => 'حجم پیوست حداکثر ۲ مگابایت باشد.',
        ]);
        $user = User::find($id);
        if (!$user) {
            return $this->missing('این کاربر پیدا نشد.');
        }
        $orderId = null;
        if (!empty($data['order_id'])) {
            if (!Order::where('id', $data['order_id'])->where('user_id', $user->id)->exists()) {
                return $this->refuse('این سفارش مال این کاربر نیست.');
            }
            $orderId = (int) $data['order_id'];
        }

        $file = $request->hasFile('file') ? $this->UploadUserTicketImage($request->file('file')) : null;
        $ticket = DB::transaction(function () use ($user, $data, $orderId, $file, $request) {
            $ticket = new Ticket();
            $ticket->user_id = $user->id;
            $ticket->subject = trim(strip_tags($data['subject']));
            $ticket->service = array_search($data['dept'], self::DEPTS, true);
            $ticket->order_id = $orderId;
            $ticket->status = $request->boolean('close') ? 'close' : 'Admin-answer';
            $ticket->new_for_user = true;
            $ticket->new_for_admin = false;
            $ticket->save();

            $detail = new Ticket_Detail();
            $detail->forceFill([
                'ticket_id' => $ticket->id,
                'operator_id' => Auth::guard('admin')->id(),
                'detail' => $data['message'],
                'type' => 'admin',
            ]);
            if ($file) {
                $detail->file = $file;
            }
            $detail->save();
            return $ticket;
        });
        app(\App\Services\Sms::class)->notifyUser($user->id, 'ticket_answer', ['NUM' => $ticket->id]);

        return response()->json(['success' => 'تیکت برای کاربر فرستاده شد.', 'ticketId' => $ticket->id]);
    }

    /** Custom Apple ID order (type 4): the login support made for the customer; completes the order. */
    public function completeAppleId(Request $request, $id)
    {
        $data = $request->validate([
            'appleid' => 'required|string|max:190',
            'password' => 'required|string|max:190',
            'brithday' => 'required|string|max:50',
            'q1' => 'required|string|max:190',
            'q2' => 'required|string|max:190',
            'q3' => 'required|string|max:190',
        ], $this->messages() + [
            'appleid.required' => 'اپل آیدی را بنویسید.',
            'password.required' => 'کلمه عبور را بنویسید.',
            'brithday.required' => 'تاریخ تولد را بنویسید.',
            'q1.required' => 'جواب سوال امنیتی ۱ را بنویسید.',
            'q2.required' => 'جواب سوال امنیتی ۲ را بنویسید.',
            'q3.required' => 'جواب سوال امنیتی ۳ را بنویسید.',
        ]);
        $order = Order::find($id);
        if (!$order) {
            return $this->missing('سفارش پیدا نشد.');
        }
        $delivery = OrderDelivery::for($order);
        if (!$delivery || !$delivery['canCompleteAppleId']) {
            return $this->refuse('این سفارش اپل آیدی سفارشیِ در انتظار تحویل نیست.');
        }
        OrderDelivery::completeAppleId($order, array_map('trim', $data), (int) Auth::guard('admin')->id());

        return response()->json(['success' => 'اپل آیدی ثبت و سفارش تکمیل شد.']);
    }
}
