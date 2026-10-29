import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, Switch, slugify } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { PlatformTile } from './ServicesPage';
import { UPLOAD_URL, type Platform } from './types';

type Form = Omit<Platform, 'id' | 'services' | 'liveServices'>;

const empty: Form = {
    name: '',
    enName: '',
    slug: '',
    short: '',
    tagline: '',
    taglineEn: '',
    content: '',
    image: '',
    color: '#0ea5e9',
    sort: 0,
    featured: false,
    visible: true,
    metaTitle: '',
    metaDesc: '',
};

const labels: Record<string, string> = {
    name: 'نام', enName: 'نام انگلیسی', slug: 'نامک', color: 'رنگ', sort: 'ترتیب', short: 'متن کوتاه سئو', tagline: 'شعار فارسی', taglineEn: 'شعار انگلیسی', image: 'لوگو',
};

export default function PlatformEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const { csrfToken } = useBoot();
    const list = useQuery<{ categories: Platform[] }>('/admin/api/service-categories');
    const existing = list.data?.categories.find((c) => String(c.id) === id);
    const [form, setForm] = useState<Form>(empty);
    const [slugTouched, setSlugTouched] = useState(!isNew);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);
    const [failure, setFailure] = useState<string | null>(null);

    useEffect(() => {
        if (existing) setForm({ ...empty, ...existing, color: existing.color || '#475569' });
        else if (isNew && list.data) setForm({ ...empty, sort: list.data.categories.length + 1 });
    }, [existing, isNew, list.data]);

    const set = (patch: Partial<Form>) => {
        setSaved(null);
        setForm((f) => ({ ...f, ...patch }));
    };

    async function save() {
        setSaving(true);
        setSaved(null);
        setFailure(null);
        try {
            const res = await postJson<{ success: string; category: Platform }>(isNew ? '/admin/api/service-categories' : `/admin/api/service-categories/${id}`, csrfToken, form);
            setErrors({});
            if (isNew) navigate(`/services?platform=${res.category.id}`);
            else {
                setSaved(res.success);
                list.reload();
            }
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
        } finally {
            setSaving(false);
        }
    }

    if (list.error) return <ErrorState error={list.error} onRetry={list.reload} boxed />;
    if (!isNew && !list.data) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }
    if (!isNew && list.data && !existing) return <ErrorState error={new ApiError('این پلتفرم پیدا نشد.')} onRetry={list.reload} boxed />;

    const problems = [
        ...Object.entries(errors).map(([k, message]) => ({ label: labels[k] ?? k, message })),
        ...(failure ? [{ label: 'ذخیره', message: failure }] : []),
    ];

    return (
        <EditorLayout
            backTo={isNew ? '/services' : `/services?platform=${id}`}
            backLabel="خدمات مجازی"
            title={isNew ? 'پلتفرم جدید' : `تنظیمات ${existing?.name ?? ''}`}
            subtitle="هر پلتفرم یک کاشی در صفحه خدمات شبکه‌های اجتماعی سایت است و سرویس‌هایش زیر آن فروخته می‌شوند."
            onSubmit={save}
            saving={saving}
            saveLabel={isNew ? 'ساخت پلتفرم' : 'ذخیره تغییرات'}
            saved={saved}
            problems={problems}
            side={
                <div>
                    <p className="mb-2 text-[13px] text-muted">پیش‌نمایش کاشی در سایت</p>
                    <div className="flex items-center gap-3 rounded-xl border border-stroke p-3">
                        <PlatformTile platform={form} size={44} />
                        <div className="min-w-0">
                            <p className="latin truncate font-bold">{form.enName || form.name || 'نام پلتفرم'}</p>
                            <p className="latin truncate text-[13px] text-muted">{form.taglineEn || form.tagline || 'Members, views, reactions'}</p>
                        </div>
                    </div>
                    <div className="mt-3 space-y-1">
                        <Switch label="نمایش در سایت" hint="پنهان‌ها در صفحه خدمات دیده نمی‌شوند." checked={form.visible} onChange={(v) => set({ visible: v })} />
                        <Switch label="ویژه" hint="بالاتر و بزرگ‌تر نمایش داده می‌شود." checked={form.featured} onChange={(v) => set({ featured: v })} />
                    </div>
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={errors.name}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} placeholder="تلگرام" />}
                    </Field>
                    <Field label="نام انگلیسی" error={errors.enName} hint="روی کاشی سایت انگلیسی نمایش داده می‌شود.">
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.enName ?? ''}
                                onChange={(e) => set({ enName: e.target.value, ...(slugTouched ? {} : { slug: slugify(e.target.value) }) })}
                                placeholder="Telegram"
                            />
                        )}
                    </Field>
                    <Field label="نامک (آدرس)" error={errors.slug} hint={<span dir="ltr" className="latin">/virtual-service/{form.slug || '…'}</span>}>
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
                                placeholder="telegram"
                            />
                        )}
                    </Field>
                    <Field label="ترتیب" error={errors.sort} hint="عدد کوچک‌تر اول می‌آید.">
                        {(p) => <input {...p} type="number" dir="ltr" className="field num" value={form.sort} onChange={(e) => set({ sort: Number(e.target.value) })} />}
                    </Field>
                    <Field label="شعار فارسی" error={errors.tagline} optional hint="یک خط زیر نام در سایت فارسی.">
                        {(p) => <input {...p} className="field" value={form.tagline ?? ''} onChange={(e) => set({ tagline: e.target.value })} placeholder="ممبر، بازدید، ری‌اکشن" />}
                    </Field>
                    <Field label="شعار انگلیسی" error={errors.taglineEn} optional hint="یک خط زیر نام در سایت انگلیسی.">
                        {(p) => <input {...p} dir="ltr" className="field latin" value={form.taglineEn ?? ''} onChange={(e) => set({ taglineEn: e.target.value })} placeholder="Members, views, reactions" />}
                    </Field>
                </div>
            </Panel>

            <Panel title="ظاهر">
                <div className="grid gap-5 sm:grid-cols-2">
                    <ImageField label="لوگو" value={form.image ?? ''} onChange={(path) => set({ image: path })} error={errors.image} uploadUrl={UPLOAD_URL} square />
                    <Field label="رنگ برند" error={errors.color} hint="وقتی لوگو نیست، کاشی با این رنگ و حرف اول نام ساخته می‌شود.">
                        {(p) => (
                            <div className="flex gap-2">
                                <input type="color" aria-label="انتخاب رنگ" value={form.color || '#475569'} onChange={(e) => set({ color: e.target.value })} className="h-[38px] w-12 shrink-0 cursor-pointer rounded-[10px] border border-field bg-surface p-1" />
                                <input {...p} dir="ltr" className="field latin" value={form.color ?? ''} onChange={(e) => set({ color: e.target.value })} placeholder="#0ea5e9" />
                            </div>
                        )}
                    </Field>
                </div>
            </Panel>

            <Panel title="محتوا و سئو">
                <div className="grid gap-4">
                    <Field label="متن کوتاه سئو" optional error={errors.short} hint="در سایت قدیم و توضیحات موتور جستجو استفاده می‌شود.">
                        {(p) => <textarea {...p} rows={2} className="field" value={form.short ?? ''} onChange={(e) => set({ short: e.target.value })} />}
                    </Field>
                    <Field label="عنوان سئو" optional error={errors.metaTitle}>
                        {(p) => <input {...p} className="field" value={form.metaTitle ?? ''} onChange={(e) => set({ metaTitle: e.target.value })} />}
                    </Field>
                    <Field label="توضیح سئو" optional error={errors.metaDesc}>
                        {(p) => <textarea {...p} rows={2} className="field" value={form.metaDesc ?? ''} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                    <Field label="متن صفحه پلتفرم (HTML)" optional error={errors.content}>
                        {(p) => <textarea {...p} rows={8} dir="auto" className="field font-mono text-[13px]" value={form.content ?? ''} onChange={(e) => set({ content: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
