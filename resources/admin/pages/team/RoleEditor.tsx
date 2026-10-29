import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router';
import { Trash2 } from 'lucide-react';
import { postJson, useQuery, type ApiError } from '../../lib/api';
import { useSave } from '../../lib/useSave';
import { count } from '../../lib/format';
import { EditorLayout } from '../../components/EditorLayout';
import { Field, slugify } from '../../components/form';
import { ErrorState, Panel } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, problemsFrom } from '../../components/list';
import { PermissionPicker, type PermissionGroup } from './PermissionPicker';

type Role = { id: number; name: string; faName: string; system: boolean; permissionIds: number[]; admins: { id: number; name: string; email: string }[] };
type Form = { name: string; faName: string; permissionIds: number[] };

const labels = { faName: 'نام نقش', name: 'نام انگلیسی', permissionIds: 'دسترسی‌ها' };

export default function RoleEditor() {
    const { id } = useParams();
    const isNew = id === 'new';
    const navigate = useNavigate();
    const existing = useQuery<{ role: Role; groups: PermissionGroup[] }>(isNew ? null : `/admin/api/roles/${id}`);
    const catalog = useQuery<{ groups: PermissionGroup[] }>(isNew ? '/admin/api/permissions' : null);
    const save = useSave();
    const [form, setForm] = useState<Form | null>(isNew ? { name: '', faName: '', permissionIds: [] } : null);
    const [nameTouched, setNameTouched] = useState(!isNew);
    const [deleteError, setDeleteError] = useState<string | null>(null);
    const [deleting, setDeleting] = useState(false);

    const role = existing.data?.role;
    useEffect(() => {
        if (role) setForm({ name: role.name, faName: role.faName, permissionIds: role.permissionIds });
    }, [role]);

    const groups = existing.data?.groups ?? catalog.data?.groups;
    const error = existing.error ?? catalog.error;
    if (error) return <ErrorState error={error} onRetry={isNew ? catalog.reload : existing.reload} boxed />;
    if (!form || !groups) return <EditorSkeleton />;

    const set = (patch: Partial<Form>) => {
        save.clearSaved();
        setForm((f) => (f ? { ...f, ...patch } : f));
    };
    const total = groups.reduce((n, g) => n + g.permissions.length, 0);

    async function submit() {
        if (!form) return;
        const res = await save.run<{ success: string; id: number }>(isNew ? '/admin/api/roles' : `/admin/api/roles/${id}`, form);
        if (res && isNew) navigate(`/roles/${res.id}`, { replace: true });
        else if (res) existing.reload();
    }

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`/admin/api/roles/${id}/delete`, save.csrfToken, {});
            navigate('/roles', { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
        } finally {
            setDeleting(false);
        }
    }

    return (
        <EditorLayout
            backTo="/roles"
            backLabel="نقش‌ها و دسترسی‌ها"
            title={isNew ? 'نقش جدید' : form.faName || role?.name || 'نقش'}
            subtitle={isNew ? 'نام نقش را بنویسید و دسترسی‌هایش را تیک بزنید.' : undefined}
            onSubmit={submit}
            saving={save.saving}
            saveLabel={isNew ? 'ساخت نقش' : 'ذخیره نقش'}
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                role ? (
                    <div className="space-y-4">
                        <div>
                            <h2 className="mb-2 font-bold">مدیران این نقش</h2>
                            {role.admins.length ? (
                                <ul className="space-y-1">
                                    {role.admins.map((a) => (
                                        <li key={a.id}>
                                            <Link to={`/admins/${a.id}`} className="font-medium underline-offset-4 hover:underline">{a.name || a.email}</Link>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-[13px] text-muted">هیچ مدیری این نقش را ندارد.</p>
                            )}
                        </div>
                        {!role.system && (
                            <div className="border-t border-stroke pt-4">
                                {role.admins.length ? (
                                    <p className="text-[13px] text-muted">برای حذف این نقش، اول نقش مدیرانش را عوض کنید.</p>
                                ) : (
                                    <ConfirmButton
                                        label="حذف نقش"
                                        confirmLabel="حذف"
                                        question="این نقش حذف شود؟"
                                        icon={<Trash2 aria-hidden size={16} />}
                                        busy={deleting}
                                        className="btn btn-secondary w-full text-error"
                                        onConfirm={remove}
                                    />
                                )}
                                {deleteError && <p role="alert" className="mt-2 text-[13px] font-bold text-error">{deleteError}</p>}
                            </div>
                        )}
                    </div>
                ) : undefined
            }
        >
            <Panel title="مشخصات">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="نام نقش" error={save.errors.faName}>
                        {(p) => (
                            <input
                                {...p}
                                className="field"
                                value={form.faName}
                                onChange={(e) => set({ faName: e.target.value, ...(nameTouched ? {} : { name: slugify(e.target.value) }) })}
                                placeholder="پشتیبان"
                            />
                        )}
                    </Field>
                    <Field
                        label="نام انگلیسی"
                        error={save.errors.name}
                        hint={role?.system ? 'نام نقش ادمین کل عوض نمی‌شود.' : 'حروف کوچک انگلیسی و خط تیره، مثل support.'}
                    >
                        {(p) => (
                            <input
                                {...p}
                                dir="ltr"
                                className="field latin"
                                value={form.name}
                                readOnly={role?.system}
                                onChange={(e) => {
                                    setNameTouched(true);
                                    set({ name: e.target.value.toLowerCase() });
                                }}
                                placeholder="support"
                            />
                        )}
                    </Field>
                </div>
            </Panel>

            <Panel title="دسترسی‌ها">
                {role?.system ? (
                    <p className="text-muted">ادمین کل از همه بررسی‌های دسترسی رد می‌شود؛ لازم نیست چیزی تیک بزنید.</p>
                ) : (
                    <>
                        <p className="mb-3 text-muted">
                            <span className="num font-bold text-ink">{count(form.permissionIds.length)}</span> از <span className="num">{count(total)}</span> دسترسی انتخاب شده.
                            بخش‌هایی از منو که دسترسی‌اش نیست، برای مدیران این نقش دیده نمی‌شوند.
                        </p>
                        <PermissionPicker groups={groups} selected={form.permissionIds} onChange={(permissionIds) => set({ permissionIds })} />
                    </>
                )}
            </Panel>
        </EditorLayout>
    );
}
