<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Captcha;
use Illuminate\Http\Request;

/** admin › کپچا (super-admin): which captcha the public forms use. See App\Services\Captcha. */
class CaptchaApiController extends Controller
{
    public function index(Captcha $captcha)
    {
        return response()->json([
            'settings' => $captcha->settings(),
            'googleReady' => $captcha->googleReady(),
            'active' => $captcha->driver(),
        ]);
    }

    public function save(Request $request, Captcha $captcha)
    {
        $data = $request->validate([
            'driver' => 'required|in:' . implode(',', Captcha::DRIVERS),
            'fallback' => 'required|boolean',
            'min_score' => 'required|numeric|min:0.1|max:0.9',
        ], [
            // No resources/lang/fa/validation.php, so the messages are spelled out here.
            'driver.*' => 'یکی از نوع‌های کپچا را انتخاب کنید.',
            'fallback.*' => 'مقدار کپچای عددی جایگزین معتبر نیست.',
            'min_score.*' => 'حداقل امتیاز باید عددی بین ۰٫۱ و ۰٫۹ باشد.',
        ]);
        $captcha->save($data);
        return response()->json(['success' => 'تنظیمات کپچا ذخیره شد و همین حالا روی فرم‌ها اعمال می‌شود.']);
    }
}
