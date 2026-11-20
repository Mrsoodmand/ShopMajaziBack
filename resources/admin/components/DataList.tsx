import { Fragment, useEffect, useId, useRef, useState, type CSSProperties, type KeyboardEvent, type MouseEvent, type ReactNode } from 'react';
import { createPortal } from 'react-dom';
import { Link, useHref } from 'react-router';
import { ArrowUpLeft, Check, ChevronDown, ChevronLeft, ChevronRight, Copy, ExternalLink, Eye, Link2, ListFilter, MoreVertical, RotateCw, X, type LucideIcon } from 'lucide-react';
import type { ApiError } from '../lib/api';
import { count, parseAmount } from '../lib/format';
import { matchPreset, normalizeJalali, presetRange, presets, type RangePreset } from '../lib/dates';
import { ErrorState, SearchField, SkeletonRows } from './ui';
import { toneRail, type Tone } from './Status';

/*
 * The admin's one list kit. A list page is: a toolbar (status tabs, search, filters, sort), active filter chips,
 * a spreadsheet-style table whose rows are real links (a click opens the quick view, a ⋯ menu holds the rest),
 * a side drawer for the quick view, a floating bar for ticked rows, and a pager. Filters live in the address, so a filtered list can be shared.
 */

/* ---------- Filters ---------- */

export type FilterOption = { value: string; label: string };

export type FilterDef =
    | { type: 'select'; key: string; label: string; options: FilterOption[]; any?: string }
    | { type: 'toggle'; key: string; label: string; hint?: string }
    | { type: 'dates'; label: string; from?: string; to?: string }
    | { type: 'range'; label: string; min?: string; max?: string; unit?: string };

export type SortOption = { value: string; label: string };

/** Every address key the filters use, each defaulting to empty (for useListParams). */
export function filterDefaults(defs: FilterDef[]): Record<string, string> {
    const keys: Record<string, string> = {};
    for (const d of defs) {
        if (d.type === 'dates') {
            keys[d.from ?? 'from'] = '';
            keys[d.to ?? 'to'] = '';
        } else if (d.type === 'range') {
            keys[d.min ?? 'min'] = '';
            keys[d.max ?? 'max'] = '';
        } else keys[d.key] = '';
    }
    return keys;
}

type Chip = { id: string; text: string; clear: Record<string, string> };

function chipsFor(defs: FilterDef[], values: Record<string, string>): Chip[] {
    const chips: Chip[] = [];
    for (const d of defs) {
        if (d.type === 'select') {
            const v = values[d.key];
            if (!v) continue;
            chips.push({ id: d.key, text: `${d.label}: ${d.options.find((o) => o.value === v)?.label ?? v}`, clear: { [d.key]: '' } });
        } else if (d.type === 'toggle') {
            if (values[d.key]) chips.push({ id: d.key, text: d.label, clear: { [d.key]: '' } });
        } else if (d.type === 'dates') {
            const fk = d.from ?? 'from';
            const tk = d.to ?? 'to';
            const from = values[fk];
            const to = values[tk];
            if (!from && !to) continue;
            const preset = matchPreset(from, to);
            const text = preset !== 'custom' ? presets.find((p) => p.id === preset)!.label : from && to ? `${from} تا ${to}` : from ? `از ${from}` : `تا ${to}`;
            chips.push({ id: fk, text: `${d.label}: ${text}`, clear: { [fk]: '', [tk]: '' } });
        } else {
            const mk = d.min ?? 'min';
            const xk = d.max ?? 'max';
            const min = values[mk];
            const max = values[xk];
            if (!min && !max) continue;
            const fmt = (v: string) => `${count(Number(v))}${d.unit ? ` ${d.unit}` : ''}`;
            const text = min && max ? `${fmt(min)} تا ${fmt(max)}` : min ? `از ${fmt(min)}` : `تا ${fmt(max)}`;
            chips.push({ id: mk, text: `${d.label}: ${text}`, clear: { [mk]: '', [xk]: '' } });
        }
    }
    return chips;
}

/**
 * Toolbar above a list. `views` (FilterTabs) sit on top; under them search, the «فیلترها» button, sort and the result
 * count. The filter panel slides open below; active filters show as chips that remove themselves.
 */
