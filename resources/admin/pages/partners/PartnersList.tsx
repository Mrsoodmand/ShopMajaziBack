import { Link } from 'react-router';
import { CircleCheck, CircleSlash, Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count, toman } from '../../lib/format';
import { Badge } from '../../components/Status';
import { FilterTabs, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

export type ServiceOption = { id: number; label: string };
type PartnerRow = {
    id: number;
    name: string;
    mobile: string;
    live: boolean;
    percent: number;
    serviceIds: number[];
    wallet: number;
    paidOut: number;
    orders: number;
    earned: number;
    deposit: 'daily' | 'weekly' | 'monthly';
};
type PartnerDetail = {
    partner: { lastPaidAt: string | null; createdAt: string | null };
    profits: { id: number; orderId: number | null; service: string; profit: number; at: string | null }[];
    settlements: { id: number; amount: number; note: string; at: string | null }[];
};

export const DEPOSIT_LABEL = { daily: 'روزانه', weekly: 'هفتگی', monthly: 'ماهانه' } as const;

const views = [
    { id: 'all', label: 'همه' },
    { id: 'live', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
];
const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'wallet', label: 'بیشترین طلب' },
    { value: 'earned', label: 'بیشترین سهم کل' },
    { value: 'orders', label: 'بیشترین سفارش' },
];

const LiveBadge = ({ live }: { live: boolean }) => (live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />);

