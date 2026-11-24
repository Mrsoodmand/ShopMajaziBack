import { useEffect, useState } from 'react';
import { BadgeDollarSign, CircleCheck, CircleSlash, CircleX, Clock, Save } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { toman } from '../../lib/format';
import { Badge, type Tone } from '../../components/Status';
import { ErrorState, FilterTabs, Notice, Panel } from '../../components/ui';
import { Switch } from '../../components/form';
import { PageHeader, useListParams } from '../../components/list';
import { PageTabs, useUrlTab } from '../../components/PageTabs';
import { DataTable, ListEmpty, ListPager, type Column } from '../../components/DataList';
import { FormFieldsEditor, type FormField } from '../../components/FormFieldsEditor';

/*
 * درخواست اکانت (App\Services\AccountRequests, Admin\AccountRequestApiController): customers ask for a subscription
 * the site doesn't sell; support answers with a price (a custom order they pay in /account) or a rejection.
 */

export type ShownStatus = 'pending' | 'quoted' | 'paid' | 'rejected' | 'cancelled';
type Stored = 'pending' | 'quoted' | 'rejected' | 'cancelled';
export const STATUS: Record<ShownStatus, { label: string; tone: Tone; icon: typeof Clock }> = {
    pending: { label: 'در انتظار بررسی', tone: 'info', icon: Clock },
    quoted: { label: 'قیمت داده شد', tone: 'warn', icon: BadgeDollarSign },
    paid: { label: 'پرداخت شد', tone: 'success', icon: CircleCheck },
    rejected: { label: 'رد شده', tone: 'danger', icon: CircleX },
    cancelled: { label: 'لغو شده', tone: 'neutral', icon: CircleSlash },
};
export const StatusBadge = ({ status }: { status: ShownStatus }) => <Badge tone={STATUS[status].tone} icon={STATUS[status].icon} label={STATUS[status].label} />;

// The tabs filter by the stored status; «قیمت داده شد» holds both quoted and paid.
const FILTERS: { id: Stored; label: string }[] = [
    { id: 'pending', label: 'در انتظار بررسی' },
    { id: 'quoted', label: 'قیمت داده شده' },
    { id: 'rejected', label: 'رد شده' },
    { id: 'cancelled', label: 'لغو شده' },
];

type Meta = { page: number; lastPage: number; total: number; perPage: number };
type Row = { id: number; status: ShownStatus; site: string; url: string | null; user: { id: number; name: string | null; mobile: string } | null; orderId: number | null; price: number | null; createdAt: string | null };

const TABS = ['requests', 'form'] as const;
type Tab = (typeof TABS)[number];

export default function AccountRequestsPage() {
    const [tab, setTab] = useUrlTab(TABS, 'requests');
    const pending = useQuery<{ counts: Record<Stored, number> }>('/admin/api/account-requests?status=pending&per=20');
    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="درخواست اکانت" subtitle="اکانت‌هایی که در سایت نیست و مشتری‌ها درخواستشان را فرستاده‌اند؛ با قیمت جواب بدهید یا رد کنید." />
            <PageTabs
                id="acc-req"
                label="بخش‌های درخواست اکانت"
                className="mb-5"
                tabs={[
                    { id: 'requests', label: 'درخواست‌ها', count: pending.data?.counts.pending, tone: pending.data?.counts.pending ? 'attention' : undefined, note: 'در انتظار' },
                    { id: 'form', label: 'فرم و تنظیمات' },
                ]}
                value={tab}
                onChange={(t: Tab) => setTab(t)}
            />
            {tab === 'requests' ? <Requests /> : <FormSettings />}
        </div>
    );
}

