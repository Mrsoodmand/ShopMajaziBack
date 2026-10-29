<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Pages;
use Illuminate\Http\Request;

/**
 * Service landing pages (`pages` table) for the React admin: texts, image, FAQ and head tags, plus each page's own
 * settings that the old admin kept on its service forms (Apple ID prices on #3, Google Voice price and Numberland on #4).
 * `seo` keeps the keys App\Services\PageSeo reads (meta_title, meta_desc, meta_image) and the old custom head HTML (meta).
 */
class PageApiController extends Controller
{
    use AdminApi;

    const APPLE_ID = 3;
    const GOOGLE_VOICE = 4;

    /** Storefront path of the legacy rows; rows saved by PageSeo carry their key in `slug`. */
    const PATHS = [1 => '/virtual-number', 3 => '/apple-id', 4 => '/google-voice', 5 => '/ip-sabet', 6 => '/virtual-service', 7 => '/accounts'];

    public function index()
    {
        return response()->json([
            'pages' => Pages::orderBy('id')->get()->map(fn (Pages $p) => [
                'id' => $p->id,
                'title' => (string) $p->title,
                'h1' => $p->h1,
                'path' => $this->path($p),
                'faq' => count($this->faqFromStore($p->faq)),
                'hasContent' => trim(strip_tags((string) $p->long_detail)) !== '',
                'updatedAt' => $this->jdate($p->updated_at),
            ])->values(),
        ]);
    }

    public function show($id)
    {
        $p = Pages::find($id);
        if (!$p) {
            return $this->missing('این صفحه پیدا نشد.');
        }
        $seo = $this->decode($p->seo);
        $detail = $this->decode($p->detail);

        return response()->json(['page' => [
            'id' => $p->id,
            'title' => (string) $p->title,
            'path' => $this->path($p),
            'kind' => $this->kind($p),
            'h1' => (string) $p->h1,
            'short' => (string) $p->short_detail,
            'content' => (string) $p->long_detail,
            'image' => (string) $p->image,
            'faq' => $this->faqFromStore($p->faq),
            'metaTitle' => (string) ($seo['meta_title'] ?? ''),
            'metaDesc' => (string) ($seo['meta_desc'] ?? ''),
            'metaImage' => (string) ($seo['meta_image'] ?? ''),
            'headHtml' => (string) ($seo['meta'] ?? ''),
            'settings' => [
                'normalPrice' => isset($detail['normal_price']) && $detail['normal_price'] !== null ? (int) $detail['normal_price'] : null,
                'customPrice' => isset($detail['custom_price']) && $detail['custom_price'] !== null ? (int) $detail['custom_price'] : null,
                'numberlandEnabled' => (int) ($detail['numberland_enabled'] ?? 1) === 1,
                'numberlandMarkup' => (float) ($detail['numberland_markup'] ?? config('services.numberland.gv_markup', 20)),
            ],
        ]]);
    }

    public function save(Request $request, $id)
    {
        $p = Pages::find($id);
        if (!$p) {
            return $this->missing('این صفحه پیدا نشد.');
        }
        $kind = $this->kind($p);
        $data = $request->validate([
            'h1' => 'required|string|max:255',
            'short' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'faq' => 'array',
            'faq.*.q' => 'nullable|string|max:500',
            'faq.*.a' => 'nullable|string',
            'metaTitle' => 'nullable|string|max:255',
            'metaDesc' => 'nullable|string|max:1000',
            'metaImage' => 'nullable|string|max:500',
            'headHtml' => 'nullable|string',
            'settings.normalPrice' => [$kind === 'apple-id' ? 'required' : 'nullable', 'integer', 'min:0'],
            'settings.customPrice' => [in_array($kind, ['apple-id', 'google-voice'], true) ? 'required' : 'nullable', 'integer', 'min:0'],
            'settings.numberlandEnabled' => 'boolean',
            'settings.numberlandMarkup' => 'nullable|numeric|min:0|max:500',
        ], $this->messages());

        $seo = $this->decode($p->seo);
        $clean = fn ($v) => ($v = trim((string) $v)) === '' ? null : $v;
        $seo['meta_title'] = $clean($data['metaTitle'] ?? null);
        $seo['meta_desc'] = $clean($data['metaDesc'] ?? null);
        $seo['meta_image'] = $clean($data['metaImage'] ?? null);
        $seo['meta'] = $clean($data['headHtml'] ?? null);
        $faq = $this->faqToStore($data['faq'] ?? []);

        $p->h1 = $data['h1'];
        $p->short_detail = $data['short'] ?? null;
        $p->long_detail = $data['content'] ?? '';
        $p->image = $data['image'] ?? null;
        $p->faq = json_encode($faq, JSON_UNESCAPED_UNICODE);
        $p->seo = json_encode($seo, JSON_UNESCAPED_UNICODE);

        // Keep every other saved key in `detail` (the storefront reads prices and Numberland settings from it).
        $settings = $data['settings'] ?? [];
        $detail = $this->decode($p->detail);
        if ($kind === 'apple-id') {
            $detail['normal_price'] = (string) $settings['normalPrice'];
            $detail['custom_price'] = (string) $settings['customPrice'];
        } elseif ($kind === 'google-voice') {
            $detail['custom_price'] = (string) $settings['customPrice'];
            $detail['numberland_enabled'] = ($settings['numberlandEnabled'] ?? true) ? 1 : 0;
            $detail['numberland_markup'] = (float) ($settings['numberlandMarkup'] ?? config('services.numberland.gv_markup', 20));
        }
        if ($detail) {
            $p->detail = json_encode($detail, JSON_UNESCAPED_UNICODE);
        }
        $p->save();

        return response()->json(['success' => 'صفحه ذخیره شد.']);
    }

    public function upload(Request $request)
    {
        return $this->storeImage($request, 'image');
    }

    private function kind(Pages $p): string
    {
        return match ((int) $p->id) {
            self::APPLE_ID => 'apple-id',
            self::GOOGLE_VOICE => 'google-voice',
            default => 'page',
        };
    }

    private function path(Pages $p): ?string
    {
        if (isset(self::PATHS[$p->id])) {
            return self::PATHS[$p->id];
        }
        return $p->slug ? (\App\Services\PageSeo::PAGES[$p->slug]['path'] ?? null) : null;
    }
}