export default function PartnersList() {
    const list = useListParams({ view: 'all', q: '', sort: '', per: '', service: '', deposit: '', owed: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ services: ServiceOption[]; partners: PartnerRow[] }>('/admin/api/partners');
    const label = (id: number) => data?.services.find((s) => s.id === id)?.label ?? `خدمت ${id}`;
    const filters: FilterDef[] = [
        { type: 'select', key: 'service', label: 'خدمت', any: 'همه خدمت‌ها', options: (data?.services ?? []).map((s) => ({ value: String(s.id), label: s.label })) },
        { type: 'select', key: 'deposit', label: 'دوره تسویه', options: Object.entries(DEPOSIT_LABEL).map(([value, label]) => ({ value, label })) },
        { type: 'toggle', key: 'owed', label: 'طلب تسویه‌نشده', hint: 'همکارانی که کیف پولشان موجودی دارد' },
    ];
    const all = data?.partners;
    const shown = useClientList(all, v, list.page, {
        search: (p, q) => p.name.toLowerCase().includes(q) || p.mobile.includes(q),
        filters: {
            view: (p, val) => (val === 'live' ? p.live : val === 'off' ? !p.live : true),
            service: (p, val) => p.serviceIds.includes(Number(val)),
            deposit: (p, val) => p.deposit === val,
            owed: (p) => p.wallet > 0,
        },
        sorts: { wallet: (a, b) => b.wallet - a.wallet, earned: (a, b) => b.earned - a.earned, orders: (a, b) => b.orders - a.orders },
    });
    const counts = all ? { all: all.length, live: all.filter((p) => p.live).length, off: all.filter((p) => !p.live).length } : undefined;

    const columns: Column<PartnerRow>[] = [
        {
            key: 'name',
            header: 'همکار',
            primary: true,
            cell: (p) => (
                <span className="leading-snug">
                    <span className="block font-bold">{p.name}</span>
                    <span dir="ltr" className="num block text-end text-[13px] text-muted">
                        {p.mobile}
                    </span>
                </span>
            ),
        },
        { key: 'services', header: 'خدمت‌ها', cell: (p) => <span className="block max-w-[240px] truncate text-[13px]">{p.serviceIds.map(label).join('، ') || '—'}</span> },
        { key: 'percent', header: 'سهم', align: 'end', cell: (p) => <span className="num">{p.percent}٪</span> },
        { key: 'wallet', header: 'طلب فعلی', align: 'end', cell: (p) => <span className={`num whitespace-nowrap ${p.wallet ? 'font-bold' : 'text-muted'}`}>{toman(p.wallet)}</span> },
        {
            key: 'earned',
            header: 'کل سهم',
            align: 'end',
            from: 'lg',
            cell: (p) => (
                <span className="text-[13px] text-muted">
                    <span className="num">{toman(p.earned)}</span>
                    <span className="block">
                        <span className="num">{p.orders}</span> سفارش · تسویه {DEPOSIT_LABEL[p.deposit]}
                    </span>
                </span>
            ),
        },
        { key: 'live', header: 'وضعیت', cell: (p) => <LiveBadge live={p.live} /> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="همکاران"
                subtitle="تأمین‌کننده‌هایی که از هر سفارش پرداخت‌شده خدمت خودشان درصدی سهم می‌گیرند. سهم به کیف پول همکار می‌رود تا تسویه شود."
                actions={
                    <Link to="/partners/new" className="btn btn-primary">
                        <Plus aria-hidden size={16} />
                        همکار جدید
                    </Link>
                }
            />
            <FilterBar
                views={<FilterTabs label="وضعیت همکار" items={views} value={v.view} counts={counts} onChange={(view) => list.update({ view })} />}
                search={{ label: 'جستجوی همکار', placeholder: 'نام یا موبایل' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="همکار"
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                label="فهرست همکاران"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(p) => p.id}
                href={(p) => `/partners/${p.id}`}
                linkState={{ list: list.here }}
                rowLabel={(p) => `همکار ${p.name}`}
                skeleton={3}
                quick={{
                    title: (p) => p.name,
                    subtitle: (p) => <LiveBadge live={p.live} />,
                    body: (p) => <PartnerQuick row={p} label={label} />,
                }}
                mobile={(p) => (
                    <>
                        <div className="flex items-center justify-between gap-2">
                            <span className="truncate font-bold">{p.name}</span>
                            <span className="num shrink-0 font-bold">{toman(p.wallet)}</span>
                        </div>
                        <div className="mt-1 flex items-center justify-between gap-2 text-[13px] text-muted">
                            <span className="num">{p.percent}٪ سهم</span>
                            <LiveBadge live={p.live} />
                        </div>
                    </>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['view'])} onClear={() => list.update({ q: '', service: '', deposit: '', owed: '' })} action={<Link to="/partners/new" className="btn btn-primary">همکار جدید</Link>}>
                        هنوز همکاری ثبت نشده است.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="همکار" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}

function PartnerQuick({ row, label }: { row: PartnerRow; label: (id: number) => string }) {
    const { data, error } = useQuery<PartnerDetail>(`/admin/api/partners/${row.id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!data) return <QuickSkeleton />;
    return (
        <>
            <QuickSection title="حساب">
                <dl className="m-0">
                    <Row label="طلب فعلی" num>
                        {toman(row.wallet)}
                    </Row>
                    <Row label="تسویه‌شده" num>
                        {toman(row.paidOut)}
                    </Row>
                    <Row label="آخرین تسویه" ltr>
                        {data.partner.lastPaidAt ?? '—'}
                    </Row>
                    <Row label="دوره تسویه">{DEPOSIT_LABEL[row.deposit]}</Row>
                    <Row label="خدمت‌ها">{row.serviceIds.map(label).join('، ') || '—'}</Row>
                </dl>
            </QuickSection>
            <QuickSection title={`آخرین سهم‌ها (${count(data.profits.length)})`}>
                {data.profits.length === 0 ? (
                    <p className="pb-2 text-muted">هنوز سهمی ثبت نشده است.</p>
                ) : (
                    <dl className="m-0">
                        {data.profits.slice(0, 6).map((p) => (
                            <Row key={p.id} label={p.orderId ? `سفارش #${p.orderId} · ${p.service}` : p.service} num>
                                {toman(p.profit)}
                            </Row>
                        ))}
                    </dl>
                )}
            </QuickSection>
        </>
    );
}
