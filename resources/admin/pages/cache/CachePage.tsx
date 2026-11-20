import { useEffect, useState } from 'react';
import { Trash2 } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { jalali } from '../../lib/dates';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { ErrorState, Panel } from '../../components/ui';

// Admin › مدیریت کش (Admin\CacheApiController, App\Services\CacheManager): the state of Laravel's caches, buttons to
// clear each one or the storefront's (Next.js) cached data, and how long the storefront keeps each section.

type Cleared = Record<string, { at: string; by: string }>;
type Status = {
    driver: string;
    appSize: number | null;
    appFiles: number | null;
    configCached: boolean;
    routesCached: boolean;
    viewsSize: number;
    gvListCached: boolean;
    storefrontReady: boolean;
    cleared: Cleared;
};
type Section = { key: string; label: string; default: number; note: string | null; seconds: number | null };
type State = { status: Status; sections: Section[] };

const API = '/admin/api/cache';

const KINDS: { kind: string; label: string; hint: string }[] = [
    { kind: 'storefront', label: 'کش سایت (همه صفحه‌ها)', hint: 'داده‌هایی که سایت (Next.js) از بک‌اند گرفته و نگه داشته؛ صفحه‌ها در بازدید بعدی تازه ساخته می‌شوند.' },
    { kind: 'gv', label: 'لیست گوگل ویس', hint: 'لیست شماره‌ها و قیمت‌های نامبرلند (۶۰ ثانیه نگه داشته می‌شود).' },
    { kind: 'app', label: 'کش برنامه (Laravel)', hint: 'همه داده‌های کش‌شده بک‌اند، از جمله لیست گوگل ویس و محدودیت‌های تعداد درخواست.' },
    { kind: 'view', label: 'قالب‌های کامپایل‌شده', hint: 'صفحه‌های Blade پنل قدیم؛ در اولین بازدید دوباره ساخته می‌شوند.' },
    { kind: 'route', label: 'کش مسیرها', hint: 'فقط اگر مسیرها کش شده باشند اثر دارد.' },
    { kind: 'config', label: 'کش تنظیمات', hint: 'فقط اگر تنظیمات کش شده باشند اثر دارد؛ بعد از تغییر .env لازم است.' },
];

const size = (bytes: number | null) => (bytes == null ? '—' : bytes < 1024 * 1024 ? `${count(Math.round(bytes / 1024))} کیلوبایت` : `${(bytes / 1024 / 1024).toLocaleString('fa-IR', { maximumFractionDigits: 1 })} مگابایت`);
const when = (iso: string) => {
    const d = new Date(iso);
    return `${jalali(d)} ${d.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Tehran' })}`;
};
const seconds = (n: number) => (n === 0 ? 'بدون کش' : n % 60 === 0 ? `${count(n / 60)} دقیقه` : `${count(n)} ثانیه`);
const toLatin = (v: string) => v.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)));

