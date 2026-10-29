import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { Trash2 } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, problemsFrom } from '../../components/list';
import { BotMessageBadge, type BotMessageStatus } from './parts';
import { SendingBar } from './BotMessages';

type Message = {
    id: number;
    title: string;
    text: string;
    status: BotMessageStatus;
    createdAt: string | null;
    progress: { sent: number; total: number } | null;
    waitingBehind: number;
};
type Form = { title: string; text: string; status: 'queued' | 'draft' };

const LIMIT = 4000;
const labels = { title: 'عنوان', text: 'متن پیام', status: 'وضعیت' };

export default function BotMessageEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const { data, error, reload } = useQuery<{ message: Message; audience: number }>(isNew ? null : `/admin/api/bot/messages/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(isNew ? { title: '', text: '', status: 'draft' } : null);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const message = data?.message;
    useEffect(() => {
        if (message) setForm({ title: message.title, text: message.text, status: message.status === 'queued' ? 'queued' : 'draft' });
    }, [message]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!form) return <EditorSkeleton />;

    const set = (patch: Partial<Form>) => {
        save.clearSaved();
        setForm((f) => (f ? { ...f, ...patch } : f));
    };
    const wasSent = message?.status === 'sent';

    async function submit() {
        if (!form) return;
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/bot/messages' : `/admin/api/bot/messages/${id}`, form);
        if (res && isNew) navigate(`/bot/messages/${res.id}`, { replace: true });
        else if (res) reload();
    }

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`/admin/api/bot/messages/${id}/delete`, save.csrfToken, {});
            navigate('/bot/messages', { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
        } finally {
            setDeleting(false);
        }
    }

    const options: { value: Form['status']; label: string; hint: string }[] = [
        { value: 'draft', label: wasSent ? 'ارسال‌شده' : 'پیش‌نویس', hint: wasSent ? 'دوباره فرستاده نمی‌شود.' : 'ذخیره می‌شود ولی فرستاده نمی‌شود.' },
        {
            value: 'queued',
            label: wasSent ? 'دوباره برای همه بفرست' : 'در صف ارسال',
            hint: `برای ${data ? count(data.audience) : 'همه'} کاربر ربات فرستاده می‌شود.`,
        },
    ];

    return (
        <EditorLayout
            backTo="/bot/messages"
            backLabel="اطلاعیه‌های ربات"
            title={isNew ? 'اطلاعیه جدید' : form.title || 'اطلاعیه'}
            subtitle={message ? <span className="inline-flex items-center gap-2"><BotMessageBadge status={message.status} /> <span dir="ltr" className="num text-muted">{message.createdAt}</span></span> : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={form.status === 'queued' && message?.status !== 'queued' ? 'ذخیره و ارسال' : 'ذخیره'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-4">
                    <fieldset className="m-0 border-0 p-0">
                        <legend className="mb-2 font-bold">ارسال</legend>
                        <div className="space-y-2">
                            {options.map((o) => (
                                <label key={o.value} className="flex cursor-pointer gap-3 rounded-xl border border-stroke px-3 py-2.5 hover:bg-surface-2 has-[:checked]:border-ink">
                                    <input type="radio" name="message-status" className="mt-1 size-4 accent-[var(--primary)]" checked={form.status === o.value} onChange={() => set({ status: o.value })} />
                                    <span>
                                        <span className="block font-medium">{o.label}</span>
                                        <span className="block text-[13px] text-muted">{o.hint}</span>
                                    </span>
                                </label>
                            ))}
                        </div>
                    </fieldset>
                    {message?.status === 'queued' && message.waitingBehind > 0 && (
                        <p className="text-[13px] text-muted">
                            <span className="num">{count(message.waitingBehind)}</span> اطلاعیه دیگر جلوتر در صف است.
                        </p>
                    )}
                    {message && (
                        <div className="border-t border-stroke pt-4">
                            <ConfirmButton
                                label="حذف اطلاعیه"
                                confirmLabel="حذف"
                                question={message.progress ? 'ارسال متوقف و اطلاعیه حذف شود؟' : 'این اطلاعیه حذف شود؟'}
                                icon={<Trash2 aria-hidden size={16} />}
                                busy={deleting}
                                className="btn btn-secondary w-full text-error"
                                onConfirm={remove}
                            />
                            {deleteError && <p role="alert" className="mt-2 text-[13px] font-bold text-error">{deleteError}</p>}
                        </div>
                    )}
                </div>
            }
        >
            {message?.progress && <SendingBar progress={message.progress} />}
            <Panel title="پیام">
                <div className="grid gap-4">
                    <Field label="عنوان" hint="فقط برای پیدا کردن اطلاعیه در این فهرست؛ برای کاربر فرستاده نمی‌شود." error={save.errors.title}>
                        {(p) => <input {...p} className="field" value={form.title} onChange={(e) => set({ title: e.target.value })} placeholder="تخفیف تمدید" />}
                    </Field>
                    <Field
                        label="متن پیام"
                        error={save.errors.text}
                        hint={
                            <>
                                <span className={`num ${form.text.length > LIMIT ? 'font-bold text-error' : ''}`}>{count(form.text.length)}</span> از <span className="num">{count(LIMIT)}</span> نویسه.
                                برای درشت‌نویسی <code dir="ltr" className="latin">&lt;b&gt;متن&lt;/b&gt;</code> و برای لینک <code dir="ltr" className="latin">&lt;a href="…"&gt;متن&lt;/a&gt;</code> بنویسید. زیر پیام دکمه «سرویس تست» می‌آید.
                            </>
                        }
                    >
                        {(p) => <textarea {...p} rows={12} dir="auto" className="field" value={form.text} onChange={(e) => set({ text: e.target.value })} />}
                    </Field>
                </div>
            </Panel>
        </EditorLayout>
    );
}
