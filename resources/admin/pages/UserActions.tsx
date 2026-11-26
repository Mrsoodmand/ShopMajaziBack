import { useId, useRef, useState, type FormEvent } from 'react';
import { Link } from 'react-router';
import { LogIn, Paperclip, Send, ShieldCheck, ShieldOff, X } from 'lucide-react';
import { ApiError, postFormData, postJson } from '../lib/api';
import { useBoot } from '../lib/boot';
import { FieldError, Notice, Panel } from '../components/ui';
import type { User } from './UserDetail';

/** Ticket departments as the storefront stores them (Admin\UserActionApiController::DEPTS). */
const DEPTS = [
    { value: 'orders', label: 'سفارش‌ها' },
    { value: 'sales', label: 'واحد فروش' },
    { value: 'technical', label: 'پشتیبانی فنی' },
    { value: 'accounting', label: 'حسابداری' },
    { value: 'other', label: 'سایر' },
];

/** admin › user, side column: open the customer's panel, disable / enable the account, send a ticket. */
export function UserActions({ user, onChanged }: { user: User; onChanged: () => void }) {
    const disabled = user.flags.loginBlocked;
    return (
        <Panel title="اقدام‌ها">
            <div className="space-y-4">
                <div className="flex items-center justify-between gap-3">
                    <span className="text-body">وضعیت حساب</span>
                    <span className={`rounded-full px-2.5 py-0.5 text-[13px] font-bold ${disabled ? 'bg-error-soft text-error' : 'bg-success-soft text-success'}`}>
                        {disabled ? 'غیرفعال' : 'فعال'}
                    </span>
                </div>
                <StatusAction user={user} onChanged={onChanged} />
                <div className="grid gap-2 border-t border-stroke pt-4">
                    <TicketAction user={user} onChanged={onChanged} />
                    <a
                        href={user.links.panel}
                        target="_blank"
                        rel="noreferrer"
                        className={`btn btn-secondary ${disabled ? 'pointer-events-none opacity-50' : ''}`}
                        aria-disabled={disabled}
                        title={disabled ? 'حساب غیرفعال است؛ ورود ممکن نیست.' : undefined}
                    >
                        <LogIn aria-hidden size={16} className="rtl:-scale-x-100" />
                        ورود به پنل کاربر
                    </a>
                </div>
            </div>
        </Panel>
    );
}

function StatusAction({ user, onChanged }: { user: User; onChanged: () => void }) {
    const { csrfToken } = useBoot();
    const disabled = user.flags.loginBlocked;
    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [done, setDone] = useState<string | null>(null);
    const id = useId();

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (!disabled && !reason.trim()) {
            setError('دلیل غیرفعال کردن را بنویسید؛ در تاریخچه کاربر می‌ماند.');
            return;
        }
        setBusy(true);
        setError(null);
        try {
            const res = await postJson<{ success: string }>(`/admin/api/users/${user.id}/status`, csrfToken, { active: disabled, reason: reason.trim() });
            setDone(res.success);
            setOpen(false);
            setReason('');
            onChanged();
        } catch (err) {
            const e2 = err as ApiError;
            setError(e2.fields?.reason ?? e2.message);
        } finally {
            setBusy(false);
        }
    }

    if (!open) {
        return (
            <>
                <button
                    type="button"
                    className={`btn w-full ${disabled ? 'btn-primary' : 'btn-secondary text-error'}`}
                    onClick={() => {
                        setOpen(true);
                        setDone(null);
                    }}
                >
                    {disabled ? <ShieldCheck aria-hidden size={16} /> : <ShieldOff aria-hidden size={16} />}
                    {disabled ? 'فعال کردن حساب' : 'غیرفعال کردن حساب'}
                </button>
                {done && <Notice>{done}</Notice>}
            </>
        );
    }

    return (
        <form onSubmit={submit} noValidate className={`rounded-xl p-3 ${disabled ? 'bg-surface-2' : 'bg-error-soft'}`}>
            <p className={`m-0 mb-2 text-[13px] ${disabled ? 'text-body' : 'font-bold text-error'}`}>
                {disabled
                    ? 'کاربر دوباره می‌تواند وارد حسابش شود و خرید کند.'
                    : 'کاربر دیگر نمی‌تواند وارد شود و اگر الان در سایت است، خارج می‌شود. سفارش‌ها و کیف پولش دست نمی‌خورد.'}
            </p>
            <label htmlFor={`${id}-reason`} className="mb-1 block font-medium">
                دلیل {disabled && <span className="font-normal text-muted">(اختیاری)</span>}
            </label>
            <textarea
                id={`${id}-reason`}
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                rows={2}
                maxLength={500}
                className="field"
                aria-invalid={!!error}
                aria-describedby={error ? `${id}-error` : undefined}
                autoFocus
            />
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
            <div className="mt-3 flex gap-2">
                <button type="submit" className={`btn flex-1 ${disabled ? 'btn-primary' : 'btn-danger'}`} disabled={busy}>
                    {busy ? 'لطفاً صبر کنید…' : disabled ? 'فعال شود' : 'غیرفعال شود'}
                </button>
                <button type="button" className="btn btn-ghost" onClick={() => { setOpen(false); setError(null); }} disabled={busy}>
                    انصراف
                </button>
            </div>
        </form>
    );
}

