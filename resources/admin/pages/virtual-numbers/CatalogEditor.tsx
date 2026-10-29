import { useEffect, useMemo, useState } from 'react';
import { useLocation, useNavigate, useParams } from 'react-router';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, Switch, slugify } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { CodeFields, OffersPanel, RebuildOffers, Thumb } from './parts';
import {
    API,
    PROVIDERS,
    UPLOAD_URL,
    emptyCodes,
    nouns,
    shortName,
    type CatalogRow,
    type CountryForm,
    type Kind,
    type Offer,
    type Region,
    type ServiceForm,
} from './types';

type Form = ServiceForm & CountryForm;

const blank: Form = {
    name: '',
    enName: '',
    slug: '',
    sort: 0,
    live: true,
    featured: false,
    image: '',
    codes: emptyCodes,
    content: '',
    metaTitle: '',
    metaDesc: '',
    seo: '',
    flagCode: '',
    regionId: '',
};

const labels: Record<string, string> = {
    name: 'نام', enName: 'نام انگلیسی', slug: 'نامک', sort: 'ترتیب', image: 'تصویر', content: 'توضیحات',
    metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', seo: 'کد سئو', flagCode: 'کد پرچم', regionId: 'منطقه',
    ...Object.fromEntries(PROVIDERS.map((p) => [`codes.${p.key}`, p.server ? `کد سرور ${p.server}` : 'کد Land'])),
};

const copy: Record<Kind, { list: string; created: string; namePh: string; enPh: string; intro: string }> = {
    services: {
        list: 'سرویس‌های شماره مجازی',
        created: 'سرویس ساخته شد. کد سرورها که درست است، «بازسازی پیشنهادها» را بزنید تا در کشورها فروخته شود.',
        namePh: 'شماره مجازی تلگرام',
        enPh: 'Telegram',
        intro: 'برنامه‌ای که برایش شماره فروخته می‌شود. هر سرور با کد خودش این برنامه را می‌شناسد.',
    },
    countries: {
        list: 'کشورهای شماره مجازی',
        created: 'کشور ساخته شد. کد سرورها که درست است، «بازسازی پیشنهادها» را بزنید تا سرویس‌ها در آن فروخته شوند.',
        namePh: 'شماره مجازی کشور آلمان',
        enPh: 'Germany',
        intro: 'کشوری که شماره‌هایش فروخته می‌شود. هر سرور با کد خودش این کشور را می‌شناسد.',
    },
};

