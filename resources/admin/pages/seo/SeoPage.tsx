import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router';
import { AlertTriangle, ExternalLink, ImageOff, Save } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { Field, ImageField } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';

/*
 * SEO of the site's main pages (App\Services\PageSeo): H1, title and description for search results, and the image
 * shown when a page is shared. Empty fields keep the page's own text. Pages down the side, the chosen one's editor
 * with live previews; edits are kept per page while switching, and each page saves on its own.
 */

type Texts = { h1: string; title: string; description: string; image: string };
type SeoPageRow = {
    key: string;
    label: string;
    path: string;
    group: string;
    h1: string | null;
    title: string | null;
    description: string | null;
    image: string | null;
    default: { h1: string; title: string; description: string } | null;
};

const FIELDS = ['h1', 'title', 'description', 'image'] as const;
const TITLE_MAX = 60;
const DESCRIPTION_MAX = 160;

const textsOf = (p: SeoPageRow): Texts => ({ h1: p.h1 ?? '', title: p.title ?? '', description: p.description ?? '', image: p.image ?? '' });
const same = (a: Texts, b: Texts) => FIELDS.every((f) => a[f].trim() === b[f].trim());
const filled = (t: Texts) => FIELDS.filter((f) => t[f].trim()).length;
const length = (s: string) => Array.from(s.trim()).length;
const fa = (n: number) => n.toLocaleString('fa-IR');

