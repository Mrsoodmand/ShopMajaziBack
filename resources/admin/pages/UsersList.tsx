import { Link, useNavigate, useSearchParams } from 'react-router';
import { Ban, SearchX } from 'lucide-react';
import { useQuery } from '../lib/api';
import { toman } from '../lib/format';
import { ErrorState, FilterTabs, Pager, SearchField, SkeletonRows } from '../components/ui';

export type UserFlags = { loginBlocked: boolean; walletBlocked: boolean; bankBlocked: boolean; accountBlocked: boolean };

type UserRow = {
    id: number;
    name: string | null;
    mobile: string | null;
    email: string | null;
    wallet: number;
    blockedWallet: number;
    orders: number;
    flags: UserFlags;
    createdAt: string | null;
};

type Filter = 'all' | 'balance' | 'blocked';

type UsersPage = { counts: Record<Filter, number>; page: number; lastPage: number; total: number; users: UserRow[] };

const filters: { id: Filter; label: string }[] = [
    { id: 'all', label: 'همه' },
    { id: 'balance', label: 'دارای موجودی' },
    { id: 'blocked', label: 'محدودشده' },
];

export function blockLabels(f: UserFlags): string[] {
    return [
        f.loginBlocked && 'ورود',
        f.walletBlocked && 'خرید با کیف پول',
        f.bankBlocked && 'خرید با درگاه',
        f.accountBlocked && 'حساب',
    ].filter(Boolean) as string[];
}

export function BlockedPill({ flags }: { flags: UserFlags }) {
    const blocks = blockLabels(flags);
    if (!blocks.length) return null;
    return (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-error-soft px-2.5 py-0.5 text-[13px] font-medium whitespace-nowrap text-error" title={`مسدود: ${blocks.join('، ')}`}>
            <Ban aria-hidden size={14} strokeWidth={2.25} />
            مسدود: {blocks.join('، ')}
        </span>
    );
}

export default function UsersList() {
    const [params, setParams] = useSearchParams();
    const navigate = useNavigate();
    const filter = (params.get('filter') as Filter) || 'all';
    const q = params.get('q') ?? '';
    const page = Number(params.get('page') ?? 1);
    const { data, error, loading, reload } = useQuery<UsersPage>(`/admin/api/users?${new URLSearchParams({ filter, q, page: String(page) })}`);
    const listHref = `?${params}`;

    function update(next: Record<string, string>) {
        const merged = new URLSearchParams(params);
        for (const [k, v] of Object.entries(next)) {
            if (v) merged.set(k, v);
            else merged.delete(k);
        }
        if (!('page' in next)) merged.delete('page');
        setParams(merged);
    }

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5">
                <h1 className="text-[22px] leading-tight font-bold">کاربران</h1>
                <p className="mt-1 text-muted">{filter === 'balance' ? 'بیشترین موجودی اول.' : 'جدیدترین ثبت‌نام‌ها اول.'}</p>
            </div>

            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label="فیلتر کاربران" items={filters} value={filter} counts={data?.counts} onChange={(id) => update({ filter: id })} />
                <div className="flex flex-1 gap-2 xl:justify-end">
                    <SearchField value={q} onSearch={(v) => update({ q: v })} label="جستجو با موبایل، نام یا ایمیل" placeholder="موبایل، نام یا ایمیل" />
                </div>
            </div>

            <section aria-label="لیست کاربران" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.users.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 px-6 py-16 text-center">
                        <SearchX aria-hidden size={28} className="text-muted" />
                        <p className="font-medium">{q ? `کاربری با «${q}» پیدا نشد.` : 'کاربری در این فهرست نیست.'}</p>
                        {(q || filter !== 'all') && (
                            <button type="button" className="btn btn-secondary mt-2" onClick={() => setParams(new URLSearchParams())}>
                                پاک کردن جستجو و فیلتر
                            </button>
                        )}
                    </div>
                ) : (
                    <div className={`transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="hidden w-full md:table">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">کاربر</th>
                                    <th scope="col" className="px-3 text-start font-medium">موبایل</th>
                                    <th scope="col" className="hidden px-3 text-start font-medium lg:table-cell">ایمیل</th>
                                    <th scope="col" className="px-3 text-end font-medium">موجودی</th>
                                    <th scope="col" className="px-3 text-end font-medium">سفارش</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">ثبت‌نام</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.users.map((u) => (
                                    <tr
                                        key={u.id}
                                        onClick={(e) => {
                                            if ((e.target as HTMLElement).closest('a')) return;
                                            navigate(`/users/${u.id}`, { state: { list: listHref } });
                                        }}
                                        className="cursor-pointer border-b border-stroke last:border-0 hover:bg-surface-2"
                                    >
                                        <td className="py-3 ps-5 pe-3">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Link
                                                    to={`/users/${u.id}`}
                                                    state={{ list: listHref }}
                                                    className="max-w-[220px] truncate font-bold underline-offset-4 hover:underline"
                                                >
                                                    {u.name || 'بدون نام'}
                                                </Link>
                                                <BlockedPill flags={u.flags} />
                                            </div>
                                        </td>
                                        <td dir="ltr" className="num px-3 text-end">
                                            {u.mobile}
                                        </td>
                                        <td dir="ltr" className="latin hidden max-w-[220px] truncate px-3 text-end text-[13px] text-muted lg:table-cell">
                                            {u.email || '—'}
                                        </td>
                                        <td className={`num px-3 text-end whitespace-nowrap ${u.wallet > 0 ? 'font-medium' : 'text-muted'}`}>{toman(u.wallet)}</td>
                                        <td className="num px-3 text-end">{u.orders}</td>
                                        <td dir="ltr" className="num py-3 ps-3 pe-5 text-end text-[13px] whitespace-nowrap text-muted">
                                            {u.createdAt}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <ul className="md:hidden">
                            {data.users.map((u) => (
                                <li key={u.id} className="border-b border-stroke last:border-0">
                                    <Link to={`/users/${u.id}`} state={{ list: listHref }} className="block px-4 py-3 hover:bg-surface-2">
                                        <div className="flex items-center justify-between gap-2">
                                            <span className="truncate font-bold">{u.name || 'بدون نام'}</span>
                                            <span className="num shrink-0 font-medium">{toman(u.wallet)}</span>
                                        </div>
                                        <div className="mt-1 flex items-center justify-between gap-2 text-[13px] text-muted">
                                            <span dir="ltr" className="num">
                                                {u.mobile}
                                            </span>
                                            <span className="num">{u.orders} سفارش</span>
                                        </div>
                                        {blockLabels(u.flags).length > 0 && (
                                            <div className="mt-1.5">
                                                <BlockedPill flags={u.flags} />
                                            </div>
                                        )}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </section>

            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun="کاربر" onPage={(p) => update({ page: String(p) })} />}
        </div>
    );
}
