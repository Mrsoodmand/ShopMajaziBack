import { useEffect, useId, useRef, useState, type FormEvent } from 'react';
import { Link, useLocation, useParams } from 'react-router';
import { ArrowRight, ExternalLink, Send } from 'lucide-react';
import { postForm, useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { toman } from '../lib/format';
import { OrderStatus, PayStatus } from '../components/Status';
import { CopyButton, ErrorState, FieldError, Notice, Panel, Row, TabbedPanel, gateLabel } from '../components/ui';

type Payment = {
    id: number;
    type: string;
    status: string;
    gate: string | null;
    price: number;
    refId: string | null;
    card: string | null;
    note: string | null;
    refunded: boolean;
    at: string | null;
};

type Order = {
    id: number;
    type: string;
    service: string | null;
    price: number;
    discountCode: string | null;
    discountPrice: number | null;
    status: string;
    payStatus: string | null;
    walletRefunded: boolean;
    createdAt: string | null;
    legacyUrl: string;
    customer: { id: number; name: string | null; mobile: string | null; email: string | null; wallet: number; url: string } | null;
    payment: Payment | null;
    payments: Payment[];
    items: { id: number; title: string | null; price: number; rows: { label: string; value: string | null; copy?: boolean; ltr?: boolean }[] }[];
    history: { id: number; from: string | null; to: string | null; reason: string | null; by: string | null; at: string | null }[];
    chats: { id: number; from: 'admin' | 'customer'; text: string; by: string | null; at: string | null; unread: boolean }[];
};

/** Order types whose full detail (credentials, IP config) still lives on the old page. */
const LEGACY_DETAIL_TYPES = ['3', '4', '5', '6'];

export default function OrderDetail() {
    const { id } = useParams();
    const location = useLocation();
    const backTo = `/orders${(location.state as { list?: string } | null)?.list ?? ''}`;
    const { data: order, error, loading, reload } = useQuery<Order>(`/admin/api/orders/${id}`);
    const { labels } = useBoot();

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={backTo} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                سفارش‌ها
            </Link>

            {error && !order ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !order ? (
                <DetailSkeleton />
            ) : (
                <div className={loading ? 'opacity-80 transition-opacity' : ''}>
                    <header className="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-3">
                                <h1 className="num text-[22px] leading-tight font-bold">سفارش #{order.id}</h1>
                                <OrderStatus status={order.status} />
                            </div>
                            <p className="mt-1.5 text-body">
                                {labels.orderType[order.type] ?? order.type}
                                {order.service && <> · {order.service}</>}
                                <span className="text-muted">
                                    {' '}· ثبت <span dir="ltr" className="num">{order.createdAt}</span>
                                </span>
                            </p>
                        </div>
                        <a href={order.legacyUrl} className="btn btn-ghost text-[13px] font-medium">
                            <ExternalLink aria-hidden size={15} />
                            صفحه قدیمی سفارش
                        </a>
                    </header>

                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="min-w-0 space-y-6">
                            <Panel title="اقلام سفارش">
                                <ItemsList order={order} />
                            </Panel>
                            <Activity order={order} onChanged={reload} />
                        </div>

                        <aside className="space-y-6 lg:sticky lg:top-20" aria-label="مشتری و اقدام‌ها">
                            <ActionsPanel order={order} onChanged={reload} />
                            <CustomerPanel order={order} />
                        </aside>
                    </div>
                </div>
            )}
        </div>
    );
}




