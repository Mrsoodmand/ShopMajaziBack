<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendSmsCampaign;
use App\Models\SmsCampaign;
use App\Services\Sms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/** admin › پیامک (super-admin): SMS.ir credit, automatic patterns, bulk sends. See App\Services\Sms. */
class SmsApiController extends Controller
{
    public function index(Sms $sms)
    {
        $Settings = $sms->settings();
        $Patterns = [];
        foreach (Sms::PATTERNS as $Key => $P) {
            $Patterns[] = [
                'key' => $Key,
                'label' => $P['label'],
                'where' => $P['where'],
                'vars' => $P['vars'],
                'required' => $P['required'],
                'new' => $P['new'] ?? false,
                'legacy' => $P['legacy'],
            ] + $Settings['patterns'][$Key];
        }
        $Admin = Auth::guard('admin')->user();
        return response()->json([
            'account' => $sms->account(),
            'patterns' => $Patterns,
            'line' => $Settings['line'],
            'lowCredit' => $Settings['low_credit'],
            'adminMobile' => Sms::normalizeMobile($Admin->mobile ?? null),
            'audience' => $sms->audienceCounts(),
        ]);
    }

    public function save(Request $request, Sms $sms)
    {
        $data = $request->validate([
            'patterns' => 'required|array',
            'patterns.*.template' => 'nullable|integer|min:1|max:99999999',
            'patterns.*.enabled' => 'required|boolean',
            'patterns.*.note' => 'nullable|string|max:300',
            'line' => 'nullable|string|regex:/^\d{4,20}$/',
            'low_credit' => 'required|integer|min:0|max:100000000',
        ], [
            'patterns.*.template.*' => 'شناسه الگو باید یک عدد باشد.',
            'patterns.*.note.*' => 'یادداشت حداکثر ۳۰۰ نویسه باشد.',
            'line.*' => 'شماره خط فقط رقم باشد.',
            'low_credit.*' => 'حد هشدار اعتبار باید یک عدد باشد.',
        ]);
        $Patterns = [];
        foreach (Sms::PATTERNS as $Key => $P) {
            $In = $data['patterns'][$Key] ?? null;
            if (!$In) continue;
            $Patterns[$Key] = [
                'template' => $In['template'] ?? null,
                'enabled' => $P['required'] ? true : (bool)$In['enabled'],
                'note' => trim((string)($In['note'] ?? '')),
            ];
            if ($P['required'] && empty($In['template'])) {
                return response()->json(['errors' => ["patterns.$Key.template" => ['«' . $P['label'] . '» بدون شناسه نمی‌شود؛ بدون آن کسی نمی‌تواند وارد شود.']]], 422);
            }
        }
        $sms->save(['patterns' => $Patterns, 'line' => $data['line'] ?? null, 'low_credit' => (int)$data['low_credit']]);
        return response()->json(['success' => 'تنظیمات پیامک ذخیره شد و از همین حالا استفاده می‌شود.']);
    }

    /* Sends one pattern with sample values to a number (the admin's own by default). */
    public function test(Request $request, Sms $sms, string $key)
    {
        abort_unless(isset(Sms::PATTERNS[$key]), 404);
        $data = $request->validate([
            'template' => 'required|integer|min:1',
            'mobile' => 'required|string',
        ], ['template.*' => 'اول شناسه الگو را وارد کنید.', 'mobile.*' => 'شماره موبایل را وارد کنید.']);
        $Mobile = Sms::normalizeMobile($data['mobile']);
        if (!$Mobile) return response()->json(['errors' => ['mobile' => ['شماره موبایل درست نیست.']]], 422);
        return response()->json($sms->test($key, (int)$data['template'], $Mobile));
    }

    /* Recipient count, parts and cost before sending; also returns the numbers' count for a pasted list. */
    public function preview(Request $request, Sms $sms)
    {
        $data = $this->validateBulk($request, false);
        $Mobiles = $sms->audience($data['audience'], (int)($data['days'] ?? 30), (string)($data['custom'] ?? ''));
        $Parts = Sms::parts((string)($data['message'] ?? ''));
        return response()->json(['recipients' => count($Mobiles), 'parts' => $Parts, 'cost' => count($Mobiles) * $Parts]);
    }

