import { useEffect, useState } from 'react';
import { Check, CircleCheck, CircleX, Clock, Link2, MessageSquareReply, Trash2, X } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { Badge } from '../../components/Status';
import { ErrorState, FieldError, FilterTabs, Notice, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { ConfirmButton, EmptyState, PageHeader, useListParams } from '../../components/list';

type Status = 'pending' | 'approved' | 'rejected';
type Comment = {
    id: number;
    name: string;
    mobile: string | null;
    text: string;
    status: Status;
    type: string;
    about: string | null;
    aboutPath: string | null;
    hasLink: boolean;
    createdAt: string | null;
    reply: { text: string; name: string; createdAt: string | null } | null;
};
type CommentsPage = { page: number; lastPage: number; total: number; counts: Record<string, number>; comments: Comment[] };

const filters = [
    { id: 'pending', label: 'در انتظار بررسی' },
    { id: 'approved', label: 'تأییدشده' },
    { id: 'rejected', label: 'ردشده' },
    { id: 'all', label: 'همه' },
];
const types = [
    { id: '', label: 'همه بخش‌ها' },
    { id: 'blog', label: 'بلاگ و اخبار' },
    { id: 'page', label: 'صفحه‌های خدمات' },
    { id: 'service', label: 'خدمات مجازی' },
];

function StatusBadge({ status }: { status: Status }) {
    if (status === 'approved') return <Badge tone="success" icon={CircleCheck} label="تأیید شده" />;
    if (status === 'rejected') return <Badge tone="danger" icon={CircleX} label="رد شده" />;
    return <Badge tone="info" icon={Clock} label="در انتظار" />;
}

export default function CommentsPage() {
    const { csrfToken } = useBoot();
    const list = useListParams({ status: 'pending', type: '', q: '' });
    const { data, error, loading, reload } = useQuery<CommentsPage>(`/admin/api/comments?${list.query}`);
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [notice, setNotice] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);

    // A new page of results starts with nothing selected.
    useEffect(() => setSelected(new Set()), [list.query]);

    async function act(url: string, payload: unknown) {
        setBusy(true);
        try {
            const res = await postJson<{ success: string }>(url, csrfToken, payload);
            setNotice(res.success);
            setSelected(new Set());
            reload();
            return true;
        } catch (e) {
            setNotice((e as ApiError).message);
            return false;
        } finally {
            setBusy(false);
        }
    }

    const rows = data?.comments ?? [];
    const allOnPage = rows.length > 0 && rows.every((c) => selected.has(c.id));
    const toggle = (id: number) => {
        const next = new Set(selected);
        if (next.has(id)) next.delete(id);
        else next.add(id);
        setSelected(next);
    };
    const bulk = (action: string) => act('/admin/api/comments/bulk', { ids: [...selected], action });

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="نظرها" subtitle="نظرهای تأییدشده در سایت دیده می‌شوند. صف بررسی از قدیمی‌ترین شروع می‌شود." />

            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label="وضعیت نظر" items={filters} value={list.values.status} counts={data?.counts} attention="pending" onChange={(status) => list.update({ status })} />
                <div className="flex flex-1 flex-wrap gap-2 xl:justify-end">
                    <label htmlFor="comment-type" className="sr-only">بخش سایت</label>
                    <select id="comment-type" className="field w-auto" value={list.values.type} onChange={(e) => list.update({ type: e.target.value })}>
                        {types.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.label}
                            </option>
                        ))}
                    </select>
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی نظر" placeholder="نام، موبایل یا متن" />
                </div>
            </div>

            {notice && (
                <div className="mb-4">
                    <Notice>{notice}</Notice>
                </div>
            )}

            {rows.length > 0 && (
                <div className="sticky top-14 z-[5] mb-3 flex flex-wrap items-center gap-2 rounded-2xl border border-stroke bg-surface/95 px-4 py-2.5 backdrop-blur">
                    <label className="flex cursor-pointer items-center gap-2 font-medium">
                        <input
                            type="checkbox"
                            className="size-4 accent-[var(--primary)]"
                            checked={allOnPage}
                            onChange={() => setSelected(allOnPage ? new Set() : new Set(rows.map((c) => c.id)))}
                        />
                        {selected.size ? <span className="num">{selected.size} انتخاب‌شده</span> : 'انتخاب همه در این صفحه'}
                    </label>
                    {selected.size > 0 && (
                        <div className="ms-auto flex flex-wrap items-center gap-2">
                            <button type="button" className="btn btn-secondary" disabled={busy} onClick={() => bulk('approved')}>
                                <Check aria-hidden size={16} />
                                تأیید
                            </button>
                            <button type="button" className="btn btn-secondary" disabled={busy} onClick={() => bulk('rejected')}>
                                <X aria-hidden size={16} />
                                رد
                            </button>
                            <ConfirmButton
                                label="حذف"
                                confirmLabel="حذف همیشگی"
                                question={`${selected.size} نظر حذف شود؟`}
                                icon={<Trash2 aria-hidden size={16} />}
                                busy={busy}
                                className="btn btn-secondary text-error"
                                onConfirm={() => bulk('delete')}
                            />
                        </div>
                    )}
                </div>
            )}

            <section aria-label="فهرست نظرها" aria-busy={loading}>
                {error ? (
                    <ErrorState error={error} onRetry={reload} boxed />
                ) : !data ? (
                    <div className="rounded-2xl border border-stroke bg-surface">
                        <SkeletonRows />
                    </div>
                ) : rows.length === 0 ? (
                    <div className="rounded-2xl border border-stroke bg-surface">
                        <EmptyState>{list.values.status === 'pending' && !list.values.q ? 'نظری منتظر بررسی نیست.' : 'نظری در این فهرست نیست.'}</EmptyState>
                    </div>
                ) : (
                    <ul className={`space-y-3 transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        {rows.map((c) => (
                            <CommentCard key={c.id} comment={c} selected={selected.has(c.id)} onToggle={() => toggle(c.id)} busy={busy} act={act} />
                        ))}
                    </ul>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="نظر" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}

function CommentCard({
    comment: c,
    selected,
    onToggle,
    busy,
    act,
}: {
    comment: Comment;
    selected: boolean;
    onToggle: () => void;
    busy: boolean;
    act: (url: string, payload: unknown) => Promise<boolean>;
}) {
    const [replying, setReplying] = useState(false);
    const [reply, setReply] = useState(c.reply?.text ?? '');
    const [replyError, setReplyError] = useState<string | null>(null);

    async function sendReply(approve: boolean) {
        if (!reply.trim()) {
            setReplyError('متن پاسخ را بنویسید.');
            return;
        }
        setReplyError(null);
        if (await act(`/admin/api/comments/${c.id}/reply`, { text: reply, approve })) setReplying(false);
    }

    return (
        <li className={`rounded-2xl border bg-surface p-4 sm:p-5 ${selected ? 'border-primary' : 'border-stroke'}`}>
            <div className="flex gap-3">
                <input type="checkbox" className="mt-1 size-4 shrink-0 accent-[var(--primary)]" checked={selected} onChange={onToggle} aria-label={`انتخاب نظر ${c.name}`} />
                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                        <div className="min-w-0">
                            <p className="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span className="font-bold">{c.name || 'بی‌نام'}</span>
                                {c.mobile && <span dir="ltr" className="num text-[13px] text-muted">{c.mobile}</span>}
                                {c.hasLink && (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-warn-soft px-2 py-0.5 text-[12px] font-medium text-warn">
                                        <Link2 aria-hidden size={12} />
                                        دارای لینک
                                    </span>
                                )}
                            </p>
                            <p className="mt-0.5 text-[13px] text-muted">
                                درباره{' '}
                                {c.aboutPath ? (
                                    <a href={c.aboutPath} target="_blank" rel="noreferrer" className="font-medium text-body underline-offset-4 hover:underline">{c.about}</a>
                                ) : (
                                    <span className="font-medium text-body">{c.about ?? 'صفحه‌ای که دیگر نیست'}</span>
                                )}{' '}
                                · <span dir="ltr" className="num">{c.createdAt}</span>
                            </p>
                        </div>
                        <StatusBadge status={c.status} />
                    </div>

                    {/* Plain text: comments are not trusted HTML. */}
                    <p dir="auto" className="mt-3 break-words whitespace-pre-wrap">{c.text}</p>

                    {c.reply && !replying && (
                        <div className="mt-3 rounded-xl bg-surface-2 px-4 py-2.5">
                            <p className="text-[12px] text-muted">
                                پاسخ {c.reply.name} · <span dir="ltr" className="num">{c.reply.createdAt}</span>
                            </p>
                            <p dir="auto" className="mt-0.5 whitespace-pre-wrap">{c.reply.text}</p>
                        </div>
                    )}

                    {replying && (
                        <div className="mt-3">
                            <label htmlFor={`reply-${c.id}`} className="mb-1 block font-medium">
                                پاسخ فروشگاه
                            </label>
                            <textarea id={`reply-${c.id}`} rows={3} className="field" value={reply} onChange={(e) => setReply(e.target.value)} aria-invalid={replyError ? true : undefined} />
                            {replyError && <FieldError id={`reply-${c.id}-error`}>{replyError}</FieldError>}
                            <div className="mt-2 flex flex-wrap gap-2">
                                {c.status !== 'approved' && (
                                    <button type="button" className="btn btn-primary" disabled={busy} onClick={() => sendReply(true)}>
                                        ثبت پاسخ و تأیید نظر
                                    </button>
                                )}
                                <button type="button" className={c.status === 'approved' ? 'btn btn-primary' : 'btn btn-secondary'} disabled={busy} onClick={() => sendReply(false)}>
                                    ثبت پاسخ
                                </button>
                                <button type="button" className="btn btn-ghost" onClick={() => setReplying(false)}>
                                    انصراف
                                </button>
                            </div>
                        </div>
                    )}

                    {!replying && (
                        <div className="mt-3 flex flex-wrap gap-2">
                            {c.status !== 'approved' && (
                                <button type="button" className="btn btn-secondary min-h-9" disabled={busy} onClick={() => act(`/admin/api/comments/${c.id}/status`, { status: 'approved' })}>
                                    <Check aria-hidden size={16} />
                                    تأیید
                                </button>
                            )}
                            {c.status !== 'rejected' && (
                                <button type="button" className="btn btn-secondary min-h-9" disabled={busy} onClick={() => act(`/admin/api/comments/${c.id}/status`, { status: 'rejected' })}>
                                    <X aria-hidden size={16} />
                                    رد
                                </button>
                            )}
                            <button type="button" className="btn btn-ghost min-h-9" onClick={() => setReplying(true)}>
                                <MessageSquareReply aria-hidden size={16} />
                                {c.reply ? 'ویرایش پاسخ' : 'پاسخ'}
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </li>
    );
}
