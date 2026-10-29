import { useEffect, useState } from 'react';
import { useLocation, useNavigate, useParams } from 'react-router';
import { ExternalLink, Trash2 } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, slugify } from '../../components/form';
import { RichText } from '../../components/RichText';
import { ErrorState, Panel } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, FaqEditor, problemsFrom, type FaqRow } from '../../components/list';
import { jalali } from '../transactions/dates';
import { KINDS, type PostKind, type PostStatus } from './PostsList';

type Post = {
    title: string;
    slug: string;
    status: PostStatus;
    short: string;
    content: string;
    image: string;
    imageBig: string;
    metaTitle: string;
    metaDesc: string;
    date: string;
    time: string;
    faq: FaqRow[];
    views?: number;
};

function blank(): Post {
    const now = new Date();
    const time = new Intl.DateTimeFormat('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Tehran' }).format(now);
    return { title: '', slug: '', status: 'draft', short: '', content: '', image: '', imageBig: '', metaTitle: '', metaDesc: '', date: jalali(now), time, faq: [] };
}

const labels: Record<string, string> = {
    title: 'عنوان', slug: 'نامک', status: 'وضعیت', short: 'خلاصه', content: 'متن', image: 'تصویر شاخص', imageBig: 'تصویر بزرگ',
    metaTitle: 'عنوان سئو', metaDesc: 'توضیح سئو', date: 'تاریخ انتشار', time: 'ساعت انتشار', faq: 'سؤالات متداول',
};

const statuses: { id: PostStatus; label: string; hint: string }[] = [
    { id: 'published', label: 'منتشر شده', hint: 'در سایت دیده می‌شود.' },
    { id: 'draft', label: 'پیش‌نویس', hint: 'ذخیره می‌شود ولی در سایت نیست.' },
    { id: 'hidden', label: 'پنهان', hint: 'از سایت برداشته می‌شود.' },
];

