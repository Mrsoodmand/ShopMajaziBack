import { Link } from 'react-router';
import { Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { ErrorState, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader } from '../../components/list';

type AdminRow = {
    id: number;
    name: string;
    email: string;
    mobile: string;
    role: { id: number; name: string; faName: string } | null;
    extraPermissions: number;
    me: boolean;
    createdAt: string | null;
};

export default function AdminsList() {
    const { data, error, loading, reload } = useQuery<{ admins: AdminRow[] }>('/admin/api/admins');

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="مدیران"
                subtitle="کسانی که به این پنل وارد می‌شوند. ورود با ایمیل و رمز است."
                actions={
                    <>
                        <Link to="/roles" className="btn btn-secondary">
                            نقش‌ها
                        </Link>
                        <Link to="/admins/new" className="btn btn-primary">
                            <Plus aria-hidden size={16} />
                            مدیر جدید
                        </Link>
                    </>
                }
            />
            <section aria-label="فهرست مدیران" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={4} />
                ) : data.admins.length === 0 ? (
                    <EmptyState>مدیری نیست.</EmptyState>
                ) : (
                    <ul className="divide-y divide-stroke">
                        {data.admins.map((a) => (
                            <li key={a.id}>
                                <Link to={`/admins/${a.id}`} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-3.5 hover:bg-surface-2">
                                    <span aria-hidden className="grid size-10 shrink-0 place-items-center rounded-full bg-surface-2 font-bold text-muted">
                                        {(a.name || a.email).trim().charAt(0)}
                                    </span>
                                    <span className="min-w-0 flex-1">
                                        <span className="flex items-center gap-2 font-bold">
                                            {a.name || 'بدون نام'}
                                            {a.me && <span className="rounded-md bg-surface-2 px-1.5 text-[12px] font-medium text-muted">شما</span>}
                                        </span>
                                        <span dir="ltr" className="latin block truncate text-end text-[13px] text-muted sm:text-start">
                                            {a.email}
                                        </span>
                                    </span>
                                    <span dir="ltr" className="num hidden text-[13px] text-muted md:block">{a.mobile}</span>
                                    <span className="w-40 text-end text-[13px]">
                                        <span className="font-medium">{a.role?.faName ?? 'بدون نقش'}</span>
                                        {a.extraPermissions > 0 && <span className="block text-muted">+<span className="num">{a.extraPermissions}</span> دسترسی جدا</span>}
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