    public function send(Request $request, Sms $sms)
    {
        $data = $this->validateBulk($request, true);
        if (SmsCampaign::where('status', 'sending')->exists()) {
            return response()->json(['error' => 'یک ارسال گروهی هنوز در حال انجام است. صبر کنید تمام شود یا آن را متوقف کنید.'], 409);
        }
        $Line = $data['line'] ?: $sms->settings()['line'];
        if (!$Line) {
            return response()->json(['errors' => ['line' => ['خط ارسال را انتخاب کنید.']]], 422);
        }
        $Mobiles = $sms->audience($data['audience'], (int)($data['days'] ?? 30), (string)($data['custom'] ?? ''));
        if (!$Mobiles) {
            return response()->json(['errors' => ['audience' => ['هیچ شماره معتبری برای ارسال نیست.']]], 422);
        }
        // The count the admin confirmed must still be right (new sign-ups in between are fine within a few).
        if (abs(count($Mobiles) - (int)$data['confirm_count']) > 20) {
            return response()->json(['error' => 'تعداد گیرنده‌ها عوض شده است. دوباره «بررسی و ارسال» را بزنید.'], 409);
        }
        $C = SmsCampaign::create([
            'admin_id' => Auth::guard('admin')->id(),
            'message' => $data['message'],
            'line' => $Line,
            'audience' => $data['audience'],
            'audience_days' => $data['audience'] === 'recent' ? (int)$data['days'] : null,
            'mobiles' => $Mobiles,
            'total' => count($Mobiles),
            'status' => 'sending',
        ]);
        SendSmsCampaign::dispatchAfterResponse($C->id);
        return response()->json(['success' => 'ارسال شروع شد. پیشرفت آن را در «تاریخچه ارسال» می‌بینید.', 'id' => $C->id]);
    }

    public function campaigns()
    {
        $Rows = SmsCampaign::orderByDesc('id')->limit(50)->get();
        $Names = \App\Admin::whereIn('id', $Rows->pluck('admin_id')->filter())->get()->mapWithKeys(fn ($a) => [$a->id => $a->full_name ?: $a->name ?: $a->mobile]);
        return response()->json(['campaigns' => $Rows->map(fn (SmsCampaign $c) => [
            'id' => $c->id,
            'message' => $c->message,
            'line' => $c->line,
            'audience' => $c->audience,
            'days' => $c->audience_days,
            'total' => $c->total,
            'done' => $c->next_offset,
            'accepted' => $c->accepted,
            'rejected' => $c->rejected,
            'cost' => $c->cost,
            'status' => $c->status,
            'error' => $c->error,
            'admin' => $Names[$c->admin_id] ?? null,
            'createdAt' => optional($c->created_at)->toIso8601String(),
            'finishedAt' => optional($c->finished_at)->toIso8601String(),
        ])]);
    }

    public function cancel(int $id)
    {
        $C = SmsCampaign::findOrFail($id);
        if ($C->status !== 'sending') {
            return response()->json(['error' => 'این ارسال دیگر در حال انجام نیست.'], 409);
        }
        $C->forceFill(['status' => 'cancelled', 'finished_at' => now()])->save();
        return response()->json(['success' => 'ارسال متوقف شد. پیامک‌هایی که فرستاده شده بودند برنمی‌گردند.']);
    }

    private function validateBulk(Request $request, bool $Sending): array
    {
        return $request->validate([
            'audience' => ['required', Rule::in(Sms::AUDIENCES)],
            'days' => 'nullable|integer|min:1|max:3650',
            'custom' => 'nullable|string|max:200000',
            'message' => ($Sending ? 'required' : 'nullable') . '|string|max:1000',
            'line' => 'nullable|string|regex:/^\d{4,20}$/',
            'confirm_count' => ($Sending ? 'required' : 'nullable') . '|integer|min:1',
        ], [
            'audience.*' => 'گیرنده‌ها را انتخاب کنید.',
            'days.*' => 'تعداد روز باید عددی بین ۱ و ۳۶۵۰ باشد.',
            'message.required' => 'متن پیامک را بنویسید.',
            'message.max' => 'متن پیامک حداکثر ۱۰۰۰ نویسه باشد.',
            'line.*' => 'شماره خط فقط رقم باشد.',
            'confirm_count.*' => 'اول «بررسی و ارسال» را بزنید.',
        ]);
    }
}
