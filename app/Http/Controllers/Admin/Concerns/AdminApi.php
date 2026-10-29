<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\lib\Jdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        return ['page' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'total' => $page->total(), 'perPage' => $page->perPage()];
    }

    /* ---------- List filters (the React lists send these as ?per=, ?from=, ?to=, ?min=, ?max=) ---------- */

    /** Rows per page the admin picked, limited to the sizes the list offers. */
    protected function perPage(Request $request, int $default): int
    {
        $per = (int) $request->input('per');
        return in_array($per, [20, 30, 50, 100], true) ? $per : $default;
    }

    /** Numbers typed with Persian or Arabic digits, as western digits. */
    protected function westernDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * Jalali day range (1404/08/07) from the request → Gregorian [from, to] as Y-m-d, null where empty.
     * A date that is not a real Jalali day answers 422 on that field.
     */
    protected function jalaliDays(Request $request, string $fromKey = 'from', string $toKey = 'to'): array
    {
        static $jdf = null;
        $jdf ??= new Jdf();
        $out = [];
        $errors = [];
        foreach ([$fromKey, $toKey] as $key) {
            $raw = $this->westernDigits(trim((string) $request->input($key, '')));
            if ($raw === '') {
                $out[] = null;
                continue;
            }
            if (!preg_match('~^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})$~', $raw, $m) || !$jdf->jcheckdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                $errors[$key] = ['تاریخ را به شکل ۱۴۰۴/۰۸/۰۷ بنویسید.'];
                $out[] = null;
                continue;
            }
            [$gy, $gm, $gd] = $jdf->jalali_to_gregorian((int) $m[1], (int) $m[2], (int) $m[3]);
            $out[] = sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        return $out;
    }

    /** Limits a date column to the whole Jalali days in ?from= / ?to=. */
    protected function filterDays($query, Request $request, string $column = 'created_at', string $fromKey = 'from', string $toKey = 'to'): void
    {
        [$from, $to] = $this->jalaliDays($request, $fromKey, $toKey);
        if ($from) {
            $query->where($column, '>=', $from . ' 00:00:00');
        }
        if ($to) {
            $query->where($column, '<=', $to . ' 23:59:59');
        }
    }

    /** Limits a number column (toman, counts) to ?min= / ?max=; separators and Persian digits are fine. */
    protected function filterBetween($query, Request $request, string $column, string $minKey = 'min', string $maxKey = 'max'): void
    {
        foreach ([$minKey => '>=', $maxKey => '<='] as $key => $op) {
            $raw = preg_replace('/[,،٬\s]/u', '', $this->westernDigits((string) $request->input($key, '')));
            if ($raw !== '' && ctype_digit($raw)) {
                $query->where($column, $op, (int) $raw);
            }
        }
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
