import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { ExternalLink } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { parseAmount, toman } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, Switch } from '../../components/form';
import { RichText } from '../../components/RichText';
import { ErrorState, Panel } from '../../components/ui';
import { EditorSkeleton, FaqEditor, problemsFrom, type FaqRow } from '../../components/list';

type Settings = { normalPrice: number | null; customPrice: number | null; numberlandEnabled: boolean; numberlandMarkup: number };
type Page = {
    id: number;
    title: string;
    path: string | null;
    kind: 'page' | 'apple-id' | 'google-voice';
    h1: string;
    short: string;
    content: string;
    image: string;
    faq: FaqRow[];
    metaTitle: string;
    metaDesc: string;
    metaImage: string;
    headHtml: string;
    settings: Settings;
};
type Form = Omit<Page, 'settings'> & { settings: { normalPrice: string; customPrice: string; numberlandEnabled: boolean; numberlandMarkup: string } };

const labels: Record<string, string> = {
    h1: 'عنوان H1', short: 'توضیح کوتاه', content: 'متن صفحه', image: 'تصویر', metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', faq: 'سؤالات متداول',
    'settings.normalPrice': 'قیمت اپل آیدی آماده', 'settings.customPrice': 'قیمت', 'settings.numberlandMarkup': 'سود نامبرلند',
};

export default function PageEditor() {
    const { id } = useParams();
    const { data, error, reload } = useQuery<{ page: Page }>(`/admin/api/pages/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(null);

    useEffect(() => {
        if (!data) return;
        const s = data.page.settings;
        setForm({
            ...data.page,
            settings: {
                normalPrice: s.normalPrice == null ? '' : String(s.normalPrice),
                customPrice: s.customPrice == null ? '' : String(s.customPrice),
                numberlandEnabled: s.numberlandEnabled,
                numberlandMarkup: String(s.numberlandMarkup),
            },
        });
    }, [data]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!form) return <EditorSkeleton />;

    const set = (patch: Partial<Form>) => {
        save.clearSaved();
        setForm((f) => (f ? { ...f, ...patch } : f));
    };
    const setSettings = (patch: Partial<Form['settings']>) => set({ settings: { ...form.settings, ...patch } });
    const err = (k: string) => save.errors[k];
    const money = (raw: string) => (raw === '' ? null : (parseAmount(raw) ?? raw));

    async function submit() {
        if (!form) return;
        const res = await save.run(`/admin/api/pages/${id}`, {
            ...form,
            settings: {
                normalPrice: money(form.settings.normalPrice),
                customPrice: money(form.settings.customPrice),
                numberlandEnabled: form.settings.numberlandEnabled,
                numberlandMarkup: form.settings.numberlandMarkup === '' ? null : Number(form.settings.numberlandMarkup.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)))),
            },
        });
        if (res) reload();
    }

    const priceInput = (key: 'normalPrice' | 'customPrice', label: string, hint?: string) => (
        <Field label={label} error={err(`settings.${key}`)} hint={hint ?? (parseAmount(form.settings[key]) != null ? toman(parseAmount(form.settings[key])) : undefined)}>
            {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.settings[key]} onChange={(e) => setSettings({ [key]: e.target.value })} />}
        </Field>
    );

    return (
        <EditorLayout
            backTo="/pages"
            backLabel="صفحه‌ها"
            title={form.title || `صفحه ${form.id}`}
            subtitle={form.path ? <span dir="ltr" className="latin">{form.path}</span> : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel="ذخیره صفحه"
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                form.path ? (
                    <a href={form.path} target="_blank" rel="noreferrer" className="flex items-center gap-1.5 text-[13px] font-medium text-body underline-offset-4 hover:underline">
                        <ExternalLink aria-hidden size={14} />
                        دیدن صفحه در سایت
                    </a>
                ) : undefined
            }
        >
            {form.kind === 'apple-id' && (
                <Panel title="قیمت‌ها">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {priceInput('normalPrice', 'اپل آیدی آماده (تومان)')}
                        {priceInput('customPrice', 'اپل آیدی سفارشی (تومان)')}
                    </div>
                    <p className="mt-3 text-[13px] text-muted">
                        موجودی اپل آیدی‌های آماده در <Link to="/apple-ids" className="font-medium text-body underline underline-offset-4">بخش اپل آیدی</Link> است.
                    </p>
                </Panel>
            )}

            {form.kind === 'google-voice' && (
                <Panel title="قیمت و نامبرلند">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {priceInput('customPrice', 'قیمت شماره دستی (تومان)')}
                        <Field label="سود روی قیمت نامبرلند (٪)" error={err('settings.numberlandMarkup')} hint="قیمت فروش = هزینه نامبرلند + این درصد، گرد به ۱۰۰۰ تومان.">
                            {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={form.settings.numberlandMarkup} onChange={(e) => setSettings({ numberlandMarkup: e.target.value })} />}
                        </Field>
                        <div className="sm:col-span-2">
                            <Switch
                                label="فروش خودکار از نامبرلند"
                                hint="روشن: شماره‌ها از نامبرلند خریده و فوری تحویل می‌شوند."
                                checked={form.settings.numberlandEnabled}
                                onChange={(numberlandEnabled) => setSettings({ numberlandEnabled })}
                            />
                        </div>
                    </div>
                </Panel>
            )}

            <Panel title="بالای صفحه">
                <div className="grid gap-4">
                    <Field label="عنوان H1" error={err('h1')}>
                        {(p) => <input {...p} className="field" value={form.h1} onChange={(e) => set({ h1: e.target.value })} />}
                    </Field>
                    <Field label="توضیح کوتاه" optional error={err('short')}>
                        {(p) => <textarea {...p} rows={3} className="field" value={form.short} onChange={(e) => set({ short: e.target.value })} />}
                    </Field>
                    <ImageField label="تصویر صفحه" value={form.image} onChange={(image) => set({ image })} error={err('image')} uploadUrl="/admin/api/uploads/page-image" noSvg />
                </div>
            </Panel>

            <Panel title="متن صفحه">
                <RichText label="متن کامل" optional value={form.content} onChange={(content) => set({ content })} error={err('content')} height={480} />
            </Panel>

            <Panel title="سؤالات متداول">
                <FaqEditor rows={form.faq} onChange={(faq) => set({ faq })} errors={save.errors} />
            </Panel>

            <Panel title="سئو">
                <div className="grid gap-4">
                    <Field label="عنوان سئو" optional error={err('metaTitle')}>
                        {(p) => <input {...p} className="field" value={form.metaTitle} onChange={(e) => set({ metaTitle: e.target.value })} />}
                    </Field>
                    <Field label="توضیح سئو" optional error={err('metaDesc')}>
                        {(p) => <textarea {...p} rows={2} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                    <ImageField label="تصویر اشتراک‌گذاری" value={form.metaImage} onChange={(metaImage) => set({ metaImage })} uploadUrl="/admin/api/uploads/page-image" noSvg hint="در تلگرام و شبکه‌ها کنار لینک دیده می‌شود." />
                    <Field label="کد HTML اضافه در head" optional hint="مثلاً اسکیما. همین عنوان‌ها در بخش «سئوی صفحه‌ها» هم دیده می‌شوند.">
                        {(p) => <textarea {...p} rows={4} dir="ltr" className="field font-mono text-[13px]" value={form.headHtml} onChange={(e) => set({ headHtml: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
