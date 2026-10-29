import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams, useSearchParams } from 'react-router';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count, parseAmount, parseDecimal, roundToman, toman } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch, slugify } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { UPLOAD_URL, type Platform, type ServiceForm } from './types';

const blank: ServiceForm = {
    categoryId: '',
    name: '',
    enName: '',
    slug: '',
    live: false,
    short: '',
    content: '',
    image: '',
    metaTitle: '',
    metaDesc: '',
    pricing: 'count',
    dollarPer1000: '',
    dollarPer500: '',
    minCount: 500,
    maxCount: 10000,
    commission: '',
    plans: [],
    fields: [{ name: 'link', title: 'لینک کانال یا پیج', type: 'text', options: [] }],
    notes: [],
    faq: [],
};

const labels: Record<string, string> = {
    categoryId: 'پلتفرم', name: 'نام', enName: 'نام انگلیسی', slug: 'نامک', pricing: 'قیمت‌گذاری', dollarPer1000: 'قیمت دلاری',
    dollarPer500: 'قیمت هر ۵۰۰ عدد', minCount: 'حداقل سفارش', maxCount: 'حداکثر سفارش', commission: 'پورسانت', plans: 'پلن‌ها',
    fields: 'فیلدهای سفارش', notes: 'نکات', faq: 'سؤالات متداول', image: 'تصویر',
};

/** "plans.1.count" → "پلن‌ها، ردیف ۲" */
function problemLabel(key: string) {
    const [root, index] = key.split('.');
    const base = labels[root] ?? root;
    return index !== undefined && /^\d+$/.test(index) ? `${base}، ردیف ${Number(index) + 1}` : base;
}

const num = (v: number | string | null | undefined) => (v === '' || v == null ? null : typeof v === 'number' ? v : parseAmount(String(v)));

