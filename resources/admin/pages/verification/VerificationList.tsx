import { Link } from 'react-router';
import { BadgeCheck, CircleCheck, CircleSlash, Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Badge } from '../../components/Status';
import { FilterTabs } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

// Verification products (App\Services\VerificationOrders): ready verified accounts, or verifying the buyer's own.
type Row = {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    kind: 'ready' | 'verify';
    live: boolean;
    currency: 'dollar' | 'toman';
    plans: number;
    livePlans: number;
    inputs: number;
    inMenu: boolean;
    onHome: boolean;
    updatedAt: string | null;
};

export const KIND_LABELS: Record<Row['kind'], string> = { ready: 'اکانت وریفای‌شده', verify: 'وریفای حساب مشتری' };

const views = [
    { id: 'all', label: 'همه' },
    { id: 'live', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
];

const filters: FilterDef[] = [
    { type: 'select', key: 'kind', label: 'نوع', options: Object.entries(KIND_LABELS).map(([value, label]) => ({ value, label })) },
    { type: 'toggle', key: 'noplans', label: 'بدون پلن فعال', hint: 'در سایت چیزی برای خرید ندارند' },
    { type: 'toggle', key: 'menu', label: 'در منو', hint: 'محصولاتی که در منوی بالای سایت هستند' },
    { type: 'toggle', key: 'home', label: 'در صفحه اول', hint: 'محصولاتی که در تب وریفای صفحه اول هستند' },
];

const sorts = [
    { value: '', label: 'ترتیب سایت' },
    { value: 'name', label: 'نام' },
];

function Thumb({ src }: { src: string | null }) {
    return (
        <span className="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl border border-stroke bg-surface-2">
            {src ? <img src={src} alt="" className="size-full object-cover" /> : <BadgeCheck aria-hidden size={18} className="text-muted" />}
        </span>
    );
}

const LiveBadge = ({ live }: { live: boolean }) => (live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />);

export default function VerificationList() {
    const list = useListParams({ view: 'all', q: '', sort: '', per: '', kind: '', noplans: '', menu: '', home: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ products: Row[]; waitingOrders: number }>('/admin/api/verification');
    const all = data?.products;
    const shown = useClientList(all, v, list.page, {
        search: (a, q) => a.name.toLowerCase().includes(q) || a.slug.toLowerCase().includes(q),
        filters: {
            view: (a, val) => (val === 'live' ? a.live : val === 'off' ? !a.live : true),
            kind: (a, val) => a.kind === val,
            noplans: (a) => a.livePlans === 0,
            menu: (a) => a.inMenu,
            home: (a) => a.onHome,
        },
        sorts: { name: (a, b) => a.name.localeCompare(b.name, 'fa') },
    });
    const counts = all ? { all: all.length, live: all.filter((a) => a.live).length, off: all.filter((a) => !a.live).length } : undefined;

    const columns: Column<Row>[] = [
        {
            key: 'name',
            header: 'محصول',
            primary: true,
            cell: (a) => (
                <span className="flex items-center gap-3">
                    <Thumb src={a.image} />
                    <span className="min-w-0">
                        <span className="block truncate font-bold">{a.name}</span>
                        <span dir="ltr" className="latin block truncate text-end text-[12px] text-muted">
                            /verify/{a.slug}
                        </span>
                    </span>
                </span>
            ),
        },
        { key: 'kind', header: 'نوع', cell: (a) => <span className="text-[13px]">{KIND_LABELS[a.kind]}</span> },
        {
            key: 'plans',
            header: 'پلن فعال',
            cell: (a) => (
                <span className={`num ${a.livePlans === 0 ? 'font-medium text-warn' : ''}`}>
                    {count(a.livePlans)} <span className="text-muted">از {count(a.plans)}</span>
                </span>
            ),
        },
        {
            key: 'menu',
            header: 'نمایش',
            from: 'lg',
            cell: (a) => {
                const where = [a.inMenu && 'منو', a.onHome && 'صفحه اول'].filter(Boolean).join('، ');
                return where ? <span className="text-[13px] font-medium">{where}</span> : <span className="text-[13px] text-muted">—</span>;
            },
        },
        { key: 'inputs', header: 'ورودی‌ها', from: 'lg', cell: (a) => <span className="num text-[13px]">{count(a.inputs)}</span> },
        { key: 'live', header: 'وضعیت', cell: (a) => <LiveBadge live={a.live} /> },
        { key: 'updated', header: 'آخرین ویرایش', from: 'lg', cell: (a) => <span dir="ltr" className="num text-[13px] text-muted">{a.updatedAt ?? '—'}</span> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="وریفای"
                subtitle="اکانت‌های وریفای‌شده و وریفای حساب مشتری، با پلن، قیمت و اطلاعاتی که از خریدار می‌خواهید."
                actions={
                    <>
                        <Link to="/orders?type=10&view=all" className="btn btn-secondary">
                            سفارش‌ها
                            {!!data?.waitingOrders && <span className="num text-muted">({count(data.waitingOrders)} در انتظار)</span>}
                        </Link>
                        <Link to="/verification/new" className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            محصول جدید
                        </Link>
                    </>
                }
            />
            <FilterBar
                views={<FilterTabs label="وضعیت محصول" items={views} value={v.view} counts={counts} onChange={(view) => list.update({ view })} />}
                search={{ label: 'جستجوی محصول', placeholder: 'نام یا نامک' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="محصول"
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                rowStatus={(a) => (a.live ? 'success' : 'neutral')}
                offset={shown ? (shown.page - 1) * shown.perPage : 0}
                label="فهرست محصولات وریفای"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(a) => a.id}
                href={(a) => `/verification/${a.id}`}
                linkState={{ list: list.here }}
                rowLabel={(a) => `محصول ${a.name}`}
                rowTone={(a) => (a.live ? undefined : 'muted')}
                skeleton={5}
                mobile={(a) => (
                    <span className="flex items-center gap-3">
                        <Thumb src={a.image} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{a.name}</span>
                            <span className="block text-[12px] text-muted">
                                {KIND_LABELS[a.kind]} · <span className="num">{a.livePlans}</span> پلن فعال
                            </span>
                        </span>
                        <LiveBadge live={a.live} />
                    </span>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['view'])} onClear={() => list.update({ q: '', kind: '', noplans: '', menu: '', home: '' })} action={<Link to="/verification/new" className="btn btn-primary">محصول جدید</Link>}>
                        هنوز محصول وریفای ساخته نشده است.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="محصول" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}