export default function SeoPage() {
    const { csrfToken } = useBoot();
    const q = useQuery<{ site: string; pages: SeoPageRow[] }>('/admin/api/seo-pages');
    const [params, setParams] = useSearchParams();
    // Saved values returned by the server, laid over the loaded list (no reload flash after saving).
    const [savedOver, setSavedOver] = useState<Record<string, Texts>>({});
    const [drafts, setDrafts] = useState<Record<string, Texts>>({});
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [failure, setFailure] = useState<string | null>(null);
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);

    const pages = q.data?.pages ?? [];
    const key = pages.some((p) => p.key === params.get('page')) ? params.get('page')! : pages[0]?.key;
    const page = pages.find((p) => p.key === key);
    const stored = (p: SeoPageRow) => savedOver[p.key] ?? textsOf(p);
    const draftOf = (p: SeoPageRow) => drafts[p.key] ?? stored(p);
    const dirty = (p: SeoPageRow) => !!drafts[p.key] && !same(drafts[p.key], stored(p));

    const groups = useMemo(() => {
        const out: { name: string; pages: SeoPageRow[] }[] = [];
        for (const p of pages) {
            const g = out.find((x) => x.name === p.group);
            if (g) g.pages.push(p);
            else out.push({ name: p.group, pages: [p] });
        }
        return out;
    }, [pages]);

    // A new page starts with a clean slate of messages.
    useEffect(() => {
        setErrors({});
        setFailure(null);
        setSaved(null);
    }, [key]);

    // After a failed save, start at the first field that needs fixing.
    useEffect(() => {
        if (Object.keys(errors).length) document.querySelector<HTMLElement>('main [aria-invalid="true"]')?.focus();
    }, [errors]);

    // Unsaved edits on any page: ask before leaving the admin.
    const anyDirty = pages.some(dirty);
    useEffect(() => {
        if (!anyDirty) return;
        const warn = (e: BeforeUnloadEvent) => e.preventDefault();
        window.addEventListener('beforeunload', warn);
        return () => window.removeEventListener('beforeunload', warn);
    }, [anyDirty]);

    if (q.error) return <ErrorState error={q.error} onRetry={q.reload} boxed />;

    const set = (patch: Partial<Texts>) => {
        if (!page) return;
        setSaved(null);
        setDrafts((d) => ({ ...d, [page.key]: { ...draftOf(page), ...patch } }));
    };

    async function save() {
        if (!page) return;
        setSaving(true);
        setSaved(null);
        setFailure(null);
        try {
            const res = await postJson<{ success: string; page: Record<string, string | null> }>(`/admin/api/seo-pages/${page.key}`, csrfToken, draftOf(page));
            const next: Texts = { h1: res.page.h1 ?? '', title: res.page.title ?? '', description: res.page.description ?? '', image: res.page.image ?? '' };
            setSavedOver((s) => ({ ...s, [page.key]: next }));
            setDrafts((d) => {
                const { [page.key]: done, ...rest } = d;
                void done;
                return rest;
            });
            setErrors({});
            setSaved(res.success);
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
        } finally {
            setSaving(false);
        }
    }

    const t = page ? draftOf(page) : null;
    const site = q.data?.site ?? '';
    const host = site.replace(/^https?:\/\//, '');

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5">
                <h1 className="text-[22px] leading-tight font-bold">سئوی صفحه‌ها</h1>
                <p className="mt-1 max-w-3xl text-muted">
                    عنوان اصلی (H1)، عنوان و توضیح نتیجه جست‌وجو و تصویر اشتراک‌گذاری صفحه‌های اصلی سایت. هر فیلد خالی بماند، متن خود صفحه نمایش داده می‌شود.
                    این متن‌ها روی نسخه فارسی سایت اعمال می‌شوند؛ نسخه انگلیسی متن‌های خودش را دارد.
                </p>
            </div>

            <div className="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <nav aria-label="صفحه‌ها" className="rounded-2xl border border-stroke bg-surface p-2 lg:sticky lg:top-20">
                    {!q.data ? (
                        <div className="space-y-2 p-2">
                            {[0, 1, 2, 3, 4].map((i) => (
                                <div key={i} className="skeleton h-12" />
                            ))}
                        </div>
                    ) : (
                        groups.map((g) => (
                            <div key={g.name} className="mb-1 last:mb-0">
                                <h2 className="px-3 pt-2.5 pb-1 text-[13px] font-bold text-muted">{g.name}</h2>
                                <ul className="space-y-0.5">
                                    {g.pages.map((p) => {
                                        const active = p.key === key;
                                        const n = filled(draftOf(p));
                                        const unsaved = dirty(p);
                                        return (
                                            <li key={p.key}>
                                                <button
                                                    type="button"
                                                    aria-current={active ? 'true' : undefined}
                                                    onClick={() => setParams({ page: p.key })}
                                                    className={`flex w-full flex-col items-start rounded-xl px-3 py-2 text-start transition-colors ${active ? 'bg-surface-2' : 'hover:bg-surface-2'}`}
                                                >
                                                    <span className={active ? 'font-bold' : 'font-medium'}>{p.label}</span>
                                                    <span className="text-[12px] text-muted">
                                                        {unsaved ? <span className="font-bold text-warn">ذخیره نشده</span> : n ? `${fa(n)} از ۴ مورد تنظیم شده` : 'متن خود صفحه'}
                                                    </span>
                                                </button>
                                            </li>
                                        );
                                    })}
                                </ul>
                            </div>
                        ))
                    )}
                </nav>

                {!page || !t ? (
                    <div className="skeleton h-96 rounded-2xl" />
                ) : (
                    <form
                        noValidate
                        onSubmit={(e) => {
                            e.preventDefault();
                            save();
                        }}
                        className="space-y-5"
                    >
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="min-w-0">
                                <h2 className="text-[18px] font-bold">{page.label}</h2>
                                <p dir="ltr" className="latin text-end text-[13px] text-muted">
                                    {page.path}
                                </p>
                            </div>
                            <a href={`${site}${page.path}`} target="_blank" rel="noopener" className="btn btn-secondary">
                                <ExternalLink aria-hidden size={16} />
                                مشاهده صفحه
                                <span className="sr-only"> (در برگه جدید)</span>
                            </a>
                        </div>

                        <Panel title="متن‌ها">
                            <div className="grid gap-5">
                                <Field label="عنوان اصلی صفحه (H1)" optional error={errors.h1} hint="بزرگ‌ترین عنوان بالای صفحه. یک جمله کوتاه که بگوید این صفحه چه می‌فروشد.">
                                    {(p) => <input {...p} className="field" value={t.h1} maxLength={255} onChange={(e) => set({ h1: e.target.value })} placeholder={page.default?.h1 ?? 'عنوان خود صفحه'} />}
                                </Field>
                                <Field label="عنوان در نتایج جست‌وجو (meta title)" optional error={errors.title} hint={<Counter n={length(t.title)} max={TITLE_MAX} over="گوگل عنوان‌های بلندتر را کوتاه می‌کند." />}>
                                    {(p) => <input {...p} className="field" value={t.title} maxLength={255} onChange={(e) => set({ title: e.target.value })} placeholder={page.default?.title ?? 'عنوان خود صفحه'} />}
                                </Field>
                                <Field label="توضیح در نتایج جست‌وجو (meta description)" optional error={errors.description} hint={<Counter n={length(t.description)} max={DESCRIPTION_MAX} over="گوگل توضیح‌های بلندتر را کوتاه می‌کند." />}>
                                    {(p) => <textarea {...p} rows={3} className="field" value={t.description} maxLength={500} onChange={(e) => set({ description: e.target.value })} placeholder={page.default?.description ?? 'توضیح خود صفحه'} />}
                                </Field>
                            </div>
                        </Panel>

                        <Panel title="تصویر اشتراک‌گذاری (og:image)">
                            <p className="mb-4 text-muted">وقتی لینک صفحه در تلگرام، واتس‌اپ یا ایکس فرستاده می‌شود، این تصویر کنار آن دیده می‌شود.</p>
                            <ImageField
                                label="تصویر"
                                value={t.image}
                                onChange={(image) => set({ image })}
                                error={errors.image}
                                uploadUrl="/admin/api/uploads/seo-image"
                                noSvg
                                hint="۱۲۰۰ × ۶۳۰ پیکسل، PNG، JPG یا WebP، حداکثر ۲ مگابایت"
                            />
                        </Panel>

                        <Panel title="پیش‌نمایش">
                            <div className="grid gap-5 xl:grid-cols-2">
                                <SearchPreview host={host} path={page.path} title={t.title} description={t.description} fallback={page.default} />
                                <SharePreview host={host} title={t.title} description={t.description} image={t.image} fallback={page.default} />
                            </div>
                        </Panel>

                        <div className="sticky bottom-4 z-10 flex flex-wrap items-center gap-3 rounded-2xl border border-stroke bg-surface px-4 py-3 shadow-[0_8px_24px_-12px_rgba(15,23,42,.25)]">
                            <button type="submit" className="btn btn-primary" disabled={saving || !dirty(page)}>
                                <Save aria-hidden size={16} />
                                {saving ? 'در حال ذخیره…' : 'ذخیره این صفحه'}
                            </button>
                            <p role="status" className="m-0 text-[13px]">
                                {failure || Object.keys(errors).length ? (
                                    <span className="flex items-center gap-1.5 font-bold text-error">
                                        <AlertTriangle aria-hidden size={15} className="shrink-0" />
                                        {failure ?? 'ذخیره نشد؛ پیام زیر فیلدها را ببینید.'}
                                    </span>
                                ) : saved ? (
                                    <span className="font-bold text-primary">{saved}</span>
                                ) : dirty(page) ? (
                                    <span className="text-warn">تغییرات این صفحه هنوز ذخیره نشده است.</span>
                                ) : (
                                    <span className="text-muted">تغییری برای ذخیره نیست.</span>
                                )}
                            </p>
                        </div>
                    </form>
                )}
            </div>
        </div>
    );
}

