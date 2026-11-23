import { useEffect, useState } from 'react';
import { Building2, CircleCheck, CircleSlash, CircleX, Clock, Save } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count, toman } from '../../lib/format';
import { Badge, type Tone } from '../../components/Status';
import { ErrorState, FilterTabs, Notice, Panel } from '../../components/ui';
import { Field, RowsEditor, Switch } from '../../components/form';
import { PageHeader, useListParams } from '../../components/list';
import { PageTabs, useUrlTab } from '../../components/PageTabs';
import { DataTable, ListEmpty, ListPager, type Column } from '../../components/DataList';
import { DiscountTable, discountSummary, fromDraft, toDraft, type DiscountDraft, type Discounts, type Family } from './DiscountTable';

/*
 * حساب‌های سازمانی (App\Services\Organizations, Admin\OrganizationApiController): requests customers sent from
 * /account, approved organisations with their discount, and the form + settings.
 */

export type ReqStatus = 'pending' | 'approved' | 'rejected' | 'cancelled';
export const STATUS: Record<ReqStatus, { label: string; tone: Tone; icon: typeof Clock }> = {
    pending: { label: 'در انتظار بررسی', tone: 'info', icon: Clock },
    approved: { label: 'تایید شده', tone: 'success', icon: CircleCheck },
    rejected: { label: 'رد شده', tone: 'danger', icon: CircleX },
    cancelled: { label: 'لغو شده', tone: 'neutral', icon: CircleSlash },
};
export const StatusBadge = ({ status }: { status: ReqStatus }) => <Badge tone={STATUS[status].tone} icon={STATUS[status].icon} label={STATUS[status].label} />;

type Meta = { page: number; lastPage: number; total: number; perPage: number };

const TABS = ['requests', 'organizations', 'form'] as const;
type Tab = (typeof TABS)[number];

export default function OrganizationsPage() {
    const [tab, setTab] = useUrlTab(TABS, 'requests');
    const pending = useQuery<{ counts: Record<ReqStatus, number> }>('/admin/api/organizations/requests?status=pending&per=20');
    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="حساب‌های سازمانی" subtitle="درخواست‌هایی که مشتری‌ها از پنل کاربری می‌فرستند، تخفیف هر سازمان و فرمی که پر می‌کنند." />
            <PageTabs
                id="org"
                label="بخش‌های حساب سازمانی"
                className="mb-5"
                tabs={[
                    { id: 'requests', label: 'درخواست‌ها', count: pending.data?.counts.pending, tone: pending.data?.counts.pending ? 'attention' : undefined, note: 'در انتظار' },
                    { id: 'organizations', label: 'سازمان‌ها' },
                    { id: 'form', label: 'فرم و تنظیمات' },
                ]}
                value={tab}
                onChange={(t: Tab) => setTab(t)}
            />
            {tab === 'requests' && <Requests />}
            {tab === 'organizations' && <Organizations />}
            {tab === 'form' && <FormSettings />}
        </div>
    );
}

/* ---------- Requests ---------- */

type ReqRow = { id: number; status: ReqStatus; company: string | null; taxCode: string | null; user: { id: number; name: string | null; mobile: string } | null; createdAt: string | null; reviewedAt: string | null };

