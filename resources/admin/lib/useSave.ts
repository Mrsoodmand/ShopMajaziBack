import { useState } from 'react';
import { ApiError, postJson } from './api';
import { useBoot } from './boot';

/**
 * Saving state for an editor: posts JSON, keeps Laravel's field errors, a general failure and the success notice.
 * `run` resolves with the server's answer, or null when saving failed.
 */
export function useSave() {
    const { csrfToken } = useBoot();
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);
    const [failure, setFailure] = useState<string | null>(null);

    async function run<T extends { success?: string }>(url: string, payload: unknown, request?: () => Promise<T>): Promise<T | null> {
        setSaving(true);
        setSaved(null);
        setFailure(null);
        try {
            const res = request ? await request() : await postJson<T>(url, csrfToken, payload);
            setErrors({});
            setSaved(res.success ?? 'ذخیره شد.');
            return res;
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
            return null;
        } finally {
            setSaving(false);
        }
    }

    return { errors, saving, saved, failure, run, clearSaved: () => setSaved(null), csrfToken };
}
