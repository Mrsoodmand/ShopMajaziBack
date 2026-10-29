import { useState } from 'react';
import { useNavigate } from 'react-router';
import { ClipboardPaste } from 'lucide-react';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, RowsEditor } from '../../components/form';
import { Panel } from '../../components/ui';
import { problemsFrom } from '../../components/list';
import { useSave } from '../../lib/useSave';
import { blankRow, type StockKind } from './config';

/** Adds several logins at once: typed row by row, or pasted from a spreadsheet (one per line, columns in field order). */
export default function StockAdd({ kind }: { kind: StockKind }) {
    const navigate = useNavigate();
    const save = useSave();
    const [rows, setRows] = useState<Record<string, string>[]>([blankRow(kind)]);
    const [paste, setPaste] = useState('');
    const [pasteNote, setPasteNote] = useState<string | null>(null);

    function readPaste() {
        const lines = paste.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
        const parsed = lines.map((line) => {
            const cells = line.includes('\t') ? line.split('\t') : line.split(/\s*[|,;]\s*/);
            return Object.fromEntries(kind.fields.map((f, i) => [f.key, (cells[i] ?? '').trim()]));
        });
        if (!parsed.length) return;
        const kept = rows.filter((r) => Object.values(r).some((v) => v.trim()));
        setRows([...kept, ...parsed]);
        setPaste('');
        setPasteNote(`${parsed.length} ردیف اضافه شد. پیش از ثبت، ستون‌ها را بررسی کنید.`);
    }

    async function submit() {
        const items = rows.filter((r) => Object.values(r).some((v) => v.trim()));
        const res = await save.run<{ success: string }>(kind.api, { items });
        if (res) navigate(kind.base, { state: { notice: res.success } });
    }

    const labels: Record<string, string> = { items: 'ردیف‌ها', ...Object.fromEntries(kind.fields.map((f) => [`items.${f.key}`, f.label])) };
    const filled = rows.filter((r) => Object.values(r).some((v) => v.trim())).length;

    return (
        <EditorLayout
            backTo={kind.base}
            backLabel={kind.title}
            title={kind.addTitle}
            subtitle="اطلاعات ورود رمزنگاری‌شده ذخیره می‌شود و فقط مدیران و خریدار آن را می‌بینند."
            onSubmit={submit}
            saving={save.saving}
            saveLabel={filled > 1 ? `ثبت ${filled} ${kind.noun}` : `ثبت ${kind.noun}`}
            saved={null}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={<p className="text-[13px] text-muted">ردیف‌های خالی نادیده گرفته می‌شوند.</p>}
        >
            <Panel title="چسباندن از اکسل یا متن">
                <Field
                    label="هر خط یک مورد"
                    optional
                    hint={<>ستون‌ها به این ترتیب، با Tab (کپی از اکسل)، | یا کاما جدا شوند: {kind.fields.map((f) => f.label).join('، ')}</>}
                >
                    {(p) => <textarea {...p} rows={4} dir="ltr" className="field latin text-[13px]" value={paste} onChange={(e) => setPaste(e.target.value)} />}
                </Field>
                <button type="button" className="btn btn-secondary mt-3" onClick={readPaste} disabled={!paste.trim()}>
                    <ClipboardPaste aria-hidden size={16} />
                    افزودن به ردیف‌ها
                </button>
                {pasteNote && <p role="status" className="mt-2 text-[13px] text-muted">{pasteNote}</p>}
            </Panel>

            <Panel title={`ردیف‌ها (${rows.length})`}>
                <RowsEditor
                    rows={rows}
                    onChange={setRows}
                    make={() => blankRow(kind)}
                    addLabel="ردیف دیگر"
                    empty="ردیفی نیست."
                    removeLabel={(r, i) => `حذف ردیف ${i + 1}`}
                    render={(row, update, i) => (
                        <div className="grid gap-3 sm:grid-cols-3">
                            {kind.fields.map((f) => (
                                <Field key={f.key} label={f.label} optional={f.optional} error={save.errors[`items.${i}.${f.key}`]} className={f.wide ? 'sm:col-span-2' : ''}>
                                    {(p) => (
                                        <input
                                            {...p}
                                            dir={f.ltr ? 'ltr' : undefined}
                                            autoComplete="off"
                                            className={`field ${f.ltr ? 'latin' : ''}`}
                                            value={row[f.key]}
                                            placeholder={f.placeholder}
                                            onChange={(e) => update({ [f.key]: e.target.value })}
                                        />
                                    )}
                                </Field>
                            ))}
                        </div>
                    )}
                />
            </Panel>
        </EditorLayout>
    );
}
