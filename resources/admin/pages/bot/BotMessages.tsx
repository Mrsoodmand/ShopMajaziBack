import { Link } from 'react-router';
import { Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { ErrorState, Pager, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';
import { BotMessageBadge, type BotMessageStatus } from './parts';

type MessageRow = { id: number; title: string; preview: string; status: BotMessageStatus; createdAt: string | null };
type Progress = { id: number; sent: number; total: number };

export default function BotMessages() {
    const list = useListParams({});
    const { data, error, loading, reload } = useQuery<{ page: number; lastPage: number; total: number; progress: Progress | null; messages: MessageRow[] }>(
        `/admin/api/bot/messages?${list.query}`,
    );

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="اطلاعیه‌های ربات"
                subtitle="پیامی که ربات تلگرام برای همه کاربرانش می‌فرستد. اطلاعیه‌های صف به ترتیب و دسته‌دسته ارسال می‌شوند."
                actions={
                    <Link to="/bot/messages/new" className="btn btn-primary">
                        <Plus aria-hidden size={16} />
                        اطلاعیه جدید
                    </Link>
                }
            />
            {data?.progress && <SendingBar progress={data.progress} />}
            <section aria-label="فهرست اطلاعیه‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={5} />
                ) : data.messages.length === 0 ? (
                    <EmptyState action={<Link to="/bot/messages/new" className="btn btn-primary">اطلاعیه جدید</Link>}>هنوز اطلاعیه‌ای نوشته نشده است.</EmptyState>
                ) : (
                    <ul className="divide-y divide-stroke">
                        {data.messages.map((m) => (
                            <li key={m.id}>
                                <Link to={`/bot/messages/${m.id}`} className="flex flex-wrap items-start gap-x-4 gap-y-1 px-5 py-3.5 hover:bg-surface-2">
                                    <span className="min-w-0 flex-1">
                                        <span className="block font-bold">{m.title}</span>
                                        <span dir="auto" className="mt-0.5 line-clamp-2 block text-[13px] text-muted">{m.preview}</span>
                                    </span>
                                    <span className="flex shrink-0 flex-col items-end gap-1">
                                        <BotMessageBadge status={m.status} />
                                        <span dir="ltr" className="num text-[12px] text-muted">{m.createdAt}</span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="اطلاعیه" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}

export function SendingBar({ progress }: { progress: { sent: number; total: number; id?: number } }) {
    const pct = progress.total ? Math.min(100, Math.round((progress.sent / progress.total) * 100)) : 0;
    return (
        <div className="mb-4 rounded-2xl border border-stroke bg-surface px-4 py-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="font-medium">
                    {progress.id ? (
                        <>
                            در حال ارسال <Link to={`/bot/messages/${progress.id}`} className="num underline-offset-4 hover:underline">اطلاعیه #{progress.id}</Link>
                        </>
                    ) : (
                        'در حال ارسال'
                    )}
                </p>
                <p className="text-[13px] text-muted">
                    <span className="num">{count(progress.sent)}</span> از <span className="num">{count(progress.total)}</span> کاربر
                </p>
            </div>
            <div className="mt-2 h-2 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-valuemin={0} aria-valuemax={100} aria-valuenow={pct} aria-label="پیشرفت ارسال">
                <div className="h-full rounded-full bg-primary" style={{ width: `${pct}%` }} />
            </div>
        </div>
    );
}
