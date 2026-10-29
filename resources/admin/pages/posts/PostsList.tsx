import { Link } from 'react-router';
import { CircleCheck, EyeOff, FileText, PencilLine, Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Badge } from '../../components/Status';
import { ErrorState, FilterTabs, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';

export type PostKind = 'blog' | 'news';
export type PostStatus = 'published' | 'draft' | 'hidden';

export const KINDS: Record<PostKind, { title: string; noun: string; base: string; site: string }> = {
    blog: { title: 'بلاگ', noun: 'نوشته', base: '/blog', site: '/blog/' },
    news: { title: 'اخبار', noun: 'خبر', base: '/news', site: '/tech-news/' },
};

type PostRow = { id: number; title: string; slug: string; image: string | null; status: PostStatus; views: number; comments: number; publishedAt: string | null; updatedAt: string | null };
type PostsPage = { page: number; lastPage: number; total: number; counts: Record<string, number>; posts: PostRow[] };

const filters = [
    { id: 'all', label: 'همه' },
    { id: 'published', label: 'منتشرشده' },
    { id: 'draft', label: 'پیش‌نویس' },
    { id: 'hidden', label: 'پنهان' },
];

export function PostBadge({ status }: { status: PostStatus }) {
    if (status === 'published') return <Badge tone="success" icon={CircleCheck} label="منتشر شده" />;
    if (status === 'draft') return <Badge tone="warn" icon={PencilLine} label="پیش‌نویس" />;
    return <Badge tone="neutral" icon={EyeOff} label="پنهان" />;
}

export default function PostsList({ kind }: { kind: PostKind }) {
    const k = KINDS[kind];
    const list = useListParams({ status: 'all', q: '' });
    const { data, error, loading, reload } = useQuery<PostsPage>(`/admin/api/posts/${kind}?${list.query}`);

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={k.title}
                subtitle="فقط نوشته‌های «منتشر شده» در سایت دیده می‌شوند."
                actions={
                    <Link to={`${k.base}/new`} className="btn btn-primary">
                        <Plus aria-hidden size={16} />
                        {k.noun} تازه
                    </Link>
                }
            />
            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label={`فیلتر ${k.title}`} items={filters} value={list.values.status} counts={data?.counts} onChange={(status) => list.update({ status })} />
                <div className="flex flex-1 xl:justify-end">
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label={`جستجوی ${k.noun}`} placeholder="عنوان یا نامک" />
                </div>
            </div>
            <section aria-label={`فهرست ${k.title}`} aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.posts.length === 0 ? (
                    <EmptyState action={<Link to={`${k.base}/new`} className="btn btn-primary">{k.noun} تازه</Link>}>
                        {list.values.q ? `${k.noun}ای با «${list.values.q}» پیدا نشد.` : `${k.noun}ای در این فهرست نیست.`}
                    </EmptyState>
                ) : (
                    <ul className={`divide-y divide-stroke transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        {data.posts.map((p) => (
                            <li key={p.id}>
                                <Link to={`${k.base}/${p.id}`} state={{ list: list.here }} className="flex items-center gap-4 px-5 py-3 hover:bg-surface-2">
                                    <span className="grid h-12 w-20 shrink-0 place-items-center overflow-hidden rounded-lg bg-surface-2">
                                        {p.image ? <img src={p.image} alt="" loading="lazy" className="size-full object-cover" /> : <FileText aria-hidden size={18} className="text-muted" />}
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate font-bold">{p.title}</span>
                                        <span className="mt-0.5 flex flex-wrap gap-x-3 text-[12px] text-muted">
                                            <span dir="ltr" className="num">{p.publishedAt}</span>
                                            <span><span className="num">{count(p.views)}</span> بازدید</span>
                                            {p.comments > 0 && <span><span className="num">{p.comments}</span> نظر</span>}
                                        </span>
                                    </span>
                                    <PostBadge status={p.status} />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun={k.noun} onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