/** "۴۲ از ۶۰ حرف", and a warning in words once past the limit. */
function Counter({ n, max, over }: { n: number; max: number; over: string }) {
    return n > max ? (
        <span className="text-warn">
            <span className="num">{fa(n)}</span> حرف؛ بیشتر از {fa(max)} حرف پیشنهادی. {over}
        </span>
    ) : (
        <span>
            <span className="num">{fa(n)}</span> از {fa(max)} حرف پیشنهادی
        </span>
    );
}

type Fallback = SeoPageRow['default'];

/** Roughly how the page shows in a search result. */
function SearchPreview({ host, path, title, description, fallback }: { host: string; path: string; title: string; description: string; fallback: Fallback }) {
    const shownTitle = title.trim() || fallback?.title;
    const shownDesc = description.trim() || fallback?.description;
    return (
        <figure className="m-0">
            <figcaption className="mb-2 text-[13px] font-bold text-muted">نتیجه جست‌وجو</figcaption>
            <div className="rounded-xl border border-stroke p-4">
                <p className="flex items-center gap-2 text-[13px]">
                    <span aria-hidden className="grid size-6 shrink-0 place-items-center rounded-full bg-surface-2 text-[11px] font-bold">ش</span>
                    <span className="min-w-0">
                        <span className="block font-medium">شاپ مجازی</span>
                        <span dir="ltr" className="latin block truncate text-end text-[12px] text-muted">
                            {host}
                            {path}
                        </span>
                    </span>
                </p>
                <p className={`mt-2 line-clamp-1 text-[17px] leading-snug ${shownTitle ? 'text-indigo' : 'text-muted'}`}>{shownTitle || 'عنوان خود صفحه'}</p>
                <p className={`mt-1 line-clamp-2 text-[13px] leading-6 ${shownDesc ? 'text-body' : 'text-muted'}`}>{shownDesc || 'توضیح خود صفحه'}</p>
            </div>
        </figure>
    );
}

/** Roughly how a shared link shows in Telegram or WhatsApp. */
function SharePreview({ host, title, description, image, fallback }: { host: string; title: string; description: string; image: string; fallback: Fallback }) {
    return (
        <figure className="m-0">
            <figcaption className="mb-2 text-[13px] font-bold text-muted">اشتراک‌گذاری لینک</figcaption>
            <div className="overflow-hidden rounded-xl border border-stroke">
                <div className="grid aspect-[1200/630] place-items-center bg-surface-2">
                    {image ? (
                        <img src={image} alt="" className="size-full object-cover" />
                    ) : (
                        <span className="flex flex-col items-center gap-1.5 text-[13px] text-muted">
                            <ImageOff aria-hidden size={22} />
                            تصویر پیش‌فرض سایت
                        </span>
                    )}
                </div>
                <div className="border-t border-stroke p-3">
                    <p dir="ltr" className="latin text-end text-[12px] text-muted">
                        {host}
                    </p>
                    <p className="mt-0.5 line-clamp-1 font-bold">{title.trim() || fallback?.title || 'عنوان خود صفحه'}</p>
                    <p className="line-clamp-2 text-[13px] text-body">{description.trim() || fallback?.description || 'توضیح خود صفحه'}</p>
                </div>
            </div>
        </figure>
    );
}
