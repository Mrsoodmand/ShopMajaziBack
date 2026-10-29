import { useEffect, useMemo, useRef, useState } from 'react';
import { CircleCheck, CircleSlash, RefreshCw } from 'lucide-react';
import { postJson, type ApiError } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count, toman } from '../../lib/format';
import { Field } from '../../components/form';
import { Badge } from '../../components/Status';
import { ErrorState, FieldError, Notice, Panel } from '../../components/ui';
import { API, PROVIDERS, nouns, type Codes, type Kind, type Offer, type OfferCounts, type OfferState } from './types';

/** A service icon (contained) or a country flag (4:3, filled); falls back to the first letter. */
export function Thumb({ src, name, flag, size = 32 }: { src?: string | null; name?: string | null; flag?: boolean; size?: number }) {
    const [broken, setBroken] = useState(false);
    useEffect(() => setBroken(false), [src]);
    const width = flag ? Math.round((size * 4) / 3) : size;
    return (
        <span
            aria-hidden
            className={`grid shrink-0 place-items-center overflow-hidden bg-surface-2 text-[13px] font-bold text-muted ${flag ? 'rounded-md ring-1 ring-stroke' : 'rounded-[10px]'}`}
            style={{ width, height: size }}
        >
            {src && !broken ? (
                <img src={src} alt="" loading="lazy" onError={() => setBroken(true)} className={flag ? 'size-full object-cover' : 'size-full object-contain p-1'} />
            ) : (
                (name ?? '?').replace('شماره مجازی', '').trim().charAt(0)
            )}
        </span>
    );
}

export function LiveBadge({ live }: { live: boolean }) {
    return live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />;
}

/** A provider code as text: latin, left-to-right; a dash where the provider doesn't offer it. */
export function Code({ value }: { value: string }) {
    return value ? (
        <span dir="ltr" className="latin num inline-block text-[13px]">
            {value}
        </span>
    ) : (
        <span className="text-muted">
            <span aria-hidden>—</span>
            <span className="sr-only">ندارد</span>
        </span>
    );
}

/** The four provider-code inputs. `compact` is the list's inline editor (no hints). */
export function CodeFields({ codes, onChange, errors, compact }: { codes: Codes; onChange: (codes: Codes) => void; errors: Record<string, string>; compact?: boolean }) {
    return (
        <div className={`grid gap-4 ${compact ? 'grid-cols-2 sm:grid-cols-4' : 'sm:grid-cols-2'}`}>
            {PROVIDERS.map((p) => (
                <Field
                    key={p.key}
                    label={p.server ? `سرور ${p.server} · ${p.label}` : p.label}
                    error={errors[`codes.${p.key}`]}
                    hint={!compact && !p.server ? 'فقط نگه داشته می‌شود؛ پیشنهاد فروشی از آن ساخته نمی‌شود.' : undefined}
                >
                    {(props) => (
                        <input
                            {...props}
                            dir="ltr"
                            autoComplete="off"
                            spellCheck={false}
                            className="field latin num"
                            value={codes[p.key]}
                            onChange={(e) => onChange({ ...codes, [p.key]: e.target.value })}
                            placeholder="—"
                        />
                    )}
                </Field>
            ))}
        </div>
    );
}

const stateMeta: Record<OfferState, { label: string; tone: 'success' | 'warn' | 'neutral' }> = {
    live: { label: 'در فروش', tone: 'success' },
    waiting: { label: 'منتظر قیمت', tone: 'warn' },
    gone: { label: 'حذف از سرور', tone: 'neutral' },
};

/** Provider prices: SMS-PVA quotes dollars, SMS-Man and SMS-Activate roubles. */
function providerPrice(o: Offer) {
    if (o.providerPrice == null) return null;
    return `${o.providerPrice.toLocaleString('en-US', { maximumFractionDigits: 2 })} ${o.provider === 'pva' ? '$' : '₽'}`;
}

/**
 * The offers built from a service's or a country's codes, one row per country (or service) and one column per
 * server, so the servers' prices sit side by side. Read only: prices come from the scheduled price checks.
 */
