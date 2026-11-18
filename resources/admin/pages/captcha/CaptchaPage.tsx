import { useEffect, useState } from 'react';
import { useQuery } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, Switch } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';

// Admin › کپچا (Admin\CaptchaApiController, App\Services\Captcha): which captcha the public forms use (comments,
// sign-in / sign-up, Contact us). Google's keys come from the backend .env; the number captcha needs nothing.

type Driver = 'off' | 'google' | 'number';
type Settings = { driver: Driver; fallback: boolean; min_score: number };
type State = { settings: Settings; googleReady: boolean; active: Driver };

const API = '/admin/api/captcha';

const DRIVERS: { id: Driver; label: string; hint: string }[] = [
    { id: 'off', label: 'خاموش', hint: 'فرم‌ها بدون کپچا ارسال می‌شوند.' },
    { id: 'google', label: 'گوگل reCAPTCHA v3', hint: 'نامرئی؛ گوگل به هر ارسال امتیاز می‌دهد و کاربر کاری نمی‌کند.' },
    { id: 'number', label: 'کپچای عددی', hint: 'تصویر ۵ رقم که کاربر تایپ می‌کند؛ بدون سرویس بیرونی و همیشه در دسترس.' },
];
const LABEL: Record<Driver, string> = { off: 'خاموش', google: 'گوگل', number: 'عددی' };

export default function CaptchaPage() {
    const state = useQuery<State>(API);
    const save = useSave();
    const [form, setForm] = useState<Settings | null>(null);
    const [score, setScore] = useState('');

    useEffect(() => {
        if (state.data) {
            setForm(state.data.settings);
            setScore(String(state.data.settings.min_score));
        }
    }, [state.data]);

    if (state.error) return <ErrorState error={state.error} onRetry={state.reload} boxed />;
    if (!state.data || !form) {
        return (
            <div className="mx-auto max-w-[1280px]" aria-label="در حال بارگذاری">
                <div className="skeleton mb-6 h-7 w-64" />
                <div className="skeleton h-96 rounded-xl" />
            </div>
        );
    }

    const { googleReady, active } = state.data;
    const set = (patch: Partial<Settings>) => {
        save.clearSaved();
        setForm({ ...form, ...patch });
    };
    async function submit() {
        const min = Number(score.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d))).replace('٫', '.'));
        const res = await save.run(API, { ...form, min_score: Number.isFinite(min) ? min : score });
        if (res) state.reload();
    }
    const labels: Record<string, string> = { driver: 'نوع کپچا', fallback: 'کپچای عددی جایگزین', min_score: 'حداقل امتیاز' };
    const problems = [
        ...Object.entries(save.errors).map(([k, message]) => ({ label: labels[k] ?? k, message })),
        ...(save.failure ? [{ label: 'ذخیره', message: save.failure }] : []),
    ];

    return (
        <EditorLayout
            backTo="/"
            backLabel="داشبورد"
            title="کپچا"
            subtitle="محافظت فرم‌های عمومی سایت (نظرها، ورود و ثبت‌نام، تماس با ما) در برابر ربات‌ها."
            onSubmit={submit}
            saving={save.saving}
            saveLabel="ذخیره تنظیمات کپچا"
            saved={save.saved}
            problems={problems}
            tabbed={false}
            side={
                <dl className="m-0 space-y-1.5">
                    {[
                        ['کپچای فعال الان', LABEL[active]],
                        ['کلیدهای گوگل در .env', googleReady ? 'تنظیم شده' : 'تنظیم نشده'],
                    ].map(([label, value]) => (
                        <div key={label} className="flex items-baseline justify-between gap-3">
                            <dt className="text-muted">{label}</dt>
                            <dd className="m-0">{value}</dd>
                        </div>
                    ))}
                </dl>
            }
        >
            {form.driver === 'google' && !googleReady && (
                <p className="rounded-xl bg-warn-soft px-4 py-3 text-[13px]">
                    کلیدهای گوگل تنظیم نشده‌اند، پس فرم‌ها فعلاً کپچای عددی نشان می‌دهند. برای گوگل،{' '}
                    <span dir="ltr" className="latin">RECAPTCHA_SITE_KEY</span> و <span dir="ltr" className="latin">RECAPTCHA_SECRET_KEY</span> (reCAPTCHA v3، دامنه سایت) را در .env بک‌اند بگذارید.
                </p>
            )}

            <Panel title="نوع کپچا">
                <fieldset className="m-0 border-0 p-0">
                    <legend className="sr-only">نوع کپچا</legend>
                    <div className="space-y-2">
                        {DRIVERS.map((d) => (
                            <label key={d.id} className={`flex cursor-pointer gap-2.5 rounded-xl border-2 px-3 py-2.5 ${form.driver === d.id ? 'border-primary' : 'border-stroke hover:border-field'}`}>
                                <input type="radio" name="captcha-driver" className="mt-1 size-4 accent-[var(--primary)]" checked={form.driver === d.id} onChange={() => set({ driver: d.id })} />
                                <span>
                                    <span className="block font-bold">{d.label}</span>
                                    <span className="block text-[13px] text-muted">{d.hint}</span>
                                </span>
                            </label>
                        ))}
                    </div>
                </fieldset>
            </Panel>

            {form.driver === 'google' && (
                <Panel title="تنظیمات گوگل">
                    <div className="space-y-4">
                        <Switch
                            label="کپچای عددی جایگزین"
                            hint="اگر گوگل در شبکه کاربر فیلتر باشد یا امتیاز پایین بدهد، به‌جای رد کردن، تصویر عددی نشان داده می‌شود. پیشنهاد می‌شود روشن بماند."
                            checked={form.fallback}
                            onChange={(v) => set({ fallback: v })}
                        />
                        <Field label="حداقل امتیاز (۰٫۱ تا ۰٫۹)" hint="امتیاز گوگل از ۰ (ربات) تا ۱ (انسان) است. پیش‌فرض ۰٫۵." error={save.errors.min_score}>
                            {(p) => (
                                <input
                                    {...p}
                                    inputMode="decimal"
                                    dir="ltr"
                                    className="field num w-32 text-end"
                                    value={score}
                                    onChange={(e) => {
                                        save.clearSaved();
                                        setScore(e.target.value);
                                    }}
                                />
                            )}
                        </Field>
                    </div>
                </Panel>
            )}
        </EditorLayout>
    );
}