function ItemsList({ order }: { order: Order }) {
    return (
        <div className="space-y-4">
            {order.items.length === 0 && <p className="text-muted">این سفارش قلمی ثبت نکرده است.</p>}
            {order.items.map((item) => (
                <div key={item.id}>
                    <div className="mb-1 flex items-baseline justify-between gap-3">
                        <h3 className="font-bold">{item.title || order.service || 'قلم سفارش'}</h3>
                        <span className="num shrink-0 text-body">{toman(item.price)}</span>
                    </div>
                    {item.rows.length > 0 && (
                        <dl className="m-0">
                            {item.rows.map((row) => (
                                <Row key={row.label} label={row.label} ltr={row.ltr}>
                                    {row.value ? (
                                        <>
                                            <span className={`select-all ${row.copy ? 'latin' : ''}`}>{row.value}</span>
                                            {row.copy && <CopyButton value={row.value} label={row.label} />}
                                        </>
                                    ) : (
                                        <span className="font-normal text-muted">ثبت نشده</span>
                                    )}
                                </Row>
                            ))}
                        </dl>
                    )}
                </div>
            ))}
            {LEGACY_DETAIL_TYPES.includes(order.type) && (
                <p className="rounded-xl bg-surface-2 px-4 py-3 text-body">
                    جزئیات کامل این نوع سفارش (اطلاعات تحویل و تکمیل آن) هنوز در{' '}
                    <a href={order.legacyUrl} className="font-medium text-indigo underline underline-offset-4">
                        صفحه قدیمی سفارش
                    </a>{' '}
                    است.
                </p>
            )}
            {order.discountCode && (
                <dl className="m-0 border-t border-stroke pt-2">
                    <Row label="کد تخفیف">{order.discountCode}</Row>
                    <Row label="مبلغ پس از تخفیف" num>
                        {toman(order.discountPrice)}
                    </Row>
                </dl>
            )}
            <div className="flex items-baseline justify-between border-t border-stroke pt-3">
                <span className="font-bold">جمع سفارش</span>
                <span className="num text-[18px] font-bold">{toman(order.price)}</span>
            </div>
        </div>
    );
}

/* ---------- Activity: chat, history, payments ---------- */

function Activity({ order, onChanged }: { order: Order; onChanged: () => void }) {
    const unread = order.chats.filter((c) => c.unread).length;
    return (
        <TabbedPanel
            label="فعالیت سفارش"
            tabs={[
                { id: 'chat', label: 'گفتگو', count: unread || order.chats.length, highlight: unread ? 'پیام جدید' : undefined },
                { id: 'history', label: 'تاریخچه وضعیت', count: order.history.length },
                { id: 'payments', label: 'تراکنش‌ها', count: order.payments.length },
            ]}
            render={(tab) =>
                tab === 'chat' ? <Chat order={order} onChanged={onChanged} /> : tab === 'history' ? <History order={order} /> : <Payments order={order} />
            }
        />
    );
}

function Chat({ order, onChanged }: { order: Order; onChanged: () => void }) {
    const { actions, csrfToken } = useBoot();
    const [text, setText] = useState('');
    const [state, setState] = useState<{ busy: boolean; error?: string }>({ busy: false });
    const inputId = useId();

    async function send(e?: FormEvent) {
        e?.preventDefault();
        if (!text.trim()) {
            setState({ busy: false, error: 'متن پیام را بنویسید.' });
            return;
        }
        setState({ busy: true });
        try {
            await postForm(actions.sendMessage, csrfToken, { order_id: order.id, message: text.trim() });
            setText('');
            setState({ busy: false });
            onChanged();
        } catch (err) {
            setState({ busy: false, error: (err as Error).message });
        }
    }

    return (
        <div>
            {order.chats.length === 0 ? (
                <p className="pb-4 text-muted">گفتگویی برای این سفارش ثبت نشده است. پیامی که بفرستید در صفحه سفارش مشتری دیده می‌شود.</p>
            ) : (
                <ol className="mb-5 max-h-[420px] space-y-3 overflow-y-auto">
                    {order.chats.map((m) => {
                        const mine = m.from === 'admin';
                        return (
                            <li key={m.id} className={`flex flex-col ${mine ? 'items-end' : 'items-start'}`}>
                                <div
                                    className={`max-w-[85%] rounded-2xl px-4 py-2.5 whitespace-pre-wrap ${
                                        mine ? 'rounded-ee-md bg-indigo-soft text-ink' : 'rounded-es-md bg-surface-2 text-ink'
                                    } ${m.unread ? 'ring-2 ring-indigo/40' : ''}`}
                                >
                                    {m.text}
                                </div>
                                <span className="mt-1 px-1 text-[12px] text-muted">
                                    {mine ? m.by || 'پشتیبانی' : 'مشتری'} · <span dir="ltr" className="num">{m.at}</span>
                                    {m.unread && <span className="ms-1.5 font-bold text-indigo">جدید</span>}
                                </span>
                            </li>
                        );
                    })}
                </ol>
            )}
            <form onSubmit={send} className="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div className="flex-1">
                    <label htmlFor={inputId} className="sr-only">
                        پیام به مشتری
                    </label>
                    <textarea
                        id={inputId}
                        rows={2}
                        value={text}
                        onChange={(e) => setText(e.target.value)}
                        onKeyDown={(e) => {
                            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) send();
                        }}
                        placeholder="پیام به مشتری… (Ctrl + Enter برای ارسال)"
                        aria-invalid={!!state.error}
                        aria-describedby={state.error ? `${inputId}-error` : undefined}
                        className="field resize-y"
                    />
                </div>
                <button type="submit" className="btn btn-secondary" disabled={state.busy}>
                    <Send aria-hidden size={16} className="-scale-x-100" />
                    {state.busy ? 'در حال ارسال…' : 'ارسال'}
                </button>
            </form>
            {state.error && <FieldError id={`${inputId}-error`}>{state.error}</FieldError>}
        </div>
    );
}