function Requests() {
    const list = useListParams({ status: 'pending', q: '', per: '' });
    const { data, error, loading, reload } = useQuery<{ counts: Record<Stored, number>; requests: Row[]; meta: Meta }>(`/admin/api/account-requests?${list.query}`);
    const columns: Column<Row>[] = [
        { key: 'id', header: 'شماره', cell: (r) => <span className="num text-muted">#{r.id}</span> },
        {
            key: 'site',
            header: 'سایت',
            primary: true,
            cell: (r) => (
                <span>
                    <span className="block font-bold">{r.site}</span>
                    {r.url && <span dir="ltr" className="block text-end text-[12px] text-muted">{r.url}</span>}
                </span>
            ),
        },
        { key: 'user', header: 'کاربر', cell: (r) => (r.user ? <span><span className="block">{r.user.name || '—'}</span><span dir="ltr" className="num block text-end text-[12px] text-muted">{r.user.mobile}</span></span> : '—') },
        { key: 'price', header: 'قیمت', from: 'lg', cell: (r) => (r.price ? <span className="num text-[13px]">{toman(r.price)}</span> : <span className="text-muted">—</span>) },
        { key: 'status', header: 'وضعیت', cell: (r) => <StatusBadge status={r.status} /> },
        { key: 'date', header: 'تاریخ', from: 'lg', cell: (r) => <span dir="ltr" className="num text-[13px] text-muted">{r.createdAt}</span> },
    ];
    return (
        <>
            <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                <FilterTabs label="وضعیت درخواست" items={FILTERS} value={list.values.status as Stored} counts={data?.counts} attention="pending" onChange={(status) => list.update({ status })} />
                <input type="search" className="field max-w-[280px]" placeholder="سایت، موبایل یا شماره" defaultValue={list.values.q} onKeyDown={(e) => e.key === 'Enter' && list.update({ q: (e.target as HTMLInputElement).value.trim() })} aria-label="جستجوی درخواست" />
            </div>
            <DataTable
                label="درخواست‌های اکانت"
                rows={data?.requests}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(r) => r.id}
                href={(r) => `/account-requests/${r.id}`}
                rowLabel={(r) => `درخواست ${r.id}`}
                rowStatus={(r) => STATUS[r.status].tone}
                offset={data ? (data.meta.page - 1) * data.meta.perPage : 0}
                skeleton={5}
                mobile={(r) => (
                    <span className="flex items-center justify-between gap-3">
                        <span className="min-w-0">
                            <span className="block truncate font-bold">{r.site}</span>
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

type SettingsData = { settings: { open: boolean }; fields: FormField[]; locked: string[]; maxMb: number };

function FormSettings() {
    const { data, error, reload } = useQuery<SettingsData>('/admin/api/account-requests/settings');
    const save = useSave();
    const [open, setOpen] = useState(true);
    const [fields, setFields] = useState<FormField[]>([]);

    useEffect(() => {
        if (data) {
            setOpen(data.settings.open);
            setFields(data.fields);
        }
    }, [data]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <div className="skeleton h-64 rounded-xl" />;

    return (
        <div className="grid gap-5">
            <Panel title="تنظیمات">
                <Switch label="پذیرش درخواست جدید" hint="خاموش: فرم درخواست در سایت نشان داده نمی‌شود. درخواست‌های قبلی سر جایشان می‌مانند." checked={open} onChange={setOpen} />
            </Panel>
            <Panel title="فرم درخواست">
                <p className="mb-3 text-[13px] text-muted">«نام سایت یا سرویس» همیشه در فرم است؛ «آدرس سایت» را می‌توانید اختیاری یا خاموش کنید. تغییر فرم، درخواست‌های قبلی را عوض نمی‌کند.</p>
                <FormFieldsEditor fields={fields} onChange={setFields} maxMb={data.maxMb} locked={data.locked} placeholder="مثلا: ایمیل حسابی که اشتراک روی آن فعال شود" />
            </Panel>
            <div className="flex flex-wrap items-center gap-3">
                <button type="button" className="btn btn-primary" disabled={save.saving} onClick={async () => { if (await save.run('/admin/api/account-requests/settings', { settings: { open }, fields })) reload(); }}>
                    <Save aria-hidden size={16} />
                    {save.saving ? 'در حال ذخیره…' : 'ذخیره فرم و تنظیمات'}
                </button>
                {save.saved && <Notice>{save.saved}</Notice>}
                {save.failure && <p role="alert" className="font-medium text-error">{save.failure}</p>}
            </div>
        </div>
    );
}
