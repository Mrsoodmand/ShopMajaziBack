import { Link } from 'react-router';
import { Ban } from 'lucide-react';
import { useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { toman } from '../lib/format';
import { OrderStatus } from '../components/Status';
import { CopyButton, FilterTabs, Row } from '../components/ui';
import { PageHeader, useListParams } from '../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, filterDefaults, isFiltered, type Column, type FilterDef } from '../components/DataList';
import type { User } from './UserDetail';

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

type UsersPage = { counts: Record<Filter, number>; page: number; lastPage: number; total: number; perPage: number; users: UserRow[] };

const views: { id: Filter; label: string }[] = [
    { id: 'all', label: 'همه' },
    { id: 'balance', label: 'دارای موجودی' },
    { id: 'blocked', label: 'محدودشده' },
];

const filters: FilterDef[] = [
    { type: 'dates', label: 'تاریخ ثبت‌نام' },
    { type: 'range', label: 'موجودی کیف پول', unit: 'تومان' },
    {
        type: 'select',
        key: 'buyer',
        label: 'خرید',
        options: [
            { value: 'yes', label: 'سفارش ثبت کرده' },
            { value: 'no', label: 'بدون سفارش' },
        ],
    },
    {
        type: 'select',
        key: 'block',
        label: 'محدودیت',
        options: [
            { value: 'login', label: 'ورود مسدود' },
            { value: 'wallet', label: 'خرید با کیف پول مسدود' },
            { value: 'bank', label: 'خرید با درگاه مسدود' },
            { value: 'account', label: 'حساب مسدود' },
        ],
    },
];

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'newest', label: 'جدیدترین ثبت‌نام' },
    { value: 'oldest', label: 'قدیمی‌ترین ثبت‌نام' },
    { value: 'wallet_desc', label: 'بیشترین موجودی' },
    { value: 'orders_desc', label: 'بیشترین سفارش' },
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
    const list = useListParams({ filter: 'all', q: '', sort: '', per: '', ...filterDefaults(filters) });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<UsersPage>(`/admin/api/users?${list.query}`);

    const columns: Column<UserRow>[] = [
        {
            key: 'name',
            header: 'کاربر',
            primary: true,
            cell: (u) => (
                <span className="leading-snug">
                    <span className="block max-w-[220px] truncate font-bold">{u.name || 'بدون نام'}</span>
                    <span className="num block text-[12px] text-muted">کد {u.id}</span>
                </span>
            ),
        },
        { key: 'mobile', header: 'موبایل', cell: (u) => <span dir="ltr" className="num">{u.mobile}</span> },
        { key: 'email', header: 'ایمیل', from: 'lg', cell: (u) => <span dir="ltr" className="latin block max-w-[220px] truncate text-end text-[13px] text-muted">{u.email || '—'}</span> },
        { key: 'wallet', header: 'موجودی', align: 'end', cell: (u) => <span className={`num whitespace-nowrap ${u.wallet > 0 ? 'font-medium' : 'text-muted'}`}>{toman(u.wallet)}</span> },
        {
            key: 'orders',
            header: 'سفارش',
            align: 'end',
            cell: (u) =>
                u.orders ? (
                    <Link to={`/orders?view=all&user=${u.id}`} className="num underline-offset-4 hover:underline" title="سفارش‌های این کاربر">
                        {u.orders}
                    </Link>
                ) : (
                    <span className="num text-muted">۰</span>
                ),
        },
        { key: 'state', header: 'وضعیت', from: 'xl', cell: (u) => (blockLabels(u.flags).length ? <BlockedPill flags={u.flags} /> : <span className="text-muted">—</span>) },
        { key: 'created', header: 'ثبت‌نام', cell: (u) => <span dir="ltr" className="num text-[13px] whitespace-nowrap text-muted">{u.createdAt}</span> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader title="کاربران" subtitle={v.filter === 'balance' && !v.sort ? 'بیشترین موجودی اول.' : 'جدیدترین ثبت‌نام‌ها اول، مگر ترتیب دیگری انتخاب کنید.'} />

            <FilterBar
                views={<FilterTabs label="فیلتر کاربران" items={views} value={v.filter as Filter} counts={data?.counts} onChange={(filter) => list.update({ filter })} />}
                search={{ label: 'جستجو با موبایل، نام، ایمیل یا کد کاربر', placeholder: 'موبایل، نام، ایمیل یا کد' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={data?.total}
                noun="کاربر"
                loading={loading}
                onRefresh={reload}
            />

            <DataTable
                label="لیست کاربران"
                rows={data?.users}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(u) => u.id}
                href={(u) => `/users/${u.id}`}
                linkState={{ list: list.here }}
                rowLabel={(u) => `کاربر ${u.name || u.mobile || u.id}`}
                quick={{
                    title: (u) => u.name || 'بدون نام',
                    subtitle: (u) => (
                        <span className="flex flex-wrap items-center gap-2">
                            <span dir="ltr" className="num">
                                {u.mobile}
                            </span>
                            <BlockedPill flags={u.flags} />
                        </span>
                    ),
                    body: (u) => <UserQuick id={u.id} />,
                }}
                mobile={(u) => (
                    <>
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
                    </>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v, ['filter'])} onClear={() => list.update({ q: '', ...filterDefaults(filters) })}>
                        کاربری در این فهرست نیست.
                    </ListEmpty>
                }
            />

            {data && (
                <ListPager
                    page={data.page}
                    lastPage={data.lastPage}
                    total={data.total}
                    perPage={data.perPage}
                    noun="کاربر"
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(n) => list.update({ per: String(n) })}
                />
            )}
        </div>
    );
}

