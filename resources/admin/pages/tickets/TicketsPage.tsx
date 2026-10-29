import { Link } from 'react-router';
import { CircleCheck, Clock, FileText, Lock, MessageCircleReply } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { waited } from '../../lib/format';
import { Badge } from '../../components/Status';
import { FilterTabs, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, isFiltered, type Column, type FilterDef } from '../../components/DataList';

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

type TicketRow = TicketSummary & { preview: string };
type TicketsPage = {
    page: number;
    lastPage: number;
    total: number;
    perPage: number;
    counts: Record<string, number>;
    departments: { value: string; label: string }[];
    tickets: TicketRow[];
};
type Detail = {
    ticket: TicketSummary & { user: { id: number; name: string | null; mobile: string | null; email: string | null } | null };
    messages: { id: number; from: 'user' | 'admin'; operator: string | null; text: string; file: string | null; createdAt: string | null }[];
};

const views = [
    { id: 'waiting', label: 'منتظر پاسخ' },
    { id: 'answered', label: 'پاسخ داده‌شده' },
    { id: 'closed', label: 'بسته' },
    { id: 'all', label: 'همه' },
];

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'updated_desc', label: 'آخرین به‌روزرسانی' },
    { value: 'updated_asc', label: 'بیشترین انتظار' },
    { value: 'newest', label: 'جدیدترین تیکت' },
    { value: 'oldest', label: 'قدیمی‌ترین تیکت' },
];

export function TicketBadge({ status, label }: { status: TicketStatus; label: string }) {
    if (status === 'waiting') return <Badge tone="info" icon={Clock} label={label} />;
    if (status === 'answered') return <Badge tone="success" icon={CircleCheck} label={label} />;
    return <Badge tone="neutral" icon={Lock} label={label} />;
}

function Waiting({ t }: { t: TicketRow }) {
    const wait = t.status === 'waiting' ? waited(t.updatedTs) : null;
    if (!wait) return <span dir="ltr" className="num text-[13px] text-muted">{t.updatedAt}</span>;
    return (
        <span className={`inline-flex items-center gap-1 text-[13px] whitespace-nowrap ${wait.late ? 'font-bold text-error' : 'text-body'}`}>
            <MessageCircleReply aria-hidden size={14} />
            {wait.text} انتظار
        </span>
    );
}

