import { useEffect, useState, type FormEvent, type ReactNode } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router';
import { ArrowRight, CircleCheck, CircleSlash, Pencil, Plus, Server, Trash2, UserPlus } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch, slugify } from '../../components/form';
import { Badge, type Tone } from '../../components/Status';
import { CopyButton, ErrorState, FilterTabs, Notice, Pager, Panel, SearchField, SkeletonRows } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, EmptyState, problemsFrom, useListParams } from '../../components/list';
import { useIpScope } from './scope';

type Plan = { id?: number; name: string; price: string; days: string; volume: string; salePrice: string; percent: string; image: string };
type Location = { id?: number; name: string; enName: string; slug: string; live: boolean; image: string; metaTitle: string; metaDesc: string; plans: Plan[] };
type ServerStatus = 'active' | 'off' | 'full' | 'removed';
type ServerRow = {
    id: number;
    name: string;
    address: string;
    ip: string;
    panelUrl: string;
    panelPort: string;
    username: string;
    hasPassword: boolean;
    inbound: string;
    inboundPort: string;
    status: ServerStatus;
    free: number;
    active: number;
    total: number;
};
type ClientStatus = 'free' | 'active' | 'expired' | 'ended' | 'suspended' | 'imported';
type ClientRow = {
    id: number;
    name: string;
    code: string;
    status: ClientStatus;
    serverId: number | null;
    orderId: number | null;
    plan: string | null;
    volume: number | null;
    expiresAt: string | null;
    user: { id: number; name: string | null; mobile: string | null } | null;
};

const blank: Location = { name: '', enName: '', slug: '', live: true, image: '', metaTitle: '', metaDesc: '', plans: [] };
const serverStatus: Record<ServerStatus, { label: string; tone: Tone }> = {
    active: { label: 'فعال', tone: 'success' },
    off: { label: 'غیرفعال', tone: 'neutral' },
    full: { label: 'تکمیل', tone: 'warn' },
    removed: { label: 'حذف‌شده', tone: 'danger' },
};
const clientStatus: Record<ClientStatus, { label: string; tone: Tone }> = {
    free: { label: 'آزاد', tone: 'info' },
    active: { label: 'فروخته و فعال', tone: 'success' },
    expired: { label: 'منقضی', tone: 'warn' },
    ended: { label: 'پایان‌یافته', tone: 'neutral' },
    suspended: { label: 'معلق (تمدیدنشده)', tone: 'warn' },
    imported: { label: 'از پنل', tone: 'neutral' },
};

type Tab = 'details' | 'servers' | 'clients';

export default function IpLocationPage() {
    const { id } = useParams();
    const isNew = id === 'new';
    const [params, setParams] = useSearchParams();
    const tab = (params.get('tab') as Tab) || 'details';
    const scope = useIpScope();
    const { data, error, reload } = useQuery<{ location: Location & { id: number }; servers: ServerRow[] }>(isNew ? null : `${scope.api}/locations/${id}`);

    if (isNew) return <LocationEditor onSaved={() => undefined} />;
    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <EditorSkeleton />;

    const tabs: { id: Tab; label: string }[] = [
        { id: 'details', label: 'مشخصات و پلن‌ها' },
        { id: 'servers', label: `سرورها (${data.servers.length})` },
        { id: 'clients', label: 'کلاینت‌ها' },
    ];

    // The three screens of a country share this tab bar, shown under each screen's title.
    const nav = (
        <nav aria-label="بخش‌های کشور" className="mb-6 flex gap-1 overflow-x-auto border-b border-stroke">
            {tabs.map((t) => (
                <button
                    key={t.id}
                    type="button"
                    aria-current={tab === t.id ? 'page' : undefined}
                    onClick={() => setParams(t.id === 'details' ? {} : { tab: t.id })}
                    className={`relative shrink-0 px-3 py-3 ${tab === t.id ? 'font-bold text-ink' : 'text-muted hover:text-ink'}`}
                >
                    {t.label}
                    {tab === t.id && <span aria-hidden className="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-ink" />}
                </button>
            ))}
        </nav>
    );

    if (tab === 'servers') return <Servers location={data.location} servers={data.servers} onChange={reload} nav={nav} />;
    if (tab === 'clients') return <Clients location={data.location} servers={data.servers} nav={nav} />;
    return <LocationEditor existing={data.location} onSaved={reload} nav={nav} />;
}

