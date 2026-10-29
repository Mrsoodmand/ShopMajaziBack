import { Link } from 'react-router';
import { Plus, ShieldCheck } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { ErrorState, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader } from '../../components/list';

type RoleRow = { id: number; name: string; faName: string; permissions: number | null; admins: number; system: boolean };

export default function RolesList() {
    const { data, error, loading, reload } = useQuery<{ permissionTotal: number; roles: RoleRow[] }>('/admin/api/roles');

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="نقش‌ها و دسترسی‌ها"
                subtitle="هر مدیر یک نقش دارد و هر نقش مجموعه‌ای از دسترسی‌هاست. ادمین کل به همه‌چیز دسترسی دارد."
                actions={
                    <>
                        <Link to="/admins" className="btn btn-secondary">
                            مدیران
                        </Link>
                        <Link to="/roles/new" className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            نقش جدید
                        </Link>
                    </>
                }
            />
            <section aria-label="فهرست نقش‌ها" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={4} />
                ) : data.roles.length === 0 ? (
                    <EmptyState action={<Link to="/roles/new" className="btn btn-primary">نقش جدید</Link>}>هنوز نقشی ساخته نشده است.</EmptyState>
                ) : (
                    <ul className="divide-y divide-stroke">
                        {data.roles.map((r) => (
                            <li key={r.id}>
                                <Link to={`/roles/${r.id}`} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3.5 hover:bg-surface-2">
                                    <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-2">
                                        <ShieldCheck aria-hidden size={18} className={r.system ? 'text-primary' : 'text-muted'} />
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="block font-bold">{r.faName || r.name}</span>
                                        <span dir="ltr" className="latin block text-end text-[12px] text-muted sm:text-start">{r.name}</span>
                                    </span>
                                    <span className="text-[13px] text-muted">
                                        {r.system ? 'همه دسترسی‌ها' : <><span className="num">{count(r.permissions ?? 0)}</span> از <span className="num">{count(data.permissionTotal)}</span> دسترسی</>}
                                    </span>
                                    <span className="w-24 text-end text-[13px]">
                                        {r.admins ? <><span className="num font-bold">{count(r.admins)}</span> مدیر</> : <span className="text-muted">بدون مدیر</span>}
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </div>
    );
}
