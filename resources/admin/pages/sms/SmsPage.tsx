import { useEffect, useId, useMemo, useRef, useState } from 'react';
import { RotateCw, Send, Square } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { useBoot } from '../../lib/boot';
import { count } from '../../lib/format';
import { jalali } from '../../lib/dates';
import { PageTabs, TabPanel, useUrlTab } from '../../components/PageTabs';
import { ErrorState, FieldError, Notice, Panel } from '../../components/ui';

// Admin › پیامک (Admin\SmsApiController, App\Services\Sms): SMS.ir credit, the automatic pattern SMS the site sends
// (template id + on/off) and bulk sends. Template texts live in the SMS.ir panel; SMS.ir's API can't list them.

type Pattern = {
    key: string;
    label: string;
    where: string;
    vars: string[];
    required: boolean;
    new: boolean;
    legacy: number | null;
    template: number | null;
    enabled: boolean;
    note: string;
};
type Account = { ok: boolean; credit: number | null; lines: string[]; error: string | null; dev: boolean };
type State = {
    account: Account;
    patterns: Pattern[];
    line: string | null;
    lowCredit: number;
    adminMobile: string | null;
    audience: { all: number; buyers: number; recent: number };
};
type Campaign = {
    id: number;
    message: string;
    line: string;
    audience: Audience;
    days: number | null;
    total: number;
    done: number;
    accepted: number;
    rejected: number;
    cost: number;
    status: 'sending' | 'done' | 'failed' | 'cancelled';
    error: string | null;
    admin: string | null;
    createdAt: string | null;
    finishedAt: string | null;
};
type Audience = 'all' | 'buyers' | 'recent' | 'custom';
type Tab = 'patterns' | 'bulk' | 'history' | 'settings';
type Row = { template: string; enabled: boolean; note: string };

const API = '/admin/api/sms';
const TABS: Tab[] = ['patterns', 'bulk', 'history', 'settings'];
const OPT_OUT = 'لغو۱۱';

