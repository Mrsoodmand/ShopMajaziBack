/*
 * Persian / Arabic digits -> English digits in form inputs.
 * Typing or pasting ۰-۹ or ٠-٩ (and the Persian decimal ٫) into any <input> is
 * turned into 0-9 / "." on the fly, so number fields, validation and the server
 * all see plain digits. Works with plain forms, jQuery and React (value is fixed
 * before React reads it).
 *
 * Skipped: password/file/checkbox/... inputs, <textarea>s (free text), and any
 * field inside [data-fa-digits="keep"]. A textarea opts in with data-fa-digits="en".
 * Keep in sync with Front/src/lib/persian-digits.ts.
 */
(function () {
    if (window.__faDigits) return;
    window.__faDigits = true;

    var ANY = /[۰-۹٠-٩٫]/;
    var ALL = /[۰-۹٠-٩٫]/g;
    var SKIP = { password: 1, file: 1, checkbox: 1, radio: 1, hidden: 1, range: 1, color: 1, button: 1, submit: 1, reset: 1, image: 1 };

    function toEn(s) {
        return s.replace(ALL, function (c) {
            var n = c.charCodeAt(0);
            if (n === 0x066B) return '.';
            return String.fromCharCode(48 + n - (n >= 0x06F0 ? 0x06F0 : 0x0660));
        });
    }

    function field(el) {
        if (!el || !el.tagName) return null;
        var tag = el.tagName;
        if (tag !== 'INPUT' && tag !== 'TEXTAREA') return null;
        if (el.readOnly || el.disabled) return null;
        var mode = el.closest && el.closest('[data-fa-digits]');
        mode = mode ? mode.getAttribute('data-fa-digits') : '';
        if (mode === 'keep') return null;
        if (tag === 'TEXTAREA') return mode === 'en' ? el : null;
        return SKIP[(el.type || 'text').toLowerCase()] ? null : el;
    }

    function insert(el, text) {
        // execCommand keeps the caret, undo history and fires a real input event.
        var ok = false;
        try { ok = document.execCommand('insertText', false, text); } catch (e) {}
        if (ok) return;
        try {
            el.setRangeText(text, el.selectionStart, el.selectionEnd, 'end');
        } catch (e) {
            el.value = el.value + text; // number/email inputs have no selection API
        }
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Keyboard typing: swap the character before the browser inserts it
    // (needed for type="number", which would otherwise reject it).
    document.addEventListener('beforeinput', function (e) {
        if (e.isComposing || !e.data || !ANY.test(e.data)) return;
        if (e.inputType && e.inputType.indexOf('insert') !== 0) return;
        var el = field(e.target);
        if (!el) return;
        e.preventDefault();
        insert(el, toEn(e.data));
    }, true);

    document.addEventListener('paste', function (e) {
        var el = field(e.target);
        if (!el || !e.clipboardData) return;
        var text = e.clipboardData.getData('text/plain');
        if (!text || !ANY.test(text)) return;
        e.preventDefault();
        insert(el, toEn(text));
    }, true);

    // Fallback (IME / mobile keyboards, autofill, drag & drop): fix the value in
    // the capture phase, before page handlers (React included) read it.
    var setInput = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
    var setArea = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set;
    function fix(el) {
        if (!ANY.test(el.value)) return false;
        var start, end;
        try { start = el.selectionStart; end = el.selectionEnd; } catch (e) {}
        (el.tagName === 'TEXTAREA' ? setArea : setInput).call(el, toEn(el.value));
        try { if (start != null) el.setSelectionRange(start, end); } catch (e) {}
        return true;
    }
    document.addEventListener('input', function (e) {
        if (e.isComposing) return;
        var el = field(e.target);
        if (el) fix(el);
    }, true);
    document.addEventListener('compositionend', function (e) {
        var el = field(e.target);
        if (el && fix(el)) el.dispatchEvent(new Event('input', { bubbles: true }));
    }, true);
    document.addEventListener('change', function (e) {
        var el = field(e.target);
        if (el && fix(el)) el.dispatchEvent(new Event('input', { bubbles: true }));
    }, true);
})();
