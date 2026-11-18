import { useEffect, useState } from 'react';
import { RefreshCw } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { jalali } from '../../lib/dates';
import { count, parseAmount, parseDecimal } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { ErrorState, Panel } from '../../components/ui';

// Admin › ارزها و سود (Admin\PricingApiController, App\Services\Pricing). Every price is a market rate plus a profit:
// the dollar's profit for subscriptions and services (an account can have its own, in its editor), and each
// operator's profit for virtual numbers. Market rates come from AriaPay every 30 minutes, unless a currency is set to
// manual: then the rate typed here stays until it is switched back to automatic.

type CurrencyRow = {
    id: number; name: string; code: string; auto: boolean; usedFor: string | null; market: number; profit: number; selling: number; updatedAt: string | null;
    mode: 'auto' | 'manual'; modeAt: string | null; feed: number | null; // feed: AriaPay's latest rate
};
type RateEdit = { mode: 'auto' | 'manual'; rate: string };
// A manual rate this far (percent) from the current one asks for confirmation (the server checks it too).
const CONFIRM_OVER = 40;
type OperatorRow = { key: string; label: string; currency: string; market: number; profit: number; rate: number; live: number };
type State = { currencies: CurrencyRow[]; operators: OperatorRow[] };

const API = '/admin/api/pricing';

// "12.50" → "12.5"; profits are edited as text so a half-typed value ("1.") isn't rewritten.
const asText = (n: number) => String(Number(n.toFixed(2)));
const withProfit = (market: number, profit: string) => {
    const p = parseDecimal(profit);
    return p === null ? null : Math.round(market * (1 + p / 100));
};

