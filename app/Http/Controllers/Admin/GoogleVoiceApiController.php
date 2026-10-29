<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\lib\PhpEncrypt;
use App\Models\GoogleVoice;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Google Voice numbers for the React admin (`google_voices`). status 0 = in stock (added by hand), 1 = delivered
 * (by hand or bought from Numberland by App\Services\GoogleVoiceNumbers). The login is stored encrypted as the old
 * admin did: {number, email, password, backup-email, 2fa}. Price and Numberland settings are on the page editor (Pages #4).
 */
class GoogleVoiceApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 40;

    public function index(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $search = trim((string) $request->input('q', ''));

        $base = GoogleVoice::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('number', 'LIKE', "%$search%");
                if (ctype_digit($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }
        $counts = [
            'all' => (clone $base)->count(),
            'stock' => (clone $base)->where('status', 0)->count(),
            'sold' => (clone $base)->where('status', 1)->count(),
        ];
        $list = clone $base;
        if ($filter === 'stock') {
            $list->where('status', 0);
        } elseif ($filter === 'sold') {
            $list->where('status', 1);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE, ['id', 'number', 'order_id', 'user_id', 'status', 'created_at', 'updated_at']);
        $users = User::whereIn('id', $page->getCollection()->pluck('user_id')->filter())->get(['id', 'name', 'mobile'])->keyBy('id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'items' => $page->getCollection()->map(fn (GoogleVoice $g) => [
                'id' => $g->id,
                'number' => (string) $g->number,
                'delivered' => (int) $g->status === 1,
                'orderId' => $g->order_id ? (int) $g->order_id : null,
                'user' => ($u = $users[$g->user_id] ?? null) ? ['id' => $u->id, 'name' => $u->name, 'mobile' => $u->mobile] : null,
                'createdAt' => $this->jdate($g->created_at),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $g = GoogleVoice::find($id);
        if (!$g) {
            return $this->missing('این شماره پیدا نشد.');
        }
        $login = $g->code && $g->serial ? (json_decode((string) PhpEncrypt::decrypt($g->serial, $g->code), true) ?: []) : [];

        return response()->json(['item' => [
            'id' => $g->id,
            'delivered' => (int) $g->status === 1,
            'orderId' => $g->order_id ? (int) $g->order_id : null,
            'number' => (string) ($login['number'] ?? $g->number),
            'email' => (string) ($login['email'] ?? ''),
            'password' => (string) ($login['password'] ?? ''),
            'backupEmail' => (string) ($login['backup-email'] ?? ''),
            'twoFactor' => (string) ($login['2fa'] ?? ''),
            'createdAt' => $this->jdate($g->created_at),
        ]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules('items.*.') + ['items' => 'required|array|min:1|max:100'], $this->messages() + [
            'items.required' => 'دست‌کم یک شماره اضافه کنید.',
        ]);
        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $item) {
                $row = new GoogleVoice();
                $this->fill($row, $item);
                $row->status = 0;
                $row->save();
            }
        });
        return response()->json(['success' => count($data['items']) . ' شماره به موجودی اضافه شد.']);
    }

    public function update(Request $request, $id)
    {
        $row = GoogleVoice::find($id);
        if (!$row) {
            return $this->missing('این شماره پیدا نشد.');
        }
        if ((int) $row->status !== 0) {
            return $this->refuse('این شماره تحویل شده و دیگر ویرایش نمی‌شود.');
        }
        $data = $request->validate($this->rules(''), $this->messages());
        $this->fill($row, $data);
        $row->save();

        return response()->json(['success' => 'شماره ذخیره شد.']);
    }

    public function delete($id)
    {
        $row = GoogleVoice::find($id);
        if (!$row) {
            return $this->missing('این شماره پیدا نشد.');
        }
        if ((int) $row->status !== 0) {
            return $this->refuse('شماره تحویل‌شده حذف نمی‌شود.');
        }
        $row->delete();
        return response()->json(['success' => 'شماره از موجودی حذف شد.']);
    }

    private function rules(string $prefix): array
    {
        return [
            $prefix . 'number' => 'required|string|max:50',
            $prefix . 'email' => 'required|string|max:190',
            $prefix . 'password' => 'required|string|max:190',
            $prefix . 'backupEmail' => 'nullable|string|max:190',
            $prefix . 'twoFactor' => 'nullable|string|max:500',
        ];
    }

    private function fill(GoogleVoice $row, array $item): void
    {
        $login = [
            'number' => trim($item['number']),
            'email' => trim($item['email']),
            'password' => $item['password'],
            'backup-email' => (string) ($item['backupEmail'] ?? ''),
            '2fa' => (string) ($item['twoFactor'] ?? ''),
        ];
        $serial = (string) random_int(1111111111111111, 9999999999999999);
        $row->number = $login['number'];
        $row->serial = $serial;
        $row->code = PhpEncrypt::encrypt($serial, $serial, json_encode($login));
        $row->operator_id = Auth::guard('admin')->id();
    }
}
