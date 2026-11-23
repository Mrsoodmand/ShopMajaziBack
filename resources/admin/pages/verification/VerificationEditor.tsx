import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { count, parseDecimal } from '../../lib/format';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch } from '../../components/form';
import { RichText } from '../../components/RichText';
import { PriceReceipt, usd } from '../../components/PriceReceipt';
import { ErrorState, Panel } from '../../components/ui';
import { EditorSkeleton, problemsFrom } from '../../components/list';
import { KIND_LABELS } from './VerificationList';

// profit: the plan's own profit ('' = the product's, else the dollar's; Pricing::planRate).
type Plan = { id?: number; name: string; enName: string; price: string; discount: string; profit: string; live: boolean; popular: boolean };
// What the buyer fills in (App\Services\VerificationOrders::fields).
type InputType = 'text' | 'email' | 'phone' | 'select' | 'textarea' | 'date' | 'file';
type Input = {
    name: string;
    title: string;
    titleEn: string;
    type: InputType;
    required: boolean;
    hint: string;
    hintEn: string;
    options: string[];
    accept: ('image' | 'pdf')[];
    maxMb: string;
};
type Product = {
    name: string;
    slug: string;
    live: boolean;
    kind: 'ready' | 'verify';
    currency: 'dollar' | 'toman';
    commission: string;
    profit: string;
    pricing?: { marketDollar: number; dollarProfit: number };
    short: string;
    content: string;
    image: string;
    metaTitle: string;
    metaDesc: string;
    headHtml: string;
    enName: string;
    tagline: string;
    taglineEn: string;
    color: string;
    delivery: string;
    deliveryEn: string;
    popular: boolean;
    // Header menu's «وریفای» group (at most 6); none chosen = one link to /verify.
    inMenu: boolean;
    // Home page shop window's «وریفای» tab (at most 8); none chosen = every live product.
    onHome: boolean;
    sort: string;
    fields: Input[];
    notes: { label: string; value: string }[];
    plans: Plan[];
};

const INPUT_TYPES: [InputType, string][] = [
    ['text', 'متن کوتاه'],
    ['email', 'ایمیل'],
    ['phone', 'شماره تلفن'],
    ['select', 'انتخاب از فهرست'],
    ['textarea', 'متن بلند'],
    ['date', 'تاریخ'],
    ['file', 'فایل (عکس / PDF)'],
];

const newInput = (type: InputType = 'text', name = '', title = ''): Input => ({
    name, title, titleEn: '', type, required: true, hint: '', hintEn: '', options: [], accept: type === 'file' ? ['image'] : [], maxMb: '5',
});

// Starting inputs for a new product, by kind: a ready account needs only where to send it; verifying needs the account and ID.
const starters: Record<Product['kind'], Input[]> = {
    ready: [newInput('email', 'email', 'ایمیل برای تحویل اکانت')],
    verify: [
        newInput('text', 'account', 'آیدی یا لینک حساب'),
        newInput('text', 'full_name', 'نام کامل (مطابق مدرک)'),
        { ...newInput('file', 'id_photo', 'عکس کارت ملی یا پاسپورت'), hint: 'عکس واضح از روی مدرک؛ فقط برای همین سفارش استفاده می‌شود.' },
    ],
};

const blank: Product = {
    name: '', slug: '', live: false, kind: 'verify', currency: 'dollar', commission: '', profit: '',
    short: '', content: '', image: '', metaTitle: '', metaDesc: '', headHtml: '',
    enName: '', tagline: '', taglineEn: '', color: '', delivery: '', deliveryEn: '', popular: false, inMenu: false, onHome: false, sort: '',
    fields: starters.verify, notes: [], plans: [],
};

const labels: Record<string, string> = {
    inMenu: 'نمایش در منو', onHome: 'نمایش در صفحه اول', name: 'نام', slug: 'نامک', kind: 'نوع', currency: 'واحد قیمت', commission: 'پورسانت', profit: 'سود اختصاصی', short: 'توضیح کوتاه', content: 'متن صفحه', image: 'تصویر',
    metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', fields: 'ورودی‌ها', notes: 'نکات', plans: 'پلن‌ها',
    enName: 'نام انگلیسی', tagline: 'شعار فارسی', taglineEn: 'شعار انگلیسی', color: 'رنگ', delivery: 'زمان تحویل', deliveryEn: 'زمان تحویل انگلیسی', sort: 'ترتیب',
    'plans.name': 'نام پلن', 'plans.enName': 'نام انگلیسی پلن', 'plans.price': 'قیمت', 'plans.discount': 'قیمت با تخفیف', 'plans.profit': 'سود پلن',
    'fields.name': 'نام فنی ورودی', 'fields.title': 'عنوان ورودی', 'fields.options': 'گزینه‌ها', 'fields.accept': 'نوع فایل', 'fields.maxMb': 'حداکثر حجم',
    'notes.label': 'عنوان نکته', 'notes.value': 'مقدار نکته',
};