export default function PricingPage() {
    const state = useQuery<State>(API);
    const save = useSave();
    const [data, setData] = useState<State | null>(null);
    const [currencies, setCurrencies] = useState<Record<number, string>>({});
    const [operators, setOperators] = useState<Record<string, string>>({});
    const [rates, setRates] = useState<Record<number, RateEdit>>({});
    const [refreshing, setRefreshing] = useState(false);
    const [refreshNote, setRefreshNote] = useState<{ ok: boolean; text: string } | null>(null);

    const load = (s: State) => {
        setData(s);
        setCurrencies(Object.fromEntries(s.currencies.map((c) => [c.id, asText(c.profit)])));
        setOperators(Object.fromEntries(s.operators.map((o) => [o.key, asText(o.profit)])));
        setRates(Object.fromEntries(s.currencies.map((c) => [c.id, { mode: c.mode, rate: String(c.market) }])));
    };
    const setRate = (id: number, p: Partial<RateEdit>) => {
        save.clearSaved();
        setRates((r) => ({ ...r, [id]: { ...r[id], ...p } }));
    };
    // The market rate a row works with while editing: the typed one on manual, the current one on automatic.
    const marketOf = (c: CurrencyRow) => (rates[c.id]?.mode === 'manual' ? parseAmount(rates[c.id].rate) ?? c.market : c.market);
    useEffect(() => {
        if (state.data) load(state.data);
    }, [state.data]);

    async function submit() {
        if (!data) return;
        // Big jumps in a manual rate: say so and ask before saving.
        const jumps = data.currencies.filter((c) => {
            const r = rates[c.id];
            const n = r?.mode === 'manual' ? parseAmount(r.rate) : null;
            return n && c.market > 0 && n !== c.market && (Math.abs(n - c.market) / c.market) * 100 > CONFIRM_OVER;
        });
        const confirm = jumps.length > 0;
        if (confirm && !window.confirm(jumps.map((c) => `${c.name}: ${count(c.market)} ← ${count(parseAmount(rates[c.id].rate) ?? 0)} تومان`).join('\n') + `\n\nاین نرخ بیش از ${CONFIRM_OVER}٪ با نرخ فعلی فرق دارد و همه قیمت‌های این ارز تغییر می‌کند. ذخیره شود؟`)) return;
        const res = await save.run<{ success: string; repriced: number } & State>(API, {
            confirm,
            rates: Object.fromEntries(data.currencies.map((c) => {
                const r = rates[c.id] ?? { mode: c.mode, rate: String(c.market) };
                return [c.id, { mode: r.mode, rate: r.mode === 'manual' ? parseAmount(r.rate) ?? r.rate : null }];
            })),
            currencies: Object.fromEntries(Object.entries(currencies).map(([id, v]) => [id, parseDecimal(v) ?? v])),
            operators: Object.fromEntries(Object.entries(operators).map(([k, v]) => [k, parseDecimal(v) ?? v])),
        });
        if (res) load(res);
    }

    async function refresh() {
        setRefreshing(true);
        setRefreshNote(null);
        const res = await save.run<{ success?: string; message?: string } & State>(`${API}/refresh`, {});
        if (res) {
            load(res);
            setRefreshNote({ ok: true, text: res.success ?? 'نرخ‌ها به‌روز شد.' });
        } else {
            setRefreshNote({ ok: false, text: 'نرخ‌ها به‌روز نشد. پیام بالای صفحه را ببینید.' });
        }
        setRefreshing(false);
    }

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!data) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-xl" />
            </div>
        );
    }

    const err = (k: string) => save.errors[k];
    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => {
            const [kind, id] = k.split('.');
            const cur = data.currencies.find((c) => String(c.id) === id);
            const label = kind === 'rates' ? (cur ? `نرخ ${cur.name}` : null) : kind === 'currencies' ? (cur ? `سود ${cur.name}` : null) : data.operators.find((o) => o.key === id)?.label;
            return { label: label ?? k, message };
        }),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];
    const dollar = data.currencies.find((c) => c.code === 'USD');
    const lastUpdate = data.currencies.filter((c) => c.auto && c.mode === 'auto' && c.updatedAt).map((c) => c.updatedAt!).sort().pop();

    return (
        <EditorLayout
            backTo="/"
            backLabel="داشبورد"
            title="ارزها و سود"
            subtitle="نرخ بازار هر ارز خودکار از AriaPay می‌آید یا آن را دستی می‌نویسید؛ قیمت فروش = نرخ بازار + درصد سود شما."
            onSubmit={submit}
            saving={save.saving && !refreshing}
            saveLabel="ذخیره نرخ‌ها و سودها"
            saved={refreshing ? null : save.saved}
            problems={problems}
            tabbed={false}
            side={
                <div className="space-y-3">
                    <dl className="m-0 space-y-1">
                        <div className="flex items-baseline justify-between gap-3">
                            <dt className="text-muted">آخرین نرخ</dt>
                            <dd className="num m-0">{lastUpdate ? jalali(new Date(lastUpdate)) + ' ' + new Date(lastUpdate).toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Tehran' }) : '—'}</dd>
                        </div>
                        {dollar && (
                            <div className="flex items-baseline justify-between gap-3">
                                <dt className="text-muted">دلار فروش</dt>
                                <dd className="num m-0 font-bold">{count(withProfit(marketOf(dollar), currencies[dollar.id] ?? '') ?? dollar.selling)}</dd>
                            </div>
                        )}
                    </dl>
                    <button type="button" className="btn btn-secondary w-full" onClick={refresh} disabled={refreshing}>
                        <RefreshCw aria-hidden className={`size-4 ${refreshing ? 'animate-spin' : ''}`} />
                        {refreshing ? 'در حال دریافت…' : 'دریافت نرخ‌ها از AriaPay'}
                    </button>
                    {refreshNote && <p role="status" className={`m-0 text-[13px] ${refreshNote.ok ? 'text-muted' : 'text-error'}`}>{refreshNote.text}</p>}
                    <p className="m-0 text-[13px] text-muted">نرخ‌های «خودکار» هر ۳۰ دقیقه به‌روز می‌شوند و قیمت شماره‌ها همان لحظه دوباره حساب می‌شود. ارزهای «دستی» تا وقتی دوباره خودکار شوند، همان نرخی را که نوشته‌اید نگه می‌دارند.</p>
                </div>
            }
        >
            <Panel title="ارزها">
                <p className="mb-3 text-muted">سود دلار برای همه اشتراک‌ها و خدمات مجازی است، مگر اکانتی که در ویرایشگرش «سود اختصاصی» داشته باشد. سود شماره مجازی جداست (پایین).</p>
                <div className="relative -mx-5 overflow-x-auto">
                    <table className="w-full min-w-[720px]">
                        <thead>
                            <tr className="border-y border-stroke text-[13px] text-muted">
                                <th scope="col" className="py-2.5 ps-5 pe-3 text-start font-medium">ارز</th>
                                <th scope="col" className="px-3 text-end font-medium">نرخ بازار (تومان)</th>
                                <th scope="col" className="px-3 text-start font-medium">سود (٪)</th>
                                <th scope="col" className="px-3 text-end font-medium">نرخ فروش (تومان)</th>
                                <th scope="col" className="py-2.5 ps-3 pe-5 text-start font-medium">کاربرد</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.currencies.map((c) => {
                                const selling = withProfit(marketOf(c), currencies[c.id] ?? '');
                                const e = err(`currencies.${c.id}`);
                                const re = err(`rates.${c.id}.rate`);
                                const r = rates[c.id] ?? { mode: c.mode, rate: String(c.market) };
                                const manual = r.mode === 'manual';
                                return (
                                    <tr key={c.id} className="border-b border-stroke align-top last:border-0">
                                        <th scope="row" className="py-3 ps-5 pe-3 text-start font-medium">
                                            {c.name} <span className="num font-normal text-muted">{c.code}</span>
                                            {c.auto ? (
                                                <div role="radiogroup" aria-label={`نرخ ${c.name}`} className="mt-1.5 inline-flex rounded-lg border border-stroke p-0.5 text-[12px] font-normal">
                                                    {(['auto', 'manual'] as const).map((m) => (
                                                        <button key={m} type="button" role="radio" aria-checked={r.mode === m} onClick={() => setRate(c.id, { mode: m, rate: m === 'manual' && !manual ? String(c.market) : r.rate })}
                                                            className={`rounded-md px-2.5 py-1 ${r.mode === m ? 'bg-primary text-white' : 'text-muted hover:text-body'}`}>
                                                            {m === 'auto' ? 'خودکار' : 'دستی'}
                                                        </button>
                                                    ))}
                                                </div>
                                            ) : (
                                                <div className="text-[12px] font-normal text-muted">دستی (AriaPay این ارز را ندارد)</div>
                                            )}
                                            {c.auto && c.mode === 'manual' && c.modeAt && (
                                                <div className="num mt-1 text-[11px] font-normal text-muted">دستی از {jalali(new Date(c.modeAt))}</div>
                                            )}
                                        </th>
                                        <td className="num px-3 py-2 text-end">
                                            {manual ? (
                                                <>
                                                    <input
                                                        aria-label={`نرخ بازار ${c.name} (تومان)`}
                                                        aria-invalid={re ? true : undefined}
                                                        inputMode="numeric"
                                                        dir="ltr"
                                                        className={`field num w-32 text-end ${re ? '!border-error' : ''}`}
                                                        value={r.rate}
                                                        onChange={(ev) => setRate(c.id, { rate: ev.target.value })}
                                                    />
                                                    {re && <p className="mt-1 text-start text-[12px] text-error">{re}</p>}
                                                </>
                                            ) : (
                                                <span className="inline-block py-1">{count(c.market)}</span>
                                            )}
                                            {c.auto && c.feed != null && (manual || c.feed !== c.market) && (
                                                <div className="mt-1 text-[11px] text-muted">AriaPay: {count(c.feed)}</div>
                                            )}
                                            {c.auto && c.mode === 'manual' && !manual && (
                                                <div className="mt-1 text-[11px] text-muted">با ذخیره، نرخ AriaPay گرفته می‌شود</div>
                                            )}
                                        </td>
                                        <td className="px-3 py-2">
                                            <input
                                                aria-label={`سود ${c.name} (درصد)`}
                                                aria-invalid={e ? true : undefined}
                                                inputMode="decimal"
                                                dir="ltr"
                                                className={`field num w-24 text-end ${e ? '!border-error' : ''}`}
                                                value={currencies[c.id] ?? ''}
                                                onChange={(ev) => {
                                                    save.clearSaved();
                                                    setCurrencies({ ...currencies, [c.id]: ev.target.value });
                                                }}
                                            />
                                            {e && <p className="mt-1 text-[12px] text-error">{e}</p>}
                                        </td>
                                        <td className="num px-3 py-3 text-end font-bold">{selling === null ? '—' : count(selling)}</td>
                                        <td className="py-3 ps-3 pe-5 text-[13px] text-muted">{c.usedFor ?? 'فعلاً در قیمت‌ها استفاده نمی‌شود'}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </Panel>

            <Panel title="سود شماره مجازی (هر سرور)">
                <p className="mb-3 text-muted">قیمت هر شماره = هزینه سرور × نرخ بازار ارز آن سرور × (۱ + سود). با ذخیره، قیمت همه شماره‌های آن سرور همان لحظه دوباره حساب می‌شود.</p>
                <div className="relative -mx-5 overflow-x-auto">
                    <table className="w-full min-w-[720px]">
                        <thead>
                            <tr className="border-y border-stroke text-[13px] text-muted">
                                <th scope="col" className="py-2.5 ps-5 pe-3 text-start font-medium">سرور</th>
                                <th scope="col" className="px-3 text-end font-medium">ارز هزینه</th>
                                <th scope="col" className="px-3 text-start font-medium">سود (٪)</th>
                                <th scope="col" className="px-3 text-end font-medium">هر واحد هزینه (تومان)</th>
                                <th scope="col" className="py-2.5 ps-3 pe-5 text-end font-medium">شماره در فروش</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.operators.map((o) => {
                                const rate = withProfit(o.market, operators[o.key] ?? '');
                                const e = err(`operators.${o.key}`);
                                return (
                                    <tr key={o.key} className="border-b border-stroke align-top last:border-0">
                                        <th scope="row" className="py-3 ps-5 pe-3 text-start font-medium">{o.label}</th>
                                        <td className="num px-3 py-3 text-end">
                                            {o.currency} <span className="text-muted">({count(o.market)})</span>
                                        </td>
                                        <td className="px-3 py-2">
                                            <input
                                                aria-label={`سود ${o.label} (درصد)`}
                                                aria-invalid={e ? true : undefined}
                                                inputMode="decimal"
                                                dir="ltr"
                                                className={`field num w-24 text-end ${e ? '!border-error' : ''}`}
                                                value={operators[o.key] ?? ''}
                                                onChange={(ev) => {
                                                    save.clearSaved();
                                                    setOperators({ ...operators, [o.key]: ev.target.value });
                                                }}
                                            />
                                            {e && <p className="mt-1 text-[12px] text-error">{e}</p>}
                                        </td>
                                        <td className="num px-3 py-3 text-end font-bold">{rate === null ? '—' : count(rate)}</td>
                                        <td className="num py-3 ps-3 pe-5 text-end">{count(o.live)}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
                <p className="mt-3 text-[13px] text-muted">مثال: سود ۱۰۰٪ یعنی قیمت فروش دو برابر هزینه سرور است (همان ضریب ×۲ قبلی).</p>
            </Panel>
        </EditorLayout>
    );
}
