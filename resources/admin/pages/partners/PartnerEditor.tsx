import { useEffect, useState, type KeyboardEvent } from 'react';
import { Link, useNavigate, useParams } from 'react-router';
import { Trash2 } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { parseAmount, toman } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, Switch } from '../../components/form';
import { ErrorState, FieldError, Notice, Panel, Row } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, problemsFrom } from '../../components/list';
import { DEPOSIT_LABEL, type ServiceOption } from './PartnersList';

type Deposit = keyof typeof DEPOSIT_LABEL;
type Partner = {
    id: number;
    name: string;
    mobile: string;
    live: boolean;
    percent: string;
    serviceIds: number[];
    deposit: Deposit;
    wallet: number;
    paidOut: number;
    lastPaidAt: string | null;
    earned: number;
    createdAt: string | null;
};
type Profit = { id: number; orderId: number | null; service: string; amount: number; profit: number; at: string | null };
type Settlement = { id: number; amount: number; note: string; at: string | null };
type Data = { services: ServiceOption[]; partner: Partner; profits: Profit[]; settlements: Settlement[] };
type Form = { name: string; mobile: string; live: boolean; percent: string; serviceIds: number[]; deposit: Deposit };

const labels = { name: 'نام', mobile: 'موبایل', percent: 'درصد سهم', serviceIds: 'خدمت‌ها', deposit: 'دوره تسویه' };

