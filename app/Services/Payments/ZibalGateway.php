<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Zibal (https://gateway.zibal.ir, docs: https://help.zibal.ir/ipg/ — spec https://api.zibal.ir/static/helpdocs/ipg.json).
 * Amounts in Rial. The callback comes back as GET ?success=1&trackId=…&orderId=…&status=…; a payment is final only
 * after /v1/verify answers result 100 (201 = verified before). Test merchant: "zibal".
 */
class ZibalGateway implements Gateway
{
    private const BASE = 'https://gateway.zibal.ir';

    // /v1/request result codes (help.zibal.ir/ipg › جداول).
    private const REQUEST_ERRORS = [
        102 => 'مرچنت یافت نشد.', 103 => 'مرچنت غیرفعال است یا قرارداد درگاه امضا نشده.', 104 => 'مرچنت نامعتبر است.',
        105 => 'مبلغ باید حداقل ۱٬۰۰۰ ریال باشد.', 106 => 'آدرس بازگشت نامعتبر است (باید روی دامنه ثبت‌شده درگاه باشد).',
        113 => 'مبلغ از سقف مجاز درگاه بیشتر است.', 114 => 'کد ملی نامعتبر است.', 115 => 'IP سرور در پنل زیبال ثبت نشده است.',
        140 => 'آدرس بازگشت ارسال نشده است.',
    ];
    // Payment status codes.
    private const STATUS = [
        -1 => 'در انتظار پرداخت', -2 => 'خطای داخلی', 1 => 'پرداخت شده - تاییدشده', 2 => 'پرداخت شده - تاییدنشده',
        3 => 'لغو شده توسط کاربر', 4 => 'شماره کارت نامعتبر است.', 5 => 'موجودی حساب کافی نیست.', 6 => 'رمز واردشده اشتباه است.',
        7 => 'تعداد درخواست‌ها بیش از حد مجاز است.', 8 => 'تعداد پرداخت اینترنتی روزانه بیش از حد مجاز است.',
        9 => 'مبلغ پرداخت اینترنتی روزانه بیش از حد مجاز است.', 10 => 'صادرکننده کارت نامعتبر است.', 11 => 'خطای سوییچ',
        12 => 'کارت قابل دسترسی نیست.', 15 => 'تراکنش استرداد شده', 16 => 'تراکنش در حال استرداد', 18 => 'تراکنش ریورس شده',
        21 => 'پذیرنده نامعتبر است.',
    ];

    public function start(string $merchant, int $rial, string $callbackUrl, string $description, array $extra): StartResult
    {
        $body = array_filter([
            'merchant' => $merchant,
            'amount' => $rial,
            'callbackUrl' => $callbackUrl,
            'description' => $description,
            'orderId' => isset($extra['orderId']) ? (string) $extra['orderId'] : null,
            'mobile' => $extra['mobile'] ?? null,
            'allowedCards' => !empty($extra['allowedCards']) ? array_values($extra['allowedCards']) : null,
        ], fn ($v) => $v !== null && $v !== '');
        try {
            $res = Http::connectTimeout(10)->timeout(25)->acceptJson()->asJson()->post(self::BASE . '/v1/request', $body)->json();
        } catch (Throwable $e) {
            return new StartResult(false, error: 'Zibal: ' . $e->getMessage());
        }
        if ((int) ($res['result'] ?? 0) === 100 && !empty($res['trackId'])) {
            $trackId = (string) $res['trackId'];
            return new StartResult(true, self::BASE . '/start/' . $trackId, $trackId);
        }
        $code = (int) ($res['result'] ?? 0);
        return new StartResult(false, error: "Zibal ($code): " . (self::REQUEST_ERRORS[$code] ?? ($res['message'] ?? 'پاسخی از درگاه دریافت نشد.')));
    }

    public function referenceFrom(Request $request): ?string
    {
        return $request->filled('trackId') ? (string) $request->input('trackId') : null;
    }

    public function callbackPaid(Request $request): bool
    {
        return (string) $request->input('success') === '1';
    }

    public function verify(string $merchant, string $reference, int $rial): VerifyResult
    {
        try {
            $res = Http::connectTimeout(10)->timeout(25)->acceptJson()->asJson()->post(self::BASE . '/v1/verify', ['merchant' => $merchant, 'trackId' => (int) $reference])->json();
        } catch (Throwable $e) {
            return new VerifyResult(false, message: 'Zibal: ' . $e->getMessage());
        }
        $result = (int) ($res['result'] ?? 0);
        if (in_array($result, [100, 201], true)) {
            // The amount paid must be the payment's amount (Rial).
            if (isset($res['amount']) && (int) $res['amount'] !== $rial) {
                return new VerifyResult(false, code: 'amount', message: 'مبلغ پرداخت‌شده با مبلغ سفارش یکسان نیست. با پشتیبانی تماس بگیرید.');
            }
            return new VerifyResult($result === 100, $result === 201, isset($res['refNumber']) ? (string) $res['refNumber'] : null,
                $res['cardNumber'] ?? null, null, (string) $result);
        }
        $status = isset($res['status']) ? (int) $res['status'] : null;
        $message = $result === 202 && $status !== null ? (self::STATUS[$status] ?? 'پرداخت انجام نشد.') : ($res['message'] ?? 'پرداخت تایید نشد.');
        return new VerifyResult(false, code: (string) $result, message: $message);
    }
}
