import { useEffect, useState } from 'react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count, parseAmount, parseDecimal, roundToman, toman } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, RowsEditor, Switch } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { PriceReceipt, usd } from '../../components/PriceReceipt';

// Admin › خدمات مجازی › تلگرام استارز (Admin\TelegramStarsController, App\Services\TelegramStars): the dollar price per
// Star, the amounts a customer may type on /telegram/stars, ready plans, and on/off. Every price is Stars × price per
// Star × the services dollar rate, rounded like the storefront. Orders wait in admin › orders; support sends the
// Stars and completes the order.

// profit: the plan's own profit on the market dollar ('' = the services rate; Pricing::planRate).
type PlanRow = { id?: number; name: string; count: string; profit: string; live: boolean };
type Saved = { live: boolean; dollarPerStar: number | null; minCount: number; maxCount: number; plans: { id: number; name: string; count: number; profit: string; live: boolean }[] };
type State = { rate: number | null; marketDollar: number; dollarProfit: number; platform: boolean; stars: Saved | null };
type Form = { live: boolean; dollarPerStar: string; minCount: string; maxCount: string; plans: PlanRow[] };

const API = '/admin/api/telegram-stars';
const START: Form = {
    live: false,
    dollarPerStar: '',
    minCount: '50',
    maxCount: '100000',
    plans: [100, 250, 500, 1000, 2500, 5000].map((n) => ({ name: '', count: String(n), profit: '', live: true })),
};
const LABELS: Record<string, string> = { dollarPerStar: 'قیمت هر استار', minCount: 'حداقل سفارش', maxCount: 'حداکثر سفارش' };

