<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\GatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin › درگاه‌های پرداخت (resources/admin/pages/gateways): which bank gateways take payments, in what order, with
 * which merchant ID (stored encrypted, never sent back in full) and whether in test mode.
 */
class PaymentGatewayApiController extends Controller
{
    public function __construct(private GatewayManager $gateways)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->state());
    }

    /**
     * {gateways: [{key, enabled, sandbox, merchant?}], in the order to try them}. `merchant` absent or null keeps
     * the saved one; "" removes it.
     */
    public function save(Request $request): JsonResponse
    {
        $keys = array_keys(GatewayManager::GATEWAYS);
        $data = $request->validate([
            'gateways' => 'required|array|min:1',
            'gateways.*.key' => ['required', 'distinct', Rule::in($keys)],
            'gateways.*.enabled' => 'boolean',
            'gateways.*.sandbox' => 'boolean',
            'gateways.*.merchant' => 'nullable|string|max:120',
        ], [
            'gateways.*.merchant.max' => 'مرچنت حداکثر ۱۲۰ کاراکتر است.',
        ]);
        DB::transaction(function () use ($data) {
            foreach (array_values($data['gateways']) as $i => $g) {
                $values = ['enabled' => (bool) ($g['enabled'] ?? false), 'sandbox' => (bool) ($g['sandbox'] ?? false), 'sort' => $i + 1, 'updated_at' => now()];
                if (array_key_exists('merchant', $g) && $g['merchant'] !== null) {
                    $values['merchant'] = $this->gateways->encrypt(trim($g['merchant']));
                    $values['tested_at'] = null; // a new merchant needs a new test
                    $values['test_result'] = null;
                }
                DB::table('payment_gateways')->updateOrInsert(['key' => $g['key']], $values + ['created_at' => now()]);
            }
        });
        $state = $this->state();
        $warning = collect($state['gateways'])->where('ready', true)->isEmpty() ? ' هیچ درگاهی آماده دریافت پرداخت نیست؛ پرداخت با کارت بانکی تا روشن کردن یک درگاه ممکن نیست.' : '';
        return response()->json(['success' => 'تنظیمات درگاه‌ها ذخیره شد.' . $warning] + $state);
    }

    /** "Test connection": opens a session for 10,000 Rial without charging anything. */
    public function test(string $key): JsonResponse
    {
        abort_unless(isset(GatewayManager::GATEWAYS[$key]), 404);
        [$ok, $message] = $this->gateways->test($key, route('user.service-bank-callback'));
        return response()->json(['ok' => $ok, 'message' => $message] + $this->state());
    }

    private function state(): array
    {
        $since = now()->subDays(30);
        $stats = Payment::where('gate', 'Bank')->where('created_at', '>=', $since)
            ->select(DB::raw("coalesce(gateway, 'zarinpal') as g"), DB::raw('count(*) as started'), DB::raw("sum(status = 'Complete') as paid"))
            ->groupBy('g')->get()->keyBy('g');
        return ['gateways' => $this->gateways->rows()->map(function ($r) use ($stats) {
            $merchant = $r->merchant ?? ($r->key === 'zarinpal' ? (config('services.zarinpal.merchant_id') ?: null) : null);
            return [
                'key' => $r->key,
                'label' => $r->label,
                'enabled' => $r->enabled,
                'sandbox' => $r->sandbox,
                'canSandbox' => (bool) $r->testMerchant,
                // Never the full merchant: its last 4 characters, and where it comes from.
                'merchant' => $merchant ? '••••' . mb_substr($merchant, -4) : null,
                'merchantSource' => $r->merchant ? 'panel' : ($merchant ? 'env' : null),
                'ready' => $r->enabled && (bool) $this->gateways->merchant($r),
                'testedAt' => $r->testedAt,
                'testResult' => $r->testResult,
                'started30d' => (int) optional($stats->get($r->key))->started,
                'paid30d' => (int) optional($stats->get($r->key))->paid,
            ];
        })->values()];
    }
}
