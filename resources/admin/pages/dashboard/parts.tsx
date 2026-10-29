import { useId, useState, type ReactNode } from 'react';
import { Link } from 'react-router';
import { AlertTriangle, ArrowDown, ArrowUp, ChevronLeft, CircleCheck, MessageSquareText, Ticket as TicketIcon } from 'lucide-react';
import { useBoot } from '../../lib/boot';
import { count, shortToman, toman, waited } from '../../lib/format';
import { OrderStatus } from '../../components/Status';
import type { Comment, Dashboard, Day, Mismatch, QueueOrder, Ticket } from './types';

/* ───────────── Block: the dashboard's container (a Panel with a count and a "see all" link) ───────────── */

export function Block({
    title,
    total,
    more,
    children,
    flush,
    className = '',
}: {
    title: string;
    total?: number;
    /** «همه» link: `to` is a React route, `href` an old Blade page. */
    more?: { to?: string; href?: string; label?: string };
    children: ReactNode;
    /** Lists run edge to edge; everything else keeps the panel's padding. */
    flush?: boolean;
    className?: string;
}) {
    const id = useId();
    const linkClass = 'inline-flex items-center gap-0.5 rounded-md px-1.5 py-1 text-[13px] font-medium text-indigo hover:bg-indigo-soft';
    const label = (
        <>
            {more?.label ?? 'همه'}
            <ChevronLeft aria-hidden size={15} />
        </>
    );
    return (
        <section aria-labelledby={id} className={`rounded-2xl border border-stroke bg-surface ${className}`}>
            <div className="flex items-center gap-2 px-5 pt-4 pb-2">
                <h2 id={id} className="text-[16px] font-bold">
                    {title}
                </h2>
                {total !== undefined && (
                    <span className={`num rounded-md px-1.5 text-[12px] font-bold ${total > 0 ? 'bg-ink text-surface' : 'bg-surface-2 text-muted'}`}>{count(total)}</span>
                )}
                <span className="flex-1" />
                {more?.to && (
                    <Link to={more.to} className={linkClass}>
                        {label}
                    </Link>
                )}
                {more?.href && (
                    <a href={more.href} className={linkClass}>
                        {label}
                    </a>
                )}
            </div>
            <div className={flush ? 'pb-2' : 'px-5 pt-1 pb-5'}>{children}</div>
        </section>
    );
}

function Empty({ children }: { children: ReactNode }) {
    return (
        <p className="mx-5 my-3 flex items-center gap-2 rounded-xl bg-surface-2 px-3 py-3 text-[13px] text-muted">
            <CircleCheck aria-hidden size={16} className="shrink-0 text-primary" />
            {children}
        </p>
    );
}

function Waited({ ts }: { ts: number | null }) {
    const w = waited(ts);
    if (!w) return null;
    return (
        <span className={`num inline-flex items-center gap-1 text-[13px] whitespace-nowrap ${w.late ? 'font-medium text-warn' : 'text-muted'}`}>
            {w.late && <AlertTriangle aria-hidden size={13} />}
            {w.text}
        </span>
    );
}

/* ───────────── Today vs yesterday ───────────── */

function Delta({ now, before }: { now: number; before: number }) {
    if (now === before) return <span className="text-[12px] text-muted">مثل دیروز</span>;
    if (before === 0) return <span className="text-[12px] text-muted">دیروز صفر</span>;
    const pct = Math.round(((now - before) / before) * 100);
    const up = pct >= 0;
    const Icon = up ? ArrowUp : ArrowDown;
    return (
        <span className={`num inline-flex items-center gap-0.5 text-[12px] font-medium ${up ? 'text-emerald-700 dark:text-emerald-300' : 'text-error'}`}>
            <Icon aria-hidden size={12} strokeWidth={2.5} />
            {Math.abs(pct)}٪ {up ? 'بیشتر' : 'کمتر'} از دیروز
        </span>
    );
}

type Figure = { label: string; value: string; note: ReactNode };