export default function TelegramStarsPage() {
    const state = useQuery<State>(API);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(null);

    useEffect(() => {
        const s = state.data?.stars;
        if (!state.data) return;
        setForm(s ? {
            live: s.live,
            dollarPerStar: s.dollarPerStar != null ? String(s.dollarPerStar) : '',
            minCount: String(s.minCount),
            maxCount: String(s.maxCount),
            plans: s.plans.map((p) => ({ id: p.id, name: p.name, count: String(p.count), profit: p.profit ?? '', live: p.live })),
        } : START);
    }, [state.data]);

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!form || !state.data) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-xl" />
            </div>
        );
    }

    const rate = state.data.rate;
    const set = (p: Partial<Form>) => { save.clearSaved(); setForm((f) => f && { ...f, ...p }); };
    const perStar = parseDecimal(form.dollarPerStar);
    // Same as the storefront: dollars × rate, rounded to the toman, last two digits to 00 or 50.
    const priceOf = (n: number | null) => (rate && perStar && n ? roundToman(perStar * n * rate) : null);
    // A plan with its own profit: market dollar × (1 + profit), as the storefront charges it; else the services rate.
    const { marketDollar, dollarProfit } = state.data;
    const ownProfit = (row: PlanRow) => (row.profit.trim() === '' ? null : parseDecimal(row.profit));
    const planPrice = (row: PlanRow, n: number | null) => {
        const own = ownProfit(row);
        return own === null ? priceOf(n) : perStar && n && marketDollar ? roundToman(perStar * n * marketDollar * (1 + own / 100)) : null;
    };
    const min = parseAmount(form.minCount);
    const max = parseAmount(form.maxCount);

    async function submit() {
        if (!form) return;
        const res = await save.run(API, {
            live: form.live,
            dollarPerStar: perStar ?? form.dollarPerStar,
            minCount: parseAmount(form.minCount),
            maxCount: parseAmount(form.maxCount),
            plans: form.plans.map((p) => ({ id: p.id, name: p.name.trim() || null, count: parseAmount(p.count), profit: p.profit.trim(), live: p.live })),
        });
        if (res) state.reload();
    }

    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => {
            const m = k.match(/^plans\.(\d+)\./);
            return { label: m ? `پلن ردیف ${count(Number(m[1]) + 1)}` : LABELS[k] ?? k, message };
        }),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];

    return (
        <EditorLayout
            backTo="/services"
            backLabel="خدمات مجازی"
            title="تلگرام استارز"
            subtitle="قیمت هر استار به دلار، پلن‌های آماده و مقدار دلخواه مشتری در صفحه استارز تلگرام."
            onSubmit={submit}
            saving={save.saving}
            saveLabel="ذخیره"
            saved={save.saved}
            problems={problems}
            tabbed={false}
            side={
                <div className="space-y-3">
                    <Switch label="فروش فعال" hint="خاموش باشد، صفحه استارز «موقتاً ناموجود» نشان می‌دهد." checked={form.live} onChange={(v) => set({ live: v })} />
                    <dl className="m-0 space-y-2 rounded-xl bg-surface-2 px-4 py-3">
                        <div>
                            <dt className="text-[13px] text-muted">۱۰۰ استار برای مشتری</dt>
                            <dd className="num m-0 mt-0.5 text-[18px] font-bold">{priceOf(100) ? toman(priceOf(100)) : '—'}</dd>
                        </div>
                        {min != null && max != null && min <= max && (
                            <div className="text-[13px] text-muted">
                                مقدار دلخواه از <span className="num">{count(min)}</span> تا <span className="num">{count(max)}</span> استار
                                {priceOf(min) && priceOf(max) ? <> (<span className="num">{toman(priceOf(min))}</span> تا <span className="num">{toman(priceOf(max))}</span>)</> : null}
                            </div>
                        )}
                        {!rate && <dd className="m-0 text-[12px] text-warn">نرخ دلار خدمات (ارزها و سود) تنظیم نشده است.</dd>}
                    </dl>
                </div>
            }
        >
            {!state.data.platform && <p role="alert" className="mb-3 rounded-xl bg-amber-500/10 px-3 py-2.5 text-[13px] font-medium text-warn">پلتفرم تلگرام (نامک telegram) در «پلتفرم‌ها و سرویس‌ها» نیست؛ ابتدا آن را بسازید.</p>}
            <Panel title="قیمت و مقدار">
                <div className="grid gap-4 sm:grid-cols-3">
                    <Field
                        label="قیمت هر استار (دلار)"
                        error={save.errors.dollarPerStar}
                        hint={rate ? <>با نرخ <span className="num">{toman(rate)}</span> (دلار خدمات با سود) به تومان تبدیل می‌شود.</> : 'نرخ دلار خدمات تنظیم نشده است.'}
                    >
                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={form.dollarPerStar} onChange={(e) => set({ dollarPerStar: e.target.value })} placeholder="0.015" />}
                    </Field>
                    <Field label="حداقل مقدار دلخواه (استار)" error={save.errors.minCount}>
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.minCount} onChange={(e) => set({ minCount: e.target.value })} placeholder="50" />}
                    </Field>
                    <Field label="حداکثر مقدار دلخواه (استار)" error={save.errors.maxCount}>
                        {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={form.maxCount} onChange={(e) => set({ maxCount: e.target.value })} placeholder="100000" />}
                    </Field>
                </div>
            </Panel>

            <Panel title="پلن‌ها">
                <p className="mb-3 text-muted">بسته‌های آماده‌ای که مشتری با یک ضربه انتخاب می‌کند. قیمت هر پلن = تعداد × قیمت هر استار؛ «سود ٪» خالی بماند، سود دلار خدمات حساب می‌شود. نام خالی بماند، «۱٬۰۰۰ استار» گذاشته می‌شود.</p>
                <RowsEditor
                    rows={form.plans}
                    onChange={(plans) => set({ plans })}
                    make={() => ({ name: '', count: '', profit: '', live: true })}
                    addLabel="افزودن پلن"
                    empty="پلنی ندارد؛ مشتری فقط مقدار دلخواه وارد می‌کند."
                    removeLabel={(r, i) => `حذف پلن ${r.name || r.count || i + 1}`}
                    render={(row, update, i) => {
                        const n = parseAmount(row.count);
                        // Same card as account plans: the fields beside a receipt of how the site price is worked out.
                        return (
                            <div className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] xl:items-start">
                                <div className="grid gap-3">
                                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-[110px_minmax(0,1fr)_90px] sm:items-end">
                                        <Field label="تعداد استار" error={save.errors[`plans.${i}.count`]}>
                                            {(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.count} onChange={(e) => update({ count: e.target.value })} />}
                                        </Field>
                                        <Field label="نام پلن" error={save.errors[`plans.${i}.name`]}>
                                            {(p) => <input {...p} className="field" value={row.name} onChange={(e) => update({ name: e.target.value })} placeholder={n ? `${count(n)} استار` : ''} />}
                                        </Field>
                                        <Field label="سود ٪" error={save.errors[`plans.${i}.profit`]}>
                                            {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={row.profit} onChange={(e) => update({ profit: e.target.value })} placeholder={String(dollarProfit)} />}
                                        </Field>
                                    </div>
                                    <Switch label="فعال" checked={row.live} onChange={(v) => update({ live: v })} />
                                </div>
                                <PriceReceipt
                                    approx
                                    lines={[
                                        { label: 'قیمت هر استار', value: perStar ? usd(perStar) : '—' },
                                        { label: 'قیمت دلاری پلن', value: perStar && n ? usd(Math.round(perStar * n * 10000) / 10000) : '—' },
                                        ...(ownProfit(row) === null
                                            ? [{ label: 'دلار خدمات (با سود)', value: rate ? `${count(Math.round(rate))} ت` : '—' }]
                                            : [{ label: 'دلار بازار', value: `${count(marketDollar)} ت` }, { label: 'سود (این پلن)', value: `${ownProfit(row)}٪` }]),
                                    ]}
                                    total={planPrice(row, n)}
                                    empty={perStar ? 'تعداد را وارد کنید' : 'قیمت هر استار را وارد کنید'}
                                />
                            </div>
                        );
                    }}
                />
            </Panel>
        </EditorLayout>
    );
}
