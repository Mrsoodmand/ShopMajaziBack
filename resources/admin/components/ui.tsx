import { useEffect, useId, useState, type ReactNode } from 'react';
import { AlertTriangle, Check, CircleCheck, Copy, RotateCw, Search, X } from 'lucide-react';
import type { ApiError } from '../lib/api';
import { count } from '../lib/format';

/** A bordered white card with a heading; the admin's one container. */
export function Panel({ title, children, actions }: { title: string; children: ReactNode; actions?: ReactNode }) {
    const id = useId();
    return (
        <section aria-labelledby={id} className="rounded-2xl border border-stroke bg-surface">
            <div className="flex items-center justify-between gap-3 px-5 pt-4 pb-1">
                <h2 id={id} className="text-[16px] font-bold">
                    {title}
                </h2>
                {actions}
            </div>
            <div className="px-5 pt-2 pb-5">{children}</div>
        </section>
    );
}

/** One label/value line. `ltr` is for numbers, codes and emails; `num` only aligns figures (money stays RTL so «تومان» follows the number). */
export function Row({ label, children, ltr, num }: { label: string; children: ReactNode; ltr?: boolean; num?: boolean }) {
    return (
        <div className="flex items-baseline justify-between gap-4 border-b border-stroke py-2.5 last:border-0">
            <dt className="shrink-0 text-muted">{label}</dt>
            <dd className={`m-0 min-w-0 text-end font-medium break-words ${ltr || num ? 'num' : ''}`} dir={ltr ? 'ltr' : undefined}>
                {children}
            </dd>
        </div>
    );
}

export function CopyButton({ value, label }: { value: string; label: string }) {
    const [copied, setCopied] = useState(false);
    useEffect(() => {
        if (!copied) return;
        const t = setTimeout(() => setCopied(false), 1500);
        return () => clearTimeout(t);
    }, [copied]);
    return (
        <button
            type="button"
            onClick={() => navigator.clipboard?.writeText(value).then(() => setCopied(true))}
            className="ms-1.5 inline-grid size-7 place-items-center rounded-md align-middle text-muted hover:bg-surface-2 hover:text-ink"
            aria-label={copied ? 'کپی شد' : `کپی ${label}`}
            title={copied ? 'کپی شد' : 'کپی'}
        >
            {copied ? <Check aria-hidden size={14} className="text-primary" /> : <Copy aria-hidden size={14} />}
        </button>
    );
}

export function FieldError({ id, children }: { id: string; children: ReactNode }) {
    return (
        <p id={id} className="mt-1.5 flex items-start gap-1.5 text-[13px] font-bold text-error">
            <X aria-hidden size={15} className="mt-0.5 shrink-0" />
            <span>
                <span className="sr-only">خطا: </span>
                {children}
            </span>
        </p>
    );
}

export function Notice({ children }: { children: ReactNode }) {
    return (
        <p role="status" className="mt-3 flex items-start gap-2 rounded-xl bg-emerald-500/10 px-3 py-2.5 text-[13px] font-medium text-emerald-800 dark:text-emerald-300">
            <CircleCheck aria-hidden size={16} className="mt-0.5 shrink-0" />
            {children}
        </p>
    );
}

/** Failed load: the message, plus a retry unless the session ended. */
export function ErrorState({ error, onRetry, boxed }: { error: ApiError; onRetry: () => void; boxed?: boolean }) {
    return (
        <div className={`flex flex-col items-center gap-3 px-6 py-16 text-center ${boxed ? 'rounded-2xl border border-stroke bg-surface' : ''}`}>
            <AlertTriangle aria-hidden size={28} className="text-error" />
            <p className="font-medium">{error.message}</p>
            {!error.sessionExpired && (
                <button type="button" className="btn btn-secondary" onClick={onRetry}>
                    <RotateCw aria-hidden size={16} />
                    تلاش دوباره
                </button>
            )}
        </div>
    );
}

/** Filter buttons with counts above a list. */
export function FilterTabs<T extends string>({
    label,
    items,
    value,
    counts,
    attention,
    onChange,
}: {
    label: string;
    items: { id: T; label: string }[];
    value: T;
    counts?: Partial<Record<T, number>>;
    attention?: T;
    onChange: (id: T) => void;
}) {
    return (
        <nav aria-label={label} className="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1 xl:pb-0">
            {items.map((v) => {
                const active = v.id === value;
                const n = counts?.[v.id];
                return (
                    <button
                        key={v.id}
                        type="button"
                        aria-pressed={active}
                        onClick={() => onChange(v.id)}
                        className={`flex h-9 shrink-0 items-center gap-2 rounded-lg px-3 text-[14px] transition-colors ${
                            active ? 'bg-ink font-medium text-surface' : 'text-body hover:bg-surface-2'
                        }`}
                    >
                        {v.label}
                        <span
                            className={`num min-w-6 rounded-md px-1.5 text-center text-[12px] ${
                                active ? 'bg-surface/20' : v.id === attention && n ? 'bg-indigo-soft font-bold text-indigo' : 'bg-surface-2 text-muted'
                            }`}
                        >
                            {n == null ? '…' : count(n)}
                        </span>
                    </button>
                );
            })}
        </nav>
    );
}

/** Search box that reports its value after a short pause. */
export function SearchField({ value, onSearch, label, placeholder }: { value: string; onSearch: (q: string) => void; label: string; placeholder: string }) {
    const [draft, setDraft] = useState(value);
    const id = useId();
    useEffect(() => setDraft(value), [value]);
    useEffect(() => {
        if (draft.trim() === value) return;
        const t = setTimeout(() => onSearch(draft.trim()), 350);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [draft]);
    return (
        <div className="relative flex-1 xl:max-w-[320px]">
            <label htmlFor={id} className="sr-only">
                {label}
            </label>
            <Search aria-hidden size={16} className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-muted" />
            <input id={id} type="search" value={draft} onChange={(e) => setDraft(e.target.value)} placeholder={placeholder} className="field ps-9" />
        </div>
    );
}

export function SkeletonRows({ rows = 8 }: { rows?: number }) {
    return (
        <div className="divide-y divide-stroke" aria-label="در حال بارگذاری">
            {Array.from({ length: rows }, (_, i) => (
                <div key={i} className="flex items-center gap-6 px-5 py-4">
                    <div className="skeleton h-4 w-12" />
                    <div className="skeleton h-4 w-32" />
                    <div className="skeleton h-4 flex-1" />
                    <div className="skeleton h-4 w-20" />
                    <div className="skeleton h-5 w-24 rounded-full" />
                </div>
            ))}
        </div>
    );
}

export function gateLabel(gate: string | null) {
    if (!gate) return '—';
    return gate === 'Wallet' ? 'کیف پول' : gate === 'Bank' ? 'درگاه بانک' : gate;
}
