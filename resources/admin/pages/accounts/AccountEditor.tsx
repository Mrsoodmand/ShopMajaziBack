import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch } from '../../components/form';
import { RichText } from '../../components/RichText';
import { ErrorState, Panel } from '../../components/ui';
import { EditorSkeleton, problemsFrom } from '../../components/list';

type Plan = { id?: number; name: string; price: string; discount: string; count: string; live: boolean };
type OrderField = { name: string; title: string; type: 'text' | 'select'; options: string[]; width: string };
type Account = {
    name: string;
    slug: string;
    live: boolean;
    withPlans: boolean;
    currency: 'dollar' | 'toman';
    commission: string;
    short: string;
    content: string;
    image: string;
    metaTitle: string;
    metaDesc: string;
    headHtml: string;
    fields: OrderField[];
    notes: { label: string; value: string }[];
    plans: Plan[];
};

const blank: Account = {
    name: '',
    slug: '',
    live: false,
    withPlans: true,
    currency: 'dollar',
    commission: '',
    short: '',
    content: '',
    image: '',
    metaTitle: '',
    metaDesc: '',
    headHtml: '',
    fields: [{ name: 'email', title: 'ایمیل حساب', type: 'text', options: [], width: '6' }],
    notes: [],
    plans: [],
};

const labels: Record<string, string> = {
    name: 'نام', slug: 'نامک', currency: 'واحد قیمت', commission: 'پورسانت', short: 'توضیح کوتاه', content: 'متن صفحه', image: 'تصویر',
    metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', fields: 'فیلدهای سفارش', notes: 'نکات', plans: 'پلن‌ها',
    'plans.name': 'نام', 'plans.price': 'قیمت', 'plans.discount': 'قیمت با تخفیف', 'plans.count': 'تعداد',
    'fields.name': 'نام فنی', 'fields.title': 'عنوان', 'fields.options': 'گزینه‌ها', 'notes.label': 'عنوان', 'notes.value': 'مقدار',
};

