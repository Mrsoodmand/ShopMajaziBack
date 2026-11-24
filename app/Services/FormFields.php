<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Forms an admin builds (key, title, type, required, enabled, hint, options, accept, max_mb, sort, builtin) and the
 * answers customers send. Same rules as App\Services\Organizations' form, shared so each new form doesn't copy them.
 * Used by App\Services\AccountRequests. Answers are kept as a snapshot ([{key, title, type, value, file?, mime?,
 * size?}]), so editing the form later never changes what was sent.
 */
class FormFields
{
    public const TYPES = ['text', 'email', 'phone', 'textarea', 'number', 'select', 'yesno', 'date', 'file'];
    public const ACCEPT = [
        'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heic'],
        'pdf' => ['application/pdf' => 'pdf'],
    ];

    /** One field as the storefront form ($Admin false) or the admin editor shows it. */
    public static function data(Model $F, bool $Admin = false): array
    {
        $Data = [
            'key' => $F->key,
            'title' => $F->title,
            'title_en' => $F->title_en ?: null,
            'type' => $F->type,
            'required' => (bool)$F->required,
            'hint' => $F->hint ?: null,
            'hint_en' => $F->hint_en ?: null,
            'options' => $F->type === 'select' ? array_values((array)$F->options) : [],
            'accept' => $F->type === 'file' ? (array)($F->accept ?: ['image']) : [],
            'max_mb' => $F->type === 'file' ? (int)($F->max_mb ?: 5) : null,
        ];
        if ($Admin) {
            $Data += ['id' => $F->id, 'enabled' => (bool)$F->enabled, 'builtin' => (bool)$F->builtin, 'sort' => (int)$F->sort];
        }
        return $Data;
    }

    /*
     * Replaces the form with the admin's rows (in order). $Builtin keys can be retitled, made optional or switched off,
     * never deleted or retyped; $AlwaysOn ones can't be switched off or made optional either.
     * @param class-string<Model> $Model
     * @return array{0: bool, 1: ?string} [ok, error]
     */
    public static function save(string $Model, array $Rows, array $Builtin, int $MaxMb, array $AlwaysOn = []): array
    {
        $Clean = [];
        $Keys = [];
        foreach (array_values($Rows) as $I => $R) {
            $Key = trim((string)($R['key'] ?? ''));
            $IsBuiltin = in_array($Key, $Builtin, true);
            $Type = $IsBuiltin ? null : (string)($R['type'] ?? 'text');
            $Title = trim((string)($R['title'] ?? ''));
            if ($Key === '') {
                $Key = 'q_' . Str::lower(Str::random(8));
            }
            if (!preg_match('/^[a-z][a-z0-9_]{0,49}$/', $Key) || isset($Keys[$Key])) {
                return [false, 'کلید «' . $Key . '» معتبر یا یکتا نیست.'];
            }
            if ($Title === '') {
                return [false, 'عنوان همه فیلدها را بنویسید.'];
            }
            if (!$IsBuiltin && !in_array($Type, self::TYPES, true)) {
                return [false, 'نوع فیلد «' . $Title . '» معتبر نیست.'];
            }
            $Options = array_values(array_unique(array_filter(array_map(fn($o) => mb_substr(trim((string)$o), 0, 100), (array)($R['options'] ?? [])), 'strlen')));
            if ($Type === 'select' && count($Options) < 2) {
                return [false, 'فیلد «' . $Title . '» دست کم دو گزینه لازم دارد.'];
            }
            $Locked = in_array($Key, $AlwaysOn, true);
            $Keys[$Key] = true;
            $Clean[] = [
                'key' => $Key, 'builtin' => $IsBuiltin, 'type' => $Type,
                'title' => mb_substr($Title, 0, 190),
                'title_en' => mb_substr(trim((string)($R['title_en'] ?? '')), 0, 190) ?: null,
                'required' => $Locked || (bool)($R['required'] ?? true),
                'enabled' => $Locked || (bool)($R['enabled'] ?? true),
                'hint' => mb_substr(trim((string)($R['hint'] ?? '')), 0, 190) ?: null,
                'hint_en' => mb_substr(trim((string)($R['hint_en'] ?? '')), 0, 190) ?: null,
                'options' => $Type === 'select' ? $Options : null,
                'accept' => $Type === 'file' ? (array_values(array_intersect(array_keys(self::ACCEPT), (array)($R['accept'] ?? []))) ?: ['image']) : null,
                'max_mb' => $Type === 'file' ? max(1, min($MaxMb, (int)($R['max_mb'] ?? 5))) : null,
                'sort' => $I + 1,
            ];
        }
        foreach ($Builtin as $Key) {
            if (!isset($Keys[$Key])) {
                return [false, 'فیلدهای اصلی فرم را نمی‌توان حذف کرد؛ می‌توانید خاموششان کنید.'];
            }
        }

        DB::transaction(function () use ($Model, $Clean) {
            $Model::whereNotIn('key', array_column($Clean, 'key'))->where('builtin', false)->delete();
            foreach ($Clean as $C) {
                $Row = $Model::firstOrNew(['key' => $C['key']]);
                $IsBuiltin = $C['builtin'];
                unset($C['builtin']);
                if ($IsBuiltin) {
                    unset($C['type']); // keeps its own
                }
                $Row->fill($C);
                $Row->builtin = $IsBuiltin;
                $Row->save();
            }
        });
        return [true, null];
    }

    /*
     * Checks what the customer sent against the form: {key: value | UploadedFile}.
     * @return array{0: array, 1: array, 2: ?array} [answers, files {key: {file, ext}}, error [message, field key]]
     */
    public static function answers(Collection $Fields, array $Given): array
    {
        $Answers = [];
        $Files = [];
        foreach ($Fields as $F) {
            $Raw = $Given[$F->key] ?? null;
            $Bad = fn(string $Why = '') => [[], [], [$Why ?: 'لطفا «' . $F->title . '» را درست وارد کنید.', $F->key]];

            if ($F->type === 'file') {
                $File = $Raw instanceof UploadedFile ? $Raw : null;
                if (!$File) {
                    if ($F->required) {
                        return $Bad('لطفا فایل «' . $F->title . '» را بفرستید.');
                    }
                    continue;
                }
                $Mimes = array_merge(...array_map(fn($a) => self::ACCEPT[$a] ?? [], (array)($F->accept ?: ['image'])));
                $Mime = $File->isValid() ? (string)$File->getMimeType() : '';
                if (!isset($Mimes[$Mime])) {
                    return $Bad('فرمت فایل «' . $F->title . '» پذیرفته نیست.');
                }
                if ($File->getSize() > (int)($F->max_mb ?: 5) * 1024 * 1024) {
                    return $Bad('حجم فایل «' . $F->title . '» باید حداکثر ' . (int)($F->max_mb ?: 5) . ' مگابایت باشد.');
                }
                $Files[$F->key] = ['file' => $File, 'ext' => $Mimes[$Mime]];
                $Base = trim((string)preg_replace('/[^\p{L}\p{N} ._()-]+/u', '', pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME))) ?: 'file';
                $Answers[] = ['key' => $F->key, 'title' => $F->title, 'type' => 'file', 'value' => mb_substr($Base, 0, 80) . '.' . $Mimes[$Mime], 'mime' => $Mime, 'size' => (int)$File->getSize()];
                continue;
            }

            // Western digits where they are checked as numbers; free text keeps the customer's own digits.
            $Value = is_scalar($Raw) ? trim((string)$Raw) : '';
            if (in_array($F->type, ['number', 'phone', 'date', 'email'], true)) {
                $Value = convertPersianNumbersToEnglish($Value);
            }
            if ($Value === '') {
                if ($F->required) {
                    return $Bad('لطفا «' . $F->title . '» را وارد کنید.');
                }
                continue;
            }
            $Ok = match ($F->type) {
                'email' => filter_var($Value, FILTER_VALIDATE_EMAIL) && strlen($Value) <= 150,
                'phone' => (bool)preg_match('/^\+?[0-9][0-9 \-]{4,19}$/', $Value),
                'number' => is_numeric($Value) && strlen($Value) <= 20,
                'select' => in_array($Value, (array)$F->options, true),
                'yesno' => in_array($Value, ['yes', 'no'], true),
                'date' => (bool)preg_match('~^\d{4}[/\-]\d{1,2}[/\-]\d{1,2}$~', $Value),
                'textarea' => mb_strlen($Value) <= 2000,
                default => mb_strlen($Value) <= 255,
            };
            if (!$Ok) {
                return $Bad();
            }
            $Answers[] = ['key' => $F->key, 'title' => $F->title, 'type' => $F->type, 'value' => strip_tags($Value)];
        }
        return [$Answers, $Files, null];
    }

    /** Puts the files on the private disk under $Folder and records each path in the answers' snapshot. */
    public static function storeFiles(array $Answers, array $Files, string $Disk, string $Folder): array
    {
        if (!$Files) {
            return $Answers;
        }
        $Store = Storage::disk($Disk);
        foreach ($Answers as &$Row) {
            if (isset($Files[$Row['key']])) {
                $F = $Files[$Row['key']];
                $Path = $Folder . '/' . $Row['key'] . '-' . Str::random(20) . '.' . $F['ext'];
                $Store->put($Path, file_get_contents($F['file']->getRealPath()));
                $Row['file'] = $Path;
            }
        }
        unset($Row);
        return $Answers;
    }

    /** The answers as an admin ($FileUrl gives each file's admin-only link) or the customer (no paths) sees them. */
    public static function shown(array $Answers, ?callable $FileUrl): array
    {
        return array_map(function ($A) use ($FileUrl) {
            $Row = ['key' => $A['key'], 'title' => $A['title'], 'type' => $A['type'], 'value' => $A['value']];
            if ($A['type'] === 'file' && $FileUrl) {
                $Row['url'] = !empty($A['file']) ? $FileUrl($A['key']) : null;
                $Row['mime'] = $A['mime'] ?? null;
            }
            return $Row;
        }, $Answers);
    }

    /** A customer's file to an admin: inline, sandboxed, never cached. Null when it isn't there. */
    public static function fileResponse(array $Answers, string $Key, string $Disk, string $Folder)
    {
        $Row = collect($Answers)->first(fn($a) => ($a['key'] ?? null) === $Key && ($a['type'] ?? null) === 'file');
        $Store = Storage::disk($Disk);
        $Path = (string)($Row['file'] ?? '');
        if (!$Row || $Path === '' || !str_starts_with($Path, $Folder . '/') || str_contains($Path, '..') || !$Store->exists($Path)) {
            return null;
        }
        $Allowed = array_merge(...array_map('array_keys', array_values(self::ACCEPT)));
        $Mime = in_array($Row['mime'] ?? '', $Allowed, true) ? $Row['mime'] : 'application/octet-stream';
        $Headers = ['Content-Type' => $Mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer'];
        if (str_starts_with($Mime, 'image/')) {
            $Headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";
        }
        return $Store->response($Path, (string)($Row['value'] ?? basename($Path)), $Headers, 'inline');
    }
}