/** SMS parts, the same rule as App\Services\Sms::parts (Persian 70/67, Latin 160/153). */
function parts(text: string): number {
    const len = [...text].length;
    if (!len) return 0;
    const [one, many] = /[^\x00-\x7F]/.test(text) ? [70, 67] : [160, 153];
    return len <= one ? 1 : Math.ceil(len / many);
}
const digits = (s: string) => s.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d))).replace(/[٠-٩]/g, (d) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
const when = (iso: string | null) => {
    if (!iso) return '—';
    const d = new Date(iso);
    return `${jalali(d)} · ${d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Tehran' })}`;
};

export default function SmsPage() {
    const state = useQuery<State>(API);
    const [tab, setTab] = useUrlTab<Tab>(TABS, 'patterns');
    const tabsId = useId();
    const save = useSave();
    const [rows, setRows] = useState<Record<string, Row> | null>(null);
    const [line, setLine] = useState('');
    const [lowCredit, setLowCredit] = useState('');

    useEffect(() => {
        if (!state.data) return;
        setRows(Object.fromEntries(state.data.patterns.map((p) => [p.key, { template: p.template ? String(p.template) : '', enabled: p.enabled, note: p.note }])));
        setLine(state.data.line ?? state.data.account.lines[0] ?? '');
        setLowCredit(String(state.data.lowCredit));
    }, [state.data]);

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!state.data || !rows) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-xl" />
            </div>
        );
    }

    const { account, patterns } = state.data;
    const low = account.credit != null && account.credit < Number(digits(lowCredit) || 0);
    const missing = patterns.filter((p) => !rows[p.key].template).length;

    async function submit() {
        const res = await save.run(API, {
            patterns: Object.fromEntries(
                Object.entries(rows!).map(([k, r]) => [k, { template: digits(r.template).trim() ? Number(digits(r.template)) : null, enabled: r.enabled, note: r.note }]),
            ),
            line: line || null,
            low_credit: Number(digits(lowCredit)) || 0,
        });
        if (res) state.reload();
    }
    const patch = (key: string, p: Partial<Row>) => {
        save.clearSaved();
        setRows((r) => r && { ...r, [key]: { ...r[key], ...p } });
    };

    const saveBar = (
        <div className="mt-4 flex flex-wrap items-center gap-3">
            <button type="button" className="btn btn-primary" disabled={save.saving} onClick={submit}>
                {save.saving ? 'در حال ذخیره…' : 'ذخیره تنظیمات پیامک'}
            </button>
            {save.failure && <p className="m-0 text-[13px] font-bold text-error">{save.failure}</p>}
            {Object.keys(save.errors).length > 0 && <p className="m-0 text-[13px] font-bold text-error">{Object.values(save.errors)[0]}</p>}
            {save.saved && <Notice>{save.saved}</Notice>}
        </div>
    );

    return (
        <div className="mx-auto max-w-[1280px]">
            <header className="mb-5 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">پیامک</h1>
                    <p className="mt-1 text-muted">پیامک‌های خودکار سایت و ارسال گروهی از طریق SMS.ir</p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    {account.dev && <span className="rounded-full bg-surface-2 px-2.5 py-1 text-[12px] text-muted">حالت توسعه: چیزی فرستاده نمی‌شود</span>}
                    {account.ok ? (
                        <span className={`num rounded-full px-3 py-1 text-[13px] font-medium ${low ? 'bg-warn-soft text-warn' : 'bg-success-soft text-success'}`}>
                            اعتبار: {count(account.credit ?? 0)}
                            {low && ' — کمتر از حد هشدار'}
                        </span>
                    ) : (
                        <span className="rounded-full bg-error-soft px-3 py-1 text-[13px] font-medium text-error">اعتبار نامعلوم</span>
                    )}
                    {line && (
                        <span className="rounded-full bg-surface-2 px-3 py-1 text-[13px] text-muted">
                            خط: <span dir="ltr" className="num">{line}</span>
                        </span>
                    )}
                    <button type="button" className="btn btn-secondary min-h-8 px-3 text-[13px]" onClick={state.reload} disabled={state.loading}>
                        <RotateCw aria-hidden size={14} className={state.loading ? 'animate-spin' : ''} />
                        به‌روزرسانی
                    </button>
                </div>
            </header>

            {!account.ok && account.error && <p className="mb-4 rounded-xl bg-error-soft px-4 py-3 text-[13px] text-ink">{account.error}</p>}

            <PageTabs
                id={tabsId}
                label="بخش‌های پیامک"
                value={tab}
                onChange={setTab}
                tabs={[
                    { id: 'patterns', label: 'الگوهای خودکار', count: missing || undefined, note: 'الگو بدون شناسه' },
                    { id: 'bulk', label: 'ارسال گروهی' },
                    { id: 'history', label: 'تاریخچه ارسال' },
                    { id: 'settings', label: 'تنظیمات' },
                ]}
            />

            {tab === 'patterns' && (
                <TabPanel id={tabsId} tab="patterns">
                    <PatternsTab patterns={patterns} rows={rows} patch={patch} errors={save.errors} adminMobile={state.data.adminMobile} />
                    {saveBar}
                </TabPanel>
            )}
            {tab === 'bulk' && (
                <TabPanel id={tabsId} tab="bulk">
                    <BulkTab state={state.data} line={line} setLine={setLine} credit={account.credit} onSent={() => setTab('history')} />
                </TabPanel>
            )}
            {tab === 'history' && (
                <TabPanel id={tabsId} tab="history">
                    <HistoryTab />
                </TabPanel>
            )}
            {tab === 'settings' && (
                <TabPanel id={tabsId} tab="settings">
                    <Panel title="تنظیمات">
                        <div className="grid max-w-xl gap-5">
                            <div>
                                <label htmlFor="sms-line" className="mb-1 block font-medium">
                                    خط ارسال گروهی
                                </label>
                                {account.lines.length ? (
                                    <select id="sms-line" dir="ltr" className="field num" value={line} onChange={(e) => { save.clearSaved(); setLine(e.target.value); }}>
                                        {account.lines.map((l) => (
                                            <option key={l} value={l}>{l}</option>
                                        ))}
                                    </select>
                                ) : (
                                    <p className="m-0 rounded-xl bg-warn-soft px-3 py-2.5 text-[13px] text-ink">
                                        خطی از SMS.ir دریافت نشد. ارسال گروهی به یک خط اختصاصی در حساب SMS.ir نیاز دارد.
                                    </p>
                                )}
                                <p className="mt-1.5 text-[13px] text-muted">پیامک‌های الگو (کد ورود، وضعیت سفارش…) از خط خود SMS.ir می‌روند و به این خط ربطی ندارند.</p>
                                {save.errors.line && <FieldError id="sms-line-error">{save.errors.line}</FieldError>}
                            </div>
                            <div>
                                <label htmlFor="sms-low" className="mb-1 block font-medium">
                                    حد هشدار اعتبار
                                </label>
                                <input
                                    id="sms-low"
                                    inputMode="numeric"
                                    dir="ltr"
                                    className="field num w-40 text-end"
                                    value={lowCredit}
                                    aria-invalid={!!save.errors.low_credit}
                                    onChange={(e) => { save.clearSaved(); setLowCredit(e.target.value); }}
                                />
                                <p className="mt-1.5 text-[13px] text-muted">وقتی اعتبار SMS.ir کمتر از این عدد شود، بالای همین صفحه هشدار نشان داده می‌شود.</p>
                                {save.errors.low_credit && <FieldError id="sms-low-error">{save.errors.low_credit}</FieldError>}
                            </div>
                        </div>
                    </Panel>
                    {saveBar}
                </TabPanel>
            )}
        </div>
    );
}