function Requests() {
    const list = useListParams({ status: 'pending', q: '', per: '' });
    const { data, error, loading, reload } = useQuery<{ counts: Record<ReqStatus, number>; requests: ReqRow[]; meta: Meta }>(`/admin/api/organizations/requests?${list.query}`);
    const columns: Column<ReqRow>[] = [
        { key: 'id', header: 'شماره', cell: (r) => <span className="num text-muted">#{r.id}</span> },
        { key: 'company', header: 'شرکت', primary: true, cell: (r) => <span className="font-bold">{r.company || '—'}</span> },
        { key: 'user', header: 'کاربر', cell: (r) => (r.user ? <span><span className="block">{r.user.name || '—'}</span><span dir="ltr" className="num block text-end text-[12px] text-muted">{r.user.mobile}</span></span> : '—') },
        { key: 'tax', header: 'کد اقتصادی', from: 'lg', cell: (r) => <span dir="ltr" className="num text-[13px]">{r.taxCode || '—'}</span> },
        { key: 'status', header: 'وضعیت', cell: (r) => <StatusBadge status={r.status} /> },
        { key: 'date', header: 'تاریخ', from: 'lg', cell: (r) => <span dir="ltr" className="num text-[13px] text-muted">{r.createdAt}</span> },
    ];
    return (
        <>
            <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                <FilterTabs
                    label="وضعیت درخواست"
                    items={(Object.keys(STATUS) as ReqStatus[]).map((id) => ({ id, label: STATUS[id].label }))}
                    value={list.values.status as ReqStatus}
                    counts={data?.counts}
                    attention="pending"
                    onChange={(status) => list.update({ status })}
                />
                <input type="search" className="field max-w-[280px]" placeholder="شرکت، کد اقتصادی یا موبایل" defaultValue={list.values.q} onKeyDown={(e) => e.key === 'Enter' && list.update({ q: (e.target as HTMLInputElement).value.trim() })} aria-label="جستجوی درخواست" />
            </div>
            <DataTable
                label="درخواست‌های حساب سازمانی"
                rows={data?.requests}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(r) => r.id}
                href={(r) => `/organizations/requests/${r.id}`}
                rowLabel={(r) => `درخواست ${r.id}`}
                rowStatus={(r) => STATUS[r.status].tone}
                offset={data ? (data.meta.page - 1) * data.meta.perPage : 0}
                skeleton={5}
                mobile={(r) => (
                    <span className="flex items-center justify-between gap-3">
                        <span className="min-w-0">
                            <span className="block truncate font-bold">{r.company || '—'}</span>
                            <span dir="ltr" className="num block text-end text-[12px] text-muted">{r.user?.mobile}</span>
                        </span>
                        <StatusBadge status={r.status} />
                    </span>
                )}
                empty={<ListEmpty filtered={!!list.values.q} onClear={() => list.update({ q: '' })}>درخواستی در این بخش نیست.</ListEmpty>}
            />
            {data && <ListPager page={data.meta.page} lastPage={data.meta.lastPage} total={data.meta.total} perPage={data.meta.perPage} noun="درخواست" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </>
    );
}

/* ---------- Organisations ---------- */

type OrgRow = { userId: number; name: string | null; mobile: string; company: string | null; taxCode: string | null; requestId: number | null; discounts: Discounts; discountedOrders: number; discountGiven: number; since: string | null };

function Organizations() {
    const list = useListParams({ q: '', per: '' });
    const { data, error, loading, reload } = useQuery<{ organizations: OrgRow[]; meta: Meta }>(`/admin/api/organizations?${list.query}`);
    const families = useQuery<SettingsData>('/admin/api/organizations/settings').data?.families ?? [];
    const columns: Column<OrgRow>[] = [
        { key: 'company', header: 'شرکت', primary: true, cell: (o) => <span><span className="block font-bold">{o.company || '—'}</span><span className="block text-[12px] text-muted">{o.name || '—'} · <span dir="ltr" className="num">{o.mobile}</span></span></span> },
        { key: 'discount', header: 'تخفیف‌ها', cell: (o) => <span className="text-[13px] font-medium">{discountSummary(o.discounts, families)}</span> },
        { key: 'orders', header: 'خرید با تخفیف', from: 'lg', cell: (o) => <span className="num text-[13px]">{count(o.discountedOrders)} سفارش · {toman(o.discountGiven)}</span> },
        { key: 'since', header: 'از', from: 'lg', cell: (o) => <span dir="ltr" className="num text-[13px] text-muted">{o.since ?? '—'}</span> },
    ];
    return (
        <>
            <div className="mb-3 flex justify-end">
                <input type="search" className="field max-w-[280px]" placeholder="شرکت، نام یا موبایل" defaultValue={list.values.q} onKeyDown={(e) => e.key === 'Enter' && list.update({ q: (e.target as HTMLInputElement).value.trim() })} aria-label="جستجوی سازمان" />
            </div>
            <DataTable
                label="سازمان‌ها"
                rows={data?.organizations}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(o) => o.userId}
                href={(o) => (o.requestId ? `/organizations/requests/${o.requestId}` : `/users/${o.userId}`)}
                rowLabel={(o) => `سازمان ${o.company ?? o.mobile}`}
                offset={data ? (data.meta.page - 1) * data.meta.perPage : 0}
                skeleton={5}
                mobile={(o) => (
                    <span className="flex items-center justify-between gap-3">
                        <span className="min-w-0"><span className="block truncate font-bold">{o.company || '—'}</span><span dir="ltr" className="num block text-end text-[12px] text-muted">{o.mobile}</span></span>
                        <span className="text-end text-[12px] font-medium">{discountSummary(o.discounts, families)}</span>
                    </span>
                )}
                empty={<ListEmpty filtered={!!list.values.q} onClear={() => list.update({ q: '' })}>هنوز حساب سازمانی تایید نشده است.</ListEmpty>}
            />
            {data && <ListPager page={data.meta.page} lastPage={data.meta.lastPage} total={data.meta.total} perPage={data.meta.perPage} noun="سازمان" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </>
    );
}

/* ---------- Form and settings ---------- */

type FieldType = 'text' | 'email' | 'phone' | 'textarea' | 'number' | 'select' | 'yesno' | 'date' | 'file';
type FormField = { id?: number; key: string; title: string; title_en: string | null; type: FieldType; required: boolean; enabled: boolean; builtin: boolean; hint: string | null; hint_en: string | null; options: string[]; accept: string[]; max_mb: number | null };
type Settings = { open: boolean; defaults: Discounts };
type SettingsData = { settings: Settings; families: Family[]; fields: FormField[]; maxDiscount: number; maxMb: number };

const TYPE_LABELS: Record<FieldType, string> = {
    text: 'متن کوتاه', email: 'ایمیل', phone: 'تلفن', textarea: 'متن بلند', number: 'عدد', select: 'انتخاب از فهرست', yesno: 'بله / خیر', date: 'تاریخ', file: 'فایل',
};

function FormSettings() {
    const { data, error, reload } = useQuery<SettingsData>('/admin/api/organizations/settings');
    const save = useSave();
    const [settings, setSettings] = useState<Settings | null>(null);
    const [fields, setFields] = useState<FormField[]>([]);
    const [defaults, setDefaults] = useState<DiscountDraft>({});

    useEffect(() => {
        if (data) {
            setSettings(data.settings);
            setDefaults(toDraft(data.settings.defaults));
            setFields(data.fields);
        }
    }, [data]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data || !settings) return <div className="skeleton h-64 rounded-xl" />;

    const move = (i: number, d: -1 | 1) => {
        const j = i + d;
        if (j < 0 || j >= fields.length) return;
        const next = [...fields];
        [next[i], next[j]] = [next[j], next[i]];
        setFields(next);
    };

    return (
        <div className="grid gap-5">
            <Panel title="تنظیمات">
                <Switch label="پذیرش درخواست جدید" hint="خاموش: فرم در پنل کاربری نشان داده نمی‌شود. سازمان‌های فعلی تخفیفشان را دارند." checked={settings.open} onChange={(open) => setSettings({ ...settings, open })} />
                <p className="mt-5 font-medium">تخفیف پیشنهادی هنگام تایید</p>
                <p className="mb-2 text-[13px] text-muted">هنگام تایید هر درخواست این درصدها از قبل پر می‌شوند و برای هر شرکت جدا قابل تغییرند.</p>
                <div className="max-w-md">
                    <DiscountTable families={data.families} value={defaults} onChange={setDefaults} errors={Object.fromEntries(Object.entries(save.errors).filter(([k]) => k.startsWith('settings.defaults.')).map(([k, v]) => [k.slice(18), v]))} max={data.maxDiscount} />
                </div>
            </Panel>

            <Panel title="فرم درخواست">
                <p className="mb-3 text-[13px] text-muted">پنج فیلد اصلی شرکت را می‌توان خاموش یا اختیاری کرد ولی حذف نمی‌شوند. تغییر فرم، درخواست‌های قبلی را عوض نمی‌کند.</p>
                <RowsEditor
                    rows={fields}
                    onChange={(rows) => {
                        // A removal keeps the other rows as they were; built-in fields can't be removed.
                        const removedBuiltin = rows.length < fields.length && fields.some((f) => f.builtin && !rows.includes(f));
                        setFields(removedBuiltin ? fields : rows);
                    }}
                    make={(): FormField => ({ key: '', title: '', title_en: null, type: 'text', required: true, enabled: true, builtin: false, hint: null, hint_en: null, options: [], accept: ['image'], max_mb: 5 })}
                    addLabel="افزودن سوال"
                    empty="فرمی تعریف نشده است."
                    removeLabel={(r, i) => `حذف سوال ${r.title || i + 1}`}
                    canRemove={(r) => !r.builtin}
                    render={(row, update, i) => (
                        <div className={`grid gap-3 ${row.enabled ? '' : 'opacity-60'}`}>
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <span className="flex items-center gap-2 text-[13px] text-muted">
                                    <span className="num">{i + 1}.</span>
                                    {row.builtin ? <span className="rounded-md bg-surface-2 px-1.5 py-0.5"><Building2 aria-hidden size={12} className="inline" /> فیلد اصلی</span> : <span>سوال شما</span>}
                                </span>
                                <span className="flex gap-1">
                                    <button type="button" className="btn btn-ghost h-7 px-2 text-[13px]" onClick={() => move(i, -1)} disabled={i === 0} aria-label="بالاتر">▲</button>
                                    <button type="button" className="btn btn-ghost h-7 px-2 text-[13px]" onClick={() => move(i, 1)} disabled={i === fields.length - 1} aria-label="پایین‌تر">▼</button>
                                </span>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px]">
                                <Field label="عنوان">{(p) => <input {...p} className="field" value={row.title} onChange={(e) => update({ title: e.target.value })} placeholder="چند نفر در شرکت کار می‌کنند؟" />}</Field>
                                <Field label="نوع">
                                    {(p) => (
                                        <select {...p} className="field" value={row.type} disabled={row.builtin} onChange={(e) => update({ type: e.target.value as FieldType })}>
                                            {(Object.keys(TYPE_LABELS) as FieldType[]).map((t) => <option key={t} value={t}>{TYPE_LABELS[t]}</option>)}
                                        </select>
                                    )}
                                </Field>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <Field label="عنوان انگلیسی" optional>{(p) => <input {...p} dir="ltr" className="field latin" value={row.title_en ?? ''} onChange={(e) => update({ title_en: e.target.value })} />}</Field>
                                <Field label="راهنما زیر خانه" optional>{(p) => <input {...p} className="field" value={row.hint ?? ''} onChange={(e) => update({ hint: e.target.value })} />}</Field>
                            </div>
                            {row.type === 'select' && (
                                <Field label="گزینه‌ها" hint="با کاما جدا کنید؛ دست‌کم دو گزینه.">
                                    {(p) => <input {...p} className="field" defaultValue={row.options.join('، ')} onBlur={(e) => update({ options: e.target.value.split(/[,،]/).map((o) => o.trim()).filter(Boolean) })} placeholder="۱ تا ۱۰ نفر، ۱۱ تا ۵۰ نفر، بیشتر از ۵۰" />}
                                </Field>
                            )}
                            {row.type === 'file' && (
                                <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_140px] sm:items-end">
                                    <div className="flex flex-wrap gap-x-6 gap-y-2">
                                        <Switch label="عکس" checked={row.accept.includes('image')} onChange={(on) => update({ accept: on ? [...row.accept, 'image'] : row.accept.filter((a) => a !== 'image') })} />
                                        <Switch label="PDF" checked={row.accept.includes('pdf')} onChange={(on) => update({ accept: on ? [...row.accept, 'pdf'] : row.accept.filter((a) => a !== 'pdf') })} />
                                    </div>
                                    <Field label={`حداکثر حجم (تا ${data.maxMb}MB)`}>{(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.max_mb ?? 5} onChange={(e) => update({ max_mb: Number(e.target.value) || 1 })} />}</Field>
                                </div>
                            )}
                            <div className="flex flex-wrap gap-x-6">
                                <Switch label="نمایش در فرم" checked={row.enabled} onChange={(enabled) => update({ enabled })} />
                                <Switch label="اجباری" checked={row.required} onChange={(required) => update({ required })} />
                            </div>
                        </div>
                    )}
                />
            </Panel>

            <div className="flex flex-wrap items-center gap-3">
                <button type="button" className="btn btn-primary" disabled={save.saving} onClick={async () => { if (await save.run('/admin/api/organizations/settings', { settings: { ...settings, defaults: fromDraft(defaults) }, fields })) reload(); }}>
                    <Save aria-hidden size={16} />
                    {save.saving ? 'در حال ذخیره…' : 'ذخیره فرم و تنظیمات'}
                </button>
                {save.saved && <Notice>{save.saved}</Notice>}
                {save.failure && <p role="alert" className="font-medium text-error">{save.failure}</p>}
            </div>
        </div>
    );
}
