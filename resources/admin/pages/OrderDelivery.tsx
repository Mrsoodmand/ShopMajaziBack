import { useId, useState, type FormEvent } from 'react';
import { Link } from 'react-router';
import { Eye, EyeOff } from 'lucide-react';
import { ApiError, postJson } from '../lib/api';
import { useBoot } from '../lib/boot';
import { CopyButton, FieldError, Notice, Row } from '../components/ui';
import type { Order } from './OrderDetail';

type DeliveryRow = { label: string; value: string | null; copy?: boolean; ltr?: boolean; secret?: boolean };

/** What App\Services\OrderDelivery::for() returns for order types 3–6. */
export type Delivery = {
    kind: 'apple' | 'ip' | 'gv';
    items: { id: number; title: string; link: string | null; rows: DeliveryRow[] }[];
    canCompleteAppleId: boolean;
};

const KIND = {
    apple: { title: 'اپل آیدی تحویل‌شده', empty: 'هنوز اپل آیدی‌ای به این سفارش داده نشده است.', open: 'صفحه اپل آیدی' },
    gv: { title: 'گوگل ویس تحویل‌شده', empty: 'هنوز شماره گوگل ویسی به این سفارش داده نشده است.', open: 'صفحه گوگل ویس' },
    ip: { title: 'آی پی ثابت تحویل‌شده', empty: 'هنوز کانفیگی برای این سفارش ساخته نشده است.', open: 'صفحه لوکیشن' },
};

/** admin › order › items: the logins / config handed to the customer, and finishing a custom Apple ID order. */
export function DeliveryBlock({ order, delivery, onChanged }: { order: Order; delivery: Delivery; onChanged: () => void }) {
    const k = KIND[delivery.kind];
    return (
        <section aria-label={k.title} className="rounded-xl border border-stroke p-4">
            <h3 className="m-0 mb-2 font-bold">{k.title}</h3>
            {delivery.items.length === 0 && !delivery.canCompleteAppleId && <p className="m-0 text-muted">{k.empty}</p>}
            <div className="space-y-4">
                {delivery.items.map((item) => (
                    <div key={item.id}>
                        {delivery.items.length > 1 && <p className="latin m-0 mb-1 text-[13px] font-bold text-body">{item.title}</p>}
                        <dl className="m-0">
                            {item.rows.map((row) => (
                                <DeliveryValue key={row.label} row={row} />
                            ))}
                        </dl>
                        {item.link && (
                            <Link to={item.link} className="mt-2 inline-block text-[13px] font-medium text-indigo underline-offset-4 hover:underline">
                                {k.open}
                            </Link>
                        )}
                    </div>
                ))}
            </div>
            {delivery.canCompleteAppleId && <CompleteAppleId orderId={order.id} onChanged={onChanged} />}
        </section>
    );
}

function DeliveryValue({ row }: { row: DeliveryRow }) {
    const [shown, setShown] = useState(!row.secret);
    if (!row.value) {
        return (
            <Row label={row.label}>
                <span className="font-normal text-muted">ثبت نشده</span>
            </Row>
        );
    }
    return (
        <Row label={row.label} ltr={row.ltr}>
            <span className={`select-all ${row.ltr ? 'latin' : ''}`}>{shown ? row.value : '••••••'}</span>
            {row.secret && (
                <button
                    type="button"
                    onClick={() => setShown(!shown)}
                    className="ms-1 inline-grid size-7 place-items-center rounded-md align-middle text-muted hover:bg-surface-2 hover:text-ink"
                    aria-label={shown ? `پنهان کردن ${row.label}` : `نمایش ${row.label}`}
                >
                    {shown ? <EyeOff aria-hidden size={14} /> : <Eye aria-hidden size={14} />}
                </button>
            )}
            {row.copy && <CopyButton value={row.value} label={row.label} />}
        </Row>
    );
}

const APPLE_FIELDS = [
    { key: 'appleid', label: 'اپل آیدی (ایمیل)', ltr: true },
    { key: 'password', label: 'کلمه عبور', ltr: true },
    { key: 'brithday', label: 'تاریخ تولد', ltr: true },
    { key: 'q1', label: 'جواب سوال امنیتی ۱' },
    { key: 'q2', label: 'جواب سوال امنیتی ۲' },
    { key: 'q3', label: 'جواب سوال امنیتی ۳' },
] as const;

/** Custom Apple ID (type 4): support made the account; typing its login in here completes the order and texts the customer. */
function CompleteAppleId({ orderId, onChanged }: { orderId: number; onChanged: () => void }) {
    const { csrfToken } = useBoot();
    const id = useId();
    const [values, setValues] = useState<Record<string, string>>({});
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [failure, setFailure] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const [done, setDone] = useState<string | null>(null);

    async function submit(e: FormEvent) {
        e.preventDefault();
        const local: Record<string, string> = {};
        for (const f of APPLE_FIELDS) if (!(values[f.key] ?? '').trim()) local[f.key] = `${f.label} را بنویسید.`;
        setErrors(local);
        if (Object.keys(local).length) return;
        setBusy(true);
        setFailure(null);
        try {
            const res = await postJson<{ success: string }>(`/admin/api/orders/${orderId}/apple-id`, csrfToken, values);
            setDone(res.success);
            onChanged();
        } catch (err) {
            const e2 = err as ApiError;
            setErrors(e2.fields ?? {});
            setFailure(Object.keys(e2.fields ?? {}).length ? null : e2.message);
        } finally {
            setBusy(false);
        }
    }

    if (done) return <Notice>{done}</Notice>;
    return (
        <form onSubmit={submit} noValidate className="mt-1">
            <p className="m-0 mb-3 text-[13px] text-body">
                اپل آیدی را بسازید و اطلاعاتش را این‌جا وارد کنید. سفارش تکمیل می‌شود و به کاربر پیامک می‌رود.
            </p>
            <div className="grid gap-3 sm:grid-cols-2">
                {APPLE_FIELDS.map((f) => (
                    <div key={f.key}>
                        <label htmlFor={`${id}-${f.key}`} className="mb-1 block font-medium">
                            {f.label}
                        </label>
                        <input
                            id={`${id}-${f.key}`}
                            value={values[f.key] ?? ''}
                            onChange={(e) => setValues({ ...values, [f.key]: e.target.value })}
                            dir={'ltr' in f && f.ltr ? 'ltr' : undefined}
                            autoComplete="off"
                            className="field"
                            aria-invalid={!!errors[f.key]}
                            aria-describedby={errors[f.key] ? `${id}-${f.key}-error` : undefined}
                        />
                        {errors[f.key] && <FieldError id={`${id}-${f.key}-error`}>{errors[f.key]}</FieldError>}
                    </div>
                ))}
            </div>
            {failure && <FieldError id={`${id}-failure`}>{failure}</FieldError>}
            <button type="submit" className="btn btn-primary mt-4" disabled={busy}>
                {busy ? 'در حال ثبت…' : 'ثبت اپل آیدی و تکمیل سفارش'}
            </button>
        </form>
    );
}
