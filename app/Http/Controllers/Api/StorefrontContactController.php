<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\ContactUs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The Next.js /contact-us form (Front/src/design/info/parts.tsx ContactForm). Same rules, table and Telegram alert
 * as the old Blade form (v2\HomeController@submitContactUs); the route adds throttle + captcha:contact.
 */
class StorefrontContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request): JsonResponse
    {
        // Honeypot: a field people never see. Bots that fill it get the same answer and nothing is saved.
        if (filled($request->input('website'))) {
            return $this->success(null, 'پیامتان به دستمان رسید.');
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'email', 'max:50'],
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'subject' => ['required', 'string', 'min:3', 'max:50'],
            'message' => ['required', 'string', 'min:3', 'max:300'],
        ], [
            'name.required' => 'نام خود را بنویسید.',
            'name.min' => 'نام باید دست‌کم ۳ حرف باشد.',
            'name.max' => 'نام حداکثر ۵۰ حرف می‌تواند باشد.',
            'email.required' => 'ایمیل خود را بنویسید.',
            'email.email' => 'ایمیل درست نیست.',
            'email.max' => 'ایمیل حداکثر ۵۰ حرف می‌تواند باشد.',
            'mobile.required' => 'شماره موبایل خود را بنویسید.',
            'mobile.regex' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.',
            'subject.*' => 'موضوع پیام را انتخاب کنید.',
            'message.required' => 'متن پیام را بنویسید.',
            'message.min' => 'متن پیام خیلی کوتاه است.',
            'message.max' => 'متن پیام حداکثر ۳۰۰ حرف می‌تواند باشد.',
        ]);

        $Contact = new ContactUs();
        foreach (['name', 'email', 'mobile', 'subject', 'message'] as $Field) {
            $Contact->{$Field} = strip_tags($data[$Field]);
        }
        $Contact->save();

        try {
            SendTelegramMessage("
💬 فرم درخواست با ما جدیدی ثبت شد!
🔴 عنوان فرم: {$Contact->subject}
👤 نام کاربر: {$Contact->name}
📞 شماره کاربر: {$Contact->mobile}
", -1001509696194);
        } catch (\Throwable $e) {
            report($e); // the message is saved; a failed alert shouldn't fail the form
        }

        return $this->success(null, 'پیامتان به دستمان رسید. به‌زودی با شما تماس می‌گیریم.');
    }
}