// "عکس پاسپورت" → "" (no Latin), "Passport photo" → "passport_photo": a starting technical name from the title.
function nameFrom(title: string, taken: string[]): string {
    const base = title.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').replace(/^[0-9_]+/, '').slice(0, 40);
    let name = base || 'field';
    for (let i = 2; taken.includes(name); i++) name = `${base || 'field'}_${i}`;
    return name;
}

export default function VerificationEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const existing = useQuery<{ product: Product & { id: number } }>(isNew ? null : `/admin/api/verification/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Product>(blank);

    useEffect(() => {
        if (existing.data) setForm({ ...blank, ...existing.data.product });
    }, [existing.data]);

    if (existing.error) return <ErrorState error={existing.error} onRetry={existing.reload} boxed />;
    if (!isNew && !existing.data) return <EditorSkeleton />;

    const set = (patch: Partial<Product>) => {
        save.clearSaved();
        setForm((f) => ({ ...f, ...patch }));
    };
    const err = (k: string) => save.errors[k];
    const touchedInputs = JSON.stringify(form.fields) !== JSON.stringify(starters[form.kind]);

    async function submit() {
        // Inputs left without a technical name get one from their title, each unique.
        const taken = form.fields.map((f) => f.name.trim()).filter(Boolean);
        const fields = form.fields.map((f) => {
            if (f.name.trim()) return { ...f, name: f.name.trim() };
            const name = nameFrom(f.titleEn || f.title, taken);
            taken.push(name);
            return { ...f, name };
        });
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/verification' : `/admin/api/verification/${id}`, {
            ...form,
            fields,
            commission: form.commission === '' ? null : form.commission,
            profit: form.profit.trim() === '' ? null : form.profit,
        });
        if (res && isNew) navigate(`/verification/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    return (
        <EditorLayout
            backTo="/verification"
            backLabel="وریفای"
            title={isNew ? 'محصول وریفای جدید' : form.name || 'ویرایش محصول'}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? 'ساخت محصول' : 'ذخیره تغییرات'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-2">
                    <Switch label="فعال در سایت" hint="غیرفعال‌ها در سایت نمایش داده نمی‌شوند." checked={form.live} onChange={(live) => set({ live })} />
                    <Switch label="محبوب" hint="بالای صفحه وریفای نشان داده می‌شود." checked={form.popular} onChange={(popular) => set({ popular })} />
                    <Switch label="نمایش در منو" hint="در گروه «وریفای» منوی بالای سایت؛ حداکثر ۶ محصول." checked={form.inMenu} onChange={(inMenu) => set({ inMenu })} />
                    {save.errors.inMenu && <p className="text-[13px] font-medium text-error">{save.errors.inMenu}</p>}
                    <Switch label="نمایش در صفحه اول" hint="در تب «وریفای» ویترین صفحه اول؛ حداکثر ۸. اگر هیچ‌کدام روشن نباشد، همه محصولات فعال نشان داده می‌شوند." checked={form.onHome} onChange={(onHome) => set({ onHome })} />
                    {save.errors.onHome && <p className="text-[13px] font-medium text-error">{save.errors.onHome}</p>}
                    {form.slug && (
                        <p dir="ltr" className="latin truncate pt-1 text-end text-[12px] text-muted">
                            /verify/{form.slug}
                        </p>
                    )}
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={err('name')}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} placeholder="وریفای حساب پی‌پال" />}
                    </Field>
                    <Field label="نامک (آدرس)" error={err('slug')} hint="حروف کوچک انگلیسی، عدد و خط تیره.">
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.slug} onChange={(e) => set({ slug: e.target.value.toLowerCase() })} placeholder="paypal-verify" />}
                    </Field>
                    <Field label="نوع" error={err('kind')} hint={form.kind === 'ready' ? 'اکانت آماده‌ای که از قبل وریفای شده تحویل می‌دهید.' : 'حساب خود مشتری را وریفای می‌کنید.'}>
                        {(p) => (
                            <select
                                {...p}
                                className="field"
                                value={form.kind}
                                onChange={(e) => {
                                    const kind = e.target.value as Product['kind'];
                                    // A new product whose inputs weren't touched gets the other kind's starting inputs.
                                    set(isNew && !touchedInputs ? { kind, fields: starters[kind] } : { kind });
                                }}
                            >
                                {(Object.keys(KIND_LABELS) as Product['kind'][]).map((k) => <option key={k} value={k}>{KIND_LABELS[k]}</option>)}
                            </select>
                        )}
                    </Field>
                    <Field label="واحد قیمت پلن‌ها" error={err('currency')}>
                        {(p) => (
                            <select {...p} className="field" value={form.currency} onChange={(e) => set({ currency: e.target.value as Product['currency'] })}>
                                <option value="dollar">دلار (با نرخ روز به تومان)</option>
                                <option value="toman">تومان</option>
                            </select>
                        )}
                    </Field>
                    <Field label="سود اختصاصی (٪)" optional error={err('profit')} hint={profitHint(form)}>
                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" placeholder={form.pricing ? String(form.pricing.dollarProfit) : ''} value={form.profit} onChange={(e) => set({ profit: e.target.value })} />}
                    </Field>
                    <Field label="پورسانت معرف (٪)" optional error={err('commission')}>
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.commission} onChange={(e) => set({ commission: e.target.value })} />}
                    </Field>
                    <div className="sm:col-span-2">
                        <ImageField label="تصویر" value={form.image} onChange={(image) => set({ image })} error={err('image')} uploadUrl="/admin/api/uploads/verification-image" square noSvg />
                    </div>
                </div>
            </Panel>

            <Panel title="پلن‌ها و قیمت">
                {err('plans') && <p className="mb-3 font-bold text-error">{err('plans')}</p>}
                <RowsEditor
                    rows={form.plans}
                    onChange={(plans) => set({ plans })}
                    make={() => ({ name: '', enName: '', price: '', discount: '', profit: '', live: true, popular: false })}
                    addLabel="افزودن پلن"
                    empty="دست‌کم یک پلن با قیمت لازم است (مثلاً «وریفای کامل» یا «سطح ۱»)."
                    removeLabel={(r, i) => `حذف پلن ${r.name || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] xl:items-start">
                            <div className="grid gap-3">
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <Field label="نام پلن" error={err(`plans.${i}.name`)}>
                                        {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="وریفای کامل" />}
                                    </Field>
                                    <Field label="نام انگلیسی" error={err(`plans.${i}.enName`)}>
                                        {(p) => <input {...p} dir="ltr" className="field latin" value={row.enName ?? ''} onChange={(e) => update({ enName: e.target.value })} placeholder="Full verification" />}
                                    </Field>
                                </div>
                                <div className={`grid grid-cols-2 gap-3 sm:items-end ${form.currency === 'dollar' ? 'sm:grid-cols-3' : ''}`}>
                                    <Field label={form.currency === 'dollar' ? 'قیمت $' : 'قیمت (تومان)'} error={err(`plans.${i}.price`)}>
                                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={row.price} onChange={(e) => update({ price: e.target.value })} />}
                                    </Field>
                                    <Field label={form.currency === 'dollar' ? 'با تخفیف $' : 'با تخفیف'} error={err(`plans.${i}.discount`)}>
                                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={row.discount} onChange={(e) => update({ discount: e.target.value })} placeholder="—" />}
                                    </Field>
                                    {form.currency === 'dollar' && (
                                        <Field label="سود ٪" error={err(`plans.${i}.profit`)}>
                                            {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" placeholder={String(inheritedProfit(form))} value={row.profit ?? ''} onChange={(e) => update({ profit: e.target.value })} />}
                                        </Field>
                                    )}
                                </div>
                                <div className="flex flex-wrap items-center gap-x-6 gap-y-2">
                                    <Switch label="فعال" checked={row.live} onChange={(live) => update({ live })} />
                                    <Switch label="محبوب‌ترین" checked={!!row.popular} onChange={(popular) => set({ plans: form.plans.map((x, j) => ({ ...x, popular: j === i ? popular : popular ? false : x.popular })) })} />
                                </div>
                            </div>
                            <PlanReceipt form={form} row={row} />
                        </div>
                    )}
                />
            </Panel>

            <Panel title="اطلاعاتی که از خریدار می‌خواهید">
                <p className="mb-3 text-muted">
                    هر ورودی یک خانه در فرم خرید است. فایل‌ها (عکس مدرک، سلفی…) جای امن و خصوصی نگه داشته می‌شوند؛ فقط ادمین‌ها از صفحه سفارش بازشان می‌کنند و
                    ۳۰ روز بعد از بسته شدن سفارش پاک می‌شوند.
                </p>
                <RowsEditor
                    rows={form.fields}
                    onChange={(fields) => set({ fields })}
                    make={() => newInput()}
                    addLabel="افزودن ورودی"
                    empty="بدون ورودی، خریدار فقط پلن را انتخاب و پرداخت می‌کند."
                    removeLabel={(r, i) => `حذف ورودی ${r.title || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3">
                            <div className="grid gap-3 sm:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1fr)]">
                                <Field label="عنوان برای خریدار" error={err(`fields.${i}.title`)}>
                                    {(p) => <input {...p} className="field" value={row.title} onChange={(e) => update({ title: e.target.value })} placeholder="عکس سلفی با مدرک" />}
                                </Field>
                                <Field label="نوع" error={err(`fields.${i}.type`)}>
                                    {(p) => (
                                        <select
                                            {...p}
                                            className="field"
                                            value={row.type}
                                            onChange={(e) => {
                                                const type = e.target.value as InputType;
                                                update({ type, accept: type === 'file' && row.accept.length === 0 ? ['image'] : row.accept });
                                            }}
                                        >
                                            {INPUT_TYPES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                                        </select>
                                    )}
                                </Field>
                                <Field label="نام فنی" optional error={err(`fields.${i}.name`)} hint="انگلیسی؛ خالی = از عنوان ساخته می‌شود.">
                                    {(p) => <input {...p} dir="ltr" className="field latin" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="selfie" />}
                                </Field>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <Field label="عنوان انگلیسی" optional error={err(`fields.${i}.titleEn`)}>
                                    {(p) => <input {...p} dir="ltr" className="field latin" value={row.titleEn} onChange={(e) => update({ titleEn: e.target.value })} placeholder="Selfie holding your ID" />}
                                </Field>
                                <Field label="راهنما زیر خانه" optional error={err(`fields.${i}.hint`)}>
                                    {(p) => <input {...p} className="field" value={row.hint} onChange={(e) => update({ hint: e.target.value })} placeholder="صورت و مدرک هر دو واضح باشند." />}
                                </Field>
                            </div>
                            {row.type === 'select' && (
                                <Field label="گزینه‌ها" hint="با کاما جدا کنید؛ دست‌کم دو گزینه." error={err(`fields.${i}.options`)}>
                                    {(p) => (
                                        <input
                                            {...p}
                                            className="field"
                                            defaultValue={row.options.join('، ')}
                                            onBlur={(e) => update({ options: e.target.value.split(/[,،]/).map((o) => o.trim()).filter(Boolean) })}
                                            placeholder="ایران، ترکیه، امارات"
                                        />
                                    )}
                                </Field>
                            )}
                            {row.type === 'file' && (
                                <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_140px] sm:items-end">
                                    <div>
                                        <p className="mb-1.5 font-medium">نوع فایل</p>
                                        <div className="flex flex-wrap gap-x-6 gap-y-2">
                                            <Switch label="عکس (JPG، PNG، WEBP، HEIC)" checked={row.accept.includes('image')} onChange={(on) => update({ accept: toggle(row.accept, 'image', on) })} />
                                            <Switch label="PDF" checked={row.accept.includes('pdf')} onChange={(on) => update({ accept: toggle(row.accept, 'pdf', on) })} />
                                        </div>
                                        {err(`fields.${i}.accept`) && <p className="mt-1 text-[13px] font-medium text-error">{err(`fields.${i}.accept`)}</p>}
                                    </div>
                                    <Field label="حداکثر حجم (MB)" error={err(`fields.${i}.maxMb`)}>
                                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.maxMb} onChange={(e) => update({ maxMb: e.target.value })} />}
                                    </Field>
                                </div>
                            )}
                            <Switch label="اجباری" hint="خاموش: خریدار می‌تواند خالی بگذارد." checked={row.required} onChange={(required) => update({ required })} />
                        </div>
                    )}
                />
            </Panel>

            <Panel title="نمایش در سایت">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام انگلیسی" optional error={err('enName')}>
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.enName} onChange={(e) => set({ enName: e.target.value })} placeholder="PayPal verification" />}
                    </Field>
                    <Field label="ترتیب" optional error={err('sort')} hint="کوچک‌تر، جلوتر.">
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.sort} onChange={(e) => set({ sort: e.target.value })} />}
                    </Field>
                    <Field label="شعار فارسی" optional error={err('tagline')} hint="یک خط زیر نام.">
                        {(p) => <input {...p} className="field" value={form.tagline} onChange={(e) => set({ tagline: e.target.value })} />}
                    </Field>
                    <Field label="شعار انگلیسی" optional error={err('taglineEn')}>
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.taglineEn} onChange={(e) => set({ taglineEn: e.target.value })} />}
                    </Field>
                    <Field label="زمان تحویل" optional error={err('delivery')}>
                        {(p) => <input {...p} className="field" value={form.delivery} onChange={(e) => set({ delivery: e.target.value })} placeholder="۱ تا ۳ روز کاری" />}
                    </Field>
                    <Field label="زمان تحویل انگلیسی" optional error={err('deliveryEn')}>
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.deliveryEn} onChange={(e) => set({ deliveryEn: e.target.value })} placeholder="1–3 working days" />}
                    </Field>
                    <Field label="رنگ برند" optional error={err('color')}>
                        {(p) => (
                            <div className="flex gap-2">
                                <input type="color" aria-label="انتخاب رنگ" value={form.color || '#475569'} onChange={(e) => set({ color: e.target.value })} className="h-[38px] w-12 shrink-0 cursor-pointer rounded-[10px] border border-field bg-surface p-1" />
                                <input {...p} dir="ltr" className="field latin" value={form.color} onChange={(e) => set({ color: e.target.value })} placeholder="#003087" />
                            </div>
                        )}
                    </Field>
                </div>
            </Panel>

            <Panel title="نکات کنار سفارش">
                <RowsEditor
                    rows={form.notes}
                    onChange={(notes) => set({ notes })}
                    make={() => ({ label: '', value: '' })}
                    addLabel="افزودن نکته"
                    empty="مثل «سطح وریفای: کامل» یا «کشور: ترکیه»."
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
                    <Field label="کد HTML اضافه در head" optional hint="مثلاً اسکیما.">
                        {(p) => <textarea {...p} rows={4} dir="ltr" className="field font-mono text-[13px]" value={form.headHtml} onChange={(e) => set({ headHtml: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}

function toggle<T>(list: T[], value: T, on: boolean): T[] {
    return on ? Array.from(new Set([...list, value])) : list.filter((x) => x !== value);
}

function profitHint(form: Product): string {
    if (form.currency === 'toman') return 'قیمت پلن‌ها به تومان است؛ سود روی آن‌ها اعمال نمی‌شود.';
    if (!form.pricing) return 'خالی بماند تا سود دلار (ارزها و سود) اعمال شود.';
    const own = parseDecimal(form.profit);
    const profit = form.profit.trim() === '' || own === null ? form.pricing.dollarProfit : own;
    return `خالی = سود دلار. هر دلار: ${count(Math.round(form.pricing.marketDollar * (1 + profit / 100)))} تومان (${profit}٪).`;
}

function inheritedProfit(form: Product): number {
    const own = parseDecimal(form.profit);
    return form.profit.trim() === '' || own === null ? (form.pricing?.dollarProfit ?? 0) : own;
}

// The plan's site price, worked out like the storefront does (it reads the dollar at request time, so it's approximate).
function PlanReceipt({ form, row }: { form: Product; row: Plan }) {
    const sale = parseDecimal(row.discount) || 0;
    const base = parseDecimal(row.price) || 0;
    const amount = sale > 0 ? sale : base;
    if (form.currency === 'toman') {
        return <PriceReceipt lines={[sale > 0 && base > 0 && { label: 'قیمت اصلی', value: `${count(base)} ت`, strike: true }]} total={amount > 0 ? Math.round(amount) : null} />;
    }
    if (!form.pricing) return <PriceReceipt lines={[]} total={null} empty="نرخ دلار در دسترس نیست" />;
    const own = parseDecimal(row.profit ?? '');
    const inherited = (row.profit ?? '').trim() === '' || own === null;
    const profit = inherited ? inheritedProfit(form) : own;
    return (
        <PriceReceipt
            approx
            lines={[
                sale > 0 && base > 0 && { label: 'قیمت اصلی', value: usd(base), strike: true },
                { label: sale > 0 ? 'قیمت با تخفیف' : 'قیمت دلاری', value: amount > 0 ? usd(amount) : '—' },
                { label: 'دلار بازار', value: `${count(Math.round(form.pricing.marketDollar))} ت` },
                { label: inherited ? 'سود (محصول)' : 'سود (این پلن)', value: `${profit}٪` },
            ]}
            total={amount > 0 ? Math.round(amount * Math.round(form.pricing.marketDollar * (1 + profit / 100))) : null}
        />
    );
}
