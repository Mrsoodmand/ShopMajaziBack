<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Ticket_Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Support tickets for the React admin. Stored status: User-Ticket (new), User-answer (the user wrote again),
 * Admin-answer (answered), close. Messages are `ticket_details` rows (type user|admin); files live in public/tickets.
 */
class TicketApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 30;
    const WAITING = ['User-Ticket', 'User-answer'];

    public function index(Request $request)
    {
        $filter = $request->input('filter', 'waiting');
        $search = trim((string) $request->input('q', ''));

        $base = Ticket::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('subject', 'LIKE', "%$search%")
                    ->orWhereHas('User', fn ($u) => $u->where('name', 'LIKE', "%$search%")->orWhere('mobile', 'LIKE', "%$search%"));
                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search)->orWhere('order_id', (int) $search);
                }
            });
        }
        $counts = [
            'waiting' => (clone $base)->whereIn('status', self::WAITING)->count(),
            'answered' => (clone $base)->where('status', 'Admin-answer')->count(),
            'closed' => (clone $base)->where('status', 'close')->count(),
            'all' => (clone $base)->count(),
        ];
        $list = (clone $base)->with('User:id,name,mobile');
        match ($filter) {
            'waiting' => $list->whereIn('status', self::WAITING)->orderBy('updated_at'), // longest wait first
            'answered' => $list->where('status', 'Admin-answer')->orderByDesc('updated_at'),
            'closed' => $list->where('status', 'close')->orderByDesc('updated_at'),
            default => $list->orderByDesc('updated_at'),
        };
        $page = $list->paginate(self::PER_PAGE);
        $last = Ticket_Detail::whereIn('id', Ticket_Detail::whereIn('ticket_id', $page->getCollection()->pluck('id'))
            ->selectRaw('max(id)')->groupBy('ticket_id'))->get()->keyBy('ticket_id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'tickets' => $page->getCollection()->map(fn (Ticket $t) => $this->summary($t) + [
                'preview' => Str::limit(trim(strip_tags((string) ($last[$t->id]->detail ?? ''))), 120),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $ticket = Ticket::with('User:id,name,mobile,email')->find($id);
        if (!$ticket) {
            return $this->missing('این تیکت پیدا نشد.');
        }
        if ($ticket->new_for_admin) {
            $ticket->new_for_admin = false;
            $ticket->timestamps = false; // reading it must not move it in the queue
            $ticket->save();
        }
        $messages = Ticket_Detail::where('ticket_id', $ticket->id)->orderBy('id')->get();
        $operators = Admin::whereIn('id', $messages->pluck('operator_id')->filter()->unique())->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->full_name ?: $a->name ?: 'پشتیبان']);

        return response()->json([
            'ticket' => $this->summary($ticket) + [
                'user' => $ticket->User ? ['id' => $ticket->User->id, 'name' => $ticket->User->name, 'mobile' => $ticket->User->mobile, 'email' => $ticket->User->email] : null,
            ],
            'messages' => $messages->map(fn (Ticket_Detail $m) => [
                'id' => $m->id,
                'from' => $m->type === 'admin' ? 'admin' : 'user',
                'operator' => $m->type === 'admin' ? ($operators[$m->operator_id] ?? 'پشتیبان') : null,
                'text' => (string) $m->detail,
                'file' => $m->file ? '/' . ltrim($m->file, '/') : null,
                'createdAt' => $this->jdate($m->created_at),
            ])->values(),
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:10000',
            'file' => 'nullable|file|mimes:png,jpg,jpeg,webp,pdf|max:2048',
            'close' => 'nullable|boolean',
        ], $this->messages() + [
            'message.required' => 'متن پاسخ را بنویسید.',
            'file.mimes' => 'فقط تصویر (PNG، JPG، WebP) یا PDF پیوست کنید.',
            'file.max' => 'حجم پیوست حداکثر ۲ مگابایت باشد.',
        ]);
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return $this->missing('این تیکت پیدا نشد.');
        }
        $detail = new Ticket_Detail();
        $detail->forceFill([
            'ticket_id' => $ticket->id,
            'operator_id' => Auth::guard('admin')->id(),
            'detail' => $request->input('message'),
            'type' => 'admin',
        ]);
        if ($request->hasFile('file')) {
            $detail->file = $this->UploadUserTicketImage($request->file('file'));
        }
        $detail->save();

        $ticket->status = $request->boolean('close') ? 'close' : 'Admin-answer';
        $ticket->new_for_user = true;
        $ticket->new_for_admin = false;
        $ticket->save();

        return response()->json(['success' => $request->boolean('close') ? 'پاسخ ارسال و تیکت بسته شد.' : 'پاسخ ارسال شد.']);
    }

    public function setStatus(Request $request, $id)
    {
        $request->validate(['open' => 'required|boolean'], $this->messages());
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return $this->missing('این تیکت پیدا نشد.');
        }
        $ticket->status = $request->boolean('open') ? 'User-answer' : 'close';
        $ticket->save();

        return response()->json(['success' => $request->boolean('open') ? 'تیکت دوباره باز شد.' : 'تیکت بسته شد.']);
    }

    private function summary(Ticket $t): array
    {
        return [
            'id' => $t->id,
            'subject' => (string) $t->subject,
            'department' => __('all.Ticket-Type.' . $t->service) !== 'all.Ticket-Type.' . $t->service ? __('all.Ticket-Type.' . $t->service) : null,
            'status' => match ($t->status) {
                'Admin-answer' => 'answered',
                'close' => 'closed',
                default => 'waiting',
            },
            'statusLabel' => __('all.tickets.' . $t->status) !== 'all.tickets.' . $t->status ? __('all.tickets.' . $t->status) : (string) $t->status,
            'unread' => (bool) $t->new_for_admin,
            'orderId' => $t->order_id ? (int) $t->order_id : null,
            'userName' => $t->User?->name,
            'userMobile' => $t->User?->mobile,
            'createdAt' => $this->jdate($t->created_at),
            'updatedAt' => $this->jdate($t->updated_at),
            'updatedTs' => $t->updated_at ? strtotime((string) $t->updated_at) : null,
        ];
    }
}