/* ---------- Location and plans ---------- */

function LocationEditor({ existing, onSaved, nav }: { existing?: Location & { id: number }; onSaved: () => void; nav?: ReactNode }) {
    const navigate = useNavigate();
    const scope = useIpScope();
    const UPLOAD = `${scope.api}/uploads/image`;
    const save = useSave();
    const [form, setForm] = useState<Location>(existing ?? blank);
    const [slugTouched, setSlugTouched] = useState(!!existing);
    useEffect(() => {
        if (existing) setForm(existing);
    }, [existing]);

    const set = (patch: Partial<Location>) => {
        save.clearSaved();
        setForm((f) => ({ ...f, ...patch }));
    };
    const err = (k: string) => save.errors[k];
    const labels = {
        name: 'نام', enName: 'نام انگلیسی', slug: 'نامک', image: 'پرچم', metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', plans: 'پلن‌ها',
        'plans.name': 'نام', 'plans.price': 'قیمت', 'plans.days': 'مدت', 'plans.volume': 'حجم', 'plans.salePrice': 'قیمت با تخفیف', 'plans.percent': 'درصد تخفیف',
    };

    async function submit() {
        const url = existing ? `${scope.api}/locations/${existing.id}` : `${scope.api}/locations`;
        const res = await save.run<{ success: string; id: number }>(url, {
            ...form,
            plans: form.plans.map((p) => ({ ...p, salePrice: p.salePrice || 0, percent: p.percent || 0 })),
        });
        if (res && !existing) navigate(`${scope.base}/${res.id}?tab=servers`, { replace: true });
        else if (res) onSaved();
    }

    return (
        <EditorLayout
            backTo={scope.base}
            backLabel={scope.title}
            title={existing ? form.name || 'کشور' : 'کشور جدید'}
            subtitle={existing ? undefined : 'بعد از ساخت، سرورها را اضافه کنید و کلاینت بسازید.'}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={existing ? 'ذخیره تغییرات' : 'ساخت کشور'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            nav={nav}
            side={<Switch label="فعال" hint="غیرفعال یعنی برای فروش عرضه نمی‌شود." checked={form.live} onChange={(live) => set({ live })} />}
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={err('name')}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} placeholder="آی پی ثابت کشور آلمان" />}
                    </Field>
                    <Field label="نام انگلیسی" error={err('enName')}>
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.enName}
                                onChange={(e) => set({ enName: e.target.value, ...(slugTouched ? {} : { slug: slugify(e.target.value) }) })}
                                placeholder="Germany Static IP"
                            />
                        )}
                    </Field>
                    <Field label="نامک (آدرس)" error={err('slug')}>
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.slug}
                                onChange={(e) => {
                                    setSlugTouched(true);
                                    set({ slug: e.target.value.toLowerCase() });
                                }}
                                placeholder="german-static-ip"
                            />
                        )}
                    </Field>
                    <div />
                    {scope.webPage && (
                        <>
                            <div className="sm:col-span-2">
                                <ImageField label="پرچم یا تصویر کشور" value={form.image} onChange={(image) => set({ image })} error={err('image')} uploadUrl={UPLOAD} square />
                            </div>
                            <Field label="عنوان سئو" error={err('metaTitle')} className="sm:col-span-2">
                                {(p) => <input {...p} className="field" value={form.metaTitle} onChange={(e) => set({ metaTitle: e.target.value })} />}
                            </Field>
                            <Field label="توضیح سئو" error={err('metaDesc')} className="sm:col-span-2">
                                {(p) => <textarea {...p} rows={2} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                            </Field>
                        </>
                    )}
                </div>
            </Panel>

            <Panel title="پلن‌های فروش">
                <p className="mb-3 text-muted">قیمت‌ها به تومان. پلنی که کسی خریده باشد حتی اگر از اینجا حذف شود، برای سفارش‌های قبلی می‌ماند.</p>
                <RowsEditor
                    rows={form.plans}
                    onChange={(plans) => set({ plans })}
                    make={() => ({ name: '', price: '', days: '30', volume: '30', salePrice: '', percent: '', image: '' })}
                    addLabel="افزودن پلن"
                    empty="بدون پلن، این کشور فروخته نمی‌شود."
                    removeLabel={(r, i) => `حذف پلن ${r.name || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-3">
                            <Field label="نام پلن" error={err(`plans.${i}.name`)}>
                                {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="یک ماهه" />}
                            </Field>
                            <Field label="قیمت (تومان)" error={err(`plans.${i}.price`)}>
                                {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.price} onChange={(e) => update({ price: e.target.value })} />}
                            </Field>
                            <Field label="قیمت با تخفیف" optional error={err(`plans.${i}.salePrice`)}>
                                {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.salePrice} onChange={(e) => update({ salePrice: e.target.value })} placeholder="0" />}
                            </Field>
                            <Field label="مدت (روز)" error={err(`plans.${i}.days`)}>
                                {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.days} onChange={(e) => update({ days: e.target.value })} />}
                            </Field>
                            <Field label="حجم (گیگابایت)" error={err(`plans.${i}.volume`)}>
                                {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.volume} onChange={(e) => update({ volume: e.target.value })} />}
                            </Field>
                            <Field label="درصد تخفیف" optional error={err(`plans.${i}.percent`)}>
                                {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.percent} onChange={(e) => update({ percent: e.target.value })} placeholder="0" />}
                            </Field>
                            <div className="sm:col-span-3">
                                <ImageField label="آیکن پلن" value={row.image} onChange={(image) => update({ image })} uploadUrl={UPLOAD} square />
                            </div>
                        </div>
                    )}
                />
            </Panel>
        </EditorLayout>
    );
}

/* ---------- Servers ---------- */

type ServerForm = {
    name: string;
    address: string;
    ip: string;
    panelUrl: string;
    panelPort: string;
    username: string;
    password: string;
    inbound: string;
    inboundPort: string;
    status: ServerStatus;
};
const blankServer: ServerForm = { name: '', address: '', ip: '', panelUrl: 'https://', panelPort: '2053', username: '', password: '', inbound: '', inboundPort: '443', status: 'active' };

function Servers({ location, servers, onChange, nav }: { location: Location & { id: number }; servers: ServerRow[]; onChange: () => void; nav: ReactNode }) {
    const scope = useIpScope();
    const [editing, setEditing] = useState<number | 'new' | null>(servers.length ? null : 'new');
    const [notice, setNotice] = useState<string | null>(null);

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={scope.base} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                {scope.title}
            </Link>
            <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">سرورهای {location.name}</h1>
                    <p className="mt-1 text-muted">هر سرور یک پنل 3x-ui است؛ کلاینت‌ها روی اینباند آن ساخته می‌شوند.</p>
                </div>
                {editing !== 'new' && (
                    <button type="button" className="btn btn-primary" onClick={() => setEditing('new')}>
                        <Plus aria-hidden size={16} />
                        سرور جدید
                    </button>
                )}
            </div>
            {nav}
            {notice && (
                <div className="mb-4">
                    <Notice>{notice}</Notice>
                </div>
            )}
            <div className="space-y-4">
                {editing === 'new' && (
                    <ServerEditor
                        locationId={location.id}
                        onCancel={servers.length ? () => setEditing(null) : undefined}
                        onSaved={(msg) => {
                            setEditing(null);
                            setNotice(msg);
                            onChange();
                        }}
                    />
                )}
                {servers.map((s) =>
                    editing === s.id ? (
                        <ServerEditor
                            key={s.id}
                            locationId={location.id}
                            server={s}
                            onCancel={() => setEditing(null)}
                            onSaved={(msg) => {
                                setEditing(null);
                                setNotice(msg);
                                onChange();
                            }}
                        />
                    ) : (
                        <ServerCard key={s.id} server={s} onEdit={() => setEditing(s.id)} onMade={(msg) => { setNotice(msg); onChange(); }} />
                    ),
                )}
                {!servers.length && editing !== 'new' && (
                    <div className="rounded-2xl border border-stroke bg-surface">
                        <EmptyState>این کشور هنوز سروری ندارد.</EmptyState>
                    </div>
                )}
            </div>
        </div>
    );
}

function ServerCard({ server: s, onEdit, onMade }: { server: ServerRow; onEdit: () => void; onMade: (msg: string) => void }) {
    const { csrfToken } = useBoot();
    const scope = useIpScope();
    const [n, setN] = useState('5');
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState<string | null>(null);
    const meta = serverStatus[s.status];

    async function make() {
        setBusy(true);
        setErr(null);
        try {
            const res = await postJson<{ success: string }>(`${scope.api}/servers/${s.id}/clients`, csrfToken, { count: Number(n) });
            onMade(res.success);
        } catch (e) {
            const ex = e as ApiError;
            setErr(ex.fields?.count ?? ex.message);
        } finally {
            setBusy(false);
        }
    }

    return (
        <section aria-label={`سرور ${s.name}`} className="rounded-2xl border border-stroke bg-surface p-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="flex items-center gap-3">
                    <span className="grid size-10 place-items-center rounded-xl bg-surface-2">
                        <Server aria-hidden size={18} className="text-muted" />
                    </span>
                    <div>
                        <h2 dir="ltr" className="latin text-end text-[16px] font-bold">{s.name}</h2>
                        <p dir="ltr" className="latin text-end text-[13px] text-muted">
                            {s.address}
                            {s.ip ? ` · ${s.ip}` : ''}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Badge tone={meta.tone} icon={s.status === 'active' ? CircleCheck : CircleSlash} label={meta.label} />
                    <button type="button" className="btn btn-ghost" onClick={onEdit}>
                        <Pencil aria-hidden size={15} />
                        ویرایش
                    </button>
                </div>
            </div>
            <dl className="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                {[
                    ['کلاینت آزاد', s.free, s.free === 0],
                    ['فروخته و فعال', s.active, false],
                    ['همه کلاینت‌ها', s.total, false],
                ].map(([label, value, warn]) => (
                    <div key={label as string} className={`rounded-xl px-3 py-2 ${warn ? 'bg-warn-soft text-warn' : 'bg-surface-2'}`}>
                        <dt className={`text-[12px] ${warn ? '' : 'text-muted'}`}>{label}</dt>
                        <dd className="num m-0 text-[16px] font-bold">{value as number}</dd>
                    </div>
                ))}
                <div className="rounded-xl bg-surface-2 px-3 py-2">
                    <dt className="text-[12px] text-muted">اینباند</dt>
                    <dd dir="ltr" className="num m-0 text-end font-bold sm:text-start">
                        #{s.inbound} : {s.inboundPort}
                    </dd>
                </div>
            </dl>
            {s.status === 'active' && (
                <div className="mt-4 flex flex-wrap items-end gap-2 border-t border-stroke pt-4">
                    <Field label="ساخت کلاینت تازه" hint="روی پنل سرور ساخته و به کلاینت‌های آزاد اضافه می‌شود." error={err ?? undefined}>
                        {(p) => (
                            <div className="flex gap-2">
                                <input {...p} type="number" min={1} max={50} dir="ltr" className="field num w-24 text-end" value={n} onChange={(e) => setN(e.target.value)} />
                                <button type="button" className="btn btn-secondary" onClick={make} disabled={busy || !Number(n)}>
                                    <UserPlus aria-hidden size={16} />
                                    {busy ? 'در حال ساخت…' : 'ساخت'}
                                </button>
                            </div>
                        )}
                    </Field>
                </div>
            )}
        </section>
    );
}

function ServerEditor({ locationId, server, onCancel, onSaved }: { locationId: number; server?: ServerRow; onCancel?: () => void; onSaved: (msg: string) => void }) {
    const scope = useIpScope();
    const save = useSave();
    const [form, setForm] = useState<ServerForm>(server ? { ...blankServer, ...server, password: '' } : blankServer);
    const set = (patch: Partial<ServerForm>) => setForm((f) => ({ ...f, ...patch }));
    const err = (k: string) => save.errors[k];

    async function submit(e: FormEvent) {
        e.preventDefault();
        const url = server ? `${scope.api}/locations/${locationId}/servers/${server.id}` : `${scope.api}/locations/${locationId}/servers`;
        const res = await save.run<{ success: string }>(url, { ...form, panelPort: Number(form.panelPort) || form.panelPort, inbound: Number(form.inbound) || form.inbound, inboundPort: Number(form.inboundPort) || form.inboundPort });
        if (res) onSaved(res.success);
    }

    const input = (key: keyof ServerForm, label: string, opts: { ltr?: boolean; hint?: string; placeholder?: string; type?: string; optional?: boolean } = {}) => (
        <Field label={label} hint={opts.hint} error={err(key)} optional={opts.optional}>
            {(p) => (
                <input
                    {...p}
                    type={opts.type ?? 'text'}
                    dir={opts.ltr ? 'ltr' : undefined}
                    autoComplete="off"
                    className={`field ${opts.ltr ? 'latin' : ''}`}
                    value={form[key]}
                    placeholder={opts.placeholder}
                    onChange={(e) => set({ [key]: e.target.value } as Partial<ServerForm>)}
                />
            )}
        </Field>
    );

    return (
        <form onSubmit={submit} noValidate className="rounded-2xl border-2 border-primary/40 bg-surface p-5">
            <h2 className="mb-4 text-[16px] font-bold">{server ? `ویرایش سرور ${server.name}` : 'سرور جدید'}</h2>
            <div className="grid gap-4 sm:grid-cols-3">
                {input('name', 'نام کوتاه', { ltr: true, placeholder: 'de1', hint: 'نام کلاینت‌ها: de1-1، de1-2، …' })}
                {input('address', 'دامنه اتصال', { ltr: true, placeholder: 't1.shopmajaziplus.ir', hint: 'آدرسی که در لینک کانفیگ می‌آید.' })}
                {input('ip', 'آی‌پی سرور', { ltr: true, placeholder: '1.2.3.4', optional: true })}
                {input('panelUrl', 'آدرس پنل 3x-ui', { ltr: true, placeholder: 'https://panel.example.com' })}
                {input('panelPort', 'پورت پنل', { ltr: true })}
                {input('username', 'نام کاربری پنل', { ltr: true })}
                {input('password', 'رمز پنل', { ltr: true, type: 'password', hint: server?.hasPassword ? 'خالی بماند تا رمز فعلی بماند.' : undefined })}
                {input('inbound', 'شناسه اینباند', { ltr: true })}
                {input('inboundPort', 'پورت اینباند', { ltr: true })}
                <Field label="وضعیت" error={err('status')}>
                    {(p) => (
                        <select {...p} className="field" value={form.status} onChange={(e) => set({ status: e.target.value as ServerStatus })}>
                            {Object.entries(serverStatus).map(([v, m]) => (
                                <option key={v} value={v}>
                                    {m.label}
                                </option>
                            ))}
                        </select>
                    )}
                </Field>
            </div>
            {save.failure && <p role="alert" className="mt-3 font-bold text-error">{save.failure}</p>}
            <p className="mt-3 text-[13px] text-muted">سرور فعال هنگام ذخیره با این اطلاعات وارد پنل می‌شود؛ اگر ورود نشود، ذخیره نمی‌شود.</p>
            <div className="mt-4 flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={save.saving}>
                    {save.saving ? 'در حال بررسی و ذخیره…' : server ? 'ذخیره سرور' : 'افزودن سرور'}
                </button>
                {onCancel && (
                    <button type="button" className="btn btn-ghost" onClick={onCancel}>
                        انصراف
                    </button>
                )}
            </div>
        </form>
    );
}

/* ---------- Clients ---------- */

function Clients({ location, servers, nav }: { location: Location & { id: number }; servers: ServerRow[]; nav: ReactNode }) {
    const { csrfToken } = useBoot();
    const scope = useIpScope();
    const list = useListParams({ tab: '', status: 'all', server: '', q: '' });
    const { data, error, loading, reload } = useQuery<{ page: number; lastPage: number; total: number; counts: Record<string, number>; clients: ClientRow[] }>(
        `${scope.api}/locations/${location.id}/clients?${list.query}`,
    );
    const [notice, setNotice] = useState<string | null>(null);
    const serverName = (id: number | null) => servers.find((s) => s.id === id)?.name ?? '—';

    async function remove(c: ClientRow) {
        try {
            const res = await postJson<{ success: string }>(`${scope.api}/clients/${c.id}/delete`, csrfToken, {});
            setNotice(res.success);
            reload();
        } catch (e) {
            setNotice((e as ApiError).message);
        }
    }

    const filters = scope.clientFilters;

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={scope.base} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                {scope.title}
            </Link>
            <h1 className="mb-5 text-[22px] leading-tight font-bold">کلاینت‌های {location.name}</h1>
            {nav}
            {notice && (
                <div className="mb-4">
                    <Notice>{notice}</Notice>
                </div>
            )}
            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label="وضعیت کلاینت" items={filters} value={list.values.status} counts={data?.counts} onChange={(status) => list.update({ status })} />
                <div className="flex flex-1 flex-wrap gap-2 xl:justify-end">
                    <label className="sr-only" htmlFor="ip-server-filter">سرور</label>
                    <select id="ip-server-filter" className="field w-auto" value={list.values.server} onChange={(e) => list.update({ server: e.target.value })}>
                        <option value="">همه سرورها</option>
                        {servers.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.name}
                            </option>
                        ))}
                    </select>
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی کلاینت" placeholder="نام کلاینت یا شماره سفارش" />
                </div>
            </div>
            <section aria-label="فهرست کلاینت‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.clients.length === 0 ? (
                    <EmptyState>کلاینتی در این فهرست نیست.</EmptyState>
                ) : (
                    <div className={`overflow-x-auto transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="w-full min-w-[820px]">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">کلاینت</th>
                                    <th scope="col" className="px-3 text-start font-medium">وضعیت</th>
                                    <th scope="col" className="px-3 text-start font-medium">سرور</th>
                                    <th scope="col" className="px-3 text-start font-medium">سفارش و خریدار</th>
                                    <th scope="col" className="px-3 text-start font-medium">پلن</th>
                                    <th scope="col" className="px-3 text-start font-medium">انقضا</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-end font-medium">
                                        <span className="sr-only">کارها</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.clients.map((c) => (
                                    <tr key={c.id} className="border-b border-stroke last:border-0">
                                        <td className="py-3 ps-5 pe-3">
                                            <span dir="ltr" className="latin font-bold">{c.name || `#${c.id}`}</span>
                                            {c.code && <CopyButton value={c.code} label={`لینک کانفیگ ${c.name}`} />}
                                        </td>
                                        <td className="px-3">
                                            <Badge tone={(clientStatus[c.status] ?? clientStatus.ended).tone} icon={c.status === 'active' ? CircleCheck : CircleSlash} label={(clientStatus[c.status] ?? clientStatus.ended).label} />
                                        </td>
                                        <td dir="ltr" className="latin px-3 text-end text-[13px]">{serverName(c.serverId)}</td>
                                        <td className="px-3 text-[13px]">
                                            {c.orderId ? (
                                                <>
                                                    <Link to={scope.orderPath(c.orderId)} className="num font-medium underline-offset-4 hover:underline">#{c.orderId}</Link>
                                                    {c.user && (
                                                        <>
                                                            {' · '}
                                                            <Link to={scope.userPath(c.user.id)} className="underline-offset-4 hover:underline"><bdi>{c.user.name || c.user.mobile}</bdi></Link>
                                                        </>
                                                    )}
                                                </>
                                            ) : (
                                                <span className="text-muted">—</span>
                                            )}
                                        </td>
                                        <td className="px-3 text-[13px]">
                                            {c.plan ?? '—'}
                                            {c.volume != null && c.orderId ? <span className="num text-muted"> · {c.volume} گیگ</span> : null}
                                        </td>
                                        <td dir="ltr" className="num px-3 text-end text-[13px] text-muted">{c.expiresAt ?? '—'}</td>
                                        <td className="py-2 ps-3 pe-5 text-end">
                                            {c.status === 'free' && !c.orderId && (
                                                <ConfirmButton
                                                    label="حذف"
                                                    confirmLabel="حذف"
                                                    question="از فهرست حذف شود؟"
                                                    icon={<Trash2 aria-hidden size={15} />}
                                                    className="btn btn-ghost text-error"
                                                    onConfirm={() => remove(c)}
                                                />
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="کلاینت" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