function History({ order }: { order: Order }) {
    if (order.history.length === 0) {
        return <p className="text-muted">وضعیت این سفارش هنوز به‌دست مدیری تغییر نکرده است.</p>;
    }
    return (
        <ol className="space-y-4">
            {order.history.map((h) => (
                <li key={h.id} className="flex gap-3">
                    <span aria-hidden className="mt-2 size-2 shrink-0 rounded-full bg-field" />
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                            {h.from && <OrderStatus status={h.from} />}
                            <ArrowRight aria-hidden size={14} className="rotate-180 text-muted" />
                            <span className="sr-only">به</span>
                            {h.to && <OrderStatus status={h.to} />}
                        </div>
                        {h.reason && h.reason.trim() !== '.' && <p className="mt-1.5 text-body">{h.reason}</p>}
                        <p className="mt-1 text-[13px] text-muted">
                            {h.by ?? 'سیستم'} · <span dir="ltr" className="num">{h.at}</span>
                        </p>
                    </div>
                </li>
            ))}
        </ol>
    );
}


function Payments({ order }: { order: Order }) {
    const { labels } = useBoot();
    if (order.payments.length === 0) return <p className="text-muted">تراکنشی برای این سفارش ثبت نشده است.</p>;
    return (
        <div className="-mx-5 overflow-x-auto">
            <table className="w-full min-w-[640px]">
                <thead>
                    <tr className="border-b border-stroke text-[13px] text-muted">
                        <th scope="col" className="ps-5 pe-3 pb-2 text-start font-medium">شناسه</th>
                        <th scope="col" className="px-3 pb-2 text-start font-medium">نوع</th>
                        <th scope="col" className="px-3 pb-2 text-start font-medium">روش</th>
                        <th scope="col" className="px-3 pb-2 text-end font-medium">مبلغ</th>
                        <th scope="col" className="px-3 pb-2 text-start font-medium">وضعیت</th>
                        <th scope="col" className="ps-3 pe-5 pb-2 text-start font-medium">زمان</th>
                    </tr>
                </thead>
                <tbody>
                    {order.payments.map((p) => (
                        <tr key={p.id} className="border-b border-stroke last:border-0">
                            <td className="num py-2.5 ps-5 pe-3 font-medium">{p.id}</td>
                            <td className="px-3">
                                {labels.paymentType[p.type] ?? p.type}
                                {p.refunded && <span className="ms-2 rounded-full bg-error-soft px-2 py-0.5 text-[12px] font-medium text-error">عودت‌شده</span>}
                                {p.note && <div className="text-[12px] text-muted">{p.note}</div>}
                            </td>
                            <td className="px-3">
                                {gateLabel(p.gate)}
                                {p.refId && (
                                    <div dir="ltr" className="num text-end text-[12px] text-muted">
                                        {p.refId}
                                    </div>
                                )}
                            </td>
                            <td className="num px-3 text-end font-medium whitespace-nowrap">{toman(p.price)}</td>
                            <td className="px-3">
                                <PayStatus status={p.status} />
                            </td>
                            <td dir="ltr" className="num ps-3 pe-5 text-end text-[13px] whitespace-nowrap text-muted">
                                {p.at}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

/* ---------- Side panels ---------- */

function CustomerPanel({ order }: { order: Order }) {
    const c = order.customer;
    const p = order.payment;
    return (
        <Panel title="مشتری و پرداخت">
            {c ? (
                <dl className="m-0">
                    <Row label="نام">
                        <Link to={`/users/${c.id}`} className="text-indigo underline-offset-4 hover:underline">
                            {c.name || 'بدون نام'}
                        </Link>
                    </Row>
                    <Row label="موبایل" ltr>
                        {c.mobile}
                        {c.mobile && <CopyButton value={c.mobile} label="موبایل" />}
                    </Row>
                    {c.email && (
                        <Row label="ایمیل" ltr>
                            <span className="latin">{c.email}</span>
                        </Row>
                    )}
                    <Row label="موجودی کیف پول" num>
                        {toman(c.wallet)}
                    </Row>
                </dl>
            ) : (
                <p className="text-muted">سفارش مهمان؛ حساب کاربری ندارد.</p>
            )}
            <dl className="m-0 mt-3 border-t border-stroke pt-1">
                <Row label="وضعیت پرداخت">
                    <PayStatus status={order.payStatus} />
                </Row>
                {p && (
                    <>
                        <Row label="روش">{gateLabel(p.gate)}</Row>
                        <Row label="مبلغ پرداختی" num>
                            {toman(p.price)}
                        </Row>
                        {p.refId && (
                            <Row label="کد رهگیری" ltr>
                                {p.refId}
                                <CopyButton value={p.refId} label="کد رهگیری" />
                            </Row>
                        )}
                        {p.card && (
                            <Row label="شماره کارت" ltr>
                                {p.card}
                            </Row>
                        )}
                    </>
                )}
            </dl>
        </Panel>
    );
}



function ActionsPanel({ order, onChanged }: { order: Order; onChanged: () => void }) {
    return (
        <Panel title="اقدام روی سفارش">
            <StatusForm order={order} onChanged={onChanged} />
            <div className="my-5 border-t border-stroke" />
            <RefundForm order={order} onChanged={onChanged} />
        </Panel>
    );
}

function StatusForm({ order, onChanged }: { order: Order; onChanged: () => void }) {
    const { labels, actions, csrfToken } = useBoot();
    const [status, setStatus] = useState(order.status);
    const [reason, setReason] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const reasonRef = useRef<HTMLInputElement>(null);
    const id = useId();

    useEffect(() => setStatus(order.status), [order.status]);

    async function submit(e: FormEvent) {
        e.preventDefault();
        setDone(null);
        if (!reason.trim()) {
            setError('علت تغییر وضعیت را بنویسید؛ در تاریخچه سفارش ثبت می‌شود.');
            reasonRef.current?.focus();
            return;
        }
        setError(null);
        setBusy(true);
        try {
            const msg = await postForm(actions.changeStatus, csrfToken, { order_id: order.id, status, detail: reason.trim() });
            setReason('');
            setDone(msg);
            onChanged();
        } catch (err) {
            setError((err as Error).message);
        } finally {
            setBusy(false);
        }
    }

    if (order.walletRefunded) {
        return <p className="text-body">مبلغ این سفارش به کیف پول کاربر برگشته است؛ وضعیت آن دیگر تغییر نمی‌کند.</p>;
    }

    const unchanged = status === order.status;
    return (
        <form onSubmit={submit} noValidate>
            <h3 className="mb-3 font-bold">تغییر وضعیت</h3>
            <label htmlFor={`${id}-status`} className="mb-1 block font-medium">
                وضعیت جدید
            </label>
            <select id={`${id}-status`} value={status} onChange={(e) => setStatus(e.target.value)} className="field">
                {Object.entries(labels.orderStatus).map(([key, label]) => (
                    <option key={key} value={key}>
                        {label.trim()}
                        {key === order.status ? ' (فعلی)' : ''}
                    </option>
                ))}
            </select>
            <label htmlFor={`${id}-reason`} className="mt-3 mb-1 block font-medium">
                علت
            </label>
            <input
                ref={reasonRef}
                id={`${id}-reason`}
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                className="field"
                placeholder="مثلاً: آیدی وارد‌شده درست نیست"
                aria-invalid={!!error}
                aria-describedby={`${id}-hint${error ? ` ${id}-error` : ''}`}
            />
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
            <p id={`${id}-hint`} className="mt-1.5 text-[13px] text-muted">
                وضعیت جدید با پیامک به مشتری اطلاع داده می‌شود.
            </p>
            <button type="submit" className="btn btn-primary mt-4 w-full" disabled={busy || unchanged}>
                {busy ? 'در حال ثبت…' : unchanged ? 'وضعیت جدید را انتخاب کنید' : `ثبت «${labels.orderStatus[status]?.trim()}»`}
            </button>
            {done && <Notice>{done}</Notice>}
        </form>
    );
}

function RefundForm({ order, onChanged }: { order: Order; onChanged: () => void }) {
    const { actions, csrfToken, labels } = useBoot();
    const refundable = order.payments.filter((p) => p.type !== '2' && !p.refunded && p.status === 'Complete');
    const [paymentId, setPaymentId] = useState('');
    const [reason, setReason] = useState('');
    const [step, setStep] = useState<'form' | 'confirm'>('form');
    const [error, setError] = useState<string | null>(null);
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();
    const confirmRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    if (!order.customer) return null;

    function review(e: FormEvent) {
        e.preventDefault();
        setDone(null);
        if (!paymentId) {
            setError('تراکنشی را که مبلغش برمی‌گردد انتخاب کنید.');
            return;
        }
        setError(null);
        if (!reason.trim()) setReason(`عودت وجه تراکنش ${paymentId} از سفارش ${order.id}`);
        setStep('confirm');
    }

    async function confirm() {
        setBusy(true);
        try {
            const msg = await postForm(actions.refund, csrfToken, { order_id: order.id, payment_id: paymentId, detail: reason.trim() });
            setDone(msg);
            setStep('form');
            setPaymentId('');
            setReason('');
            onChanged();
        } catch (err) {
            setError((err as Error).message);
            setStep('form');
        } finally {
            setBusy(false);
        }
    }

    if (refundable.length === 0) {
        return (
            <div>
                <h3 className="mb-1 font-bold">عودت وجه به کیف پول</h3>
                <p className="text-muted">تراکنش پرداخت‌شده‌ای برای عودت نمانده است.</p>
                {done && <Notice>{done}</Notice>}
            </div>
        );
    }

    if (step === 'confirm') {
        const after = order.customer.wallet + order.price;
        return (
            <div role="group" aria-labelledby={`${id}-confirm-title`}>
                <h3 id={`${id}-confirm-title`} className="mb-3 font-bold">
                    عودت را تأیید کنید
                </h3>
                <dl className="m-0 rounded-xl bg-warn-soft px-4 py-1">
                    <Row label="مبلغ" num>
                        {toman(order.price)}
                    </Row>
                    <Row label="به کیف پول">
                        {order.customer.name || 'بدون نام'}
                        {order.customer.mobile && (
                            <span dir="ltr" className="num block text-end text-[13px] font-normal text-muted">
                                {order.customer.mobile}
                            </span>
                        )}
                    </Row>
                    <Row label="موجودی فعلی" num>
                        {toman(order.customer.wallet)}
                    </Row>
                    <Row label="موجودی پس از عودت" num>
                        {toman(after)}
                    </Row>
                    <Row label="علت">{reason}</Row>
                </dl>
                <p className="mt-2 text-[13px] text-muted">
                    مبلغ کل سفارش برمی‌گردد و تراکنش «{labels.paymentType['2']}» ثبت می‌شود. این کار برگشت‌پذیر نیست.
                </p>
                <div className="mt-4 flex gap-2">
                    <button ref={confirmRef} type="button" className="btn btn-primary flex-1" onClick={confirm} disabled={busy}>
                        {busy ? 'در حال عودت…' : `عودت ${toman(order.price)}`}
                    </button>
                    <button type="button" className="btn btn-secondary" onClick={() => setStep('form')} disabled={busy}>
                        انصراف
                    </button>
                </div>
            </div>
        );
    }

    return (
        <form onSubmit={review} noValidate>
            <h3 className="mb-3 font-bold">عودت وجه به کیف پول</h3>
            <label htmlFor={`${id}-payment`} className="mb-1 block font-medium">
                تراکنش
            </label>
            <select
                id={`${id}-payment`}
                value={paymentId}
                onChange={(e) => setPaymentId(e.target.value)}
                className="field"
                aria-invalid={!!error && !paymentId}
                aria-describedby={error ? `${id}-error` : undefined}
            >
                <option value="">انتخاب تراکنش</option>
                {refundable.map((p) => (
                    <option key={p.id} value={p.id}>
                        {p.id} · {toman(p.price)} · {gateLabel(p.gate)}
                    </option>
                ))}
            </select>
            <label htmlFor={`${id}-reason`} className="mt-3 mb-1 block font-medium">
                علت <span className="font-normal text-muted">(اختیاری)</span>
            </label>
            <input id={`${id}-reason`} value={reason} onChange={(e) => setReason(e.target.value)} className="field" placeholder="مثلاً: پیامک نرسید" />
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
            <button type="submit" className="btn btn-secondary mt-4 w-full">
                بررسی عودت
            </button>
            {done && <Notice>{done}</Notice>}
        </form>
    );
}

function DetailSkeleton() {
    return (
        <div aria-label="در حال بارگذاری">
            <div className="skeleton mb-2 h-7 w-56" />
            <div className="skeleton mb-6 h-4 w-80" />
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                <div className="skeleton h-72 rounded-2xl" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        </div>
    );
}
