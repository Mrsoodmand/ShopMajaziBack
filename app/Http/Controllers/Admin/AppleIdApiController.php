<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Order;
use App\Models\OrderItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Apple ID stock for the React admin (`apple_id`). type 1 = ready (sold from stock, order type 3),
 * type 2 = custom (made by support for one order type 4). status 0 = in stock, 1 = delivered.
 * Logins are stored encrypted the way the old admin did (PhpEncrypt with the row's serial), so App\Services\AppleIds
 * and the old user panel read them unchanged. Prices and page texts are on the page editor (Pages #3).
 */
class AppleIdApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 40;
    const FIELDS = ['appleid', 'password', 'brithday', 'q1', 'q2', 'q3'];

    public function index(Request $request)
    {
        $filter = $request->input('filter', 'stock');
        $search = trim((string) $request->input('q', ''));

        $base = AppleId::query();
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('apple_id', 'LIKE', "%$search%");
                if (ctype_digit($search)) {
                    $q->orWhere('order_id', (int) $search);
                }
            });
        }
        $scopes = [
            'stock' => fn ($q) => $q->where('type', 1)->where('status', 0),
            'sold' => fn ($q) => $q->where('type', 1)->where('status', 1),
            'custom' => fn ($q) => $q->where('type', 2),
        ];
        $counts = ['all' => (clone $base)->count()];
        foreach ($scopes as $key => $scope) {
            $counts[$key] = $scope(clone $base)->count();
        }
        $list = clone $base;
        if (isset($scopes[$filter])) {
            $scopes[$filter]($list);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE, ['id', 'apple_id', 'order_id', 'user_id', 'status', 'type', 'created_at', 'updated_at']);
        $users = User::whereIn('id', $page->getCollection()->pluck('user_id')->filter())->get(['id', 'name', 'mobile'])->keyBy('id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'waitingOrders' => $this->waitingOrders(),
            'items' => $page->getCollection()->map(fn (AppleId $a) => [
                'id' => $a->id,
                'appleId' => (string) $a->apple_id,
                'kind' => (int) $a->type === 2 ? 'custom' : 'ready',
                'delivered' => (int) $a->status === 1,
                'orderId' => $a->order_id ? (int) $a->order_id : null,
                'user' => ($u = $users[$a->user_id] ?? null) ? ['id' => $u->id, 'name' => $u->name, 'mobile' => $u->mobile] : null,
                'createdAt' => $this->jdate($a->created_at),
                'updatedAt' => $this->jdate($a->updated_at),
            ])->values(),
        ]);
    }

    /** One Apple ID with its login (decrypted for support). */
    public function show($id)
    {
        $a = AppleId::find($id);
        if (!$a) {
            return $this->missing('این اپل آیدی پیدا نشد.');
        }
        $login = $this->login($a);

        return response()->json(['item' => [
            'id' => $a->id,
            'kind' => (int) $a->type === 2 ? 'custom' : 'ready',
            'delivered' => (int) $a->status === 1,
            'orderId' => $a->order_id ? (int) $a->order_id : null,
            'appleId' => (string) ($login['appleid'] ?? $a->apple_id),
            'password' => (string) ($login['password'] ?? ''),
            'birthday' => (string) ($login['brithday'] ?? ''),
            'q1' => (string) ($login['q1'] ?? ''),
            'q2' => (string) ($login['q2'] ?? ''),
            'q3' => (string) ($login['q3'] ?? ''),
            'createdAt' => $this->jdate($a->created_at),
        ]]);
    }

    /** Adds ready Apple IDs to the stock, then hands them to paid orders that were waiting for stock. */
    public function store(Request $request)
    {
        $data = $request->validate($this->rules('items.*.') + ['items' => 'required|array|min:1|max:100'], $this->messages() + [
            'items.required' => 'دست‌کم یک اپل آیدی اضافه کنید.',
        ]);
        $emails = collect($data['items'])->pluck('appleId')->map(fn ($e) => mb_strtolower(trim($e)));
        if ($emails->duplicates()->isNotEmpty()) {
            return response()->json(['message' => 'تکراری', 'errors' => ['items' => ['یک اپل آیدی دو بار آمده است.']]], 422);
        }
        $existing = AppleId::where('type', 1)->whereIn('apple_id', $emails)->pluck('apple_id')->map(fn ($e) => mb_strtolower($e));
        if ($existing->isNotEmpty()) {
            $errors = [];
            foreach ($emails as $i => $email) {
                if ($existing->contains($email)) {
                    $errors["items.$i.appleId"] = ['این اپل آیدی قبلاً ثبت شده است.'];
                }
            }
            return response()->json(['message' => 'تکراری', 'errors' => $errors], 422);
        }

        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $item) {
                $row = new AppleId();
                $this->fill($row, $item);
                $row->status = 0;
                $row->type = 1;
                $row->save();
            }
        });
        $delivered = $this->fulfillWaitingOrders();
        $n = count($data['items']);

        return response()->json([
            'success' => $delivered ? "$n اپل آیدی اضافه شد و $delivered سفارش منتظر تحویل شد." : "$n اپل آیدی به موجودی اضافه شد.",
            'delivered' => $delivered,
        ]);
    }

    /** Edits one Apple ID still in stock (delivered ones belong to a customer). */
    public function update(Request $request, $id)
    {
        $row = AppleId::find($id);
        if (!$row) {
            return $this->missing('این اپل آیدی پیدا نشد.');
        }
        if ((int) $row->status !== 0) {
            return $this->refuse('این اپل آیدی تحویل شده و دیگر ویرایش نمی‌شود.');
        }
        $data = $request->validate($this->rules(''), $this->messages());
        $taken = AppleId::where('type', 1)->where('id', '!=', $row->id)->where('apple_id', trim($data['appleId']))->exists();
        if ($taken) {
            return response()->json(['message' => 'تکراری', 'errors' => ['appleId' => ['این اپل آیدی قبلاً ثبت شده است.']]], 422);
        }
        $this->fill($row, $data);
        $row->save();

        return response()->json(['success' => 'اپل آیدی ذخیره شد.']);
    }

    public function delete($id)
    {
        $row = AppleId::find($id);
        if (!$row) {
            return $this->missing('این اپل آیدی پیدا نشد.');
        }
        if ((int) $row->status !== 0) {
            return $this->refuse('اپل آیدی تحویل‌شده حذف نمی‌شود.');
        }
        $row->delete();
        return response()->json(['success' => 'اپل آیدی از موجودی حذف شد.']);
    }

    /* ---------- Helpers ---------- */

    private function rules(string $prefix): array
    {
        return [
            $prefix . 'appleId' => 'required|string|max:190',
            $prefix . 'password' => 'required|string|max:190',
            $prefix . 'birthday' => 'required|string|max:50',
            $prefix . 'q1' => 'required|string|max:190',
            $prefix . 'q2' => 'required|string|max:190',
            $prefix . 'q3' => 'required|string|max:190',
        ];
    }

    private function fill(AppleId $row, array $item): void
    {
        $login = [
            'appleid' => trim($item['appleId']),
            'password' => $item['password'],
            'brithday' => $item['birthday'],
            'q1' => $item['q1'],
            'q2' => $item['q2'],
            'q3' => $item['q3'],
        ];
        $serial = (string) random_int(1111111111111111, 9999999999999999);
        $row->apple_id = $login['appleid'];
        $row->serial = $serial;
        $row->code = PhpEncrypt::encrypt($serial, $serial, json_encode($login));
        $row->operator_id = Auth::guard('admin')->id();
    }

    private function login(AppleId $a): array
    {
        if (!$a->code || !$a->serial) {
            return [];
        }
        return json_decode((string) PhpEncrypt::decrypt($a->serial, $a->code), true) ?: [];
    }

    /** Paid ready-Apple-ID orders still short of their quantity. */
    private function waitingOrders(): int
    {
        return Order::where('type', 3)->where('status', 'pending')->count();
    }

    /**
     * Same rule as the old admin: each pending order type 3 gets stock up to its quantity (OrderItem detail);
     * a full order becomes complete and the buyer gets the "complete" SMS (template 138554).
     */
    private function fulfillWaitingOrders(): int
    {
        $completed = 0;
        $orders = Order::where('type', 3)->where('status', 'pending')->orderBy('id')->get();
        foreach ($orders as $order) {
            $done = DB::transaction(function () use ($order) {
                $item = OrderItem::where('order_id', $order->id)->first();
                if (!$item) {
                    return false;
                }
                $want = max(1, intval($item->detail));
                $given = AppleId::where('order_id', $order->id)->where('status', 1)->count();
                $stock = AppleId::where('type', 1)->where('status', 0)->orderBy('id')->lockForUpdate()->take(max(0, $want - $given))->get();
                foreach ($stock as $row) {
                    $row->forceFill(['status' => 1, 'order_id' => $order->id, 'user_id' => $order->user_id])->save();
                }
                if ($given + $stock->count() < $want) {
                    return false;
                }
                $order->status = 'complete';
                $order->save();
                return true;
            });
            if ($done) {
                $completed++;
                $mobile = $order->user_id ? User::find($order->user_id)?->mobile : $order->user_number;
                if ($mobile) {
                    sendSmsWithSmsir($mobile, ['NUM' => $order->id, 'STATUS' => 'تکمیل شده'], 138554);
                }
            }
        }
        return $completed;
    }
}
