import { Link } from 'react-router';
import { useQuery } from '../../lib/api';
import { count, toman } from '../../lib/format';
import { ErrorState, FilterTabs, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';
import { botUserName } from './parts';

type BotUserRow = { id: number; telegramId: string; username: string; name: string; wallet: number; joinedAt: string | null; orders: number };

const filters = [
    { id: 'all', label: 'همه' },
    { id: 'buyers', label: 'خریداران' },
];

export default function BotUsers() {
    const list = useListParams({ filter: 'all', q: '' });
    const { data, error, loading, reload } = useQuery<{ page: number; lastPage: number; total: number; counts: Record<string, number>; users: BotUserRow[] }>(
        `/admin/api/bot/users?${list.query}`,
    );

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="کاربران ربات" subtitle="کسانی که ربات تلگرام را استارت کرده‌اند، با کیف پول و خریدهایشان در ربات." />
            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <FilterTabs label="نوع کاربر" items={filters} value={list.values.filter} counts={data?.counts} onChange={(filter) => list.update({ filter })} />
                <div className="flex flex-1 sm:justify-end">
                    <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی کاربر ربات" placeholder="یوزرنیم، نام یا آیدی تلگرام" />
                </div>
            </div>
            <section aria-label="فهرست کاربران ربات" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.users.length === 0 ? (
                    <EmptyState>{list.values.q ? `کاربری با «${list.values.q}» پیدا نشد.` : 'کاربری در این فهرست نیست.'}</EmptyState>
                ) : (
                    <div className={`overflow-x-auto transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="w-full min-w-[640px]">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">کاربر</th>
                                    <th scope="col" className="px-3 text-start font-medium">آیدی تلگرام</th>
                                    <th scope="col" className="px-3 text-end font-medium">کیف پول</th>
                                    <th scope="col" className="px-3 text-end font-medium">خرید</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">عضویت</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.users.map((u) => (
                                    <tr key={u.id} className="border-b border-stroke last:border-0 hover:bg-surface-2">
                                        <td className="py-3 ps-5 pe-3">
                                            <Link to={`/bot/users/${u.id}`} state={{ list: list.here }} dir="auto" className="font-bold underline-offset-4 hover:underline">
                                                {botUserName(u)}
                                            </Link>
                                            {u.username && u.name && u.name !== u.username && <span dir="auto" className="block text-[13px] text-muted">{u.name}</span>}
                                        </td>
                                        <td dir="ltr" className="num px-3 text-end text-[13px] text-muted">{u.telegramId}</td>
                                        <td className="num px-3 text-end">{toman(u.wallet)}</td>
                                        <td className="num px-3 text-end">{u.orders ? count(u.orders) : <span className="text-muted">—</span>}</td>
                                        <td dir="ltr" className="num py-3 ps-3 pe-5 text-end text-[13px] text-muted">{u.joinedAt}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="کاربر" onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
