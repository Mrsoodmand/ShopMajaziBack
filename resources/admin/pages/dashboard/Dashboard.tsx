import { useQuery } from '../../lib/api';
import { ErrorState } from '../../components/ui';
import type { Dashboard as Data } from './types';
import { Block, ByService, CommentList, DashboardSkeleton, DaysChart, MismatchList, Pulse, QueueCounts, QueueList, TicketList } from './parts';

/**
 * Admin home. What needs a person comes first (the order queue, oldest first, with tickets, payment mismatches and
 * comments beside it); how the shop is doing (today, last 14 days, sales by service) sits around it.
 */
export default function Dashboard() {
    const { data, error, reload } = useQuery<Data>('/admin/api/dashboard');

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5">
                <h1 className="text-[22px] leading-tight font-bold">داشبورد</h1>
                <p className="mt-1 text-muted">{data?.today ?? ' '}</p>
            </div>

            {error ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !data ? (
                <DashboardSkeleton />
            ) : (
                <div className="space-y-5">
                    <Pulse data={data} />
                    <div className="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1fr)_380px]">
                        <div className="space-y-5">
                            {data.queue && (
                                <Block title="منتظر اقدام" total={data.queue.total} more={{ to: '/orders', label: 'صف سفارش‌ها' }} flush>
                                    <QueueCounts queue={data.queue} />
                                    <QueueList orders={data.queue.oldest} />
                                </Block>
                            )}
                            {data.days && (
                                <Block title="۱۴ روز گذشته">
                                    <DaysChart days={data.days} />
                                </Block>
                            )}
                        </div>
                        <div className="space-y-5">
                            {data.tickets && (
                                <Block title="تیکت‌های باز" total={data.tickets.total} more={{ to: '/tickets', label: 'همه تیکت‌ها' }} flush>
                                    <TicketList items={data.tickets.items.slice(0, 4)} itemLink="/tickets/" />
                                </Block>
                            )}
                            {data.mismatches && (
                                <Block title="مغایرت پرداخت" total={data.mismatches.total} more={{ to: '/transactions', label: 'صف بررسی' }} flush>
                                    <MismatchList items={data.mismatches.items.slice(0, 3)} link="/transactions" />
                                </Block>
                            )}
                            {data.comments && (
                                <Block title="نظرهای منتظر تأیید" total={data.comments.total} more={{ to: '/comments', label: 'صف نظرها' }} flush>
                                    <CommentList items={data.comments.items.slice(0, 3)} link="/comments" />
                                </Block>
                            )}
                            {data.byType && (
                                <Block title="فروش ۳۰ روز، به تفکیک سرویس">
                                    <ByService rows={data.byType} />
                                </Block>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
