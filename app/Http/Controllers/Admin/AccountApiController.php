<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Foreign accounts and subscriptions for the React admin: products of type "accounts" with their plans
 * (plans.type = accounts). Saved in the old admin's shapes: `detail` JSON (name, type 1 = with plans, price_type
 * 1 = dollar / 0 = toman, texts, meta, image_gallery, seo), `filds` [{type text|select_box, title, col, filed_name,
 * select_value}], `notes` {label: value}. The old site (v2\AccountsController, site-v2/accounts) reads them unchanged.
 */
class AccountApiController extends Controller
{
    use AdminApi;

    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));
        $query = Product::where('type', 'accounts');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('faName', 'LIKE', "%$search%")->orWhere('slug', 'LIKE', "%$search%"));
        }
        $products = $query->orderByDesc('status')->orderByDesc('id')->get();
        $plans = Plan::where('type', 'accounts')->whereIn('product_id', $products->pluck('id'))
            ->select('product_id', DB::raw('count(*) as total'), DB::raw('sum(status = 1) as live'))->groupBy('product_id')->get()->keyBy('product_id');

        return response()->json([
            'accounts' => $products->map(function (Product $p) use ($plans) {
                $d = $this->decode($p->detail);
                return [
                    'id' => $p->id,
                    'name' => (string) ($d['name'] ?? $p->faName),
                    'slug' => (string) $p->slug,
                    'image' => $d['image_gallery'] ?? null,
                    'live' => (int) $p->status === 1,
                    'currency' => (string) ($d['price_type'] ?? '1') === '0' ? 'toman' : 'dollar',
                    'plans' => (int) ($plans[$p->id]->total ?? 0),
                    'livePlans' => (int) ($plans[$p->id]->live ?? 0),
                    'updatedAt' => $this->jdate($p->updated_at),
                ];
            })->values(),
        ]);
    }

    public function show($id)
    {
        $p = Product::where('type', 'accounts')->find($id);
        if (!$p) {
            return $this->missing('این اکانت پیدا نشد.');
        }
        $d = $this->decode($p->detail);
        $notes = $this->decode($p->notes);

        return response()->json(['account' => [
            'id' => $p->id,
            'name' => (string) ($d['name'] ?? $p->faName),
            'slug' => (string) $p->slug,
            'live' => (int) $p->status === 1,
            'withPlans' => (string) ($d['type'] ?? '1') !== '0',
            'currency' => (string) ($d['price_type'] ?? '1') === '0' ? 'toman' : 'dollar',
            'commission' => $p->commission !== null ? (string) $p->commission : '',
            'short' => (string) ($d['short_detail'] ?? ''),
            'content' => (string) ($d['long_detail'] ?? ''),
            'image' => (string) ($d['image_gallery'] ?? ''),
            'metaTitle' => (string) ($d['meta_title'] ?? ''),
            'metaDesc' => (string) ($d['meta_desc'] ?? ''),
            'headHtml' => (string) ($d['seo'] ?? ''),
            'fields' => collect($this->decode($p->filds))->map(fn ($f) => [
                'name' => (string) ($f['filed_name'] ?? ''),
                'title' => (string) ($f['title'] ?? ''),
                'type' => ($f['type'] ?? 'text') === 'select_box' ? 'select' : 'text',
                'options' => ($f['type'] ?? '') === 'select_box'
                    ? array_values(array_filter(array_map('trim', explode(',', (string) ($f['select_value'] ?? ''))))) : [],
                'width' => (string) ($f['col'] ?? ''),
            ])->values(),
            'notes' => collect($notes)->map(fn ($v, $k) => ['label' => (string) $k, 'value' => (string) $v])->values(),
            'plans' => Plan::where('product_id', $p->id)->where('type', 'accounts')->orderBy('sort')->orderBy('id')->get()->map(fn (Plan $pl) => [
                'id' => $pl->id,
                'name' => (string) $pl->name,
                'price' => $pl->price !== null ? (string) $pl->price : '',
                'discount' => $pl->discount !== null ? (string) $pl->discount : '',
                'count' => $pl->count !== null ? (string) $pl->count : '',
                'live' => (int) $pl->status === 1,
            ])->values(),
        ]]);
    }

    public function save(Request $request, $id = null)
    {
        $p = $id ? Product::where('type', 'accounts')->find($id) : new Product();
        if (!$p) {
            return $this->missing('این اکانت پیدا نشد.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->where('type', 'accounts')->ignore($p->id)],
            'live' => 'boolean',
            'withPlans' => 'boolean',
            'currency' => ['required', Rule::in(['dollar', 'toman'])],
            'commission' => 'nullable|numeric|min:0|max:100',
            'short' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|string|max:500',
            'metaTitle' => 'required|string|max:255',
            'metaDesc' => 'required|string|max:1000',
            'headHtml' => 'nullable|string',
            'fields' => 'array',
            'fields.*.name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z][A-Za-z0-9_]*$/', 'distinct'],
            'fields.*.title' => 'required|string|max:255',
            'fields.*.type' => ['required', Rule::in(['text', 'select'])],
            'fields.*.options' => 'array',
            'fields.*.options.*' => 'string|max:100',
            'fields.*.width' => 'nullable|string|max:10',
            'notes' => 'array',
            'notes.*.label' => 'required|string|max:255|distinct',
            'notes.*.value' => 'required|string|max:255',
            'plans' => 'array',
            'plans.*.id' => 'nullable|integer',
            'plans.*.name' => 'required|string|max:255',
            'plans.*.price' => 'required|numeric|min:0',
            'plans.*.discount' => 'nullable|numeric|min:0',
            'plans.*.count' => 'nullable|integer|min:0',
            'plans.*.live' => 'boolean',
        ], $this->messages() + [
            'fields.*.name.regex' => 'نام فنی فقط حروف انگلیسی، عدد و _ (مثل email).',
        ]);
        foreach ($data['fields'] ?? [] as $i => $f) {
            if ($f['type'] === 'select' && empty($f['options'])) {
                return response()->json(['message' => 'گزینه', 'errors' => ["fields.$i.options" => ['دست‌کم یک گزینه بنویسید.']]], 422);
            }
        }

        DB::transaction(function () use ($p, $data) {
            $detail = array_merge($this->decode($p->detail), [
                'name' => $data['name'],
                'type' => ($data['withPlans'] ?? true) ? '1' : '0',
                'price_type' => $data['currency'] === 'toman' ? '0' : '1',
                'short_detail' => $data['short'],
                'long_detail' => $data['content'],
                'slug' => $data['slug'],
                'meta_title' => $data['metaTitle'],
                'meta_desc' => $data['metaDesc'],
                'image_gallery' => $data['image'] ?? null,
                'seo' => $data['headHtml'] ?? null,
            ]);
            $fields = collect($data['fields'] ?? [])->map(fn ($f) => [
                'type' => $f['type'] === 'select' ? 'select_box' : 'text',
                'title' => $f['title'],
                'col' => ($f['width'] ?? '') ?: '6',
                'filed_name' => $f['name'],
                'select_value' => $f['type'] === 'select' ? implode(',', $f['options']) : 'null',
            ])->values()->all();
            $notes = collect($data['notes'] ?? [])->mapWithKeys(fn ($n) => [$n['label'] => $n['value']])->all();

            $p->forceFill([
                'type' => 'accounts',
                'faName' => $data['name'],
                'slug' => $data['slug'],
                'detail' => json_encode($detail, JSON_UNESCAPED_UNICODE),
                'price' => 0,
                'filds' => json_encode($fields, JSON_UNESCAPED_UNICODE),
                'notes' => json_encode((object) $notes, JSON_UNESCAPED_UNICODE),
                'commission' => $data['commission'] ?? null,
                'status' => ($data['live'] ?? false) ? 1 : 0,
            ])->save();

            $keep = [];
            foreach (array_values($data['plans'] ?? []) as $i => $row) {
                $plan = !empty($row['id']) ? Plan::where('product_id', $p->id)->where('type', 'accounts')->find($row['id']) : null;
                $plan ??= new Plan();
                $plan->forceFill([
                    'product_id' => $p->id,
                    'type' => 'accounts',
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'discount' => ($row['discount'] ?? '') === '' ? null : $row['discount'],
                    'count' => ($row['count'] ?? '') === '' ? null : $row['count'],
                    'status' => ($row['live'] ?? true) ? 1 : 0,
                    'sort' => $i + 1,
                ])->save();
                $keep[] = $plan->id;
            }
            Plan::where('product_id', $p->id)->where('type', 'accounts')->whereNotIn('id', $keep)->delete();
        });

        return response()->json(['success' => $id ? 'تغییرات ذخیره شد.' : 'اکانت ساخته شد.', 'id' => $p->id]);
    }

    public function upload(Request $request)
    {
        return $this->storeImage($request, 'accounts');
    }
}
