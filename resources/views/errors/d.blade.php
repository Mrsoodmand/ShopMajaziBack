{{--
    Error pages, design D "Broken Route" (chosen in Front's /design/page-errors), in Persian.
    Self-contained on purpose: no layout, session, database or build assets, so it still renders when the error
    comes from one of those. Fonts live in public/errors/fonts. Each errors/<code>.blade.php includes this file.
    Front's app/not-found.tsx and app/error.tsx show the same design for pages Next.js serves; keep the copy in step
    with Front/src/design/errors/copy.ts and src/i18n/fa/errors.ts.
--}}
@php
    $code = (string) ($code ?? 500);
    $copy = [
        '400' => ['این درخواست درست نبود', 'چیزی در لینک یا فرم درست نبود و نتوانستیم آن را باز کنیم.', 'آدرس را بررسی کنید یا برگردید و دوباره امتحان کنید.', false],
        '401' => ['لطفاً اول وارد شوید', 'این صفحه مخصوص حساب کاربری است. وارد شوید تا مستقیم به همین‌جا برگردید.', 'تازه آمده‌اید؟ ثبت‌نام فقط چند ثانیه طول می‌کشد.', false],
        '403' => ['به این صفحه دسترسی ندارید', 'حساب شما اجازه دیدن این صفحه را ندارد.', 'اگر فکر می‌کنید باید داشته باشید، به پشتیبانی پیام دهید تا بررسی کنیم.', false],
        '404' => ['این صفحه را پیدا نکردیم', 'شاید لینک قدیمی باشد یا صفحه جابه‌جا شده باشد. هرچه دنبالش بودید هنوز این‌جاست.', 'سرویس موردنظر را جست‌وجو کنید یا از یکی از این‌ها شروع کنید.', false],
        '419' => ['این صفحه منقضی شد', 'مدتی دور بودید و برای امنیت حسابتان، فرم منقضی شد.', 'صفحه را تازه کنید و دوباره ارسال کنید. هیچ مبلغی کسر نشده است.', true],
        '429' => ['کمی آهسته‌تر', 'در مدت کوتاهی درخواست‌های زیادی از شما دریافت کردیم.', 'یک دقیقه صبر کنید و دوباره امتحان کنید.', true],
        '500' => ['مشکلی از سمت ما پیش آمد', 'مشکل از شما نیست. خطا ثبت شد و در حال رفع آن هستیم.', 'چند لحظه دیگر دوباره امتحان کنید. کیف پول و سفارش‌هایتان امن هستند.', true],
        '503' => ['چند دقیقه دیگر برمی‌گردیم', 'در حال به‌روزرسانی سایت هستیم تا بهتر شود.', 'کیف پول و سفارش‌هایتان امن هستند. کمی بعد دوباره سر بزنید.', true],
    ];
    // Other codes take the copy of their family.
    $key = isset($copy[$code]) ? $code : ($code[0] === '5' ? '500' : '400');
    [$title, $body, $hint, $retry] = $copy[$key];
    $signIn = $code === '401';
    $maintenance = $code === '503';
    $ours = $code[0] === '5';
    $popular = ['/virtual-number' => 'شماره مجازی', '/apple-id' => 'اپل آیدی', '/google-voice' => 'گوگل ویس', '/accounts' => 'اکانت‌های پرمیوم', '/telegram/premium' => 'تلگرام پرمیوم', '/blog' => 'وبلاگ'];
    $faDigits = fn ($s) => strtr((string) $s, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
@endphp
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — شاپ مجازی</title>
    <link rel="icon" href="/upload/logo/favicon-32x32.png">
    <style>
        @font-face { font-family: IRANSans; src: url(/errors/fonts/IRANSansFaNum.woff2) format("woff2"); font-weight: 400; font-display: swap; }
        @font-face { font-family: IRANSans; src: url(/errors/fonts/IRANSansFaNum_Bold.woff2) format("woff2"); font-weight: 700; font-display: swap; }
        :root {
            --page: #F5F6FB; --card: #fff; --ink: #0f172a; --text: #475569; --muted: #64748b; --faint: #94a3b8;
            --line: #e2e8f0; --soft: #f1f5f9; --btn: #0f172a; --btn-ink: #fff; --btn-hover: #1e293b;
            --accent: #4f46e5; --accent-bg: #eef2ff; --accent-ring: #c7d2fe;
            --red: #e11d48; --red-bg: #fff1f2; --red-ring: #fecdd3; --green: #10b981;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --page: #070B14; --card: #0f172a; --ink: #fff; --text: #94a3b8; --muted: #94a3b8; --faint: #64748b;
                --line: rgba(255,255,255,.1); --soft: rgba(255,255,255,.05); --btn: #fff; --btn-ink: #0f172a; --btn-hover: #e2e8f0;
                --accent: #818cf8; --accent-bg: rgba(99,102,241,.1); --accent-ring: rgba(99,102,241,.2);
                --red: #fb7185; --red-bg: rgba(244,63,94,.1); --red-ring: rgba(244,63,94,.2);
            }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: var(--page); color: var(--ink); font-family: IRANSans, Tahoma, system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        .top { display: flex; align-items: center; justify-content: space-between; max-width: 72rem; margin: 0 auto; padding: 1rem 1.5rem; }
        .brand { display: inline-flex; align-items: center; gap: .6rem; font-weight: 700; font-size: 1.1rem; }
        .brand img { width: 36px; height: 36px; border-radius: 10px; }
        .top-link { font-size: .9rem; color: var(--muted); }
        .top-link:hover { color: var(--ink); }
        main { display: grid; gap: 3rem; align-items: center; max-width: 72rem; margin: 0 auto; padding: 2rem 1.5rem 6rem; }
        @media (min-width: 1024px) { main { grid-template-columns: 1fr 1fr; padding-top: 3.5rem; } }
        .code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .875rem; font-weight: 600; color: var(--faint); margin: 0; }
        h1 { margin: .75rem 0 0; font-size: clamp(1.9rem, 4.5vw, 3rem); line-height: 1.3; letter-spacing: -.01em; }
        .body { margin: 1rem 0 0; max-width: 32rem; font-size: 1.125rem; line-height: 1.9; color: var(--text); }
        .hint { margin: .25rem 0 0; max-width: 32rem; line-height: 1.9; color: var(--muted); }
        .actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: .75rem; padding: .75rem 1.25rem; font: 600 .9rem/1.4 inherit; font-family: inherit; cursor: pointer; transition: background .15s, border-color .15s, color .15s; }
        .btn svg { width: 16px; height: 16px; }
        .primary { background: var(--btn); color: var(--btn-ink); border: 1px solid transparent; }
        .primary:hover { background: var(--btn-hover); }
        .ghost { background: var(--card); color: var(--text); border: 1px solid var(--line); }
        .ghost:hover { color: var(--ink); border-color: var(--faint); }
        .search { position: relative; max-width: 28rem; margin-top: 2rem; }
        .search input { width: 100%; border: 1px solid var(--line); background: var(--card); color: var(--ink); border-radius: 1rem; padding: .8rem 3rem .8rem 1rem; font: inherit; font-size: .9rem; outline: none; }
        .search input:focus { border-color: var(--faint); box-shadow: 0 0 0 4px rgba(15,23,42,.05); }
        .search svg { position: absolute; right: 1rem; top: 50%; width: 20px; height: 20px; transform: translateY(-50%); color: var(--faint); }
        .chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; }
        .chip { border: 1px solid var(--line); background: var(--card); color: var(--text); border-radius: 999px; padding: .35rem .9rem; font-size: .875rem; }
        .chip:hover { color: var(--ink); border-color: var(--faint); }
        .support { display: inline-flex; align-items: center; gap: .4rem; margin-top: 2.5rem; font-size: .875rem; color: var(--muted); }
        .support:hover { color: var(--ink); text-decoration: underline; text-underline-offset: 4px; }
        .support svg { width: 16px; height: 16px; }
        .scene { width: 100%; max-width: 28rem; margin: 0 auto; direction: ltr; }
        .window { overflow: hidden; border: 1px solid var(--line); background: var(--card); border-radius: 1rem; box-shadow: 0 20px 40px -12px rgba(15,23,42,.08); }
        .bar { display: flex; align-items: center; gap: .5rem; padding: .75rem 1rem; border-bottom: 1px solid var(--soft); }
        .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--line); }
        .addr { flex: 1; margin-left: .75rem; border-radius: .4rem; background: var(--soft); padding: .25rem .75rem; font: 11px ui-monospace, monospace; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .stage { position: relative; height: 16rem; color: var(--faint); }
        .stage svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .tag { position: absolute; left: 51%; top: 44%; transform: translate(-50%, -50%); border-radius: .5rem; padding: .25rem .6rem; font: 700 .875rem ui-monospace, monospace; background: var(--accent-bg); color: var(--accent); box-shadow: inset 0 0 0 1px var(--accent-ring); }
        .tag.ours { background: var(--red-bg); color: var(--red); box-shadow: inset 0 0 0 1px var(--red-ring); }
        .progress { position: absolute; left: 2.5rem; right: 2.5rem; bottom: 2.5rem; height: 6px; overflow: hidden; border-radius: 999px; background: var(--soft); }
        .progress span { display: block; width: 33%; height: 100%; border-radius: 999px; background: var(--green); animation: fill 2.4s ease-in-out infinite; }
        @keyframes fill { from { transform: translateX(-100%); } to { transform: translateX(300%); } }
        @media (prefers-reduced-motion: reduce) { .progress span { animation: none; } }
    </style>
</head>
<body>
<header class="top">
    <a class="brand" href="/"><img src="/upload/logo/apple-fav.jpg" alt="">شاپ مجازی</a>
    <a class="top-link" href="/">صفحه اصلی</a>
</header>
<main>
    <div>
        <p class="code">خطای {{ $faDigits($code) }}</p>
        <h1>{{ $title }}</h1>
        <p class="body">{{ $body }}</p>
        <p class="hint">{{ $hint }}</p>
        <div class="actions">
            @if ($retry)
                <button type="button" class="btn primary" onclick="location.reload()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.5 0 4.8 1 6.5 2.7L21 8"/><path d="M21 3v5h-5"/></svg>دوباره امتحان کنید</button>
            @endif
            @if ($signIn)
                <a class="btn primary" href="/login">ورود به حساب</a>
            @endif
            <a class="btn {{ $retry || $signIn ? 'ghost' : 'primary' }}" href="/"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>رفتن به صفحه اصلی</a>
            <button type="button" class="btn ghost" onclick="history.length > 1 ? history.back() : location.assign('/')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>بازگشت</button>
        </div>
        @if ($code === '404')
            <form class="search" action="/virtual-service">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input name="q" type="search" placeholder="جست‌وجوی سرویس، اپلیکیشن یا کشور" aria-label="جست‌وجو">
            </form>
            <div class="chips">
                @foreach ($popular as $href => $label)
                    <a class="chip" href="{{ $href }}">{{ $label }}</a>
                @endforeach
            </div>
        @endif
        <a class="support" href="https://t.me/shopmajazi" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="m4.9 4.9 4.3 4.3M14.8 14.8l4.3 4.3M14.8 9.2l4.3-4.3M4.9 19.1l4.3-4.3"/></svg>پیام به پشتیبانی در تلگرام</a>
    </div>

    <div class="scene" aria-hidden="true">
        <div class="window">
            <div class="bar"><span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="addr">shopmajazi.com/…</span></div>
            <div class="stage">
                <svg viewBox="0 0 400 256">
                    <defs><linearGradient id="l" x1="0" x2="1"><stop offset="0" stop-color="currentColor" stop-opacity=".15"/><stop offset="1" stop-color="currentColor" stop-opacity=".6"/></linearGradient></defs>
                    @if ($maintenance)
                        <path d="M40 200 C 140 200, 160 60, 360 60" fill="none" stroke="url(#l)" stroke-width="2" stroke-dasharray="4 6"/>
                    @else
                        <path d="M40 200 C 110 200, 140 130, 180 120" fill="none" stroke="url(#l)" stroke-width="2" stroke-dasharray="4 6"/>
                        <path d="M228 104 C 280 90, 300 60, 360 60" fill="none" stroke="currentColor" stroke-opacity=".12" stroke-width="2" stroke-dasharray="4 6"/>
                        <circle r="4" fill="{{ $ours ? 'var(--red)' : 'var(--accent)' }}"><animateMotion dur="2.6s" repeatCount="indefinite" path="M40 200 C 110 200, 140 130, 180 120"/></circle>
                    @endif
                    <circle cx="40" cy="200" r="6" fill="currentColor" fill-opacity=".45"/>
                    <circle cx="360" cy="60" r="6" fill="currentColor" fill-opacity=".25"/>
                </svg>
                @if ($maintenance)
                    <div class="progress"><span></span></div>
                @else
                    <span class="tag {{ $ours ? 'ours' : '' }}">{{ $code }}</span>
                @endif
            </div>
        </div>
    </div>
</main>
</body>
</html>
