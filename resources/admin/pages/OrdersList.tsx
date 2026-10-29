import { Link } from 'react-router';
import { AlertTriangle, MessageSquare } from 'lucide-react';
import { useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { toman, waited } from '../lib/format';
import { OrderStatus, PayStatus } from '../components/Status';
import { CopyButton, FilterTabs, Row, gateLabel } from '../components/ui';
import { PageHeader, useListParams } from '../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, filterDefaults, type Column, type FilterDef } from '../components/DataList';
import type { Order } from './OrderDetail';

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
    perPage: number;
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

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'newest', label: 'جدیدترین' },
    { value: 'oldest', label: 'قدیمی‌ترین' },
    { value: 'price_desc', label: 'بیشترین مبلغ' },
    { value: 'price_asc', label: 'کمترین مبلغ' },
];

export default function OrdersList() {
    const { labels } = useBoot();
    const filters: FilterDef[] = [
        { type: 'select', key: 'type', label: 'سرویس', any: 'همه سرویس‌ها', options: Object.entries(labels.orderType).map(([value, label]) => ({ value, label })) },
        {
            type: 'select',
            key: 'pay',
            label: 'وضعیت پرداخت',
            options: [...Object.entries(labels.payStatus).map(([value, label]) => ({ value, label: label.trim() })), { value: 'none', label: 'بدون پرداخت' }],
        },
        { type: 'dates', label: 'تاریخ ثبت' },
        { type: 'range', label: 'مبلغ', unit: 'تومان' },
        { type: 'toggle', key: 'unread', label: 'پیام خوانده‌نشده', hint: 'سفارش‌هایی که مشتری در گفتگو منتظر پاسخ است' },
    ];
    const list = useListParams({ view: 'action', q: '', sort: '', per: '', user: '', ...filterDefaults(filters) });
    const { data, error, loading, reload } = useQuery<OrdersPage>(`/admin/api/orders?${list.query}`);
    const v = list.values;
    const typeLabel = v.type ? labels.orderType[v.type] : null;
    const queue = v.view === 'action' && !v.sort;
    const filtered = Object.entries(v).some(([k, val]) => val && !['view', 'sort', 'per'].includes(k));

    const columns: Column<OrderRow>[] = [
        {
            key: 'id',
            header: 'سفارش',
            primary: true,
            cell: (o) => (
                <span className="leading-snug">
                    <span className="num block font-bold text-ink">#{o.id}</span>
                    <span className="block text-[12px] text-muted">{labels.orderType[o.type] ?? o.type}</span>
                </span>
            ),
        },
        {
            key: 'customer',
            header: 'مشتری',
            cell: (o) =>
                o.customer ? (
                    <div className="leading-snug">
                        <Link to={`/users/${o.customer.id}`} className="block max-w-[180px] truncate font-medium underline-offset-4 hover:underline">
                            {o.customer.name || 'بدون نام'}
                        </Link>
                        <div dir="ltr" className="num text-end text-[13px] text-muted">
                            {o.customer.mobile}
                        </div>
                    </div>
                ) : (
                    <span className="text-muted">مهمان</span>
                ),
        },
        { key: 'service', header: 'سرویس', cell: (o) => <div className="max-w-[260px] truncate">{o.service || '—'}</div> },
        { key: 'price', header: 'مبلغ', align: 'end', className: 'num font-medium whitespace-nowrap', cell: (o) => toman(o.price) },
        {
            key: 'status',
            header: 'وضعیت',
            cell: (o) => (
                <div className="flex items-center gap-2">
                    <OrderStatus status={o.status} />
                    {o.unread > 0 && <Unread n={o.unread} />}
                </div>
            ),
        },
        { key: 'pay', header: 'پرداخت', from: 'lg', cell: (o) => <PayStatus status={o.payStatus} /> },
        {
            key: 'time',
            header: queue ? 'در صف' : 'ثبت',
            className: 'whitespace-nowrap',
            cell: (o) => {
                const w = waited(o.createdTs);
                return queue && w ? (
                    <span className={`num inline-flex items-center gap-1 ${w.late ? 'font-medium text-warn' : 'text-body'}`}>
                        {w.late && <AlertTriangle aria-hidden size={14} />}
                        {w.text}
                        {w.late && <span className="sr-only">(بیش از یک روز)</span>}
                    </span>
                ) : (
                    <span dir="ltr" className="num text-[13px] text-muted">
                        {o.createdAt}
                    </span>
                );
            },
        },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={typeLabel ? `سفارش‌های ${typeLabel}` : 'سفارش‌ها'}
                subtitle={queue ? 'قدیمی‌ترین سفارش بالای صف است.' : 'جدیدترین سفارش‌ها اول، مگر ترتیب دیگری انتخاب کنید.'}
            />

            <FilterBar
                views={<FilterTabs label="فیلتر وضعیت" items={views} value={v.view as View} counts={data?.counts} attention="action" onChange={(view) => list.update({ view })} />}
                search={{ label: 'جستجو با شماره سفارش، موبایل یا نام سرویس', placeholder: 'شماره سفارش، موبایل یا سرویس' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={data?.total}
                noun="سفارش"
                loading={loading}
                onRefresh={reload}
            />

            <DataTable
                label="لیست سفارش‌ها"
                rows={data?.orders}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(o) => o.id}
                href={(o) => `/orders/${o.id}`}
                linkState={{ list: list.here }}
                rowLabel={(o) => `سفارش ${o.id}`}
                quick={{
                    title: (o) => <span className="num">سفارش #{o.id}</span>,
                    subtitle: (o) => (
                        <span className="flex flex-wrap items-center gap-2">
                            <OrderStatus status={o.status} />
                            <span>{labels.orderType[o.type] ?? o.type}</span>
                        </span>
                    ),
                    body: (o) => <OrderQuick id={o.id} />,
                }}
                mobile={(o) => {
                    const w = waited(o.createdTs);
                    return (
                        <>
                            <div className="flex items-center justify-between gap-2">
                                <span className="num font-bold">#{o.id}</span>
                                <OrderStatus status={o.status} />
                            </div>
                            <div className="mt-1 truncate">{o.service || labels.orderType[o.type]}</div>
                            <div className="mt-1 flex items-center justify-between gap-2 text-[13px] text-muted">
                                <span className="truncate">{o.customer?.name || o.customer?.mobile || 'مهمان'}</span>
                                <span className="num shrink-0 font-medium text-ink">{toman(o.price)}</span>
                            </div>
                            {(o.unread > 0 || (queue && w)) && (
                                <div className="mt-1.5 flex items-center gap-3 text-[13px]">
                                    {queue && w && <span className={w.late ? 'font-medium text-warn' : 'text-muted'}>در صف: {w.text}</span>}
                                    {o.unread > 0 && <Unread n={o.unread} />}
                                </div>
                            )}
                        </>
                    );
                }}
                empty={
                    <ListEmpty filtered={filtered} onClear={() => list.update({ q: '', type: '', pay: '', from: '', to: '', min: '', max: '', unread: '', user: '' })}>
                        {v.view === 'action' ? 'صف خالی است؛ سفارشی منتظر اقدام نیست.' : 'سفارشی در این فهرست نیست.'}
                    </ListEmpty>
                }
            />

            {data && (
                <ListPager
                    page={data.page}
                    lastPage={data.lastPage}
                    total={data.total}
                    perPage={data.perPage}
                    noun="سفارش"
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(n) => list.update({ per: String(n) })}
                />
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

/** Quick view: what support needs to decide on an order without leaving the queue. */
function OrderQuick({ id }: { id: number }) {
    const { data: order, error } = useQuery<Order>(`/admin/api/orders/${id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!order) return <QuickSkeleton />;
    const c = order.customer;
    const p = order.payment;
    const lastChats = order.chats.slice(-3);
    return (
        <>
            <QuickSection title="مبلغ و پرداخت">
                <dl className="m-0">
                    <Row label="جمع سفارش" num>
                        {toman(order.price)}
                    </Row>
                    <Row label="وضعیت پرداخت">
                        <PayStatus status={order.payStatus} />
                    </Row>
                    {p && <Row label="روش">{gateLabel(p.gate)}</Row>}
                    {p?.refId && (
                        <Row label="کد رهگیری" ltr>
                            {p.refId}
                            <CopyButton value={p.refId} label="کد رهگیری" />
                        </Row>
                    )}
                    <Row label="ثبت" ltr>
                        {order.createdAt}
                    </Row>
                </dl>
            </QuickSection>
            <QuickSection title="مشتری">
                {c ? (
                    <dl className="m-0">
                        <Row label="نام">
                            <Link to={`/users/${c.id}`} className="text-indigo underline-offset-4 hover:underline">
                                {c.name || 'بدون نام'}
                            </Link>
                        </Row>
                        <Row label="موبایل" ltr>
                            {c.mobile}
                            {c.mobile && <CopyButton value={c.mobile} label="موبایل" />}
                        </Row>
                        <Row label="کیف پول" num>
                            {toman(c.wallet)}
                        </Row>
                    </dl>
                ) : (
                    <p className="pb-2 text-muted">سفارش مهمان؛ حساب کاربری ندارد.</p>
                )}
            </QuickSection>
            {order.items.map((item) => (
                <QuickSection key={item.id} title={item.title || order.service || 'قلم سفارش'}>
                    {item.rows.length ? (
                        <dl className="m-0">
                            {item.rows.map((r) => (
                                <Row key={r.label} label={r.label} ltr={r.ltr}>
                                    {r.value ? (
                                        <>
                                            <span className={`select-all ${r.copy ? 'latin' : ''}`}>{r.value}</span>
                                            {r.copy && <CopyButton value={r.value} label={r.label} />}
                                        </>
                                    ) : (
                                        <span className="font-normal text-muted">ثبت نشده</span>
                                    )}
                                </Row>
                            ))}
                        </dl>
                    ) : (
                        <p className="num pb-2">{toman(item.price)}</p>
                    )}
                </QuickSection>
            ))}
            <QuickSection title={`گفتگو${order.chats.length ? ` (${order.chats.length})` : ''}`}>
                {lastChats.length === 0 ? (
                    <p className="pb-2 text-muted">پیامی رد و بدل نشده است.</p>
                ) : (
                    <ul className="space-y-2 pb-2">
                        {lastChats.map((m) => (
                            <li key={m.id} className={`rounded-xl px-3 py-2 text-[13px] ${m.from === 'admin' ? 'bg-surface-2' : 'bg-indigo-soft'}`}>
                                <span className="mb-0.5 flex justify-between gap-2 text-[12px] text-muted">
                                    <span className="font-medium">{m.from === 'admin' ? (m.by ?? 'پشتیبانی') : 'مشتری'}</span>
                                    <span dir="ltr" className="num">
                                        {m.at}
                                    </span>
                                </span>
                                <span className="line-clamp-3 whitespace-pre-line">{m.text}</span>
                            </li>
                        ))}
                    </ul>
                )}
            </QuickSection>
        </>
    );
}
