import { useEffect, useState } from 'react';
import { Link, useLocation, useParams } from 'react-router';
import { ArrowRight } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { CopyButton, ErrorState, Notice, Panel, Row } from '../../components/ui';
import { BotOrderBadge, botUserName, ORDER_STATUS, type BotOrderStatus, type BotOrderSummary, type BotPayment, type BotUserRef } from './parts';
import { PaymentsTable } from './BotUserDetail';

type Order = BotOrderSummary & {
    country: string | null;
    plan: string | null;
    volume: number;
    used: number;
    expiresAt: string | null;
    note: string;
    user: BotUserRef | null;
};
type Client = { id: number; name: string; code: string; server: string | null; locationId: number | null; volume: number | null; expiresAt: string | null };
type Data = { order: Order; client: Client | null; payments: BotPayment[]; history: { id: number; text: string; at: string | null }[] };

export default function BotOrderDetail() {
    const { id } = useParams();
    const location = useLocation();
    const backTo = `/bot/orders${(location.state as { list?: string } | null)?.list ?? ''}`;
    const { data, error, loading, reload } = useQuery<Data>(`/admin/api/bot/orders/${id}`);
    const o = data?.order;

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={backTo} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                سفارش‌های ربات
            </Link>
            {error && !data ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !data || !o ? (
                <div aria-label="در حال بارگذاری">
                    <div className="skeleton mb-6 h-7 w-72" />
                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="skeleton h-80 rounded-2xl" />
                        <div className="skeleton h-64 rounded-2xl" />
                    </div>
                </div>
            ) : (
                <div className={loading ? 'opacity-80 transition-opacity' : ''}>
                    <header className="mb-6 flex flex-wrap items-center gap-3">
                        <h1 className="text-[22px] leading-tight font-bold">
                            سفارش <span className="num">#{o.id}</span>
                        </h1>
                        <BotOrderBadge status={o.status} />
                    </header>
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="min-w-0 space-y-6">
                            <Panel title="سفارش">
                                <dl className="m-0">
                                    <Row label="عنوان">{o.title}</Row>
                                    <Row label="کشور">{o.country ?? '—'}</Row>
                                    <Row label="پلن">{o.plan ?? '—'}</Row>
                                    <Row label="حجم" num>
                                        {o.volume ? `${o.volume} گیگ` : '—'}
                                    </Row>
                                    <Row label="انقضا" ltr>
                                        {o.expiresAt ?? '—'}
                                    </Row>
                                    <Row label="ثبت" ltr>
                                        {o.createdAt}
                                    </Row>
                                    {o.note && <Row label="یادداشت">{o.note}</Row>}
                                </dl>
                            </Panel>
                            <Panel title="کلاینت آی‌پی">
                                {data.client ? (
                                    <>
                                        <dl className="m-0">
                                            <Row label="نام کلاینت" ltr>
                                                <span className="latin">{data.client.name || `#${data.client.id}`}</span>
                                            </Row>
                                            <Row label="سرور" ltr>
                                                <span className="latin">{data.client.server ?? '—'}</span>
                                            </Row>
                                            <Row label="حجم" num>
                                                {data.client.volume != null ? `${data.client.volume} گیگ` : '—'}
                                            </Row>
                                            <Row label="انقضای کلاینت" ltr>
                                                {data.client.expiresAt ?? '—'}
                                            </Row>
                                        </dl>
                                        {data.client.code && (
                                            <div className="mt-3">
                                                <p className="mb-1 flex items-center gap-1 font-medium">
                                                    لینک کانفیگ
                                                    <CopyButton value={data.client.code} label="لینک کانفیگ" />
                                                </p>
                                                <p dir="ltr" className="latin rounded-xl bg-surface-2 px-3 py-2 text-[13px] break-all">{data.client.code}</p>
                                            </div>
                                        )}
                                        {data.client.locationId && (
                                            <Link to={`/bot/ip/${data.client.locationId}?tab=clients`} className="mt-3 inline-block font-medium text-indigo underline-offset-4 hover:underline">
                                                کلاینت‌های این کشور
                                            </Link>
                                        )}
                                    </>
                                ) : (
                                    <p className="text-muted">کلاینتی به این سفارش داده نشده است.</p>
                                )}
                            </Panel>
                            <Panel title="تراکنش‌ها">
                                {data.payments.length ? <PaymentsTable payments={data.payments} /> : <p className="text-muted">تراکنشی برای این سفارش نیست.</p>}
                            </Panel>
                        </div>
                        <aside className="space-y-6 lg:sticky lg:top-20" aria-label="خریدار و وضعیت">
                            <Panel title="خریدار">
                                {o.user ? (
                                    <dl className="m-0">
                                        <Row label="کاربر">
                                            <Link to={`/bot/users/${o.user.id}`} dir="auto" className="underline-offset-4 hover:underline">
                                                {botUserName(o.user)}
                                            </Link>
                                        </Row>
                                        <Row label="آیدی تلگرام" ltr>
                                            {o.user.telegramId}
                                        </Row>
                                    </dl>
                                ) : (
                                    <p className="text-muted">کاربر این سفارش پیدا نشد.</p>
                                )}
                            </Panel>
                            <Panel title="وضعیت سفارش">
                                <StatusForm orderId={o.id} status={o.status} onChanged={reload} />
                                {data.history.length > 0 && (
                                    <div className="mt-4 border-t border-stroke pt-3">
                                        <h3 className="mb-1 text-[13px] font-bold">تغییرهای قبلی</h3>
                                        <ul className="space-y-1 text-[13px] text-muted">
                                            {data.history.map((h) => (
                                                <li key={h.id}>
                                                    {h.text} · <span dir="ltr" className="num">{h.at}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </Panel>
                        </aside>
                    </div>
                </div>
            )}
        </div>
    );
}

function StatusForm({ orderId, status, onChanged }: { orderId: number; status: BotOrderStatus; onChanged: () => void }) {
    const { csrfToken } = useBoot();
    const [value, setValue] = useState<BotOrderStatus>(status);
    const [busy, setBusy] = useState(false);
    const [msg, setMsg] = useState<string | null>(null);
    useEffect(() => setValue(status), [status]);

    async function apply() {
        setBusy(true);
        setMsg(null);
        try {
            const res = await postJson<{ success: string }>(`/admin/api/bot/orders/${orderId}/status`, csrfToken, { status: value });
            setMsg(res.success);
            onChanged();
        } catch (e) {
            setMsg((e as ApiError).message);
        } finally {
            setBusy(false);
        }
    }

    return (
        <div>
            <label htmlFor="bot-order-status" className="mb-1 block font-medium">
                وضعیت
            </label>
            <select id="bot-order-status" className="field" value={value} onChange={(e) => setValue(e.target.value as BotOrderStatus)}>
                {(Object.keys(ORDER_STATUS) as BotOrderStatus[]).map((s) => (
                    <option key={s} value={s}>
                        {ORDER_STATUS[s].label}
                    </option>
                ))}
            </select>
            <p className="mt-1.5 text-[13px] text-muted">فقط وضعیت سفارش در ربات عوض می‌شود؛ کلاینت روی پنل سرور دست نمی‌خورد.</p>
            <button type="button" className="btn btn-secondary mt-3 w-full" onClick={apply} disabled={busy || value === status}>
                {busy ? 'در حال ثبت…' : 'ثبت وضعیت'}
            </button>
            {msg && <Notice>{msg}</Notice>}
        </div>
    );
}
