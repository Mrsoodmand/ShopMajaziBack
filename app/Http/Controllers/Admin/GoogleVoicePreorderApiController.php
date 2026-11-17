<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\GoogleVoiceNumbers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Filling a Google Voice pre-order from admin › order (see App\Services\GoogleVoiceNumbers): retry Numberland now,
 * or deliver a login by hand. Refunds use the order page's usual refund form.
 */
class GoogleVoicePreorderApiController extends Controller
{
    /* What the order page shows for a pre-order, or null for any other order. Used by OrderApiController::show. */
    public static function info(Order $order): ?array
    {
        if ((int) $order->type !== GoogleVoiceNumbers::ORDER_TYPE) {
            return null;
        }
        $item = OrderItem::where('order_id', $order->id)->first();
        if (!GoogleVoiceNumbers::isPreorder($item) && $order->status !== 'review') {
            return null;
        }
        $detail = json_decode((string) $item->detail, true) ?: [];
        return [
            'waiting' => in_array($order->status, ['preorder', 'review'], true),
            'wanted' => $detail['wanted'] ?? null,
            'unclear' => $detail['unclear'] ?? null,
            'tries' => (int) ($detail['tries'] ?? 0),
            'autofill' => app(GoogleVoiceNumbers::class)->settings()['preorder_autofill'],
        ];
    }

    public function retry($id, GoogleVoiceNumbers $numbers)
    {
        $order = $this->waiting($id);
        if (!$order) {
            return response()->json(['error' => ['این سفارش در انتظار شماره نیست.']], 422);
        }
        if ($order->status === 'review') { // an older unclear order: make it a pre-order so it can be filled
            $order->status = 'preorder';
            $order->save();
        }
        $result = $numbers->fillPreorders($order->id)[$order->id] ?? 'busy';
        $this->history($order, 'تلاش دوباره خرید از نامبرلند: ' . $result);
        $messages = [
            'ok' => 'شماره از نامبرلند خریده و تحویل شد.',
            'preorder' => 'نامبرلند این بار هم شماره نداد؛ سفارش همچنان پیش‌سفارش است.',
            'no_number' => 'الان شماره‌ای با قیمت مناسب این سفارش در نامبرلند نیست.',
            'balance' => 'موجودی نامبرلند کافی نیست.',
            'unavailable' => 'نامبرلند پاسخ نداد. کمی بعد دوباره تلاش کنید.',
            'review' => 'پاسخ نامبرلند نامشخص بود؛ خرید را در پنل نامبرلند بررسی کنید.',
            'refunded' => 'نامبرلند رد کرد و مبلغ به کیف پول مشتری برگشت.',
            'busy' => 'این سفارش همین الان در حال پردازش است.',
        ];
        $ok = $result === 'ok';
        return response()->json($ok ? ['success' => $messages['ok']] : ['error' => [$messages[$result] ?? $result]], $ok ? 200 : 422);
    }

    public function deliver(Request $request, $id, GoogleVoiceNumbers $numbers)
    {
        $data = $request->validate([
            'number' => 'required|string|max:32',
            'email' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'recovery' => 'nullable|string|max:255',
        ], [], ['number' => 'شماره', 'email' => 'جیمیل', 'password' => 'رمز', 'recovery' => 'ایمیل بازیابی']);
        $order = $this->waiting($id);
        if (!$order || !$numbers->deliverByHand($order, $data)) {
            return response()->json(['error' => ['این سفارش در انتظار شماره نیست.']], 422);
        }
        $this->history($order, 'تحویل دستی شماره ' . $data['number']);
        return response()->json(['success' => 'شماره تحویل شد و مشتری آن را در سفارش‌هایش می‌بیند.']);
    }

    private function waiting($id): ?Order
    {
        $order = Order::where('id', (int) $id)->where('type', GoogleVoiceNumbers::ORDER_TYPE)->first();
        return $order && in_array($order->status, ['preorder', 'review'], true) ? $order : null;
    }

    private function history(Order $order, string $what): void
    {
        $h = new History();
        $h->type = 'order';
        $h->post_id = $order->id;
        $h->detail = json_encode(['old_status' => $order->getOriginal('status'), 'new_status' => $order->fresh()->status, 'detail' => $what, 'changed_date' => Carbon::now()]);
        $h->admin_id = Auth::guard('admin')->id();
        $h->save();
    }
}