export default function CachePage() {
    const state = useQuery<State>(API);
    const save = useSave();
    const [times, setTimes] = useState<Record<string, string>>({});
    const [busy, setBusy] = useState<string | null>(null);
    const [note, setNote] = useState<{ ok: boolean; text: string } | null>(null);

    useEffect(() => {
        if (state.data) setTimes(Object.fromEntries(state.data.sections.map((s) => [s.key, s.seconds == null ? '' : String(s.seconds)])));
    }, [state.data]);

    async function clear(kind: string, section?: string) {
        const id = section ? `${kind}:${section}` : kind;
        setBusy(id);
        setNote(null);
        try {
            const res = await postJson<{ success?: string }>(`${API}/clear`, save.csrfToken, { kind, section: section ?? null });
            setNote({ ok: true, text: res.success ?? 'کش پاک شد.' });
            state.reload();
        } catch (e) {
            setNote({ ok: false, text: (e as ApiError).message });
        } finally {
            setBusy(null);
        }
    }

    async function submit() {
        const res = await save.run(`${API}/times`, {
            // Digits go as numbers; anything else as typed, so the server's message names the field.
            times: Object.fromEntries(Object.entries(times).map(([k, v]) => {
                const t = toLatin(v.trim());
                return [k, t === '' ? null : /^\d+$/.test(t) ? Number(t) : v];
            })),
        });
        if (res) state.reload();
    }

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!state.data) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-xl" />
            </div>
        );
    }

    const { status, sections } = state.data;
    const last = (id: string) => status.cleared[id];
    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => ({ label: sections.find((s) => `times.${s.key}` === k)?.label ?? k, message })),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];

    return (
        <EditorLayout
            backTo="/"
            backLabel="داشبورد"
            title="مدیریت کش"
            subtitle="وضعیت کش‌ها، پاک کردن هر کدام، و مدت نگه‌داری هر بخش سایت."
            onSubmit={submit}
            saving={save.saving}
            saveLabel="ذخیره زمان‌های کش"
            saved={save.saved}
            problems={problems}
            tabbed={false}
            side={
                <dl className="m-0 space-y-1.5">
                    {[
                        ['درایور کش', <span dir="ltr" className="latin">{status.driver}</span>],
                        ['حجم کش برنامه', status.appFiles == null ? '—' : `${size(status.appSize)} · ${count(status.appFiles)} فایل`],
                        ['قالب‌های کامپایل‌شده', size(status.viewsSize)],
                        ['تنظیمات کش‌شده', status.configCached ? 'بله' : 'خیر'],
                        ['مسیرها کش‌شده', status.routesCached ? 'بله' : 'خیر'],
                        ['لیست گوگل ویس در کش', status.gvListCached ? 'بله' : 'خیر'],
                    ].map(([label, value]) => (
                        <div key={label as string} className="flex items-baseline justify-between gap-3">
                            <dt className="text-muted">{label}</dt>
                            <dd className="num m-0">{value}</dd>
                        </div>
                    ))}
                </dl>
            }
        >
            {!status.storefrontReady && (
                <p className="rounded-xl bg-warn-soft px-4 py-3 text-[13px]">
                    پاک کردن کش سایت هنوز وصل نیست: <span dir="ltr" className="latin">STOREFRONT_REVALIDATE_SECRET</span> در .env بک‌اند و همان مقدار به‌صورت{' '}
                    <span dir="ltr" className="latin">REVALIDATE_SECRET</span> در سایت (Next.js) لازم است. اسکریپت دیپلوی این را خودش تنظیم می‌کند.
                </p>
            )}

            <Panel title="پاک کردن کش">
                <ul className="m-0 list-none divide-y divide-stroke p-0">
                    {KINDS.map(({ kind, label, hint }) => {
                        const l = last(kind);
                        const off = kind === 'storefront' && !status.storefrontReady;
                        return (
                            <li key={kind} className="flex flex-wrap items-center gap-3 py-3 first:pt-0 last:pb-0">
                                <div className="min-w-[220px] flex-1">
                                    <div className="font-medium">{label}</div>
                                    <p className="m-0 text-[13px] text-muted">{hint}</p>
                                    {l && <p className="num m-0 mt-0.5 text-[12px] text-muted">آخرین بار: {when(l.at)} · {l.by}</p>}
                                </div>
                                <button type="button" className="btn btn-secondary" onClick={() => clear(kind)} disabled={busy !== null || off}>
                                    <Trash2 aria-hidden className="size-4" />
                                    {busy === kind ? 'در حال پاک کردن…' : 'پاک کردن'}
                                </button>
                            </li>
                        );
                    })}
                </ul>
                {note && (
                    <p role="status" className={`m-0 mt-3 text-[13px] ${note.ok ? 'text-muted' : 'text-error'}`}>
                        {note.text}
                    </p>
                )}
            </Panel>

            <Panel title="زمان کش هر بخش سایت">
                <p className="mb-3 text-muted">
                    سایت پاسخ هر بخش را این مدت (ثانیه) نگه می‌دارد و بعد دوباره از بک‌اند می‌گیرد. خالی = زمان پیش‌فرض. ۰ = بدون کش (هر بازدید تازه، کندتر). تغییرها تا یک دقیقه بعد اعمال می‌شوند.
                </p>
                <div className="relative -mx-5 overflow-x-auto">
                    <table className="grid-table w-full min-w-[640px]">
                        <thead>
                            <tr className="border-y border-stroke text-[13px] text-muted">
                                <th scope="col" className="py-2.5 ps-5 pe-3 text-start font-medium">بخش</th>
                                <th scope="col" className="px-3 text-start font-medium">پیش‌فرض</th>
                                <th scope="col" className="px-3 text-start font-medium">زمان دلخواه (ثانیه)</th>
                                <th scope="col" className="py-2.5 ps-3 pe-5 text-end font-medium">کش این بخش</th>
                            </tr>
                        </thead>
                        <tbody>
                            {sections.map((s) => {
                                const e = save.errors[`times.${s.key}`];
                                const id = `storefront:${s.key}`;
                                const l = last(id);
                                return (
                                    <tr key={s.key} className="border-b border-stroke align-top last:border-0">
                                        <th scope="row" className="py-3 ps-5 pe-3 text-start font-medium">
                                            {s.label} <span dir="ltr" className="latin text-[12px] font-normal text-muted">{s.key}</span>
                                            {s.note && <div className="text-[12px] font-normal text-muted">{s.note}</div>}
                                        </th>
                                        <td className="num px-3 py-3 text-muted">{seconds(s.default)}</td>
                                        <td className="px-3 py-2">
                                            <input
                                                aria-label={`زمان کش ${s.label} (ثانیه)`}
                                                aria-invalid={e ? true : undefined}
                                                inputMode="numeric"
                                                dir="ltr"
                                                placeholder={String(s.default)}
                                                className="field num w-32 text-end"
                                                value={times[s.key] ?? ''}
                                                onChange={(ev) => {
                                                    save.clearSaved();
                                                    setTimes((t) => ({ ...t, [s.key]: ev.target.value }));
                                                }}
                                            />
                                            {e && <div className="mt-1 text-[12px] text-error">{e}</div>}
                                        </td>
                                        <td className="py-2 ps-3 pe-5 text-end">
                                            <button type="button" className="btn btn-ghost text-[13px]" onClick={() => clear('storefront', s.key)} disabled={busy !== null || !status.storefrontReady}>
                                                {busy === id ? 'در حال پاک کردن…' : 'پاک کردن'}
                                            </button>
                                            {l && <div className="num text-[11px] text-muted">{when(l.at)}</div>}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </Panel>
        </EditorLayout>
    );
}
