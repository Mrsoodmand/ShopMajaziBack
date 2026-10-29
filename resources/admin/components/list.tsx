import { useEffect, useRef, useState, type ReactNode } from 'react';
import { useSearchParams } from 'react-router';
import { AlertTriangle, SearchX } from 'lucide-react';
import { RowsEditor, Field } from './form';
import { count } from '../lib/format';

/** Reads and updates list filters kept in the address (?filter=…&q=…&page=…); changing a filter goes back to page 1. */
export function useListParams<K extends string>(defaults: Record<K, string>) {
    const [params, setParams] = useSearchParams();
    const values = Object.fromEntries(Object.entries(defaults).map(([k, d]) => [k, params.get(k) ?? (d as string)])) as Record<K, string>;
    const page = Number(params.get('page') ?? 1) || 1;

    function update(next: Partial<Record<K | 'page', string>>) {
        const merged = new URLSearchParams(params);
        for (const [k, v] of Object.entries(next) as [string, string | undefined][]) {
            if (v && v !== (defaults as Record<string, string>)[k]) merged.set(k, v);
            else merged.delete(k);
        }
        if (!('page' in next)) merged.delete('page');
        setParams(merged);
    }

    /** Query string for the API: every filter plus the page. */
    const query = new URLSearchParams({ ...values, page: String(page) }).toString();
    return { values, page, update, query, reset: () => setParams(new URLSearchParams()), here: `?${params}` };
}

export function PageHeader({ title, subtitle, actions }: { title: string; subtitle?: ReactNode; actions?: ReactNode }) {
    return (
        <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div className="min-w-0">
                <h1 className="text-[22px] leading-tight font-bold">{title}</h1>
                {subtitle && <p className="mt-1 text-muted">{subtitle}</p>}
            </div>
            {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
        </div>
    );
}

export function EmptyState({ children, action }: { children: ReactNode; action?: ReactNode }) {
    return (
        <div className="flex flex-col items-center gap-2 px-6 py-16 text-center">
            <SearchX aria-hidden size={28} className="text-muted" />
            <p className="font-medium">{children}</p>
            {action && <div className="mt-2">{action}</div>}
        </div>
    );
}

/** A bar above a list asking for attention (orders waiting for stock and the like). */
export function AttentionBar({ children, action }: { children: ReactNode; action?: ReactNode }) {
    return (
        <div role="status" className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-warn-soft px-4 py-3 text-warn">
            <p className="flex items-start gap-2 font-medium">
                <AlertTriangle aria-hidden size={18} className="mt-0.5 shrink-0" />
                <span>{children}</span>
            </p>
            {action}
        </div>
    );
}

/** A destructive action that asks once more in place before it runs. */
export function ConfirmButton({
    label,
    confirmLabel,
    question,
    onConfirm,
    busy,
    icon,
    className = 'btn btn-secondary',
}: {
    label: string;
    confirmLabel: string;
    question: string;
    onConfirm: () => void;
    busy?: boolean;
    icon?: ReactNode;
    className?: string;
}) {
    const [asking, setAsking] = useState(false);
    const yes = useRef<HTMLButtonElement>(null);
    useEffect(() => {
        if (asking) yes.current?.focus();
    }, [asking]);
    if (!asking) {
        return (
            <button type="button" className={className} onClick={() => setAsking(true)} disabled={busy}>
                {icon}
                {label}
            </button>
        );
    }
    return (
        <div role="alertdialog" aria-label={question} className="flex flex-wrap items-center gap-2 rounded-xl bg-error-soft px-3 py-2">
            <span className="text-[13px] font-bold text-error">{question}</span>
            <button ref={yes} type="button" className="btn btn-danger min-h-8" disabled={busy} onClick={() => { onConfirm(); setAsking(false); }}>
                {busy ? 'لطفاً صبر کنید…' : confirmLabel}
            </button>
            <button type="button" className="btn btn-ghost min-h-8" onClick={() => setAsking(false)}>
                انصراف
            </button>
        </div>
    );
}

export type FaqRow = { q: string; a: string };

export function FaqEditor({ rows, onChange, errors }: { rows: FaqRow[]; onChange: (rows: FaqRow[]) => void; errors: Record<string, string> }) {
    return (
        <RowsEditor
            rows={rows}
            onChange={onChange}
            make={() => ({ q: '', a: '' })}
            addLabel="افزودن سؤال"
            empty="سؤالی ثبت نشده است."
            removeLabel={(_, i) => `حذف سؤال ${i + 1}`}
            render={(row, update, i) => (
                <div className="grid gap-3">
                    <Field label="سؤال" error={errors[`faq.${i}.q`]}>
                        {(p) => <input {...p} className="field" value={row.q} onChange={(e) => update({ q: e.target.value })} />}
                    </Field>
                    <Field label="پاسخ" error={errors[`faq.${i}.a`]}>
                        {(p) => <textarea {...p} rows={3} className="field" value={row.a} onChange={(e) => update({ a: e.target.value })} />}
                    </Field>
                </div>
            )}
        />
    );
}

/** A small count pill. */
export function CountPill({ n, tone = 'neutral' }: { n: number; tone?: 'neutral' | 'warn' | 'success' }) {
    const cls = tone === 'warn' ? 'bg-warn-soft text-warn' : tone === 'success' ? 'bg-emerald-500/12 text-emerald-800 dark:text-emerald-300' : 'bg-surface-2 text-muted';
    return <span className={`num inline-block min-w-6 rounded-md px-1.5 text-center text-[12px] font-medium ${cls}`}>{count(n)}</span>;
}

/** Turns API field errors into the editor's problem list, naming rows ("plans.1.price" → "پلن‌ها، ردیف ۲"). */
export function problemsFrom(errors: Record<string, string>, labels: Record<string, string>, failure: string | null) {
    const label = (key: string) => {
        const [root, index, field] = key.split('.');
        const base = labels[root] ?? root;
        if (index !== undefined && /^\d+$/.test(index)) return `${base}، ردیف ${Number(index) + 1}${field && labels[`${root}.${field}`] ? ` (${labels[`${root}.${field}`]})` : ''}`;
        return base;
    };
    return [...Object.entries(errors).map(([k, message]) => ({ label: label(k), message })), ...(failure ? [{ label: 'ذخیره', message: failure }] : [])];
}

export function EditorSkeleton() {
    return (
        <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
            <div className="skeleton mb-6 h-7 w-64" />
            <div className="skeleton h-96 rounded-2xl" />
        </div>
    );
}
