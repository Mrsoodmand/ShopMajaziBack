import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router';
import { Plus, Star } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count } from '../../lib/format';
import { FieldError, FilterTabs, Notice, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';
import { CodeFields, Code, LiveBadge, Thumb } from './parts';
import { API, PROVIDERS, nouns, shortName, type CatalogRow, type Codes, type Kind, type Region } from './types';

const views = [
    { id: 'all', label: 'همه' },
    { id: 'live', label: 'فعال' },
    { id: 'off', label: 'غیرفعال' },
    { id: 'unsold', label: 'بدون فروش' },
];

const copy: Record<Kind, { title: string; intro: string; add: string; search: string }> = {
    services: {
        title: 'سرویس‌های شماره مجازی',
        intro: 'برنامه‌هایی که برایشان شماره فروخته می‌شود، با کد هر سرور. ترتیب همان ترتیب سایت است.',
        add: 'سرویس جدید',
        search: 'نام، نامک یا کد',
    },
    countries: {
        title: 'کشورهای شماره مجازی',
        intro: 'کشورهایی که از آن‌ها شماره فروخته می‌شود، با کد هر سرور. ترتیب همان ترتیب سایت است.',
        add: 'کشور جدید',
        search: 'نام، نامک یا کد',
    },
};

const sorts = [
    { value: '', label: 'ترتیب سایت' },
    { value: 'name', label: 'نام' },
    { value: 'offers', label: 'بیشترین پیشنهاد فعال' },
];

function matches(row: CatalogRow, q: string) {
    return [row.name, row.enName, row.slug, row.flagCode, ...Object.values(row.codes)].some((v) => (v ?? '').toLowerCase().includes(q));
}

const displayName = (r: CatalogRow) => shortName(r.name) || r.enName || r.slug || `#${r.id}`;

export default function CatalogList({ kind }: { kind: Kind }) {
    const list = useListParams({ view: 'all', q: '', sort: '', per: '', region: '', featured: '', missing: '' });
    const v = list.values;
    const query = useQuery<{ services?: CatalogRow[]; countries?: CatalogRow[]; regions?: Region[] }>(`${API}/${kind}`);
    const rows = query.data?.[kind] ?? null;
    const regions = query.data?.regions ?? [];
    const [saved, setSaved] = useState<string | null>(null);
    const n = nouns[kind];
    const c = copy[kind];

    const filters: FilterDef[] = [
        ...(kind === 'countries' && regions.length ? [{ type: 'select' as const, key: 'region', label: 'منطقه', any: 'همه منطقه‌ها', options: regions.map((r) => ({ value: String(r.id), label: r.name })) }] : []),
        {
            type: 'select',
            key: 'missing',
            label: 'بدون کد سرور',
            any: 'فرقی نمی‌کند',
            options: PROVIDERS.map((p) => ({ value: p.key, label: p.server ? `سرور ${p.server} (${p.label})` : p.label })),
        },
        ...(kind === 'services' ? [{ type: 'toggle' as const, key: 'featured', label: 'فقط ویژه‌ها' }] : []),
    ];

    const base = { q: v.q, region: v.region, featured: v.featured, missing: v.missing };
    const tests = {
        region: (r: CatalogRow, val: string) => String(r.regionId ?? '') === val,
        featured: (r: CatalogRow) => !!r.featured,
        missing: (r: CatalogRow, val: string) => !r.codes[val as keyof Codes],
    };
    // The tab counts follow search and filters, but not the tab itself.
    const scoped = useClientList(rows, base, 1, { search: matches, filters: tests, per: 100000 });
    const counts = scoped
        ? {
              all: scoped.total,
              live: scoped.all.filter((r) => r.live).length,
              off: scoped.all.filter((r) => !r.live).length,
              unsold: scoped.all.filter((r) => r.offers.live === 0).length,
          }
        : undefined;
    const shown = useClientList(rows, v, list.page, {
        search: matches,
        filters: { ...tests, view: (r, val) => (val === 'live' ? r.live : val === 'off' ? !r.live : val === 'unsold' ? r.offers.live === 0 : true) },
        sorts: { name: (a, b) => displayName(a).localeCompare(displayName(b), 'fa'), offers: (a, b) => b.offers.live - a.offers.live },
        per: 50,
    });

    const columns: Column<CatalogRow>[] = [
        {
            key: 'name',
            header: n.one,
            primary: true,
            cell: (r) => (
                <span className="flex items-center gap-3">
                    <Thumb src={kind === 'countries' ? r.flag : r.image} name={r.name} flag={kind === 'countries'} />
                    <span className="min-w-0">
                        <span className="flex items-center gap-1.5 font-bold">
                            {displayName(r)}
                            {r.featured && <Star aria-label="ویژه" size={13} className="shrink-0 fill-current text-warn" />}
                        </span>
                        <span className="latin block truncate text-[12px] text-muted" dir="ltr">
                            {r.enName}
                        </span>
                    </span>
                </span>
            ),
        },
        ...PROVIDERS.map(
            (p): Column<CatalogRow> => ({
                key: p.key,
                from: 'lg',
                header: (
                    <>
                        {p.server ? `سرور ${p.server}` : 'Land'}
                        <span className="block text-[12px] font-normal">{p.server ? <span className="latin">{p.label}</span> : 'فقط کد'}</span>
                    </>
                ),
                cell: (r) => <Code value={r.codes[p.key]} />,
            }),
        ),
        {
            key: 'offers',
            header: 'در فروش',
            className: 'num whitespace-nowrap',
            cell: (r) =>
                r.offers.total === 0 ? (
                    <span className="text-muted">بدون پیشنهاد</span>
                ) : (
                    <span className={r.offers.live === 0 ? 'font-medium text-warn' : ''}>
                        {count(r.offers.live)} <span className="text-muted">از {count(r.offers.total)}</span>
                    </span>
                ),
        },
        { key: 'live', header: 'وضعیت', cell: (r) => <LiveBadge live={r.live} /> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={c.title}
                subtitle={c.intro}
                actions={
                    <Link to={`/virtual-numbers/${kind}/new`} className="btn btn-primary">
                        <Plus aria-hidden size={16} />
                        {c.add}
                    </Link>
                }
            />

            <FilterBar
                views={<FilterTabs label={`فیلتر ${n.plural}`} items={views} value={v.view} counts={counts} attention="unsold" onChange={(view) => list.update({ view })} />}
                search={{ label: `جستجوی ${n.plural}`, placeholder: c.search }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun={n.one}
                loading={query.loading}
                onRefresh={query.reload}
            />

            {saved && (
                <div className="mb-3">
                    <Notice>{saved}</Notice>
                </div>
            )}

            <DataTable
                label={n.plural}
                rows={shown?.rows}
                loading={query.loading}
                error={query.error}
                onRetry={query.reload}
                columns={columns}
                rowKey={(r) => r.id}
                href={(r) => `/virtual-numbers/${kind}/${r.id}`}
                linkState={{ list: list.here }}
                rowLabel={(r) => `${n.one} ${displayName(r)}`}
                rowTone={(r) => (r.live ? undefined : 'muted')}
                quick={{
                    title: (r) => displayName(r),
                    subtitle: (r) => (
                        <span className="flex flex-wrap items-center gap-2">
                            <LiveBadge live={r.live} />
                            <span dir="ltr" className="latin text-muted">
                                {r.enName}
                            </span>
                        </span>
                    ),
                    body: (r) => (
                        <CatalogQuick
                            kind={kind}
                            row={r}
                            onSaved={(msg) => {
                                setSaved(`${displayName(r)}: ${msg}`);
                                query.reload();
                            }}
                        />
                    ),
                }}
                mobile={(r) => (
                    <span className="flex items-center gap-3">
                        <Thumb src={kind === 'countries' ? r.flag : r.image} name={r.name} flag={kind === 'countries'} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{displayName(r)}</span>
                            <span className="num block text-[12px] text-muted">
                                {count(r.offers.live)} از {count(r.offers.total)} در فروش
                            </span>
                        </span>
                        <LiveBadge live={r.live} />
                    </span>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v)} onClear={() => list.update({ q: '', view: '', region: '', featured: '', missing: '' })}>
                        هنوز {n.one}ی ساخته نشده است.
                    </ListEmpty>
                }
            />
            {shown && (
                <ListPager
                    page={shown.page}
                    lastPage={shown.lastPage}
                    total={shown.total}
                    perPage={shown.perPage}
                    noun={n.one}
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(x) => list.update({ per: String(x) })}
                />
            )}
            <p className="mt-3 text-[13px] text-muted">«در فروش» یعنی پیشنهادهایی که قیمت دارند و در سایت خریدنی‌اند. کدهای هر ردیف از «نمایش سریع» هم ویرایش می‌شوند.</p>
        </div>
    );
}

/** Quick view: the row's numbers plus the old list's per-row code editor. */
function CatalogQuick({ kind, row, onSaved }: { kind: Kind; row: CatalogRow; onSaved: (msg: string) => void }) {
    const { csrfToken } = useBoot();
    const [codes, setCodes] = useState<Codes>(row.codes);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [failure, setFailure] = useState<string | null>(null);
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const box = useRef<HTMLDivElement>(null);
    const changed = PROVIDERS.some((p) => (codes[p.key] ?? '') !== (row.codes[p.key] ?? ''));

    useEffect(() => setCodes(row.codes), [row.codes]);

    async function save() {
        setBusy(true);
        setFailure(null);
        setDone(null);
        try {
            const res = await postJson<{ success: string }>(`${API}/${kind}/${row.id}/codes`, csrfToken, { codes });
            setErrors({});
            setDone(res.success);
            onSaved(res.success);
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
        } finally {
            setBusy(false);
        }
    }

    return (
        <>
            <QuickSection title="فروش">
                <dl className="m-0">
                    <Row label="پیشنهاد فعال" num>
                        {count(row.offers.live)} از {count(row.offers.total)}
                    </Row>
                    <Row label="ترتیب در سایت" num>
                        {row.sort}
                    </Row>
                    {row.slug && (
                        <Row label="نامک" ltr>
                            <span className="latin">{row.slug}</span>
                        </Row>
                    )}
                </dl>
            </QuickSection>
            <QuickSection title="کدهای سرورها">
                <form
                    className="pt-1 pb-3"
                    noValidate
                    onSubmit={(e) => {
                        e.preventDefault();
                        save();
                    }}
                >
                    <div ref={box}>
                        <CodeFields codes={codes} onChange={setCodes} errors={errors} compact />
                    </div>
                    <div className="mt-3 flex flex-wrap items-center gap-2">
                        <button type="submit" className="btn btn-primary" disabled={busy || !changed}>
                            {busy ? 'در حال ذخیره…' : 'ذخیره کدها'}
                        </button>
                        {changed && (
                            <button type="button" className="btn btn-ghost" onClick={() => setCodes(row.codes)} disabled={busy}>
                                برگرداندن
                            </button>
                        )}
                    </div>
                    <p className="mt-2 text-[12px] text-muted">خالی یعنی آن سرور این {nouns[kind].one} را نمی‌فروشد. بعد از تغییر کد، پیشنهادها را از صفحه کامل بازسازی کنید.</p>
                    {failure && <FieldError id={`codes-${row.id}-error`}>{failure}</FieldError>}
                    {done && <Notice>{done}</Notice>}
                </form>
            </QuickSection>
        </>
    );
}