export default function PostEditor({ kind }: { kind: PostKind }) {
    const k = KINDS[kind];
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const location = useLocation();
    const back = k.base + ((location.state as { list?: string } | null)?.list ?? '');
    const existing = useQuery<{ post: Post & { id: number } }>(isNew ? null : `/admin/api/posts/${kind}/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Post>(blank);
    const [slugTouched, setSlugTouched] = useState(!isNew);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    useEffect(() => {
        if (existing.data) setForm({ ...blank(), ...existing.data.post });
    }, [existing.data]);

    if (existing.error) return <ErrorState error={existing.error} onRetry={existing.reload} boxed />;
    if (!isNew && !existing.data) return <EditorSkeleton />;

    const set = (patch: Partial<Post>) => {
        save.clearSaved();
        setForm((f) => ({ ...f, ...patch }));
    };
    const err = (key: string) => save.errors[key];
    const live = !isNew && existing.data?.post.status === 'published';

    async function submit() {
        const res = await save.run<{ success: string; id: number }>(isNew ? `/admin/api/posts/${kind}` : `/admin/api/posts/${kind}/${id}`, form);
        if (res && isNew) navigate(`${k.base}/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`/admin/api/posts/${kind}/${id}/delete`, save.csrfToken, {});
            navigate(back, { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
            setDeleting(false);
        }
    }

    return (
        <EditorLayout
            backTo={back}
            backLabel={k.title}
            title={isNew ? `${k.noun} تازه` : form.title || `ویرایش ${k.noun}`}
            subtitle={!isNew && existing.data?.post.views != null ? <><span className="num">{existing.data.post.views}</span> بازدید</> : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? `ذخیره ${k.noun}` : 'ذخیره تغییرات'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-4">
                    <fieldset className="m-0 border-0 p-0">
                        <legend className="mb-2 font-medium">وضعیت</legend>
                        <div className="space-y-1.5">
                            {statuses.map((s) => (
                                <label key={s.id} className={`flex cursor-pointer gap-2.5 rounded-xl border-2 px-3 py-2 ${form.status === s.id ? 'border-primary' : 'border-stroke hover:border-field'}`}>
                                    <input type="radio" name="post-status" className="mt-1 size-4 accent-[var(--primary)]" checked={form.status === s.id} onChange={() => set({ status: s.id })} />
                                    <span>
                                        <span className="block font-bold">{s.label}</span>
                                        <span className="block text-[12px] text-muted">{s.hint}</span>
                                    </span>
                                </label>
                            ))}
                        </div>
                    </fieldset>
                    <div className="grid grid-cols-[1fr_88px] gap-2">
                        <Field label="تاریخ انتشار" error={err('date')}>
                            {(p) => <input {...p} dir="ltr" className="field num" value={form.date} onChange={(e) => set({ date: e.target.value })} placeholder="1404/08/07" />}
                        </Field>
                        <Field label="ساعت" error={err('time')}>
                            {(p) => <input {...p} dir="ltr" className="field num" value={form.time} onChange={(e) => set({ time: e.target.value })} placeholder="10:30" />}
                        </Field>
                    </div>
                    {live && form.slug && (
                        <a href={k.site + form.slug} target="_blank" rel="noreferrer" className="flex items-center gap-1.5 text-[13px] font-medium text-body underline-offset-4 hover:underline">
                            <ExternalLink aria-hidden size={14} />
                            دیدن در سایت
                        </a>
                    )}
                    {!isNew && (
                        <div className="border-t border-stroke pt-4">
                            <ConfirmButton
                                label={`حذف ${k.noun}`}
                                confirmLabel="بله، حذف شود"
                                question="برای همیشه حذف شود؟"
                                icon={<Trash2 aria-hidden size={16} />}
                                busy={deleting}
                                className="btn btn-secondary w-full text-error"
                                onConfirm={remove}
                            />
                            {deleteError && <p role="alert" className="mt-2 text-[13px] font-bold text-error">{deleteError}</p>}
                        </div>
                    )}
                </div>
            }
        >
            <Panel title="نوشته">
                <div className="grid gap-4">
                    <Field label="عنوان" error={err('title')}>
                        {(p) => <input {...p} className="field text-[16px] font-bold" value={form.title} onChange={(e) => set({ title: e.target.value })} />}
                    </Field>
                    <Field label="نامک (آدرس)" error={err('slug')} hint={<span dir="ltr" className="latin">{k.site}{form.slug || 'my-post'}</span>}>
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.slug}
                                onChange={(e) => {
                                    setSlugTouched(true);
                                    set({ slug: e.target.value.toLowerCase().replace(/\s+/g, '-') });
                                }}
                                onBlur={() => !slugTouched || set({ slug: slugify(form.slug) })}
                                placeholder="virtual-number-guide"
                            />
                        )}
                    </Field>
                    <Field label="خلاصه" error={err('short')} hint="در فهرست نوشته‌ها و بالای صفحه نمایش داده می‌شود.">
                        {(p) => <textarea {...p} rows={3} className="field" value={form.short} onChange={(e) => set({ short: e.target.value })} />}
                    </Field>
                    <RichText label="متن" value={form.content} onChange={(content) => set({ content })} error={err('content')} height={560} />
                </div>
            </Panel>

            <Panel title="تصویرها">
                <div className="grid gap-5 sm:grid-cols-2">
                    <ImageField label="تصویر شاخص" value={form.image} onChange={(image) => set({ image })} error={err('image')} uploadUrl="/admin/api/uploads/post-image" noSvg />
                    <ImageField
                        label="تصویر بزرگ (اختیاری)"
                        value={form.imageBig}
                        onChange={(imageBig) => set({ imageBig })}
                        error={err('imageBig')}
                        uploadUrl="/admin/api/uploads/post-image"
                        noSvg
                        hint="خالی بماند، همان تصویر شاخص استفاده می‌شود."
                    />
                </div>
            </Panel>

            {kind === 'blog' && (
                <Panel title="سؤالات متداول">
                    <FaqEditor rows={form.faq} onChange={(faq) => set({ faq })} errors={save.errors} />
                </Panel>
            )}

            <Panel title="سئو">
                <div className="grid gap-4">
                    <Field label="عنوان سئو" error={err('metaTitle')} hint={<span className="num">{form.metaTitle.length} نویسه؛ تا حدود ۶۰ نویسه در گوگل کامل دیده می‌شود.</span>}>
                        {(p) => <input {...p} className="field" value={form.metaTitle} onChange={(e) => set({ metaTitle: e.target.value })} />}
                    </Field>
                    <Field label="توضیح سئو" error={err('metaDesc')} hint={<span className="num">{form.metaDesc.length} نویسه؛ ۱۲۰ تا ۱۶۰ نویسه مناسب است.</span>}>
                        {(p) => <textarea {...p} rows={3} className="field" value={form.metaDesc} onChange={(e) => set({ metaDesc: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
