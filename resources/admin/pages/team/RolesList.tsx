import { Link } from 'react-router';
import { Plus, ShieldCheck } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { count } from '../../lib/format';
import { Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import type { PermissionGroup } from './PermissionPicker';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, QuickSkeleton, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

type RoleRow = { id: number; name: string; faName: string; permissions: number | null; admins: number; system: boolean };
type RoleDetail = { role: { admins: { id: number; name: string; email: string }[]; permissionIds: number[] }; groups: PermissionGroup[] };

const filters: FilterDef[] = [
    {
        type: 'select',
        key: 'used',
        label: 'مدیران',
        options: [
            { value: 'yes', label: 'دارای مدیر' },
            { value: 'no', label: 'بدون مدیر' },
        ],
    },
];

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'admins', label: 'بیشترین مدیر' },
    { value: 'perms', label: 'بیشترین دسترسی' },
];

export default function RolesList() {
    const list = useListParams({ q: '', sort: '', per: '', used: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ permissionTotal: number; roles: RoleRow[] }>('/admin/api/roles');
    const total = data?.permissionTotal ?? 0;
    const shown = useClientList(data?.roles, v, list.page, {
        search: (r, q) => r.name.toLowerCase().includes(q) || (r.faName ?? '').includes(q),
        filters: { used: (r, val) => (val === 'yes' ? r.admins > 0 : r.admins === 0) },
        sorts: { admins: (a, b) => b.admins - a.admins, perms: (a, b) => (b.system ? total : (b.permissions ?? 0)) - (a.system ? total : (a.permissions ?? 0)) },
    });
    const perms = (r: RoleRow) => (r.system ? 'همه دسترسی‌ها' : `${count(r.permissions ?? 0)} از ${count(total)}`);

    const columns: Column<RoleRow>[] = [
        {
            key: 'name',
            header: 'نقش',
            primary: true,
            cell: (r) => (
                <span className="flex items-center gap-3">
                    <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-2">
                        <ShieldCheck aria-hidden size={18} className={r.system ? 'text-primary' : 'text-muted'} />
                    </span>
                    <span className="min-w-0">
                        <span className="block font-bold">{r.faName || r.name}</span>
                        <span dir="ltr" className="latin block text-end text-[12px] text-muted">
                            {r.name}
                        </span>
                    </span>
                </span>
            ),
        },
        { key: 'perms', header: 'دسترسی', cell: (r) => <span className="num text-[13px]">{perms(r)}</span> },
        { key: 'admins', header: 'مدیر', align: 'end', cell: (r) => (r.admins ? <span className="num font-bold">{count(r.admins)}</span> : <span className="text-muted">بدون مدیر</span>) },
    ];

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
            <FilterBar search={{ label: 'جستجوی نقش', placeholder: 'نام نقش' }} filters={filters} sort={{ options: sorts }} values={v} onChange={list.update} total={shown?.total} noun="نقش" loading={loading} onRefresh={reload} />
            <DataTable
                label="فهرست نقش‌ها"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(r) => r.id}
                href={(r) => `/roles/${r.id}`}
                linkState={{ list: list.here }}
                rowLabel={(r) => `نقش ${r.faName || r.name}`}
                skeleton={4}
                quick={{
                    title: (r) => r.faName || r.name,
                    subtitle: (r) => <span className="num">{perms(r)} دسترسی</span>,
                    body: (r) => <RoleQuick id={r.id} system={r.system} />,
                }}
                mobile={(r) => (
                    <>
                        <span className="block font-bold">{r.faName || r.name}</span>
                        <span className="num block text-[13px] text-muted">
                            {perms(r)} · {r.admins ? `${count(r.admins)} مدیر` : 'بدون مدیر'}
                        </span>
                    </>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v)} onClear={() => list.update({ q: '', used: '' })} action={<Link to="/roles/new" className="btn btn-primary">نقش جدید</Link>}>
                        هنوز نقشی ساخته نشده است.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="نقش" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}

function RoleQuick({ id, system }: { id: number; system: boolean }) {
    const { data, error } = useQuery<RoleDetail>(`/admin/api/roles/${id}`);
    if (error) return <p className="text-error">{error.message}</p>;
    if (!data) return <QuickSkeleton />;
    const granted = new Set(data.role.permissionIds);
    return (
        <>
            <QuickSection title={`مدیران این نقش (${data.role.admins.length})`}>
                {data.role.admins.length === 0 ? (
                    <p className="pb-2 text-muted">مدیری این نقش را ندارد.</p>
                ) : (
                    <dl className="m-0">
                        {data.role.admins.map((a) => (
                            <Row key={a.id} label={a.name || 'بدون نام'} ltr>
                                <Link to={`/admins/${a.id}`} className="latin text-indigo underline-offset-4 hover:underline">
                                    {a.email}
                                </Link>
                            </Row>
                        ))}
                    </dl>
                )}
            </QuickSection>
            <QuickSection title="دسترسی‌ها">
                {system ? (
                    <p className="pb-2">ادمین کل به همه بخش‌ها دسترسی دارد.</p>
                ) : (
                    <dl className="m-0">
                        {data.groups.map((g) => {
                            const n = g.permissions.filter((p) => granted.has(p.id)).length;
                            return (
                                <Row key={g.id} label={g.name} num>
                                    <span className={n ? '' : 'font-normal text-muted'}>
                                        {n} از {g.permissions.length}
                                    </span>
                                </Row>
                            );
                        })}
                    </dl>
                )}
            </QuickSection>
        </>
    );
}