export default function TicketsPage() {
    const list = useListParams({ filter: 'waiting', q: '', sort: '', per: '', department: '', unread: '', order: '', from: '', to: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<TicketsPage>(`/admin/api/tickets?${list.query}`);
    const filters: FilterDef[] = [
        { type: 'select', key: 'department', label: 'بخش', any: 'همه بخش‌ها', options: data?.departments ?? [] },
        { type: 'dates', label: 'تاریخ ثبت' },
        { type: 'toggle', key: 'unread', label: 'خوانده‌نشده', hint: 'پیام تازه‌ای که هنوز باز نشده' },
        { type: 'toggle', key: 'order', label: 'مربوط به سفارش', hint: 'تیکت‌هایی که شماره سفارش دارند' },
    ];

    const columns: Column<TicketRow>[] = [
        {
            key: 'subject',
            header: 'موضوع',
            primary: true,
            cell: (t) => (
                <span className="flex min-w-0 items-start gap-2.5">
                    <span aria-hidden className={`mt-2 size-2 shrink-0 rounded-full ${t.unread ? 'bg-indigo' : 'bg-transparent'}`} />
                    <span className="min-w-0">
                        <span className={`block max-w-[380px] truncate ${t.unread ? 'font-bold' : 'font-medium'}`}>
                            {t.subject || 'بدون موضوع'}
                            {t.unread && <span className="sr-only"> (خوانده‌نشده)</span>}
                        </span>
                        <span className="block max-w-[380px] truncate text-[13px] text-muted">{t.preview || '—'}</span>
                    </span>
                </span>
            ),
        },
        {
            key: 'user',
            header: 'کاربر',
            cell: (t) => (
                <span className="leading-snug">
                    <span className="block max-w-[160px] truncate">{t.userName || '—'}</span>
                    <span dir="ltr" className="num block text-end text-[12px] text-muted">
                        {t.userMobile}
                    </span>
                </span>
            ),
        },
        { key: 'department', header: 'بخش', from: 'lg', cell: (t) => <span className="text-[13px]">{t.department ?? '—'}</span> },
        {
            key: 'order',
            header: 'سفارش',
            from: 'xl',
            cell: (t) =>
                t.orderId ? (
                    <Link to={`/orders/${t.orderId}`} className="num underline-offset-4 hover:underline">
                        #{t.orderId}
                    </Link>
                ) : (
                    <span className="text-muted">—</span>
                ),
        },
        { key: 'status', header: 'وضعیت', cell: (t) => <TicketBadge status={t.status} label={t.statusLabel} /> },
        { key: 'time', header: 'آخرین پیام', cell: (t) => <Waiting t={t} /> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="تیکت‌ها" subtitle={v.filter === 'waiting' && !v.sort ? 'بیشترین انتظار اول.' : 'آخرین به‌روزرسانی اول، مگر ترتیب دیگری انتخاب کنید.'} />
            <FilterBar
                views={<FilterTabs label="فیلتر تیکت‌ها" items={views} value={v.filter} counts={data?.counts} attention="waiting" onChange={(filter) => list.update({ filter })} />}
                search={{ label: 'جستجوی تیکت', placeholder: 'موضوع، نام، موبایل یا شماره' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={data?.total}
                noun="تیکت"
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                label="فهرست تیکت‌ها"
                rows={data?.tickets}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(t) => t.id}
                href={(t) => `/tickets/${t.id}`}
                linkState={{ list: list.here }}
                rowLabel={(t) => `تیکت ${t.id}`}
                quick={{
                    title: (t) => t.subject || 'بدون موضوع',
                    subtitle: (t) => (
                        <span className="flex flex-wrap items-center gap-2">
                            <TicketBadge status={t.status} label={t.statusLabel} />
                            <span className="num text-muted">#{t.id}</span>
                            {t.department && <span>{t.department}</span>}
                        </span>
                    ),
                    body: (t) => <TicketQuick id={t.id} />,
                }}
                mobile={(t) => (
                    <>
                        <div className="flex items-start justify-between gap-2">
                            <span className={`min-w-0 truncate ${t.unread ? 'font-bold' : 'font-medium'}`}>{t.subject || 'بدون موضوع'}</span>
                            <TicketBadge status={t.status} label={t.statusLabel} />
                        </div>
                        <p className="mt-0.5 truncate text-[13px] text-muted">{t.preview || '—'}</p>
                        <div className="mt-1 flex flex-wrap items-center justify-between gap-2 text-[12px] text-muted">
                            <span>{t.userName || t.userMobile}</span>
                            <Waiting t={t} />
                        </div>
                    </>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['filter'])} onClear={() => list.update({ q: '', department: '', unread: '', order: '', from: '', to: '' })}>
                        {v.filter === 'waiting' ? 'تیکتی منتظر پاسخ نیست.' : 'تیکتی در این فهرست نیست.'}
                    </ListEmpty>
                }
            />
            {data && (
                <ListPager
                    page={data.page}
                    lastPage={data.lastPage}
                    total={data.total}
                    perPage={data.perPage}
                    noun="تیکت"
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(n) => list.update({ per: String(n) })}
                />
            )}
        </div>
    );
}

function TicketQuick({ id }: { id: number }) {
    const { data, error } = useQuery<Detail>(`/admin/api/tickets/${id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!data) return <QuickSkeleton />;
    const { ticket, messages } = data;
    return (
        <>
            <QuickSection title="کاربر">
                {ticket.user ? (
                    <dl className="m-0">
                        <Row label="نام">
                            <Link to={`/users/${ticket.user.id}`} className="text-indigo underline-offset-4 hover:underline">
                                {ticket.user.name || 'بدون نام'}
                            </Link>
                        </Row>
                        <Row label="موبایل" ltr>
                            {ticket.user.mobile}
                        </Row>
                        {ticket.orderId && (
                            <Row label="سفارش">
                                <Link to={`/orders/${ticket.orderId}`} className="num text-indigo underline-offset-4 hover:underline">
                                    #{ticket.orderId}
                                </Link>
                            </Row>
                        )}
                    </dl>
                ) : (
                    <p className="pb-2 text-muted">کاربر این تیکت پیدا نشد.</p>
                )}
            </QuickSection>
            <QuickSection title={`گفتگو (${messages.length})`}>
                <ol className="space-y-2.5 pb-2">
                    {messages.slice(-6).map((m) => (
                        <li key={m.id} className={`rounded-xl px-3 py-2 ${m.from === 'admin' ? 'bg-indigo-soft' : 'bg-surface-2'}`}>
                            <span className="mb-0.5 flex justify-between gap-2 text-[12px] text-muted">
                                <span className="font-bold text-body">{m.from === 'admin' ? m.operator : ticket.user?.name || 'کاربر'}</span>
                                <span dir="ltr" className="num">
                                    {m.createdAt}
                                </span>
                            </span>
                            <p className="line-clamp-4 break-words whitespace-pre-wrap" dir="auto">
                                {m.text}
                            </p>
                            {m.file && (
                                <a href={m.file} target="_blank" rel="noreferrer" className="mt-1 inline-flex items-center gap-1 text-[12px] font-medium underline">
                                    <FileText aria-hidden size={13} />
                                    پیوست
                                </a>
                            )}
                        </li>
                    ))}
                </ol>
                {messages.length > 6 && <p className="pb-2 text-[12px] text-muted">پیام‌های قبلی در صفحه کامل تیکت است.</p>}
            </QuickSection>
        </>
    );
}
