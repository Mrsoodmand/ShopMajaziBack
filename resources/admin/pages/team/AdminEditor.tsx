import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router';
import { Eye, EyeOff, Trash2 } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, problemsFrom } from '../../components/list';
import { PermissionPicker, type PermissionGroup } from './PermissionPicker';

type RoleOption = { id: number; name: string; faName: string; all: boolean; permissionIds: number[] };
type Admin = { id: number; name: string; email: string; mobile: string; roleId: number | null; permissionIds: number[]; me: boolean };
type Options = { roles: RoleOption[]; groups: PermissionGroup[] };
type Form = { name: string; email: string; mobile: string; password: string; roleId: number | null; permissionIds: number[] };

const labels = { name: 'نام', email: 'ایمیل', mobile: 'موبایل', password: 'رمز', roleId: 'نقش', permissionIds: 'دسترسی‌ها' };

export default function AdminEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const existing = useQuery<{ admin: Admin } & Options>(isNew ? null : `/admin/api/admins/${id}`);
    const fresh = useQuery<Options>(isNew ? '/admin/api/admins/options' : null);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(null);
    const [showPassword, setShowPassword] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    const admin = existing.data?.admin;
    const options = existing.data ?? fresh.data;
    useEffect(() => {
        if (admin) setForm({ name: admin.name, email: admin.email, mobile: admin.mobile, password: '', roleId: admin.roleId, permissionIds: admin.permissionIds });
    }, [admin]);
    useEffect(() => {
        if (isNew && fresh.data && !form) {
            const plain = fresh.data.roles.find((r) => !r.all) ?? fresh.data.roles[0];
            setForm({ name: '', email: '', mobile: '', password: '', roleId: plain?.id ?? null, permissionIds: [] });
        }
    }, [isNew, fresh.data, form]);

    const error = existing.error ?? fresh.error;
    if (error) return <ErrorState error={error} onRetry={isNew ? fresh.reload : existing.reload} boxed />;
    if (!form || !options) return <EditorSkeleton />;

    const set = (patch: Partial<Form>) => {
        save.clearSaved();
        setForm((f) => (f ? { ...f, ...patch } : f));
    };
    const err = (k: string) => save.errors[k];
    const role = options.roles.find((r) => r.id === form.roleId);
    const fromRole = role?.permissionIds ?? [];
    const extra = form.permissionIds.filter((p) => !fromRole.includes(p));

    async function submit() {
        if (!form) return;
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/admins' : `/admin/api/admins/${id}`, { ...form, permissionIds: extra });
        if (res && isNew) navigate(`/admins/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`/admin/api/admins/${id}/delete`, save.csrfToken, {});
            navigate('/admins', { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
        } finally {
            setDeleting(false);
        }
    }

    return (
        <EditorLayout
            backTo="/admins"
            backLabel="مدیران"
            title={isNew ? 'مدیر جدید' : form.name || form.email || 'مدیر'}
            subtitle={admin?.me ? 'حساب خودتان' : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? 'ساخت مدیر' : 'ذخیره مدیر'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                admin && !admin.me ? (
                    <div>
                        <ConfirmButton
                            label="حذف مدیر"
                            confirmLabel="حذف"
                            question="دسترسی این مدیر به پنل برداشته شود؟"
                            icon={<Trash2 aria-hidden size={16} />}
                            busy={deleting}
                            className="btn btn-secondary w-full text-error"
                            onConfirm={remove}
                        />
                        {deleteError && <p role="alert" className="mt-2 text-[13px] font-bold text-error">{deleteError}</p>}
                    </div>
                ) : undefined
            }
        >
            <Panel title="حساب">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام و نام خانوادگی" error={err('name')}>
                        {(p) => <input {...p} className="field" value={form.name} onChange={(e) => set({ name: e.target.value })} autoComplete="off" />}
                    </Field>
                    <Field label="موبایل" error={err('mobile')}>
                        {(p) => <input {...p} inputMode="tel" dir="ltr" className="field num text-end" value={form.mobile} onChange={(e) => set({ mobile: e.target.value })} placeholder="09121234567" autoComplete="off" />}
                    </Field>
                    <Field label="ایمیل" hint="با همین ایمیل وارد پنل می‌شود." error={err('email')}>
                        {(p) => <input {...p} type="email" dir="ltr" className="field latin" value={form.email} onChange={(e) => set({ email: e.target.value })} autoComplete="off" />}
                    </Field>
                    <Field label={isNew ? 'رمز' : 'رمز تازه'} optional={!isNew} hint={isNew ? 'دست‌کم ۸ نویسه.' : 'خالی بماند تا رمز فعلی بماند.'} error={err('password')}>
                        {(p) => (
                            <div className="relative">
                                <input
                                    {...p}
                                    type={showPassword ? 'text' : 'password'}
                                    dir="ltr"
                                    className="field latin pe-11"
                                    value={form.password}
                                    onChange={(e) => set({ password: e.target.value })}
                                    autoComplete="new-password"
                                />
                                <button
                                    type="button"
                                    className="absolute inset-y-0 end-0 grid w-10 place-items-center text-muted hover:text-ink"
                                    onClick={() => setShowPassword(!showPassword)}
                                    aria-label={showPassword ? 'پنهان کردن رمز' : 'نمایش رمز'}
                                    aria-pressed={showPassword}
                                >
                                    {showPassword ? <EyeOff aria-hidden size={17} /> : <Eye aria-hidden size={17} />}
                                </button>
                            </div>
                        )}
                    </Field>
                </div>
            </Panel>

            <Panel title="نقش و دسترسی‌ها">
                <Field label="نقش" error={err('roleId')} hint={admin?.me && role?.all ? 'نقش ادمین کل را از خودتان نمی‌توانید بگیرید.' : undefined}>
                    {(p) => (
                        <select {...p} className="field sm:max-w-sm" value={form.roleId ?? ''} onChange={(e) => set({ roleId: Number(e.target.value) || null })}>
                            {options.roles.map((r) => (
                                <option key={r.id} value={r.id}>
                                    {r.faName}
                                </option>
                            ))}
                        </select>
                    )}
                </Field>
                {role?.all ? (
                    <p className="mt-4 text-muted">ادمین کل به همه بخش‌ها دسترسی دارد.</p>
                ) : (
                    <div className="mt-5">
                        <h3 className="font-bold">دسترسی‌ها</h3>
                        <p className="mt-1 mb-3 text-muted">
                            دسترسی‌های نقش «{role?.faName}» قفل‌اند. برای این مدیر می‌توانید دسترسی جدا هم تیک بزنید
                            {extra.length > 0 && <> (<span className="num">{count(extra.length)}</span> دسترسی جدا)</>}.
                        </p>
                        <PermissionPicker groups={options.groups} selected={extra} fixed={fromRole} onChange={(ids) => set({ permissionIds: ids })} />
                    </div>
                )}
            </Panel>
        </EditorLayout>
    );
}
