import { useId, useRef, useState, type ReactNode } from 'react';
import { FolderOpen, ImagePlus, Plus, Trash2, Upload } from 'lucide-react';
import { uploadFile, type FieldErrors } from '../lib/api';
import { chooseImage } from '../lib/ckfinder';
import { useBoot } from '../lib/boot';
import { FieldError } from './ui';

export type ControlProps = { id: string; 'aria-invalid'?: boolean; 'aria-describedby'?: string };

/** Label, optional hint, the control, then its error, wired together for screen readers. */
export function Field({
    label,
    hint,
    error,
    optional,
    className = '',
    children,
}: {
    label: string;
    hint?: ReactNode;
    error?: string;
    optional?: boolean;
    className?: string;
    children: (props: ControlProps) => ReactNode;
}) {
    const id = useId();
    const describedBy = [hint ? `${id}-hint` : null, error ? `${id}-error` : null].filter(Boolean).join(' ') || undefined;
    return (
        <div className={className}>
            <label htmlFor={id} className="mb-1 block font-medium">
                {label}
                {optional && <span className="font-normal text-muted"> (اختیاری)</span>}
            </label>
            {children({ id, 'aria-invalid': error ? true : undefined, 'aria-describedby': describedBy })}
            {hint && (
                <p id={`${id}-hint`} className="mt-1.5 text-[13px] text-muted">
                    {hint}
                </p>
            )}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </div>
    );
}

/** A labelled on/off switch built on a real checkbox. */
export function Switch({ label, hint, checked, onChange }: { label: string; hint?: string; checked: boolean; onChange: (v: boolean) => void }) {
    return (
        <label className="flex cursor-pointer items-start gap-3 rounded-lg py-1.5">
            <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} className="peer sr-only" />
            <span
                aria-hidden
                className={`relative mt-0.5 h-5 w-9 shrink-0 rounded-full transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-ring ${
                    checked ? 'bg-primary' : 'bg-field'
                }`}
            >
                <span className={`absolute top-0.5 size-4 rounded-full bg-white shadow-sm transition-[inset-inline-start] ${checked ? 'start-[18px]' : 'start-0.5'}`} />
            </span>
            <span>
                <span className="block font-medium">{label}</span>
                {hint && <span className="block text-[13px] text-muted">{hint}</span>}
            </span>
        </label>
    );
}

/** Image path with a preview and an upload button; the server stores the file and returns its public path. */
/** `noSvg`: for images other sites show (share images), where SVG doesn't work. */
export function ImageField({ label, value, onChange, error, uploadUrl, square, noSvg, hint }: { label: string; value: string; onChange: (path: string) => void; error?: string; uploadUrl: string; square?: boolean; noSvg?: boolean; hint?: string }) {
    const { csrfToken } = useBoot();
    const input = useRef<HTMLInputElement>(null);
    const [busy, setBusy] = useState(false);
    const [uploadError, setUploadError] = useState<string | null>(null);
    const id = useId();

    async function pick(file: File | undefined) {
        if (!file) return;
        setBusy(true);
        setUploadError(null);
        try {
            const { path } = await uploadFile(uploadUrl, csrfToken, file);
            onChange(path);
        } catch (e) {
            const err = e as { fields?: FieldErrors; message: string };
            setUploadError(err.fields?.file ?? err.message);
        } finally {
            setBusy(false);
            if (input.current) input.current.value = '';
        }
    }

    // Pick a file already in the media library (CKFinder), or upload one there.
    async function fromLibrary() {
        setUploadError(null);
        try {
            const url = await chooseImage();
            if (noSvg && /\.svg($|\?)/i.test(url)) {
                setUploadError('اینجا SVG کار نمی‌کند؛ یک تصویر PNG، JPG یا WebP انتخاب کنید.');
                return;
            }
            onChange(url);
        } catch (e) {
            setUploadError((e as Error).message);
        }
    }

    const shown = error ?? uploadError ?? undefined;
    return (
        <div>
            <span id={`${id}-label`} className="mb-1 block font-medium">
                {label}
            </span>
            <div className="flex items-center gap-3">
                <div className={`grid shrink-0 place-items-center overflow-hidden rounded-xl border border-stroke bg-surface-2 ${square ? 'size-16' : 'h-16 w-24'}`}>
                    {value ? <img src={value} alt="" className="size-full object-contain" /> : <ImagePlus aria-hidden size={22} className="text-muted" />}
                </div>
                <div className="flex flex-col gap-1.5">
                    <input ref={input} type="file" accept={noSvg ? 'image/png,image/jpeg,image/webp' : 'image/png,image/jpeg,image/webp,image/svg+xml'} className="sr-only" id={`${id}-file`} onChange={(e) => pick(e.target.files?.[0])} />
                    <div className="flex gap-2">
                        <button type="button" className="btn btn-secondary" onClick={() => input.current?.click()} disabled={busy} aria-describedby={`${id}-label`}>
                            <Upload aria-hidden size={16} />
                            {busy ? 'در حال بارگذاری…' : value ? 'تغییر تصویر' : 'بارگذاری تصویر'}
                        </button>
                        <button type="button" className="btn btn-secondary" onClick={fromLibrary} disabled={busy} aria-describedby={`${id}-label`}>
                            <FolderOpen aria-hidden size={16} />
                            از کتابخانه
                        </button>
                        {value && (
                            <button type="button" className="btn btn-ghost" onClick={() => onChange('')}>
                                حذف
                            </button>
                        )}
                    </div>
                    <span className="text-[12px] text-muted">{hint ?? (noSvg ? 'PNG، JPG یا WebP، حداکثر ۲ مگابایت' : 'PNG، JPG، WebP یا SVG، حداکثر ۲ مگابایت')}</span>
                </div>
            </div>
            {shown && <FieldError id={`${id}-error`}>{shown}</FieldError>}
        </div>
    );
}

/** Edits a list of rows: each row renders its own inputs; rows can be added and removed. */
export function RowsEditor<T>({
    rows,
    onChange,
    make,
    addLabel,
    empty,
    render,
    removeLabel,
}: {
    rows: T[];
    onChange: (rows: T[]) => void;
    make: () => T;
    addLabel: string;
    empty: string;
    removeLabel: (row: T, i: number) => string;
    render: (row: T, update: (patch: Partial<T>) => void, i: number) => ReactNode;
}) {
    return (
        <div>
            {rows.length === 0 && <p className="mb-3 text-muted">{empty}</p>}
            <ol className="space-y-3">
                {rows.map((row, i) => (
                    <li key={i} className="flex items-start gap-2 rounded-xl border border-stroke p-3">
                        <div className="min-w-0 flex-1">{render(row, (patch) => onChange(rows.map((r, j) => (j === i ? { ...r, ...patch } : r))), i)}</div>
                        <button
                            type="button"
                            className="btn btn-ghost mt-6 shrink-0 px-2 text-error"
                            onClick={() => onChange(rows.filter((_, j) => j !== i))}
                            aria-label={removeLabel(row, i)}
                            title="حذف"
                        >
                            <Trash2 aria-hidden size={16} />
                        </button>
                    </li>
                ))}
            </ol>
            <button type="button" className="btn btn-secondary mt-3" onClick={() => onChange([...rows, make()])}>
                <Plus aria-hidden size={16} />
                {addLabel}
            </button>
        </div>
    );
}

/** Lowercase latin slug from any text (Persian characters are dropped). */
export function slugify(text: string): string {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 60);
}
