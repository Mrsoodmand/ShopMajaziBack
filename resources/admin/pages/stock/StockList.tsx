import { Link, useLocation, useNavigate } from 'react-router';
import { CircleCheck, PackageCheck, Plus, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Badge } from '../../components/Status';
import { ErrorState, FilterTabs, Notice, Pager, SearchField, SkeletonRows } from '../../components/ui';
import { AttentionBar, EmptyState, PageHeader, useListParams } from '../../components/list';
import type { StockKind, StockPage, StockRow } from './config';

function State({ row }: { row: StockRow }) {
    return row.delivered ? <Badge tone="success" icon={CircleCheck} label="تحویل شده" /> : <Badge tone="info" icon={PackageCheck} label="در انبار" />;
}

export default function StockList({ kind }: { kind: StockKind }) {
    const navigate = useNavigate();
    const notice = (useLocation().state as { notice?: string } | null)?.notice;
    const list = useListParams({ filter: kind.defaultFilter, q: '' });
    const { data, error, loading, reload } = useQuery<StockPage>(`${kind.api}?${list.query}`);
    const name = (r: StockRow) => (kind.primary === 'appleId' ? r.appleId : r.number) || '—';
    const open = (r: StockRow) => navigate(`${kind.base}/${r.id}`, { state: { list: list.here } });

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={kind.title}
                subtitle={kind.subtitle}
                actions={
                    <>
                        <Link to={`/pages/${kind.settingsPage}`} className="btn btn-secondary">
                            <Settings2 aria-hidden size={16} />
                            قیمت و متن صفحه
                        </Link>
                        <Link to={`${kind.base}/new`} className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            {kind.addTitle}
                        </Link>
                    </>
                }
            />

            {notice && (
                <div className="mb-4">
                    <Notice>{notice}</Notice>
                </div>
            )}

            {!!data?.waitingOrders && (
                <AttentionBar
                    action={
                        <Link to={kind.ordersHref} className="btn btn-secondary">
                            دیدن سفارش‌ها
                        </Link>
                    }
                >
                    <span className="num">{count(data.waitingOrders)}</span> سفارش پرداخت‌شده منتظر موجودی است. با افزودن {kind.noun} به انبار، خودکار تحویل می‌شوند.
                </AttentionBar>
            )}

            <div className="mb-4 flex flex-col gap-3 xl:flex-row xl:items-center">
                <FilterTabs label={`فیلتر ${kind.title}`} items={kind.filters} value={list.values.filter} counts={data?.counts} onChange={(id) => list.update({ filter: id })} />
                <div className="flex flex-1 xl:justify-end">
                    <SearchField
                        value={list.values.q}
                        onSearch={(q) => list.update({ q })}
                        label={`جستجوی ${kind.noun} یا شماره سفارش`}
                        placeholder={kind.primary === 'appleId' ? 'ایمیل یا شماره سفارش' : 'شماره یا شماره سفارش'}
                    />
                </div>
            </div>

            <section aria-label={`فهرست ${kind.title}`} aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows />
                ) : data.items.length === 0 ? (
                    <EmptyState
                        action={
                            list.values.q ? (
                                <button type="button" className="btn btn-secondary" onClick={list.reset}>
                                    پاک کردن جستجو
                                </button>
                            ) : (
                                <Link to={`${kind.base}/new`} className="btn btn-primary">
                                    {kind.addTitle}
                                </Link>
                            )
                        }
                    >
                        {list.values.q ? `چیزی با «${list.values.q}» پیدا نشد.` : 'موردی در این فهرست نیست.'}
                    </EmptyState>
                ) : (
                    <div className={`transition-opacity ${loading ? 'opacity-60' : ''}`}>
                        <table className="hidden w-full md:table">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">{kind.primary === 'appleId' ? 'اپل آیدی' : 'شماره'}</th>
                                    {kind.id === 'apple-ids' && <th scope="col" className="px-3 text-start font-medium">نوع</th>}
                                    <th scope="col" className="px-3 text-start font-medium">وضعیت</th>
                                    <th scope="col" className="px-3 text-start font-medium">سفارش</th>
                                    <th scope="col" className="px-3 text-start font-medium">خریدار</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">ثبت</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.items.map((r) => (
                                    <tr
                                        key={r.id}
                                        onClick={(e) => !(e.target as HTMLElement).closest('a') && open(r)}
                                        className="cursor-pointer border-b border-stroke last:border-0 hover:bg-surface-2"
                                    >
                                        <td className="py-3 ps-5 pe-3">
                                            <Link to={`${kind.base}/${r.id}`} state={{ list: list.here }} dir="ltr" className="latin block max-w-[300px] truncate text-end font-bold underline-offset-4 hover:underline">
                                                {name(r)}
                                            </Link>
                                        </td>
                                        {kind.id === 'apple-ids' && <td className="px-3 text-[13px]">{r.kind === 'custom' ? 'سفارشی' : 'آماده'}</td>}
                                        <td className="px-3">
                                            <State row={r} />
                                        </td>
                                        <td className="px-3">
                                            {r.orderId ? (
                                                <Link to={`/orders/${r.orderId}`} className="num font-medium underline-offset-4 hover:underline">
                                                    #{r.orderId}
                                                </Link>
                                            ) : (
                                                <span className="text-muted">—</span>
                                            )}
                                        </td>
                                        <td className="px-3">
                                            {r.user ? (
                                                <Link to={`/users/${r.user.id}`} className="underline-offset-4 hover:underline">
                                                    {r.user.name || <span dir="ltr" className="num">{r.user.mobile}</span>}
                                                </Link>
                                            ) : (
                                                <span className="text-muted">—</span>
                                            )}
                                        </td>
                                        <td dir="ltr" className="num py-3 ps-3 pe-5 text-end text-[13px] whitespace-nowrap text-muted">
                                            {r.createdAt}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <ul className="md:hidden">
                            {data.items.map((r) => (
                                <li key={r.id} className="border-b border-stroke last:border-0">
                                    <Link to={`${kind.base}/${r.id}`} state={{ list: list.here }} className="block px-4 py-3 hover:bg-surface-2">
                                        <div className="flex items-center justify-between gap-2">
                                            <span dir="ltr" className="latin truncate font-bold">{name(r)}</span>
                                            <State row={r} />
                                        </div>
                                        <div className="mt-1 flex justify-between gap-2 text-[13px] text-muted">
                                            <span className="num">{r.orderId ? `سفارش #${r.orderId}` : kind.id === 'apple-ids' && r.kind === 'custom' ? 'سفارشی' : 'بدون سفارش'}</span>
                                            <span dir="ltr" className="num">{r.createdAt}</span>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </section>
            {data && <Pager page={data.page} lastPage={data.lastPage} total={data.total} noun={kind.noun} onPage={(p) => list.update({ page: String(p) })} />}
        </div>
    );
}
