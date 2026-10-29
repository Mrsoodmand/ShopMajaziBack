import { useLayoutEffect, useRef, type KeyboardEvent, type ReactNode } from 'react';
import { useLocation, useSearchParams } from 'react-router';
import { count } from '../lib/format';

/*
 * Tabs that split one record's page (an order, a user, an editor) into its parts. The open tab is kept in the
 * address (?tab=…), so reloading, going back or sharing the link lands on the same part.
 */

export type PageTab<T extends string = string> = {
    id: T;
    label: string;
    /** A small number after the label (messages, rows). */
    count?: number;
    /** `attention`: something new waits here; `error`: fields in this tab failed to save. */
    tone?: 'attention' | 'error';
    /** Read to screen readers after the count ("پیام جدید", "خطا"). */
    note?: string;
};

export function useUrlTab<T extends string>(ids: readonly T[], fallback: T, key = 'tab'): [T, (tab: T) => void] {
    const [params, setParams] = useSearchParams();
    const location = useLocation();
    const raw = params.get(key) as T | null;
    const tab = raw && ids.includes(raw) ? raw : fallback;
    const set = (next: T) => {
        const p = new URLSearchParams(params);
        if (next === fallback) p.delete(key);
        else p.set(key, next);
        // keep the page's state (the list it was opened from) and don't jump to the top
        setParams(p, { replace: true, state: location.state, preventScrollReset: true });
    };
    return [tab, set];
}

export function PageTabs<T extends string>({
    id,
    label,
    tabs,
    value,
    onChange,
    className = '',
}: {
    id: string;
    label: string;
    tabs: PageTab<T>[];
    value: T;
    onChange: (tab: T) => void;
    className?: string;
}) {
    const list = useRef<HTMLDivElement>(null);
    const bar = useRef<HTMLSpanElement>(null);
    const placed = useRef(false);

    // The underline slides from the old tab to the new one (placed without sliding the first time).
    useLayoutEffect(() => {
        const el = list.current?.querySelector<HTMLElement>('[aria-selected="true"]');
        const b = bar.current;
        if (!el || !b) return;
        b.style.transition = placed.current ? '' : 'none';
        b.style.translate = `${el.offsetLeft}px 0`;
        b.style.width = `${el.offsetWidth}px`;
        placed.current = true;
        el.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }, [value, tabs.length]);

    function onKeyDown(e: KeyboardEvent) {
        const i = tabs.findIndex((t) => t.id === value);
        const step = e.key === 'ArrowLeft' ? 1 : e.key === 'ArrowRight' ? -1 : 0; // RTL: left is next
        const jump = e.key === 'Home' ? 0 : e.key === 'End' ? tabs.length - 1 : null;
        if (!step && jump === null) return;
        e.preventDefault();
        const next = tabs[jump ?? (i + step + tabs.length) % tabs.length].id;
        onChange(next);
        list.current?.querySelector<HTMLElement>(`#${CSS.escape(`${id}-${next}`)}`)?.focus();
    }

    return (
        <div className={`sticky top-14 z-[4] -mx-1 mb-5 bg-page/95 px-1 backdrop-blur ${className}`}>
            <div ref={list} role="tablist" aria-label={label} onKeyDown={onKeyDown} className="relative flex gap-1 overflow-x-auto border-b border-stroke [scrollbar-width:none]">
                {tabs.map((t) => {
                    const selected = t.id === value;
                    return (
                        <button
                            key={t.id}
                            id={`${id}-${t.id}`}
                            type="button"
                            role="tab"
                            aria-selected={selected}
                            aria-controls={`${id}-${t.id}-panel`}
                            tabIndex={selected ? 0 : -1}
                            onClick={() => onChange(t.id)}
                            className={`flex h-11 shrink-0 items-center gap-2 rounded-t-lg px-3.5 text-[14px] whitespace-nowrap transition-colors ${
                                selected ? 'font-bold text-ink' : 'text-muted hover:bg-surface-2/70 hover:text-ink'
                            }`}
                        >
                            {t.label}
                            {t.tone === 'error' ? (
                                <span className="num grid min-w-5 place-items-center rounded-md bg-error px-1 text-[12px] font-bold text-surface">
                                    {count(t.count ?? 1)}
                                    <span className="sr-only"> {t.note ?? 'خطا'}</span>
                                </span>
                            ) : t.count ? (
                                <span className={`num min-w-5 rounded-md px-1.5 text-center text-[12px] ${t.tone === 'attention' ? 'bg-indigo font-bold text-surface' : 'bg-surface-2 text-muted'}`}>
                                    {count(t.count)}
                                    {t.note && <span className="sr-only"> {t.note}</span>}
                                </span>
                            ) : null}
                        </button>
                    );
                })}
                <span
                    ref={bar}
                    aria-hidden
                    className="pointer-events-none absolute bottom-0 left-0 h-0.5 rounded-full bg-ink transition-[translate,width] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
                />
            </div>
        </div>
    );
}

export function TabPanel({ id, tab, children, className = '' }: { id: string; tab: string; children: ReactNode; className?: string }) {
    return (
        <div role="tabpanel" id={`${id}-${tab}-panel`} aria-labelledby={`${id}-${tab}`} tabIndex={-1} className={`outline-none ${className}`}>
            {children}
        </div>
    );
}

/** Page tabs whose parts each sit in one card; `render` draws the open part. */
export function TabbedSections<T extends string>({
    id,
    label,
    tabs,
    render,
}: {
    id: string;
    label: string;
    tabs: PageTab<T>[];
    render: (tab: T) => ReactNode;
}) {
    const [tab, setTab] = useUrlTab(
        tabs.map((t) => t.id),
        tabs[0].id,
    );
    const current = tabs.find((t) => t.id === tab) ?? tabs[0];
    return (
        <div className="min-w-0">
            <PageTabs id={id} label={label} tabs={tabs} value={tab} onChange={setTab} />
            <TabPanel id={id} tab={tab}>
                <section aria-label={current.label} className="rounded-2xl border border-stroke bg-surface p-5">
                    {render(tab)}
                </section>
            </TabPanel>
        </div>
    );
}

/** How many field errors belong to each tab: a tab owns the error keys that start with one of its field names. */
export function errorsByTab(errors: Record<string, string> | undefined, fields: Record<string, string[] | undefined>): Record<string, number> {
    const out: Record<string, number> = {};
    for (const key of Object.keys(errors ?? {})) {
        const root = key.split('.')[0];
        for (const [tab, names] of Object.entries(fields)) {
            if (names?.includes(root)) {
                out[tab] = (out[tab] ?? 0) + 1;
                break;
            }
        }
    }
    return out;
}
