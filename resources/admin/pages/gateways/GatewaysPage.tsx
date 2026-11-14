import { useEffect, useState } from 'react';
import { ArrowDown, ArrowUp, PlugZap } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { jalali } from '../../lib/dates';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Switch } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';

// Admin › درگاه‌های پرداخت (Admin\PaymentGatewayApiController, App\Services\Payments\GatewayManager). Checkout uses the
// first gateway that is on and ready; if it can't open a payment session, the next one is tried.

type Gateway = {
    key: string;
    label: string;
    enabled: boolean;
    sandbox: boolean;
    canSandbox: boolean;
    merchant: string | null; // masked: ••••1234
    merchantSource: 'panel' | 'env' | null;
    ready: boolean;
    testedAt: string | null;
    testResult: string | null;
    started30d: number;
    paid30d: number;
};
type State = { gateways: Gateway[] };
// Merchant being edited: undefined = keep the saved one, '' = remove it, text = the new one.
type Row = Gateway & { newMerchant?: string };

const API = '/admin/api/payment-gateways';

export default function GatewaysPage() {
    const state = useQuery<State>(API);
    const save = useSave();
    const [rows, setRows] = useState<Row[] | null>(null);
    const [testing, setTesting] = useState<string | null>(null);
    const [tests, setTests] = useState<Record<string, { ok: boolean; message: string }>>({});

    useEffect(() => {
        if (state.data) setRows(state.data.gateways);
    }, [state.data]);

    const patch = (key: string, p: Partial<Row>) => {
        save.clearSaved();
        setRows((r) => r && r.map((g) => (g.key === key ? { ...g, ...p } : g)));
    };
    const move = (i: number, by: number) => {
        save.clearSaved();
        setRows((r) => {
            if (!r) return r;
            const next = [...r];
            [next[i], next[i + by]] = [next[i + by], next[i]];
            return next;
        });
    };

    async function submit() {
        if (!rows) return;
        const res = await save.run<{ success: string } & State>(API, {
            gateways: rows.map((g) => ({
                key: g.key,
                enabled: g.enabled,
                sandbox: g.sandbox,
                ...(g.newMerchant !== undefined ? { merchant: g.newMerchant.trim() } : {}),
            })),
        });
        if (res) setRows(res.gateways);
    }

    async function test(key: string) {
        setTesting(key);
        try {
            const res = await postJson<{ ok: boolean; message: string } & State>(`${API}/${key}/test`, save.csrfToken, {});
            setTests((t) => ({ ...t, [key]: { ok: res.ok, message: res.message } }));
            // Keep unsaved edits; refresh only what the test changed.
            setRows((r) => r && r.map((g) => ({ ...g, ...pick(res.gateways.find((x) => x.key === g.key)) })));
        } catch (e) {
            setTests((t) => ({ ...t, [key]: { ok: false, message: (e as ApiError).message } }));
        } finally {
            setTesting(null);
        }
    }

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!rows) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }

    const first = rows.find((g) => g.enabled && (g.merchant || (g.sandbox && g.canSandbox)));
    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => {
            const i = Number(k.split('.')[1]);
            return { label: rows[i] ? `درگاه ${rows[i].label}` : k, message };
        }),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];

    return (
        <EditorLayout
            backTo="/"
            backLabel="داشبورد"
            title="درگاه‌های پرداخت"
            subtitle="پرداخت با کارت از اولین درگاه روشن انجام می‌شود؛ اگر آن درگاه پرداخت را نپذیرد، درگاه بعدی امتحان می‌شود."
            onSubmit={submit}
            saving={save.saving}
            saveLabel="ذخیره درگاه‌ها"
            saved={save.saved}
            problems={problems}
            tabbed={false}
            side={
                <dl className="m-0 space-y-1">
                    <div className="flex items-baseline justify-between gap-3">
                        <dt className="text-muted">درگاه فعلی</dt>
                        <dd className="m-0 font-bold">{first ? first.label : <span className="text-error">هیچ‌کدام</span>}</dd>
                    </div>
                    <div className="flex items-baseline justify-between gap-3">
                        <dt className="text-muted">پرداخت موفق (۳۰ روز)</dt>
                        <dd className="num m-0">
                            {count(rows.reduce((a, g) => a + g.paid30d, 0))} از {count(rows.reduce((a, g) => a + g.started30d, 0))}
                        </dd>
                    </div>
                </dl>
            }
        >
            <Panel title="درگاه‌ها به ترتیب">
                <ol className="m-0 list-none space-y-4 p-0">
                    {rows.map((g, i) => {
                        const t = tests[g.key];
                        return (
                            <li key={g.key} className="rounded-xl border border-stroke p-4">
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="num grid size-7 shrink-0 place-items-center rounded-full bg-surface-2 text-[13px] font-bold">{i + 1}</span>
                                    <h3 className="m-0 text-[16px] font-bold">{g.label}</h3>
                                    <span className={`rounded-full px-2 py-0.5 text-[12px] ${g.ready ? 'bg-primary/10 text-primary' : 'bg-surface-2 text-muted'}`}>
                                        {g.ready ? (g.sandbox ? 'آماده · آزمایشی' : 'آماده') : g.enabled ? 'مرچنت ندارد' : 'خاموش'}
                                    </span>
                                    <span className="num ms-auto text-[13px] text-muted">
                                        ۳۰ روز: {count(g.paid30d)} موفق از {count(g.started30d)}
                                    </span>
                                    <div className="flex gap-1">
                                        <button type="button" className="btn btn-ghost px-2" aria-label={`${g.label} بالاتر`} disabled={i === 0} onClick={() => move(i, -1)}>
                                            <ArrowUp aria-hidden className="size-4" />
                                        </button>
                                        <button type="button" className="btn btn-ghost px-2" aria-label={`${g.label} پایین‌تر`} disabled={i === rows.length - 1} onClick={() => move(i, 1)}>
                                            <ArrowDown aria-hidden className="size-4" />
                                        </button>
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div className="space-y-3">
                                        <Switch label="روشن" hint="پرداخت‌ها از این درگاه انجام شود" checked={g.enabled} onChange={(v) => patch(g.key, { enabled: v })} />
                                        {g.canSandbox && (
                                            <Switch
                                                label="حالت آزمایشی"
                                                hint="با مرچنت تستی درگاه؛ پولی جابه‌جا نمی‌شود. برای فروش واقعی خاموش باشد."
                                                checked={g.sandbox}
                                                onChange={(v) => patch(g.key, { sandbox: v })}
                                            />
                                        )}
                                    </div>
                                    <div>
                                        <label htmlFor={`m-${g.key}`} className="mb-1 block font-medium">
                                            مرچنت
                                        </label>
                                        <input
                                            id={`m-${g.key}`}
                                            dir="ltr"
                                            autoComplete="off"
                                            spellCheck={false}
                                            className="field num"
                                            placeholder={g.merchant ? `${g.merchant} (بدون تغییر)` : 'مرچنت را وارد کنید'}
                                            value={g.newMerchant ?? ''}
                                            onChange={(e) => patch(g.key, { newMerchant: e.target.value })}
                                        />
                                        <p className="mt-1.5 text-[13px] text-muted">
                                            {g.merchantSource === 'env'
                                                ? 'فعلاً از فایل .env سرور خوانده می‌شود؛ با واردکردن مرچنت اینجا جایگزین می‌شود.'
                                                : g.merchant
                                                  ? 'رمزنگاری‌شده ذخیره شده و فقط ۴ کاراکتر آخر نمایش داده می‌شود.'
                                                  : 'مرچنت را از پنل درگاه کپی کنید.'}
                                            {g.merchantSource === 'panel' && g.newMerchant === undefined && (
                                                <>
                                                    {' '}
                                                    <button type="button" className="text-error underline-offset-4 hover:underline" onClick={() => patch(g.key, { newMerchant: '' })}>
                                                        حذف مرچنت
                                                    </button>
                                                </>
                                            )}
                                        </p>
                                    </div>
                                </div>

                                <div className="mt-4 flex flex-wrap items-center gap-3 border-t border-stroke pt-3">
                                    <button type="button" className="btn btn-secondary" disabled={testing !== null} onClick={() => test(g.key)}>
                                        <PlugZap aria-hidden className="size-4" />
                                        {testing === g.key ? 'در حال تست…' : 'تست اتصال'}
                                    </button>
                                    <p role="status" className={`m-0 text-[13px] ${t ? (t.ok ? 'text-primary' : 'text-error') : 'text-muted'}`}>
                                        {t ? t.message : g.testResult ? `${g.testResult}${g.testedAt ? ` — ${jalali(new Date(g.testedAt))}` : ''}` : 'هنوز تست نشده'}
                                    </p>
                                    <span className="text-[12px] text-muted">تست، مرچنت ذخیره‌شده را امتحان می‌کند (بدون کسر وجه).</span>
                                </div>
                            </li>
                        );
                    })}
                </ol>
            </Panel>
            <Panel title="پیش از روشن کردن زیبال">
                <ul className="m-0 list-disc space-y-1 ps-5 text-muted">
                    <li>در پنل زیبال، وب‌سایت درگاه باید shopmajazi.com باشد؛ وگرنه آدرس بازگشت پذیرفته نمی‌شود (خطای ۱۰۶).</li>
                    <li>اگر زیبال خطای ۱۱۵ داد، IP سرور (135.125.159.146) را در پنل زیبال ثبت کنید.</li>
                    <li>مرچنت را اینجا وارد و ذخیره کنید، «تست اتصال» را بزنید، سپس زیبال را بالای فهرست قرار دهید.</li>
                </ul>
            </Panel>
        </EditorLayout>
    );
}

// The fields a test changes (result and time), so unsaved edits on the page stay.
function pick(g: Gateway | undefined): Partial<Gateway> {
    return g ? { testedAt: g.testedAt, testResult: g.testResult } : {};
}
