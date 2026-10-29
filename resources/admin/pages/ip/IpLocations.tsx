import { Link } from 'react-router';
import { Globe, Plus, Server, Settings2 } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count, toman } from '../../lib/format';
import { ErrorState } from '../../components/ui';
import { AttentionBar, EmptyState, PageHeader } from '../../components/list';
import { useIpScope } from './scope';

export type LocationRow = {
    id: number;
    name: string;
    enName: string;
    slug: string;
    image: string | null;
    live: boolean;
    plans: number;
    fromPrice: number | null;
    servers: number;
    free: number;
    active: number;
};

export default function IpLocations() {
    const scope = useIpScope();
    const { data, error, reload } = useQuery<{ waitingOrders: number; locations: LocationRow[] }>(`${scope.api}/locations`);

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title={scope.title}
                subtitle={scope.subtitle}
                actions={
                    <>
                        <Link to={scope.ordersLink} className="btn btn-secondary">
                            سفارش‌ها
                        </Link>
                        {scope.pageLink && (
                            <Link to={scope.pageLink} className="btn btn-secondary">
                                <Settings2 aria-hidden size={16} />
                                متن صفحه
                            </Link>
                        )}
                        <Link to={`${scope.base}/new`} className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            کشور جدید
                        </Link>
                    </>
                }
            />

            {!!data?.waitingOrders && (
                <AttentionBar action={<Link to={scope.ordersLink} className="btn btn-secondary">دیدن سفارش‌ها</Link>}>
                    <span className="num">{count(data.waitingOrders)}</span> سفارش پرداخت‌شده منتظر کلاینت آزاد است. در کشور همان سفارش، از بخش سرورها کلاینت بسازید تا خودکار تحویل شود.
                </AttentionBar>
            )}

            {error ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !data ? (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="در حال بارگذاری">
                    {Array.from({ length: 6 }, (_, i) => (
                        <div key={i} className="skeleton h-36 rounded-2xl" />
                    ))}
                </div>
            ) : data.locations.length === 0 ? (
                <div className="rounded-2xl border border-stroke bg-surface">
                    <EmptyState action={<Link to={`${scope.base}/new`} className="btn btn-primary">کشور جدید</Link>}>هنوز کشوری ساخته نشده است.</EmptyState>
                </div>
            ) : (
                <ul className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {data.locations.map((l) => (
                        <li key={l.id}>
                            <Link to={`${scope.base}/${l.id}`} className="block h-full rounded-2xl border border-stroke bg-surface p-4 transition-colors hover:border-field">
                                <div className="flex items-center gap-3">
                                    <span className="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-surface-2">
                                        {l.image ? <img src={l.image} alt="" className="size-full object-cover" /> : <Globe aria-hidden size={20} className="text-muted" />}
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate font-bold">{l.name}</span>
                                        <span className="block text-[13px] text-muted">
                                            {l.plans ? <>از <span className="num">{toman(l.fromPrice)}</span> · <span className="num">{l.plans}</span> پلن</> : 'بدون پلن'}
                                        </span>
                                    </span>
                                    {!l.live && <span className="rounded-full bg-surface-2 px-2.5 py-0.5 text-[12px] text-muted">غیرفعال</span>}
                                </div>
                                <dl className="mt-4 grid grid-cols-3 gap-2 text-center">
                                    <div className="rounded-xl bg-surface-2 py-2">
                                        <dt className="text-[12px] text-muted">سرور</dt>
                                        <dd className="num m-0 font-bold">
                                            <Server aria-hidden size={13} className="me-1 inline text-muted" />
                                            {l.servers}
                                        </dd>
                                    </div>
                                    <div className={`rounded-xl py-2 ${l.free === 0 ? 'bg-warn-soft text-warn' : 'bg-surface-2'}`}>
                                        <dt className={`text-[12px] ${l.free === 0 ? '' : 'text-muted'}`}>آزاد</dt>
                                        <dd className="num m-0 font-bold">{l.free}</dd>
                                    </div>
                                    <div className="rounded-xl bg-surface-2 py-2">
                                        <dt className="text-[12px] text-muted">فعال</dt>
                                        <dd className="num m-0 font-bold">{l.active}</dd>
                                    </div>
                                </dl>
                                {l.free === 0 && l.live && (
                                    <p className="mt-2 flex items-center gap-1.5 text-[12px] text-warn">
                                        {scope.webPage ? 'کلاینت آزادی نمانده؛ سفارش جدید منتظر می‌ماند.' : 'کلاینت آزادی نمانده؛ ربات چیزی برای فروش ندارد.'}
                                    </p>
                                )}
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
