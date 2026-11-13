import { Children, isValidElement, useEffect, useId, useLayoutEffect, useRef, useState, type FormEvent, type ReactElement, type ReactNode } from 'react';
import { Link } from 'react-router';
import { AlertTriangle, ArrowRight } from 'lucide-react';
import { Notice } from './ui';
import { PageTabs, useUrlTab } from './PageTabs';

type Part = ReactElement<{ title?: string; tabLabel?: string }>;

/**
 * A create/edit screen: the form on the start side, a sticky panel with the save button on the end side.
 * Each top-level section (a <Panel title="…">, or any element with a `tabLabel`) becomes its own tab, kept in ?tab=.
 * Every tab stays mounted (only hidden), so typed text, editors and uploads survive switching tabs. When saving
 * fails validation, the panel lists every problem, the tabs holding invalid fields get a red count, and the
 * first of them opens.
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
    tabbed = true,
    tabKey = 'tab',
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
    /** false keeps every section on one page. */
    tabbed?: boolean;
    /** The address key that holds the open tab, for pages that already use ?tab= for something else. */
    tabKey?: string;
    children?: ReactNode;
}) {
    const summary = useRef<HTMLDivElement>(null);
    const body = useRef<HTMLDivElement>(null);
    const tabsId = useId();
    const parts = (Children.toArray(children).filter(isValidElement) as Part[]).filter((p) => p.props.tabLabel || p.props.title);
    const useTabs = tabbed && parts.length > 1;
    // Children.toArray keys each section by its place in the JSX, so a section shown only sometimes keeps its id.
    const ids = parts.map((p) => String(p.key ?? '').replace(/[^\w-]/g, '') || 'part');
    const [tab, setTab] = useUrlTab(ids, ids[0] ?? '', tabKey);
    const [invalid, setInvalid] = useState<Record<string, number>>({});
    const problemCount = problems?.length ?? 0;
    // Editors rebuild `problems` on every render, so the effect below keys on its content: otherwise each keystroke
    // after a failed save would pull focus to the error summary (and back to the first invalid tab).
    const problemsKey = problems?.length ? JSON.stringify(problems) : '';

    // Count invalid fields per tab after each render (fields mark themselves aria-invalid).
    useLayoutEffect(() => {
        if (!useTabs || !body.current) return;
        const next: Record<string, number> = {};
        body.current.querySelectorAll<HTMLElement>('[data-part]').forEach((el) => {
            const n = el.querySelectorAll('[aria-invalid="true"]').length;
            if (n) next[el.dataset.part!] = n;
        });
        setInvalid((prev) => (JSON.stringify(prev) === JSON.stringify(next) ? prev : next));
    });

    useEffect(() => {
        if (!problemCount) return;
        summary.current?.focus();
        if (!useTabs || !body.current) return;
        const firstBad = [...body.current.querySelectorAll<HTMLElement>('[data-part]')].find((el) => el.querySelector('[aria-invalid="true"]'));
        if (firstBad && firstBad.dataset.part !== tab) setTab(firstBad.dataset.part!);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [problemsKey]);

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
                <div ref={body} className="min-w-0">
                    {useTabs ? (
                        <>
                            <PageTabs
                                id={tabsId}
                                label={`بخش‌های ${title}`}
                                value={tab}
                                onChange={setTab}
                                tabs={parts.map((p, i) => ({
                                    id: ids[i],
                                    label: (p.props.tabLabel ?? p.props.title)!,
                                    count: invalid[ids[i]],
                                    tone: invalid[ids[i]] ? 'error' : undefined,
                                    note: 'فیلد نیازمند اصلاح',
                                }))}
                            />
                            {parts.map((p, i) => (
                                <div
                                    key={ids[i]}
                                    data-part={ids[i]}
                                    role="tabpanel"
                                    id={`${tabsId}-${ids[i]}-panel`}
                                    aria-labelledby={`${tabsId}-${ids[i]}`}
                                    hidden={ids[i] !== tab}
                                    className="space-y-6"
                                >
                                    {p}
                                </div>
                            ))}
                        </>
                    ) : (
                        <div className="space-y-6">{children}</div>
                    )}
                </div>
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