function TicketAction({ user, onChanged }: { user: User; onChanged: () => void }) {
    const { csrfToken } = useBoot();
    const dialog = useRef<HTMLDialogElement>(null);
    const fileInput = useRef<HTMLInputElement>(null);
    const id = useId();
    const [subject, setSubject] = useState('');
    const [dept, setDept] = useState('other');
    const [orderId, setOrderId] = useState('');
    const [message, setMessage] = useState('');
    const [file, setFile] = useState<File | null>(null);
    const [close, setClose] = useState(false);
    const [busy, setBusy] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [failure, setFailure] = useState<string | null>(null);
    const [sent, setSent] = useState<{ text: string; ticketId: number } | null>(null);

    function reset() {
        setSubject('');
        setDept('other');
        setOrderId('');
        setMessage('');
        setFile(null);
        setClose(false);
        setErrors({});
        setFailure(null);
        if (fileInput.current) fileInput.current.value = '';
    }

    async function submit(e: FormEvent) {
        e.preventDefault();
        const local: Record<string, string> = {};
        if (!subject.trim()) local.subject = 'عنوان تیکت را بنویسید.';
        if (!message.trim()) local.message = 'متن پیام را بنویسید.';
        setErrors(local);
        if (Object.keys(local).length) return;
        setBusy(true);
        setFailure(null);
        const body = new FormData();
        body.append('subject', subject.trim());
        body.append('dept', dept);
        if (orderId) body.append('order_id', orderId);
        body.append('message', message.trim());
        if (file) body.append('file', file);
        if (close) body.append('close', '1');
        try {
            const res = await postFormData<{ success: string; ticketId: number }>(`/admin/api/users/${user.id}/ticket`, csrfToken, body);
            setSent({ text: res.success, ticketId: res.ticketId });
            dialog.current?.close();
            reset();
            onChanged();
        } catch (err) {
            const e2 = err as ApiError;
            setErrors(e2.fields ?? {});
            setFailure(Object.keys(e2.fields ?? {}).length ? null : e2.message);
        } finally {
            setBusy(false);
        }
    }

    return (
        <>
            <button
                type="button"
                className="btn btn-secondary w-full"
                onClick={() => {
                    setSent(null);
                    dialog.current?.showModal();
                }}
            >
                <Send aria-hidden size={16} className="rtl:-scale-x-100" />
                ارسال تیکت به کاربر
            </button>
            {sent && (
                <Notice>
                    {sent.text}{' '}
                    <Link to={`/tickets/${sent.ticketId}`} className="font-medium underline underline-offset-4">
                        مشاهده تیکت
                    </Link>
                </Notice>
            )}

            <dialog
                ref={dialog}
                aria-labelledby={`${id}-title`}
                className="m-auto w-[min(560px,calc(100vw-32px))] rounded-2xl border border-stroke bg-surface p-0 text-body shadow-pop backdrop:bg-black/40"
            >
                <form onSubmit={submit} noValidate className="p-5">
                    <div className="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 id={`${id}-title`} className="m-0 text-[18px] font-bold text-ink">
                                تیکت جدید برای {user.name || 'کاربر'}
                            </h2>
                            <p className="m-0 mt-1 text-[13px] text-muted">در «تیکت‌ها»ی پنل کاربر، به‌عنوان پیام پشتیبانی دیده می‌شود.</p>
                        </div>
                        <button type="button" onClick={() => dialog.current?.close()} className="grid size-9 shrink-0 place-items-center rounded-lg text-muted hover:bg-surface-2 hover:text-ink" aria-label="بستن">
                            <X aria-hidden size={18} />
                        </button>
                    </div>

                    <label htmlFor={`${id}-subject`} className="mb-1 block font-medium">
                        عنوان
                    </label>
                    <input
                        id={`${id}-subject`}
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        maxLength={150}
                        className="field"
                        aria-invalid={!!errors.subject}
                        aria-describedby={errors.subject ? `${id}-subject-error` : undefined}
                    />
                    {errors.subject && <FieldError id={`${id}-subject-error`}>{errors.subject}</FieldError>}

                    <div className="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label htmlFor={`${id}-dept`} className="mb-1 block font-medium">
                                دپارتمان
                            </label>
                            <select id={`${id}-dept`} value={dept} onChange={(e) => setDept(e.target.value)} className="field">
                                {DEPTS.map((d) => (
                                    <option key={d.value} value={d.value}>
                                        {d.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label htmlFor={`${id}-order`} className="mb-1 block font-medium">
                                سفارش مرتبط <span className="font-normal text-muted">(اختیاری)</span>
                            </label>
                            <select
                                id={`${id}-order`}
                                value={orderId}
                                onChange={(e) => setOrderId(e.target.value)}
                                className="field"
                                aria-invalid={!!errors.order_id}
                                aria-describedby={errors.order_id ? `${id}-order-error` : undefined}
                            >
                                <option value="">بدون سفارش</option>
                                {user.orders.map((o) => (
                                    <option key={o.id} value={o.id}>
                                        #{o.id}
                                        {o.service ? ` · ${o.service}` : ''}
                                    </option>
                                ))}
                            </select>
                            {errors.order_id && <FieldError id={`${id}-order-error`}>{errors.order_id}</FieldError>}
                        </div>
                    </div>

                    <label htmlFor={`${id}-message`} className="mt-3 mb-1 block font-medium">
                        متن پیام
                    </label>
                    <textarea
                        id={`${id}-message`}
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        rows={5}
                        maxLength={10000}
                        className="field"
                        aria-invalid={!!errors.message}
                        aria-describedby={errors.message ? `${id}-message-error` : undefined}
                    />
                    {errors.message && <FieldError id={`${id}-message-error`}>{errors.message}</FieldError>}

                    <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <input ref={fileInput} id={`${id}-file`} type="file" accept="image/png,image/jpeg,image/webp,application/pdf" className="sr-only" onChange={(e) => setFile(e.target.files?.[0] ?? null)} />
                            {file ? (
                                <span className="inline-flex items-center gap-1.5 rounded-lg bg-surface-2 px-2.5 py-1.5 text-[13px]">
                                    <Paperclip aria-hidden size={14} />
                                    <span className="latin max-w-[200px] truncate">{file.name}</span>
                                    <button
                                        type="button"
                                        className="text-muted hover:text-ink"
                                        aria-label="حذف پیوست"
                                        onClick={() => {
                                            setFile(null);
                                            if (fileInput.current) fileInput.current.value = '';
                                        }}
                                    >
                                        <X aria-hidden size={14} />
                                    </button>
                                </span>
                            ) : (
                                <label htmlFor={`${id}-file`} className="btn btn-ghost cursor-pointer text-[13px]">
                                    <Paperclip aria-hidden size={15} />
                                    پیوست (تصویر یا PDF تا ۲ مگابایت)
                                </label>
                            )}
                            {errors.file && <FieldError id={`${id}-file-error`}>{errors.file}</FieldError>}
                        </div>
                        <label className="flex cursor-pointer items-center gap-2 text-[13px]">
                            <input type="checkbox" checked={close} onChange={(e) => setClose(e.target.checked)} className="size-4" />
                            بسته بفرست (فقط اطلاع‌رسانی، بدون انتظار پاسخ)
                        </label>
                    </div>

                    {failure && <FieldError id={`${id}-failure`}>{failure}</FieldError>}
                    <div className="mt-5 flex justify-end gap-2">
                        <button type="button" className="btn btn-secondary" onClick={() => dialog.current?.close()} disabled={busy}>
                            انصراف
                        </button>
                        <button type="submit" className="btn btn-primary" disabled={busy}>
                            <Send aria-hidden size={16} className="rtl:-scale-x-100" />
                            {busy ? 'در حال ارسال…' : 'ارسال تیکت'}
                        </button>
                    </div>
                </form>
            </dialog>
        </>
    );
}
