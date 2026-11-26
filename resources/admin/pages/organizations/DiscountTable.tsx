import { useId } from 'react';

/*
 * A company's percent per product family (users.org_discounts): one row per family, empty = no discount on it.
 * Used when approving a request, on an approved company, and for the defaults in settings.
 */

export type Family = { key: string; label: string };
export type Discounts = Record<string, number>;
/** What the inputs hold while typing (Persian digits and "12.5" allowed). */
export type DiscountDraft = Record<string, string>;

const FA: Record<string, string> = { '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9', '٫': '.', '،': '.' };

export const toDraft = (d: Discounts | null | undefined): DiscountDraft => Object.fromEntries(Object.entries(d ?? {}).map(([k, v]) => [k, String(v)]));

/** The draft as numbers for the API; empty or 0 rows are left out. */
export function fromDraft(d: DiscountDraft): Discounts {
    const out: Discounts = {};
    for (const [k, raw] of Object.entries(d)) {
        const n = Number(raw.replace(/[۰-۹٫،]/g, (c) => FA[c]).trim());
        if (raw.trim() !== '' && !Number.isNaN(n) && n > 0) out[k] = n;
    }
    return out;
}

/** "اشتراک‌ها ۱۰٪ · وریفای ۵٪" — a short summary for lists. */
export function discountSummary(d: Discounts, families: Family[]): string {
    const parts = families.filter((f) => d[f.key]).map((f) => `${f.label} ${d[f.key].toLocaleString('fa-IR')}٪`);
    return parts.length ? parts.join(' · ') : 'بدون تخفیف';
}

/** "12.5" / "۱۲٫۵" → 12.5; empty → null. */
export function vatNumber(raw: string): number | null {
    const t = raw.replace(/[۰-۹٫،]/g, (c) => FA[c]).trim();
    if (t === '') return null;
    const n = Number(t);
    return Number.isNaN(n) ? null : n;
}

/** A company's (or the default) VAT percent for official invoices. */
export function VatInput({ label, hint, value, onChange, error }: { label: string; hint?: string; value: string; onChange: (v: string) => void; error?: string }) {
    const id = useId();
    return (
        <div>
            <label htmlFor={id} className="mb-1 block font-medium">{label}</label>
            <input id={id} inputMode="decimal" dir="ltr" placeholder="—" aria-invalid={error ? true : undefined} aria-describedby={hint ? `${id}-h` : undefined}
                className={`field num h-9 max-w-28 text-end ${error ? 'border-error' : ''}`} value={value} onChange={(e) => onChange(e.target.value)} />
            {error && <span className="mt-1 block text-[12px] font-bold text-error">{error}</span>}
            {hint && <p id={`${id}-h`} className="mt-1 text-[13px] text-muted">{hint}</p>}
        </div>
    );
}

export function DiscountTable({ families, value, onChange, errors = {}, max }: { families: Family[]; value: DiscountDraft; onChange: (d: DiscountDraft) => void; errors?: Record<string, string>; max: number }) {
    const id = useId();
    return (
        <div>
            <table className="w-full text-[14px]">
                <caption className="sr-only">درصد تخفیف برای هر محصول</caption>
                <thead>
                    <tr className="text-[13px] text-muted">
                        <th scope="col" className="pb-1.5 text-start font-medium">محصول</th>
                        <th scope="col" className="w-28 pb-1.5 text-end font-medium">تخفیف (٪)</th>
                    </tr>
                </thead>
                <tbody>
                    {families.map((f) => {
                        const err = errors[f.key];
                        const on = (value[f.key] ?? '').trim() !== '' && Number(fromDraft({ x: value[f.key] }).x ?? 0) > 0;
                        return (
                            <tr key={f.key} className="border-t border-stroke">
                                <th scope="row" className={`py-2 text-start font-medium ${on ? '' : 'text-muted'}`}>
                                    <label htmlFor={`${id}-${f.key}`}>{f.label}</label>
                                    {err && <span className="block text-[12px] font-bold text-error">{err}</span>}
                                </th>
                                <td className="py-1.5">
                                    <input
                                        id={`${id}-${f.key}`}
                                        inputMode="decimal"
                                        dir="ltr"
                                        placeholder="—"
                                        aria-invalid={err ? true : undefined}
                                        className={`field num h-9 text-end ${err ? 'border-error' : ''}`}
                                        value={value[f.key] ?? ''}
                                        onChange={(e) => onChange({ ...value, [f.key]: e.target.value })}
                                    />
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
            <p className="mt-2 text-[13px] text-muted">خالی یا صفر یعنی این محصول برای این شرکت تخفیف ندارد. حداکثر {max.toLocaleString('fa-IR')}٪. شماره مجازی تخفیف نمی‌گیرد؛ پیش‌سفارش گوگل ویس هم با قیمت عادی است.</p>
        </div>
    );
}
