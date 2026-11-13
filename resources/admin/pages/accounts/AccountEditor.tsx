import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch } from '../../components/form';
import { RichText } from '../../components/RichText';
import { ErrorState, Panel } from '../../components/ui';
import { EditorSkeleton, problemsFrom } from '../../components/list';

type Plan = { id?: number; name: string; enName: string; months: string; price: string; discount: string; count: string; live: boolean };
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
    // Storefront (/accounts). Empty = guessed from the fields above.
    enName: string;
    tagline: string;
    taglineEn: string;
    category: string;
    color: string;
    activation: '' | 'own' | 'login' | 'ready';
    delivery: string;
    deliveryEn: string;
    popular: boolean;
    sort: string;
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
    enName: '',
    tagline: '',
    taglineEn: '',
    category: '',
    color: '',
    activation: '',
    delivery: '',
    deliveryEn: '',
    popular: false,
    sort: '',
    fields: [{ name: 'email', title: 'ایمیل حساب', type: 'text', options: [], width: '6' }],
    notes: [],
    plans: [],
};

const labels: Record<string, string> = {
    name: 'نام', slug: 'نامک', currency: 'واحد قیمت', commission: 'پورسانت', short: 'توضیح کوتاه', content: 'متن صفحه', image: 'تصویر',
    metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', fields: 'فیلدهای سفارش', notes: 'نکات', plans: 'پلن‌ها',
    enName: 'نام انگلیسی', tagline: 'شعار فارسی', taglineEn: 'شعار انگلیسی', category: 'دسته', color: 'رنگ', activation: 'نحوه تحویل', delivery: 'زمان تحویل', deliveryEn: 'زمان تحویل انگلیسی', sort: 'ترتیب',
    'plans.name': 'نام', 'plans.enName': 'نام انگلیسی', 'plans.months': 'مدت', 'plans.price': 'قیمت', 'plans.discount': 'قیمت با تخفیف', 'plans.count': 'تعداد',
    'fields.name': 'نام فنی', 'fields.title': 'عنوان', 'fields.options': 'گزینه‌ها', 'notes.label': 'عنوان', 'notes.value': 'مقدار',
};

const CATEGORIES: [string, string][] = [
    ['', 'سایر'], ['ai', 'هوش مصنوعی'], ['video', 'ویدیو'], ['music', 'موسیقی'], ['design', 'طراحی'], ['work', 'کار و آموزش'], ['trading', 'ترید'], ['social', 'شبکه اجتماعی'],
];
const ACTIVATIONS: [string, string][] = [
    ['', 'خودکار (از فیلدها)'],
    ['own', 'روی حساب خریدار، بدون رمز'],
    ['login', 'روی حساب خریدار، با ایمیل و رمز'],
    ['ready', 'اکانت آماده تحویل می‌دهیم'],
];

