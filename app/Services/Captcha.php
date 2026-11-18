<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/*
 * Captcha for the public forms (comments, sign-in / sign-up, Contact us). Checked by the `captcha` middleware
 * (App\Http\Middleware\VerifyCaptcha); the storefront reads the mode from GET api/v1/storefront/captcha.
 *
 * Modes, chosen in admin › Captcha (Admin\CaptchaApiController), kept in the `settings` row named "captcha":
 * - off:    nothing is checked (the default until an admin turns it on, so local dev needs no keys).
 * - google: Google reCAPTCHA v3, invisible and scored. Keys from RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY.
 *           With `fallback` on, a low score, a blocked Google script or a failed Google check asks for the
 *           number captcha instead, so people on networks that filter Google can still send the form.
 *           Without keys this mode works as `number`.
 * - number: a picture of 5 digits (SVG drawn as distorted strokes, no text in it), answered once within 5 minutes.
 *
 * Requests send `captcha_token` (Google) or `captcha_id` + `captcha_answer` (number). Once a sign-in request
 * (action `login`, in the web session) passes, the next few sign-in requests in that session pass without asking
 * again, so the sign-in steps (number → password or SMS code) need the captcha only once. Other actions never pass.
 */
class Captcha
{
    public const DRIVERS = ['off', 'google', 'number'];

    private const TTL = 300;          // seconds a number challenge stays valid
    private const LENGTH = 5;         // digits in a number challenge
    private const PASS_SECONDS = 600; // a passed captcha covers the session this long…
    private const PASS_USES = 5;      // …for at most this many more requests

    public const MESSAGES = [
        'captcha_required' => 'لطفاً کد امنیتی را وارد کنید.',
        'captcha_invalid' => 'کد امنیتی اشتباه است یا منقضی شده. کد تازه را وارد کنید.',
        'captcha_number' => 'برای ادامه، اعداد تصویر را وارد کنید.',
        'captcha_failed' => 'تأیید امنیتی انجام نشد. صفحه را تازه کنید و دوباره تلاش کنید.',
    ];

    /* Saved settings, with defaults. */
    public function settings(): array
    {
        $Saved = json_decode((string)optional(Setting::where('name', 'captcha')->first())->key, true) ?: [];
        return [
            'driver' => in_array($Saved['driver'] ?? null, self::DRIVERS, true) ? $Saved['driver'] : 'off',
            'fallback' => (bool)($Saved['fallback'] ?? true),
            'min_score' => max(0.1, min(0.9, (float)($Saved['min_score'] ?? 0.5))),
        ];
    }

    public function save(array $Data): void
    {
        $Row = Setting::where('name', 'captcha')->first() ?? (new Setting())->forceFill(['name' => 'captcha']);
        $Row->key = json_encode([
            'driver' => $Data['driver'],
            'fallback' => (bool)$Data['fallback'],
            'min_score' => (float)$Data['min_score'],
        ]);
        $Row->save();
        Cache::forget('captcha-settings');
    }

    public function googleReady(): bool
    {
        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret'));
    }

    /* The mode in use: what the admin chose, with Google turned into `number` when its keys are missing. */
    public function driver(): string
    {
        $Driver = $this->cached()['driver'];
        return $Driver === 'google' && !$this->googleReady() ? 'number' : $Driver;
    }

    /* What the storefront needs to know. */
    public function publicConfig(): array
    {
        $Driver = $this->driver();
        return [
            'driver' => $Driver,
            'site_key' => $Driver === 'google' ? config('services.recaptcha.site_key') : null,
            'fallback' => $Driver === 'google' && $this->cached()['fallback'],
        ];
    }

    /*
     * Checks the request. Returns null when it may go on, else an error code (a key of MESSAGES).
     * $Action: the reCAPTCHA v3 action the page used (comment, login, contact).
     */
    public function check(Request $request, string $Action): ?string
    {
        $Driver = $this->driver();
        if ($Driver === 'off') {
            return null;
        }
        // Only the sign-in steps share a pass; any other form (comments, Contact us, partner pages) asks every time.
        $Session = $Action === 'login' && $request->hasSession() ? $request->session() : null;
        if ($Session && $this->usePass($Session)) {
            return null;
        }
        $Fallback = $Driver === 'number' || $this->cached()['fallback'];

        if (filled($request->input('captcha_id')) && $Fallback) {
            if (!$this->checkNumber((string)$request->input('captcha_id'), (string)$request->input('captcha_answer'))) {
                return 'captcha_invalid';
            }
        } elseif ($Driver === 'google' && filled($request->input('captcha_token'))) {
            if (!$this->checkGoogle((string)$request->input('captcha_token'), $Action)) {
                return $Fallback ? 'captcha_number' : 'captcha_failed';
            }
        } else {
            return $Driver === 'number' ? 'captcha_required' : ($Fallback ? 'captcha_number' : 'captcha_required');
        }

        if ($Session) {
            $Session->put('captcha_pass', ['until' => time() + self::PASS_SECONDS, 'uses' => self::PASS_USES]);
        }
        return null;
    }