export function OffersPanel({ kind, offers, error, onRetry }: { kind: Kind; offers: Offer[] | null; error: ApiError | null; onRetry: () => void }) {
    const servers = PROVIDERS.filter((p) => p.server);
    const rows = useMemo(() => {
        const groups = new Map<string, { name: string; image: string | null; cells: Partial<Record<string, Offer>> }>();
        for (const o of offers ?? []) {
            const key = o.name;
            if (!groups.has(key)) groups.set(key, { name: o.name, image: o.image, cells: {} });
            groups.get(key)!.cells[o.provider] = o;
        }
        return [...groups.values()];
    }, [offers]);
    const tally = useMemo(() => {
        const t: Record<OfferState, number> = { live: 0, waiting: 0, gone: 0 };
        for (const o of offers ?? []) t[o.state]++;
        return t;
    }, [offers]);
    const other = nouns[kind].other;

    return (
        <Panel title="پیشنهادهای فروش">
            {error ? (
                <ErrorState error={error} onRetry={onRetry} />
            ) : !offers ? (
                <div className="space-y-2" aria-label="در حال بارگذاری">
                    {[0, 1, 2, 3].map((i) => (
                        <div key={i} className="skeleton h-10" />
                    ))}
                </div>
            ) : offers.length === 0 ? (
                <p className="py-6 text-center text-muted">
                    هنوز پیشنهادی ساخته نشده است. کد سرورها را ذخیره کنید و «بازسازی پیشنهادها» را بزنید.
                </p>
            ) : (
                <>
                    <p className="num mb-3 text-muted">
                        {count(rows.length)} {other} · {count(tally.live)} در فروش
                        {tally.waiting > 0 && <> · {count(tally.waiting)} منتظر قیمت</>}
                        {tally.gone > 0 && <> · {count(tally.gone)} حذف از سرور</>}
                        <span className="block text-[13px]">قیمت‌ها را بررسی خودکار قیمت سرورها به‌روز می‌کند و با همان گرد کردن سایت نمایش داده می‌شوند.</span>
                    </p>
                    <div className="relative -mx-5 overflow-x-auto">
                        <table className="w-full min-w-[560px]">
                            <thead>
                                <tr className="border-y border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-2.5 ps-5 pe-3 text-start font-medium">{other}</th>
                                    {servers.map((s) => (
                                        <th key={s.key} scope="col" className="px-3 text-start font-medium">
                                            سرور {s.server}
                                            <span className="latin block text-[12px] font-normal">{s.label}</span>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {rows.map((r) => (
                                    <tr key={r.name} className="border-b border-stroke last:border-0">
                                        <th scope="row" className="py-2.5 ps-5 pe-3 text-start font-medium">
                                            <span className="flex items-center gap-2.5">
                                                <Thumb src={r.image} name={r.name} flag={kind === 'services'} size={24} />
                                                {r.name}
                                            </span>
                                        </th>
                                        {servers.map((s) => {
                                            const o = r.cells[s.key];
                                            if (!o) {
                                                return (
                                                    <td key={s.key} className="px-3 text-muted">
                                                        <span aria-hidden>—</span>
                                                        <span className="sr-only">ساخته نشده</span>
                                                    </td>
                                                );
                                            }
                                            const meta = stateMeta[o.state];
                                            const sub = providerPrice(o);
                                            return (
                                                <td key={s.key} className="px-3 py-2 align-top">
                                                    <span className={`num block font-medium ${o.state === 'live' ? '' : 'text-muted'}`}>
                                                        {o.price != null ? toman(o.price) : 'بدون قیمت'}
                                                    </span>
                                                    <span className="flex flex-wrap items-center gap-x-2 text-[12px] text-muted">
                                                        {o.state !== 'live' && (
                                                            <span className={meta.tone === 'warn' ? 'font-medium text-warn' : 'font-medium'}>{meta.label}</span>
                                                        )}
                                                        {sub && (
                                                            <span dir="ltr" className="latin num" title={o.updatedAt ? `به‌روزرسانی ${o.updatedAt}` : undefined}>
                                                                {sub}
                                                            </span>
                                                        )}
                                                    </span>
                                                </td>
                                            );
                                        })}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </Panel>
    );
}

/**
 * «بازسازی پیشنهادها» in an editor's side panel: builds (or re-codes) this item's offers on every matching
 * service or country. Asks for confirmation inline first; prices and sale status are never touched.
 */
export function RebuildOffers({ kind, id, counts, unsaved, onDone }: { kind: Kind; id: number; counts: OfferCounts | null; unsaved: boolean; onDone: () => void }) {
    const { csrfToken } = useBoot();
    const [step, setStep] = useState<'idle' | 'confirm'>('idle');
    const [busy, setBusy] = useState(false);
    const [done, setDone] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const confirmRef = useRef<HTMLButtonElement>(null);
    const n = nouns[kind];

    useEffect(() => {
        if (step === 'confirm') confirmRef.current?.focus();
    }, [step]);

    async function run() {
        setBusy(true);
        setError(null);
        try {
            const res = await postJson<{ success: string }>(`${API}/${kind}/${id}/rebuild`, csrfToken, {});
            setDone(res.success);
            setStep('idle');
            onDone();
        } catch (e) {
            setError((e as Error).message);
        } finally {
            setBusy(false);
        }
    }

    return (
        <div className="border-t border-stroke pt-4">
            <h2 className="font-bold">پیشنهادهای فروش</h2>
            <p className="num mt-0.5 text-muted">{counts ? `${count(counts.live)} از ${count(counts.total)} در فروش` : '…'}</p>
            {step === 'confirm' ? (
                <div role="group" aria-label="تأیید بازسازی" className="mt-3 rounded-xl bg-warn-soft px-4 py-3">
                    <p className="text-[13px] text-body">
                        برای این {n.one} در هر {n.other}ی که همان سرور را دارد، ردیف فروش ساخته یا کدش به‌روز می‌شود. قیمت‌ها و وضعیت فروش دست نمی‌خورند؛ ردیف‌های تازه تا رسیدن اولین قیمت در سایت دیده نمی‌شوند.
                    </p>
                    {unsaved && <p className="mt-2 text-[13px] font-bold text-warn">کدهای تغییرکرده هنوز ذخیره نشده‌اند و در این بازسازی به کار نمی‌روند.</p>}
                    <div className="mt-3 flex gap-2">
                        <button ref={confirmRef} type="button" className="btn btn-primary flex-1" onClick={run} disabled={busy}>
                            {busy ? 'در حال بازسازی…' : 'بازسازی'}
                        </button>
                        <button type="button" className="btn btn-secondary" onClick={() => setStep('idle')} disabled={busy}>
                            انصراف
                        </button>
                    </div>
                </div>
            ) : (
                <button
                    type="button"
                    className="btn btn-secondary mt-3 w-full"
                    onClick={() => {
                        setDone(null);
                        setError(null);
                        setStep('confirm');
                    }}
                >
                    <RefreshCw aria-hidden size={15} />
                    بازسازی پیشنهادها
                </button>
            )}
            {error && <FieldError id={`rebuild-${id}-error`}>{error}</FieldError>}
            {done && <Notice>{done}</Notice>}
        </div>
    );
}
