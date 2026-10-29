import { Fragment, useEffect, useId, useMemo, useRef, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router';
import { Pencil, Plus, Star } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count } from '../../lib/format';
import { ErrorState, FieldError, FilterTabs, Notice, SearchField, SkeletonRows } from '../../components/ui';
import { CodeFields, Code, LiveBadge, Thumb } from './parts';
import { API, PROVIDERS, nouns, shortName, type CatalogRow, type Codes, type Kind, type Region } from './types';

type View = 'all' | 'live' | 'off' | 'unsold';

const views: { id: View; label: string }[] = [
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

function matches(row: CatalogRow, q: string) {
    const needle = q.toLowerCase();
    return [row.name, row.enName, row.slug, row.flagCode, ...Object.values(row.codes)].some((v) => (v ?? '').toLowerCase().includes(needle));
}

export default function CatalogList({ kind }: { kind: Kind }) {
    const [params, setParams] = useSearchParams();
    const navigate = useNavigate();
    const q = params.get('q') ?? '';
    const view = (params.get('view') as View) || 'all';
    const region = params.get('region') ?? '';
    const list = useQuery<{ services?: CatalogRow[]; countries?: CatalogRow[]; regions?: Region[] }>(`${API}/${kind}`);
    const rows = list.data?.[kind] ?? null;
    const regions = list.data?.regions ?? [];
    const [editing, setEditing] = useState<number | null>(null);
    const [saved, setSaved] = useState<string | null>(null);
    const regionId = useId();
    const n = nouns[kind];
    const c = copy[kind];

    function update(patch: Record<string, string>) {
        const next = new URLSearchParams(params);
        for (const [k, v] of Object.entries(patch)) {
            if (v) next.set(k, v);
            else next.delete(k);
        }
        setParams(next, { replace: true });
    }

    const scoped = useMemo(() => (rows ?? []).filter((r) => (!q || matches(r, q)) && (!region || String(r.regionId ?? '') === region)), [rows, q, region]);
    const counts = useMemo(
        () => ({
            all: scoped.length,
            live: scoped.filter((r) => r.live).length,
            off: scoped.filter((r) => !r.live).length,
            unsold: scoped.filter((r) => r.offers.live === 0).length,
        }),
        [scoped],
    );
    const shown = scoped.filter((r) => (view === 'live' ? r.live : view === 'off' ? !r.live : view === 'unsold' ? r.offers.live === 0 : true));
    const columns = 4 + PROVIDERS.length;

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">{c.title}</h1>
                    <p className="mt-1 text-muted">{c.intro}</p>
                </div>
                <Link to={`/virtual-numbers/${kind}/new`} className="btn btn-primary">
                    <Plus aria-hidden size={16} />
                    {c.add}
                </Link>
            </div>

            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label={`فیلتر ${n.plural}`} items={views} value={view} counts={rows ? counts : undefined} attention="unsold" onChange={(v) => update({ view: v === 'all' ? '' : v })} />
                <div className="flex flex-1 gap-2 xl:justify-end">
                    {kind === 'countries' && regions.length > 0 && (
                        <>
                            <label htmlFor={regionId} className="sr-only">
                                منطقه
                            </label>
                            <select id={regionId} value={region} onChange={(e) => update({ region: e.target.value })} className="field w-auto min-w-[140px]">
                                <option value="">همه منطقه‌ها</option>
                                {regions.map((r) => (
                                    <option key={r.id} value={r.id}>
                                        {r.name}
                                    </option>
                                ))}
                            </select>
                        </>
                    )}
                    <SearchField value={q} onSearch={(v) => update({ q: v })} label={`جستجوی ${n.plural}`} placeholder={c.search} />
                </div>
            </div>

            {saved && <div className="mb-3"><Notice>{saved}</Notice></div>}

            <section aria-label={n.plural} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {list.error ? (
                    <ErrorState error={list.error} onRetry={list.reload} />
                ) : !rows ? (
                    <SkeletonRows rows={8} />
                ) : shown.length === 0 ? (
                    <div className="px-5 py-14 text-center">
                        <p className="font-medium">{q || region || view !== 'all' ? `${n.one}ی با این فیلتر پیدا نشد.` : `هنوز ${n.one}ی ساخته نشده است.`}</p>
                        {(q || region || view !== 'all') && (
                            <button type="button" className="btn btn-secondary mt-3" onClick={() => setParams(new URLSearchParams(), { replace: true })}>
                                پاک کردن فیلترها
                            </button>
                        )}
                    </div>
                ) : (
                    <div className="relative overflow-x-auto">
                        <table className={`w-full ${list.loading ? 'opacity-60' : ''}`}>
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">{n.one}</th>
                                    {PROVIDERS.map((p) => (
                                        <th key={p.key} scope="col" className="hidden px-3 text-start font-medium lg:table-cell">
                                            {p.server ? `سرور ${p.server}` : 'Land'}
                                            <span className="block text-[12px] font-normal">{p.server ? <span className="latin">{p.label}</span> : 'فقط کد'}</span>
                                        </th>
                                    ))}
                                    <th scope="col" className="px-3 text-start font-medium">در فروش</th>
                                    <th scope="col" className="hidden px-3 text-start font-medium sm:table-cell">وضعیت</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-end font-medium">
                                        <span className="sr-only">کدها</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {shown.map((r) => {
                                    const open = editing === r.id;
                                    const name = shortName(r.name) || r.enName || r.slug || `#${r.id}`;
                                    const to = `/virtual-numbers/${kind}/${r.id}`;
                                    return (
                                        <Fragment key={r.id}>
                                            <tr
                                                onClick={(e) => {
                                                    if (!(e.target as HTMLElement).closest('a,button,input')) navigate(to);
                                                }}
                                                className={`cursor-pointer border-b border-stroke hover:bg-surface-2 ${open ? 'bg-surface-2' : 'last:border-0'}`}
                                            >
                                                <td className="py-3 ps-5 pe-3">
                                                    <div className="flex items-center gap-3">
                                                        <Thumb src={kind === 'countries' ? r.flag : r.image} name={r.name} flag={kind === 'countries'} />
                                                        <div className="min-w-0">
                                                            <Link to={to} className="flex items-center gap-1.5 font-bold underline-offset-4 hover:underline">
                                                                {name}
                                                                {r.featured && <Star aria-label="ویژه" size={13} className="shrink-0 fill-current text-warn" />}
                                                            </Link>
                                                            <span className="latin block truncate text-[12px] text-muted" dir="ltr">
                                                                {r.enName}
                                                            </span>
                                                            {!r.live && <span className="block text-[12px] font-medium text-muted sm:hidden">غیرفعال</span>}
                                                            <span className="mt-1 hidden flex-wrap gap-x-3 text-[12px] text-muted sm:flex lg:hidden">
                                                                {PROVIDERS.map((p) => (
                                                                    <span key={p.key}>
                                                                        {p.server ? `سرور ${p.server}` : 'Land'}: <Code value={r.codes[p.key]} />
                                                                    </span>
                                                                ))}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                {PROVIDERS.map((p) => (
                                                    <td key={p.key} className="hidden px-3 lg:table-cell">
                                                        <Code value={r.codes[p.key]} />
                                                    </td>
                                                ))}
                                                <td className="num px-3 whitespace-nowrap">
                                                    {r.offers.total === 0 ? (
                                                        <span className="text-muted">بدون پیشنهاد</span>
                                                    ) : (
                                                        <span className={r.offers.live === 0 ? 'font-medium text-warn' : ''}>
                                                            {count(r.offers.live)} <span className="text-muted">از {count(r.offers.total)}</span>
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="hidden px-3 sm:table-cell">
                                                    <LiveBadge live={r.live} />
                                                </td>
                                                <td className="py-3 ps-3 pe-5 text-end">
                                                    <button
                                                        type="button"
                                                        className="btn btn-ghost"
                                                        aria-expanded={open}
                                                        aria-controls={`codes-${r.id}`}
                                                        aria-label={`ویرایش کدهای ${name}`}
                                                        onClick={() => {
                                                            setSaved(null);
                                                            setEditing(open ? null : r.id);
                                                        }}
                                                    >
                                                        <Pencil aria-hidden size={15} />
                                                        <span className="hidden sm:inline">کدها</span>
                                                    </button>
                                                </td>
                                            </tr>
                                            {open && (
                                                <tr id={`codes-${r.id}`} className="border-b border-stroke bg-surface-2 last:border-0">
                                                    <td colSpan={columns} className="px-5 pt-1 pb-4">
                                                        <QuickCodes
                                                            kind={kind}
                                                            row={r}
                                                            name={name}
                                                            onCancel={() => setEditing(null)}
                                                            onSaved={(msg) => {
                                                                setEditing(null);
                                                                setSaved(`${name}: ${msg}`);
                                                                list.reload();
                                                            }}
                                                        />
                                                    </td>
                                                </tr>
                                            )}
                                        </Fragment>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
            {rows && shown.length > 0 && (
                <p className="num mt-3 text-[13px] text-muted">
                    {count(shown.length)} {n.one}
                    {shown.length !== rows.length && ` از ${count(rows.length)}`} · «در فروش» یعنی پیشنهادهایی که قیمت دارند و در سایت خریدنی‌اند.
                </p>
            )}
        </div>
    );
}

/** The old list's per-row «ذخیره»: edit one row's provider codes in place. */
function QuickCodes({ kind, row, name, onCancel, onSaved }: { kind: Kind; row: CatalogRow; name: string; onCancel: () => void; onSaved: (msg: string) => void }) {
    const { csrfToken } = useBoot();
    const [codes, setCodes] = useState<Codes>(row.codes);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [failure, setFailure] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const first = useRef<HTMLDivElement>(null);

    useEffect(() => {
        first.current?.querySelector('input')?.focus();
    }, []);

    async function save() {
        setBusy(true);
        setFailure(null);
        try {
            const res = await postJson<{ success: string }>(`${API}/${kind}/${row.id}/codes`, csrfToken, { codes });
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
        <form
            onSubmit={(e) => {
                e.preventDefault();
                save();
            }}
            onKeyDown={(e) => {
                if (e.key === 'Escape') onCancel();
            }}
            aria-label={`کدهای ${name}`}
            noValidate
        >
            <div ref={first}>
                <CodeFields codes={codes} onChange={setCodes} errors={errors} compact />
            </div>
            <div className="mt-3 flex flex-wrap items-center gap-2">
                <button type="submit" className="btn btn-primary" disabled={busy}>
                    {busy ? 'در حال ذخیره…' : 'ذخیره کدها'}
                </button>
                <button type="button" className="btn btn-secondary" onClick={onCancel} disabled={busy}>
                    انصراف
                </button>
                <span className="text-[13px] text-muted">خالی یعنی آن سرور این {nouns[kind].one} را نمی‌فروشد. پیشنهادها را بعد از تغییر کد، از صفحه {nouns[kind].one} بازسازی کنید.</span>
            </div>
            {failure && <FieldError id={`codes-${row.id}-error`}>{failure}</FieldError>}
        </form>
    );
}
