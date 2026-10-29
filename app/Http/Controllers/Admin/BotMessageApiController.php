<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\HegzaMessage;
use App\Models\HegzaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Announcements the Telegram bot sends to all its users (hegza_messages).
 * A scheduled call (api/hegza/telegramSendMessage) takes the oldest queued message (status 0), sends it to users in
 * batches and marks each user (hegza_user.message = 1); when everyone has it the message turns to 1 (sent) and the marks
 * are cleared. -1 is a draft that is never sent. Queuing a sent message again sends it to everyone again.
 */
class BotMessageApiController extends Controller
{
    use AdminApi;

    const STATUS = ['0' => 'queued', '1' => 'sent', '-1' => 'draft'];

    public function index()
    {
        $messages = HegzaMessage::orderByDesc('id')->paginate(30);
        $sending = HegzaMessage::where('status', 0)->orderBy('id')->value('id');

        return response()->json($this->pageMeta($messages) + [
            'progress' => $sending ? $this->progress() + ['id' => (int) $sending] : null,
            'messages' => $messages->getCollection()->map(fn (HegzaMessage $m) => [
                'id' => $m->id,
                'title' => (string) $m->title,
                'preview' => mb_substr(trim(strip_tags((string) $m->long_detail)), 0, 140),
                'status' => self::STATUS[(string) $m->status] ?? 'draft',
                'createdAt' => $this->jdate($m->created_at),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $m = HegzaMessage::find($id);
        if (!$m) {
            return $this->missing('این اطلاعیه پیدا نشد.');
        }
        $first = HegzaMessage::where('status', 0)->orderBy('id')->value('id');

        return response()->json([
            'message' => [
                'id' => $m->id,
                'title' => (string) $m->title,
                'text' => (string) $m->long_detail,
                'status' => self::STATUS[(string) $m->status] ?? 'draft',
                'createdAt' => $this->jdate($m->created_at),
                'progress' => (int) $m->status === 0 && $first === $m->id ? $this->progress() : null,
                'waitingBehind' => (int) $m->status === 0 && $first !== $m->id ? HegzaMessage::where('status', 0)->where('id', '<', $m->id)->count() : 0,
            ],
            'audience' => HegzaUser::whereNotNull('user_id')->count(),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $m = $id ? HegzaMessage::find($id) : new HegzaMessage();
        if (!$m) {
            return $this->missing('این اطلاعیه پیدا نشد.');
        }
        $data = $request->validate([
            // hegza_messages.title is 3-byte utf8, so emoji can't be stored there (the text column takes them).
            'title' => ['required', 'string', 'max:220', 'not_regex:/[\x{10000}-\x{10FFFF}]/u'],
            'text' => 'required|string|max:4000',
            'status' => ['required', Rule::in(['queued', 'draft'])],
        ], $this->messages() + ['text.max' => 'تلگرام پیام بلندتر از ۴۰۰۰ نویسه را نمی‌فرستد.', 'title.not_regex' => 'ایموجی را در متن پیام بگذارید؛ عنوان آن را نگه نمی‌دارد.']);

        // Stopping the message being sent clears the bot's "already got it" marks, so the next one reaches everyone.
        if ($this->isSending($m) && $data['status'] === 'draft') {
            $this->clearMarks();
        }
        $m->title = $data['title'];
        $m->long_detail = $data['text'];
        $m->short_detail ??= 'all';
        // Not queued: a draft stays a draft and a sent message stays sent.
        $m->status = $data['status'] === 'queued' ? 0 : (($m->exists && (int) $m->status === 1) ? 1 : -1);
        $m->creator = Auth::guard('admin')->id();
        $m->save();

        return response()->json([
            'success' => $data['status'] === 'queued' ? 'اطلاعیه در صف ارسال است.' : 'پیش‌نویس ذخیره شد.',
            'id' => $m->id,
        ]);
    }

    public function delete($id)
    {
        $m = HegzaMessage::find($id);
        if (!$m) {
            return $this->missing('این اطلاعیه پیدا نشد.');
        }
        if ($this->isSending($m)) {
            $this->clearMarks();
        }
        $m->delete();

        return response()->json(['success' => 'اطلاعیه حذف شد.']);
    }

    private function isSending(HegzaMessage $m): bool
    {
        return $m->exists && (int) $m->status === 0 && HegzaMessage::where('status', 0)->orderBy('id')->value('id') === $m->id;
    }

    private function clearMarks(): void
    {
        HegzaUser::where('message', 1)->update(['message' => 0]);
    }

    /** How far the message being sent has got. */
    private function progress(): array
    {
        return [
            'sent' => HegzaUser::whereNotNull('user_id')->where('message', 1)->count(),
            'total' => HegzaUser::whereNotNull('user_id')->count(),
        ];
    }
}
