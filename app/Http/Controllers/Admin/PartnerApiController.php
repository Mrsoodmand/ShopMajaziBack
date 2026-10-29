<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Partner;
use App\Models\PartnerProfit;
use App\Models\PartnerServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Partners (table `partner`): people who supply a service and take a percent of each paid order of it.
 * When an order of a partner's service is paid without a buy price, FactorProfit() (helper.php) credits
 * percent% of the amount to the active partner's wallet and logs it in partner_profit.
 * Settling pays part of the wallet out: wallet goes down, deposit (total paid out) goes up.
 */
class PartnerApiController extends Controller
{
    use AdminApi;

    const DEPOSIT = ['1' => 'daily', '2' => 'weekly', '3' => 'monthly'];

    public function index()
    {
        $services = PartnerServices::get(['partner_id', 'service_id'])->groupBy('partner_id');
        $profits = PartnerProfit::select('partner_id', DB::raw('count(*) c'), DB::raw('sum(profit) s'))->groupBy('partner_id')->get()->keyBy('partner_id');

        return response()->json([
            'services' => $this->services(),
            'partners' => Partner::orderBy('id')->get()->map(fn (Partner $p) => [
                'id' => $p->id,
                'name' => (string) $p->name,
                'mobile' => (string) $p->mobile,
                'live' => (int) $p->status === 1,
                'percent' => (float) $p->percent,
                'serviceIds' => ($services[$p->id] ?? collect())->pluck('service_id')->map(fn ($v) => (int) $v)->values(),
                'wallet' => (int) round((float) $p->wallet),
                'paidOut' => (int) round((float) $p->deposit),
                'orders' => (int) ($profits[$p->id]->c ?? 0),
                'earned' => (int) round((float) ($profits[$p->id]->s ?? 0)),
                'deposit' => self::DEPOSIT[(string) $p->deposit_type] ?? 'monthly',
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $p = Partner::find($id);
        if (!$p) {
            return $this->missing('این همکار پیدا نشد.');
        }
        $profits = PartnerProfit::where('partner_id', $p->id)->orderByDesc('id')->take(50)->get();
        $settlements = History::where('type', 'partner')->where('post_id', $p->id)->orderByDesc('id')->take(20)->get();

        return response()->json([
            'services' => $this->services(),
            'partner' => [
                'id' => $p->id,
                'name' => (string) $p->name,
                'mobile' => (string) $p->mobile,
                'live' => (int) $p->status === 1,
                'percent' => (string) (float) $p->percent,
                'serviceIds' => PartnerServices::where('partner_id', $p->id)->pluck('service_id')->map(fn ($v) => (int) $v)->values(),
                'deposit' => self::DEPOSIT[(string) $p->deposit_type] ?? 'monthly',
                'wallet' => (int) round((float) $p->wallet),
                'paidOut' => (int) round((float) $p->deposit),
                'lastPaidAt' => $p->last_deposit ? $this->jdate(date('Y-m-d H:i:s', (int) $p->last_deposit)) : null,
                'earned' => (int) round((float) PartnerProfit::where('partner_id', $p->id)->sum('profit')),
                'createdAt' => $this->jdate($p->created_at, 'Y/m/d'),
            ],
            'profits' => $profits->map(fn ($r) => [
                'id' => $r->id,
                'orderId' => $r->order_id ? (int) $r->order_id : null,
                'service' => __('order.type.' . $r->service_type),
                'amount' => (int) round((float) $r->order_price),
                'profit' => (int) round((float) $r->profit),
                'at' => $this->jdate($r->created_at),
            ])->values(),
            'settlements' => $settlements->map(fn ($h) => [
                'id' => $h->id,
                'amount' => (int) ($this->decode($h->detail)['amount'] ?? 0),
                'note' => (string) ($this->decode($h->detail)['note'] ?? ''),
                'at' => $this->jdate($h->created_at),
            ])->values(),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $p = $id ? Partner::find($id) : new Partner();
        if (!$p) {
            return $this->missing('این همکار پیدا نشد.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'live' => 'boolean',
            'percent' => 'required|numeric|min:0|max:100',
            'serviceIds' => 'required|array|min:1',
            'serviceIds.*' => ['integer', Rule::in(array_keys($this->serviceLabels()))],
            'deposit' => ['required', Rule::in(array_values(self::DEPOSIT))],
        ], $this->messages() + [
            'mobile.regex' => 'شماره موبایل را مثل 09121234567 بنویسید.',
            'serviceIds.required' => 'دست‌کم یک خدمت را انتخاب کنید.',
            'serviceIds.min' => 'دست‌کم یک خدمت را انتخاب کنید.',
            'percent.max' => 'درصد بین ۰ تا ۱۰۰ باشد.',
        ]);

        DB::transaction(function () use ($p, $data) {
            $p->name = $data['name'];
            $p->mobile = $data['mobile'];
            $p->status = ($data['live'] ?? true) ? 1 : 0;
            $p->percent = (string) (0 + $data['percent']);
            $p->percent_type ??= 2;
            $p->deposit_type = (int) array_search($data['deposit'], self::DEPOSIT, true);
            if (!$p->exists) {
                $p->wallet = 0;
                $p->deposit = 0;
            }
            $p->save();

            $ids = array_values(array_unique(array_map('intval', $data['serviceIds'])));
            PartnerServices::where('partner_id', $p->id)->whereNotIn('service_id', $ids)->delete();
            $have = PartnerServices::where('partner_id', $p->id)->pluck('service_id')->map(fn ($v) => (int) $v)->all();
            foreach (array_diff($ids, $have) as $service) {
                $row = new PartnerServices();
                $row->partner_id = $p->id;
                $row->service_id = $service;
                $row->save();
            }
        });

        return response()->json(['success' => $id ? 'همکار ذخیره شد.' : 'همکار ساخته شد.', 'id' => $p->id]);
    }

    /** Records a payout to the partner: the wallet goes down by the amount. */
    public function settle(Request $request, $id)
    {
        $p = Partner::find($id);
        if (!$p) {
            return $this->missing('این همکار پیدا نشد.');
        }
        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ], $this->messages() + ['amount.min' => 'مبلغ را بنویسید.']);

        $done = DB::transaction(function () use ($p, $data) {
            $p = Partner::lockForUpdate()->find($p->id);
            if ($data['amount'] > round((float) $p->wallet)) {
                return false;
            }
            $p->wallet = (float) $p->wallet - $data['amount'];
            $p->deposit = (float) $p->deposit + $data['amount'];
            $p->last_deposit = time();
            $p->save();

            $h = new History();
            $h->type = 'partner';
            $h->post_id = $p->id;
            $h->detail = json_encode(['amount' => $data['amount'], 'note' => $data['note'] ?? '']); // escaped: histories.detail is 3-byte utf8
            $h->admin_id = Auth::guard('admin')->id();
            $h->save();
            return true;
        });
        if (!$done) {
            return response()->json(['message' => 'مبلغ', 'errors' => ['amount' => ['مبلغ از موجودی کیف پول همکار بیشتر است.']]], 422);
        }

        return response()->json(['success' => 'تسویه ثبت شد.']);
    }

    /** A partner with earnings on record stays (switch them off instead), so the history keeps its owner. */
    public function delete($id)
    {
        $p = Partner::find($id);
        if (!$p) {
            return $this->missing('این همکار پیدا نشد.');
        }
        if (PartnerProfit::where('partner_id', $p->id)->exists()) {
            return $this->refuse('این همکار سابقه سهم دارد؛ به جای حذف، غیرفعالش کنید.');
        }
        DB::transaction(function () use ($p) {
            PartnerServices::where('partner_id', $p->id)->delete();
            $p->delete();
        });

        return response()->json(['success' => 'همکار حذف شد.']);
    }

    private function serviceLabels(): array
    {
        return collect(__('order.type'))->except('0')->mapWithKeys(fn ($label, $k) => [(int) $k => $label])->all();
    }

    private function services(): array
    {
        return collect($this->serviceLabels())->map(fn ($label, $id) => ['id' => $id, 'label' => $label])->values()->all();
    }
}
