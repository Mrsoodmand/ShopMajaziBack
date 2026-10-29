import { useEffect, useId, useState, type FormEvent } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router';
import { AlertTriangle, MousePointerClick, Scale, SearchX, X } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { toman, waited } from '../../lib/format';
import { PayStatus } from '../../components/Status';
import { ErrorState, FieldError, FilterTabs, Pager, SearchField, SkeletonRows } from '../../components/ui';
import TransactionPanel from './TransactionPanel';
import { jalali, matchPreset, normalizeJalali, presetRange, presets, type RangePreset } from './dates';
import { gateName, type TransactionRow, type TransactionsPage as PageData, type View } from './types';

const views: { id: View; label: string }[] = [
    { id: 'review', label: 'نیازمند بررسی' },
    { id: 'paid', label: 'موفق' },
    { id: 'waiting', label: 'در انتظار پرداخت' },
    { id: 'failed', label: 'ناموفق' },
    { id: 'all', label: 'همه' },
];

const gates = [
    { id: '', label: 'همه روش‌ها' },
    { id: 'Bank', label: 'درگاه بانک' },
    { id: 'Wallet', label: 'کیف پول' },
    { id: 'none', label: 'سیستمی (عودت، پورسانت)' },
];

/** Filters kept in the URL; `kind` is the payment type (`type` is taken by the sidebar's order links). */
const FILTERS = ['q', 'kind', 'gate', 'from', 'to', 'user'] as const;

