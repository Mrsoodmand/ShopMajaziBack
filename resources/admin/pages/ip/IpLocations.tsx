import { Link } from 'react-router';
import { Globe, Plus, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count, toman } from '../../lib/format';
import { FilterTabs, Row } from '../../components/ui';
import { AttentionBar, PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';
import { LiveBadge } from '../virtual-numbers/parts';
import { useIpScope } from './scope';

export type LocationRow = {
    id: number;
    name: string;
    enName: string;
    slug: string;
    image: string | null;
    live: boolean;
    plans: number;
    fromPrice: number | null;
    servers: number;
    free: number;
    active: number;
};

const views = [
    { id: 'all', label: 'همه' },
    { id: 'live', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
    { id: 'empty', label: 'بدون کلاینت آزاد' },
];

const filters: FilterDef[] = [
    { type: 'toggle', key: 'noplans', label: 'بدون پلن' },
    { type: 'toggle', key: 'noservers', label: 'بدون سرور' },
];

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'name', label: 'نام' },
    { value: 'free', label: 'کمترین کلاینت آزاد' },
    { value: 'active', label: 'بیشترین کلاینت فعال' },
];

function Flag({ src }: { src: string | null }) {
    return (
        <span className="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl bg-surface-2">
            {src ? <img src={src} alt="" className="size-full object-cover" /> : <Globe aria-hidden size={18} className="text-muted" />}
        </span>
    );
}

