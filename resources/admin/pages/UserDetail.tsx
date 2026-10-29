import { useEffect, useId, useRef, useState, type FormEvent } from 'react';
import { Link, useLocation, useParams } from 'react-router';
import { ArrowRight, Eye, EyeOff, ExternalLink, LogIn, MessageSquare } from 'lucide-react';
import { postForm, useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { parseAmount, toman } from '../lib/format';
import { OrderStatus, PayStatus } from '../components/Status';
import { CopyButton, ErrorState, FieldError, Notice, Panel, Row, TabbedPanel, gateLabel } from '../components/ui';
import { BlockedPill, type UserFlags } from './UsersList';

type User = {
    id: number;
    name: string | null;
    mobile: string | null;
    email: string | null;
    wallet: number;
    blockedWallet: number;
    flags: UserFlags;
    createdAt: string | null;
    stats: { orders: number; completed: number; spent: number };
    links: { legacy: string; panel: string };
    orders: { id: number; type: string; service: string | null; price: number; status: string; createdAt: string | null }[];
    payments: { id: number; orderId: number | null; type: string; status: string; gate: string | null; price: number; note: string | null; at: string | null }[];
    tickets: { id: number; subject: string | null; status: string; statusLabel: string; unread: boolean; url: string; at: string | null }[];
    referrals: { id: number; name: string | null; mobile: string | null; createdAt: string | null }[];
    history: { id: number; changes: { kind: 'credit' | 'debit' | 'info' | 'reason'; text: string }[]; by: string | null; at: string | null }[];
};


/**
 * UserController@SubmitUserChange rewrites every field on each save, so every request
 * carries the user's current name and switches alongside the change being made.
 */
function baseFields(user: User): Record<string, string | number> {
    return {
        id: user.id,
        user_name: user.name ?? '',
        wallet: 0,
        wallet_action: 1,
        blocked_wallet: 0,
        blocked_wallet_action: 1,
        ...(user.flags.loginBlocked ? { status: 1 } : {}),
        ...(user.flags.walletBlocked ? { wallet_blocked: 1 } : {}),
        ...(user.flags.bankBlocked ? { bank_blocked: 1 } : {}),
    };
}

export default function UserDetail() {
    const { id } = useParams();
    const location = useLocation();
    const backTo = `/users${(location.state as { list?: string } | null)?.list ?? ''}`;
    const { data: user, error, loading, reload } = useQuery<User>(`/admin/api/users/${id}`);

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={backTo} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                کاربران
            </Link>

            {error && !user ? (
                <ErrorState error={error} onRetry={reload} boxed />
            ) : !user ? (
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
                    <header className="mb-6 flex flex-wrap items-start justify-between gap-4">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-3">
                                <h1 className="text-[22px] leading-tight font-bold">{user.name || 'بدون نام'}</h1>
                                <BlockedPill flags={user.flags} />
                            </div>
                            <p className="mt-1.5 text-body">
                                <span dir="ltr" className="num">
                                    {user.mobile}
                                </span>
                                {user.mobile && <CopyButton value={user.mobile} label="موبایل" />}
                                <span className="text-muted">
                                    {' '}· عضو از <span dir="ltr" className="num">{user.createdAt}</span>
                                </span>
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-1">
                            <a href={user.links.panel} target="_blank" rel="noreferrer" className="btn btn-ghost text-[13px] font-medium">
                                <LogIn aria-hidden size={15} className="rtl:-scale-x-100" />
                                ورود به پنل کاربر
                            </a>
                            <a href={user.links.legacy} className="btn btn-ghost text-[13px] font-medium">
                                <ExternalLink aria-hidden size={15} />
                                صفحه قدیمی کاربر
                            </a>
                        </div>
                    </header>

                    <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div className="min-w-0">
                            <Activity user={user} />
                        </div>
                        <aside className="space-y-6 lg:sticky lg:top-20" aria-label="حساب و اقدام‌ها">
                            <Panel title="حساب">
                                <dl className="m-0">
                                    <Row label="موجودی کیف پول" num>
                                        {toman(user.wallet)}
                                    </Row>
                                    <Row label="موجودی مسدود" num>
                                        {toman(user.blockedWallet)}
                                    </Row>
                                    <Row label="سفارش‌ها" num>
                                        {user.stats.orders}
                                    </Row>
                                    <Row label="سفارش تکمیل‌شده" num>
                                        {user.stats.completed}
                                    </Row>
                                    <Row label="خرید تکمیل‌شده" num>
                                        {toman(user.stats.spent)}
                                    </Row>
                                    {user.email && (
                                        <Row label="ایمیل" ltr>
                                            <span className="latin">{user.email}</span>
                                        </Row>
                                    )}
                                </dl>
                            </Panel>
                            <Panel title="کیف پول">
                                <WalletForm user={user} onChanged={reload} />
                            </Panel>
                            <Panel title="تنظیمات حساب">
                                <SettingsForm user={user} onChanged={reload} />
                            </Panel>
                        </aside>
                    </div>
                </div>
            )}
        </div>
    );
}

