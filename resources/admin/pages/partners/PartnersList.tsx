import { Link } from 'react-router';
import { CircleCheck, CircleSlash, Plus } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { toman } from '../../lib/format';
import { Badge } from '../../components/Status';
import { ErrorState, SkeletonRows } from '../../components/ui';
import { EmptyState, PageHeader } from '../../components/list';

export type ServiceOption = { id: number; label: string };
type PartnerRow = {
    id: number;
    name: string;
    mobile: string;
    live: boolean;
    percent: number;
    serviceIds: number[];
    wallet: number;
    paidOut: number;
    orders: number;
    earned: number;
    deposit: 'daily' | 'weekly' | 'monthly';
};

export const DEPOSIT_LABEL = { daily: 'روزانه', weekly: 'هفتگی', monthly: 'ماهانه' } as const;

export default function PartnersList() {
    const { data, error, loading, reload } = useQuery<{ services: ServiceOption[]; partners: PartnerRow[] }>('/admin/api/partners');
    const label = (id: number) => data?.services.find((s) => s.id === id)?.label ?? `خدمت ${id}`;

    return (
        <div className="mx-auto max-w-[1280px]">
            <PageHeader
                title="همکاران"
                subtitle="تأمین‌کننده‌هایی که از هر سفارش پرداخت‌شده خدمت خودشان درصدی سهم می‌گیرند. سهم به کیف پول همکار می‌رود تا تسویه شود."
                actions={
                    <Link to="/partners/new" className="btn btn-primary">
                        <Plus aria-hidden size={16} />
                        همکار جدید
                    </Link>
                }
            />
            <section aria-label="فهرست همکاران" aria-busy={loading} className="overflow-hidden rounded-2xl border border-stroke bg-surface">
                {error ? (
                    <ErrorState error={error} onRetry={reload} />
                ) : !data ? (
                    <SkeletonRows rows={3} />
                ) : data.partners.length === 0 ? (
                    <EmptyState action={<Link to="/partners/new" className="btn btn-primary">همکار جدید</Link>}>هنوز همکاری ثبت نشده است.</EmptyState>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[760px]">
                            <thead>
                                <tr className="border-b border-stroke text-[13px] text-muted">
                                    <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">همکار</th>
                                    <th scope="col" className="px-3 text-start font-medium">خدمت‌ها</th>
                                    <th scope="col" className="px-3 text-end font-medium">سهم</th>
                                    <th scope="col" className="px-3 text-end font-medium">طلب فعلی</th>
                                    <th scope="col" className="px-3 text-end font-medium">کل سهم</th>
                                    <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                {data.partners.map((p) => (
                                    <tr key={p.id} className="border-b border-stroke last:border-0 hover:bg-surface-2">
                                        <td className="py-3 ps-5 pe-3">
                                            <Link to={`/partners/${p.id}`} className="font-bold underline-offset-4 hover:underline">{p.name}</Link>
                                            <span dir="ltr" className="num block text-end text-[13px] text-muted">{p.mobile}</span>
                                        </td>
                                        <td className="px-3 text-[13px]">{p.serviceIds.map(label).join('، ') || '—'}</td>
                                        <td className="num px-3 text-end">{p.percent}٪</td>
                                        <td className="num px-3 text-end font-bold">{toman(p.wallet)}</td>
                                        <td className="px-3 text-end text-[13px] text-muted">
                                            <span className="num">{toman(p.earned)}</span>
                                            <span className="block"><span className="num">{p.orders}</span> سفارش · تسویه {DEPOSIT_LABEL[p.deposit]}</span>
                                        </td>
                                        <td className="py-3 ps-3 pe-5">
                                            {p.live ? <Badge tone="success" icon={CircleCheck} label="فعال" /> : <Badge tone="neutral" icon={CircleSlash} label="غیرفعال" />}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
        </div>
    );
}
