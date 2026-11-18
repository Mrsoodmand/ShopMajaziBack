{{--
    Captcha for an old Blade form (App\Services\Captcha, admin › کپچا). Put it inside the <form>:
        @include('captcha.field', ['action' => 'comment'])           // Persian
        @include('captcha.field', ['action' => 'partner', 'lang' => 'en'])
    `action` is the reCAPTCHA v3 action and must match the route's `captcha:<action>` middleware.
    Works with plain form posts (the middleware sends them back with a `captcha` error) and with jQuery AJAX submits
    (a captcha error in the JSON answer brings up a new picture). No jQuery needed. Next.js forms use Front/src/lib/captcha.tsx.
--}}
@php
    $CaptchaLang = ($lang ?? 'fa') === 'en' ? 'en' : 'fa';
    $CaptchaText = $CaptchaLang === 'en'
        ? ['label' => 'Type the numbers in the picture', 'alt' => 'Security code picture', 'new' => 'New picture',
           'google' => 'This form is protected by Google reCAPTCHA.', 'privacy' => 'Privacy', 'terms' => 'Terms',
           'error' => 'The security check didn\'t pass. Type the numbers in the new picture.']
        : ['label' => 'اعداد داخل تصویر را وارد کنید', 'alt' => 'تصویر کد امنیتی', 'new' => 'تصویر جدید',
           'google' => 'این فرم با reCAPTCHA گوگل محافظت می‌شود.', 'privacy' => 'حریم خصوصی', 'terms' => 'شرایط',
           'error' => null];
    $CaptchaError = isset($errors) ? $errors->first('captcha') : null;
    $CaptchaConfig = app(\App\Services\Captcha::class)->publicConfig();
@endphp
<div class="shop-captcha" data-action="{{ $action }}" data-config='@json($CaptchaConfig)' style="margin:0 0 1rem">
    <input type="hidden" name="captcha_token" value="">
    <input type="hidden" name="captcha_id" value="" disabled>
    <div class="shop-captcha-number" style="display:none">
        <label style="display:block;margin-bottom:.4rem">{{ $CaptchaText['label'] }}</label>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:.5rem">
            <img alt="{{ $CaptchaText['alt'] }}" width="176" height="56" style="border-radius:.5rem;background:#f8fafc">
            <input class="form-control" name="captcha_answer" type="text" inputmode="numeric" autocomplete="off" dir="ltr" maxlength="5" disabled
                   style="width:10rem;max-width:10rem;letter-spacing:.3em;text-align:center">
            <button type="button" class="btn btn-link btn-sm shop-captcha-refresh">{{ $CaptchaText['new'] }}</button>
        </div>
    </div>
    <p class="shop-captcha-google small text-muted" style="display:none;margin:0">
        {{ $CaptchaText['google'] }}
        <a href="https://policies.google.com/privacy" target="_blank" rel="noreferrer">{{ $CaptchaText['privacy'] }}</a> ·
        <a href="https://policies.google.com/terms" target="_blank" rel="noreferrer">{{ $CaptchaText['terms'] }}</a>
    </p>
    @if($CaptchaError)
        <p class="text-danger" style="margin:.4rem 0 0"><strong>{{ $CaptchaText['error'] ?? $CaptchaError }}</strong></p>
    @endif
</div>
<script>
    (function () {
        var root = document.currentScript.previousElementSibling, form = root.closest('form');
        var $ = function (s) { return root.querySelector(s); };
        var token = $('[name=captcha_token]'), id = $('[name=captcha_id]'), answer = $('[name=captcha_answer]'), img = $('img');
        var cfg = JSON.parse(root.getAttribute('data-config')), action = root.getAttribute('data-action');
        var mode = 'off', google = null, passing = false;

        // One Google script per page, from recaptcha.net (reachable where www.google.com is filtered).
        function loadGoogle(key) {
            var w = window.__shopCaptchaGoogle = window.__shopCaptchaGoogle || {};
            return w[key] = w[key] || new Promise(function (resolve, reject) {
                var timer = setTimeout(function () { reject(); }, 8000);
                var st = document.createElement('style');
                st.textContent = '.grecaptcha-badge{visibility:hidden}'; // the notice below replaces Google's badge
                document.head.appendChild(st);
                var s = document.createElement('script');
                s.src = 'https://www.recaptcha.net/recaptcha/api.js?render=' + encodeURIComponent(key);
                s.async = true;
                s.onload = function () { grecaptcha.ready(function () { clearTimeout(timer); resolve(grecaptcha); }); };
                s.onerror = function () { clearTimeout(timer); reject(); };
                document.head.appendChild(s);
            });
        }
        function show(el, on) { el.style.display = on ? '' : 'none'; }
        function number() { // a picture is good for one try
            mode = 'number';
            show($('.shop-captcha-google'), false);
            show($('.shop-captcha-number'), true);
            id.disabled = answer.disabled = false;
            answer.value = '';
            fetch('/api/v1/storefront/captcha/number', {cache: 'no-store', headers: {Accept: 'application/json'}})
                .then(function (r) { return r.json(); })
                .then(function (j) { if (j.data) { id.value = j.data.id; img.src = j.data.image; } });
        }
        function apply(c) {
            cfg = c;
            if (c.driver === 'number') return number();
            show($('.shop-captcha-number'), false);
            id.disabled = answer.disabled = true;
            if (c.driver !== 'google' || !c.site_key) { mode = 'off'; show($('.shop-captcha-google'), false); return; }
            mode = 'google';
            show($('.shop-captcha-google'), true);
            loadGoogle(c.site_key).then(function (g) { google = g; }, function () { if (c.fallback && mode === 'google') number(); });
        }

        // Google: a fresh token right before the form goes, whether it is posted normally or sent by a page script
        // bound to the submit button's click. The event is held, the token fetched, and the click/submit replayed.
        function hold(e, replay) {
            if (passing || mode !== 'google' || !google) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            token.value = '';
            google.execute(cfg.site_key, {action: action}).then(function (t) { token.value = t; }, function () {}).then(function () {
                passing = true;
                try { replay(); } finally { passing = false; }
            });
        }
        form.addEventListener('click', function (e) {
            var b = e.target.closest('button[type=submit], input[type=submit], .submit-comment');
            if (b && form.contains(b)) hold(e, function () { b.click(); });
        }, true);
        form.addEventListener('submit', function (e) {
            hold(e, function () { form.requestSubmit ? form.requestSubmit() : form.submit(); });
        }, true);

        // AJAX submits (jQuery, loaded at the end of the page): a captcha error says which mode is on now; any other
        // answer used up the picture.
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery) return;
            jQuery(document).ajaxComplete(function (ev, xhr, opts) {
                var path = function (u) { try { return new URL(u, location.href).pathname; } catch (x) { return u; } };
                if (path(opts.url) !== path(form.action)) return;
                var j = xhr.responseJSON;
                if (j && j.data && j.data.captcha) {
                    if (j.code === 'captcha_number' || j.data.captcha.driver === 'number') number(); else apply(j.data.captcha);
                } else if (mode === 'number') {
                    number();
                }
            });
        });
        $('.shop-captcha-refresh').addEventListener('click', number);
        apply(cfg);
        @if($CaptchaError && $CaptchaConfig['driver'] === 'google' && $CaptchaConfig['fallback'])
            number(); // sent back after a failed Google check: the picture instead
        @endif
    })();
</script>
