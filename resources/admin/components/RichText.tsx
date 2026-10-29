import { useEffect, useId, useLayoutEffect, useRef, useState } from 'react';
import { Code2, Type } from 'lucide-react';
import { FieldError } from './ui';

/*
 * HTML editor for long texts (blog posts, page texts): CKEditor 4 from /plugins/ckeditor, the editor the old admin used,
 * loaded on first use. Images come from the media library (CKFinder). "HTML" switches to the raw source in a textarea;
 * if the editor script can't load, the textarea is all there is.
 *
 * CKEditor rewrites the DOM around the element it replaces, so that element is created here by hand inside a box React
 * never re-renders, and the editor is destroyed in a layout effect, while the box is still in the page. Destroying it
 * after React has removed the element makes CKEditor throw (it reads styles of a detached node).
 */

type CKEditorInstance = {
    status: string;
    getData(): string;
    setData(html: string): void;
    on(event: string, cb: () => void): void;
    destroy(noUpdate?: boolean): void;
};
type CKEditorApi = { replace(el: HTMLElement, config: Record<string, unknown>): CKEditorInstance };

declare global {
    interface Window {
        CKEDITOR?: CKEditorApi;
    }
}

let loading: Promise<CKEditorApi> | null = null;

function loadEditor(): Promise<CKEditorApi> {
    if (window.CKEDITOR) return Promise.resolve(window.CKEDITOR);
    loading ??= new Promise<CKEditorApi>((resolve, reject) => {
        const s = document.createElement('script');
        s.src = '/plugins/ckeditor/ckeditor.js';
        s.async = true;
        s.onload = () => (window.CKEDITOR ? resolve(window.CKEDITOR) : reject(new Error('ویرایشگر بارگذاری نشد.')));
        s.onerror = () => {
            loading = null;
            s.remove();
            reject(new Error('ویرایشگر بارگذاری نشد؛ متن را به صورت HTML بنویسید.'));
        };
        document.head.appendChild(s);
    });
    return loading;
}

export function RichText({
    label,
    value,
    onChange,
    error,
    hint,
    height = 360,
    optional,
}: {
    label: string;
    value: string;
    onChange: (html: string) => void;
    error?: string;
    hint?: string;
    height?: number;
    optional?: boolean;
}) {
    const id = useId();
    const box = useRef<HTMLDivElement>(null);
    const editor = useRef<CKEditorInstance | null>(null);
    const latest = useRef(value);
    const change = useRef(onChange);
    change.current = onChange;
    const [source, setSource] = useState(false);
    const [failed, setFailed] = useState<string | null>(null);
    const [ready, setReady] = useState(false);
    const plain = source || !!failed;

    // Mount the editor in the box (unless showing the source); tear it down before React touches the DOM.
    useLayoutEffect(() => {
        const container = box.current;
        if (plain || !container) return;
        let alive = true;
        let ed: CKEditorInstance | null = null;
        loadEditor()
            .then((CKEDITOR) => {
                if (!alive) return;
                const el = document.createElement('textarea');
                el.value = latest.current;
                container.appendChild(el);
                ed = CKEDITOR.replace(el, {
                    height,
                    language: 'fa',
                    contentsLangDirection: 'rtl',
                    title: label,
                    filebrowserBrowseUrl: '/admin/ckfinder/browser',
                    filebrowserImageBrowseUrl: '/admin/ckfinder/browser?type=Images',
                    removePlugins: 'elementspath',
                    resize_enabled: true,
                });
                const instance = ed;
                instance.on('instanceReady', () => {
                    // The value may have changed while the editor was loading.
                    if (instance.getData() !== latest.current) instance.setData(latest.current);
                    if (alive) setReady(true);
                });
                instance.on('change', () => {
                    const html = instance.getData();
                    latest.current = html;
                    change.current(html);
                });
                editor.current = instance;
            })
            .catch((e: Error) => alive && setFailed(e.message));
        return () => {
            alive = false;
            editor.current = null;
            setReady(false);
            if (ed) {
                try {
                    ed.destroy(true);
                } catch {
                    // An editor destroyed mid-load can still throw; its DOM is cleared below either way.
                }
            }
            container.replaceChildren();
        };
    }, [plain, height, label]);

    // A value from outside (a loaded record) replaces the editor's content.
    useEffect(() => {
        if (value === latest.current) return;
        latest.current = value;
        if (editor.current?.status === 'ready') editor.current.setData(value);
    }, [value]);

    return (
        <div>
            <div className="mb-1 flex items-center justify-between gap-2">
                <label htmlFor={plain ? id : undefined} className="font-medium">
                    {label}
                    {optional && <span className="font-normal text-muted"> (اختیاری)</span>}
                </label>
                {!failed && (
                    <button type="button" className="btn btn-ghost min-h-8 text-[13px]" onClick={() => setSource(!source)} aria-pressed={source}>
                        {source ? <Type aria-hidden size={15} /> : <Code2 aria-hidden size={15} />}
                        {source ? 'ویرایشگر' : 'HTML'}
                    </button>
                )}
            </div>
            <div className={`overflow-hidden rounded-xl ${error ? 'ring-1 ring-error' : ''}`}>
                {/* CKEditor's box: React renders no children here, so it never fights the editor over this DOM. */}
                <div ref={box} hidden={plain} />
                {!plain && !ready && <div aria-hidden className="animate-pulse rounded-xl bg-surface-2" style={{ height: height + 80 }} />}
                {plain && (
                    <textarea
                        id={id}
                        value={value}
                        onChange={(e) => {
                            latest.current = e.target.value;
                            onChange(e.target.value);
                        }}
                        rows={Math.round(height / 22)}
                        dir="auto"
                        aria-invalid={error ? true : undefined}
                        className="field font-mono text-[13px]"
                    />
                )}
            </div>
            {failed && <p className="mt-1.5 text-[13px] text-warn">{failed}</p>}
            {hint && <p className="mt-1.5 text-[13px] text-muted">{hint}</p>}
            {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
        </div>
    );
}