function Activity({ user }: { user: User }) {
    const { labels } = useBoot();
    const unreadTickets = user.tickets.filter((t) => t.unread).length;
    return (
        <TabbedPanel
            label="فعالیت کاربر"
            tabs={[
                { id: 'orders', label: 'سفارش‌ها', count: user.stats.orders },
                { id: 'payments', label: 'تراکنش‌ها', count: user.payments.length },
                { id: 'tickets', label: 'تیکت‌ها', count: unreadTickets || user.tickets.length, highlight: unreadTickets ? 'تیکت جدید' : undefined },
                { id: 'referrals', label: 'معرفی‌شده‌ها', count: user.referrals.length },
                { id: 'history', label: 'تغییرات حساب', count: user.history.length },
            ]}
            render={(tab) => {
                if (tab === 'orders') {
                    if (!user.orders.length) return <p className="text-muted">این کاربر هنوز سفارشی ثبت نکرده است.</p>;
                    return (
                        <>
                            <ul className="-mx-5 divide-y divide-stroke">
                                {user.orders.map((o) => (
                                    <li key={o.id}>
                                        <Link to={`/orders/${o.id}`} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3 hover:bg-surface-2">
                                            <span className="num w-16 font-bold">#{o.id}</span>
                                            <span className="min-w-0 flex-1 truncate">
                                                {o.service || labels.orderType[o.type] || o.type}
                                                <span className="ms-2 text-[13px] text-muted">{labels.orderType[o.type]}</span>
                                            </span>
                                            <span className="num font-medium whitespace-nowrap">{toman(o.price)}</span>
                                            <OrderStatus status={o.status} />
                                            <span dir="ltr" className="num w-32 text-end text-[13px] text-muted">
                                                {o.createdAt}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                            {user.stats.orders > user.orders.length && (
                                <Link to={`/orders?view=all&q=${user.mobile ?? ''}`} className="mt-3 inline-block font-medium text-indigo underline-offset-4 hover:underline">
                                    همه {user.stats.orders} سفارش این کاربر
                                </Link>
                            )}
                        </>
                    );
                }
                if (tab === 'payments') {
                    if (!user.payments.length) return <p className="text-muted">تراکنشی ثبت نشده است.</p>;
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
                                    {user.payments.map((p) => (
                                        <tr key={p.id} className="border-b border-stroke last:border-0">
                                            <td className="num py-2.5 ps-5 pe-3 font-medium">{p.id}</td>
                                            <td className="px-3">
                                                {labels.paymentType[p.type] ?? p.type} <span className="text-[13px] text-muted">· {gateLabel(p.gate)}</span>
                                                {p.orderId ? (
                                                    <Link to={`/orders/${p.orderId}`} className="num ms-2 text-[13px] text-indigo hover:underline">
                                                        سفارش #{p.orderId}
                                                    </Link>
                                                ) : null}
                                                {p.note && <div className="text-[12px] text-muted">{p.note}</div>}
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
                if (tab === 'tickets') {
                    if (!user.tickets.length) return <p className="text-muted">تیکتی ثبت نشده است.</p>;
                    return (
                        <ul className="-mx-5 divide-y divide-stroke">
                            {user.tickets.map((t) => (
                                <li key={t.id}>
                                    <a href={t.url} className="flex items-center gap-4 px-5 py-3 hover:bg-surface-2">
                                        <span className="num w-12 text-muted">#{t.id}</span>
                                        <span className="min-w-0 flex-1 truncate font-medium">{t.subject || 'بدون موضوع'}</span>
                                        {t.unread && (
                                            <span className="inline-flex items-center gap-1 text-[13px] font-bold text-indigo">
                                                <MessageSquare aria-hidden size={14} />
                                                جدید
                                            </span>
                                        )}
                                        <span className="text-[13px] text-body">{t.statusLabel}</span>
                                        <span dir="ltr" className="num text-[13px] text-muted">
                                            {t.at}
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    );
                }
                if (tab === 'referrals') {
                    if (!user.referrals.length) return <p className="text-muted">کسی با کد معرف این کاربر ثبت‌نام نکرده است.</p>;
                    return (
                        <ul className="-mx-5 divide-y divide-stroke">
                            {user.referrals.map((r) => (
                                <li key={r.id}>
                                    <Link to={`/users/${r.id}`} className="flex items-center gap-4 px-5 py-3 hover:bg-surface-2">
                                        <span className="min-w-0 flex-1 truncate font-medium">{r.name || 'بدون نام'}</span>
                                        <span dir="ltr" className="num text-body">
                                            {r.mobile}
                                        </span>
                                        <span dir="ltr" className="num text-[13px] text-muted">
                                            {r.createdAt}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    );
                }
                if (!user.history.length) return <p className="text-muted">حساب این کاربر هنوز به‌دست مدیری تغییر نکرده است.</p>;
                return (
                    <ol className="space-y-4">
                        {user.history.map((h) => (
                            <li key={h.id} className="flex gap-3">
                                <span aria-hidden className="mt-2 size-2 shrink-0 rounded-full bg-field" />
                                <div className="min-w-0 flex-1">
                                    {h.changes.map((c, i) => (
                                        <p
                                            key={i}
                                            className={
                                                c.kind === 'credit'
                                                    ? 'font-medium text-emerald-800 dark:text-emerald-300'
                                                    : c.kind === 'debit'
                                                      ? 'font-medium text-error'
                                                      : c.kind === 'reason'
                                                        ? 'text-body'
                                                        : 'text-[13px] text-muted'
                                            }
                                        >
                                            {c.text}
                                        </p>
                                    ))}
                                    <p className="mt-1 text-[13px] text-muted">
                                        {h.by ?? 'سیستم'} · <span dir="ltr" className="num">{h.at}</span>
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ol>
                );
            }}
        />
    );
}

function WalletForm({ user, onChanged }: { user: User; onChanged: () => void }) {
    const { actions, csrfToken } = useBoot();
    const [target, setTarget] = useState<'wallet' | 'blocked'>('wallet');
    const [direction, setDirection] = useState<'credit' | 'debit'>('credit');
    const [amountRaw, setAmountRaw] = useState('');
    const [reason, setReason] = useState('');
    const [step, setStep] = useState<'form' | 'confirm'>('form');
    const [errors, setErrors] = useState<{ amount?: string; reason?: string; server?: string }>({});
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();
    const amountRef = useRef<HTMLInputElement>(null);
    const reasonRef = useRef<HTMLInputElement>(null);
    const confirmRef = useRef<HTMLButtonElement>(null);

    const amount = parseAmount(amountRaw);
    const current = target === 'wallet' ? user.wallet : user.blockedWallet;
    const targetLabel = target === 'wallet' ? 'کیف پول' : 'موجودی مسدود';

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    function review(e: FormEvent) {
        e.preventDefault();
        setDone(null);
        const next: typeof errors = {};
        if (!amount) next.amount = 'مبلغ را به تومان و بیشتر از صفر بنویسید.';
        else if (direction === 'debit' && amount > current) next.amount = `بیشتر از موجودی فعلی (${toman(current)}) نمی‌شود کسر کرد.`;
        if (!reason.trim()) next.reason = 'علت را بنویسید؛ در تغییرات حساب ثبت می‌شود.';
        setErrors(next);
        if (next.amount) amountRef.current?.focus();
        else if (next.reason) reasonRef.current?.focus();
        else setStep('confirm');
    }

    async function confirm() {
        if (!amount) return;
        setBusy(true);
        const action = direction === 'credit' ? 1 : 0;
        const change: Record<string, number> =
            target === 'wallet' ? { wallet: amount, wallet_action: action } : { blocked_wallet: amount, blocked_wallet_action: action };
        try {
            const msg = await postForm(actions.saveUser, csrfToken, { ...baseFields(user), ...change, detail: reason.trim() });
            setDone(msg);
            setAmountRaw('');
            setReason('');
            setStep('form');
            onChanged();
        } catch (err) {
            setErrors({ server: (err as Error).message });
            setStep('form');
        } finally {
            setBusy(false);
        }
    }

    if (step === 'confirm' && amount) {
        const after = direction === 'credit' ? current + amount : current - amount;
        return (
            <div role="group" aria-labelledby={`${id}-confirm`}>
                <h3 id={`${id}-confirm`} className="mb-3 font-bold">
                    {direction === 'credit' ? 'افزایش' : 'کسر'} را تأیید کنید
                </h3>
                <dl className="m-0 rounded-xl bg-warn-soft px-4 py-1">
                    <Row label={targetLabel}>{user.name || user.mobile}</Row>
                    <Row label="موجودی فعلی" num>
                        {toman(current)}
                    </Row>
                    <Row label={direction === 'credit' ? 'افزایش' : 'کسر'} num>
                        {toman(amount)}
                    </Row>
                    <Row label="موجودی جدید" num>
                        {toman(after)}
                    </Row>
                    <Row label="علت">{reason}</Row>
                </dl>
                <div className="mt-4 flex gap-2">
                    <button ref={confirmRef} type="button" className="btn btn-primary flex-1" onClick={confirm} disabled={busy}>
                        {busy ? 'در حال ثبت…' : `${direction === 'credit' ? 'افزایش' : 'کسر'} ${toman(amount)}`}
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
                            ['credit', 'افزایش'],
                            ['debit', 'کسر'],
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
            <label htmlFor={`${id}-target`} className="mt-3 mb-1 block font-medium">
                حساب
            </label>
            <select id={`${id}-target`} value={target} onChange={(e) => setTarget(e.target.value as 'wallet' | 'blocked')} className="field">
                <option value="wallet">کیف پول ({toman(user.wallet)})</option>
                <option value="blocked">موجودی مسدود ({toman(user.blockedWallet)})</option>
            </select>
            <label htmlFor={`${id}-amount`} className="mt-3 mb-1 block font-medium">
                مبلغ (تومان)
            </label>
            <input
                ref={amountRef}
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
            <label htmlFor={`${id}-reason`} className="mt-3 mb-1 block font-medium">
                علت
            </label>
            <input
                ref={reasonRef}
                id={`${id}-reason`}
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                placeholder="مثلاً: جبران تأخیر سفارش ۲۵۴۴"
                className="field"
                aria-invalid={!!errors.reason}
                aria-describedby={errors.reason ? `${id}-reason-error` : undefined}
            />
            {errors.reason && <FieldError id={`${id}-reason-error`}>{errors.reason}</FieldError>}
            {errors.server && <FieldError id={`${id}-server-error`}>{errors.server}</FieldError>}
            <button type="submit" className="btn btn-secondary mt-4 w-full">
                بررسی تغییر موجودی
            </button>
            {done && <Notice>{done}</Notice>}
        </form>
    );
}

function SettingsForm({ user, onChanged }: { user: User; onChanged: () => void }) {
    const { actions, csrfToken } = useBoot();
    const [name, setName] = useState(user.name ?? '');
    const [flags, setFlags] = useState(user.flags);
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [done, setDone] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();

    useEffect(() => {
        setName(user.name ?? '');
        setFlags(user.flags);
    }, [user]);

    const dirty =
        name.trim() !== (user.name ?? '') ||
        password !== '' ||
        flags.loginBlocked !== user.flags.loginBlocked ||
        flags.walletBlocked !== user.flags.walletBlocked ||
        flags.bankBlocked !== user.flags.bankBlocked;

    async function submit(e: FormEvent) {
        e.preventDefault();
        setDone(null);
        if (password && password.length < 6) {
            setError('رمز عبور جدید دست‌کم ۶ نویسه باشد.');
            return;
        }
        setError(null);
        setBusy(true);
        try {
            const msg = await postForm(actions.saveUser, csrfToken, {
                ...baseFields(user),
                user_name: name.trim(),
                status: flags.loginBlocked ? 1 : '',
                wallet_blocked: flags.walletBlocked ? 1 : '',
                bank_blocked: flags.bankBlocked ? 1 : '',
                ...(password ? { password } : {}),
            });
            setPassword('');
            setDone(msg);
            onChanged();
        } catch (err) {
            setError((err as Error).message);
        } finally {
            setBusy(false);
        }
    }

    const switches: { key: keyof Omit<UserFlags, 'accountBlocked'>; label: string; hint: string }[] = [
        { key: 'loginBlocked', label: 'مسدود کردن ورود', hint: 'کاربر نمی‌تواند وارد حسابش شود.' },
        { key: 'walletBlocked', label: 'مسدود کردن خرید با کیف پول', hint: 'پرداخت از موجودی کیف پول بسته می‌شود.' },
        { key: 'bankBlocked', label: 'مسدود کردن خرید با درگاه', hint: 'پرداخت با کارت بانکی بسته می‌شود.' },
    ];

    return (
        <form onSubmit={submit} noValidate>
            <label htmlFor={`${id}-name`} className="mb-1 block font-medium">
                نام و نام خانوادگی
            </label>
            <input id={`${id}-name`} value={name} onChange={(e) => setName(e.target.value)} className="field" />

            <fieldset className="mt-4 mb-0 border-0 p-0">
                <legend className="mb-1 font-medium">محدودیت‌ها</legend>
                <div className="space-y-1">
                    {switches.map((s) => (
                        <label key={s.key} className="flex cursor-pointer items-start gap-3 rounded-lg px-1 py-2 hover:bg-surface-2">
                            <input
                                type="checkbox"
                                checked={flags[s.key]}
                                onChange={(e) => setFlags({ ...flags, [s.key]: e.target.checked })}
                                className="mt-1 size-4 shrink-0 accent-[var(--error)]"
                            />
                            <span>
                                <span className={`block ${flags[s.key] ? 'font-bold text-error' : 'font-medium'}`}>{s.label}</span>
                                <span className="block text-[13px] text-muted">{s.hint}</span>
                            </span>
                        </label>
                    ))}
                </div>
            </fieldset>

            <label htmlFor={`${id}-password`} className="mt-4 mb-1 block font-medium">
                رمز عبور جدید <span className="font-normal text-muted">(اختیاری)</span>
            </label>
            <div className="relative">
                <input
                    id={`${id}-password`}
                    type={showPassword ? 'text' : 'password'}
                    autoComplete="new-password"
                    dir="ltr"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="field pe-11"
                    aria-describedby={`${id}-password-hint`}
                />
                <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute end-1 top-1/2 grid size-8 -translate-y-1/2 place-items-center rounded-md text-muted hover:bg-surface-2 hover:text-ink"
                    aria-label={showPassword ? 'پنهان کردن رمز' : 'نمایش رمز'}
                >
                    {showPassword ? <EyeOff aria-hidden size={16} /> : <Eye aria-hidden size={16} />}
                </button>
            </div>
            <p id={`${id}-password-hint`} className="mt-1.5 text-[13px] text-muted">
                خالی بماند تا رمز فعلی عوض نشود.
            </p>
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
            <button type="submit" className="btn btn-primary mt-4 w-full" disabled={busy || !dirty}>
                {busy ? 'در حال ذخیره…' : dirty ? 'ذخیره تغییرات' : 'تغییری نداده‌اید'}
            </button>
            {done && <Notice>{done}</Notice>}
        </form>
    );
}
