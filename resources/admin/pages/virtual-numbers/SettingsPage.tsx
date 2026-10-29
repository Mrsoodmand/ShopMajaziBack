import { useEffect, useState } from 'react';
import { Link } from 'react-router';
import { ArrowLeft } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, ImageField, RowsEditor, Switch } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { API, UPLOAD_URL, type ProviderKey } from './types';

type Stat = { key: ProviderKey; label: string; server: number | null; services: number; countries: number; pairs: number; offers: number; live: number; lastUpdate: string | null };
type Page = { image: string; content: string; seoHtml: string; faq: { q: string; a: string }[] };
type Settings = { providers: Record<ProviderKey, boolean>; stats: Stat[]; page: Page };

const labels: Record<string, string> = { 'page.image': 'تصویر صفحه', 'page.content': 'متن صفحه', 'page.seoHtml': 'کد سئو' };

export default function SettingsPage() {
    const { csrfToken } = useBoot();
    const settings = useQuery<Settings>(`${API}/settings`);
    const [providers, setProviders] = useState<Record<ProviderKey, boolean> | null>(null);
    const [page, setPage] = useState<Page | null>(null);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState<string | null>(null);
    const [failure, setFailure] = useState<string | null>(null);

    useEffect(() => {
        if (settings.data) {
            setProviders(settings.data.providers);
            setPage(settings.data.page);
        }
    }, [settings.data]);

    const setP = (patch: Partial<Page>) => {
        setSaved(null);
        setPage((p) => (p ? { ...p, ...patch } : p));
    };

    async function save() {
        setSaving(true);
        setSaved(null);
        setFailure(null);
        try {
            const res = await postJson<{ success: string }>(`${API}/settings`, csrfToken, { providers, page });
            setErrors({});
            setSaved(res.success);
        } catch (e) {
            const err = e as ApiError;
            setErrors(err.fields ?? {});
            if (!err.fields || !Object.keys(err.fields).length) setFailure(err.message);
        } finally {
            setSaving(false);
        }
    }

    if (settings.error) return <ErrorState error={settings.error} onRetry={settings.reload} boxed />;
    if (!settings.data || !providers || !page) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-2xl" />
            </div>
        );
    }

    const stats = settings.data.stats;
    const total = stats.reduce((a, s) => ({ offers: a.offers + s.offers, live: a.live + s.live }), { offers: 0, live: 0 });
    const problems = [
        ...Object.entries(errors).map(([k, message]) => {
            const faq = k.match(/^page\.faq\.(\d+)\.(q|a)$/);
            return { label: faq ? `سؤال ${Number(faq[1]) + 1}` : (labels[k] ?? k), message };
        }),
        ...(failure ? [{ label: 'ذخیره', message: failure }] : []),
    ];

    return (
        <EditorLayout
            backTo="/virtual-numbers/services"
            backLabel="سرویس‌های شماره مجازی"
            title="تنظیمات شماره مجازی"
            subtitle="سرورهای فروش و متن صفحه شماره مجازی سایت."
            onSubmit={save}
            saving={saving}
            saveLabel="ذخیره تنظیمات"
            saved={saved}
            problems={problems}
            side={
                <dl className="m-0 space-y-1">
                    <div className="flex items-baseline justify-between gap-3">
                        <dt className="text-muted">پیشنهادهای در فروش</dt>
                        <dd className="num m-0 font-bold">{count(total.live)}</dd>
                    </div>
                    <div className="flex items-baseline justify-between gap-3">
                        <dt className="text-muted">کل پیشنهادها</dt>
                        <dd className="num m-0">{count(total.offers)}</dd>
                    </div>
                </dl>
            }
        >
            <Panel title="سرورها">
                <p className="mb-3 text-muted">کلید هر سرور در همان تنظیمات پنل قدیم ذخیره می‌شود؛ فروشگاه فعلاً آن را نمی‌خواند و فروش هر سرور به کدها و قیمت‌هایش بستگی دارد.</p>
                <div className="relative -mx-5 overflow-x-auto">
                    <table className="w-full min-w-[640px]">
                        <thead>
                            <tr className="border-y border-stroke text-[13px] text-muted">
                                <th scope="col" className="py-2.5 ps-5 pe-3 text-start font-medium">سرور</th>
                                <th scope="col" className="px-3 text-end font-medium">سرویس با کد</th>
                                <th scope="col" className="px-3 text-end font-medium">کشور با کد</th>
                                <th scope="col" className="px-3 text-end font-medium">ترکیب ممکن</th>
                                <th scope="col" className="px-3 text-end font-medium">در فروش</th>
                                <th scope="col" className="py-2.5 ps-3 pe-5 text-start font-medium">آخرین قیمت</th>
                            </tr>
                        </thead>
                        <tbody>
                            {stats.map((s) => (
                                <tr key={s.key} className="border-b border-stroke last:border-0">
                                    <th scope="row" className="py-2 ps-5 pe-3 text-start font-normal">
                                        <Switch
                                            label={s.server ? `سرور ${s.server} · ${s.label}` : s.label}
                                            hint={s.server ? undefined : 'فقط کد؛ پیشنهاد فروش ندارد'}
                                            checked={providers[s.key]}
                                            onChange={(v) => {
                                                setSaved(null);
                                                setProviders({ ...providers, [s.key]: v });
                                            }}
                                        />
                                    </th>
                                    <td className="num px-3 text-end">{count(s.services)}</td>
                                    <td className="num px-3 text-end">{count(s.countries)}</td>
                                    <td className="num px-3 text-end text-muted">{count(s.pairs)}</td>
                                    <td className="num px-3 text-end">
                                        {s.server ? (
                                            <>
                                                <span className="font-medium">{count(s.live)}</span> <span className="text-muted">از {count(s.offers)}</span>
                                            </>
                                        ) : (
                                            <span className="text-muted">—</span>
                                        )}
                                    </td>
                                    <td className="num py-2 ps-3 pe-5 whitespace-nowrap text-muted">{s.lastUpdate ?? '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <p className="mt-3 text-[13px] text-muted">«ترکیب ممکن» یعنی سرویس‌های دارای کد ضرب در کشورهای دارای کد؛ «آخرین قیمت» زمان آخرین به‌روزرسانی خودکار قیمت آن سرور است.</p>
            </Panel>

            <Panel title="صفحه شماره مجازی">
                <div className="grid gap-5">
                    <p className="rounded-xl bg-surface-2 px-4 py-3 text-body">
                        عنوان H1، عنوان و توضیح سئو و تصویر اشتراک‌گذاری این صفحه در{' '}
                        <Link to="/seo" className="inline-flex items-center gap-1 font-medium text-indigo underline underline-offset-4">
                            سئوی صفحه‌ها
                            <ArrowLeft aria-hidden size={14} />
                        </Link>{' '}
                        تنظیم می‌شوند.
                    </p>
                    <ImageField
                        label="تصویر شاخص"
                        hint="همان تصویر شاخص پنل قدیم؛ صفحه‌های فعلی سایت از آن استفاده نمی‌کنند."
                        value={page.image}
                        onChange={(path) => setP({ image: path })}
                        error={errors['page.image']}
                        uploadUrl={UPLOAD_URL}
                    />
                    <Field
                        label="متن صفحه (HTML)"
                        optional
                        error={errors['page.content']}
                        hint={
                            <>
                                متن بلند پایین صفحه خرید شماره مجازی (<span dir="ltr" className="latin">/virtual-number/buy</span>).
                            </>
                        }
                    >
                        {(p) => <textarea {...p} rows={10} dir="auto" className="field text-[13px]" value={page.content} onChange={(e) => setP({ content: e.target.value })} />}
                    </Field>
                    <Field label="کد سئو (HTML)" optional error={errors['page.seoHtml']} hint="داخل head صفحه می‌آید.">
                        {(p) => <textarea {...p} rows={4} dir="ltr" className="field font-mono text-[13px]" value={page.seoHtml} onChange={(e) => setP({ seoHtml: e.target.value })} />}
                    </Field>
                </div>
            </Panel>

            <Panel title="سؤالات متداول">
                <p className="mb-3 text-muted">همراه صفحه شماره مجازی به سایت جدید فرستاده می‌شوند. صفحه خرید قدیم فعلاً سؤالات ثابت خودش را نشان می‌دهد.</p>
                <RowsEditor
                    rows={page.faq}
                    onChange={(faq) => setP({ faq })}
                    make={() => ({ q: '', a: '' })}
                    addLabel="افزودن سؤال"
                    empty="هنوز سؤالی اضافه نشده است."
                    removeLabel={(row, i) => `حذف سؤال ${i + 1}${row.q ? `: ${row.q}` : ''}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3">
                            <Field label={`سؤال ${i + 1}`} error={errors[`page.faq.${i}.q`]}>
                                {(p) => <input {...p} className="field" value={row.q} onChange={(e) => update({ q: e.target.value })} />}
                            </Field>
                            <Field label="پاسخ" error={errors[`page.faq.${i}.a`]}>
                                {(p) => <textarea {...p} rows={2} className="field" value={row.a} onChange={(e) => update({ a: e.target.value })} />}
                            </Field>
                        </div>
                    )}
                />
            </Panel>
        </EditorLayout>
    );
}