function UserQuick({ id }: { id: number }) {
    const { labels } = useBoot();
    const { data: user, error } = useQuery<User>(`/admin/api/users/${id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!user) return <QuickSkeleton />;
    return (
        <>
            <QuickSection title="حساب">
                <dl className="m-0">
                    <Row label="موبایل" ltr>
                        {user.mobile}
                        {user.mobile && <CopyButton value={user.mobile} label="موبایل" />}
                    </Row>
                    {user.email && (
                        <Row label="ایمیل" ltr>
                            <span className="latin">{user.email}</span>
                        </Row>
                    )}
                    <Row label="موجودی کیف پول" num>
                        {toman(user.wallet)}
                    </Row>
                    <Row label="موجودی مسدود" num>
                        {toman(user.blockedWallet)}
                    </Row>
                    <Row label="عضو از" ltr>
                        {user.createdAt}
                    </Row>
                </dl>
            </QuickSection>
            <QuickSection title="خرید">
                <dl className="m-0">
                    <Row label="سفارش‌ها" num>
                        {user.stats.orders}
                    </Row>
                    <Row label="تکمیل‌شده" num>
                        {user.stats.completed}
                    </Row>
                    <Row label="مبلغ خرید تکمیل‌شده" num>
                        {toman(user.stats.spent)}
                    </Row>
                </dl>
            </QuickSection>
            <QuickSection
                title="آخرین سفارش‌ها"
                action={
                    user.stats.orders > 0 ? (
                        <Link to={`/orders?view=all&user=${user.id}`} className="text-[13px] font-medium text-indigo underline-offset-4 hover:underline">
                            همه
                        </Link>
                    ) : undefined
                }
            >
                {user.orders.length === 0 ? (
                    <p className="pb-2 text-muted">سفارشی ثبت نکرده است.</p>
                ) : (
                    <ul className="-mx-4 divide-y divide-stroke">
                        {user.orders.slice(0, 5).map((o) => (
                            <li key={o.id}>
                                <Link to={`/orders/${o.id}`} className="flex items-center gap-3 px-4 py-2.5 hover:bg-surface-2">
                                    <span className="num w-14 font-bold">#{o.id}</span>
                                    <span className="min-w-0 flex-1 truncate text-[13px]">{o.service || labels.orderType[o.type]}</span>
                                    <OrderStatus status={o.status} />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </QuickSection>
        </>
    );
}
