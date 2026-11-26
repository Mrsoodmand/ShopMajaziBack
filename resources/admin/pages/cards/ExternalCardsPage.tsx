import { useState, type FormEvent } from 'react';
import { CreditCard, Eye, EyeOff, Pencil, Plus, Trash2 } from 'lucide-react';
import { postJson, useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { Field, Switch } from '../../components/form';
import { CopyButton, ErrorState, Notice, Panel } from '../../components/ui';

// Admin › کارت‌های خارجی (super-admin; Admin\ExternalCardApiController, App\Models\ExternalCard): the shop's own
// cards support pays outside services with. Number and expiry are stored encrypted; the CVV is never stored, so
// whoever pays reads it from the card. Account orders pick one of these in admin › order (optional).

type Card = { id: number; title: string; number: string; expiry: string; last4: string; active: boolean; note: string | null; orders: number };
type Draft = { id?: number; title: string; number: string; expiry: string; active: boolean; note: string };

const API = '/admin/api/external-cards';
const EMPTY: Draft = { title: '', number: '', expiry: '', active: true, note: '' };

/** "6037991234564821" → "6037 9912 3456 4821". */
const spaced = (n: string) => n.replace(/(\d{4})(?=\d)/g, '$1 ');
const masked = (c: Card) => `•••• •••• •••• ${c.last4}`;

export default function ExternalCardsPage() {
    const { data, error, reload } = useQuery<{ cards: Card[] }>(API);
    const save = useSave();
    const [draft, setDraft] = useState<Draft | null>(null);
    const [shown, setShown] = useState<Record<number, boolean>>({});
    const [notice, setNotice] = useState<string | null>(null);
    const [failed, setFailed] = useState<string | null>(null);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <div className="skeleton mx-auto h-96 max-w-[960px] rounded-xl" />;

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (!draft) return;
        const res = await save.run(draft.id ? `${API}/${draft.id}` : API, draft);
        if (res) {
            setNotice(res.success ?? 'ذخیره شد.');
            setDraft(null);
            reload();
        }
    }

    async function remove(c: Card) {
        if (!window.confirm(`کارت «${c.title}» حذف شود؟`)) return;
        setNotice(null);
        setFailed(null);
        try {
            const res = await postJson<{ success?: string }>(`${API}/${c.id}/delete`, save.csrfToken, {});
            setNotice(res.success ?? 'حذف شد.');
            reload();
        } catch (err) {
            setFailed((err as Error).message);
        }
    }

    const set = (patch: Partial<Draft>) => setDraft((d) => (d ? { ...d, ...patch } : d));

    return (
        <div className="mx-auto grid max-w-[960px] gap-5">
            <div className="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-xl font-bold">کارت‌های خارجی</h1>
                    <p className="mt-1 text-muted">کارت‌هایی که با آن‌ها اکانت‌ها را از سرویس‌های خارجی می‌خرید. در هر سفارش اکانت می‌توانید کارت پرداخت را انتخاب کنید.</p>
                </div>
                {!draft && (
                    <button type="button" className="btn btn-primary" onClick={() => { save.clearSaved(); setNotice(null); setDraft({ ...EMPTY }); }}>
                        <Plus aria-hidden size={16} />
                        افزودن کارت
                    </button>
                )}
            </div>

            {notice && <Notice>{notice}</Notice>}
            {failed && <p role="alert" className="rounded-xl bg-error-soft px-3 py-2.5 font-medium text-error">{failed}</p>}

            {draft && (
                <Panel title={draft.id ? 'ویرایش کارت' : 'کارت جدید'}>
                    <form onSubmit={submit} className="grid gap-4" noValidate>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <Field label="نام کارت" hint="برای شناختن کارت در سفارش‌ها، مثلا «ویزا ملت»." error={save.errors.title}>
                                {(p) => <input {...p} className="field" value={draft.title} onChange={(e) => set({ title: e.target.value })} maxLength={100} />}
                            </Field>
                            <Field label="شماره کارت" error={save.errors.number}>
                                {(p) => <input {...p} dir="ltr" inputMode="numeric" autoComplete="off" className="field num" value={draft.number} onChange={(e) => set({ number: e.target.value })} placeholder="0000 0000 0000 0000" />}
                            </Field>
                            <Field label="تاریخ انقضا" hint="مثلا 08/28 یا 1405/06" error={save.errors.expiry}>
                                {(p) => <input {...p} dir="ltr" autoComplete="off" className="field num" value={draft.expiry} onChange={(e) => set({ expiry: e.target.value })} placeholder="MM/YY" />}
                            </Field>
                            <Field label="یادداشت" optional error={save.errors.note}>
                                {(p) => <input {...p} className="field" value={draft.note} onChange={(e) => set({ note: e.target.value })} maxLength={255} />}
                            </Field>
                        </div>
                        <p className="rounded-lg bg-surface-2 px-3 py-2 text-[13px] text-muted">CVV ذخیره نمی‌شود؛ هنگام پرداخت آن را از روی خود کارت وارد کنید.</p>
                        <Switch label="فعال" hint="کارت غیرفعال در انتخاب سفارش‌های جدید نشان داده نمی‌شود." checked={draft.active} onChange={(active) => set({ active })} />
                        {save.failure && <p role="alert" className="font-medium text-error">{save.failure}</p>}
                        <div className="flex flex-wrap gap-2">
                            <button type="submit" className="btn btn-primary" disabled={save.saving}>{save.saving ? 'در حال ذخیره…' : 'ذخیره کارت'}</button>
                            <button type="button" className="btn btn-secondary" onClick={() => setDraft(null)}>انصراف</button>
                        </div>
                    </form>
                </Panel>
            )}

            <Panel title="کارت‌ها">
                {data.cards.length === 0 ? (
                    <p className="text-muted">هنوز کارتی اضافه نشده است.</p>
                ) : (
                    <ul className="m-0 grid list-none gap-3 p-0">
                        {data.cards.map((c) => (
                            <li key={c.id} className={`rounded-xl border border-stroke p-4 ${c.active ? '' : 'opacity-60'}`}>
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="flex items-center gap-2 font-bold">
                                            <CreditCard aria-hidden size={16} className="text-muted" />
                                            {c.title}
                                            {!c.active && <span className="rounded-full bg-surface-2 px-2 py-0.5 text-[12px] font-normal text-muted">غیرفعال</span>}
                                        </p>
                                        <p className="mt-1 flex flex-wrap items-center gap-2">
                                            <span dir="ltr" className="num">{shown[c.id] ? spaced(c.number) : masked(c)}</span>
                                            <button type="button" className="btn btn-ghost h-7 px-2" onClick={() => setShown((s) => ({ ...s, [c.id]: !s[c.id] }))} aria-label={shown[c.id] ? 'پنهان کردن شماره' : 'نمایش شماره'}>
                                                {shown[c.id] ? <EyeOff aria-hidden size={15} /> : <Eye aria-hidden size={15} />}
                                            </button>
                                            {shown[c.id] && <CopyButton value={c.number} label="شماره کارت" />}
                                        </p>
                                        <p className="mt-1 text-[13px] text-muted">
                                            انقضا <span dir="ltr" className="num">{shown[c.id] ? c.expiry : '••/••'}</span> · <span className="num">{c.orders.toLocaleString('fa-IR')}</span> سفارش
                                            {c.note && <> · {c.note}</>}
                                        </p>
                                    </div>
                                    <div className="flex gap-1">
                                        <button type="button" className="btn btn-secondary h-8 px-3" onClick={() => { save.clearSaved(); setNotice(null); setDraft({ id: c.id, title: c.title, number: c.number, expiry: c.expiry, active: c.active, note: c.note ?? '' }); }}>
                                            <Pencil aria-hidden size={14} />
                                            ویرایش
                                        </button>
                                        <button type="button" className="btn btn-ghost h-8 px-2 text-error" onClick={() => remove(c)} aria-label={`حذف کارت ${c.title}`}>
                                            <Trash2 aria-hidden size={15} />
                                        </button>
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </div>
    );
}
