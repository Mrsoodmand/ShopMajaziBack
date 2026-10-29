import { Link, useLocation } from 'react-router';
import { CircleCheck, PackageCheck, Plus, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Badge } from '../../components/Status';
import { CopyButton, FilterTabs, Notice, Row } from '../../components/ui';
import { AttentionBar, PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, isFiltered, type Column, type FilterDef } from '../../components/DataList';
import type { StockItem, StockKind, StockPage, StockRow } from './config';

function State({ row }: { row: { delivered: boolean } }) {
    return row.delivered ? <Badge tone="success" icon={CircleCheck} label="تحویل شده" /> : <Badge tone="info" icon={PackageCheck} label="در انبار" />;
}

const filters: FilterDef[] = [{ type: 'dates', label: 'تاریخ افزودن' }];
const sorts = [
    { value: '', label: 'جدیدترین' },
    { value: 'oldest', label: 'قدیمی‌ترین' },
];

export default function StockList({ kind }: { kind: StockKind }) {
    const notice = (useLocation().state as { notice?: string } | null)?.notice;
    const list = useListParams({ filter: kind.defaultFilter, q: '', sort: '', per: '', from: '', to: '', user: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<StockPage & { perPage: number }>(`${kind.api}?${list.query}`);
    const name = (r: StockRow) => (kind.primary === 'appleId' ? r.appleId : r.number) || '—';

    const columns: Column<StockRow>[] = [
        {
            key: 'name',
            header: kind.primary === 'appleId' ? 'اپل آیدی' : 'شماره',
            primary: true,
            cell: (r) => (
                <span dir="ltr" className="latin block max-w-[320px] truncate text-end font-bold">
                    {name(r)}
                </span>
            ),
        },
        ...(kind.id === 'apple-ids' ? [{ key: 'kind', header: 'نوع', cell: (r: StockRow) => <span className="text-[13px]">{r.kind === 'custom' ? 'سفارشی' : 'آماده'}</span> }] : []),
        { key: 'state', header: 'وضعیت', cell: (r) => <State row={r} /> },
        {
            key: 'order',
            header: 'سفارش',
            cell: (r) =>
                r.orderId ? (
                    <Link to={`/orders/${r.orderId}`} className="num font-medium underline-offset-4 hover:underline">
                        #{r.orderId}
                    </Link>
                ) : (
                    <span className="text-muted">—</span>
                ),
        },
        {
            key: 'user',
            header: 'خریدار',
            cell: (r) =>
                r.user ? (
                    <Link to={`/users/${r.user.id}`} className="underline-offset-4 hover:underline">
                        {r.user.name || (
                            <span dir="ltr" className="num">
                                {r.user.mobile}
                            </span>
                        )}
                    </Link>
                ) : (
                    <span className="text-muted">—</span>
                ),
        },
        { key: 'created', header: 'ثبت', cell: (r) => <span dir="ltr" className="num text-[13px] whitespace-nowrap text-muted">{r.createdAt}</span> },
    ];

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

            <FilterBar
                views={<FilterTabs label={`فیلتر ${kind.title}`} items={kind.filters} value={v.filter} counts={data?.counts} onChange={(filter) => list.update({ filter })} />}
                search={{ label: `جستجوی ${kind.noun} یا شماره سفارش`, placeholder: kind.primary === 'appleId' ? 'ایمیل یا شماره سفارش' : 'شماره یا شماره سفارش' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={data?.total}
                noun={kind.noun}
                loading={loading}
                onRefresh={reload}
            />

            <DataTable
                label={`فهرست ${kind.title}`}
                rows={data?.items}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(r) => r.id}
                href={(r) => `${kind.base}/${r.id}`}
                linkState={{ list: list.here }}
                rowLabel={(r) => `${kind.noun} ${name(r)}`}
                quick={{
                    title: (r) => (
                        <span dir="ltr" className="latin">
                            {name(r)}
                        </span>
                    ),
                    subtitle: (r) => <State row={r} />,
                    body: (r) => <StockQuick kind={kind} id={r.id} row={r} />,
                }}
                mobile={(r) => (
                    <>
                        <div className="flex items-center justify-between gap-2">
                            <span dir="ltr" className="latin truncate font-bold">
                                {name(r)}
                            </span>
                            <State row={r} />
                        </div>
                        <div className="mt-1 flex justify-between gap-2 text-[13px] text-muted">
                            <span className="num">{r.orderId ? `سفارش #${r.orderId}` : kind.id === 'apple-ids' && r.kind === 'custom' ? 'سفارشی' : 'بدون سفارش'}</span>
                            <span dir="ltr" className="num">
                                {r.createdAt}
                            </span>
                        </div>
                    </>
                )}
                empty={
                    <ListEmpty
                        filtered={isFiltered(v, ['filter'])}
                        onClear={() => list.update({ q: '', from: '', to: '', user: '' })}
                        action={
                            <Link to={`${kind.base}/new`} className="btn btn-primary">
                                {kind.addTitle}
                            </Link>
                        }
                    >
                        موردی در این فهرست نیست.
                    </ListEmpty>
                }
            />
            {data && (
                <ListPager
                    page={data.page}
                    lastPage={data.lastPage}
                    total={data.total}
                    perPage={data.perPage}
                    noun={kind.noun}
                    onPage={(p) => list.update({ page: String(p) })}
                    onPer={(n) => list.update({ per: String(n) })}
                />
            )}
        </div>
    );
}

/** The login itself, with copy buttons, so support can hand it over without opening the page. */
function StockQuick({ kind, id, row }: { kind: StockKind; id: number; row: StockRow }) {
    const { data, error } = useQuery<{ item: StockItem }>(`${kind.api}/${id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!data) return <QuickSkeleton />;
    const item = data.item;
    return (
        <>
            <QuickSection title="اطلاعات ورود">
                <dl className="m-0">
                    {kind.fields.map((f) => {
                        const value = item[f.key];
                        return (
                            <Row key={f.key} label={f.label} ltr={f.ltr}>
                                {value ? (
                                    <>
                                        <span className={`select-all ${f.ltr ? 'latin' : ''}`}>{value}</span>
                                        <CopyButton value={value} label={f.label} />
                                    </>
                                ) : (
                                    <span className="font-normal text-muted">—</span>
                                )}
                            </Row>
                        );
                    })}
                </dl>
            </QuickSection>
            <QuickSection title="تحویل">
                <dl className="m-0">
                    <Row label="وضعیت">
                        <State row={item} />
                    </Row>
                    <Row label="سفارش">
                        {item.orderId ? (
                            <Link to={`/orders/${item.orderId}`} className="num text-indigo underline-offset-4 hover:underline">
                                #{item.orderId}
                            </Link>
                        ) : (
                            '—'
                        )}
                    </Row>
                    {row.user && (
                        <Row label="خریدار">
                            <Link to={`/users/${row.user.id}`} className="text-indigo underline-offset-4 hover:underline">
                                {row.user.name || row.user.mobile}
                            </Link>
                        </Row>
                    )}
                    <Row label="ثبت" ltr>
                        {item.createdAt}
                    </Row>
                </dl>
            </QuickSection>
        </>
    );
}