export function FilterBar({
    views,
    search,
    filters = [],
    sort,
    values,
    onChange,
    total,
    noun,
    loading,
    onRefresh,
    actions,
}: {
    views?: ReactNode;
    search?: { label: string; placeholder: string; key?: string };
    filters?: FilterDef[];
    sort?: { options: SortOption[]; key?: string; label?: string };
    values: Record<string, string>;
    onChange: (patch: Record<string, string>) => void;
    total?: number | null;
    noun?: string;
    loading?: boolean;
    onRefresh?: () => void;
    actions?: ReactNode;
}) {
    const chips = chipsFor(filters, values);
    const [open, setOpen] = useState(false);
    const panelId = useId();
    const sortId = useId();
    const searchKey = search?.key ?? 'q';
    const sortKey = sort?.key ?? 'sort';
    const clearAll = () => onChange(Object.assign({ [searchKey]: '' }, ...chips.map((c) => c.clear)));

    return (
        <div className="mb-4 space-y-3">
            {views}
            <div className="flex flex-wrap items-center gap-2">
                {search && (
                    <div className="flex min-w-[220px] flex-1 sm:max-w-[360px]">
                        <SearchField value={values[searchKey] ?? ''} onSearch={(q) => onChange({ [searchKey]: q })} label={search.label} placeholder={search.placeholder} />
                    </div>
                )}
                {filters.length > 0 && (
                    <button
                        type="button"
                        className={`btn ${open || chips.length ? 'btn-secondary border-ink/40' : 'btn-secondary'}`}
                        aria-expanded={open}
                        aria-controls={panelId}
                        onClick={() => setOpen(!open)}
                    >
                        <ListFilter aria-hidden size={16} />
                        فیلترها
                        {chips.length > 0 && <span className="num grid min-w-5 place-items-center rounded-md bg-primary px-1 text-[12px] text-primary-ink">{chips.length}</span>}
                        <ChevronDown aria-hidden size={15} className={`text-muted transition-transform duration-200 ${open ? 'rotate-180' : ''}`} />
                    </button>
                )}
                {sort && (
                    <>
                        <label htmlFor={sortId} className="sr-only">
                            {sort.label ?? 'مرتب‌سازی'}
                        </label>
                        <select id={sortId} className="field w-auto min-w-[150px]" value={values[sortKey] ?? ''} onChange={(e) => onChange({ [sortKey]: e.target.value })}>
                            {sort.options.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
                    </>
                )}
                {actions}
                <div className="ms-auto flex items-center gap-1 text-[13px] text-muted">
                    {total != null && (
                        <span className="num whitespace-nowrap" aria-live="polite">
                            {count(total)} {noun}
                        </span>
                    )}
                    {onRefresh && (
                        <button type="button" className="btn btn-ghost min-h-9 px-2" onClick={onRefresh} aria-label="به‌روزرسانی فهرست" title="به‌روزرسانی">
                            <RotateCw aria-hidden size={16} className={loading ? 'animate-spin' : ''} />
                        </button>
                    )}
                </div>
            </div>

            {filters.length > 0 && (
                <div
                    id={panelId}
                    inert={!open}
                    className={`grid transition-[grid-template-rows] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] ${open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'}`}
                >
                    <div className="min-h-0 overflow-hidden">
                        <div className={`rounded-xl border border-stroke bg-surface shadow-card p-4 transition-opacity duration-200 ${open ? 'opacity-100' : 'opacity-0'}`}>
                            <div className="grid gap-x-4 gap-y-3 sm:grid-cols-2 xl:grid-cols-4">
                                {filters.map((d) => (
                                    <FilterControl key={d.type === 'dates' ? (d.from ?? 'from') : d.type === 'range' ? (d.min ?? 'min') : d.key} def={d} values={values} onChange={onChange} />
                                ))}
                            </div>
                            <div className="mt-4 flex items-center justify-between gap-3 border-t border-stroke pt-3">
                                <p className="text-[13px] text-muted">هر تغییر همان لحظه روی فهرست اعمال می‌شود.</p>
                                <button type="button" className="btn btn-ghost min-h-9" onClick={clearAll} disabled={!chips.length}>
                                    پاک کردن فیلترها
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {(chips.length > 0 || values[searchKey]) && (
                <ul aria-label="فیلترهای فعال" className="flex flex-wrap items-center gap-2">
                    {values[searchKey] && <FilterChip text={`جستجو: «${values[searchKey]}»`} onClear={() => onChange({ [searchKey]: '' })} />}
                    {chips.map((c) => (
                        <FilterChip key={c.id} text={c.text} onClear={() => onChange(c.clear)} />
                    ))}
                    {chips.length + (values[searchKey] ? 1 : 0) > 1 && (
                        <li>
                            <button type="button" className="px-2 text-[13px] font-medium text-muted underline-offset-4 hover:text-ink hover:underline" onClick={clearAll}>
                                پاک کردن همه
                            </button>
                        </li>
                    )}
                </ul>
            )}
        </div>
    );
}

function FilterChip({ text, onClear }: { text: string; onClear: () => void }) {
    return (
        <li className="flex items-center gap-1 rounded-full border border-stroke bg-surface py-0.5 ps-3 pe-1 text-[13px]">
            <span className="num">{text}</span>
            <button type="button" onClick={onClear} className="grid size-6 place-items-center rounded-full text-muted hover:bg-surface-2 hover:text-ink" aria-label={`برداشتن فیلتر ${text}`}>
                <X aria-hidden size={14} />
            </button>
        </li>
    );
}

function FilterControl({ def, values, onChange }: { def: FilterDef; values: Record<string, string>; onChange: (patch: Record<string, string>) => void }) {
    const id = useId();
    if (def.type === 'select') {
        return (
            <div>
                <label htmlFor={id} className="mb-1 block text-[13px] font-medium text-body">
                    {def.label}
                </label>
                <select id={id} className="field" value={values[def.key] ?? ''} onChange={(e) => onChange({ [def.key]: e.target.value })}>
                    <option value="">{def.any ?? 'همه'}</option>
                    {def.options.map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </select>
            </div>
        );
    }
    if (def.type === 'toggle') {
        const on = !!values[def.key];
        return (
            <label className="flex cursor-pointer items-start gap-3 self-end rounded-xl border border-stroke px-3 py-2.5 hover:bg-surface-2">
                <input type="checkbox" className="mt-1 size-4 accent-[var(--primary)]" checked={on} onChange={(e) => onChange({ [def.key]: e.target.checked ? '1' : '' })} />
                <span>
                    <span className="block font-medium">{def.label}</span>
                    {def.hint && <span className="block text-[12px] text-muted">{def.hint}</span>}
                </span>
            </label>
        );
    }
    if (def.type === 'dates') return <DatesControl def={def} values={values} onChange={onChange} />;
    return <RangeControl def={def} values={values} onChange={onChange} />;
}

function DatesControl({ def, values, onChange }: { def: Extract<FilterDef, { type: 'dates' }>; values: Record<string, string>; onChange: (patch: Record<string, string>) => void }) {
    const fk = def.from ?? 'from';
    const tk = def.to ?? 'to';
    const from = values[fk] ?? '';
    const to = values[tk] ?? '';
    const [preset, setPreset] = useState<RangePreset>(() => matchPreset(from, to));
    const [draft, setDraft] = useState({ from, to });
    const [error, setError] = useState<string | null>(null);
    const id = useId();

    useEffect(() => {
        setDraft({ from, to });
        if (!from && !to) setPreset((p) => (p === 'custom' ? p : 'any'));
        else if (matchPreset(from, to) !== 'custom') setPreset(matchPreset(from, to));
    }, [from, to]);

    function apply(next = draft) {
        const f = next.from.trim() ? normalizeJalali(next.from) : '';
        const t = next.to.trim() ? normalizeJalali(next.to) : '';
        if (f === null || t === null) {
            setError('تاریخ را به شکل ۱۴۰۴/۰۸/۰۷ بنویسید.');
            return;
        }
        setError(null);
        if (f !== from || t !== to) onChange({ [fk]: f, [tk]: t });
    }

    return (
        <div className="sm:col-span-2 xl:col-span-1">
            <label htmlFor={id} className="mb-1 block text-[13px] font-medium text-body">
                {def.label}
            </label>
            <select
                id={id}
                className="field"
                value={preset}
                onChange={(e) => {
                    const p = e.target.value as RangePreset;
                    setPreset(p);
                    setError(null);
                    if (p !== 'custom') onChange({ [fk]: presetRange(p).from, [tk]: presetRange(p).to });
                }}
            >
                {presets.map((p) => (
                    <option key={p.id} value={p.id}>
                        {p.label}
                    </option>
                ))}
            </select>
            {preset === 'custom' && (
                <div className="mt-2 grid grid-cols-2 gap-2">
                    {(['from', 'to'] as const).map((k) => (
                        <input
                            key={k}
                            dir="ltr"
                            inputMode="numeric"
                            className="field num text-center"
                            placeholder="1404/08/07"
                            aria-label={k === 'from' ? `${def.label} از` : `${def.label} تا`}
                            aria-invalid={error ? true : undefined}
                            value={draft[k]}
                            onChange={(e) => setDraft({ ...draft, [k]: e.target.value })}
                            onBlur={() => apply()}
                            onKeyDown={(e) => e.key === 'Enter' && apply()}
                        />
                    ))}
                </div>
            )}
            {error && <p className="mt-1 text-[12px] font-bold text-error">{error}</p>}
        </div>
    );
}

function RangeControl({ def, values, onChange }: { def: Extract<FilterDef, { type: 'range' }>; values: Record<string, string>; onChange: (patch: Record<string, string>) => void }) {
    const mk = def.min ?? 'min';
    const xk = def.max ?? 'max';
    const [draft, setDraft] = useState({ min: values[mk] ?? '', max: values[xk] ?? '' });
    const [error, setError] = useState(false);
    useEffect(() => setDraft({ min: values[mk] ?? '', max: values[xk] ?? '' }), [values, mk, xk]);

    function apply() {
        const min = draft.min.trim() ? parseAmount(draft.min) : null;
        const max = draft.max.trim() ? parseAmount(draft.max) : null;
        if ((draft.min.trim() && min === null) || (draft.max.trim() && max === null)) {
            setError(true);
            return;
        }
        setError(false);
        const patch = { [mk]: min === null ? '' : String(min), [xk]: max === null ? '' : String(max) };
        if (patch[mk] !== (values[mk] ?? '') || patch[xk] !== (values[xk] ?? '')) onChange(patch);
    }

    return (
        <fieldset>
            <legend className="mb-1 block text-[13px] font-medium text-body">
                {def.label}
                {def.unit && <span className="font-normal text-muted"> ({def.unit})</span>}
            </legend>
            <div className="grid grid-cols-2 gap-2">
                {(['min', 'max'] as const).map((k) => (
                    <input
                        key={k}
                        dir="ltr"
                        inputMode="numeric"
                        className="field num text-center"
                        placeholder={k === 'min' ? 'از' : 'تا'}
                        aria-label={`${def.label} ${k === 'min' ? 'از' : 'تا'}`}
                        aria-invalid={error ? true : undefined}
                        value={draft[k]}
                        onChange={(e) => setDraft({ ...draft, [k]: e.target.value })}
                        onBlur={apply}
                        onKeyDown={(e) => e.key === 'Enter' && apply()}
                    />
                ))}
            </div>
            {error && <p className="mt-1 text-[12px] font-bold text-error">فقط عدد بنویسید.</p>}
        </fieldset>
    );
}

/* ---------- Table ---------- */

export type Column<T> = {
    key: string;
    header: ReactNode;
    cell: (row: T) => ReactNode;
    align?: 'start' | 'end';
    /** Smallest screen that shows the column. */
    from?: 'lg' | 'xl';
    className?: string;
    /** The row's name: wrapped in the row's link, so this cell must hold no links or buttons of its own. */
    primary?: boolean;
};

export type QuickView<T> = {
    title: (row: T) => ReactNode;
    subtitle?: (row: T) => ReactNode;
    body: (row: T) => ReactNode;
};

/** Something to do with the rows ticked in a list, shown in the floating bar. */
export type BulkAction<T> = { label: string; icon?: LucideIcon; run: (rows: T[]) => void | Promise<void> };

const showFrom = { lg: 'hidden lg:table-cell', xl: 'hidden xl:table-cell' };

/**
 * A list as a spreadsheet grid: compact striped rows with a line between columns, a row number that turns into a
 * checkbox, an optional status bar on each row's start edge and day headers when the list is sorted by date.
 *
 * Rows are real links. With a quick view, a plain click on a row opens the side drawer; ctrl/middle click opens the
 * record in a new tab and right-click copies its address. The ⋯ menu at the end of the row holds «صفحه کامل»,
 * «زبانه جدید» and «کپی پیوند». Ticked rows get a floating bar (copy links, plus the page's own `bulk` actions).
 */
export function DataTable<T>({
    label,
    rows,
    loading,
    error,
    onRetry,
    columns,
    rowKey,
    href,
    linkState,
    rowLabel,
    quick,
    mobile,
    empty,
    rowTone,
    rowStatus,
    groupBy,
    offset = 0,
    bulk = [],
    skeleton = 8,
}: {
    label: string;
    rows: T[] | null | undefined;
    loading?: boolean;
    error?: ApiError | null;
    onRetry?: () => void;
    columns: Column<T>[];
    rowKey: (row: T) => string | number;
    href: (row: T) => string;
    linkState?: unknown;
    rowLabel: (row: T) => string;
    quick?: QuickView<T>;
    /** The row on a phone, as a card. */
    mobile: (row: T) => ReactNode;
    empty: ReactNode;
    rowTone?: (row: T) => 'warn' | 'muted' | undefined;
    /** Colours the bar on the row's start edge. */
    rowStatus?: (row: T) => Tone | undefined;
    /** The day header a row falls under; pass it only while the list is sorted by date. */
    groupBy?: (row: T) => string | null;
    /** Rows on the pages before this one, so row numbers carry on across pages. */
    offset?: number;
    bulk?: BulkAction<T>[];
    skeleton?: number;
}) {
    const [peek, setPeek] = useState<number | null>(null);
    const [picked, setPicked] = useState<Set<string | number>>(() => new Set());
    const rowsKey = rows?.map(rowKey).join(',');
    const base = useHref('/').replace(/\/$/, '');

    // A new page or filter closes the drawer and drops the ticks.
    useEffect(() => {
        setPeek(null);
        setPicked(new Set());
    }, [rowsKey]);

    const pickedRows = rows?.filter((r) => picked.has(rowKey(r))) ?? [];
    const all = !!rows?.length && pickedRows.length === rows.length;
    const toggle = (r: T) =>
        setPicked((p) => {
            const next = new Set(p);
            const k = rowKey(r);
            if (next.has(k)) next.delete(k);
            else next.add(k);
            return next;
        });
    const toggleAll = () => setPicked(all || !rows ? new Set() : new Set(rows.map(rowKey)));
    const fullUrl = (r: T) => new URL(base + href(r), window.location.origin).href;

    const groups = new Map<string, number>();
    if (groupBy && rows) for (const r of rows) {
        const g = groupBy(r);
        if (g) groups.set(g, (groups.get(g) ?? 0) + 1);
    }
    const groupAt = (index: number) => {
        if (!groupBy || !rows) return null;
        const g = groupBy(rows[index]);
        return g && (index === 0 || groupBy(rows[index - 1]) !== g) ? g : null;
    };
    const span = columns.length + 2;

    const rail = (r: T) => {
        const s = rowStatus?.(r);
        return s ? ({ 'data-rail': s, style: { '--rail': toneRail[s] } as CSSProperties } as const) : {};
    };

    function onRowLink(e: MouseEvent, index: number) {
        if (!quick || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        e.preventDefault();
        setPeek(index);
    }

    return (
        <>
            <section aria-label={label} aria-busy={loading} className="relative overflow-clip rounded-xl border border-stroke bg-surface shadow-card">
                {loading && rows && <span aria-hidden className="list-progress absolute inset-x-0 top-0 z-[2] h-0.5" />}
                {error && !rows ? (
                    <ErrorState error={error} onRetry={onRetry ?? (() => {})} />
                ) : !rows ? (
                    <SkeletonRows rows={skeleton} />
                ) : rows.length === 0 ? (
                    empty
                ) : (
                    <div className={`transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="grid-table hidden w-full md:table" data-picking={pickedRows.length ? '' : undefined}>
                            <thead>
                                <tr>
                                    <th scope="col" className="grid-gutter sticky top-16 z-[3]">
                                        <input
                                            type="checkbox"
                                            className="size-4 align-middle accent-[var(--primary)]"
                                            aria-label="انتخاب همه ردیف‌های این صفحه"
                                            checked={all}
                                            ref={(el) => {
                                                if (el) el.indeterminate = pickedRows.length > 0 && !all;
                                            }}
                                            onChange={toggleAll}
                                        />
                                    </th>
                                    {columns.map((c) => (
                                        <th key={c.key} scope="col" className={`sticky top-16 z-[3] px-3 ${c.align === 'end' ? 'text-end' : 'text-start'} ${c.from ? showFrom[c.from] : ''}`}>
                                            {c.header}
                                        </th>
                                    ))}
                                    <th scope="col" className="sticky top-16 z-[3] w-12 px-2">
                                        <span className="sr-only">کارها</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.map((r, index) => {
                                    const g = groupAt(index);
                                    const on = picked.has(rowKey(r));
                                    return (
                                        <Fragment key={rowKey(r)}>
                                            {g && (
                                                <tr className="grid-group">
                                                    <th scope="colgroup" colSpan={span} className="px-4 text-start">
                                                        {g}
                                                        <span className="num ms-2 font-normal text-subtle">{count(groups.get(g) ?? 0)}</span>
                                                    </th>
                                                </tr>
                                            )}
                                            <tr className="data-row group" data-tone={rowTone?.(r)} aria-selected={on || undefined} {...rail(r)}>
                                                <td className="grid-gutter">
                                                    <label className="flex cursor-pointer items-center justify-center">
                                                        <span className="grid-num num">{count(offset + index + 1)}</span>
                                                        <input
                                                            type="checkbox"
                                                            className="grid-check size-4 align-middle accent-[var(--primary)]"
                                                            aria-label={`انتخاب ${rowLabel(r)}`}
                                                            checked={on}
                                                            onChange={() => toggle(r)}
                                                        />
                                                    </label>
                                                </td>
                                                {columns.map((c) => (
                                                    <td key={c.key} className={`px-3 ${c.align === 'end' ? 'text-end' : ''} ${c.from ? showFrom[c.from] : ''} ${c.className ?? ''}`}>
                                                        {c.primary ? (
                                                            <Link to={href(r)} state={linkState} className="row-link block" onClick={(e) => onRowLink(e, index)}>
                                                                {c.cell(r)}
                                                            </Link>
                                                        ) : (
                                                            c.cell(r)
                                                        )}
                                                    </td>
                                                ))}
                                                <td className="px-2 text-center whitespace-nowrap">
                                                    <RowMenu label={rowLabel(r)} to={href(r)} url={fullUrl(r)} state={linkState} onQuick={quick ? () => setPeek(index) : undefined} />
                                                </td>
                                            </tr>
                                        </Fragment>
                                    );
                                })}
                            </tbody>
                        </table>

                        <ul className="grid-list divide-y divide-stroke md:hidden">
                            {rows.map((r, index) => {
                                const g = groupAt(index);
                                const t = rowTone?.(r);
                                return (
                                    <Fragment key={rowKey(r)}>
                                        {g && (
                                            <li className="bg-surface-2 px-4 py-1.5 text-[12px] font-semibold text-muted">
                                                {g}
                                                <span className="num ms-2 font-normal text-subtle">{count(groups.get(g) ?? 0)}</span>
                                            </li>
                                        )}
                                        <li className={`flex items-stretch ${t === 'warn' ? 'bg-warn-soft/60' : t === 'muted' ? 'text-muted' : ''}`} {...rail(r)}>
                                            <Link to={href(r)} state={linkState} className="block min-w-0 flex-1 px-4 py-3 hover:bg-surface-2">
                                                {mobile(r)}
                                            </Link>
                                            {quick && (
                                                <button
                                                    type="button"
                                                    className="grid w-12 shrink-0 place-items-center border-s border-stroke text-muted hover:bg-surface-2 hover:text-ink"
                                                    aria-label={`نمایش سریع ${rowLabel(r)}`}
                                                    onClick={() => setPeek(index)}
                                                >
                                                    <Eye aria-hidden size={18} />
                                                </button>
                                            )}
                                        </li>
                                    </Fragment>
                                );
                            })}
                        </ul>
                    </div>
                )}
            </section>
            {pickedRows.length > 0 && (
                <BulkBar
                    n={pickedRows.length}
                    onClear={() => setPicked(new Set())}
                    actions={[
                        { label: 'کپی پیوندها', icon: Link2, run: (sel: T[]) => navigator.clipboard?.writeText(sel.map(fullUrl).join('\n')) },
                        ...bulk,
                    ]}
                    rows={pickedRows}
                />
            )}
            {quick && rows && (
                <QuickDrawer rows={rows} index={peek} onIndex={setPeek} quick={quick} href={href} linkState={linkState} rowLabel={rowLabel} rowKey={rowKey} />
            )}
        </>
    );
}

/** The ⋯ menu at the end of a row: quick view, full page, new tab, copy link. Drawn over the page so the list's rounded corners don't clip it. */
export function RowMenu({ label, to, url, state, onQuick }: { label: string; to: string; url: string; state?: unknown; onQuick?: () => void }) {
    const [at, setAt] = useState<{ top: number; left: number; up: boolean } | null>(null);
    const [copied, setCopied] = useState(false);
    const button = useRef<HTMLButtonElement>(null);
    const menu = useRef<HTMLDivElement>(null);
    const id = useId();

    useEffect(() => {
        if (!at) return;
        menu.current?.querySelector<HTMLElement>('[role="menuitem"]')?.focus();
        const close = (e: Event) => {
            if (e.type === 'scroll' || e.type === 'resize' || !(menu.current?.contains(e.target as Node) || button.current?.contains(e.target as Node))) setAt(null);
        };
        const key = (e: globalThis.KeyboardEvent) => {
            if (e.key === 'Escape') {
                setAt(null);
                button.current?.focus();
            }
        };
        document.addEventListener('pointerdown', close);
        document.addEventListener('keydown', key);
        window.addEventListener('scroll', close, true);
        window.addEventListener('resize', close);
        return () => {
            document.removeEventListener('pointerdown', close);
            document.removeEventListener('keydown', key);
            window.removeEventListener('scroll', close, true);
            window.removeEventListener('resize', close);
        };
    }, [at]);

    useEffect(() => {
        if (!copied) return;
        const t = setTimeout(() => {
            setCopied(false);
            setAt(null);
        }, 900);
        return () => clearTimeout(t);
    }, [copied]);

    function open() {
        if (at) return setAt(null);
        const r = button.current!.getBoundingClientRect();
        const up = r.bottom + 190 > window.innerHeight;
        setAt({ top: up ? r.top - 4 : r.bottom + 4, left: r.left, up });
    }

    function onMenuKey(e: KeyboardEvent) {
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
        e.preventDefault();
        const items = [...(menu.current?.querySelectorAll<HTMLElement>('[role="menuitem"]') ?? [])];
        const i = items.indexOf(document.activeElement as HTMLElement);
        items[(i + (e.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length]?.focus();
    }

    const item = 'flex w-full items-center gap-2.5 rounded-md px-2.5 py-1.5 text-start text-[13px] text-body hover:bg-surface-2 hover:text-ink focus-visible:bg-surface-2 focus-visible:outline-none';
    return (
        <>
            <button
                ref={button}
                type="button"
                className={`inline-grid size-8 place-items-center rounded-lg border text-muted transition-colors hover:bg-surface hover:text-ink ${at ? 'border-stroke bg-surface text-ink' : 'border-transparent group-hover:border-stroke group-hover:bg-surface'}`}
                aria-label={`کارهای ${label}`}
                aria-haspopup="menu"
                aria-expanded={!!at}
                aria-controls={at ? id : undefined}
                onClick={open}
            >
                <MoreVertical aria-hidden size={16} />
            </button>
            {at &&
                createPortal(
                    <div
                        ref={menu}
                        id={id}
                        role="menu"
                        aria-label={`کارهای ${label}`}
                        dir="rtl"
                        onKeyDown={onMenuKey}
                        className="fixed z-50 min-w-48 rounded-xl border border-stroke bg-surface p-1 shadow-pop"
                        style={{ top: at.top, left: at.left, translate: at.up ? '0 -100%' : undefined }}
                    >
                        {onQuick && (
                            <button type="button" role="menuitem" className={item} onClick={() => (setAt(null), onQuick())}>
                                <Eye aria-hidden size={15} />
                                نمایش سریع
                            </button>
                        )}
                        <Link to={to} state={state} role="menuitem" className={item} onClick={() => setAt(null)}>
                            <ArrowUpLeft aria-hidden size={15} />
                            صفحه کامل
                        </Link>
                        <a href={url} target="_blank" rel="noopener" role="menuitem" className={item} onClick={() => setAt(null)}>
                            <ExternalLink aria-hidden size={15} />
                            باز کردن در زبانه جدید
                        </a>
                        <button type="button" role="menuitem" className={item} onClick={() => navigator.clipboard?.writeText(url).then(() => setCopied(true))}>
                            {copied ? <Check aria-hidden size={15} className="text-primary" /> : <Copy aria-hidden size={15} />}
                            {copied ? 'پیوند کپی شد' : 'کپی پیوند'}
                        </button>
                    </div>,
                    document.body,
                )}
        </>
    );
}

/** Floating bar while rows are ticked: how many, what to do with them, and a way to untick. */
function BulkBar<T>({ n, rows, actions, onClear }: { n: number; rows: T[]; actions: BulkAction<T>[]; onClear: () => void }) {
    const [done, setDone] = useState<string | null>(null);
    useEffect(() => {
        if (!done) return;
        const t = setTimeout(() => setDone(null), 1500);
        return () => clearTimeout(t);
    }, [done]);
    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-5 z-30 hidden justify-center px-4 md:flex">
            <div role="region" aria-label="ردیف‌های انتخاب‌شده" className="bulk-bar pointer-events-auto flex items-center gap-2 rounded-xl bg-ink py-1.5 ps-4 pe-1.5 text-[13px] text-page shadow-pop">
                <span className="num me-2 font-medium" aria-live="polite">
                    {done ?? `${count(n)} ردیف انتخاب شد`}
                </span>
                {actions.map((a) => (
                    <button
                        key={a.label}
                        type="button"
                        className="inline-flex h-8 items-center gap-1.5 rounded-lg border border-page/25 px-3 hover:bg-page/10"
                        onClick={async () => {
                            await a.run(rows);
                            setDone(`${a.label}: انجام شد`);
                        }}
                    >
                        {a.icon && <a.icon aria-hidden size={15} />}
                        {a.label}
                    </button>
                ))}
                <button type="button" className="inline-grid size-8 place-items-center rounded-lg hover:bg-page/10" onClick={onClear} aria-label="لغو انتخاب" title="لغو انتخاب">
                    <X aria-hidden size={16} />
                </button>
            </div>
        </div>
    );
}

/* ---------- Quick view drawer ---------- */

function QuickDrawer<T>({
    rows,
    index,
    onIndex,
    quick,
    href,
    linkState,
    rowLabel,
    rowKey,
}: {
    rows: T[];
    index: number | null;
    onIndex: (i: number | null) => void;
    quick: QuickView<T>;
    href: (row: T) => string;
    linkState?: unknown;
    rowLabel: (row: T) => string;
    rowKey: (row: T) => string | number;
}) {
    const dialog = useRef<HTMLDialogElement>(null);
    const [shown, setShown] = useState<number | null>(index);
    const row = shown != null ? rows[shown] : undefined;
    const fullHref = useHref(row ? href(row) : '.');
    const titleId = useId();

    // Keep the last row on screen while the drawer slides shut.
    useEffect(() => {
        const d = dialog.current;
        if (!d) return;
        if (index != null) {
            setShown(index);
            if (!d.open) d.showModal();
        } else if (d.open) d.close();
    }, [index]);

    function step(by: number) {
        if (shown == null) return;
        const next = shown + by;
        if (next >= 0 && next < rows.length) onIndex(next);
    }

    function onKeyDown(e: KeyboardEvent) {
        if (!(e.altKey || e.ctrlKey || e.metaKey) && !(e.target instanceof HTMLInputElement || e.target instanceof HTMLTextAreaElement || e.target instanceof HTMLSelectElement)) {
            if (e.key === 'ArrowDown' || e.key === 'PageDown') {
                e.preventDefault();
                step(1);
            } else if (e.key === 'ArrowUp' || e.key === 'PageUp') {
                e.preventDefault();
                step(-1);
            }
        }
    }

    return (
        <dialog
            ref={dialog}
            aria-labelledby={titleId}
            className="peek-drawer m-0 h-dvh max-h-none w-[min(560px,100vw)] max-w-none bg-transparent p-0"
            style={{ insetInlineEnd: 0, insetInlineStart: 'auto' }}
            onClose={() => onIndex(null)}
            onClick={(e) => e.target === dialog.current && dialog.current.close()}
            onKeyDown={onKeyDown}
        >
            {row && shown != null && (
                <div className="flex h-full flex-col border-s border-stroke bg-page shadow-[0_0_48px_-12px_rgb(0_0_0/0.35)]">
                    <header className="flex items-start gap-3 border-b border-stroke bg-surface px-5 py-4">
                        <div className="min-w-0 flex-1">
                            <p className="text-[12px] font-medium text-muted">نمایش سریع</p>
                            <h2 id={titleId} className="mt-0.5 text-[18px] leading-snug font-bold">
                                {quick.title(row)}
                            </h2>
                            {quick.subtitle && <div className="mt-1 text-[13px] text-body">{quick.subtitle(row)}</div>}
                        </div>
                        <div className="flex shrink-0 items-center gap-1">
                            <button type="button" className="btn btn-ghost min-h-9 px-2" onClick={() => step(-1)} disabled={shown === 0} aria-label="ردیف قبلی" title="ردیف قبلی (↑)">
                                <ChevronRight aria-hidden size={18} />
                            </button>
                            <span className="num min-w-12 text-center text-[12px] text-muted">
                                {shown + 1} / {rows.length}
                            </span>
                            <button type="button" className="btn btn-ghost min-h-9 px-2" onClick={() => step(1)} disabled={shown === rows.length - 1} aria-label="ردیف بعدی" title="ردیف بعدی (↓)">
                                <ChevronLeft aria-hidden size={18} />
                            </button>
                            <button type="button" className="btn btn-ghost min-h-9 px-2" onClick={() => dialog.current?.close()} aria-label="بستن">
                                <X aria-hidden size={18} />
                            </button>
                        </div>
                    </header>
                    <div key={rowKey(row)} className="min-h-0 flex-1 overflow-y-auto overscroll-contain p-5">
                        {quick.body(row)}
                    </div>
                    <footer className="flex flex-wrap items-center gap-2 border-t border-stroke bg-surface px-5 py-3">
                        <Link to={href(row)} state={linkState} className="btn btn-primary" onClick={() => dialog.current?.close()}>
                            <ArrowUpLeft aria-hidden size={16} />
                            صفحه کامل
                        </Link>
                        <a href={fullHref} target="_blank" rel="noopener" className="btn btn-secondary">
                            <ExternalLink aria-hidden size={15} />
                            زبانه جدید
                        </a>
                        <span className="ms-auto hidden text-[12px] text-muted sm:inline">↑ ↓ ردیف قبلی و بعدی · Esc بستن</span>
                        <span className="sr-only">{rowLabel(row)}</span>
                    </footer>
                </div>
            )}
        </dialog>
    );
}

/** A titled group of facts inside a quick view. */
export function QuickSection({ title, children, action }: { title: string; children: ReactNode; action?: ReactNode }) {
    return (
        <section className="mb-4 rounded-xl border border-stroke bg-surface shadow-card px-4 pt-3 pb-2 last:mb-0">
            <div className="mb-1 flex items-center justify-between gap-3">
                <h3 className="text-[13px] font-bold text-muted">{title}</h3>
                {action}
            </div>
            {children}
        </section>
    );
}

export function QuickSkeleton() {
    return (
        <div className="space-y-4" aria-label="در حال بارگذاری">
            {[0, 1].map((i) => (
                <div key={i} className="rounded-xl border border-stroke bg-surface shadow-card p-4">
                    <div className="skeleton mb-4 h-3 w-24" />
                    {[0, 1, 2, 3].map((j) => (
                        <div key={j} className="flex justify-between py-2.5">
                            <div className="skeleton h-3.5 w-20" />
                            <div className="skeleton h-3.5 w-32" />
                        </div>
                    ))}
                </div>
            ))}
        </div>
    );
}

/* ---------- Lists that load every row at once ---------- */

/**
 * Search, filter, sort and page rows already in memory, driven by the same address keys as server lists.
 * `filters` holds one test per key; a key with no value in the address is not tested.
 */
export function useClientList<T>(
    rows: T[] | null | undefined,
    values: Record<string, string>,
    page: number,
    opts: {
        search?: (row: T, q: string) => boolean;
        filters?: Record<string, (row: T, value: string) => boolean>;
        sorts?: Record<string, (a: T, b: T) => number>;
        per?: number;
    },
) {
    const q = (values.q ?? '').trim().toLowerCase();
    if (!rows) return null;
    let all = rows.filter((r) => (!q || !opts.search || opts.search(r, q)) && Object.entries(opts.filters ?? {}).every(([k, test]) => !values[k] || test(r, values[k])));
    const sorter = values.sort ? opts.sorts?.[values.sort] : undefined;
    if (sorter) all = [...all].sort(sorter);
    const perPage = Number(values.per) || opts.per || 50;
    const lastPage = Math.max(1, Math.ceil(all.length / perPage));
    const current = Math.min(page, lastPage);
    return { all, rows: all.slice((current - 1) * perPage, current * perPage), total: all.length, page: current, lastPage, perPage };
}

/** True when the address holds a search or any filter beyond the ones listed in `ignore`. */
export function isFiltered(values: Record<string, string>, ignore: string[] = []) {
    return Object.entries(values).some(([k, v]) => v && !['sort', 'per', ...ignore].includes(k));
}

/* ---------- Pager ---------- */

const PER_PAGE_OPTIONS = [20, 30, 50, 100];

function pageWindow(page: number, last: number): (number | '…')[] {
    const out: (number | '…')[] = [];
    const add = (n: number | '…') => out[out.length - 1] !== n && out.push(n);
    for (let n = 1; n <= last; n++) {
        if (n === 1 || n === last || Math.abs(n - page) <= 1 || (page <= 3 && n <= 4) || (page >= last - 2 && n >= last - 3)) add(n);
        else add('…');
    }
    return out;
}

/** «۳۱–۶۰ از ۴۵۶ سفارش», rows per page, and numbered pages. */
export function ListPager({
    page,
    lastPage,
    total,
    perPage,
    noun,
    onPage,
    onPer,
}: {
    page: number;
    lastPage: number;
    total: number;
    perPage?: number;
    noun: string;
    onPage: (p: number) => void;
    onPer?: (n: number) => void;
}) {
    const perId = useId();
    if (!total) return null;
    const per = perPage ?? Math.ceil(total / Math.max(lastPage, 1));
    const first = (page - 1) * per + 1;
    const lastRow = Math.min(total, page * per);
    return (
        <nav aria-label="صفحه‌بندی" className="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div className="flex flex-wrap items-center gap-3 text-[13px] text-muted">
                <span className="num">
                    {count(first)}–{count(lastRow)} از {count(total)} {noun}
                </span>
                {onPer && (
                    <span className="flex items-center gap-2">
                        <label htmlFor={perId}>در هر صفحه</label>
                        <select id={perId} className="field min-h-8 w-auto py-0.5 text-[13px]" value={per} onChange={(e) => onPer(Number(e.target.value))}>
                            {PER_PAGE_OPTIONS.map((n) => (
                                <option key={n} value={n}>
                                    {n}
                                </option>
                            ))}
                        </select>
                    </span>
                )}
            </div>
            {lastPage > 1 && (
                <div className="flex items-center gap-1">
                    <button type="button" className="btn btn-ghost min-h-9 px-2" disabled={page <= 1} onClick={() => onPage(page - 1)} aria-label="صفحه قبلی">
                        <ChevronRight aria-hidden size={18} />
                    </button>
                    {pageWindow(page, lastPage).map((n, i) =>
                        n === '…' ? (
                            <span key={`gap${i}`} aria-hidden className="px-1 text-muted">
                                …
                            </span>
                        ) : (
                            <button
                                key={n}
                                type="button"
                                aria-current={n === page ? 'page' : undefined}
                                aria-label={`صفحه ${n}`}
                                onClick={() => onPage(n)}
                                className={`num grid h-9 min-w-9 place-items-center rounded-lg px-2 text-[14px] transition-colors ${n === page ? 'bg-primary font-bold text-primary-ink' : 'text-body hover:bg-surface-2'}`}
                            >
                                {n}
                            </button>
                        ),
                    )}
                    <button type="button" className="btn btn-ghost min-h-9 px-2" disabled={page >= lastPage} onClick={() => onPage(page + 1)} aria-label="صفحه بعدی">
                        <ChevronLeft aria-hidden size={18} />
                    </button>
                </div>
            )}
        </nav>
    );
}

/** Empty list: what is (not) there, plus a way out when filters hide everything. */
export function ListEmpty({ filtered, onClear, children, action }: { filtered: boolean; onClear: () => void; children: ReactNode; action?: ReactNode }) {
    return (
        <div className="flex flex-col items-center gap-2 px-6 py-16 text-center">
            <ListFilter aria-hidden size={26} className="text-muted" />
            <p className="font-medium">{filtered ? 'با این جستجو و فیلترها چیزی پیدا نشد.' : children}</p>
            <div className="mt-2 flex flex-wrap justify-center gap-2">
                {filtered && (
                    <button type="button" className="btn btn-secondary" onClick={onClear}>
                        پاک کردن جستجو و فیلترها
                    </button>
                )}
                {action}
            </div>
        </div>
    );
}