function figures(d: Dashboard): Figure[] {
    const out: Figure[] = [];
    const today = d.days?.at(-1);
    const yesterday = d.days?.at(-2);
    if (today && yesterday) {
        out.push({ label: 'فروش امروز', value: toman(today.sales), note: <Delta now={today.sales} before={yesterday.sales} /> });
        out.push({ label: 'سفارش تکمیل‌شده', value: count(today.orders), note: <Delta now={today.orders} before={yesterday.orders} /> });
    }
    out.push({ label: 'کاربر جدید', value: count(d.users.today), note: <span className="num text-[12px] text-muted">از {count(d.users.total)} کاربر</span> });
    if (d.queue) {
        out.push({ label: 'منتظر پرداخت', value: count(d.queue.waitingToPay), note: <span className="text-[12px] text-muted">سفارش امروز، هنوز پرداخت‌نشده</span> });
    }
    return out;
}

/** Today's figures: four across on wide screens, two by two on narrow ones. */
export function Pulse({ data }: { data: Dashboard }) {
    const items = figures(data);
    return (
        <dl className="grid grid-cols-2 overflow-hidden rounded-2xl border border-stroke bg-surface lg:grid-cols-4">
            {items.map((f) => (
                <div key={f.label} className="border-stroke px-5 py-4 [&:not(:first-child)]:border-s max-lg:[&:nth-child(3)]:border-s-0 max-lg:[&:nth-child(n+3)]:border-t">
                    <dt className="text-[13px] text-muted">{f.label}</dt>
                    <dd className="m-0 mt-1">
                        <div className="num text-[20px] leading-tight font-bold">{f.value}</div>
                        <div className="mt-1">{f.note}</div>
                    </dd>
                </div>
            ))}
        </dl>
    );
}

/* ───────────── Last 14 days ───────────── */

type Metric = 'sales' | 'orders';

