import { useEffect, useId, useRef, useState, type KeyboardEvent, type ReactNode } from 'react';
import { AlertTriangle, Check, ChevronLeft, ChevronRight, CircleCheck, Copy, RotateCw, Search, X } from 'lucide-react';
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

export type TabDef<T extends string> = { id: T; label: string; count?: number; highlight?: string };

/** Accessible tabs inside a card (arrow keys follow RTL reading order). */
export function TabbedPanel<T extends string>({
    label,
    tabs,
    initial,
    render,
}: {
    label: string;
    tabs: TabDef<T>[];
    initial?: T;
    render: (tab: T) => ReactNode;
}) {
    const [tab, setTab] = useState<T>(initial ?? tabs[0].id);
    const base = useId();
    const refs = useRef<Partial<Record<T, HTMLButtonElement | null>>>({});

    function onKeyDown(e: KeyboardEvent) {
        const i = tabs.findIndex((t) => t.id === tab);
        const step = e.key === 'ArrowLeft' ? 1 : e.key === 'ArrowRight' ? -1 : 0;
        if (!step) return;
        e.preventDefault();
        const next = tabs[(i + step + tabs.length) % tabs.length].id;
        setTab(next);
        refs.current[next]?.focus();
    }

    return (
        <section className="rounded-2xl border border-stroke bg-surface">
            <div role="tablist" aria-label={label} className="flex gap-1 overflow-x-auto border-b border-stroke px-3 pt-2" onKeyDown={onKeyDown}>
                {tabs.map((t) => {
                    const selected = t.id === tab;
                    return (
                        <button
                            key={t.id}
                            ref={(el) => {
                                refs.current[t.id] = el;
                            }}
                            type="button"
                            role="tab"
                            id={`${base}-${t.id}`}
                            aria-selected={selected}
                            aria-controls={`${base}-${t.id}-panel`}
                            tabIndex={selected ? 0 : -1}
                            onClick={() => setTab(t.id)}
                            className={`relative flex shrink-0 items-center gap-2 px-3 py-3 transition-colors ${
                                selected ? 'font-bold text-ink' : 'text-muted hover:text-ink'
                            }`}
                        >
                            {t.label}
                            {!!t.count && (
                                <span className={`num rounded-md px-1.5 text-[12px] ${t.highlight ? 'bg-indigo font-bold text-surface' : 'bg-surface-2 text-muted'}`}>
                                    {t.count}
                                    {t.highlight && <span className="sr-only"> {t.highlight}</span>}
                                </span>
                            )}
                            {selected && <span aria-hidden className="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-ink" />}
                        </button>
                    );
                })}
            </div>
            <div role="tabpanel" id={`${base}-${tab}-panel`} aria-labelledby={`${base}-${tab}`} className="p-5">
                {render(tab)}
            </div>
        </section>
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

export function Pager({ page, lastPage, total, noun, onPage }: { page: number; lastPage: number; total: number; noun: string; onPage: (p: number) => void }) {
    if (lastPage <= 1) return null;
    return (
        <nav aria-label="صفحه‌بندی" className="mt-4 flex items-center justify-between gap-3">
            <p className="num text-muted">
                صفحه {page} از {lastPage} · {count(total)} {noun}
            </p>
            <div className="flex gap-2">
                <button type="button" className="btn btn-secondary" disabled={page <= 1} onClick={() => onPage(page - 1)}>
                    <ChevronRight aria-hidden size={16} />
                    قبلی
                </button>
                <button type="button" className="btn btn-secondary" disabled={page >= lastPage} onClick={() => onPage(page + 1)}>
                    بعدی
                    <ChevronLeft aria-hidden size={16} />
                </button>
            </div>
        </nav>
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
