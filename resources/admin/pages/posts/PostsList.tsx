import { Link } from 'react-router';
import { CircleCheck, ExternalLink, EyeOff, FileText, PencilLine, Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Badge } from '../../components/Status';
import { FilterTabs, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, isFiltered, type Column, type FilterDef } from '../../components/DataList';

export type PostKind = 'blog' | 'news';
export type PostStatus = 'published' | 'draft' | 'hidden';

export const KINDS: Record<PostKind, { title: string; noun: string; base: string; site: string }> = {
    blog: { title: 'بلاگ', noun: 'نوشته', base: '/blog', site: '/blog/' },
    news: { title: 'اخبار', noun: 'خبر', base: '/news', site: '/tech-news/' },
};

type PostRow = { id: number; title: string; slug: string; image: string | null; status: PostStatus; views: number; comments: number; publishedAt: string | null; updatedAt: string | null };
type PostsPage = { page: number; lastPage: number; total: number; perPage: number; counts: Record<string, number>; posts: PostRow[] };

const views = [
    { id: 'all', label: 'همه' },
    { id: 'published', label: 'منتشرشده' },
    { id: 'draft', label: 'پیش‌نویس' },
    { id: 'hidden', label: 'پنهان' },
];

const filters: FilterDef[] = [
    { type: 'dates', label: 'تاریخ ساخت' },
    { type: 'toggle', key: 'image', label: 'فقط با تصویر شاخص' },
];

const sorts = [
    { value: '', label: 'جدیدترین' },
    { value: 'oldest', label: 'قدیمی‌ترین' },
    { value: 'views', label: 'پربازدیدترین' },
    { value: 'updated', label: 'آخرین ویرایش' },
];

export function PostBadge({ status }: { status: PostStatus }) {
    if (status === 'published') return <Badge tone="success" icon={CircleCheck} label="منتشر شده" />;
    if (status === 'draft') return <Badge tone="warn" icon={PencilLine} label="پیش‌نویس" />;
    return <Badge tone="neutral" icon={EyeOff} label="پنهان" />;
}

function Cover({ src, large }: { src: string | null; large?: boolean }) {
    return (
        <span className={`grid shrink-0 place-items-center overflow-hidden rounded-lg bg-surface-2 ${large ? 'aspect-video w-full' : 'h-11 w-[72px]'}`}>
            {src ? <img src={src} alt="" loading="lazy" className="size-full object-cover" /> : <FileText aria-hidden size={18} className="text-muted" />}
        </span>
    );
}

export default function PostsList({ kind }: { kind: PostKind }) {
    const k = KINDS[kind];
    const list = useListParams({ status: 'all', q: '', sort: '', per: '', from: '', to: '', image: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<PostsPage>(`/admin/api/posts/${kind}?${list.query}`);

    const columns: Column<PostRow>[] = [
        {
            key: 'title',
            header: 'عنوان',
            primary: true,
            cell: (p) => (
                <span className="flex items-center gap-3">
                    <Cover src={p.image} />
                    <span className="min-w-0">
                        <span className="block max-w-[420px] truncate font-bold">{p.title}</span>
                        <span dir="ltr" className="latin block max-w-[420px] truncate text-end text-[12px] text-muted">
                            {k.site}
                            {p.slug}
                        </span>
                    </span>
                </span>
            ),
        },
        { key: 'status', header: 'وضعیت', cell: (p) => <PostBadge status={p.status} /> },
        { key: 'views', header: 'بازدید', align: 'end', cell: (p) => <span className="num">{count(p.views)}</span> },
        { key: 'comments', header: 'نظر', align: 'end', from: 'lg', cell: (p) => <span className={`num ${p.comments ? '' : 'text-muted'}`}>{count(p.comments)}</span> },
        { key: 'date', header: 'انتشار', cell: (p) => <span dir="ltr" className="num text-[13px] whitespace-nowrap text-muted">{p.publishedAt}</span> },
    ];

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
            <FilterBar
                views={<FilterTabs label={`فیلتر ${k.title}`} items={views} value={v.status} counts={data?.counts} onChange={(status) => list.update({ status })} />}
                search={{ label: `جستجوی ${k.noun}`, placeholder: 'عنوان یا نامک' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={data?.total}
                noun={k.noun}
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                label={`فهرست ${k.title}`}
                rows={data?.posts}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(p) => p.id}
                href={(p) => `${k.base}/${p.id}`}
                linkState={{ list: list.here }}
                rowLabel={(p) => `${k.noun} ${p.title}`}
                rowTone={(p) => (p.status === 'hidden' ? 'muted' : undefined)}
                quick={{
                    title: (p) => p.title,
                    subtitle: (p) => <PostBadge status={p.status} />,
                    body: (p) => (
                        <>
                            <div className="mb-4">
                                <Cover src={p.image} large />
                            </div>
                            <QuickSection
                                title="نوشته"
                                action={
                                    p.status === 'published' ? (
                                        <a href={`${k.site}${p.slug}`} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-[13px] font-medium text-indigo hover:underline">
                                            <ExternalLink aria-hidden size={13} />
                                            دیدن در سایت
                                        </a>
                                    ) : undefined
                                }
                            >
                                <dl className="m-0">
                                    <Row label="نامک" ltr>
                                        <span className="latin">{p.slug}</span>
                                    </Row>
                                    <Row label="بازدید" num>
                                        {count(p.views)}
                                    </Row>
                                    <Row label="نظرها" num>
                                        {count(p.comments)}
                                    </Row>
                                    <Row label="انتشار" ltr>
                                        {p.publishedAt ?? '—'}
                                    </Row>
                                    <Row label="آخرین ویرایش" ltr>
                                        {p.updatedAt ?? '—'}
                                    </Row>
                                </dl>
                            </QuickSection>
                            {p.comments > 0 && (
                                <Link to="/comments?type=blog&status=all" className="text-[13px] font-medium text-indigo underline-offset-4 hover:underline">
                                    نظرهای بلاگ
                                </Link>
                            )}
                        </>
                    ),
                }}
                mobile={(p) => (
                    <span className="flex items-center gap-3">
                        <Cover src={p.image} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{p.title}</span>
                            <span className="mt-0.5 flex flex-wrap gap-x-3 text-[12px] text-muted">
                                <span dir="ltr" className="num">
                                    {p.publishedAt}
                                </span>
                                <span>
                                    <span className="num">{count(p.views)}</span> بازدید
                                </span>
                            </span>
                        </span>
                        <PostBadge status={p.status} />
                    </span>
                )}
                empty={
                    <ListEmpty
                        filtered={isFiltered(v, ['status'])}
                        onClear={() => list.update({ q: '', from: '', to: '', image: '' })}
                        action={
                            <Link to={`${k.base}/new`} className="btn btn-primary">
                                {k.noun} تازه
                            </Link>
                        }
                    >
                        {k.noun}ای در این فهرست نیست.
                    </ListEmpty>
                }
            />
            {data && (
                <ListPager
                    page={data.page}
                    lastPage={data.lastPage}
                    total={data.total}
                    perPage={data.perPage}
                    noun={k.noun}
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(n) => list.update({ per: String(n) })}
                />
            )}
        </div>
    );
}