// The months the storefront reads from a plan's name when «ماه» is empty (same rule as AccountOrders::months).
const MONTH_WORDS: Record<string, number> = { دوازده: 12, یازده: 11, هیجده: 18, هجده: 18, یک: 1, دو: 2, سه: 3, چهار: 4, پنج: 5, شش: 6, شیش: 6, هفت: 7, هشت: 8, نه: 9, ده: 10 };
function guessMonths(name: string): string {
    const n = name.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)));
    const w = Object.keys(MONTH_WORDS).join('|');
    let m = n.match(/(\d{1,2})\s*-?\s*(ماه|month)/i);
    if (m) return m[1];
    m = n.match(new RegExp(`(?:^|[^\\p{L}])(${w})\\s*ماه`, 'u'));
    if (m) return String(MONTH_WORDS[m[1]]);
    m = n.match(/(\d{1,2})\s*-?\s*(سال|year)/i);
    if (m) return String(+m[1] * 12);
    m = n.match(new RegExp(`(?:^|[^\\p{L}])(${w})\\s*سال`, 'u'));
    if (m) return String(MONTH_WORDS[m[1]] * 12);
    return '';
}

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
                    <Switch label="محبوب" hint="در سایت جدید بالای صفحه اشتراک‌ها نشان داده می‌شود." checked={form.popular} onChange={(popular) => set({ popular })} />
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
                        make={() => ({ name: '', enName: '', months: '', price: '', discount: '', count: '', live: true })}
                        addLabel="افزودن پلن"
                        empty="هنوز پلنی ندارد."
                        removeLabel={(r, i) => `حذف پلن ${r.name || i + 1}`}
                        render={(row, update, i) => (
                            <div className="grid gap-3 sm:grid-cols-[1fr_1fr_80px_110px_110px_80px_auto] sm:items-end">
                                <Field label="نام پلن" error={err(`plans.${i}.name`)}>
                                    {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="یک ماهه" />}
                                </Field>
                                <Field label="نام انگلیسی" optional error={err(`plans.${i}.enName`)}>
                                    {(p) => <input {...p} dir="ltr" className="field latin" value={row.enName ?? ''} onChange={(e) => update({ enName: e.target.value })} placeholder="Pro · 1 month" />}
                                </Field>
                                <Field label="ماه" optional error={err(`plans.${i}.months`)}>
                                    {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.months ?? ''} onChange={(e) => update({ months: e.target.value })} placeholder={guessMonths(row.name)} />}
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

            <Panel title="نمایش در سایت">
                <p className="mb-4 text-muted">آنچه صفحه اشتراک‌ها در سایت جدید نشان می‌دهد. هر چه خالی بماند، از نام، فیلدها و نکات حدس زده می‌شود.</p>
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام انگلیسی" optional error={err('enName')} hint="نام در سایت انگلیسی.">
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.enName} onChange={(e) => set({ enName: e.target.value })} placeholder="TradingView" />}
                    </Field>
                    <Field label="دسته" optional error={err('category')}>
                        {(p) => (
                            <select {...p} className="field" value={form.category} onChange={(e) => set({ category: e.target.value })}>
                                {CATEGORIES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                            </select>
                        )}
                    </Field>
                    <Field label="شعار فارسی" optional error={err('tagline')} hint="یک خط زیر نام.">
                        {(p) => <input {...p} className="field" value={form.tagline} onChange={(e) => set({ tagline: e.target.value })} placeholder="نمودار و ابزار حرفه‌ای تحلیل بازار" />}
                    </Field>
                    <Field label="شعار انگلیسی" optional error={err('taglineEn')}>
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.taglineEn} onChange={(e) => set({ taglineEn: e.target.value })} placeholder="Pro charts and market tools" />}
                    </Field>
                    <Field label="نحوه تحویل" optional error={err('activation')}>
                        {(p) => (
                            <select {...p} className="field" value={form.activation} onChange={(e) => set({ activation: e.target.value as Account['activation'] })}>
                                {ACTIVATIONS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                            </select>
                        )}
                    </Field>
                    <Field label="رنگ برند" optional error={err('color')} hint="کاشی و پس‌زمینه کارت با این رنگ.">
                        {(p) => (
                            <div className="flex gap-2">
                                <input type="color" aria-label="انتخاب رنگ" value={form.color || '#475569'} onChange={(e) => set({ color: e.target.value })} className="h-[38px] w-12 shrink-0 cursor-pointer rounded-[10px] border border-field bg-surface p-1" />
                                <input {...p} dir="ltr" className="field latin" value={form.color} onChange={(e) => set({ color: e.target.value })} placeholder="#10a37f" />
                            </div>
                        )}
                    </Field>
                    <Field label="زمان تحویل" optional error={err('delivery')} hint="خالی: از نکته «زمان تحویل / فعال‌سازی».">
                        {(p) => <input {...p} className="field" value={form.delivery} onChange={(e) => set({ delivery: e.target.value })} placeholder="کمتر از ۴ ساعت کاری" />}
                    </Field>
                    <Field label="زمان تحویل انگلیسی" optional error={err('deliveryEn')}>
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.deliveryEn} onChange={(e) => set({ deliveryEn: e.target.value })} placeholder="Within 4 working hours" />}
                    </Field>
                    <Field label="ترتیب" optional error={err('sort')} hint="کوچک‌تر، جلوتر.">
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.sort} onChange={(e) => set({ sort: e.target.value })} />}
                    </Field>
                </div>
            </Panel>

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
