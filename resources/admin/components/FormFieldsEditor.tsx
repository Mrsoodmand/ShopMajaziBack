import { Lock } from 'lucide-react';
import { Field, RowsEditor, Switch } from './form';

/*
 * The editor for a form the admin builds (App\Services\FormFields on the server): title, type, hints, select
 * options, file types and size, shown/required switches, order. Built-in fields can be retitled but not removed or
 * retyped; `locked` ones also stay shown and required. Used by «درخواست اکانت» (pages/account-requests).
 */

export type FieldType = 'text' | 'email' | 'phone' | 'textarea' | 'number' | 'select' | 'yesno' | 'date' | 'file';
export type FormField = {
    id?: number;
    key: string;
    title: string;
    title_en: string | null;
    type: FieldType;
    required: boolean;
    enabled: boolean;
    builtin: boolean;
    hint: string | null;
    hint_en: string | null;
    options: string[];
    accept: string[];
    max_mb: number | null;
};

export const TYPE_LABELS: Record<FieldType, string> = {
    text: 'متن کوتاه', email: 'ایمیل', phone: 'تلفن', textarea: 'متن بلند', number: 'عدد', select: 'انتخاب از فهرست', yesno: 'بله / خیر', date: 'تاریخ', file: 'فایل',
};

export function FormFieldsEditor({
    fields,
    onChange,
    maxMb,
    locked = [],
    placeholder = 'عنوان فیلد',
}: {
    fields: FormField[];
    onChange: (fields: FormField[]) => void;
    maxMb: number;
    locked?: string[];
    placeholder?: string;
}) {
    const move = (i: number, d: -1 | 1) => {
        const j = i + d;
        if (j < 0 || j >= fields.length) return;
        const next = [...fields];
        [next[i], next[j]] = [next[j], next[i]];
        onChange(next);
    };
    return (
        <RowsEditor
            rows={fields}
            onChange={(rows) => {
                // Built-in fields can't be removed: a removal that drops one is ignored.
                const removedBuiltin = rows.length < fields.length && fields.some((f) => f.builtin && !rows.includes(f));
                onChange(removedBuiltin ? fields : rows);
            }}
            make={(): FormField => ({ key: '', title: '', title_en: null, type: 'text', required: false, enabled: true, builtin: false, hint: null, hint_en: null, options: [], accept: ['image'], max_mb: 5 })}
            addLabel="افزودن فیلد"
            empty="فیلدی تعریف نشده است."
            removeLabel={(r, i) => `حذف فیلد ${r.title || i + 1}`}
            canRemove={(r) => !r.builtin}
            render={(row, update, i) => {
                const isLocked = locked.includes(row.key);
                return (
                    <div className={`grid gap-3 ${row.enabled ? '' : 'opacity-60'}`}>
                        <div className="flex flex-wrap items-center justify-between gap-2">
                            <span className="flex items-center gap-2 text-[13px] text-muted">
                                <span className="num">{i + 1}.</span>
                                {row.builtin ? (
                                    <span className="rounded-md bg-surface-2 px-1.5 py-0.5">
                                        <Lock aria-hidden size={12} className="inline" /> فیلد اصلی
                                    </span>
                                ) : (
                                    <span>فیلد شما</span>
                                )}
                            </span>
                            <span className="flex gap-1">
                                <button type="button" className="btn btn-ghost h-7 px-2 text-[13px]" onClick={() => move(i, -1)} disabled={i === 0} aria-label="بالاتر">▲</button>
                                <button type="button" className="btn btn-ghost h-7 px-2 text-[13px]" onClick={() => move(i, 1)} disabled={i === fields.length - 1} aria-label="پایین‌تر">▼</button>
                            </span>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px]">
                            <Field label="عنوان">{(p) => <input {...p} className="field" value={row.title} onChange={(e) => update({ title: e.target.value })} placeholder={placeholder} />}</Field>
                            <Field label="نوع">
                                {(p) => (
                                    <select {...p} className="field" value={row.type} disabled={row.builtin} onChange={(e) => update({ type: e.target.value as FieldType })}>
                                        {(Object.keys(TYPE_LABELS) as FieldType[]).map((t) => <option key={t} value={t}>{TYPE_LABELS[t]}</option>)}
                                    </select>
                                )}
                            </Field>
                        </div>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <Field label="عنوان انگلیسی" optional>{(p) => <input {...p} dir="ltr" className="field latin" value={row.title_en ?? ''} onChange={(e) => update({ title_en: e.target.value })} />}</Field>
                            <Field label="راهنما زیر خانه" optional>{(p) => <input {...p} className="field" value={row.hint ?? ''} onChange={(e) => update({ hint: e.target.value })} />}</Field>
                        </div>
                        {row.type === 'select' && (
                            <Field label="گزینه‌ها" hint="با کاما جدا کنید؛ دست‌کم دو گزینه.">
                                {(p) => <input {...p} className="field" defaultValue={row.options.join('، ')} onBlur={(e) => update({ options: e.target.value.split(/[,،]/).map((o) => o.trim()).filter(Boolean) })} placeholder="یک‌ماهه، سه‌ماهه، یک‌ساله" />}
                            </Field>
                        )}
                        {row.type === 'file' && (
                            <div className="grid gap-3 sm:grid-cols-[minmax(0,1fr)_140px] sm:items-end">
                                <div className="flex flex-wrap gap-x-6 gap-y-2">
                                    <Switch label="عکس" checked={row.accept.includes('image')} onChange={(on) => update({ accept: on ? [...row.accept, 'image'] : row.accept.filter((a) => a !== 'image') })} />
                                    <Switch label="PDF" checked={row.accept.includes('pdf')} onChange={(on) => update({ accept: on ? [...row.accept, 'pdf'] : row.accept.filter((a) => a !== 'pdf') })} />
                                </div>
                                <Field label={`حداکثر حجم (تا ${maxMb}MB)`}>{(p) => <input {...p} inputMode="numeric" dir="ltr" className="field num text-end" value={row.max_mb ?? 5} onChange={(e) => update({ max_mb: Number(e.target.value) || 1 })} />}</Field>
                            </div>
                        )}
                        {isLocked ? (
                            <p className="text-[13px] text-muted">همیشه در فرم و اجباری است.</p>
                        ) : (
                            <div className="flex flex-wrap gap-x-6">
                                <Switch label="نمایش در فرم" checked={row.enabled} onChange={(enabled) => update({ enabled })} />
                                <Switch label="اجباری" checked={row.required} onChange={(required) => update({ required })} />
                            </div>
                        )}
                    </div>
                );
            }}
        />
    );
}
