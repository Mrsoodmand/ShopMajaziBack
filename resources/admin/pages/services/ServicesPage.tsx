import { Link, useSearchParams } from 'react-router';
import { EyeOff, Pencil, Plus, Star } from 'lucide-react';
import { useQuery, type ApiError } from '../../lib/api';
import { toman } from '../../lib/format';
import { ErrorState, Row } from '../../components/ui';
import { useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, QuickSection, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';
import { platformInitial, type Platform, type ServiceRow } from './types';

export function PlatformTile({ platform, size = 36 }: { platform: Pick<Platform, 'name' | 'enName' | 'image' | 'color'>; size?: number }) {
    return (
        <span
            aria-hidden
            className="grid shrink-0 place-items-center overflow-hidden rounded-[10px] font-bold text-white"
            style={{ width: size, height: size, background: platform.image ? 'var(--surface-2)' : platform.color || '#475569' }}
        >
            {platform.image ? <img src={platform.image} alt="" className="size-full object-contain p-1" /> : platformInitial(platform)}
        </span>
    );
}

export default function ServicesPage() {
    const [params, setParams] = useSearchParams();
    const cats = useQuery<{ categories: Platform[] }>('/admin/api/service-categories');
    const platforms = cats.data?.categories ?? [];
    const selectedId = Number(params.get('platform')) || platforms[0]?.id;
    const selected = platforms.find((p) => p.id === selectedId);
    const services = useQuery<{ services: ServiceRow[]; rate: number | null }>(selected ? `/admin/api/services?category=${selected.id}` : null);

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">خدمات مجازی</h1>
                    <p className="mt-1 text-muted">پلتفرم‌ها و سرویس‌هایی که در صفحه خدمات شبکه‌های اجتماعی سایت فروخته می‌شوند.</p>
                </div>
                <Link to="/services/platforms/new" className="btn btn-secondary">
                    <Plus aria-hidden size={16} />
                    پلتفرم جدید
                </Link>
            </div>

            {cats.error ? (
                <ErrorState error={cats.error} onRetry={cats.reload} boxed />
            ) : (
                <div className="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                    <nav aria-label="پلتفرم‌ها" className="rounded-2xl border border-stroke bg-surface p-2">
                        {!cats.data ? (
                            <div className="space-y-2 p-2">
                                {[0, 1, 2].map((i) => (
                                    <div key={i} className="skeleton h-12" />
                                ))}
                            </div>
                        ) : platforms.length === 0 ? (
                            <p className="p-3 text-muted">هنوز پلتفرمی ساخته نشده است.</p>
                        ) : (
                            <ul className="space-y-0.5">
                                {platforms.map((p) => {
                                    const active = p.id === selectedId;
                                    return (
                                        <li key={p.id}>
                                            <button
                                                type="button"
                                                aria-current={active ? 'true' : undefined}
                                                onClick={() => setParams({ platform: String(p.id) })}
                                                className={`flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start transition-colors ${active ? 'bg-surface-2' : 'hover:bg-surface-2'}`}
                                            >
                                                <PlatformTile platform={p} />
                                                <span className="min-w-0 flex-1">
                                                    <span className={`flex items-center gap-1.5 ${active ? 'font-bold' : 'font-medium'}`}>
                                                        <span className="truncate">{p.name}</span>
                                                        {p.featured && <Star aria-label="ویژه" size={13} className="shrink-0 fill-current text-warn" />}
                                                        {!p.visible && <EyeOff aria-label="پنهان در سایت" size={13} className="shrink-0 text-muted" />}
                                                    </span>
                                                    <span className="num block text-[13px] text-muted">
                                                        {p.services} سرویس · {p.liveServices} فعال
                                                    </span>
                                                </span>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </nav>

                    <section aria-labelledby="platform-services" className="min-w-0 rounded-2xl border border-stroke bg-surface">
                        {selected && (
                            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-stroke px-5 py-4">
                                <div className="flex min-w-0 items-center gap-3">
                                    <PlatformTile platform={selected} size={40} />
                                    <div className="min-w-0">
                                        <h2 id="platform-services" className="text-[16px] font-bold">
                                            سرویس‌های {selected.name}
                                        </h2>
                                        <p dir="ltr" className="latin text-end text-[13px] text-muted">
                                            /virtual-service/{selected.slug}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <Link to={`/services/platforms/${selected.id}`} className="btn btn-ghost">
                                        <Pencil aria-hidden size={15} />
                                        تنظیمات پلتفرم
                                    </Link>
                                    <Link to={`/services/new?platform=${selected.id}`} className="btn btn-primary">
                                        <Plus aria-hidden size={16} />
                                        سرویس جدید
                                    </Link>
                                </div>
                            </div>
                        )}
                        {!selected && cats.data ? (
                            <p className="px-5 py-12 text-center text-muted">یک پلتفرم بسازید تا سرویس‌هایش را اضافه کنید.</p>
                        ) : (
                            <div className="p-4">
                                <ServicesTable rows={services.data?.services} rate={services.data?.rate ?? null} loading={services.loading} error={services.error} onRetry={services.reload} platformId={selected?.id} />
                            </div>
                        )}
                    </section>
                </div>
            )}
        </div>
    );
}

const serviceFilters: FilterDef[] = [
    {
        type: 'select',
        key: 'pricing',
        label: 'قیمت‌گذاری',
        options: [
            { value: 'count', label: 'تعداد دلخواه' },
            { value: 'plans', label: 'پلن‌های ثابت' },
        ],
    },
    { type: 'toggle', key: 'noprice', label: 'بدون قیمت', hint: 'سرویس‌هایی که در سایت خریدنی نیستند' },
];

const serviceSorts = [
    { value: '', label: 'ترتیب سایت' },
    { value: 'name', label: 'نام' },
    { value: 'price_desc', label: 'گران‌ترین' },
    { value: 'price_asc', label: 'ارزان‌ترین' },
];

const serviceViews = [
    { id: '', label: 'همه' },
    { id: 'on', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
] as const;

function ServiceState({ live }: { live: boolean }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-[13px] font-medium ${live ? 'bg-emerald-500/12 text-emerald-800 dark:text-emerald-300' : 'bg-surface-2 text-muted'}`}>
            {live ? 'فعال' : 'غیرفعال'}
        </span>
    );
}

/** One platform's services. The platform stays in ?platform=; the list's own filters sit beside it in the address. */
function ServicesTable({
    rows,
    rate,
    loading,
    error,
    onRetry,
    platformId,
}: {
    rows: ServiceRow[] | undefined;
    rate: number | null;
    loading: boolean;
    error: ApiError | null;
    onRetry: () => void;
    platformId?: number;
}) {
    const list = useListParams({ platform: '', live: '', q: '', sort: '', per: '', pricing: '', noprice: '' });
    const v = list.values;
    const shown = useClientList(rows, v, list.page, {
        search: (r, q) => [r.name, r.enName, r.slug].some((x) => (x ?? '').toLowerCase().includes(q)),
        filters: {
            live: (r, val) => (val === 'on' ? r.live : !r.live),
            pricing: (r, val) => r.pricing === val,
            noprice: (r) => r.tomanPer1000 == null,
        },
        sorts: {
            name: (a, b) => (a.name ?? a.slug).localeCompare(b.name ?? b.slug, 'fa'),
            price_desc: (a, b) => (b.tomanPer1000 ?? -1) - (a.tomanPer1000 ?? -1),
            price_asc: (a, b) => (a.tomanPer1000 ?? Infinity) - (b.tomanPer1000 ?? Infinity),
        },
        per: 100,
    });
    const counts: Record<string, number> | null = rows ? { '': rows.length, on: rows.filter((r) => r.live).length, off: rows.filter((r) => !r.live).length } : null;
    const price = (s: ServiceRow) => (s.tomanPer1000 != null ? toman(s.tomanPer1000) : <span className="text-warn">{rate ? 'قیمت ثبت نشده' : 'نرخ دلار تنظیم نشده'}</span>);

    const columns: Column<ServiceRow>[] = [
        {
            key: 'name',
            header: 'سرویس',
            primary: true,
            cell: (s) => (
                <span className="leading-snug">
                    <span className="block font-bold">{s.name || s.slug}</span>
                    <span dir="ltr" className="latin block text-end text-[12px] text-muted">
                        {s.slug}
                    </span>
                </span>
            ),
        },
        { key: 'pricing', header: 'قیمت‌گذاری', from: 'lg', cell: (s) => (s.pricing === 'count' ? 'تعداد دلخواه' : `${s.plans} پلن`) },
        { key: 'price', header: 'هر ۱۰۰۰ عدد', align: 'end', className: 'num whitespace-nowrap', cell: price },
        { key: 'live', header: 'وضعیت', cell: (s) => <ServiceState live={s.live} /> },
    ];

    return (
        <>
            <FilterBar
                views={
                    <nav aria-label="وضعیت سرویس" className="flex gap-1">
                        {serviceViews.map((t) => {
                            const active = v.live === t.id;
                            return (
                                <button
                                    key={t.id}
                                    type="button"
                                    aria-pressed={active}
                                    onClick={() => list.update({ live: t.id })}
                                    className={`flex h-9 items-center gap-2 rounded-lg px-3 text-[14px] transition-colors ${active ? 'bg-ink font-medium text-surface' : 'text-body hover:bg-surface-2'}`}
                                >
                                    {t.label}
                                    <span className={`num min-w-6 rounded-md px-1.5 text-center text-[12px] ${active ? 'bg-surface/20' : 'bg-surface-2 text-muted'}`}>{counts ? counts[t.id] : '…'}</span>
                                </button>
                            );
                        })}
                    </nav>
                }
                search={{ label: 'جستجوی سرویس', placeholder: 'نام یا نامک' }}
                filters={serviceFilters}
                sort={{ options: serviceSorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="سرویس"
                loading={loading}
                onRefresh={onRetry}
            />
            <DataTable
                label="سرویس‌های پلتفرم"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={onRetry}
                columns={columns}
                rowKey={(s) => s.id}
                href={(s) => `/services/${s.id}`}
                linkState={{ list: list.here }}
                rowLabel={(s) => `سرویس ${s.name || s.slug}`}
                rowTone={(s) => (s.live ? undefined : 'muted')}
                skeleton={4}
                quick={{
                    title: (s) => s.name || s.slug,
                    subtitle: (s) => <ServiceState live={s.live} />,
                    body: (s) => (
                        <QuickSection title="قیمت">
                            <dl className="m-0">
                                <Row label="قیمت‌گذاری">{s.pricing === 'count' ? 'تعداد دلخواه (مضرب ۵۰۰)' : `${s.plans} پلن ثابت`}</Row>
                                {s.pricing === 'count' ? (
                                    <Row label="دلار هر ۵۰۰" num>
                                        {s.dollarPer500 ?? '—'}
                                    </Row>
                                ) : (
                                    <Row label="دلار هر ۱۰۰۰" num>
                                        {s.dollarPer1000 ?? '—'}
                                    </Row>
                                )}
                                <Row label="تومان هر ۱۰۰۰" num>
                                    {price(s)}
                                </Row>
                                <Row label="نامک" ltr>
                                    <span className="latin">{s.slug}</span>
                                </Row>
                            </dl>
                        </QuickSection>
                    ),
                }}
                mobile={(s) => (
                    <span className="flex items-center justify-between gap-3">
                        <span className="min-w-0">
                            <span className="block truncate font-bold">{s.name || s.slug}</span>
                            <span className="num block text-[12px] text-muted">{price(s)}</span>
                        </span>
                        <ServiceState live={s.live} />
                    </span>
                )}
                empty={
                    <ListEmpty
                        filtered={isFiltered(v, ['platform'])}
                        onClear={() => list.update({ q: '', live: '', pricing: '', noprice: '' })}
                        action={
                            platformId ? (
                                <Link to={`/services/new?platform=${platformId}`} className="btn btn-primary">
                                    سرویس جدید
                                </Link>
                            ) : undefined
                        }
                    >
                        این پلتفرم هنوز سرویسی ندارد؛ تا سرویس فعالی نداشته باشد، در سایت نمایش داده نمی‌شود.
                    </ListEmpty>
                }
            />
        </>
    );
}