export default function IpLocations() {
    const scope = useIpScope();
    const list = useListParams({ view: 'all', q: '', sort: '', per: '', noplans: '', noservers: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ waitingOrders: number; locations: LocationRow[] }>(`${scope.api}/locations`);
    const all = data?.locations;
    const shown = useClientList(all, v, list.page, {
        search: (l, q) => [l.name, l.enName, l.slug].some((s) => (s ?? '').toLowerCase().includes(q)),
        filters: {
            view: (l, val) => (val === 'live' ? l.live : val === 'off' ? !l.live : val === 'empty' ? l.free === 0 : true),
            noplans: (l) => l.plans === 0,
            noservers: (l) => l.servers === 0,
        },
        sorts: { name: (a, b) => a.name.localeCompare(b.name, 'fa'), free: (a, b) => a.free - b.free, active: (a, b) => b.active - a.active },
    });
    const counts = all
        ? { all: all.length, live: all.filter((l) => l.live).length, off: all.filter((l) => !l.live).length, empty: all.filter((l) => l.free === 0).length }
        : undefined;

    const columns: Column<LocationRow>[] = [
        {
            key: 'name',
            header: 'کشور',
            primary: true,
            cell: (l) => (
                <span className="flex items-center gap-3">
                    <Flag src={l.image} />
                    <span className="min-w-0">
                        <span className="block truncate font-bold">{l.name}</span>
                        <span dir="ltr" className="latin block truncate text-end text-[12px] text-muted">
                            {l.enName}
                        </span>
                    </span>
                </span>
            ),
        },
        {
            key: 'plans',
            header: 'پلن‌ها',
            cell: (l) => (l.plans ? <span className="text-[13px]"><span className="num">{l.plans}</span> پلن · از <span className="num">{toman(l.fromPrice)}</span></span> : <span className="text-warn">بدون پلن</span>),
        },
        { key: 'servers', header: 'سرور', align: 'end', cell: (l) => <span className="num">{count(l.servers)}</span> },
        { key: 'free', header: 'آزاد', align: 'end', cell: (l) => <span className={`num ${l.free === 0 ? 'font-bold text-warn' : ''}`}>{count(l.free)}</span> },
        { key: 'active', header: 'فعال', align: 'end', cell: (l) => <span className="num">{count(l.active)}</span> },
        { key: 'live', header: 'وضعیت', cell: (l) => <LiveBadge live={l.live} /> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={scope.title}
                subtitle={scope.subtitle}
                actions={
                    <>
                        <Link to={scope.ordersLink} className="btn btn-secondary">
                            سفارش‌ها
                        </Link>
                        {scope.pageLink && (
                            <Link to={scope.pageLink} className="btn btn-secondary">
                                <Settings2 aria-hidden size={16} />
                                متن صفحه
                            </Link>
                        )}
                        <Link to={`${scope.base}/new`} className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            کشور جدید
                        </Link>
                    </>
                }
            />

            {!!data?.waitingOrders && (
                <AttentionBar action={<Link to={scope.ordersLink} className="btn btn-secondary">دیدن سفارش‌ها</Link>}>
                    <span className="num">{count(data.waitingOrders)}</span> سفارش پرداخت‌شده منتظر کلاینت آزاد است. در کشور همان سفارش، از بخش سرورها کلاینت بسازید تا خودکار تحویل شود.
                </AttentionBar>
            )}

            <FilterBar
                views={<FilterTabs label="وضعیت کشورها" items={views} value={v.view} counts={counts} attention="empty" onChange={(view) => list.update({ view })} />}
                search={{ label: 'جستجوی کشور', placeholder: 'نام یا نامک' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="کشور"
                loading={loading}
                onRefresh={reload}
            />

            <DataTable
                label="فهرست کشورها"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(l) => l.id}
                href={(l) => `${scope.base}/${l.id}`}
                linkState={{ list: list.here }}
                rowLabel={(l) => `کشور ${l.name}`}
                rowTone={(l) => (l.live && l.free === 0 ? 'warn' : l.live ? undefined : 'muted')}
                skeleton={6}
                quick={{
                    title: (l) => l.name,
                    subtitle: (l) => <LiveBadge live={l.live} />,
                    body: (l) => (
                        <>
                            <QuickSection title="فروش">
                                <dl className="m-0">
                                    <Row label="پلن‌ها" num>
                                        {l.plans}
                                    </Row>
                                    <Row label="ارزان‌ترین پلن" num>
                                        {toman(l.fromPrice)}
                                    </Row>
                                    <Row label="نامک" ltr>
                                        <span className="latin">{l.slug}</span>
                                    </Row>
                                </dl>
                            </QuickSection>
                            <QuickSection title="سرورها و کلاینت‌ها">
                                <dl className="m-0">
                                    <Row label="سرور" num>
                                        {count(l.servers)}
                                    </Row>
                                    <Row label="کلاینت آزاد" num>
                                        <span className={l.free === 0 ? 'text-warn' : ''}>{count(l.free)}</span>
                                    </Row>
                                    <Row label="کلاینت فعال" num>
                                        {count(l.active)}
                                    </Row>
                                </dl>
                                {l.free === 0 && l.live && <p className="pb-2 text-[13px] text-warn">کلاینت آزادی نمانده؛ سفارش جدید منتظر می‌ماند.</p>}
                            </QuickSection>
                            <div className="flex flex-wrap gap-2">
                                <Link to={`${scope.base}/${l.id}?tab=servers`} className="btn btn-secondary min-h-9">
                                    سرورها
                                </Link>
                                <Link to={`${scope.base}/${l.id}?tab=clients`} className="btn btn-secondary min-h-9">
                                    کلاینت‌ها
                                </Link>
                            </div>
                        </>
                    ),
                }}
                mobile={(l) => (
                    <span className="flex items-center gap-3">
                        <Flag src={l.image} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{l.name}</span>
                            <span className="num block text-[12px] text-muted">
                                {l.servers} سرور · {l.free} آزاد · {l.active} فعال
                            </span>
                        </span>
                        <LiveBadge live={l.live} />
                    </span>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['view'])} onClear={() => list.update({ q: '', noplans: '', noservers: '' })} action={<Link to={`${scope.base}/new`} className="btn btn-primary">کشور جدید</Link>}>
                        هنوز کشوری ساخته نشده است.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="کشور" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}
