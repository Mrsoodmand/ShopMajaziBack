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

const SESSION_EXPIRED = 'نشست شما تمام شده است. صفحه را تازه کنید و دوباره وارد شوید.';
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

async function send(url: string, init: RequestInit) {
    let res: Response;
    try {
        res = await fetch(url, { credentials: 'same-origin', ...init });
    } catch (e) {
        if ((e as Error).name === 'AbortError') throw e;
        throw new ApiError(OFFLINE);
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