export default function TransactionsPage() {
    const { labels } = useBoot();
    const { id } = useParams();
    const openId = id ? Number(id) : null;
    const [params, setParams] = useSearchParams();
    const navigate = useNavigate();

    const view = (params.get('view') as View) || 'review';
    const get = (k: string) => params.get(k) ?? '';
    const page = Number(params.get('page') ?? 1);

    const apiUrl = `/admin/api/transactions?${new URLSearchParams({
        view,
        q: get('q'),
        type: get('kind'),
        gate: get('gate'),
        from: get('from'),
        to: get('to'),
        user: get('user'),
        page: String(page),
    })}`;
    const { data, error, loading, reload } = useQuery<PageData>(apiUrl);

    function update(next: Record<string, string>) {
        const merged = new URLSearchParams(params);
        for (const [k, v] of Object.entries(next)) {
            if (v) merged.set(k, v);
            else merged.delete(k);
        }
        if (!('page' in next)) merged.delete('page');
        setParams(merged);
    }

    const search = params.toString() ? `?${params}` : '';
    const open = (txId: number) => navigate({ pathname: `/transactions/${txId}`, search });
    const close = () => navigate({ pathname: '/transactions', search });
    const filtered = FILTERS.some((k) => get(k));
    const limited = data && data.scope !== 'all' ? data.scope : null;

    return (
        <div className="mx-auto max-w-[1440px]">
            <div className={openId ? 'hidden lg:block' : ''}>
                <header className="mb-5">
                    <h1 className="text-[22px] leading-tight font-bold">تراکنش‌ها</h1>
                    <p className="mt-1 text-muted">
                        {view === 'review' ? 'قدیمی‌ترین مورد بالای صف است.' : 'جدیدترین تراکنش‌ها اول.'}
                        {limited && (
                            <>
                                {' '}
                                شما فقط{' '}
                                {limited.map((s) => (s === 'bank' ? 'پرداخت‌های بانکی' : 'مغایرت‌ها')).join(' و ')} را می‌بینید.
                            </>
                        )}
                    </p>
                </header>

                <div className="mb-3 flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <FilterTabs label="فیلتر وضعیت" items={views} value={view} counts={data?.counts} attention="review" onChange={(v) => update({ view: v })} />
                    <SearchField
                        value={get('q')}
                        onSearch={(q) => update({ q })}
                        label="جستجو با شماره تراکنش، کد رهگیری، شماره سفارش یا موبایل"
                        placeholder="شماره تراکنش، کد رهگیری یا موبایل"
                    />
                </div>

                <Filters params={params} update={update} user={data?.user ?? null} onReset={() => setParams(new URLSearchParams(view === 'review' ? {} : { view }))} />
            </div>

            {/* The guide column only appears where the list still has room; an opened transaction always gets its column from lg. */}
            <div className={`grid items-start gap-6 ${openId ? 'lg:grid-cols-[minmax(0,1fr)_380px]' : 'xl:grid-cols-[minmax(0,1fr)_380px]'}`}>
                <section
                    aria-label="لیست تراکنش‌ها"
                    aria-busy={loading}
                    className={`@container min-w-0 overflow-hidden rounded-2xl border border-stroke bg-surface ${openId ? 'hidden lg:block' : ''}`}
                >
                    {error && !data ? (
                        <ErrorState error={error} onRetry={reload} />
                    ) : !data ? (
                        <SkeletonRows />
                    ) : data.transactions.length === 0 ? (
                        <Empty view={view} q={get('q')} filtered={filtered} onReset={() => setParams(new URLSearchParams({ view: 'all' }))} />
                    ) : (
                        <div className={`transition-opacity ${loading ? 'opacity-60' : ''}`}>
                            {/* Sized by the list's own width (container query), so it fits beside an open panel too. */}
                            <table className="hidden w-full text-start @min-[600px]:table">
                                <thead>
                                    <tr className="border-b border-stroke text-[13px] text-muted">
                                        <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">{view === 'review' ? 'تراکنش · در صف' : 'تراکنش · ثبت'}</th>
                                        <th scope="col" className="px-3 text-start font-medium">کاربر</th>
                                        <th scope="col" className="px-3 text-start font-medium">نوع و روش</th>
                                        <th scope="col" className="px-3 text-end font-medium">مبلغ</th>
                                        <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {data.transactions.map((t) => (
                                        <Row key={t.id} t={t} view={view} selected={t.id === openId} search={search} onOpen={open} typeLabel={labels.paymentType[t.type] ?? t.type} />
                                    ))}
                                </tbody>
                            </table>

                            <ul className="@min-[600px]:hidden">
                                {data.transactions.map((t) => (
                                    <li key={t.id} className="border-b border-stroke last:border-0">
                                        <Link
                                            to={{ pathname: `/transactions/${t.id}`, search }}
                                            aria-current={t.id === openId ? 'true' : undefined}
                                            className={`block px-4 py-3 ${t.id === openId ? 'bg-indigo-soft' : 'hover:bg-surface-2'}`}
                                        >
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="num font-bold">#{t.id}</span>
                                                <PayStatus status={t.status} />
                                            </div>
                                            <div className="mt-1 flex items-center justify-between gap-2">
                                                <span className="truncate">{labels.paymentType[t.type] ?? t.type}</span>
                                                <span className="num shrink-0 font-medium">{toman(t.price)}</span>
                                            </div>
                                            <div className="mt-1 flex items-center justify-between gap-2 text-[13px] text-muted">
                                                <span className="truncate">{t.customer?.name || t.customer?.mobile || 'بدون کاربر'}</span>
                                                <span className="shrink-0">{gateName(t.gate, t.type)}</span>
                                            </div>
                                            <When t={t} view={view} className="mt-1 block text-[13px]" />
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </section>

                {openId ? (
                    <TransactionPanel key={openId} id={openId} onClose={close} onChanged={reload} onOpen={open} />
                ) : (
                    <Placeholder view={view} reviewCount={data?.counts.review ?? null} />
                )}
            </div>

            {data && !openId && (
                <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="تراکنش" onPage={(p) => update({ page: String(p) })} />
            )}
            {data && openId && (
                <div className="hidden lg:block">
                    <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="تراکنش" onPage={(p) => update({ page: String(p) })} />
                </div>
            )}
        </div>
    );
}

function Row({
    t,
    view,
    selected,
    search,
    typeLabel,
    onOpen,
}: {
    t: TransactionRow;
    view: View;
    selected: boolean;
    search: string;
    typeLabel: string;
    onOpen: (id: number) => void;
}) {
    return (
        <tr
            onClick={(e) => {
                if ((e.target as HTMLElement).closest('a')) return;
                onOpen(t.id);
            }}
            className={`cursor-pointer border-b border-stroke last:border-0 ${selected ? 'bg-indigo-soft' : 'hover:bg-surface-2'}`}
        >
            <td className="py-3 ps-5 pe-3 whitespace-nowrap">
                <Link
                    to={{ pathname: `/transactions/${t.id}`, search }}
                    aria-current={selected ? 'true' : undefined}
                    className="num font-bold text-ink underline-offset-4 hover:underline"
                >
                    #{t.id}
                </Link>
                <When t={t} view={view} className="block text-[13px]" />
            </td>
            <td className="px-3">
                {t.customer ? (
                    <div className="leading-snug">
                        <div className="max-w-[160px] truncate font-medium">{t.customer.name || 'بدون نام'}</div>
                        <div dir="ltr" className="num text-end text-[13px] text-muted">
                            {t.customer.mobile}
                        </div>
                    </div>
                ) : (
                    <span className="text-muted">بدون کاربر</span>
                )}
            </td>
            <td className="px-3">
                <div className="leading-snug">
                    <div className="max-w-[200px] truncate">{typeLabel}</div>
                    <div className="text-[13px] whitespace-nowrap text-muted">
                        {gateName(t.gate, t.type)}
                        {t.orderId && <span className="num"> · سفارش {t.orderId}</span>}
                    </div>
                </div>
            </td>
            <td className="num px-3 text-end font-medium whitespace-nowrap">{toman(t.price)}</td>
            <td className="py-3 ps-3 pe-5">
                <PayStatus status={t.status} />
            </td>
        </tr>
    );
}

/** How long a queued item has waited (amber after a day), or when it was made. */
function When({ t, view, className }: { t: TransactionRow; view: View; className: string }) {
    const w = view === 'review' ? waited(t.createdTs) : null;
    if (w) {
        return (
            <span className={`${className} num ${w.late ? 'font-medium text-warn' : 'text-muted'}`}>
                <span className="inline-flex items-center gap-1">
                    {w.late && <AlertTriangle aria-hidden size={13} />}
                    در صف: {w.text}
                    {w.late && <span className="sr-only">(بیش از یک روز)</span>}
                </span>
            </span>
        );
    }
    return (
        <span className={`${className} num text-muted`}>
            <span dir="ltr">{t.createdAt}</span>
        </span>
    );
}

/** Payment type, gateway, date range and the user filter that "all transactions of this user" sets. */
function Filters({
    params,
    update,
    user,
    onReset,
}: {
    params: URLSearchParams;
    update: (next: Record<string, string>) => void;
    user: { id: number; name: string | null; mobile: string | null } | null;
    onReset: () => void;
}) {
    const { labels } = useBoot();
    const kindId = useId();
    const gateId = useId();
    const rangeId = useId();
    const from = params.get('from') ?? '';
    const to = params.get('to') ?? '';
    const [preset, setPreset] = useState<RangePreset>(() => matchPreset(from, to));
    const userId = params.get('user');
    const any = FILTERS.some((k) => params.get(k));

    useEffect(() => {
        if (from || to) setPreset((p) => (p === 'custom' ? p : matchPreset(from, to)));
        else setPreset((p) => (p === 'custom' ? p : 'any'));
    }, [from, to]);

    function choosePreset(p: RangePreset) {
        setPreset(p);
        if (p !== 'custom') update(presetRange(p));
    }

    return (
        <div className="mb-4 grid grid-cols-3 items-start gap-2 sm:flex sm:flex-wrap">
            <label htmlFor={kindId} className="sr-only">
                نوع تراکنش
            </label>
            <select id={kindId} value={params.get('kind') ?? ''} onChange={(e) => update({ kind: e.target.value })} className="field min-w-0 sm:w-auto sm:min-w-[150px]">
                <option value="">همه انواع</option>
                {Object.entries(labels.paymentType).map(([id, label]) => (
                    <option key={id} value={id}>
                        {label}
                    </option>
                ))}
            </select>
            <label htmlFor={gateId} className="sr-only">
                روش پرداخت
            </label>
            <select id={gateId} value={params.get('gate') ?? ''} onChange={(e) => update({ gate: e.target.value })} className="field min-w-0 sm:w-auto sm:min-w-[150px]">
                {gates.map((g) => (
                    <option key={g.id} value={g.id}>
                        {g.label}
                    </option>
                ))}
            </select>
            <label htmlFor={rangeId} className="sr-only">
                بازه زمانی
            </label>
            <select id={rangeId} value={preset} onChange={(e) => choosePreset(e.target.value as RangePreset)} className="field min-w-0 sm:w-auto sm:min-w-[140px]">
                {presets.map((p) => (
                    <option key={p.id} value={p.id}>
                        {p.label}
                    </option>
                ))}
            </select>
            {preset === 'custom' && <CustomRange from={from} to={to} onApply={(r) => update(r)} />}

            {userId && (
                <span className="col-span-3 inline-flex h-[38px] items-center gap-1 justify-self-start rounded-[10px] bg-indigo-soft ps-3 pe-1 text-[14px] text-indigo">
                    کاربر:{' '}
                    <span className="font-medium">{user ? user.name || user.mobile || `#${user.id}` : `#${userId}`}</span>
                    <button
                        type="button"
                        onClick={() => update({ user: '' })}
                        className="grid size-7 place-items-center rounded-md hover:bg-indigo/10"
                        aria-label="برداشتن فیلتر کاربر"
                    >
                        <X aria-hidden size={14} />
                    </button>
                </span>
            )}
            {any && (
                <button type="button" className="btn btn-ghost col-span-3 justify-self-start text-[13px] font-medium" onClick={onReset}>
                    پاک کردن فیلترها
                </button>
            )}
        </div>
    );
}

/** Jalali from/to boxes; applied together on submit so a half-typed date never reaches the server. */
function CustomRange({ from, to, onApply }: { from: string; to: string; onApply: (r: { from: string; to: string }) => void }) {
    const [draft, setDraft] = useState({ from, to });
    const [error, setError] = useState<{ field: 'from' | 'to'; text: string } | null>(null);
    const id = useId();
    const today = jalali(new Date());

    useEffect(() => setDraft({ from, to }), [from, to]);

    function apply(e: FormEvent) {
        e.preventDefault();
        const out = { from: '', to: '' };
        for (const k of ['from', 'to'] as const) {
            if (!draft[k].trim()) continue;
            const v = normalizeJalali(draft[k]);
            if (!v) {
                setError({ field: k, text: `تاریخ را به شکل ${today} بنویسید.` });
                document.getElementById(`${id}-${k}`)?.focus();
                return;
            }
            out[k] = v;
        }
        if (out.from && out.to && out.from > out.to) {
            setError({ field: 'to', text: 'تاریخ پایان باید بعد از تاریخ شروع باشد.' });
            return;
        }
        setError(null);
        onApply(out);
    }

    return (
        <form onSubmit={apply} noValidate className="col-span-3 flex flex-wrap items-start gap-2">
            {(['from', 'to'] as const).map((k) => (
                <div key={k} className="w-[132px]">
                    <label htmlFor={`${id}-${k}`} className="sr-only">
                        {k === 'from' ? 'از تاریخ' : 'تا تاریخ'}
                    </label>
                    <input
                        id={`${id}-${k}`}
                        value={draft[k]}
                        onChange={(e) => setDraft({ ...draft, [k]: e.target.value })}
                        placeholder={k === 'from' ? `از ${today.slice(0, 8)}01` : `تا ${today}`}
                        inputMode="numeric"
                        dir="ltr"
                        className="field num text-end"
                        aria-invalid={error?.field === k}
                        aria-describedby={error?.field === k ? `${id}-error` : undefined}
                    />
                </div>
            ))}
            <button type="submit" className="btn btn-secondary">
                اعمال
            </button>
            {error && (
                <div className="basis-full">
                    <FieldError id={`${id}-error`}>{error.text}</FieldError>
                </div>
            )}
        </form>
    );
}

/** The end column before a transaction is opened: what the queue is and how to work it. */
function Placeholder({ view, reviewCount }: { view: View; reviewCount: number | null }) {
    const queue = view === 'review';
    return (
        <aside className="hidden rounded-2xl border border-dashed border-field px-6 py-10 text-center xl:sticky xl:top-20 xl:block" aria-label="راهنما">
            {queue ? <Scale aria-hidden size={26} className="mx-auto text-muted" /> : <MousePointerClick aria-hidden size={26} className="mx-auto text-muted" />}
            <h2 className="mt-3 font-bold">{queue ? 'مغایرت یعنی چه؟' : 'یک تراکنش را باز کنید'}</h2>
            <p className="mx-auto mt-2 max-w-[300px] text-body">
                {queue
                    ? 'کاربر کیف پولش را با کارتی غیر از کارت ثبت‌شده شارژ کرده؛ مبلغ از حسابش کم شده ولی به کیف پول نرسیده. کارت را با پشتیبانی بررسی کنید، بعد این‌جا کیف پول را شارژ یا تراکنش را رد کنید.'
                    : 'جزئیات، مشتری، سفارش و اقدام‌ها این‌جا باز می‌شوند و فهرست سر جایش می‌ماند.'}
            </p>
            {queue && reviewCount === 0 && <p className="mt-3 text-[13px] font-medium text-primary">الان موردی منتظر بررسی نیست.</p>}
        </aside>
    );
}

function Empty({ view, q, filtered, onReset }: { view: View; q: string; filtered: boolean; onReset: () => void }) {
    return (
        <div className="flex flex-col items-center gap-2 px-6 py-16 text-center">
            <SearchX aria-hidden size={28} className="text-muted" />
            <p className="font-medium">
                {q ? `تراکنشی با «${q}» پیدا نشد.` : view === 'review' ? 'صف خالی است؛ تراکنشی منتظر بررسی نیست.' : 'تراکنشی در این فهرست نیست.'}
            </p>
            {filtered ? (
                <button type="button" className="btn btn-secondary mt-2" onClick={onReset}>
                    پاک کردن جستجو و فیلتر
                </button>
            ) : (
                view === 'review' && <p className="text-muted">شارژهای کیف پولی که کارت پرداختشان با کارت ثبت‌شده فرق دارد این‌جا می‌آیند.</p>
            )}
        </div>
    );
}
