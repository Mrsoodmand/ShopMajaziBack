import { Link } from 'react-router';
import { AppWindow, CircleCheck, CircleSlash, Plus, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { Badge } from '../../components/Status';
import { ErrorState, SearchField, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader, useListParams } from '../../components/list';

type AccountRow = {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    live: boolean;
    currency: 'dollar' | 'toman';
    plans: number;
    livePlans: number;
    updatedAt: string | null;
};

export default function AccountsList() {
    const list = useListParams({ q: '' });
    const { data, error, loading, reload } = useQuery<{ accounts: AccountRow[] }>(`/admin/api/accounts?q=${encodeURIComponent(list.values.q)}`);

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="اکانت‌های خارجی"
                subtitle="اشتراک‌ها و اکانت سایت‌های خارجی، با پلن‌ها و فیلدهایی که خریدار پر می‌کند."
                actions={
                    <>
                        <Link to="/orders?type=7&view=all" className="btn btn-secondary">
                            سفارش‌ها
                        </Link>
                        <Link to="/pages/7" className="btn btn-secondary">
                            <Settings2 aria-hidden size={16} />
                            متن صفحه
                        </Link>
                        <Link to="/accounts/new" className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            اکانت جدید
                        </Link>
                    </>
                }
            />
            <div className="mb-4 flex">
                <SearchField value={list.values.q} onSearch={(q) => list.update({ q })} label="جستجوی اکانت" placeholder="نام یا نامک" />
            </div>
            <section aria-label="فهرست اکانت‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={5} />
                ) : data.accounts.length === 0 ? (
                    <EmptyState action={<Link to="/accounts/new" className="btn btn-primary">اکانت جدید</Link>}>
                        {list.values.q ? `اکانتی با «${list.values.q}» پیدا نشد.` : 'هنوز اکانتی ساخته نشده است.'}
                    </EmptyState>
                ) : (
                    <ul className={`divide-y divide-stroke transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        {data.accounts.map((a) => (
                            <li key={a.id}>
                                <Link to={`/accounts/${a.id}`} className="flex items-center gap-4 px-5 py-3.5 hover:bg-surface-2">
                                    <span className="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-stroke bg-surface-2">
                                        {a.image ? <img src={a.image} alt="" className="size-full object-cover" /> : <AppWindow aria-hidden size={20} className="text-muted" />}
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate font-bold">{a.name}</span>
                                        <span dir="ltr" className="latin block truncate text-end text-[12px] text-muted sm:text-start">
                                            /accounts/{a.slug}
                                        </span>
                                    </span>
                                    <span className="hidden text-[13px] text-muted sm:block">
                                        <span className="num">{a.livePlans}</span> پلن فعال از <span className="num">{a.plans}</span> · {a.currency === 'dollar' ? 'دلاری' : 'تومانی'}
                                    </span>
                                    {a.live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />}
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </div>
    );
}
