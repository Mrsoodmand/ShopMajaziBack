import { Link } from 'react-router';
import { AppWindow, CircleCheck, CircleSlash, ExternalLink, Plus, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count, toman } from '../../lib/format';
import { Badge } from '../../components/Status';
import { FilterTabs, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

type AccountRow = {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    live: boolean;
    currency: 'dollar' | 'toman';
    plans: number;
    livePlans: number;
    updatedAt: string | null;
};

type AccountDetail = { plans: { id?: number; name: string; price: string; discount: string; live: boolean }[]; fields: { title: string }[]; short: string; withPlans: boolean };

const views = [
    { id: 'all', label: 'همه' },
    { id: 'live', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
];

const filters: FilterDef[] = [
    {
        type: 'select',
        key: 'currency',
        label: 'ارز قیمت',
        options: [
            { value: 'dollar', label: 'دلاری' },
            { value: 'toman', label: 'تومانی' },
        ],
    },
    { type: 'toggle', key: 'noplans', label: 'بدون پلن فعال', hint: 'در سایت چیزی برای خرید ندارند' },
];

const sorts = [
    { value: '', label: 'ترتیب سایت' },
    { value: 'name', label: 'نام' },
    { value: 'plans', label: 'بیشترین پلن فعال' },
];

function Thumb({ src }: { src: string | null }) {
    return (
        <span className="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl border border-stroke bg-surface-2">
            {src ? <img src={src} alt="" className="size-full object-cover" /> : <AppWindow aria-hidden size={18} className="text-muted" />}
        </span>
    );
}

const LiveBadge = ({ live }: { live: boolean }) => (live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />);

export default function AccountsList() {
    const list = useListParams({ view: 'all', q: '', sort: '', per: '', currency: '', noplans: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ accounts: AccountRow[] }>('/admin/api/accounts');
    const all = data?.accounts;
    const shown = useClientList(all, v, list.page, {
        search: (a, q) => a.name.toLowerCase().includes(q) || a.slug.toLowerCase().includes(q),
        filters: {
            view: (a, val) => (val === 'live' ? a.live : val === 'off' ? !a.live : true),
            currency: (a, val) => a.currency === val,
            noplans: (a) => a.livePlans === 0,
        },
        sorts: { name: (a, b) => a.name.localeCompare(b.name, 'fa'), plans: (a, b) => b.livePlans - a.livePlans },
    });
    const counts = all ? { all: all.length, live: all.filter((a) => a.live).length, off: all.filter((a) => !a.live).length } : undefined;

    const columns: Column<AccountRow>[] = [
        {
            key: 'name',
            header: 'اکانت',
            primary: true,
            cell: (a) => (
                <span className="flex items-center gap-3">
                    <Thumb src={a.image} />
                    <span className="min-w-0">
                        <span className="block truncate font-bold">{a.name}</span>
                        <span dir="ltr" className="latin block truncate text-end text-[12px] text-muted">
                            /accounts/{a.slug}
                        </span>
                    </span>
                </span>
            ),
        },
        {
            key: 'plans',
            header: 'پلن فعال',
            cell: (a) => (
                <span className={`num ${a.livePlans === 0 ? 'font-medium text-warn' : ''}`}>
                    {count(a.livePlans)} <span className="text-muted">از {count(a.plans)}</span>
                </span>
            ),
        },
        { key: 'currency', header: 'ارز', cell: (a) => <span className="text-[13px]">{a.currency === 'dollar' ? 'دلاری' : 'تومانی'}</span> },
        { key: 'live', header: 'وضعیت', cell: (a) => <LiveBadge live={a.live} /> },
        { key: 'updated', header: 'آخرین ویرایش', from: 'lg', cell: (a) => <span dir="ltr" className="num text-[13px] text-muted">{a.updatedAt ?? '—'}</span> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="اکانت‌های خارجی"
                subtitle="اشتراک‌ها و اکانت سایت‌های خارجی، با پلن‌ها و فیلدهایی که خریدار پر می‌کند."
                actions={
                    <>
                        <Link to="/orders?type=7&view=all" className="btn btn-secondary">
                            سفارش‌ها
                        </Link>
                        <Link to="/pages/7" className="btn btn-secondary">
                            <Settings2 aria-hidden size={16} />
                            متن صفحه
                        </Link>
                        <Link to="/accounts/new" className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            اکانت جدید
                        </Link>
                    </>
                }
            />
            <FilterBar
                views={<FilterTabs label="وضعیت اکانت" items={views} value={v.view} counts={counts} onChange={(view) => list.update({ view })} />}
                search={{ label: 'جستجوی اکانت', placeholder: 'نام یا نامک' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="اکانت"
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                label="فهرست اکانت‌ها"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(a) => a.id}
                href={(a) => `/accounts/${a.id}`}
                linkState={{ list: list.here }}
                rowLabel={(a) => `اکانت ${a.name}`}
                rowTone={(a) => (a.live ? undefined : 'muted')}
                skeleton={5}
                quick={{
                    title: (a) => a.name,
                    subtitle: (a) => <LiveBadge live={a.live} />,
                    body: (a) => <AccountQuick row={a} />,
                }}
                mobile={(a) => (
                    <span className="flex items-center gap-3">
                        <Thumb src={a.image} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{a.name}</span>
                            <span className="num block text-[12px] text-muted">
                                {a.livePlans} پلن فعال از {a.plans}
                            </span>
                        </span>
                        <LiveBadge live={a.live} />
                    </span>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['view'])} onClear={() => list.update({ q: '', currency: '', noplans: '' })} action={<Link to="/accounts/new" className="btn btn-primary">اکانت جدید</Link>}>
                        هنوز اکانتی ساخته نشده است.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="اکانت" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}

function AccountQuick({ row }: { row: AccountRow }) {
    const { data, error } = useQuery<{ account: AccountDetail }>(`/admin/api/accounts/${row.id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!data) return <QuickSkeleton />;
    const a = data.account;
    const unit = row.currency === 'dollar' ? 'دلار' : 'تومان';
    return (
        <>
            {a.short && <p className="mb-4 text-body">{a.short}</p>}
            <QuickSection
                title="پلن‌ها"
                action={
                    row.live ? (
                        <a href={`/accounts/${row.slug}`} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-[13px] font-medium text-indigo hover:underline">
                            <ExternalLink aria-hidden size={13} />
                            دیدن در سایت
                        </a>
                    ) : undefined
                }
            >
                {!a.withPlans ? (
                    <p className="pb-2 text-muted">این اکانت بدون پلن فروخته می‌شود.</p>
                ) : a.plans.length === 0 ? (
                    <p className="pb-2 text-warn">پلنی ندارد.</p>
                ) : (
                    <dl className="m-0">
                        {a.plans.map((p, i) => (
                            <Row key={p.id ?? i} label={p.name || `پلن ${i + 1}`} num>
                                <span className={p.live ? '' : 'text-muted line-through'}>
                                    {unit === 'تومان' ? toman(Number(p.price)) : `${p.price} ${unit}`}
                                </span>
                            </Row>
                        ))}
                    </dl>
                )}
            </QuickSection>
            {a.fields.length > 0 && (
                <QuickSection title="آنچه خریدار پر می‌کند">
                    <ul className="flex flex-wrap gap-1.5 pb-2">
                        {a.fields.map((f, i) => (
                            <li key={i} className="rounded-md bg-surface-2 px-2 py-0.5 text-[13px]">
                                {f.title}
                            </li>
                        ))}
                    </ul>
                </QuickSection>
            )}
        </>
    );
}