export default function ServiceEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const [params] = useSearchParams();
    const navigate = useNavigate();
    const { csrfToken } = useBoot();
    const cats = useQuery<{ categories: Platform[] }>('/admin/api/service-categories');
    const existing = useQuery<{ service: ServiceForm & { id: number }; rate: number | null }>(isNew ? null : `/admin/api/services/${id}`);
    const rateQuery = useQuery<{ rate: number | null }>(isNew ? '/admin/api/services?category=0' : null);
    const rate = existing.data?.rate ?? rateQuery.data?.rate ?? null;

    const [form, setForm] = useState<ServiceForm>(() => ({ ...blank, categoryId: Number(params.get('platform')) || '' }));
    const [slugTouched, setSlugTouched] = useState(!isNew);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);
    const [failure, setFailure] = useState<string | null>(null);

    useEffect(() => {
        if (existing.data) setForm({ ...blank, ...existing.data.service, commission: existing.data.service.commission ?? '' });
    }, [existing.data]);

    const set = (patch: Partial<ServiceForm>) => {
        setSaved(null);
        setForm((f) => ({ ...f, ...patch }));
    };
    const platform = cats.data?.categories.find((c) => c.id === form.categoryId);

    // What the buyer pays, in toman, rounded like the storefront (the last two digits become 00 or 50).
    // Any amount: per 500 (the step); packages: per 1,000.
    const price = useMemo(() => {
        const d = parseDecimal(form.pricing === 'count' ? form.dollarPer500 : form.dollarPer1000);
        return rate && d ? roundToman(d * rate) : null;
    }, [form.pricing, form.dollarPer500, form.dollarPer1000, rate]);
    const minCount = num(form.minCount);
    const maxCount = num(form.maxCount);

    async function save() {
        setSaving(true);
        setSaved(null);
        setFailure(null);
        const payload = {
            ...form,
            categoryId: form.categoryId || null,
            dollarPer500: form.pricing === 'count' ? (parseDecimal(form.dollarPer500) ?? form.dollarPer500) : null,
            minCount: num(form.minCount),
            maxCount: num(form.maxCount),
            dollarPer1000: form.dollarPer1000 === '' ? null : (parseDecimal(form.dollarPer1000) ?? form.dollarPer1000),
            commission: num(form.commission),
            plans: form.plans.map((pl) => ({ ...pl, count: num(pl.count) })),
            fields: form.fields.map((f) => ({ ...f, options: f.type === 'select' ? f.options : [] })),
        };
        try {
            const res = await postJson<{ success: string; id: number }>(isNew ? '/admin/api/services' : `/admin/api/services/${id}`, csrfToken, payload);
            setErrors({});
            if (isNew) navigate(`/services/${res.id}`, { replace: true });
            else {
                setSaved(res.success);
                existing.reload();
            }
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
        } finally {
            setSaving(false);
        }
    }

    if (existing.error) return <ErrorState error={existing.error} onRetry={existing.reload} boxed />;
    if (!isNew && !existing.data) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }

    const err = (k: string) => errors[k];
    const problems = [
        ...Object.entries(errors).map(([k, message]) => ({ label: problemLabel(k), message })),
        ...(failure ? [{ label: 'ذخیره', message: failure }] : []),
    ];
    const backTo = form.categoryId ? `/services?platform=${form.categoryId}` : '/services';

    return (
        <EditorLayout
            backTo={backTo}
            backLabel="خدمات مجازی"
            title={isNew ? 'سرویس جدید' : form.name || 'ویرایش سرویس'}
            subtitle={platform ? <>در پلتفرم {platform.name}</> : undefined}
            onSubmit={save}
            saving={saving}
            saveLabel={isNew ? 'ساخت سرویس' : 'ذخیره تغییرات'}
            saved={saved}
            problems={problems}
            side={
                <div className="space-y-3">
                    <Switch label="فعال در سایت" hint="غیرفعال‌ها در سایت دیده و فروخته نمی‌شوند." checked={form.live} onChange={(v) => set({ live: v })} />
                    <dl className="m-0 rounded-xl bg-surface-2 px-4 py-3">
                        <dt className="text-[13px] text-muted">{form.pricing === 'count' ? 'مشتری برای هر ۵۰۰ عدد می‌پردازد' : 'مشتری برای هر ۱۰۰۰ عدد می‌پردازد'}</dt>
                        <dd className="num m-0 mt-0.5 text-[18px] font-bold">{price ? toman(price) : '—'}</dd>
                        {form.pricing === 'count' && minCount != null && maxCount != null && minCount <= maxCount && (
                            <dd className="m-0 mt-1 text-[13px] text-muted">
                                سفارش از <span className="num">{count(minCount)}</span> تا <span className="num">{count(maxCount)}</span> عدد، با گام ۵۰۰
                                {price && minCount % 500 === 0 && maxCount % 500 === 0 ? <> (<span className="num">{toman((minCount / 500) * price)}</span> تا <span className="num">{toman((maxCount / 500) * price)}</span>)</> : null}
                            </dd>
                        )}
                        {!rate && <dd className="m-0 mt-1 text-[12px] text-warn">نرخ دلار خدمات (ارز ۲۰) تنظیم نشده است.</dd>}
                    </dl>
                    {platform && form.slug && (
                        <p dir="ltr" className="latin truncate text-end text-[12px] text-muted">
                            /virtual-service/{platform.slug}/{form.slug}
                        </p>
                    )}
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="پلتفرم" error={err('categoryId')}>
                        {(p) => (
                            <select {...p} className="field" value={form.categoryId} onChange={(e) => set({ categoryId: Number(e.target.value) || '' })}>
                                <option value="">انتخاب پلتفرم</option>
                                {cats.data?.categories.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </select>
                        )}
                    </Field>
                    <Field label="نام" error={err('name')}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} placeholder="ممبر واقعی تلگرام" />}
                    </Field>
                    <Field label="نام انگلیسی" error={err('enName')}>
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.enName}
                                onChange={(e) => set({ enName: e.target.value, ...(slugTouched ? {} : { slug: slugify(e.target.value) }) })}
                                placeholder="Real Telegram members"
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
                                placeholder="buy-telegram-members"
                            />
                        )}
                    </Field>
                    <Field label="توضیح کوتاه" optional className="sm:col-span-2" error={err('short')}>
                        {(p) => <textarea {...p} rows={2} className="field" value={form.short} onChange={(e) => set({ short: e.target.value })} />}
                    </Field>
                </div>
            </Panel>

            <Panel title="قیمت">
                <fieldset className="m-0 border-0 p-0">
                    <legend className="mb-2 font-medium">مشتری چطور تعداد را انتخاب می‌کند؟</legend>
                    <div className="grid gap-2 sm:grid-cols-2">
                        {(
                            [
                                ['count', 'تعداد دلخواه', 'مشتری هر تعدادی بین حداقل و حداکثر را با گام ۵۰۰ انتخاب می‌کند؛ قیمت هر ۵۰۰ عدد به دلار.'],
                                ['plans', 'پلن‌های ثابت', 'بسته‌های آماده (مثل ۵۰۰ و ۱۰۰۰)؛ قیمت از نرخ دلار حساب می‌شود.'],
                            ] as const
                        ).map(([value, title, hint]) => (
                            <label
                                key={value}
                                className={`flex cursor-pointer gap-3 rounded-2xl border-2 p-3.5 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring ${
                                    form.pricing === value ? 'border-primary' : 'border-stroke hover:border-field'
                                }`}
                            >
                                <input type="radio" name="pricing" value={value} checked={form.pricing === value} onChange={() => set({ pricing: value })} className="mt-1 size-4 shrink-0 accent-[var(--primary)]" />
                                <span>
                                    <span className="block font-bold">{title}</span>
                                    <span className="block text-[13px] text-muted">{hint}</span>
                                </span>
                            </label>
                        ))}
                    </div>
                </fieldset>

                {form.pricing === 'count' ? (
                    <div className="mt-4 grid gap-4 sm:grid-cols-3">
                        <Field
                            label="قیمت هر ۵۰۰ عدد (دلار)"
                            error={err('dollarPer500')}
                            hint={rate ? <>با نرخ <span className="num">{toman(rate)}</span> به تومان تبدیل می‌شود.</> : 'نرخ دلار خدمات تنظیم نشده است.'}
                        >
                            {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={form.dollarPer500 ?? ''} onChange={(e) => set({ dollarPer500: e.target.value })} placeholder="0.40" />}
                        </Field>
                        <Field label="حداقل سفارش (عدد)" error={err('minCount')} hint="مضربی از ۵۰۰، دست‌کم ۵۰۰.">
                            {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.minCount} onChange={(e) => set({ minCount: e.target.value })} placeholder="500" />}
                        </Field>
                        <Field label="حداکثر سفارش (عدد)" error={err('maxCount')} hint="مضربی از ۵۰۰.">
                            {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.maxCount} onChange={(e) => set({ maxCount: e.target.value })} placeholder="10000" />}
                        </Field>
                    </div>
                ) : (
                    <div className="mt-4 grid gap-4 sm:grid-cols-2">
                        <Field
                            label="قیمت هر ۱۰۰۰ عدد (دلار)"
                            error={err('dollarPer1000')}
                            hint={rate ? <>با نرخ <span className="num">{toman(rate)}</span> به تومان تبدیل می‌شود.</> : 'نرخ دلار خدمات تنظیم نشده است.'}
                        >
                            {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={form.dollarPer1000 ?? ''} onChange={(e) => set({ dollarPer1000: e.target.value })} placeholder="1.20" />}
                        </Field>
                    </div>
                )}
                <div className="mt-4 grid gap-4 sm:grid-cols-3">
                    <Field label="پورسانت معرف (٪)" optional error={err('commission')}>
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.commission ?? ''} onChange={(e) => set({ commission: e.target.value })} />}
                    </Field>
                </div>
            </Panel>

            {form.pricing === 'plans' && (
                <Panel title="پلن‌ها">
                    {err('plans') && <p className="mb-3 font-bold text-error">{err('plans')}</p>}
                    <RowsEditor
                        rows={form.plans}
                        onChange={(plans) => set({ plans })}
                        make={() => ({ name: '', count: '', live: true })}
                        addLabel="افزودن پلن"
                        empty="هنوز پلنی ندارد."
                        removeLabel={(r, i) => `حذف پلن ${r.name || i + 1}`}
                        render={(row, update, i) => (
                            <div className="grid gap-3 sm:grid-cols-[1fr_140px_auto] sm:items-end">
                                <Field label="نام پلن" error={err(`plans.${i}.name`)}>
                                    {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="۵۰۰ ممبر تلگرام" />}
                                </Field>
                                <Field label="تعداد" error={err(`plans.${i}.count`)}>
                                    {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.count} onChange={(e) => update({ count: e.target.value })} />}
                                </Field>
                                <Switch label="فعال" checked={row.live} onChange={(v) => update({ live: v })} />
                            </div>
                        )}
                    />
                </Panel>
            )}

            <Panel title="فیلدهای سفارش">
                <p className="mb-3 text-muted">آنچه مشتری هنگام خرید وارد می‌کند؛ معمولاً فقط لینک کانال یا پیج. هیچ‌وقت رمز عبور نخواهید.</p>
                <RowsEditor
                    rows={form.fields}
                    onChange={(fields) => set({ fields })}
                    make={() => ({ name: '', title: '', type: 'text' as const, options: [] })}
                    addLabel="افزودن فیلد"
                    empty="بدون فیلد، سفارش نمی‌داند کجا انجام شود."
                    removeLabel={(r, i) => `حذف فیلد ${r.title || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-3">
                            <Field label="عنوان برای مشتری" error={err(`fields.${i}.title`)}>
                                {(p) => <input {...p} className="field" value={row.title} onChange={(e) => update({ title: e.target.value })} placeholder="لینک کانال" />}
                            </Field>
                            <Field label="نام فنی" error={err(`fields.${i}.name`)}>
                                {(p) => <input {...p} dir="ltr" className="field latin" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder="link" />}
                            </Field>
                            <Field label="نوع">
                                {(p) => (
                                    <select {...p} className="field" value={row.type} onChange={(e) => update({ type: e.target.value as 'text' | 'select' })}>
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
                                            placeholder="عادی، سریع"
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
                    empty="مثل «زمان تحویل: آنی» یا «ضمانت: ۳۰ روز»."
                    removeLabel={(r, i) => `حذف نکته ${r.label || i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-2">
                            <Field label="عنوان" error={err(`notes.${i}.label`)}>
                                {(p) => <input {...p} className="field" value={row.label} onChange={(e) => update({ label: e.target.value })} placeholder="زمان تحویل" />}
                            </Field>
                            <Field label="مقدار" error={err(`notes.${i}.value`)}>
                                {(p) => <input {...p} className="field" value={row.value} onChange={(e) => update({ value: e.target.value })} placeholder="آنی" />}
                            </Field>
                        </div>
                    )}
                />
            </Panel>

            <Panel title="سؤالات متداول">
                <RowsEditor
                    rows={form.faq}
                    onChange={(faq) => set({ faq })}
                    make={() => ({ q: '', a: '' })}
                    addLabel="افزودن سؤال"
                    empty="سؤالی ثبت نشده است."
                    removeLabel={(r, i) => `حذف سؤال ${i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3">
                            <Field label="سؤال" error={err(`faq.${i}.q`)}>
                                {(p) => <input {...p} className="field" value={row.q} onChange={(e) => update({ q: e.target.value })} />}
                            </Field>
                            <Field label="پاسخ" error={err(`faq.${i}.a`)}>
                                {(p) => <textarea {...p} rows={2} className="field" value={row.a} onChange={(e) => update({ a: e.target.value })} />}
                            </Field>
                        </div>
                    )}
                />
            </Panel>

            <Panel title="محتوا و سئو">
                <div className="grid gap-4">
                    <ImageField label="تصویر سرویس" value={form.image} onChange={(path) => set({ image: path })} error={err('image')} uploadUrl={UPLOAD_URL} />
                    <Field label="عنوان سئو" optional error={err('metaTitle')}>
                        {(p) => <input {...p} className="field" value={form.metaTitle} onChange={(e) => set({ metaTitle: e.target.value })} />}
                    </Field>
                    <Field label="توضیح سئو" optional error={err('metaDesc')}>
                        {(p) => <textarea {...p} rows={2} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                    <Field label="متن صفحه سرویس (HTML)" optional error={err('content')}>
                        {(p) => <textarea {...p} rows={10} dir="auto" className="field font-mono text-[13px]" value={form.content} onChange={(e) => set({ content: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
