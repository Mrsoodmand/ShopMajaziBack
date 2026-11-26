import { useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count, toman } from '../../lib/format';
import { Switch } from '../../components/form';
import { ErrorState, Notice, Panel } from '../../components/ui';

// Admin › معرفی دوستان (super-admin; Admin\ReferralApiController, App\Services\Referrals): the on/off switch and the
// commission percent per service. A referrer can get their own percents on admin › user › معرفی‌شده‌ها.

export type Family = { key: string; label: string };
type Data = {
    settings: { enabled: boolean; percents: Record<string, number> };
    families: Family[];
    stats: { referred: number; referrers: number; paid: number; custom: number };
    recent: { id: number; orderId: number | null; price: number; status: string; note: string | null; user: { id: number; name: string | null; mobile: string | null } | null; at: string | null }[];
};

const API = '/admin/api/referral';

/** 7.5 → "7.5", 10 → "10" (what the box shows). */
export const pctText = (n: number | undefined | null) => (n == null ? '' : String(Math.round(n * 100) / 100));

/** One row per service: label, a small percent box. */
export function PercentRows({ families, values, onChange, errors, placeholders, prefix }: { families: Family[]; values: Record<string, string>; onChange: (key: string, value: string) => void; errors: Record<string, string>; placeholders?: Record<string, string>; prefix: string }) {
    return (
        <div className="divide-y divide-stroke">
            {families.map((f) => {
                const error = errors[`${prefix}.${f.key}`];
                const id = `${prefix}-${f.key}`;
                return (
                    <div key={f.key} className="py-2.5">
                        <div className="flex items-center justify-between gap-4">
                            <label htmlFor={id} className="font-medium">
                                {f.label}
                            </label>
                            <div className="flex items-center gap-1.5">
                                <input
                                    id={id}
                                    dir="ltr"
                                    inputMode="decimal"
                                    className="field num w-24 text-center"
                                    value={values[f.key] ?? ''}
                                    placeholder={placeholders?.[f.key]}
                                    onChange={(e) => onChange(f.key, e.target.value)}
                                    aria-invalid={error ? true : undefined}
                                    aria-describedby={error ? `${id}-error` : undefined}
                                />
                                <span className="text-muted">٪</span>
                            </div>
                        </div>
                        {error && (
                            <p id={`${id}-error`} className="mt-1 text-[13px] font-bold text-error">
                                {error}
                            </p>
                        )}
                    </div>
                );
            })}
        </div>
    );
}

export default function ReferralPage() {
    const { data, error, reload } = useQuery<Data>(API);
    const save = useSave();
    const [enabled, setEnabled] = useState(false);
    const [percents, setPercents] = useState<Record<string, string>>({});

    useEffect(() => {
        if (!data) return;
        setEnabled(data.settings.enabled);
        setPercents(Object.fromEntries(data.families.map((f) => [f.key, pctText(data.settings.percents[f.key])])));
    }, [data]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <div className="skeleton mx-auto h-96 max-w-[960px] rounded-xl" />;

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (await save.run(API, { enabled, percents })) reload();
    }

    const s = data.stats;
    return (
        <div className="mx-auto grid max-w-[960px] gap-5">
            <div>
                <h1 className="text-xl font-bold">معرفی دوستان</h1>
                <p className="mt-1 text-muted">هر کاربر یک کد معرف دارد. وقتی سفارش کسی که با این کد ثبت‌نام کرده تکمیل شود، درصدی از مبلغ سفارش (بدون ارزش افزوده) به موجودی مسدود معرف اضافه می‌شود.</p>
            </div>

            <dl className="m-0 grid grid-cols-2 gap-3 sm:grid-cols-4">
                {[
                    ['معرفی‌شده', count(s.referred)],
                    ['معرف', count(s.referrers)],
                    ['پورسانت پرداخت‌شده', toman(s.paid)],
                    ['پورسانت اختصاصی', count(s.custom)],
                ].map(([label, value]) => (
                    <div key={label} className="rounded-xl border border-stroke bg-surface px-4 py-3 shadow-card">
                        <dt className="text-[13px] text-muted">{label}</dt>
                        <dd className="num m-0 mt-1 text-[17px] font-bold">{value}</dd>
                    </div>
                ))}
            </dl>

            <form onSubmit={submit} className="grid gap-5" noValidate>
                <Panel title="تنظیمات">
                    <Switch
                        label="معرفی دوستان فعال باشد"
                        hint="خاموش: کادر کد معرف در ثبت‌نام و کارت معرفی در داشبورد کاربر دیده نمی‌شود و پورسانتی پرداخت نمی‌شود."
                        checked={enabled}
                        onChange={setEnabled}
                    />
                </Panel>
                <Panel title="پورسانت هر سرویس">
                    <p className="mb-2 text-[13px] text-muted">درصدی که معرف از هر سفارش تکمیل‌شده می‌گیرد. ۰ یعنی این سرویس پورسانت ندارد. برای یک کاربر خاص، از صفحه همان کاربر › معرفی‌شده‌ها درصد جدا بگذارید.</p>
                    <PercentRows families={data.families} values={percents} onChange={(k, v) => setPercents((p) => ({ ...p, [k]: v }))} errors={save.errors} prefix="percents" />
                </Panel>
                {save.failure && <p role="alert" className="font-medium text-error">{save.failure}</p>}
                {save.saved && <Notice>{save.saved}</Notice>}
                <div>
                    <button type="submit" className="btn btn-primary" disabled={save.saving}>
                        {save.saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات'}
                    </button>
                </div>
            </form>

            <Panel title="آخرین پورسانت‌ها">
                {data.recent.length === 0 ? (
                    <p className="text-muted">هنوز پورسانتی پرداخت نشده است.</p>
                ) : (
                    <ul className="-mx-5 m-0 list-none divide-y divide-stroke p-0">
                        {data.recent.map((p) => (
                            <li key={p.id} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3">
                                <span className="min-w-0 flex-1">
                                    {p.user ? (
                                        <Link to={`/users/${p.user.id}`} className="font-medium hover:underline">
                                            {p.user.name || p.user.mobile || 'بدون نام'}
                                        </Link>
                                    ) : (
                                        '—'
                                    )}
                                    {p.note && <span className="block truncate text-[13px] text-muted">{p.note}</span>}
                                </span>
                                {p.orderId && (
                                    <Link to={`/orders/${p.orderId}`} className="num text-[13px] text-muted hover:underline">
                                        سفارش {p.orderId}
                                    </Link>
                                )}
                                <span className={`num font-medium ${p.status === 'Complete' ? 'text-success' : 'text-muted line-through'}`}>{toman(p.price)}</span>
                                <span dir="ltr" className="num text-[13px] text-muted">
                                    {p.at}
                                </span>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>
        </div>
    );
}
