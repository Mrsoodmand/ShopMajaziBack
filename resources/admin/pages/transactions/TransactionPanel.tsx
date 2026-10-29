import { useEffect, useId, useRef, useState, type FormEvent, type ReactNode } from 'react';
import { Link } from 'react-router';
import { ArrowRight, CircleCheck, CircleX, ListFilter, X } from 'lucide-react';
import { postForm, postJson, useQuery, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { toman } from '../../lib/format';
import { OrderStatus, PayStatus } from '../../components/Status';
import { CopyButton, ErrorState, FieldError, Notice, Row } from '../../components/ui';
import { gateName, MISMATCH, type Transaction } from './types';

/**
 * The end column of the transactions screen: one transaction, its customer and order, and what can be done
 * with it. Below 1024px it replaces the list.
 */
export default function TransactionPanel({
    id,
    onClose,
    onChanged,
    onOpen,
}: {
    id: number;
    onClose: () => void;
    onChanged: () => void;
    onOpen: (id: number) => void;
}) {
    const { data: t, error, loading, reload } = useQuery<Transaction>(`/admin/api/transactions/${id}`);
    const { labels } = useBoot();
    const headingRef = useRef<HTMLHeadingElement>(null);
    const [notice, setNotice] = useState<string | null>(null);

    // Keyboard and screen-reader users land on the opened transaction; on phones the panel starts at the top.
    useEffect(() => {
        if (!t) return;
        headingRef.current?.focus({ preventScroll: true });
        if (window.matchMedia('(max-width: 1023px)').matches) window.scrollTo({ top: 0 });
    }, [t?.id]);

    function done(message: string) {
        setNotice(message);
        reload();
        onChanged();
    }

    return (
        <aside
            aria-label={`تراکنش ${id}`}
            onKeyDown={(e) => {
                if (e.key === 'Escape' && !(e.target as HTMLElement).closest('input, textarea, select')) onClose();
            }}
            className="min-w-0 rounded-2xl border border-stroke bg-surface lg:sticky lg:top-20 lg:max-h-[calc(100dvh-6rem)] lg:overflow-y-auto"
        >
            <button type="button" onClick={onClose} className="flex items-center gap-1.5 px-5 pt-4 text-muted hover:text-ink lg:hidden">
                <ArrowRight aria-hidden size={16} />
                فهرست تراکنش‌ها
            </button>

            {error && !t ? (
                <ErrorState error={error as ApiError} onRetry={reload} />
            ) : !t ? (
                <PanelSkeleton />
            ) : (
                <div className={loading ? 'opacity-70 transition-opacity' : ''}>
                    <header className="flex items-start justify-between gap-3 px-5 pt-4">
                        <div className="min-w-0">
                            <h2 ref={headingRef} tabIndex={-1} className="num text-[18px] leading-tight font-bold focus:outline-none">
                                تراکنش #{t.id}
                            </h2>
                            <p className="mt-1 text-body">
                                {labels.paymentType[t.type] ?? t.type} · {gateName(t.gate, t.type)}
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={onClose}
                            className="-me-2 hidden size-9 shrink-0 place-items-center rounded-lg text-muted hover:bg-surface-2 hover:text-ink lg:grid"
                            aria-label="بستن جزئیات"
                            title="بستن (Esc)"
                        >
                            <X aria-hidden size={18} />
                        </button>
                    </header>

                    <div className="flex flex-wrap items-baseline justify-between gap-3 px-5 pt-4 pb-5">
                        <span className="num text-[24px] leading-none font-bold">{toman(t.price)}</span>
                        <PayStatus status={t.status} />
                    </div>

                    {notice && (
                        <div className="-mt-2 px-5 pb-5">
                            <Notice>{notice}</Notice>
                        </div>
                    )}
                    {t.status === MISMATCH && t.type === '3' && <Mismatch t={t} onDone={done} />}

                    <Section title="جزئیات پرداخت">
                        <dl className="m-0">
                            {t.gateway && <Row label="درگاه">{t.gateway}</Row>}
                            {t.refId && (
                                <Row label="کد رهگیری" ltr>
                                    {t.refId}
                                    <CopyButton value={t.refId} label="کد رهگیری" />
                                </Row>
                            )}
                            {t.card && (
                                <Row label="کارت پرداخت" ltr>
                                    {t.card}
                                </Row>
                            )}
                            {t.fee != null && (
                                <Row label="کارمزد درگاه" num>
                                    {toman(Math.round(t.fee / 10))}
                                </Row>
                            )}
                            {t.authority && (
                                <Row label="شناسه درگاه" ltr>
                                    <span className="latin text-[13px] break-all">{t.authority}</span>
                                </Row>
                            )}
                            <Row label="ثبت" ltr>
                                {t.createdAt}
                            </Row>
                            {t.paidAt && (
                                <Row label="پرداخت" ltr>
                                    {t.paidAt}
                                </Row>
                            )}
                            {t.updatedAt && t.updatedAt !== t.createdAt && (
                                <Row label="آخرین تغییر" ltr>
                                    {t.updatedAt}
                                </Row>
                            )}
                            {t.note && <Row label="توضیح">{t.note}</Row>}
                            {t.error && <Row label="خطا">{t.error}</Row>}
                        </dl>
                    </Section>

                    <CustomerSection t={t} />

                    {(t.order || t.refundOf || t.refundedBy) && (
                        <Section title={t.order ? `سفارش #${t.order.id}` : 'عودت وجه'}>
                            {t.order && (
                                <dl className="m-0">
                                    <Row label="سرویس">
                                        <Link to={`/orders/${t.order.id}`} className="text-indigo underline-offset-4 hover:underline">
                                            {t.order.service || labels.orderType[t.order.type] || `سفارش ${t.order.id}`}
                                        </Link>
                                    </Row>
                                    <Row label="وضعیت سفارش">
                                        <OrderStatus status={t.order.status} />
                                    </Row>
                                    <Row label="مبلغ سفارش" num>
                                        {toman(t.order.price)}
                                    </Row>
                                </dl>
                            )}
                            {t.refundOf && (
                                <p className="mt-3 text-body">
                                    عودت مبلغ{' '}
                                    <button type="button" onClick={() => onOpen(t.refundOf!)} className="num font-medium text-indigo underline underline-offset-4">
                                        تراکنش #{t.refundOf}
                                    </button>{' '}
                                    به کیف پول.
                                </p>
                            )}
                            {t.refundedBy && (
                                <p className="mt-3 flex items-start gap-2 rounded-xl bg-surface-2 px-3 py-2.5 text-body">
                                    <CircleCheck aria-hidden size={16} className="mt-1 shrink-0 text-primary" />
                                    <span>
                                        مبلغ این پرداخت با{' '}
                                        <button type="button" onClick={() => onOpen(t.refundedBy!.id)} className="num font-medium text-indigo underline underline-offset-4">
                                            تراکنش #{t.refundedBy.id}
                                        </button>{' '}
                                        به کیف پول برگشته است
                                        {t.refundedBy.at && (
                                            <span className="text-muted">
                                                {' '}
                                                (<span dir="ltr" className="num">{t.refundedBy.at}</span>)
                                            </span>
                                        )}
                                        .
                                    </span>
                                </p>
                            )}
                            {t.can.refund && <Refund t={t} onDone={done} />}
                        </Section>
                    )}

                    {t.history.length > 0 && (
                        <Section title="تاریخچه">
                            <ol className="space-y-4">
                                {t.history.map((h) => (
                                    <li key={h.id} className="flex gap-3">
                                        <span aria-hidden className="mt-2 size-2 shrink-0 rounded-full bg-field" />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                {h.from && <PayStatus status={h.from} />}
                                                <ArrowRight aria-hidden size={14} className="rotate-180 text-muted" />
                                                <span className="sr-only">به</span>
                                                {h.to && <PayStatus status={h.to} />}
                                            </div>
                                            {h.reason && <p className="mt-1.5 text-body">{h.reason}</p>}
                                            <p className="mt-1 text-[13px] text-muted">
                                                {h.by ?? 'سیستم'} · <span dir="ltr" className="num">{h.at}</span>
                                            </p>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </Section>
                    )}
                </div>
            )}
        </aside>
    );
}

function Section({ title, children }: { title: string; children: ReactNode }) {
    const id = useId();
    return (
        <section aria-labelledby={id} className="border-t border-stroke px-5 pt-4 pb-5">
            <h3 id={id} className="mb-1 font-bold">
                {title}
            </h3>
            {children}
        </section>
    );
}

function CustomerSection({ t }: { t: Transaction }) {
    const c = t.customer;
    if (!c) {
        return (
            <Section title="کاربر">
                <p className="text-muted">این تراکنش به کاربری وصل نیست.</p>
            </Section>
        );
    }
    return (
        <Section title="کاربر">
            <dl className="m-0">
                <Row label="نام">
                    <Link to={`/users/${c.id}`} className="text-indigo underline-offset-4 hover:underline">
                        {c.name || 'بدون نام'}
                    </Link>
                </Row>
                {c.mobile && (
                    <Row label="موبایل" ltr>
                        {c.mobile}
                        <CopyButton value={c.mobile} label="موبایل" />
                    </Row>
                )}
                <Row label="موجودی کیف پول" num>
                    {toman(c.wallet)}
                </Row>
                {c.walletBlocked && <Row label="کیف پول">خرید با کیف پول مسدود است</Row>}
            </dl>
            <Link to={`/transactions?view=all&user=${c.id}`} className="btn btn-ghost mt-2 -ms-2.5 text-[13px] font-medium">
                <ListFilter aria-hidden size={15} />
                همه تراکنش‌های این کاربر
            </Link>
        </Section>
    );
}

/* ---------- Mismatched wallet top-up: credit or reject, each after a confirm step ---------- */

function Mismatch({ t, onDone }: { t: Transaction; onDone: (message: string) => void }) {
    const { csrfToken } = useBoot();
    const [action, setAction] = useState<'credit' | 'reject'>('credit');
    const [note, setNote] = useState('');
    const [step, setStep] = useState<'form' | 'confirm'>('form');
    const [error, setError] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();
    const noteRef = useRef<HTMLTextAreaElement>(null);
    const confirmRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    function review(e: FormEvent) {
        e.preventDefault();
        if (!note.trim()) {
            setError(action === 'credit' ? 'بنویسید کارت را چطور بررسی کردید؛ در تاریخچه ثبت می‌شود.' : 'بنویسید چرا رد می‌شود؛ در تاریخچه ثبت می‌شود.');
            noteRef.current?.focus();
            return;
        }
        setError(null);
        setStep('confirm');
    }

    async function confirm() {
        setBusy(true);
        try {
            const res = await postJson<{ success: string }>(`/admin/api/transactions/${t.id}/resolve`, csrfToken, { action, note: note.trim() });
            onDone(res.success);
        } catch (err) {
            const e = err as ApiError;
            setError(e.fields?.note ?? e.message);
            setStep('form');
        } finally {
            setBusy(false);
        }
    }

    const wallet = t.customer?.wallet ?? 0;

    return (
        <section aria-labelledby={`${id}-title`} className="mx-5 mb-5 rounded-xl bg-warn-soft px-4 pt-3 pb-4">
            <h3 id={`${id}-title`} className="font-bold">
                {step === 'confirm' ? (action === 'credit' ? 'شارژ کیف پول را تأیید کنید' : 'رد تراکنش را تأیید کنید') : 'مغایرت کارت'}
            </h3>

            {step === 'form' ? (
                <>
                    <p className="mt-1 text-[13px] text-body">
                        مبلغ از کارت کاربر کم شده ولی چون کارت پرداخت با کارت ثبت‌شده یکی نیست، کیف پول شارژ نشده است.
                    </p>
                    <dl className="m-0 mt-2">
                        <Row label="کارت ثبت‌شده" ltr>
                            {t.registeredCard ?? <span className="font-normal text-muted">ثبت نشده</span>}
                        </Row>
                        <Row label="کارت پرداخت" ltr>
                            <span className="inline-flex items-center gap-1.5">
                                {t.cardMatches === false && (
                                    <span className="inline-flex items-center gap-1 text-[12px] font-bold text-error" dir="rtl">
                                        <CircleX aria-hidden size={14} />
                                        متفاوت
                                    </span>
                                )}
                                {t.card ?? '—'}
                            </span>
                        </Row>
                    </dl>

                    <form onSubmit={review} noValidate className="mt-3">
                        <fieldset>
                            <legend className="mb-1.5 font-medium">اقدام</legend>
                            <div className="grid grid-cols-2 gap-2">
                                {(
                                    [
                                        { id: 'credit', label: 'شارژ کیف پول', hint: 'کارت مال خود کاربر است' },
                                        { id: 'reject', label: 'رد تراکنش', hint: 'مبلغ به کارت برگشته' },
                                    ] as const
                                ).map((o) => (
                                    <label
                                        key={o.id}
                                        className={`cursor-pointer rounded-xl border-2 bg-surface px-3 py-2 transition-colors has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-ring ${
                                            action === o.id ? 'border-ink' : 'border-stroke hover:border-field'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name={`${id}-action`}
                                            value={o.id}
                                            checked={action === o.id}
                                            onChange={() => setAction(o.id)}
                                            className="sr-only"
                                        />
                                        <span className="flex items-center gap-1.5 font-medium">
                                            {action === o.id ? <CircleCheck aria-hidden size={15} className="shrink-0" /> : <span aria-hidden className="size-[15px] shrink-0 rounded-full border border-field" />}
                                            {o.label}
                                        </span>
                                        <span className="mt-0.5 block text-[12px] text-muted">{o.hint}</span>
                                    </label>
                                ))}
                            </div>
                        </fieldset>
                        <label htmlFor={`${id}-note`} className="mt-3 mb-1 block font-medium">
                            {action === 'credit' ? 'نتیجه بررسی' : 'علت رد'}
                        </label>
                        <textarea
                            ref={noteRef}
                            id={`${id}-note`}
                            rows={2}
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                            className="field resize-y"
                            placeholder={action === 'credit' ? 'مثلاً: عکس کارت در تیکت ۵۱۲ بررسی شد' : 'مثلاً: بانک مبلغ را به کارت برگرداند'}
                            aria-invalid={!!error}
                            aria-describedby={error ? `${id}-error` : undefined}
                        />
                        {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
                        <button type="submit" className="btn btn-secondary mt-3 w-full bg-surface">
                            {action === 'credit' ? 'بررسی شارژ' : 'بررسی رد'}
                        </button>
                    </form>
                </>
            ) : (
                <div role="group" aria-labelledby={`${id}-title`}>
                    <dl className="m-0 mt-2">
                        {action === 'credit' ? (
                            <>
                                <Row label="مبلغ" num>
                                    {toman(t.price)}
                                </Row>
                                <Row label="به کیف پول">
                                    {t.customer?.name || 'بدون نام'}
                                    {t.customer?.mobile && (
                                        <span dir="ltr" className="num block text-end text-[13px] font-normal text-muted">
                                            {t.customer.mobile}
                                        </span>
                                    )}
                                </Row>
                                <Row label="موجودی فعلی" num>
                                    {toman(wallet)}
                                </Row>
                                <Row label="موجودی پس از شارژ" num>
                                    {toman(wallet + t.price)}
                                </Row>
                            </>
                        ) : (
                            <Row label="کیف پول">تغییر نمی‌کند</Row>
                        )}
                        <Row label={action === 'credit' ? 'نتیجه بررسی' : 'علت'}>{note.trim()}</Row>
                    </dl>
                    <p className="mt-2 text-[13px] text-body">
                        {action === 'credit'
                            ? 'تراکنش «موفق» می‌شود و در تاریخچه تراکنش و حساب کاربر ثبت می‌شود. این کار برگشت‌پذیر نیست.'
                            : 'تراکنش «لغو شده» می‌شود و از صف بیرون می‌رود. این کار برگشت‌پذیر نیست.'}
                    </p>
                    <div className="mt-3 flex gap-2">
                        <button
                            ref={confirmRef}
                            type="button"
                            onClick={confirm}
                            disabled={busy}
                            className={`btn flex-1 ${action === 'credit' ? 'btn-primary' : 'btn-secondary border-error bg-surface text-error'}`}
                        >
                            {busy ? 'در حال ثبت…' : action === 'credit' ? `شارژ ${toman(t.price)}` : 'رد تراکنش'}
                        </button>
                        <button type="button" className="btn btn-secondary bg-surface" onClick={() => setStep('form')} disabled={busy}>
                            انصراف
                        </button>
                    </div>
                </div>
            )}
        </section>
    );
}

/* ---------- Refund a paid order payment to the wallet (OrderController@ChangeStatusWallet) ---------- */

function Refund({ t, onDone }: { t: Transaction; onDone: (message: string) => void }) {
    const { actions, csrfToken, labels } = useBoot();
    const [reason, setReason] = useState('');
    const [step, setStep] = useState<'idle' | 'form' | 'confirm'>('idle');
    const [error, setError] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();
    const confirmRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    if (!t.order || !t.customer) return null;
    const { order, customer } = t;
    const detail = reason.trim() || `عودت وجه تراکنش ${t.id} از سفارش ${order.id}`;

    async function confirm() {
        setBusy(true);
        try {
            onDone(await postForm(actions.refund, csrfToken, { order_id: order.id, payment_id: t.id, detail }));
        } catch (err) {
            setError((err as Error).message);
            setStep('form');
        } finally {
            setBusy(false);
        }
    }

    if (step === 'idle') {
        return (
            <button type="button" className="btn btn-secondary mt-4 w-full" onClick={() => setStep('form')}>
                عودت به کیف پول
            </button>
        );
    }

    if (step === 'confirm') {
        return (
            <div role="group" aria-labelledby={`${id}-confirm`} className="mt-4 rounded-xl bg-warn-soft px-4 pt-3 pb-4">
                <h4 id={`${id}-confirm`} className="font-bold">
                    عودت را تأیید کنید
                </h4>
                <dl className="m-0 mt-1">
                    <Row label="مبلغ" num>
                        {toman(order.price)}
                    </Row>
                    <Row label="به کیف پول">{customer.name || customer.mobile || 'بدون نام'}</Row>
                    <Row label="موجودی فعلی" num>
                        {toman(customer.wallet)}
                    </Row>
                    <Row label="موجودی پس از عودت" num>
                        {toman(customer.wallet + order.price)}
                    </Row>
                    <Row label="علت">{detail}</Row>
                </dl>
                <p className="mt-2 text-[13px] text-body">
                    مبلغ کل سفارش برمی‌گردد و تراکنش «{labels.paymentType['2']}» ثبت می‌شود. این کار برگشت‌پذیر نیست.
                </p>
                <div className="mt-3 flex gap-2">
                    <button ref={confirmRef} type="button" className="btn btn-primary flex-1" onClick={confirm} disabled={busy}>
                        {busy ? 'در حال عودت…' : `عودت ${toman(order.price)}`}
                    </button>
                    <button type="button" className="btn btn-secondary bg-surface" onClick={() => setStep('form')} disabled={busy}>
                        انصراف
                    </button>
                </div>
            </div>
        );
    }

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                setError(null);
                setStep('confirm');
            }}
            noValidate
            className="mt-4"
        >
            <h4 className="mb-2 font-bold">عودت به کیف پول</h4>
            <label htmlFor={`${id}-reason`} className="mb-1 block font-medium">
                علت <span className="font-normal text-muted">(اختیاری)</span>
            </label>
            <input
                id={`${id}-reason`}
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                className="field"
                placeholder="مثلاً: پیامک نرسید"
                aria-describedby={error ? `${id}-error` : undefined}
            />
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
            <div className="mt-3 flex gap-2">
                <button type="submit" className="btn btn-secondary flex-1">
                    بررسی عودت
                </button>
                <button type="button" className="btn btn-ghost" onClick={() => setStep('idle')}>
                    انصراف
                </button>
            </div>
        </form>
    );
}

function PanelSkeleton() {
    return (
        <div className="space-y-3 p-5" aria-label="در حال بارگذاری">
            <div className="skeleton h-6 w-40" />
            <div className="skeleton h-4 w-56" />
            <div className="skeleton h-8 w-48" />
            <div className="skeleton h-40 w-full rounded-xl" />
            <div className="skeleton h-28 w-full rounded-xl" />
        </div>
    );
}
