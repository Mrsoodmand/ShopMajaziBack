import { Link } from 'react-router';
import { PanelsTopLeft, SearchCheck } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { ErrorState, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader } from '../../components/list';

type PageRow = { id: number; title: string; h1: string | null; path: string | null; faq: number; hasContent: boolean; updatedAt: string | null };

export default function PagesList() {
    const { data, error, reload } = useQuery<{ pages: PageRow[] }>('/admin/api/pages');

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="صفحه‌ها"
                subtitle="متن، تصویر و سؤالات متداول صفحه‌های اصلی خدمات. قیمت اپل آیدی و گوگل ویس هم در صفحه خودشان است."
                actions={
                    <Link to="/seo" className="btn btn-secondary">
                        <SearchCheck aria-hidden size={16} />
                        سئوی همه صفحه‌ها
                    </Link>
                }
            />
            <section aria-label="فهرست صفحه‌ها" className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={6} />
                ) : data.pages.length === 0 ? (
                    <EmptyState>صفحه‌ای نیست.</EmptyState>
                ) : (
                    <ul className="divide-y divide-stroke">
                        {data.pages.map((p) => (
                            <li key={p.id}>
                                <Link to={`/pages/${p.id}`} className="flex items-center gap-4 px-5 py-3.5 hover:bg-surface-2">
                                    <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-2">
                                        <PanelsTopLeft aria-hidden size={18} className="text-muted" />
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate font-bold">{p.title || `صفحه ${p.id}`}</span>
                                        <span className="block truncate text-[13px] text-muted">{p.h1 || 'بدون H1'}</span>
                                    </span>
                                    <span className="hidden text-end text-[12px] text-muted sm:block">
                                        {p.path && <span dir="ltr" className="latin block">{p.path}</span>}
                                        <span>
                                            {p.hasContent ? 'متن دارد' : 'بدون متن'} · <span className="num">{p.faq}</span> سؤال
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </div>
    );
}
