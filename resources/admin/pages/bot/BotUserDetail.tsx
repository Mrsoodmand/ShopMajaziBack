import { useEffect, useId, useRef, useState, type FormEvent } from 'react';
import { Link, useLocation, useParams } from 'react-router';
import { ArrowRight } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count, parseAmount, toman } from '../../lib/format';
import { CopyButton, ErrorState, FieldError, Notice, Panel, Row, TabbedPanel } from '../../components/ui';
import { BotOrderBadge, BotPayBadge, botUserName, type BotOrderSummary, type BotPayment } from './parts';

type BotUser = {
    id: number;
    telegramId: string;
    username: string;
    name: string;
    wallet: number;
    joinedAt: string | null;
    path: string;
    percent: number;
    parent: { id: number; telegramId: string; username: string; name: string } | null;
    referrals: number;
    commission: number;
    paid: number;
    orders: number;
    activeIps: number;
};
type Data = { user: BotUser; orders: BotOrderSummary[]; payments: BotPayment[]; history: { id: number; change: number | null; note: string; at: string | null }[] };

export default function BotUserDetail() {
    const { id } = useParams();
    const location = useLocation();
    const backTo = `/bot/users${(location.state as { list?: string } | null)?.list ?? ''}`;
    const { data, error, loading, reload } = useQuery<Data>(`/admin/api/bot/users/${id}`);
    const u = data?.user;

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={backTo} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                کاربران ربات
            </Link>
            {error && !data ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !data || !u ? (
                <div aria-label="در حال بارگذاری">
                    <div className="skeleton mb-2 h-7 w-56" />
                    <div className="skeleton mb-6 h-4 w-80" />
                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="skeleton h-72 rounded-2xl" />
                        <div className="skeleton h-96 rounded-2xl" />
                    </div>
                </div>
            ) : (
                <div className={loading ? 'opacity-80 transition-opacity' : ''}>
                    <header className="mb-6">
                        <h1 className="text-[22px] leading-tight font-bold"><bdi>{botUserName(u)}</bdi></h1>
                        <p className="mt-1.5 text-body">
                            آیدی تلگرام <span dir="ltr" className="num">{u.telegramId}</span>
                            <CopyButton value={u.telegramId} label="آیدی تلگرام" />
                            <span className="text-muted">
                                {' '}· عضو از <span dir="ltr" className="num">{u.joinedAt}</span>
                            </span>
                        </p>
                    </header>
                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="min-w-0">
                            <Activity data={data} />
                        </div>
                        <aside className="space-y-6 lg:sticky lg:top-20" aria-label="حساب و کیف پول">
                            <Panel title="حساب">
                                <dl className="m-0">
                                    <Row label="کیف پول" num>
                                        <span className="font-bold">{toman(u.wallet)}</span>
                                    </Row>
                                    <Row label="خرید پرداخت‌شده" num>
                                        {toman(u.paid)}
                                    </Row>
                                    <Row label="سفارش‌ها" num>
                                        {count(u.orders)}
                                    </Row>
                                    <Row label="آی‌پی فعال" num>
                                        {count(u.activeIps)}
                                    </Row>
                                    <Row label="معرفی‌شده‌ها" num>
                                        {count(u.referrals)}
                                    </Row>
                                    <Row label="پورسانت گرفته" num>
                                        {toman(u.commission)}
                                    </Row>
                                    <Row label="معرف">
                                        {u.parent ? (
                                            <Link to={`/bot/users/${u.parent.id}`} dir="auto" className="underline-offset-4 hover:underline">
                                                {botUserName(u.parent)}
                                            </Link>
                                        ) : (
                                            <span className="text-muted">—</span>
                                        )}
                                    </Row>
                                </dl>
                            </Panel>
                            <Panel title="کیف پول">
                                <WalletForm user={u} onChanged={reload} />
                            </Panel>
                        </aside>
                    </div>
                </div>
            )}
        </div>
    );
}

