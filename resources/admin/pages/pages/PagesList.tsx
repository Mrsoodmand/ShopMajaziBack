import { Link } from 'react-router';
import { ExternalLink, PanelsTopLeft, SearchCheck } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { Row } from '../../components/ui';
import { PageHeader, useListParams } from '../../components/list';
import { DataTable, FilterBar, ListEmpty, ListPager, QuickSection, isFiltered, useClientList, type Column, type FilterDef } from '../../components/DataList';

type PageRow = { id: number; title: string; h1: string | null; path: string | null; faq: number; hasContent: boolean; updatedAt: string | null };

const filters: FilterDef[] = [
    {
        type: 'select',
        key: 'content',
        label: 'متن صفحه',
        options: [
            { value: 'yes', label: 'متن دارد' },
            { value: 'no', label: 'بدون متن' },
        ],
    },
    {
        type: 'select',
        key: 'faq',
        label: 'سؤالات متداول',
        options: [
            { value: 'yes', label: 'دارد' },
            { value: 'no', label: 'ندارد' },
        ],
    },
    { type: 'toggle', key: 'noh1', label: 'بدون H1' },
];

const sorts = [
    { value: '', label: 'ترتیب پیش‌فرض' },
    { value: 'title', label: 'عنوان' },
    { value: 'updated', label: 'آخرین ویرایش' },
];

export default function PagesList() {
    const list = useListParams({ q: '', sort: '', per: '', content: '', faq: '', noh1: '' });
    const v = list.values;
    const { data, error, loading, reload } = useQuery<{ pages: PageRow[] }>('/admin/api/pages');
    const shown = useClientList(data?.pages, v, list.page, {
        search: (p, q) => [p.title, p.h1, p.path].some((s) => (s ?? '').toLowerCase().includes(q)),
        filters: {
            content: (p, val) => (val === 'yes' ? p.hasContent : !p.hasContent),
            faq: (p, val) => (val === 'yes' ? p.faq > 0 : p.faq === 0),
            noh1: (p) => !p.h1,
        },
        sorts: { title: (a, b) => (a.title ?? '').localeCompare(b.title ?? '', 'fa'), updated: (a, b) => (b.updatedAt ?? '').localeCompare(a.updatedAt ?? '') },
    });

    const columns: Column<PageRow>[] = [
        {
            key: 'title',
            header: 'صفحه',
            primary: true,
            cell: (p) => (
                <span className="flex items-center gap-3">
                    <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-surface-2">
                        <PanelsTopLeft aria-hidden size={18} className="text-muted" />
                    </span>
                    <span className="min-w-0">
                        <span className="block truncate font-bold">{p.title || `صفحه ${p.id}`}</span>
                        <span className={`block max-w-[360px] truncate text-[13px] ${p.h1 ? 'text-muted' : 'text-warn'}`}>{p.h1 || 'بدون H1'}</span>
                    </span>
                </span>
            ),
        },
        { key: 'path', header: 'نشانی', from: 'lg', cell: (p) => (p.path ? <span dir="ltr" className="latin text-[13px] text-muted">{p.path}</span> : <span className="text-muted">—</span>) },
        { key: 'content', header: 'متن', cell: (p) => <span className={`text-[13px] ${p.hasContent ? '' : 'text-muted'}`}>{p.hasContent ? 'دارد' : 'ندارد'}</span> },
        { key: 'faq', header: 'سؤال', align: 'end', cell: (p) => <span className={`num ${p.faq ? '' : 'text-muted'}`}>{p.faq}</span> },
        { key: 'updated', header: 'آخرین ویرایش', from: 'lg', cell: (p) => <span dir="ltr" className="num text-[13px] text-muted">{p.updatedAt ?? '—'}</span> },
    ];

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="صفحه‌ها"
                subtitle="متن، تصویر و سؤالات متداول صفحه‌های اصلی خدمات. قیمت اپل آیدی و گوگل ویس هم در صفحه خودشان است."
                actions={
                    <Link to="/seo" className="btn btn-secondary">
                        <SearchCheck aria-hidden size={16} />
                        سئوی همه صفحه‌ها
                    </Link>
                }
            />
            <FilterBar
                search={{ label: 'جستجوی صفحه', placeholder: 'عنوان، H1 یا نشانی' }}
                filters={filters}
                sort={{ options: sorts }}
                values={v}
                onChange={list.update}
                total={shown?.total}
                noun="صفحه"
                loading={loading}
                onRefresh={reload}
            />
            <DataTable
                label="فهرست صفحه‌ها"
                rows={shown?.rows}
                loading={loading}
                error={error}
                onRetry={reload}
                columns={columns}
                rowKey={(p) => p.id}
                href={(p) => `/pages/${p.id}`}
                linkState={{ list: list.here }}
                rowLabel={(p) => `صفحه ${p.title || p.id}`}
                skeleton={6}
                quick={{
                    title: (p) => p.title || `صفحه ${p.id}`,
                    subtitle: (p) => (p.path ? <span dir="ltr" className="latin">{p.path}</span> : null),
                    body: (p) => (
                        <QuickSection
                            title="محتوا"
                            action={
                                p.path ? (
                                    <a href={p.path} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1 text-[13px] font-medium text-indigo hover:underline">
                                        <ExternalLink aria-hidden size={13} />
                                        دیدن در سایت
                                    </a>
                                ) : undefined
                            }
                        >
                            <dl className="m-0">
                                <Row label="H1">{p.h1 || <span className="text-warn">ندارد</span>}</Row>
                                <Row label="متن صفحه">{p.hasContent ? 'دارد' : 'ندارد'}</Row>
                                <Row label="سؤالات متداول" num>
                                    {p.faq}
                                </Row>
                                <Row label="آخرین ویرایش" ltr>
                                    {p.updatedAt ?? '—'}
                                </Row>
                            </dl>
                        </QuickSection>
                    ),
                }}
                mobile={(p) => (
                    <>
                        <span className="block truncate font-bold">{p.title || `صفحه ${p.id}`}</span>
                        <span className="block truncate text-[13px] text-muted">{p.h1 || 'بدون H1'}</span>
                    </>
                )}
                empty={
                    <ListEmpty filtered={isFiltered(v)} onClear={() => list.update({ q: '', content: '', faq: '', noh1: '' })}>
                        صفحه‌ای نیست.
                    </ListEmpty>
                }
            />
            {shown && <ListPager page={shown.page} lastPage={shown.lastPage} total={shown.total} perPage={shown.perPage} noun="صفحه" onPage={(p) => list.update({ page: String(p) })} onPer={(n) => list.update({ per: String(n) })} />}
        </div>
    );
}