    /* A new number challenge: [id, SVG data URI]. */
    public function challenge(): array
    {
        $Answer = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $Answer .= (string)random_int(0, 9);
        }
        $Id = Str::random(32);
        Cache::put("captcha:n:$Id", hash('sha256', $Answer . config('app.key')), self::TTL);
        return ['id' => $Id, 'image' => 'data:image/svg+xml;base64,' . base64_encode($this->svg($Answer)), 'length' => self::LENGTH, 'expires_in' => self::TTL];
    }

    private function cached(): array
    {
        return Cache::remember('captcha-settings', 60, fn () => $this->settings());
    }

    private function usePass($Session): bool
    {
        $Pass = $Session->get('captcha_pass');
        if (!is_array($Pass) || ($Pass['until'] ?? 0) < time() || ($Pass['uses'] ?? 0) < 1) {
            return false;
        }
        $Session->put('captcha_pass', ['until' => $Pass['until'], 'uses' => $Pass['uses'] - 1]);
        return true;
    }

    private function checkNumber(string $Id, string $Answer): bool
    {
        if (!preg_match('/^[A-Za-z0-9]{32}$/', $Id)) {
            return false;
        }
        $Hash = Cache::pull("captcha:n:$Id"); // one try per picture
        $Answer = strtr(trim($Answer), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        return is_string($Hash) && hash_equals($Hash, hash('sha256', $Answer . config('app.key')));
    }

    private function checkGoogle(string $Token, string $Action): bool
    {
        try {
            $Res = Http::asForm()->timeout(6)->post(config('services.recaptcha.verify_url'), [
                'secret' => config('services.recaptcha.secret'),
                'response' => $Token,
            ])->json();
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
        return ($Res['success'] ?? false) === true
            && (float)($Res['score'] ?? 0) >= $this->cached()['min_score']
            && (($Res['action'] ?? $Action) === $Action);
    }

    /* Strokes of each digit in a 10×16 box. */
    private const GLYPHS = [
        0 => [[[5, 0], [8, 1], [10, 4], [10, 12], [8, 15], [5, 16], [2, 15], [0, 12], [0, 4], [2, 1], [5, 0]]],
        1 => [[[2, 3], [6, 0], [6, 16]], [[2, 16], [10, 16]]],
        2 => [[[0, 4], [1, 1], [5, 0], [9, 1], [10, 4], [9, 7], [0, 16], [10, 16]]],
        3 => [[[0, 1], [5, 0], [9, 1], [10, 4], [8, 7], [4, 8]], [[4, 8], [8, 9], [10, 12], [9, 15], [5, 16], [0, 15]]],
        4 => [[[7, 16], [7, 0], [0, 11], [10, 11]]],
        5 => [[[10, 0], [1, 0], [0, 7], [5, 6], [9, 7], [10, 11], [9, 15], [5, 16], [0, 15]]],
        6 => [[[9, 1], [5, 0], [2, 1], [0, 5], [0, 12], [2, 15], [5, 16], [8, 15], [10, 12], [9, 9], [5, 8], [1, 9], [0, 11]]],
        7 => [[[0, 0], [10, 0], [4, 16]], [[3, 8], [8, 8]]],
        8 => [[[5, 8], [1, 6], [1, 2], [5, 0], [9, 2], [9, 6], [5, 8], [0, 11], [1, 15], [5, 16], [9, 15], [10, 11], [5, 8]]],
        9 => [[[1, 15], [5, 16], [8, 15], [10, 11], [10, 4], [8, 1], [5, 0], [2, 1], [0, 4], [1, 7], [5, 8], [9, 7], [10, 5]]],
    ];

    /* The digits as jittered, rotated strokes on a noisy background. The SVG holds no text and no fixed shapes. */
    private function svg(string $Answer): string
    {
        $W = 200;
        $H = 64;
        $R = fn (float $a, float $b) => $a + (random_int(0, 10000) / 10000) * ($b - $a);
        $Colors = ['#1e293b', '#312e81', '#7f1d1d', '#14532d', '#0c4a6e'];
        $Parts = [];

        for ($i = 0; $i < 7; $i++) { // noise curves
            $Parts[] = sprintf('<path d="M%.1f %.1f Q%.1f %.1f %.1f %.1f" stroke="%s" stroke-opacity="%.2f" stroke-width="%.1f" fill="none"/>',
                $R(0, 30), $R(0, $H), $R(40, 160), $R(-20, $H + 20), $R(170, $W), $R(0, $H), $Colors[array_rand($Colors)], $R(0.25, 0.55), $R(1, 2.2));
        }
        for ($i = 0; $i < 40; $i++) { // specks
            $Parts[] = sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="%s" fill-opacity="%.2f"/>', $R(0, $W), $R(0, $H), $R(0.6, 1.8), $Colors[array_rand($Colors)], $R(0.2, 0.6));
        }
        foreach (str_split($Answer) as $n => $Digit) {
            $Scale = $R(1.9, 2.4);
            $Angle = deg2rad($R(-18, 18));
            $Skew = $R(-0.25, 0.25);
            $Cx = 25 + $n * 37 + $R(-3, 3);
            $Cy = $H / 2 + $R(-5, 5);
            $Color = $Colors[array_rand($Colors)];
            foreach (self::GLYPHS[(int)$Digit] as $Stroke) {
                $D = '';
                foreach ($Stroke as $k => [$x, $y]) {
                    $x = ($x - 5 + $R(-0.7, 0.7)) * $Scale;
                    $y = ($y - 8 + $R(-0.7, 0.7)) * $Scale;
                    $x += $y * $Skew;
                    $Px = $Cx + $x * cos($Angle) - $y * sin($Angle);
                    $Py = $Cy + $x * sin($Angle) + $y * cos($Angle);
                    $D .= sprintf('%s%.1f %.1f', $k === 0 ? 'M' : 'L', $Px, $Py);
                }
                $Parts[] = sprintf('<path d="%s" stroke="%s" stroke-width="%.1f" stroke-linecap="round" stroke-linejoin="round" fill="none"/>', $D, $Color, $R(2.4, 3.4));
            }
        }
        shuffle($Parts);
        return sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d"><rect width="100%%" height="100%%" fill="#f8fafc"/>%s</svg>', $W, $H, $W, $H, implode('', $Parts));
    }
}