function Activity({ data }: { data: Data }) {
    const { user, orders, payments, history } = data;
    return (
        <TabbedPanel
            label="فعالیت کاربر در ربات"
            tabs={[
                { id: 'orders', label: 'سفارش‌ها', count: user.orders },
                { id: 'payments', label: 'تراکنش‌ها', count: payments.length },
                { id: 'history', label: 'تغییرات کیف پول', count: history.length },
            ]}
            render={(tab) => {
                if (tab === 'orders') {
                    if (!orders.length) return <p className="text-muted">این کاربر سفارشی در ربات ندارد.</p>;
                    return (
                        <>
                            <ul className="-mx-5 divide-y divide-stroke">
                                {orders.map((o) => (
                                    <li key={o.id}>
                                        <Link to={`/bot/orders/${o.id}`} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3 hover:bg-surface-2">
                                            <span className="num w-16 font-bold">#{o.id}</span>
                                            <span className="min-w-0 flex-1 truncate">{o.title}</span>
                                            <BotOrderBadge status={o.status} />
                                            <span dir="ltr" className="num w-32 text-end text-[13px] text-muted">{o.createdAt}</span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                            {user.orders > orders.length && (
                                <Link to={`/bot/orders?user=${user.id}`} className="mt-3 inline-block font-medium text-indigo underline-offset-4 hover:underline">
                                    همه {count(user.orders)} سفارش این کاربر
                                </Link>
                            )}
                        </>
                    );
                }
                if (tab === 'payments') {
                    if (!payments.length) return <p className="text-muted">تراکنشی ثبت نشده است.</p>;
                    return <PaymentsTable payments={payments} />;
                }
                if (!history.length) return <p className="text-muted">کیف پول این کاربر از پنل تغییر نکرده است.</p>;
                return (
                    <ul className="-mx-5 divide-y divide-stroke">
                        {history.map((h) => (
                            <li key={h.id} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3">
                                <span className={`num w-28 font-bold ${h.change != null && h.change < 0 ? 'text-error' : ''}`}>
                                    {h.change == null ? '—' : `${h.change > 0 ? '+' : '−'}${toman(Math.abs(h.change))}`}
                                </span>
                                <span className="min-w-0 flex-1">{h.note || <span className="text-muted">بدون توضیح</span>}</span>
                                <span dir="ltr" className="num text-[13px] text-muted">{h.at}</span>
                            </li>
                        ))}
                    </ul>
                );
            }}
        />
    );
}

export function PaymentsTable({ payments }: { payments: BotPayment[] }) {
    return (
        <div className="-mx-5 overflow-x-auto">
            <table className="w-full min-w-[600px]">
                <thead>
                    <tr className="border-b border-stroke text-[13px] text-muted">
                        <th scope="col" className="ps-5 pe-3 pb-2 text-start font-medium">شناسه</th>
                        <th scope="col" className="px-3 pb-2 text-start font-medium">نوع</th>
                        <th scope="col" className="px-3 pb-2 text-end font-medium">مبلغ</th>
                        <th scope="col" className="px-3 pb-2 text-start font-medium">وضعیت</th>
                        <th scope="col" className="ps-3 pe-5 pb-2 text-start font-medium">زمان</th>
                    </tr>
                </thead>
                <tbody>
                    {payments.map((p) => (
                        <tr key={p.id} className="border-b border-stroke last:border-0">
                            <td className="num py-2.5 ps-5 pe-3 font-medium">
                                {p.id}
                                {p.orderId && (
                                    <Link to={`/bot/orders/${p.orderId}`} className="block text-[12px] font-normal text-muted underline-offset-4 hover:underline">
                                        سفارش #{p.orderId}
                                    </Link>
                                )}
                            </td>
                            <td className="px-3 text-[13px]">
                                {p.type}
                                {p.method && <span className="block text-muted">{p.method}</span>}
                            </td>
                            <td className="num px-3 text-end">{toman(p.amount)}</td>
                            <td className="px-3">
                                <BotPayBadge status={p.status} />
                            </td>
                            <td dir="ltr" className="num ps-3 pe-5 text-end text-[13px] text-muted">{p.at}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function WalletForm({ user, onChanged }: { user: BotUser; onChanged: () => void }) {
    const { csrfToken } = useBoot();
    const [direction, setDirection] = useState<'add' | 'subtract'>('add');
    const [amountRaw, setAmountRaw] = useState('');
    const [note, setNote] = useState('');
    const [step, setStep] = useState<'form' | 'confirm'>('form');
    const [errors, setErrors] = useState<{ amount?: string; note?: string; server?: string }>({});
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();
    const confirmRef = useRef<HTMLButtonElement>(null);
    const amount = parseAmount(amountRaw);

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    function review(e: FormEvent) {
        e.preventDefault();
        setDone(null);
        const next: typeof errors = {};
        if (!amount) next.amount = 'مبلغ را به تومان و بیشتر از صفر بنویسید.';
        else if (direction === 'subtract' && amount > user.wallet) next.amount = `بیشتر از موجودی (${toman(user.wallet)}) نمی‌شود کسر کرد.`;
        if (!note.trim()) next.note = 'علت را بنویسید؛ در تغییرات کیف پول ثبت می‌شود.';
        setErrors(next);
        if (!next.amount && !next.note) setStep('confirm');
    }

    async function confirm() {
        if (!amount) return;
        setBusy(true);
        try {
            const res = await postJson<{ success: string }>(`/admin/api/bot/users/${user.id}/wallet`, csrfToken, { action: direction, amount, note: note.trim() });
            setDone(res.success);
            setAmountRaw('');
            setNote('');
            setStep('form');
            onChanged();
        } catch (e) {
            const err = e as ApiError;
            setErrors({ amount: err.fields?.amount, note: err.fields?.note, server: err.fields ? undefined : err.message });
            setStep('form');
        } finally {
            setBusy(false);
        }
    }

    if (step === 'confirm' && amount) {
        const after = direction === 'add' ? user.wallet + amount : user.wallet - amount;
        return (
            <div role="group" aria-labelledby={`${id}-confirm`}>
                <h3 id={`${id}-confirm`} className="mb-3 font-bold">
                    {direction === 'add' ? 'افزایش' : 'کسر'} را تأیید کنید
                </h3>
                <dl className="m-0 rounded-xl bg-warn-soft px-4 py-1">
                    <Row label="موجودی فعلی" num>
                        {toman(user.wallet)}
                    </Row>
                    <Row label={direction === 'add' ? 'افزایش' : 'کسر'} num>
                        {toman(amount)}
                    </Row>
                    <Row label="موجودی جدید" num>
                        {toman(after)}
                    </Row>
                    <Row label="علت">{note}</Row>
                </dl>
                <div className="mt-4 flex gap-2">
                    <button ref={confirmRef} type="button" className="btn btn-primary flex-1" onClick={confirm} disabled={busy}>
                        {busy ? 'در حال ثبت…' : `${direction === 'add' ? 'افزایش' : 'کسر'} ${toman(amount)}`}
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
            <fieldset className="m-0 border-0 p-0">
                <legend className="mb-1 font-medium">عملیات</legend>
                <div className="grid grid-cols-2 gap-2">
                    {(
                        [
                            ['add', 'افزایش'],
                            ['subtract', 'کسر'],
                        ] as const
                    ).map(([value, label]) => (
                        <label
                            key={value}
                            className={`flex h-[38px] cursor-pointer items-center justify-center rounded-[10px] border font-medium transition-colors has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring ${
                                direction === value ? 'border-ink bg-ink text-surface' : 'border-field hover:bg-surface-2'
                            }`}
                        >
                            <input type="radio" name={`${id}-dir`} value={value} checked={direction === value} onChange={() => setDirection(value)} className="sr-only" />
                            {label}
                        </label>
                    ))}
                </div>
            </fieldset>
            <label htmlFor={`${id}-amount`} className="mt-3 mb-1 block font-medium">
                مبلغ (تومان)
            </label>
            <input
                id={`${id}-amount`}
                inputMode="numeric"
                dir="ltr"
                value={amountRaw}
                onChange={(e) => setAmountRaw(e.target.value)}
                placeholder="50,000"
                className="field num text-end"
                aria-invalid={!!errors.amount}
                aria-describedby={errors.amount ? `${id}-amount-error` : undefined}
            />
            {errors.amount && <FieldError id={`${id}-amount-error`}>{errors.amount}</FieldError>}
            <label htmlFor={`${id}-note`} className="mt-3 mb-1 block font-medium">
                علت
            </label>
            <input
                id={`${id}-note`}
                value={note}
                onChange={(e) => setNote(e.target.value)}
                placeholder="مثلاً: جبران قطعی سرور"
                className="field"
                aria-invalid={!!errors.note}
                aria-describedby={errors.note ? `${id}-note-error` : undefined}
            />
            {errors.note && <FieldError id={`${id}-note-error`}>{errors.note}</FieldError>}
            {errors.server && <FieldError id={`${id}-server-error`}>{errors.server}</FieldError>}
            <button type="submit" className="btn btn-secondary mt-4 w-full">
                بررسی تغییر موجودی
            </button>
            {done && <Notice>{done}</Notice>}
        </form>
    );
}
