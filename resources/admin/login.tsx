import { StrictMode, useRef, useState, type FormEvent } from 'react';
import { createRoot } from 'react-dom/client';
import { Eye, EyeOff, LogIn } from 'lucide-react';
import { FieldError } from './components/ui';
import './styles.css';

/** Data Laravel renders on the mount element (AdminAuth\LoginController::showLoginForm). */
type LoginBoot = { action: string; csrfToken: string; email: string | null };
type Errors = { email?: string; password?: string; form?: string };

function LoginPage({ boot }: { boot: LoginBoot }) {
    const [email, setEmail] = useState(boot.email ?? '');
    const [password, setPassword] = useState('');
    const [remember, setRemember] = useState(true);
    const [show, setShow] = useState(false);
    const [busy, setBusy] = useState(false);
    const [errors, setErrors] = useState<Errors>({});
    const emailRef = useRef<HTMLInputElement>(null);
    const passwordRef = useRef<HTMLInputElement>(null);

    function fail(next: Errors) {
        setErrors(next);
        setBusy(false);
        // Focus the first field with a problem so keyboard and screen-reader users land on it.
        requestAnimationFrame(() => (next.email ? emailRef : passwordRef).current?.focus());
    }

    async function submit(e: FormEvent) {
        e.preventDefault();
        const missing: Errors = {};
        if (!email.trim()) missing.email = 'ایمیل را وارد کنید.';
        if (!password) missing.password = 'کلمه عبور را وارد کنید.';
        if (missing.email || missing.password) return fail(missing);

        setBusy(true);
        setErrors({});
        let res: Response;
        try {
            res = await fetch(boot.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': boot.csrfToken },
                body: JSON.stringify({ email: email.trim(), password, remember }),
            });
        } catch {
            return fail({ form: 'اتصال برقرار نشد. اینترنت را بررسی کنید و دوباره تلاش کنید.' });
        }

        const body = await res.json().catch(() => null);
        if (res.ok) {
            window.location.assign(body?.redirect ?? '/admin/app');
            return;
        }
        if (res.status === 419) return fail({ form: 'این صفحه مدتی باز مانده است. صفحه را تازه کنید و دوباره وارد شوید.' });
        const fieldErrors = body?.errors as Record<string, string[]> | undefined;
        if (fieldErrors) {
            setPassword('');
            return fail({ email: fieldErrors.email?.[0], password: fieldErrors.password?.[0] });
        }
        fail({ form: `خطای سرور (${res.status}). دوباره تلاش کنید.` });
    }

    return (
        <main className="grid min-h-dvh place-items-center px-4 py-10">
            <div className="w-full max-w-[400px]">
                <div className="mb-6 flex items-center justify-center gap-2.5">
                    <span aria-hidden className="grid size-9 place-items-center rounded-xl bg-primary text-[15px] font-bold text-primary-ink">
                        ش
                    </span>
                    <span className="text-[16px] font-bold">مدیریت شاپ مجازی</span>
                </div>

                <form noValidate onSubmit={submit} className="rounded-2xl border border-stroke bg-surface p-6 shadow-[0_12px_32px_-16px_rgb(15_23_42/0.18)] sm:p-8">
                    <h1 className="text-[20px] leading-tight font-bold">ورود</h1>
                    <p className="mt-1 text-muted">با ایمیل و کلمه عبور مدیر وارد شوید.</p>

                    {errors.form && (
                        <p role="alert" className="mt-5 rounded-xl bg-error-soft px-3 py-2.5 text-[13px] font-medium text-error">
                            {errors.form}
                        </p>
                    )}

                    <div className="mt-6">
                        <label htmlFor="email" className="mb-1.5 block font-medium">
                            ایمیل
                        </label>
                        <input
                            ref={emailRef}
                            id="email"
                            type="email"
                            dir="ltr"
                            autoComplete="username"
                            autoFocus={!boot.email}
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            aria-invalid={!!errors.email}
                            aria-describedby={errors.email ? 'email-error' : undefined}
                            className="field latin text-start"
                        />
                        {errors.email && <FieldError id="email-error">{errors.email}</FieldError>}
                    </div>

                    <div className="mt-4">
                        <label htmlFor="password" className="mb-1.5 block font-medium">
                            کلمه عبور
                        </label>
                        <div className="relative">
                            <input
                                ref={passwordRef}
                                id="password"
                                type={show ? 'text' : 'password'}
                                dir="ltr"
                                autoComplete="current-password"
                                autoFocus={!!boot.email}
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                aria-invalid={!!errors.password}
                                aria-describedby={errors.password ? 'password-error' : undefined}
                                className="field latin pe-11 text-start"
                            />
                            <button
                                type="button"
                                onClick={() => setShow(!show)}
                                aria-label={show ? 'پنهان کردن کلمه عبور' : 'نمایش کلمه عبور'}
                                aria-pressed={show}
                                className="absolute inset-y-0 end-0 grid w-10 place-items-center rounded-e-[10px] text-muted hover:text-ink"
                            >
                                {show ? <EyeOff aria-hidden size={17} /> : <Eye aria-hidden size={17} />}
                            </button>
                        </div>
                        {errors.password && <FieldError id="password-error">{errors.password}</FieldError>}
                    </div>

                    <label className="mt-4 flex w-fit cursor-pointer items-center gap-2 text-body">
                        <input type="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} className="size-4 accent-[var(--primary)]" />
                        مرا به خاطر بسپار
                    </label>

                    <button type="submit" disabled={busy} className="btn btn-primary mt-6 w-full">
                        {busy ? (
                            <>
                                <span aria-hidden className="size-4 animate-spin rounded-full border-2 border-current border-t-transparent" />
                                در حال ورود…
                            </>
                        ) : (
                            <>
                                <LogIn aria-hidden size={17} className="rtl:-scale-x-100" />
                                ورود
                            </>
                        )}
                    </button>
                </form>
            </div>
        </main>
    );
}

const root = document.getElementById('admin-login');
if (root) {
    const boot = JSON.parse(root.dataset.boot ?? '{}') as LoginBoot;
    createRoot(root).render(
        <StrictMode>
            <LoginPage boot={boot} />
        </StrictMode>,
    );
}
