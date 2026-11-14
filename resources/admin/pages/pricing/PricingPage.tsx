import { useEffect, useState } from 'react';
import { RefreshCw } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { jalali } from '../../lib/dates';
import { count, parseDecimal } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { ErrorState, Panel } from '../../components/ui';

// Admin › ارزها و سود (Admin\PricingApiController, App\Services\Pricing). Every price is a market rate plus a profit:
// the dollar's profit for subscriptions and services (an account can have its own, in its editor), and each
// operator's profit for virtual numbers. Market rates come from AriaPay every 30 minutes.

type CurrencyRow = { id: number; name: string; code: string; auto: boolean; usedFor: string | null; market: number; profit: number; selling: number; updatedAt: string | null };
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
    const [refreshing, setRefreshing] = useState(false);
    const [refreshNote, setRefreshNote] = useState<{ ok: boolean; text: string } | null>(null);

    const load = (s: State) => {
        setData(s);
        setCurrencies(Object.fromEntries(s.currencies.map((c) => [c.id, asText(c.profit)])));
        setOperators(Object.fromEntries(s.operators.map((o) => [o.key, asText(o.profit)])));
    };
    useEffect(() => {
        if (state.data) load(state.data);
    }, [state.data]);

    async function submit() {
        const res = await save.run<{ success: string; repriced: number } & State>(API, {
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
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }

    const err = (k: string) => save.errors[k];
    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => {
            const [kind, id] = k.split('.');
            const label = kind === 'currencies' ? data.currencies.find((c) => String(c.id) === id)?.name : data.operators.find((o) => o.key === id)?.label;
            return { label: label ? `سود ${label}` : k, message };
        }),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];
    const dollar = data.currencies.find((c) => c.code === 'USD');
    const lastUpdate = data.currencies.filter((c) => c.auto && c.updatedAt).map((c) => c.updatedAt!).sort().pop();

    return (
        <EditorLayout
            backTo="/"
            backLabel="داشبورد"
            title="ارزها و سود"
            subtitle="نرخ بازار هر ارز خودکار از AriaPay می‌آید؛ قیمت فروش = نرخ بازار + درصد سود شما."
            onSubmit={submit}
            saving={save.saving && !refreshing}
            saveLabel="ذخیره سودها"
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
                                <dd className="num m-0 font-bold">{count(withProfit(dollar.market, currencies[dollar.id] ?? '') ?? dollar.selling)}</dd>
                            </div>
                        )}
                    </dl>
                    <button type="button" className="btn btn-secondary w-full" onClick={refresh} disabled={refreshing}>
                        <RefreshCw aria-hidden className={`size-4 ${refreshing ? 'animate-spin' : ''}`} />
                        {refreshing ? 'در حال دریافت…' : 'دریافت نرخ‌ها از AriaPay'}
                    </button>
                    {refreshNote && <p role="status" className={`m-0 text-[13px] ${refreshNote.ok ? 'text-muted' : 'text-error'}`}>{refreshNote.text}</p>}
                    <p className="m-0 text-[13px] text-muted">نرخ‌ها هر ۳۰ دقیقه خودکار به‌روز می‌شوند و قیمت شماره‌ها همان لحظه دوباره حساب می‌شود.</p>
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
                                const selling = withProfit(c.market, currencies[c.id] ?? '');
                                const e = err(`currencies.${c.id}`);
                                return (
                                    <tr key={c.id} className="border-b border-stroke align-top last:border-0">
                                        <th scope="row" className="py-3 ps-5 pe-3 text-start font-medium">
                                            {c.name} <span className="num font-normal text-muted">{c.code}</span>
                                            <div className="text-[12px] font-normal text-muted">{c.auto ? 'خودکار از AriaPay' : 'دستی'}</div>
                                        </th>
                                        <td className="num px-3 py-3 text-end">{count(c.market)}</td>
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
