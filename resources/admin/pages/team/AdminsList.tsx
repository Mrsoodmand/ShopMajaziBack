import { Link } from 'react-router';
import { Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { CopyButton, Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

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

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'name', label: 'نام' },
    { value: 'newest', label: 'جدیدترین' },
];

function Avatar({ a }: { a: AdminRow }) {
    return (
        <span aria-hidden className="grid size-10 shrink-0 place-items-center rounded-full bg-surface-2 font-bold text-muted">
            {(a.name || a.email).trim().charAt(0)}
        </span>
    );
}

export default function AdminsList() {
    const list = useListParams({ q: '', sort: '', per: '', role: '', extra: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ admins: AdminRow[] }>('/admin/api/admins');
    const roles = [...new Map((data?.admins ?? []).filter((a) => a.role).map((a) => [String(a.role!.id), a.role!.faName || a.role!.name])).entries()];
    const filters: FilterDef[] = [
        { type: 'select', key: 'role', label: 'نقش', any: 'همه نقش‌ها', options: [...roles.map(([value, label]) => ({ value, label })), { value: 'none', label: 'بدون نقش' }] },
        { type: 'toggle', key: 'extra', label: 'دسترسی جدا از نقش', hint: 'مدیرانی که دسترسی اضافه دارند' },
    ];
    const shown = useClientList(data?.admins, v, list.page, {
        search: (a, q) => [a.name, a.email, a.mobile].some((s) => (s ?? '').toLowerCase().includes(q)),
        filters: { role: (a, val) => (val === 'none' ? !a.role : String(a.role?.id) === val), extra: (a) => a.extraPermissions > 0 },
        sorts: { name: (a, b) => (a.name || a.email).localeCompare(b.name || b.email, 'fa'), newest: (a, b) => b.id - a.id },
    });

    const columns: Column<AdminRow>[] = [
        {
            key: 'name',
            header: 'مدیر',
            primary: true,
            cell: (a) => (
                <span className="flex items-center gap-3">
                    <Avatar a={a} />
                    <span className="min-w-0">
                        <span className="flex items-center gap-2 font-bold">
                            {a.name || 'بدون نام'}
                            {a.me && <span className="rounded-md bg-surface-2 px-1.5 text-[12px] font-medium text-muted">شما</span>}
                        </span>
                        <span dir="ltr" className="latin block truncate text-end text-[13px] text-muted">
                            {a.email}
                        </span>
                    </span>
                </span>
            ),
        },
        { key: 'mobile', header: 'موبایل', from: 'lg', cell: (a) => <span dir="ltr" className="num text-[13px]">{a.mobile || '—'}</span> },
        {
            key: 'role',
            header: 'نقش',
            cell: (a) => (
                <span className="text-[13px]">
                    {a.role ? (
                        <Link to={`/roles/${a.role.id}`} className="font-medium underline-offset-4 hover:underline">
                            {a.role.faName || a.role.name}
                        </Link>
                    ) : (
                        <span className="text-muted">بدون نقش</span>
                    )}
                    {a.extraPermissions > 0 && (
                        <span className="block text-muted">
                            +<span className="num">{a.extraPermissions}</span> دسترسی جدا
                        </span>
                    )}
                </span>
            ),
        },
        { key: 'created', header: 'عضو از', from: 'lg', cell: (a) => <span dir="ltr" className="num text-[13px] text-muted">{a.createdAt ?? '—'}</span> },
    ];

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
            <FilterBar search={{ label: 'جستجوی مدیر', placeholder: 'نام، ایمیل یا موبایل' }} filters={filters} sort={{ options: sorts }} values={v} onChange={list.update} total={shown?.total} noun="مدیر" loading={loading} onRefresh={reload} />
            <DataTable
                label="فهرست مدیران"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(a) => a.id}
                href={(a) => `/admins/${a.id}`}
                linkState={{ list: list.here }}
                rowLabel={(a) => `مدیر ${a.name || a.email}`}
                skeleton={4}
                quick={{
                    title: (a) => a.name || 'بدون نام',
                    subtitle: (a) => a.role?.faName ?? 'بدون نقش',
                    body: (a) => (
                        <QuickSection title="ورود و تماس">
                            <dl className="m-0">
                                <Row label="ایمیل" ltr>
                                    <span className="latin">{a.email}</span>
                                    <CopyButton value={a.email} label="ایمیل" />
                                </Row>
                                <Row label="موبایل" ltr>
                                    {a.mobile || '—'}
                                </Row>
                                <Row label="نقش">{a.role?.faName ?? 'بدون نقش'}</Row>
                                <Row label="دسترسی جدا" num>
                                    {a.extraPermissions}
                                </Row>
                                <Row label="عضو از" ltr>
                                    {a.createdAt ?? '—'}
                                </Row>
                            </dl>
                        </QuickSection>
                    ),
                }}
                mobile={(a) => (
                    <span className="flex items-center gap-3">
                        <Avatar a={a} />
                        <span className="min-w-0 flex-1">
                            <span className="block truncate font-bold">{a.name || 'بدون نام'}</span>
                            <span className="block truncate text-[13px] text-muted">{a.role?.faName ?? 'بدون نقش'}</span>
                        </span>
                    </span>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v)} onClear={() => list.update({ q: '', role: '', extra: '' })}>
                        مدیری نیست.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="مدیر" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}
