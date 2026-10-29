import { Link } from 'react-router';
import { X } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { ErrorState, FilterTabs, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';
import { BotOrderBadge, botUserName, type BotOrderSummary, type BotUserRef } from './parts';

type Row = BotOrderSummary & { user: BotUserRef | null };

const filters = [
    { id: 'all', label: 'همه' },
    { id: 'active', label: 'فعال' },
    { id: 'suspended', label: 'معلق' },
    { id: 'waiting', label: 'در انتظار پرداخت' },
    { id: 'done', label: 'تکمیل‌شده' },
    { id: 'ended', label: 'پایان‌یافته' },
];

export default function BotOrders() {
    const list = useListParams({ status: 'all', q: '', user: '' });
    const { data, error, loading, reload } = useQuery<{ page: number; lastPage: number; total: number; counts: Record<string, number>; orders: Row[] }>(
        `/admin/api/bot/orders?${list.query}`,
    );

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="سفارش‌های ربات"
                subtitle="آی‌پی‌هایی که از ربات تلگرام خریده شده‌اند. سفارش فعال اگر سر وقت تمدید نشود معلق و بعد پایان‌یافته می‌شود."
                actions={
                    <Link to="/bot/ip" className="btn btn-secondary">
                        آی‌پی‌های ربات
                    </Link>
                }
            />
            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label="وضعیت سفارش" items={filters} value={list.values.status} counts={data?.counts} attention="suspended" onChange={(status) => list.update({ status })} />
                <div className="flex flex-1 flex-wrap items-center gap-2 xl:justify-end">
                    {list.values.user && (
                        <button type="button" className="btn btn-secondary" onClick={() => list.update({ user: '' })} aria-label="برداشتن فیلتر: فقط سفارش‌های یک کاربر">
                            فقط سفارش‌های یک کاربر
                            <X aria-hidden size={15} />
                        </button>
                    )}
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی سفارش ربات" placeholder="شماره سفارش، یوزرنیم یا آیدی تلگرام" />
                </div>
            </div>
            <section aria-label="فهرست سفارش‌های ربات" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.orders.length === 0 ? (
                    <EmptyState>سفارشی در این فهرست نیست.</EmptyState>
                ) : (
                    <div className={`overflow-x-auto transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="w-full min-w-[680px]">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">سفارش</th>
                                    <th scope="col" className="px-3 text-start font-medium">خریدار</th>
                                    <th scope="col" className="px-3 text-start font-medium">وضعیت</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">ثبت</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.orders.map((o) => (
                                    <tr key={o.id} className="border-b border-stroke last:border-0 hover:bg-surface-2">
                                        <td className="py-3 ps-5 pe-3">
                                            <Link to={`/bot/orders/${o.id}`} state={{ list: list.here }} className="font-bold underline-offset-4 hover:underline">
                                                <span className="num">#{o.id}</span> {o.title}
                                            </Link>
                                        </td>
                                        <td className="px-3 text-[13px]">
                                            {o.user ? (
                                                <Link to={`/bot/users/${o.user.id}`} dir="auto" className="underline-offset-4 hover:underline">
                                                    {botUserName(o.user)}
                                                </Link>
                                            ) : (
                                                <span className="text-muted">—</span>
                                            )}
                                        </td>
                                        <td className="px-3">
                                            <BotOrderBadge status={o.status} />
                                        </td>
                                        <td dir="ltr" className="num py-3 ps-3 pe-5 text-end text-[13px] text-muted">{o.createdAt}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="سفارش" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
