import { useCallback, useEffect, useState } from 'react';

export type FieldErrors = Record<string, string>;

export class ApiError extends Error {
    constructor(
        message: string,
        readonly sessionExpired = false,
        /** Laravel validation errors, first message per field ("plans.0.name" → "…"). */
        readonly fields: FieldErrors = {},
    ) {
        super(message);
    }
}

// Refreshing would lose what is on the page, so ask for a sign-in in another tab; the next save picks up the new session.
const SESSION_EXPIRED = 'نشست شما تمام شده است. لطفاً در زبانه‌ای دیگر دوباره وارد شوید و سپس دوباره ذخیره کنید؛ تغییرات این صفحه از بین نمی‌رود.';
const OFFLINE = 'اتصال برقرار نشد. اینترنت را بررسی کنید و دوباره تلاش کنید.';
const HEADERS = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

async function readJson(res: Response) {
    // An expired admin session redirects to the login page instead of answering with JSON.
    const isJson = (res.headers.get('content-type') ?? '').includes('application/json');
    if (res.status === 419 || res.status === 401 || (!isJson && (res.ok || res.redirected))) {
        throw new ApiError(SESSION_EXPIRED, true);
    }
    const body = isJson ? await res.json() : null;
    if (!res.ok) {
        if (res.status === 403) throw new ApiError('دسترسی به این بخش را ندارید.');
        if (res.status === 422 && body?.errors) {
            const fields: FieldErrors = {};
            for (const [k, v] of Object.entries(body.errors as Record<string, string[]>)) fields[k] = v[0];
            throw new ApiError('چند مورد نیاز به اصلاح دارد.', false, fields);
        }
        const errors: unknown = body?.error ?? body?.message;
        const message = Array.isArray(errors) ? errors.join(' ') : typeof errors === 'string' ? errors : null;
        throw new ApiError(message ?? `خطای سرور (${res.status}). دوباره تلاش کنید.`);
    }
    return body;
}

/*
 * The CSRF token rendered into the page goes stale when the session behind it changes (signing in again in another
 * tab, or the session file expiring while the admin is still remembered), and every save then fails with 419 although
 * the admin is signed in. So a 419 fetches the current token once and retries; only if that fails is the session gone.
 */
let freshToken: string | null = null;

async function currentToken(): Promise<string | null> {
    try {
        const res = await fetch('/admin/api/csrf', { credentials: 'same-origin', headers: HEADERS });
        const isJson = (res.headers.get('content-type') ?? '').includes('application/json');
        if (!res.ok || !isJson) return null;
        const body = await res.json();
        return typeof body?.token === 'string' ? body.token : null;
    } catch {
        return null;
    }
}

function withToken(init: RequestInit, token: string): RequestInit {
    const headers = { ...(init.headers as Record<string, string>), 'X-CSRF-TOKEN': token };
    let body = init.body;
    if (body instanceof URLSearchParams && body.has('_token')) {
        body = new URLSearchParams(body);
        body.set('_token', token);
    }
    return { ...init, headers, body };
}

async function attempt(url: string, init: RequestInit) {
    try {
        return await fetch(url, { credentials: 'same-origin', ...init });
    } catch (e) {
        if ((e as Error).name === 'AbortError') throw e;
        throw new ApiError(OFFLINE);
    }
}

async function send(url: string, init: RequestInit) {
    const posting = !!init.method && init.method !== 'GET';
    if (posting && freshToken) init = withToken(init, freshToken);
    let res = await attempt(url, init);
    if (posting && res.status === 419) {
        const token = await currentToken();
        if (token) {
            freshToken = token;
            res = await attempt(url, withToken(init, token));
        }
    }
    return readJson(res);
}

export function getJson<T>(url: string, signal?: AbortSignal): Promise<T> {
    return send(url, { headers: HEADERS, signal });
}

/** Posts a form to an existing Laravel admin route; for AJAX those answer {success} or {error: [...]}. */
export async function postForm(url: string, csrfToken: string, data: Record<string, string | number>): Promise<string> {
    const body = new URLSearchParams({ _token: csrfToken });
    for (const [k, v] of Object.entries(data)) body.set(k, String(v));
    const json = await send(url, { method: 'POST', body, headers: { ...HEADERS, 'X-CSRF-TOKEN': csrfToken } });
    return typeof json?.success === 'string' ? json.success : 'انجام شد.';
}

/** Posts JSON to a React-admin API route (admin/api/*). */
export function postJson<T = { success?: string }>(url: string, csrfToken: string, data: unknown): Promise<T> {
    return send(url, {
        method: 'POST',
        body: JSON.stringify(data),
        headers: { ...HEADERS, 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    });
}

/** Uploads one file as multipart form data under the name "file". */
export function uploadFile<T = { path: string }>(url: string, csrfToken: string, file: File): Promise<T> {
    const body = new FormData();
    body.set('file', file);
    return send(url, { method: 'POST', body, headers: { ...HEADERS, 'X-CSRF-TOKEN': csrfToken } });
}

/** Loads JSON for a URL and keeps the previous result while a new URL loads, so tables don't flash. */
export function useQuery<T>(url: string | null) {
    const [data, setData] = useState<T | null>(null);
    const [error, setError] = useState<ApiError | null>(null);
    const [loading, setLoading] = useState(url !== null);
    const [version, setVersion] = useState(0);

    useEffect(() => {
        if (url === null) {
            setLoading(false);
            return;
        }
        const ctrl = new AbortController();
        setLoading(true);
        setError(null);
        getJson<T>(url, ctrl.signal)
            .then((d) => setData(d))
            .catch((e: Error) => {
                if (e.name !== 'AbortError') setError(e instanceof ApiError ? e : new ApiError(e.message));
            })
            .finally(() => {
                if (!ctrl.signal.aborted) setLoading(false);
            });
        return () => ctrl.abort();
    }, [url, version]);

    const reload = useCallback(() => setVersion((v) => v + 1), []);
    return { data, error, loading, reload };
}

/** Posts multipart form data (text fields plus files) to a React-admin API route. */
export function postFormData<T = { success?: string }>(url: string, csrfToken: string, body: FormData): Promise<T> {
    return send(url, { method: 'POST', body, headers: { ...HEADERS, 'X-CSRF-TOKEN': csrfToken } });
}