/** Bars per day, oldest at the start (right, in RTL) and today at the end. Values show on hover/focus and for today. */
export function DaysChart({ days }: { days: Day[] }) {
    const [metric, setMetric] = useState<Metric>('sales');
    const values = days.map((d) => d[metric]);
    const max = Math.max(...values, 1);
    const total = values.reduce((a, b) => a + b, 0);
    const fmt = (v: number) => (metric === 'sales' ? shortToman(v) : count(v));
    const tableId = useId();

    return (
        <div className="@container">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="m-0 text-[13px] text-muted">
                    جمع ۱۴ روز: <span className="num font-bold text-ink">{metric === 'sales' ? toman(total) : `${count(total)} سفارش`}</span>
                </p>
                <div role="radiogroup" aria-label="نمودار بر اساس" className="inline-flex rounded-lg bg-surface-2 p-0.5 text-[13px]">
                    {(['sales', 'orders'] as Metric[]).map((m) => (
                        <button
                            key={m}
                            type="button"
                            role="radio"
                            aria-checked={metric === m}
                            onClick={() => setMetric(m)}
                            className={`rounded-md px-2.5 py-1 transition-colors ${metric === m ? 'bg-surface font-bold text-ink shadow-sm' : 'text-muted hover:text-ink'}`}
                        >
                            {m === 'sales' ? 'فروش' : 'تعداد'}
                        </button>
                    ))}
                </div>
            </div>

            <div aria-hidden className={`relative mt-4 flex items-end gap-1 h-44`}>
                {total === 0 && (
                    <p className="absolute inset-0 grid place-items-center text-[13px] text-muted">در این ۱۴ روز سفارش تکمیل‌شده‌ای نبوده.</p>
                )}
                {days.map((d, i) => {
                    const v = d[metric];
                    const isToday = i === days.length - 1;
                    return (
                        <div key={d.date} className="group relative flex h-full flex-1 flex-col justify-end" title={`${d.weekday} ${d.date}: ${fmt(v)}`}>
                            <span
                                className={`num pointer-events-none absolute inset-x-[-12px] text-center text-[11px] font-bold whitespace-nowrap transition-opacity ${
                                    isToday && v > 0 ? 'text-primary' : 'text-ink opacity-0 group-hover:opacity-100'
                                }`}
                                style={{ bottom: `calc(${(v / max) * 100}% + 4px)` }}
                            >
                                {fmt(v)}
                            </span>
                            <div
                                className={`rounded-t-[5px] transition-[height,background-color] duration-200 ${
                                    isToday ? 'bg-primary' : v > 0 ? 'bg-primary/30 group-hover:bg-primary/55' : 'bg-stroke'
                                }`}
                                style={{ height: v > 0 ? `max(${(v / max) * 100}%, 3px)` : '2px' }}
                            />
                        </div>
                    );
                })}
            </div>
            <div aria-hidden className="mt-2 flex gap-1 border-t border-stroke pt-1.5 text-[11px] text-muted">
                {days.map((d, i) => {
                    const isToday = i === days.length - 1;
                    const sparse = isToday || i % 7 === 0;
                    // A narrow chart only has room for the first date of each week and «امروز».
                    return (
                        <span key={d.date} className={`num min-w-0 flex-1 text-center whitespace-nowrap ${isToday ? 'font-bold text-ink' : ''} ${sparse ? '' : '@max-xl:invisible'}`}>
                            {isToday ? 'امروز' : d.date}
                        </span>
                    );
                })}
            </div>

            <table id={tableId} className="sr-only">
                <caption>{metric === 'sales' ? 'فروش' : 'سفارش تکمیل‌شده'} در ۱۴ روز گذشته</caption>
                <tbody>
                    {days.map((d) => (
                        <tr key={d.date}>
                            <th scope="row">
                                {d.weekday} {d.date}
                            </th>
                            <td>{metric === 'sales' ? toman(d.sales) : count(d.orders)}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

/* ───────────── Sales by service, last 30 days ───────────── */

export function ByService({ rows }: { rows: NonNullable<Dashboard['byType']> }) {
    const { labels } = useBoot();
    const total = rows.reduce((a, r) => a + r.sales, 0);
    if (!rows.length) return <p className="m-0 text-[13px] text-muted">در ۳۰ روز گذشته سفارش تکمیل‌شده‌ای نبوده.</p>;
    return (
        <ul className="m-0 list-none space-y-3.5 p-0">
            {rows.map((r) => {
                const share = total ? r.sales / total : 0;
                return (
                    <li key={r.type}>
                        <div className="flex items-baseline justify-between gap-3">
                            <Link to={`/orders?type=${r.type}&view=all`} className="font-medium hover:underline">
                                {labels.orderType[r.type] ?? r.type}
                            </Link>
                            <span className="num text-[13px] whitespace-nowrap">
                                <span className="font-bold">{shortToman(r.sales)}</span>
                                <span className="text-muted"> · {count(r.orders)} سفارش</span>
                            </span>
                        </div>
                        <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-surface-2">
                            <div className="h-full rounded-full bg-indigo" style={{ width: `${Math.max(share * 100, 2)}%` }} />
                        </div>
                    </li>
                );
            })}
        </ul>
    );
}

/* ───────────── Lists ───────────── */

const rowLink = 'flex items-center gap-3 px-5 py-2.5 transition-colors hover:bg-surface-2 focus-visible:outline-offset-[-2px]';

export function QueueCounts({ queue }: { queue: NonNullable<Dashboard['queue']> }) {
    const { labels } = useBoot();
    return (
        <p className="m-0 flex flex-wrap gap-x-4 gap-y-1 px-5 pb-2 text-[13px] text-muted">
            {Object.entries(queue.counts).map(([status, n]) => (
                <span key={status} className="num">
                    {labels.orderStatus[status]?.trim() || status}: <span className="font-bold text-ink">{count(n)}</span>
                </span>
            ))}
        </p>
    );
}

export function QueueList({ orders }: { orders: QueueOrder[] }) {
    const { labels } = useBoot();
    if (!orders.length) return <Empty>هیچ سفارشی منتظر شما نیست.</Empty>;
    return (
        <ul className="m-0 list-none p-0">
            {orders.map((o) => (
                <li key={o.id} className="border-t border-stroke first:border-t-0">
                    <Link to={`/orders/${o.id}`} state={{ list: '/' }} className={rowLink}>
                        <span className="num w-14 shrink-0 font-bold">#{o.id}</span>
                        <span className="min-w-0 flex-1 leading-snug">
                            <span className="block truncate">{o.service || labels.orderType[o.type] || '—'}</span>
                            <span className="block truncate text-[13px] text-muted">
                                {labels.orderType[o.type] ?? o.type}
                                {o.customer && <> · {o.customer.name || o.customer.mobile}</>}
                            </span>
                        </span>
                        <span className="num hidden shrink-0 text-[13px] text-body sm:block">{toman(o.price)}</span>
                        <span className="flex shrink-0 flex-col items-end gap-1">
                            <OrderStatus status={o.status} />
                            <Waited ts={o.createdTs} />
                        </span>
                    </Link>
                </li>
            ))}
        </ul>
    );
}

const ticketStatus: Record<string, string> = { 'User-answer': 'پاسخ کاربر', 'User-Ticket': 'تیکت جدید', progressing: 'در حال بررسی' };

export function TicketList({ items, itemLink }: { items: Ticket[]; itemLink: string }) {
    if (!items.length) return <Empty>تیکت بی‌پاسخی نیست.</Empty>;
    return (
        <ul className="m-0 list-none p-0">
            {items.map((t) => (
                <li key={t.id} className="border-t border-stroke first:border-t-0">
                    <Link to={itemLink + t.id} className={rowLink}>
                        <TicketIcon aria-hidden size={16} className={`shrink-0 ${t.unread ? 'text-indigo' : 'text-muted'}`} />
                        <span className="min-w-0 flex-1 leading-snug">
                            <span className={`block truncate ${t.unread ? 'font-bold' : ''}`}>{t.subject || 'بدون موضوع'}</span>
                            <span className="block truncate text-[13px] text-muted">
                                {ticketStatus[t.status] ?? t.status}
                                {t.customer && <> · {t.customer}</>}
                            </span>
                        </span>
                        <Waited ts={t.updatedTs} />
                    </Link>
                </li>
            ))}
        </ul>
    );
}

export function MismatchList({ items, link }: { items: Mismatch[]; link: string }) {
    if (!items.length) return <Empty>مغایرتی برای بررسی نیست.</Empty>;
    return (
        <ul className="m-0 list-none p-0">
            {items.map((p) => (
                <li key={p.id} className="border-t border-stroke first:border-t-0">
                    <Link to={`${link}/${p.id}`} className={rowLink}>
                        <span className="min-w-0 flex-1 leading-snug">
                            <span className="num block font-medium">{toman(p.price)}</span>
                            <span className="num block truncate text-[13px] text-muted">
                                پرداخت #{p.id}
                                {p.customer && <> · {p.customer}</>}
                            </span>
                        </span>
                        <Waited ts={p.createdTs} />
                    </Link>
                </li>
            ))}
        </ul>
    );
}

export function CommentList({ items, link }: { items: Comment[]; link: string }) {
    if (!items.length) return <Empty>نظری منتظر تأیید نیست.</Empty>;
    return (
        <ul className="m-0 list-none p-0">
            {items.map((c) => (
                <li key={c.id} className="border-t border-stroke first:border-t-0">
                    <Link to={link} className={`${rowLink} items-start`}>
                        <MessageSquareText aria-hidden size={16} className="mt-1 shrink-0 text-muted" />
                        <span className="min-w-0 flex-1 leading-snug">
                            <span className="block text-[13px] font-bold">{c.name || 'بی‌نام'}</span>
                            <span className="line-clamp-2 text-[13px] text-body">{c.text || '—'}</span>
                        </span>
                        <Waited ts={c.createdTs} />
                    </Link>
                </li>
            ))}
        </ul>
    );
}

/* ───────────── Loading ───────────── */

export function DashboardSkeleton() {
    return (
        <div aria-busy="true" aria-label="در حال بارگذاری داشبورد" className="space-y-5">
            <div className="skeleton h-24 rounded-2xl" />
            <div className="grid gap-5 lg:grid-cols-[1fr_360px]">
                <div className="skeleton h-80 rounded-2xl" />
                <div className="skeleton h-80 rounded-2xl" />
            </div>
        </div>
    );
}