export default function PartnerEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const existing = useQuery<Data>(isNew ? null : `/admin/api/partners/${id}`);
    const list = useQuery<{ services: ServiceOption[] }>(isNew ? '/admin/api/partners' : null);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(isNew ? { name: '', mobile: '', live: true, percent: '', serviceIds: [], deposit: 'monthly' } : null);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const partner = existing.data?.partner;
    useEffect(() => {
        if (partner) setForm({ name: partner.name, mobile: partner.mobile, live: partner.live, percent: partner.percent, serviceIds: partner.serviceIds, deposit: partner.deposit });
    }, [partner]);

    const services = existing.data?.services ?? list.data?.services;
    const error = existing.error ?? list.error;
    if (error) return <ErrorState error={error} onRetry={isNew ? list.reload : existing.reload} boxed />;
    if (!form || !services) return <EditorSkeleton />;

    const set = (patch: Partial<Form>) => {
        save.clearSaved();
        setForm((f) => (f ? { ...f, ...patch } : f));
    };
    const err = (k: string) => save.errors[k];
    const toggleService = (sid: number, on: boolean) => set({ serviceIds: on ? [...form.serviceIds, sid] : form.serviceIds.filter((s) => s !== sid) });

    async function submit() {
        if (!form) return;
        const percent = form.percent.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)));
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/partners' : `/admin/api/partners/${id}`, { ...form, percent: percent === '' ? null : Number(percent) });
        if (res && isNew) navigate(`/partners/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`/admin/api/partners/${id}/delete`, save.csrfToken, {});
            navigate('/partners', { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
        } finally {
            setDeleting(false);
        }
    }

    return (
        <EditorLayout
            backTo="/partners"
            backLabel="همکاران"
            title={isNew ? 'همکار جدید' : form.name || 'همکار'}
            subtitle={partner?.createdAt ? <>همکار از <span dir="ltr" className="num">{partner.createdAt}</span></> : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? 'ساخت همکار' : 'ذخیره همکار'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-4">
                    <Switch label="فعال" hint="همکار غیرفعال از سفارش‌های تازه سهمی نمی‌گیرد." checked={form.live} onChange={(live) => set({ live })} />
                    {partner && (
                        <div className="border-t border-stroke pt-4">
                            {partner.earned > 0 ? (
                                <p className="text-[13px] text-muted">همکاری که سابقه سهم دارد حذف نمی‌شود؛ غیرفعالش کنید.</p>
                            ) : (
                                <ConfirmButton
                                    label="حذف همکار"
                                    confirmLabel="حذف"
                                    question="این همکار حذف شود؟"
                                    icon={<Trash2 aria-hidden size={16} />}
                                    busy={deleting}
                                    className="btn btn-secondary w-full text-error"
                                    onConfirm={remove}
                                />
                            )}
                            {deleteError && <p role="alert" className="mt-2 text-[13px] font-bold text-error">{deleteError}</p>}
                        </div>
                    )}
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={err('name')}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} />}
                    </Field>
                    <Field label="موبایل" error={err('mobile')}>
                        {(p) => <input {...p} inputMode="tel" dir="ltr" className="field num text-end" value={form.mobile} onChange={(e) => set({ mobile: e.target.value })} placeholder="09121234567" />}
                    </Field>
                </div>
            </Panel>

            <Panel title="سهم و تسویه">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="درصد سهم" error={err('percent')} hint="از مبلغ هر سفارش پرداخت‌شده خدمت‌های زیر.">
                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={form.percent} onChange={(e) => set({ percent: e.target.value })} placeholder="50" />}
                    </Field>
                    <Field label="دوره تسویه" error={err('deposit')}>
                        {(p) => (
                            <select {...p} className="field" value={form.deposit} onChange={(e) => set({ deposit: e.target.value as Deposit })}>
                                {(Object.keys(DEPOSIT_LABEL) as Deposit[]).map((d) => (
                                    <option key={d} value={d}>
                                        {DEPOSIT_LABEL[d]}
                                    </option>
                                ))}
                            </select>
                        )}
                    </Field>
                </div>
                <fieldset className="m-0 mt-5 border-0 p-0" aria-describedby={err('serviceIds') ? 'partner-services-error' : undefined}>
                    <legend className="mb-2 font-medium">خدمت‌ها</legend>
                    <div className="grid gap-2 sm:grid-cols-3">
                        {services.map((s) => (
                            <label key={s.id} className="flex cursor-pointer items-center gap-2.5 rounded-xl border border-stroke px-3 py-2.5 hover:bg-surface-2 has-[:checked]:border-ink">
                                <input type="checkbox" className="size-4 accent-[var(--primary)]" checked={form.serviceIds.includes(s.id)} onChange={(e) => toggleService(s.id, e.target.checked)} />
                                {s.label}
                            </label>
                        ))}
                    </div>
                    {err('serviceIds') && <FieldError id="partner-services-error">{err('serviceIds')}</FieldError>}
                </fieldset>
            </Panel>

            {existing.data && <Earnings data={existing.data} onChanged={existing.reload} />}
        </EditorLayout>
    );
}

function Earnings({ data, onChanged }: { data: Data; onChanged: () => void }) {
    const { partner, profits, settlements } = data;
    return (
        <>
            <Panel title="کیف پول همکار">
                <div className="grid gap-6 md:grid-cols-2">
                    <dl className="m-0">
                        <Row label="طلب فعلی" num>
                            <span className="font-bold">{toman(partner.wallet)}</span>
                        </Row>
                        <Row label="کل سهم ثبت‌شده" num>
                            {toman(partner.earned)}
                        </Row>
                        <Row label="پرداخت‌شده تا امروز" num>
                            {toman(partner.paidOut)}
                        </Row>
                        <Row label="آخرین تسویه" num>
                            {partner.lastPaidAt ?? '—'}
                        </Row>
                    </dl>
                    <SettleForm partner={partner} onDone={onChanged} />
                </div>
                {settlements.length > 0 && (
                    <div className="mt-5 border-t border-stroke pt-4">
                        <h3 className="mb-2 font-bold">تسویه‌های ثبت‌شده</h3>
                        <ul className="divide-y divide-stroke">
                            {settlements.map((s) => (
                                <li key={s.id} className="flex flex-wrap items-center gap-x-4 gap-y-1 py-2">
                                    <span className="num font-bold">{toman(s.amount)}</span>
                                    <span className="min-w-0 flex-1 text-muted">{s.note}</span>
                                    <span dir="ltr" className="num text-[13px] text-muted">{s.at}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </Panel>

            <Panel title="سهم از سفارش‌ها">
                {profits.length === 0 ? (
                    <p className="text-muted">هنوز سهمی ثبت نشده است.</p>
                ) : (
                    <div className="-mx-5 overflow-x-auto">
                        <table className="w-full min-w-[560px]">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="ps-5 pe-3 pb-2 text-start font-medium">سفارش</th>
                                    <th scope="col" className="px-3 pb-2 text-start font-medium">خدمت</th>
                                    <th scope="col" className="px-3 pb-2 text-end font-medium">مبلغ سفارش</th>
                                    <th scope="col" className="px-3 pb-2 text-end font-medium">سهم</th>
                                    <th scope="col" className="ps-3 pe-5 pb-2 text-start font-medium">زمان</th>
                                </tr>
                            </thead>
                            <tbody>
                                {profits.map((r) => (
                                    <tr key={r.id} className="border-b border-stroke last:border-0">
                                        <td className="py-2.5 ps-5 pe-3">
                                            {r.orderId ? <Link to={`/orders/${r.orderId}`} className="num font-medium underline-offset-4 hover:underline">#{r.orderId}</Link> : '—'}
                                        </td>
                                        <td className="px-3 text-[13px]">{r.service}</td>
                                        <td className="num px-3 text-end">{toman(r.amount)}</td>
                                        <td className="num px-3 text-end font-bold">{toman(r.profit)}</td>
                                        <td dir="ltr" className="num ps-3 pe-5 text-end text-[13px] text-muted">{r.at}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        {profits.length >= 50 && <p className="mt-3 px-5 text-[13px] text-muted">۵۰ سهم آخر نمایش داده شده است.</p>}
                    </div>
                )}
            </Panel>
        </>
    );
}

function SettleForm({ partner, onDone }: { partner: Partner; onDone: () => void }) {
    const save = useSave();
    const [amount, setAmount] = useState('');
    const [note, setNote] = useState('');

    // This sits inside the partner form, so it is a group with its own button, not a nested form.
    async function submit() {
        const value = parseAmount(amount);
        const res = await save.run(`/admin/api/partners/${partner.id}/settle`, { amount: value ?? amount, note });
        if (res) {
            setAmount('');
            setNote('');
            onDone();
        }
    }

    function onEnter(e: KeyboardEvent) {
        if (e.key === 'Enter' && (e.target as HTMLElement).tagName === 'INPUT') {
            e.preventDefault();
            submit();
        }
    }

    if (partner.wallet <= 0) return <p className="self-center text-muted">طلبی برای تسویه نیست.</p>;

    return (
        <div role="group" aria-label="ثبت تسویه" onKeyDown={onEnter}>
            <h3 className="mb-2 font-bold">ثبت تسویه</h3>
            <p className="mb-3 text-[13px] text-muted">مبلغی را که به همکار پرداخت کرده‌اید ثبت کنید؛ از طلبش کم می‌شود.</p>
            <div className="grid gap-3">
                <Field label="مبلغ (تومان)" error={save.errors.amount} hint={parseAmount(amount) ? toman(parseAmount(amount)) : undefined}>
                    {(p) => (
                        <div className="flex gap-2">
                            <input {...p} inputMode="numeric" dir="ltr" className="field num flex-1 text-end" value={amount} onChange={(e) => setAmount(e.target.value)} />
                            <button type="button" className="btn btn-ghost shrink-0" onClick={() => setAmount(String(partner.wallet))}>
                                همه طلب
                            </button>
                        </div>
                    )}
                </Field>
                <Field label="توضیح" optional error={save.errors.note}>
                    {(p) => <input {...p} className="field" value={note} onChange={(e) => setNote(e.target.value)} placeholder="مثلاً: واریز به کارت، پیگیری ۱۲۳۴" />}
                </Field>
            </div>
            {save.failure && <p role="alert" className="mt-2 font-bold text-error">{save.failure}</p>}
            <button type="button" className="btn btn-secondary mt-3 w-full" disabled={save.saving} onClick={submit}>
                {save.saving ? 'در حال ثبت…' : 'ثبت تسویه'}
            </button>
            {save.saved && <Notice>{save.saved}</Notice>}
        </div>
    );
}
