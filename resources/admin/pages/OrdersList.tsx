import { useEffect, useId, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router';
import { AlertTriangle, ChevronLeft, ChevronRight, MessageSquare, RotateCw, Search, SearchX } from 'lucide-react';
import { useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { count, toman, waited } from '../lib/format';
import { OrderStatus, PayStatus } from '../components/Status';

type OrderRow = {
    id: number;
    type: string;
    service: string | null;
    price: number;
    status: string;
    payStatus: string | null;
    createdAt: string | null;
    createdTs: number | null;
    customer: { id: number; name: string | null; mobile: string | null } | null;
    unread: number;
};

type OrdersPage = {
    counts: Record<View, number>;
    page: number;
    lastPage: number;
    total: number;
    orders: OrderRow[];
};

type View = 'action' | 'waiting' | 'complete' | 'failed' | 'all';

const views: { id: View; label: string }[] = [
    { id: 'action', label: 'نیازمند اقدام' },
    { id: 'waiting', label: 'در انتظار پرداخت' },
    { id: 'complete', label: 'تکمیل‌شده' },
    { id: 'failed', label: 'لغو و عودت' },
    { id: 'all', label: 'همه' },
];

export default function OrdersList() {
    const { labels } = useBoot();
    const [params, setParams] = useSearchParams();
    const navigate = useNavigate();
    const view = (params.get('view') as View) || 'action';
    const type = params.get('type') ?? '';
    const q = params.get('q') ?? '';
    const page = Number(params.get('page') ?? 1);
    const [draft, setDraft] = useState(q);
    const searchId = useId();
    const typeId = useId();

    useEffect(() => setDraft(q), [q]);

    const apiUrl = `/admin/api/orders?${new URLSearchParams({ view, type, q, page: String(page) })}`;
    const { data, error, loading, reload } = useQuery<OrdersPage>(apiUrl);

    function update(next: Record<string, string>) {
        const merged = new URLSearchParams(params);
        for (const [k, v] of Object.entries(next)) {
            if (v) merged.set(k, v);
            else merged.delete(k);
        }
        if (!('page' in next)) merged.delete('page');
        setParams(merged);
    }

    // Search as the admin types, after a short pause.
    useEffect(() => {
        if (draft.trim() === q) return;
        const t = setTimeout(() => update({ q: draft.trim() }), 350);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [draft]);

    const typeLabel = type ? labels.orderType[type] : null;
    const listHref = `?${params}`;
    const showWaited = view === 'action';

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">{typeLabel ? `سفارش‌های ${typeLabel}` : 'سفارش‌ها'}</h1>
                    <p className="mt-1 text-muted">
                        {view === 'action' ? 'قدیمی‌ترین سفارش بالای صف است.' : 'جدیدترین سفارش‌ها اول.'}
                    </p>
                </div>
            </div>

            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <nav aria-label="فیلتر وضعیت" className="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1 xl:pb-0">
                    {views.map((v) => {
                        const active = v.id === view;
                        const n = data?.counts[v.id];
                        return (
                            <button
                                key={v.id}
                                type="button"
                                aria-pressed={active}
                                onClick={() => update({ view: v.id })}
                                className={`flex h-9 shrink-0 items-center gap-2 rounded-lg px-3 text-[14px] transition-colors ${
                                    active ? 'bg-ink font-medium text-surface' : 'text-body hover:bg-surface-2'
                                }`}
                            >
                                {v.label}
                                <span
                                    className={`num min-w-6 rounded-md px-1.5 text-center text-[12px] ${
                                        active ? 'bg-surface/20' : v.id === 'action' && n ? 'bg-indigo-soft font-bold text-indigo' : 'bg-surface-2 text-muted'
                                    }`}
                                >
                                    {n == null ? '…' : count(n)}
                                </span>
                            </button>
                        );
                    })}
                </nav>

                <div className="flex flex-1 gap-2 xl:justify-end">
                    <div className="relative flex-1 xl:max-w-[320px]">
                        <label htmlFor={searchId} className="sr-only">
                            جستجو با شماره سفارش، موبایل یا نام سرویس
                        </label>
                        <Search aria-hidden size={16} className="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-muted" />
                        <input
                            id={searchId}
                            type="search"
                            value={draft}
                            onChange={(e) => setDraft(e.target.value)}
                            placeholder="شماره سفارش یا موبایل"
                            className="field ps-9"
                        />
                    </div>
                    <label htmlFor={typeId} className="sr-only">
                        نوع سفارش
                    </label>
                    <select id={typeId} value={type} onChange={(e) => update({ type: e.target.value })} className="field w-auto min-w-[150px]">
                        <option value="">همه سرویس‌ها</option>
                        {Object.entries(labels.orderType).map(([id, label]) => (
                            <option key={id} value={id}>
                                {label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <section aria-label="لیست سفارش‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                        <AlertTriangle aria-hidden size={28} className="text-error" />
                        <p className="font-medium">{error.message}</p>
                        {!error.sessionExpired && (
                            <button type="button" className="btn btn-secondary" onClick={reload}>
                                <RotateCw aria-hidden size={16} />
                                تلاش دوباره
                            </button>
                        )}
                    </div>
                ) : !data ? (
                    <SkeletonRows />
                ) : data.orders.length === 0 ? (
                    <Empty q={q} type={typeLabel} view={view} onReset={() => setParams(new URLSearchParams({ view: 'all' }))} />
                ) : (
                    <div className={`transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="hidden w-full text-start md:table">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">سفارش</th>
                                    <th scope="col" className="px-3 text-start font-medium">مشتری</th>
                                    <th scope="col" className="px-3 text-start font-medium">سرویس</th>
                                    <th scope="col" className="px-3 text-end font-medium">مبلغ</th>
                                    <th scope="col" className="px-3 text-start font-medium">وضعیت</th>
                                    <th scope="col" className="hidden px-3 text-start font-medium lg:table-cell">پرداخت</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">{showWaited ? 'در صف' : 'ثبت'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.orders.map((o) => {
                                    const w = waited(o.createdTs);
                                    return (
                                        <tr
                                            key={o.id}
                                            onClick={(e) => {
                                                if ((e.target as HTMLElement).closest('a')) return;
                                                navigate(`/orders/${o.id}`, { state: { list: listHref } });
                                            }}
                                            className="cursor-pointer border-b border-stroke last:border-0 hover:bg-surface-2"
                                        >
                                            <td className="py-3 ps-5 pe-3">
                                                <Link
                                                    to={`/orders/${o.id}`}
                                                    state={{ list: listHref }}
                                                    className="num font-bold text-ink underline-offset-4 hover:underline"
                                                >
                                                    #{o.id}
                                                </Link>
                                            </td>
                                            <td className="px-3">
                                                {o.customer ? (
                                                    <div className="leading-snug">
                                                        <div className="max-w-[180px] truncate font-medium">{o.customer.name || 'بدون نام'}</div>
                                                        <div dir="ltr" className="num text-end text-[13px] text-muted">{o.customer.mobile}</div>
                                                    </div>
                                                ) : (
                                                    <span className="text-muted">مهمان</span>
                                                )}
                                            </td>
                                            <td className="px-3">
                                                <div className="leading-snug">
                                                    <div className="max-w-[260px] truncate">{o.service || '—'}</div>
                                                    <div className="text-[13px] text-muted">{labels.orderType[o.type] ?? o.type}</div>
                                                </div>
                                            </td>
                                            <td className="num px-3 text-end font-medium whitespace-nowrap">{toman(o.price)}</td>
                                            <td className="px-3">
                                                <div className="flex items-center gap-2">
                                                    <OrderStatus status={o.status} />
                                                    {o.unread > 0 && <Unread n={o.unread} />}
                                                </div>
                                            </td>
                                            <td className="hidden px-3 lg:table-cell">
                                                <PayStatus status={o.payStatus} />
                                            </td>
                                            <td className="py-3 ps-3 pe-5 whitespace-nowrap">
                                                {showWaited && w ? (
                                                    <span className={`num inline-flex items-center gap-1 ${w.late ? 'font-medium text-warn' : 'text-body'}`}>
                                                        {w.late && <AlertTriangle aria-hidden size={14} />}
                                                        {w.text}
                                                        {w.late && <span className="sr-only">(بیش از یک روز)</span>}
                                                    </span>
                                                ) : (
                                                    <span dir="ltr" className="num text-[13px] text-muted">{o.createdAt}</span>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>

                        <ul className="md:hidden">
                            {data.orders.map((o) => {
                                const w = waited(o.createdTs);
                                return (
                                    <li key={o.id} className="border-b border-stroke last:border-0">
                                        <Link to={`/orders/${o.id}`} state={{ list: listHref }} className="block px-4 py-3 hover:bg-surface-2">
                                            <div className="flex items-center justify-between gap-2">
                                                <span className="num font-bold">#{o.id}</span>
                                                <OrderStatus status={o.status} />
                                            </div>
                                            <div className="mt-1 truncate">{o.service || labels.orderType[o.type]}</div>
                                            <div className="mt-1 flex items-center justify-between gap-2 text-[13px] text-muted">
                                                <span className="truncate">{o.customer?.name || o.customer?.mobile || 'مهمان'}</span>
                                                <span className="num shrink-0 font-medium text-ink">{toman(o.price)}</span>
                                            </div>
                                            {(o.unread > 0 || (showWaited && w)) && (
                                                <div className="mt-1.5 flex items-center gap-3 text-[13px]">
                                                    {showWaited && w && (
                                                        <span className={`inline-flex items-center gap-1 ${w.late ? 'font-medium text-warn' : 'text-muted'}`}>
                                                            {w.late && <AlertTriangle aria-hidden size={14} />}
                                                            در صف: {w.text}
                                                            {w.late && <span className="sr-only">(بیش از یک روز)</span>}
                                                        </span>
                                                    )}
                                                    {o.unread > 0 && <Unread n={o.unread} />}
                                                </div>
                                            )}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                )}
            </section>

            {data && data.lastPage > 1 && (
                <nav aria-label="صفحه‌بندی" className="mt-4 flex items-center justify-between gap-3">
                    <p className="num text-muted">
                        صفحه {data.page} از {data.lastPage} · {count(data.total)} سفارش
                    </p>
                    <div className="flex gap-2">
                        <button type="button" className="btn btn-secondary" disabled={data.page <= 1} onClick={() => update({ page: String(data.page - 1) })}>
                            <ChevronRight aria-hidden size={16} />
                            قبلی
                        </button>
                        <button
                            type="button"
                            className="btn btn-secondary"
                            disabled={data.page >= data.lastPage}
                            onClick={() => update({ page: String(data.page + 1) })}
                        >
                            بعدی
                            <ChevronLeft aria-hidden size={16} />
                        </button>
                    </div>
                </nav>
            )}
        </div>
    );
}

function Unread({ n }: { n: number }) {
    return (
        <span className="inline-flex items-center gap-1 text-[13px] font-medium text-indigo">
            <MessageSquare aria-hidden size={14} />
            <span className="num">{n}</span>
            <span className="sr-only">پیام خوانده‌نشده</span>
        </span>
    );
}

function Empty({ q, type, view, onReset }: { q: string; type: string | null; view: View; onReset: () => void }) {
    const filtered = q || type;
    return (
        <div className="flex flex-col items-center gap-2 px-6 py-16 text-center">
            <SearchX aria-hidden size={28} className="text-muted" />
            <p className="font-medium">
                {q ? `سفارشی با «${q}» پیدا نشد.` : view === 'action' ? 'صف خالی است؛ سفارشی منتظر اقدام نیست.' : 'سفارشی در این فهرست نیست.'}
            </p>
            {filtered ? (
                <button type="button" className="btn btn-secondary mt-2" onClick={onReset}>
                    پاک کردن جستجو و فیلتر
                </button>
            ) : (
                <p className="text-muted">سفارش‌های پرداخت‌شده‌ای که بررسی یا انجام لازم دارند اینجا می‌آیند.</p>
            )}
        </div>
    );
}

function SkeletonRows() {
    return (
        <div className="divide-y divide-stroke" aria-label="در حال بارگذاری">
            {Array.from({ length: 8 }, (_, i) => (
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
