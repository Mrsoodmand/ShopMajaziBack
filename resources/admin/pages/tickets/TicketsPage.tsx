import { Link } from 'react-router';
import { CircleCheck, Clock, Lock, MessageCircleReply } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { waited } from '../../lib/format';
import { Badge } from '../../components/Status';
import { ErrorState, FilterTabs, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';

export type TicketStatus = 'waiting' | 'answered' | 'closed';

export type TicketSummary = {
    id: number;
    subject: string;
    department: string | null;
    status: TicketStatus;
    statusLabel: string;
    unread: boolean;
    orderId: number | null;
    userName: string | null;
    userMobile: string | null;
    createdAt: string | null;
    updatedAt: string | null;
    updatedTs: number | null;
};

type TicketsPage = { page: number; lastPage: number; total: number; counts: Record<string, number>; tickets: (TicketSummary & { preview: string })[] };

const filters = [
    { id: 'waiting', label: 'منتظر پاسخ' },
    { id: 'answered', label: 'پاسخ داده‌شده' },
    { id: 'closed', label: 'بسته' },
    { id: 'all', label: 'همه' },
];

export function TicketBadge({ status, label }: { status: TicketStatus; label: string }) {
    if (status === 'waiting') return <Badge tone="info" icon={Clock} label={label} />;
    if (status === 'answered') return <Badge tone="success" icon={CircleCheck} label={label} />;
    return <Badge tone="neutral" icon={Lock} label={label} />;
}

export default function TicketsPage() {
    const list = useListParams({ filter: 'waiting', q: '' });
    const { data, error, loading, reload } = useQuery<TicketsPage>(`/admin/api/tickets?${list.query}`);

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="تیکت‌ها" subtitle={list.values.filter === 'waiting' ? 'بیشترین انتظار اول.' : 'آخرین به‌روزرسانی اول.'} />
            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label="فیلتر تیکت‌ها" items={filters} value={list.values.filter} counts={data?.counts} attention="waiting" onChange={(filter) => list.update({ filter })} />
                <div className="flex flex-1 xl:justify-end">
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی تیکت" placeholder="موضوع، نام، موبایل یا شماره" />
                </div>
            </div>
            <section aria-label="فهرست تیکت‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.tickets.length === 0 ? (
                    <EmptyState>{list.values.filter === 'waiting' && !list.values.q ? 'تیکتی منتظر پاسخ نیست.' : 'تیکتی در این فهرست نیست.'}</EmptyState>
                ) : (
                    <ul className={`divide-y divide-stroke transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        {data.tickets.map((t) => {
                            const wait = t.status === 'waiting' ? waited(t.updatedTs) : null;
                            return (
                                <li key={t.id}>
                                    <Link
                                        to={`/tickets/${t.id}`}
                                        state={{ list: list.here }}
                                        className="flex gap-4 px-5 py-3.5 hover:bg-surface-2"
                                    >
                                        <span className="mt-1.5 size-2 shrink-0 rounded-full" style={{ background: t.unread ? 'var(--indigo)' : 'transparent' }} aria-hidden />
                                        <span className="min-w-0 flex-1">
                                            <span className="flex flex-wrap items-center gap-2">
                                                <span className={`truncate ${t.unread ? 'font-bold' : 'font-medium'}`}>{t.subject || 'بدون موضوع'}</span>
                                                {t.unread && <span className="sr-only">(خوانده‌نشده)</span>}
                                                <span className="num text-[12px] text-muted">#{t.id}</span>
                                            </span>
                                            <span className="mt-0.5 block truncate text-[13px] text-muted">{t.preview || '—'}</span>
                                            <span className="mt-1 flex flex-wrap gap-x-3 text-[12px] text-muted">
                                                <span>{t.userName || <span dir="ltr" className="num">{t.userMobile}</span>}</span>
                                                {t.department && <span>{t.department}</span>}
                                                {t.orderId && <span className="num">سفارش #{t.orderId}</span>}
                                            </span>
                                        </span>
                                        <span className="flex shrink-0 flex-col items-end gap-1.5">
                                            <TicketBadge status={t.status} label={t.statusLabel} />
                                            {wait ? (
                                                <span className={`flex items-center gap-1 text-[12px] ${wait.late ? 'font-bold text-error' : 'text-muted'}`}>
                                                    <MessageCircleReply aria-hidden size={13} />
                                                    {wait.text} انتظار
                                                </span>
                                            ) : (
                                                <span dir="ltr" className="num text-[12px] text-muted">{t.updatedAt}</span>
                                            )}
                                        </span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="تیکت" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