export default function CatalogEditor({ kind }: { kind: Kind }) {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const location = useLocation();
    const { csrfToken } = useBoot();
    const n = nouns[kind];
    const c = copy[kind];
    const one = kind === 'services' ? 'service' : 'country';

    const record = useQuery<{ service?: Form; country?: Form; regions?: Region[] }>(isNew ? null : `${API}/${kind}/${id}`);
    const list = useQuery<{ services?: CatalogRow[]; countries?: CatalogRow[]; regions?: Region[] }>(`${API}/${kind}`);
    const offers = useQuery<{ offers: Offer[] }>(isNew ? null : `${API}/offers?${one}=${id}`);
    const loaded = record.data?.[one];
    const regions = record.data?.regions ?? list.data?.regions ?? [];
    const counts = list.data?.[kind]?.find((r) => String(r.id) === id)?.offers ?? null;

    const [form, setForm] = useState<Form>(blank);
    const [autoSlug, setAutoSlug] = useState(isNew);
    const [autoMeta, setAutoMeta] = useState(isNew);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>((location.state as { created?: boolean } | null)?.created ? c.created : null);
    const [failure, setFailure] = useState<string | null>(null);

    useEffect(() => {
        if (loaded) setForm({ ...blank, ...loaded, codes: { ...emptyCodes, ...loaded.codes } });
    }, [loaded]);
    useEffect(() => {
        // A new record goes to the end of the site order.
        if (isNew && list.data?.[kind]) setForm((f) => ({ ...f, sort: Math.max(0, ...list.data![kind]!.map((r) => r.sort)) + 1 }));
    }, [isNew, list.data, kind]);

    const unsavedCodes = useMemo(() => !!loaded && PROVIDERS.some((p) => (loaded.codes[p.key] ?? '') !== form.codes[p.key].trim()), [loaded, form.codes]);

    const set = (patch: Partial<Form>) => {
        setSaved(null);
        setForm((f) => ({ ...f, ...patch }));
    };

    async function save() {
        setSaving(true);
        setSaved(null);
        setFailure(null);
        const payload =
            kind === 'services'
                ? { ...form, flagCode: undefined, regionId: undefined }
                : { ...form, featured: undefined, regionId: form.regionId === '' ? null : form.regionId };
        try {
            const res = await postJson<{ success: string; id: number }>(isNew ? `${API}/${kind}` : `${API}/${kind}/${id}`, csrfToken, payload);
            setErrors({});
            if (isNew) navigate(`/virtual-numbers/${kind}/${res.id}`, { replace: true, state: { created: true } });
            else {
                setSaved(res.success);
                record.reload();
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

    if (record.error) return <ErrorState error={record.error} onRetry={record.reload} boxed />;
    if (!isNew && !loaded) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }

    const problems = [
        ...Object.entries(errors).map(([k, message]) => ({ label: labels[k] ?? k, message })),
        ...(failure ? [{ label: 'ذخیره', message: failure }] : []),
    ];
    const flag = form.flagCode.trim() ? `/image/svg/${form.flagCode.trim().toLowerCase()}.svg` : null;
    const title = isNew ? (kind === 'services' ? 'سرویس جدید' : 'کشور جدید') : shortName(loaded?.name) || loaded?.enName || `${n.one} ${id}`;

    return (
        <EditorLayout
            backTo={`/virtual-numbers/${kind}`}
            backLabel={c.list}
            title={title}
            subtitle={c.intro}
            onSubmit={save}
            saving={saving}
            saveLabel={isNew ? `ساخت ${n.one}` : 'ذخیره تغییرات'}
            saved={saved}
            problems={problems}
            side={
                <div>
                    <div className="flex items-center gap-3 rounded-xl border border-stroke p-3">
                        <Thumb src={kind === 'countries' ? flag : form.image} name={form.name} flag={kind === 'countries'} size={40} />
                        <div className="min-w-0">
                            <p className="truncate font-bold">{shortName(form.name) || `نام ${n.one}`}</p>
                            <p dir="ltr" className="latin truncate text-end text-[13px] text-muted">
                                {form.enName || 'English name'}
                            </p>
                        </div>
                    </div>
                    <div className="mt-3 space-y-1">
                        <Switch
                            label="فعال"
                            hint={
                                kind === 'services'
                                    ? 'غیرفعال‌ها و پیشنهادهایشان در سایت نمایش داده نمی‌شوند.'
                                    : 'فعلاً فقط ذخیره می‌شود؛ سایت کشورها را از روی پیشنهادهای در فروش نشان می‌دهد.'
                            }
                            checked={form.live}
                            onChange={(v) => set({ live: v })}
                        />
                        {kind === 'services' && (
                            <Switch label="ویژه" hint="در سایت با ستاره «محبوب» نمایش داده می‌شود." checked={form.featured} onChange={(v) => set({ featured: v })} />
                        )}
                    </div>
                    {!isNew && (
                        <RebuildOffers
                            kind={kind}
                            id={Number(id)}
                            counts={counts}
                            unsaved={unsavedCodes}
                            onDone={() => {
                                offers.reload();
                                list.reload();
                            }}
                        />
                    )}
                </div>
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام" error={errors.name} hint={`مثل «${c.namePh}». «شماره مجازی» اول نام در فهرست‌ها نمایش داده نمی‌شود.`}>
                        {(p) => (
                            <input
                                {...p}
                                className="field"
                                value={form.name}
                                onChange={(e) => set({ name: e.target.value, ...(autoMeta ? { metaTitle: e.target.value ? `خرید ${e.target.value}` : '' } : {}) })}
                            />
                        )}
                    </Field>
                    <Field label="نام انگلیسی" error={errors.enName} hint={<>مثل <span className="latin">{c.enPh}</span>؛ نامک از روی آن ساخته می‌شود.</>}>
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.enName}
                                onChange={(e) => set({ enName: e.target.value, ...(autoSlug ? { slug: slugify(e.target.value) } : {}) })}
                            />
                        )}
                    </Field>
                    <Field label="نامک" error={errors.slug} hint="حروف کوچک انگلیسی، عدد، نقطه و خط تیره.">
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.slug}
                                onChange={(e) => {
                                    setAutoSlug(false);
                                    set({ slug: e.target.value.toLowerCase() });
                                }}
                            />
                        )}
                    </Field>
                    <Field label="ترتیب" error={errors.sort} hint="عدد کوچک‌تر در سایت اول می‌آید.">
                        {(p) => <input {...p} type="number" min={0} dir="ltr" className="field num" value={form.sort} onChange={(e) => set({ sort: e.target.value === '' ? '' : Number(e.target.value) })} />}
                    </Field>
                    {kind === 'countries' && (
                        <>
                            <Field label="کد پرچم" error={errors.flagCode} hint={<>دو حرف ISO مثل <span className="latin">DE</span>؛ پرچم از <span dir="ltr" className="latin">/image/svg</span> می‌آید.</>}>
                                {(p) => (
                                    <div className="flex items-center gap-2">
                                        <input {...p} dir="ltr" className="field latin" value={form.flagCode} onChange={(e) => set({ flagCode: e.target.value.trim() })} />
                                        <Thumb src={flag} name={form.name} flag size={28} />
                                    </div>
                                )}
                            </Field>
                            <Field label="منطقه" error={errors.regionId} optional>
                                {(p) => (
                                    <select {...p} className="field" value={form.regionId} onChange={(e) => set({ regionId: e.target.value === '' ? '' : Number(e.target.value) })}>
                                        <option value="">بدون منطقه</option>
                                        {regions.map((r) => (
                                            <option key={r.id} value={r.id}>
                                                {r.name}
                                            </option>
                                        ))}
                                    </select>
                                )}
                            </Field>
                        </>
                    )}
                </div>
            </Panel>

            <Panel title="کد سرورها">
                <p className="mb-4 text-muted">
                    هر سرور این {n.one} را با کد خودش می‌شناسد. خالی یعنی آن سرور این {n.one} را نمی‌فروشد. پیشنهاد فروش فقط برای سروری ساخته می‌شود که هم {n.one} و هم {n.other} کدش را داشته باشند.
                </p>
                <CodeFields codes={form.codes} onChange={(codes) => set({ codes })} errors={errors} />
            </Panel>

            <Panel title="تصویر و محتوا">
                <div className="grid gap-5">
                    <ImageField
                        label={kind === 'services' ? 'آیکن سرویس' : 'تصویر کشور'}
                        hint={kind === 'countries' ? 'اختیاری؛ فهرست‌ها پرچم را نشان می‌دهند. PNG، JPG، WebP یا SVG، حداکثر ۲ مگابایت' : undefined}
                        value={form.image}
                        onChange={(path) => set({ image: path })}
                        error={errors.image}
                        uploadUrl={UPLOAD_URL}
                        square={kind === 'services'}
                    />
                    <Field
                        label="توضیحات"
                        optional
                        error={errors.content}
                        hint={kind === 'countries' ? <span className="num">{form.content.length} از ۲۵۵ حرف</span> : 'HTML مجاز است.'}
                    >
                        {(p) =>
                            kind === 'countries' ? (
                                <textarea {...p} rows={3} maxLength={255} className="field" value={form.content} onChange={(e) => set({ content: e.target.value })} />
                            ) : (
                                <textarea {...p} rows={8} dir="auto" className="field text-[13px]" value={form.content} onChange={(e) => set({ content: e.target.value })} />
                            )
                        }
                    </Field>
                </div>
            </Panel>

            <Panel title="سئو">
                <div className="grid gap-4">
                    <Field label="عنوان سئو" error={errors.metaTitle} hint={<span className="num">{form.metaTitle.length} حرف؛ بهتر است زیر ۶۰ بماند.</span>}>
                        {(p) => (
                            <input
                                {...p}
                                className="field"
                                value={form.metaTitle}
                                onChange={(e) => {
                                    setAutoMeta(false);
                                    set({ metaTitle: e.target.value });
                                }}
                            />
                        )}
                    </Field>
                    <Field label="توضیح سئو" error={errors.metaDesc} hint={<span className="num">{form.metaDesc.length} از ۲۵۵ حرف</span>}>
                        {(p) => <textarea {...p} rows={3} maxLength={255} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                    <Field label="کد سئو (HTML)" optional error={errors.seo} hint="داخل head صفحه می‌آید؛ مثل اسکیمای FAQ.">
                        {(p) => <textarea {...p} rows={5} dir="ltr" className="field font-mono text-[13px]" value={form.seo} onChange={(e) => set({ seo: e.target.value })} />}
                    </Field>
                </div>
            </Panel>

            {!isNew && <OffersPanel kind={kind} offers={offers.data?.offers ?? null} error={offers.error} onRetry={offers.reload} />}
        </EditorLayout>
    );
}
