<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PageSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
 * React admin › SEO: the H1, meta title, meta description and share image of the site's main pages (App\Services\PageSeo).
 * Viewing needs page-list or edit-page; saving needs edit-page (the old admin › pages permissions).
 */
class SeoApiController extends Controller
{
    public function index(PageSeo $Seo)
    {
        $Values = $Seo->all();
        return response()->json([
            'site' => rtrim(StorefrontUrl(), '/'), // for the previews and "view page" links
            'pages' => collect(PageSeo::PAGES)->map(fn($Page, $Key) => [
                'key' => $Key,
                'label' => $Page['label'],
                'path' => $Page['path'],
                'group' => $Page['group'],
                'default' => $Page['default'] ?? null, // the old Laravel pages' own texts; null = the storefront's own
            ] + $Values[$Key])->values(),
        ]);
    }

    public function save(Request $Request, PageSeo $Seo, string $key)
    {
        if (!PageSeo::exists($key)) {
            return response()->json(['error' => ['این صفحه در فهرست سئو نیست.']], 404);
        }
        $Data = $Request->validate([
            'h1' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => ['nullable', 'string', 'max:500', 'regex:#^(/|https?://)#'],
        ], [
            'h1.max' => 'H1 حداکثر ۲۵۵ حرف باشد.',
            'title.max' => 'عنوان حداکثر ۲۵۵ حرف باشد (بهتر است زیر ۶۰ حرف بماند).',
            'description.max' => 'توضیح حداکثر ۵۰۰ حرف باشد (بهتر است زیر ۱۶۰ حرف بماند).',
            'image.regex' => 'آدرس تصویر باید با / یا https:// شروع شود.',
            'string' => 'متن وارد کنید.',
        ]);
        $Seo->save($key, $Data);

        return response()->json(['success' => 'سئوی صفحه ذخیره شد.', 'page' => ['key' => $key] + $Seo->get($key)]);
    }

    /* Share image: shown when the page is shared in Telegram, WhatsApp, X… (1200 × 630 works everywhere). */
    public function upload(Request $Request)
    {
        $Request->validate(['file' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048'], [
            'file.required' => 'فایلی انتخاب نشده است.',
            'file.image' => 'فقط تصویر PNG، JPG یا WebP بارگذاری کنید.',
            'file.mimes' => 'فقط تصویر PNG، JPG یا WebP بارگذاری کنید (شبکه‌های اجتماعی SVG را نشان نمی‌دهند).',
            'file.max' => 'حجم تصویر حداکثر ۲ مگابایت باشد.',
        ]);
        $File = $Request->file('file');
        $Name = (Str::slug(pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'share')
            . '-' . now()->format('YmdHis') . '.' . strtolower($File->getClientOriginalExtension());
        $File->move(public_path('upload/seo'), $Name);

        return response()->json(['path' => '/upload/seo/' . $Name]);
    }
}
