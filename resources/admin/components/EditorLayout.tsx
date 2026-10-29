import { useEffect, useRef, type FormEvent, type ReactNode } from 'react';
import { Link } from 'react-router';
import { AlertTriangle, ArrowRight } from 'lucide-react';
import { Notice } from './ui';

/**
 * A create/edit screen: the form on the start side, a sticky panel with the save button on the end side.
 * When saving fails validation, the panel lists every problem, and focus moves to that list.
 */
export function EditorLayout({
    backTo,
    backLabel,
    title,
    subtitle,
    onSubmit,
    saving,
    saveLabel,
    saved,
    problems,
    side,
    nav,
    children,
}: {
    backTo: string;
    backLabel: string;
    title: string;
    subtitle?: ReactNode;
    onSubmit: () => void;
    saving: boolean;
    saveLabel: string;
    saved: string | null;
    problems: { label: string; message: string }[] | null;
    side?: ReactNode;
    /** Section tabs shown under the title (e.g. a record with several screens). */
    nav?: ReactNode;
    children: ReactNode;
}) {
    const summary = useRef<HTMLDivElement>(null);
    useEffect(() => {
        if (problems?.length) summary.current?.focus();
    }, [problems]);

    function submit(e: FormEvent) {
        e.preventDefault();
        onSubmit();
    }

    return (
        <form onSubmit={submit} noValidate className="mx-auto max-w-[1280px]">
            <Link to={backTo} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                {backLabel}
            </Link>
            <header className="mb-6">
                <h1 className="text-[22px] leading-tight font-bold">{title}</h1>
                {subtitle && <p className="mt-1.5 text-body">{subtitle}</p>}
            </header>
            {nav}
            <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div className="min-w-0 space-y-6">{children}</div>
                <aside className="space-y-4 rounded-2xl border border-stroke bg-surface p-5 lg:sticky lg:top-20">
                    {side}
                    {problems && problems.length > 0 && (
                        <div ref={summary} tabIndex={-1} role="alert" className="rounded-xl bg-error-soft px-4 py-3 text-error">
                            <p className="flex items-center gap-2 font-bold">
                                <AlertTriangle aria-hidden size={16} />
                                {problems.length} مورد نیاز به اصلاح دارد
                            </p>
                            <ul className="mt-1.5 list-disc space-y-0.5 ps-5 text-[13px]">
                                {problems.map((p, i) => (
                                    <li key={i}>
                                        <span className="font-bold">{p.label}:</span> {p.message}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    <button type="submit" className="btn btn-primary w-full" disabled={saving}>
                        {saving ? 'در حال ذخیره…' : saveLabel}
                    </button>
                    {saved && <Notice>{saved}</Notice>}
                </aside>
            </div>
        </form>
    );
}