function PatternsTab({
    patterns,
    rows,
    patch,
    errors,
    adminMobile,
}: {
    patterns: Pattern[];
    rows: Record<string, Row>;
    patch: (key: string, p: Partial<Row>) => void;
    errors: Record<string, string>;
    adminMobile: string | null;
}) {
    const { csrfToken } = useBoot();
    const [mobile, setMobile] = useState(adminMobile ? `0${adminMobile}` : '');
    const [testing, setTesting] = useState<string | null>(null);
    const [results, setResults] = useState<Record<string, { ok: boolean; message: string }>>({});

    async function test(p: Pattern) {
        setTesting(p.key);
        try {
            const res = await postJson<{ ok: boolean; message: string }>(`${API}/patterns/${p.key}/test`, csrfToken, { template: Number(digits(rows[p.key].template)), mobile: digits(mobile) });
            setResults((r) => ({ ...r, [p.key]: res }));
        } catch (e) {
            const err = e as ApiError;
            setResults((r) => ({ ...r, [p.key]: { ok: false, message: Object.values(err.fields ?? {})[0] ?? err.message } }));
        } finally {
            setTesting(null);
        }
    }

    return (
        <>
            <div className="mb-4 flex flex-wrap items-end gap-3">
                <div>
                    <label htmlFor="sms-test-mobile" className="mb-1 block text-[13px] font-medium text-muted">
                        شماره برای ارسال آزمایشی
                    </label>
                    <input id="sms-test-mobile" dir="ltr" inputMode="tel" className="field num w-48" placeholder="09xxxxxxxxx" value={mobile} onChange={(e) => setMobile(e.target.value)} />
                </div>
                <p className="m-0 max-w-xl text-[13px] text-muted">
                    ارسال آزمایشی، الگو را با مقدارهای نمونه (کد ۱۲۳۴۵، سفارش ۱۲۳۴) با شناسه‌ای که الان در ردیف نوشته شده می‌فرستد؛ حتی اگر هنوز ذخیره نکرده باشید.
                </p>
            </div>
            <div className="rounded-xl border border-stroke bg-surface shadow-card">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[860px] border-collapse text-start">
                        <thead>
                            <tr className="border-b border-stroke text-[12px] text-muted">
                                <th className="px-4 py-2.5 text-start font-medium">الگو</th>
                                <th className="px-3 py-2.5 text-start font-medium">متغیرها</th>
                                <th className="px-3 py-2.5 text-start font-medium">شناسه الگو در SMS.ir</th>
                                <th className="px-3 py-2.5 text-start font-medium">فعال</th>
                                <th className="px-4 py-2.5 text-start font-medium">
                                    <span className="sr-only">ارسال آزمایشی</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {patterns.map((p) => {
                                const r = rows[p.key];
                                const err = errors[`patterns.${p.key}.template`];
                                const res = results[p.key];
                                const hasId = !!digits(r.template).trim();
                                return (
                                    <tr key={p.key} className="border-b border-stroke align-top last:border-0">
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap items-center gap-2 font-semibold text-ink">
                                                {p.label}
                                                {p.new && <span className="rounded-full bg-success-soft px-2 py-0.5 text-[11px] font-medium text-success">جدید</span>}
                                            </div>
                                            <div className="text-[13px] text-muted">{p.where}</div>
                                            <input
                                                aria-label={`یادداشت متن ${p.label}`}
                                                className="mt-2 w-full max-w-sm rounded-lg border border-transparent bg-transparent px-2 py-1 text-[13px] text-body hover:border-stroke focus:border-primary focus:outline-none"
                                                placeholder="یادداشت متن الگو (اختیاری)"
                                                value={r.note}
                                                onChange={(e) => patch(p.key, { note: e.target.value })}
                                            />
                                        </td>
                                        <td className="px-3 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                {p.vars.map((v) => (
                                                    <span key={v} dir="ltr" className="latin rounded-md bg-indigo-soft px-2 py-0.5 text-[12px] text-indigo">
                                                        {v}
                                                    </span>
                                                ))}
                                            </div>
                                        </td>
                                        <td className="px-3 py-3">
                                            <input
                                                dir="ltr"
                                                inputMode="numeric"
                                                aria-label={`شناسه الگو ${p.label}`}
                                                aria-invalid={!!err}
                                                className="field num w-32 text-center"
                                                placeholder="—"
                                                value={r.template}
                                                onChange={(e) => patch(p.key, { template: e.target.value })}
                                            />
                                            {err && <FieldError id={`sms-${p.key}-error`}>{err}</FieldError>}
                                            {p.legacy && digits(r.template) !== String(p.legacy) && <p className="mt-1 text-[12px] text-muted">قبلاً: <span className="num">{p.legacy}</span></p>}
                                        </td>
                                        <td className="px-3 py-3">
                                            {p.required ? (
                                                <span className="inline-block pt-2 text-[12px] text-muted">همیشه روشن</span>
                                            ) : (
                                                <label className="inline-flex cursor-pointer pt-1.5">
                                                    <input type="checkbox" className="peer sr-only" checked={r.enabled} onChange={(e) => patch(p.key, { enabled: e.target.checked })} aria-label={`${p.label} فعال`} />
                                                    <span aria-hidden className={`relative h-5 w-9 rounded-full transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring ${r.enabled ? 'bg-primary' : 'bg-field'}`}>
                                                        <span className={`absolute top-0.5 size-4 rounded-full bg-white shadow-sm transition-[inset-inline-start] ${r.enabled ? 'start-[18px]' : 'start-0.5'}`} />
                                                    </span>
                                                </label>
                                            )}
                                            {!p.required && r.enabled && !hasId && <p className="mt-1 text-[12px] text-warn">بدون شناسه فرستاده نمی‌شود</p>}
                                        </td>
                                        <td className="px-4 py-3">
                                            <button type="button" className="btn btn-ghost min-h-8 px-2 text-[13px] text-primary" disabled={!hasId || testing !== null || !mobile} onClick={() => test(p)}>
                                                {testing === p.key ? 'در حال ارسال…' : 'ارسال آزمایشی'}
                                            </button>
                                            {res && <p role="status" className={`mt-1 max-w-48 text-[12px] ${res.ok ? 'text-success' : 'text-error'}`}>{res.message}</p>}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
            <p className="mt-3 text-[13px] text-muted">
                متن الگوها در پنل SMS.ir ساخته و تأیید می‌شود و اینجا فقط شناسه را عوض می‌کنید. اسم متغیرها در الگو باید دقیقاً همین‌ها باشد (مثلاً <span dir="ltr" className="latin">#NUM#</span>).
                الگوی خاموش یا بدون شناسه فرستاده نمی‌شود.
            </p>
        </>
    );
}

function BulkTab({ state, line, setLine, credit, onSent }: { state: State; line: string; setLine: (l: string) => void; credit: number | null; onSent: () => void }) {
    const { csrfToken } = useBoot();
    const [audience, setAudience] = useState<Audience>('buyers');
    const [days, setDays] = useState('30');
    const [custom, setCustom] = useState('');
    const [message, setMessage] = useState('');
    const [optOut, setOptOut] = useState(true);
    const [recipients, setRecipients] = useState<number | null>(null);
    const [checking, setChecking] = useState(false);
    const [sending, setSending] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
    const dialog = useRef<HTMLDialogElement>(null);
    const titleId = useId();

    const text = optOut && message.trim() && !message.includes(OPT_OUT) ? `${message.trim()}\n${OPT_OUT}` : message.trim();
    const n = parts(text);
    const body = () => ({ audience, days: Number(digits(days)) || 30, custom, message: text, line });

    // Counts for the fixed groups come with the page; "recent" with another day count and pasted lists are asked for.
    const known: Partial<Record<Audience, number>> = { all: state.audience.all, buyers: state.audience.buyers, ...(digits(days) === '30' ? { recent: state.audience.recent } : {}) };
    useEffect(() => {
        if (audience !== 'custom' && audience !== 'recent') return setRecipients(known[audience] ?? null);
        if (audience === 'recent' && known.recent != null) return setRecipients(known.recent);
        setRecipients(null);
        const t = setTimeout(async () => {
            try {
                const res = await postJson<{ recipients: number }>(`${API}/bulk/preview`, csrfToken, body());
                setRecipients(res.recipients);
            } catch {
                setRecipients(null);
            }
        }, 400);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [audience, days, custom]);

    async function review() {
        setError(null);
        setFieldErrors({});
        if (!text) return setFieldErrors({ message: 'متن پیامک را بنویسید.' });
        setChecking(true);
        try {
            const res = await postJson<{ recipients: number }>(`${API}/bulk/preview`, csrfToken, body());
            setRecipients(res.recipients);
            if (!res.recipients) setFieldErrors({ audience: 'هیچ شماره معتبری برای ارسال نیست.' });
            else dialog.current?.showModal();
        } catch (e) {
            const err = e as ApiError;
            setFieldErrors(err.fields ?? {});
            if (!Object.keys(err.fields ?? {}).length) setError(err.message);
        } finally {
            setChecking(false);
        }
    }

    async function send() {
        setSending(true);
        try {
            await postJson(`${API}/bulk`, csrfToken, { ...body(), confirm_count: recipients });
            dialog.current?.close();
            setMessage('');
            onSent();
        } catch (e) {
            const err = e as ApiError;
            dialog.current?.close();
            setFieldErrors(err.fields ?? {});
            setError(Object.values(err.fields ?? {})[0] ?? err.message);
        } finally {
            setSending(false);
        }
    }

    const groups: { id: Audience; label: string; hint: string }[] = [
        { id: 'all', label: 'همه کاربران', hint: `${count(state.audience.all)} شماره (حساب‌های مسدود نه)` },
        { id: 'buyers', label: 'کاربرانی که خرید کرده‌اند', hint: `${count(state.audience.buyers)} شماره` },
        { id: 'recent', label: 'خریداران روزهای اخیر', hint: 'کسانی که در این چند روز سفارش پرداخت‌شده داشته‌اند' },
        { id: 'custom', label: 'فهرست دلخواه', hint: 'شماره‌ها را بچسبانید؛ هر خط یا با ویرگول جدا' },
    ];
    const cost = recipients != null ? recipients * n : null;
    const noLine = !state.account.lines.length;

    return (
        <div className="grid gap-5 lg:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
            <Panel title="گیرنده‌ها">
                <fieldset className="m-0 border-0 p-0">
                    <legend className="sr-only">گیرنده‌ها</legend>
                    <div className="space-y-2">
                        {groups.map((g) => (
                            <label key={g.id} className={`flex cursor-pointer gap-2.5 rounded-xl border-2 px-3 py-2.5 ${audience === g.id ? 'border-primary' : 'border-stroke hover:border-field'}`}>
                                <input type="radio" name="sms-audience" className="mt-1 size-4 accent-[var(--primary)]" checked={audience === g.id} onChange={() => setAudience(g.id)} />
                                <span className="min-w-0 flex-1">
                                    <span className="block font-bold">{g.label}</span>
                                    <span className="num block text-[13px] text-muted">{g.hint}</span>
                                    {g.id === 'recent' && audience === 'recent' && (
                                        <span className="mt-2 flex items-center gap-2 text-[13px]">
                                            <input aria-label="تعداد روز" inputMode="numeric" dir="ltr" className="field num h-8 w-20 text-center" value={days} onChange={(e) => setDays(e.target.value)} />
                                            روز اخیر
                                        </span>
                                    )}
                                </span>
                            </label>
                        ))}
                    </div>
                </fieldset>
                {audience === 'custom' && (
                    <textarea
                        aria-label="فهرست شماره‌ها"
                        dir="ltr"
                        className="field num mt-3 min-h-32 py-2"
                        placeholder={'09121234567\n09351234567'}
                        value={custom}
                        onChange={(e) => setCustom(e.target.value)}
                    />
                )}
                {fieldErrors.audience && <FieldError id="sms-audience-error">{fieldErrors.audience}</FieldError>}
                <p className="num mt-3 mb-0 text-[13px] text-muted">
                    گیرنده‌ها: <b className="text-ink">{recipients == null ? '…' : count(recipients)}</b> شماره (تکراری‌ها و شماره‌های نادرست حذف می‌شوند)
                </p>
            </Panel>

            <Panel title="متن پیامک">
                {noLine && (
                    <p className="mt-0 mb-3 rounded-xl bg-warn-soft px-3 py-2.5 text-[13px] text-ink">
                        حساب SMS.ir خط اختصاصی ندارد، پس ارسال گروهی ممکن نیست. یک خط در پنل SMS.ir بگیرید و این صفحه را تازه کنید.
                    </p>
                )}
                <label htmlFor="sms-text" className="sr-only">
                    متن پیامک
                </label>
                <textarea
                    id="sms-text"
                    className="field min-h-40 py-2.5 leading-7"
                    placeholder="مثلاً: سلام، تخفیف ۲۰٪ خدمات مجازی تا جمعه فعال است."
                    value={message}
                    maxLength={900}
                    aria-invalid={!!fieldErrors.message}
                    onChange={(e) => setMessage(e.target.value)}
                />
                {fieldErrors.message && <FieldError id="sms-text-error">{fieldErrors.message}</FieldError>}
                <label className="mt-3 flex cursor-pointer items-center gap-2 text-[13px]">
                    <input type="checkbox" className="size-4 accent-[var(--primary)]" checked={optOut} onChange={(e) => setOptOut(e.target.checked)} />
                    افزودن «{OPT_OUT}» به آخر متن (برای پیامک تبلیغاتی لازم است)
                </label>
                <div className="num mt-4 grid grid-cols-3 gap-2 rounded-xl bg-surface-2 px-3 py-2.5 text-[13px]">
                    <div>
                        <span className="block text-muted">نویسه</span>
                        <b className="text-[16px] text-ink">{count([...text].length)}</b>
                    </div>
                    <div>
                        <span className="block text-muted">بخش هر پیامک</span>
                        <b className="text-[16px] text-ink">{count(n)}</b>
                    </div>
                    <div>
                        <span className="block text-muted">مصرف تقریبی</span>
                        <b className="text-[16px] text-ink">{cost == null ? '…' : count(cost)}</b>
                    </div>
                </div>
                {state.account.lines.length > 1 && (
                    <div className="mt-4">
                        <label htmlFor="sms-bulk-line" className="mb-1 block text-[13px] font-medium text-muted">
                            خط ارسال
                        </label>
                        <select id="sms-bulk-line" dir="ltr" className="field num w-60" value={line} onChange={(e) => setLine(e.target.value)}>
                            {state.account.lines.map((l) => (
                                <option key={l} value={l}>{l}</option>
                            ))}
                        </select>
                    </div>
                )}
                {error && <p className="mt-3 mb-0 text-[13px] font-bold text-error">{error}</p>}
                <div className="mt-4 flex justify-end">
                    <button type="button" className="btn btn-primary" disabled={noLine || checking || !text} onClick={review}>
                        <Send aria-hidden size={16} />
                        {checking ? 'در حال بررسی…' : 'بررسی و ارسال…'}
                    </button>
                </div>
            </Panel>

            <dialog ref={dialog} aria-labelledby={titleId} className="m-auto w-[min(460px,calc(100vw-32px))] rounded-2xl border border-stroke bg-surface p-0 text-body shadow-pop backdrop:bg-black/40">
                <div className="p-5">
                    <h2 id={titleId} className="num m-0 text-[18px] font-bold text-ink">
                        ارسال به {count(recipients ?? 0)} نفر؟
                    </h2>
                    <div className="mt-3 rounded-2xl rounded-be-sm bg-surface-2 px-3.5 py-2.5 text-[14px] leading-7 whitespace-pre-wrap text-ink">{text}</div>
                    <dl className="num mt-4 mb-0 space-y-1.5 text-[13px]">
                        <div className="flex justify-between gap-3">
                            <dt className="text-muted">گیرنده‌ها</dt>
                            <dd className="m-0">{groups.find((g) => g.id === audience)?.label}{audience === 'recent' ? ` (${digits(days)} روز)` : ''}</dd>
                        </div>
                        <div className="flex justify-between gap-3">
                            <dt className="text-muted">مصرف تقریبی</dt>
                            <dd className="m-0">
                                {count(cost ?? 0)}
                                {credit != null && ` از ${count(credit)} اعتبار`}
                            </dd>
                        </div>
                        <div className="flex justify-between gap-3">
                            <dt className="text-muted">خط</dt>
                            <dd className="m-0" dir="ltr">{line}</dd>
                        </div>
                        <div className="flex justify-between gap-3">
                            <dt className="text-muted">دسته‌ها</dt>
                            <dd className="m-0">{count(Math.ceil((recipients ?? 0) / 100))} دسته ۱۰۰تایی</dd>
                        </div>
                    </dl>
                    {credit != null && cost != null && cost > credit && (
                        <p className="mt-3 mb-0 rounded-xl bg-error-soft px-3 py-2 text-[13px] text-ink">اعتبار برای همه گیرنده‌ها کافی نیست؛ ارسال وسط کار متوقف می‌شود.</p>
                    )}
                    <p className="mt-3 mb-0 rounded-xl bg-primary-soft px-3 py-2 text-[13px] text-ink">بعد از شروع، پیامک‌های فرستاده‌شده برنمی‌گردند. می‌توانید ادامه ارسال را از «تاریخچه ارسال» متوقف کنید.</p>
                    <div className="mt-5 flex justify-end gap-2">
                        <button type="button" className="btn btn-secondary" onClick={() => dialog.current?.close()} disabled={sending}>
                            انصراف
                        </button>
                        <button type="button" className="btn btn-primary" onClick={send} disabled={sending}>
                            {sending ? 'در حال شروع…' : 'بله، ارسال شود'}
                        </button>
                    </div>
                </div>
            </dialog>
        </div>
    );
}

const STATUS: Record<Campaign['status'], { label: string; tone: string }> = {
    sending: { label: 'در حال ارسال', tone: 'bg-warn-soft text-warn' },
    done: { label: 'تمام شد', tone: 'bg-success-soft text-success' },
    failed: { label: 'متوقف شد (خطا)', tone: 'bg-error-soft text-error' },
    cancelled: { label: 'لغو شد', tone: 'bg-surface-2 text-muted' },
};
const AUDIENCE: Record<Audience, string> = { all: 'همه کاربران', buyers: 'خریداران', recent: 'خریداران اخیر', custom: 'فهرست دلخواه' };

function HistoryTab() {
    const list = useQuery<{ campaigns: Campaign[] }>(`${API}/campaigns`);
    const { csrfToken } = useBoot();
    const [busy, setBusy] = useState<number | null>(null);
    const sending = useMemo(() => list.data?.campaigns.some((c) => c.status === 'sending') ?? false, [list.data]);

    // Refresh while a send is running so its progress moves.
    useEffect(() => {
        if (!sending) return;
        const t = setInterval(list.reload, 3000);
        return () => clearInterval(t);
    }, [sending, list.reload]);

    async function cancel(id: number) {
        if (!window.confirm('ادامه این ارسال متوقف شود؟ پیامک‌هایی که رفته‌اند برنمی‌گردند.')) return;
        setBusy(id);
        try {
            await postJson(`${API}/campaigns/${id}/cancel`, csrfToken, {});
        } catch (e) {
            window.alert((e as ApiError).message);
        } finally {
            setBusy(null);
            list.reload();
        }
    }

    if (list.error) return <ErrorState error={list.error} onRetry={list.reload} boxed />;
    if (!list.data) return <div className="skeleton h-64 rounded-xl" aria-label="در حال بارگذاری" />;
    if (!list.data.campaigns.length) {
        return <p className="rounded-xl border border-stroke bg-surface px-6 py-14 text-center text-muted shadow-card">هنوز ارسال گروهی انجام نشده است.</p>;
    }

    return (
        <div className="rounded-xl border border-stroke bg-surface shadow-card">
            <div className="overflow-x-auto">
                <table className="w-full min-w-[820px] border-collapse">
                    <thead>
                        <tr className="border-b border-stroke text-[12px] text-muted">
                            <th className="px-4 py-2.5 text-start font-medium">تاریخ</th>
                            <th className="px-3 py-2.5 text-start font-medium">متن</th>
                            <th className="px-3 py-2.5 text-start font-medium">گیرنده‌ها</th>
                            <th className="px-3 py-2.5 text-start font-medium">نتیجه</th>
                            <th className="px-3 py-2.5 text-start font-medium">مصرف</th>
                            <th className="px-4 py-2.5 text-start font-medium">وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        {list.data.campaigns.map((c) => {
                            const s = STATUS[c.status];
                            const pct = c.total ? Math.round((c.done / c.total) * 100) : 0;
                            return (
                                <tr key={c.id} className="border-b border-stroke align-top text-[13px] last:border-0">
                                    <td className="num px-4 py-3 whitespace-nowrap">
                                        {when(c.createdAt)}
                                        {c.admin && <div className="text-[12px] text-muted">{c.admin}</div>}
                                    </td>
                                    <td className="max-w-80 px-3 py-3">
                                        <p className="m-0 line-clamp-2 whitespace-pre-wrap text-ink" title={c.message}>{c.message}</p>
                                    </td>
                                    <td className="num px-3 py-3 whitespace-nowrap">
                                        {count(c.total)}
                                        <div className="text-[12px] text-muted">{AUDIENCE[c.audience]}{c.days ? ` · ${c.days} روز` : ''}</div>
                                    </td>
                                    <td className="num px-3 py-3 whitespace-nowrap">
                                        <span className="text-success">{count(c.accepted)} پذیرفته</span>
                                        {c.rejected > 0 && <div className="text-[12px] text-error">{count(c.rejected)} رد شد</div>}
                                    </td>
                                    <td className="num px-3 py-3">{count(c.cost)}</td>
                                    <td className="px-4 py-3">
                                        <span className={`num inline-block rounded-full px-2.5 py-0.5 text-[12px] font-medium whitespace-nowrap ${s.tone}`}>
                                            {s.label}
                                            {c.status === 'sending' && ` ${pct}٪`}
                                        </span>
                                        {c.status === 'sending' && (
                                            <>
                                                <div className="mt-2 h-1.5 w-28 overflow-hidden rounded-full bg-surface-3">
                                                    <div className="h-full rounded-full bg-warn transition-[width]" style={{ width: `${pct}%` }} />
                                                </div>
                                                <button type="button" className="btn btn-ghost mt-1 min-h-7 px-1.5 text-[12px] text-error" disabled={busy === c.id} onClick={() => cancel(c.id)}>
                                                    <Square aria-hidden size={12} />
                                                    توقف
                                                </button>
                                            </>
                                        )}
                                        {c.error && <p className="mt-1 mb-0 max-w-56 text-[12px] text-error">{c.error}</p>}
                                        {c.status !== 'sending' && c.done < c.total && <p className="num mt-1 mb-0 text-[12px] text-muted">{count(c.total - c.done)} شماره فرستاده نشد</p>}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
