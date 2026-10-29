<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\lib\Jdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/** Small helpers shared by the React admin's JSON controllers. */
trait AdminApi
{
    protected function missing(string $message)
    {
        return response()->json(['error' => [$message]], 404);
    }

    protected function refuse(string $message, int $status = 422)
    {
        return response()->json(['error' => [$message]], $status);
    }

    /** Jalali "1404/08/07 14:05" in the shop's time zone, or null. */
    protected function jdate($value, string $format = 'Y/m/d H:i'): ?string
    {
        static $jdf = null;
        $jdf ??= new Jdf();
        return $value ? $jdf->jdate($format, strtotime((string) $value)) : null;
    }

    protected function decode($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) ? $decoded : [];
    }

    /** Paging fields every list answers with. */
    protected function pageMeta(LengthAwarePaginator $page): array
    {
        return ['page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total()];
    }

    /** Stores one uploaded image under public/upload/{folder} and answers with its public path. */
    protected function storeImage(Request $request, string $folder, bool $svg = false)
    {
        $types = $svg ? 'png,jpg,jpeg,webp,svg' : 'png,jpg,jpeg,webp';
        $request->validate(['file' => 'required|file|mimes:' . $types . '|max:2048'], [
            'file.required' => 'فایلی انتخاب نشده است.',
            'file.mimes' => $svg ? 'فقط تصویر (PNG، JPG، WebP یا SVG) بارگذاری کنید.' : 'فقط تصویر (PNG، JPG یا WebP) بارگذاری کنید.',
            'file.max' => 'حجم تصویر حداکثر ۲ مگابایت باشد.',
        ]);
        $file = $request->file('file');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        // The extension comes from the file's content, never from the name the browser sent.
        $name .= '-' . now()->format('YmdHis') . '.' . ($file->guessExtension() ?: 'bin');
        $file->move(public_path('upload/' . $folder), $name);

        return response()->json(['path' => '/upload/' . $folder . '/' . $name]);
    }

    /** FAQ rows [{q, a}] in the stored shape [{question, answer}], or null when empty (as the old admin saved it). */
    protected function faqToStore(array $rows): ?array
    {
        $faq = collect($rows)->filter(fn ($r) => trim((string) ($r['q'] ?? '')) !== '')
            ->map(fn ($r) => ['question' => trim($r['q']), 'answer' => (string) ($r['a'] ?? '')])->values()->all();
        return $faq ?: null;
    }

    protected function faqFromStore($value): array
    {
        return collect($this->decode($value))->filter(fn ($r) => is_array($r) && isset($r['question']))
            ->map(fn ($r) => ['q' => (string) $r['question'], 'a' => (string) ($r['answer'] ?? '')])->values()->all();
    }

    /** Persian validation messages shared by the editors. */
    protected function messages(): array
    {
        return [
            'required' => 'این فیلد را پر کنید.',
            'required_if' => 'این فیلد را پر کنید.',
            'string' => 'متن وارد کنید.',
            'integer' => 'یک عدد صحیح بنویسید.',
            'numeric' => 'یک عدد بنویسید.',
            'email' => 'یک ایمیل درست بنویسید.',
            'min' => 'مقدار کمتر از حد مجاز است.',
            'max' => 'مقدار بیشتر از حد مجاز است.',
            'in' => 'یکی از گزینه‌ها را انتخاب کنید.',
            'array' => 'فهرست نامعتبر است.',
            'distinct' => 'این مقدار تکراری است.',
            'slug.regex' => 'فقط حروف کوچک انگلیسی، عدد و خط تیره (مثل my-post).',
            'slug.unique' => 'این نامک قبلاً استفاده شده است.',
            'date_format' => 'تاریخ را به شکل ۱۴۰۴/۰۸/۰۷ بنویسید.',
        ];
    }
}