export default function AccountEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const existing = useQuery<{ account: Account & { id: number } }>(isNew ? null : `/admin/api/accounts/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Account>(blank);

    useEffect(() => {
        if (existing.data) setForm({ ...blank, ...existing.data.account });
    }, [existing.data]);

    if (existing.error) return <ErrorState error={existing.error} onRetry={existing.reload} boxed />;
    if (!isNew && !existing.data) return <EditorSkeleton />;

    const set = (patch: Partial<Account>) => {
        save.clearSaved();
        setForm((f) => ({ ...f, ...patch }));
    };
    const err = (k: string) => save.errors[k];
    const unit = form.currency === 'dollar' ? 'دلار' : 'تومان';

    async function submit() {
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/accounts' : `/admin/api/accounts/${id}`, {
            ...form,
            commission: form.commission === '' ? null : form.commission,
            plans: form.withPlans ? form.plans : [],
        });
        if (res && isNew) navigate(`/accounts/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    return (
        <EditorLayout
            backTo="/accounts"
            backLabel="اکانت‌های خارجی"
            title={isNew ? 'اکانت جدید' : form.name || 'ویرایش اکانت'}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? 'ساخت اکانت' : 'ذخیره تغییرات'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-2">
                    <Switch label="فعال در سایت" hint="غیرفعال‌ها در سایت نمایش داده نمی‌شوند." checked={form.live} onChange={(live) => set({ live })} />
                    <Switch label="فروش با پلن" hint="خاموش: یک محصول بدون پلن‌های جدا." checked={form.withPlans} onChange={(withPlans) => set({ withPlans })} />
                    {form.slug && (
                        <p dir="ltr" className="latin truncate pt-1 text-end text-[12px] text-muted">
                            /accounts/{form.slug}
                        </p>
                    )}
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={err('name')}>
                        {(p) => (
                            <input
                                {...p}
                                className="field"
                                value={form.name}
                                onChange={(e) => set({ name: e.target.value })}
                                placeholder="خرید اشتراک تریدینگ ویو"
                            />
                        )}
                    </Field>
                    <Field label="نامک (آدرس)" error={err('slug')} hint="حروف کوچک انگلیسی، عدد و خط تیره.">
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.slug}
                                onChange={(e) => {
                                    set({ slug: e.target.value.toLowerCase() });
                                }}
                                placeholder="trading-view"
                            />
                        )}
                    </Field>
                    <Field label="واحد قیمت پلن‌ها" error={err('currency')}>
                        {(p) => (
                            <select {...p} className="field" value={form.currency} onChange={(e) => set({ currency: e.target.value as Account['currency'] })}>
                                <option value="dollar">دلار (با نرخ روز به تومان)</option>
                                <option value="toman">تومان</option>
                            </select>
                        )}
                    </Field>
                    <Field label="پورسانت معرف (٪)" optional error={err('commission')}>
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.commission} onChange={(e) => set({ commission: e.target.value })} />}
                    </Field>
                    <div className="sm:col-span-2">
                        <ImageField label="تصویر" value={form.image} onChange={(image) => set({ image })} error={err('image')} uploadUrl="/admin/api/uploads/account-image" square noSvg />
                    </div>
                </div>
            </Panel>

            {form.withPlans && (
                <Panel title="پلن‌ها">
                    {err('plans') && <p className="mb-3 font-bold text-error">{err('plans')}</p>}
                    <RowsEditor
                        rows={form.plans}
                        onChange={(plans) => set({ plans })}
                        make={() => ({ name: '', price: '', discount: '', count: '', live: true })}
                        addLabel="افزودن پلن"
                        empty="هنوز پلنی ندارد."
                        removeLabel={(r, i) => `حذف پلن ${r.name || i + 1}`}
                        render={(row, update, i) => (
                            <div className="grid gap-3 sm:grid-cols-[1fr_120px_120px_90px_auto] sm:items-end">
                                <Field label="نام پلن" error={err(`plans.${i}.name`)}>
                                    {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="یک ماهه" />}
                                </Field>
                                <Field label={`قیمت (${unit})`} error={err(`plans.${i}.price`)}>
                                    {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={row.price} onChange={(e) => update({ price: e.target.value })} />}
                                </Field>
                                <Field label="با تخفیف" optional error={err(`plans.${i}.discount`)}>
                                    {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={row.discount} onChange={(e) => update({ discount: e.target.value })} />}
                                </Field>
                                <Field label="تعداد" optional error={err(`plans.${i}.count`)}>
                                    {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.count} onChange={(e) => update({ count: e.target.value })} />}
                                </Field>
                                <Switch label="فعال" checked={row.live} onChange={(live) => update({ live })} />
                            </div>
                        )}
                    />
                </Panel>
            )}

            <Panel title="فیلدهای سفارش">
                <p className="mb-3 text-muted">آنچه خریدار هنگام سفارش وارد می‌کند (مثلاً ایمیل حسابی که اشتراک روی آن فعال می‌شود).</p>
                <RowsEditor
                    rows={form.fields}
                    onChange={(fields) => set({ fields })}
                    make={() => ({ name: '', title: '', type: 'text' as const, options: [], width: '6' })}
                    addLabel="افزودن فیلد"
                    empty="بدون فیلد، خریدار چیزی وارد نمی‌کند."
                    removeLabel={(r, i) => `حذف فیلد ${r.title || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-3">
                            <Field label="عنوان برای خریدار" error={err(`fields.${i}.title`)}>
                                {(p) => <input {...p} className="field" value={row.title} onChange={(e) => update({ title: e.target.value })} placeholder="ایمیل حساب" />}
                            </Field>
                            <Field label="نام فنی" error={err(`fields.${i}.name`)}>
                                {(p) => <input {...p} dir="ltr" className="field latin" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="email" />}
                            </Field>
                            <Field label="نوع">
                                {(p) => (
                                    <select {...p} className="field" value={row.type} onChange={(e) => update({ type: e.target.value as OrderField['type'] })}>
                                        <option value="text">متن</option>
                                        <option value="select">انتخاب از فهرست</option>
                                    </select>
                                )}
                            </Field>
                            {row.type === 'select' && (
                                <Field label="گزینه‌ها" className="sm:col-span-3" hint="با کاما جدا کنید." error={err(`fields.${i}.options`)}>
                                    {(p) => (
                                        <input
                                            {...p}
                                            className="field"
                                            value={row.options.join('، ')}
                                            onChange={(e) => update({ options: e.target.value.split(/[,،]/).map((o) => o.trim()).filter(Boolean) })}
                                        />
                                    )}
                                </Field>
                            )}
                        </div>
                    )}
                />
            </Panel>

            <Panel title="نکات کنار سفارش">
                <RowsEditor
                    rows={form.notes}
                    onChange={(notes) => set({ notes })}
                    make={() => ({ label: '', value: '' })}
                    addLabel="افزودن نکته"
                    empty="مثل «زمان تحویل: ۲ تا ۶ ساعت»."
                    removeLabel={(r, i) => `حذف نکته ${r.label || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-2">
                            <Field label="عنوان" error={err(`notes.${i}.label`)}>
                                {(p) => <input {...p} className="field" value={row.label} onChange={(e) => update({ label: e.target.value })} />}
                            </Field>
                            <Field label="مقدار" error={err(`notes.${i}.value`)}>
                                {(p) => <input {...p} className="field" value={row.value} onChange={(e) => update({ value: e.target.value })} />}
                            </Field>
                        </div>
                    )}
                />
            </Panel>

            <Panel title="متن صفحه">
                <div className="grid gap-5">
                    <RichText label="توضیح کوتاه (بالای صفحه)" value={form.short} onChange={(short) => set({ short })} error={err('short')} height={200} />
                    <RichText label="متن کامل" value={form.content} onChange={(content) => set({ content })} error={err('content')} height={420} />
                </div>
            </Panel>

            <Panel title="سئو">
                <div className="grid gap-4">
                    <Field label="عنوان سئو" error={err('metaTitle')}>
                        {(p) => <input {...p} className="field" value={form.metaTitle} onChange={(e) => set({ metaTitle: e.target.value })} />}
                    </Field>
                    <Field label="توضیح سئو" error={err('metaDesc')}>
                        {(p) => <textarea {...p} rows={2} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                    <Field label="کد HTML اضافه در head" optional hint="مثلاً اسکیما؛ همان «توضیحات سئو» پنل قدیمی.">
                        {(p) => <textarea {...p} rows={4} dir="ltr" className="field font-mono text-[13px]" value={form.headHtml} onChange={(e) => set({ headHtml: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
