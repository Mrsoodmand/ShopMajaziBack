import { Link, useNavigate, useSearchParams } from 'react-router';
import { EyeOff, Pencil, Plus, Star } from 'lucide-react';
import { useQuery } from '../../lib/api';
import { toman } from '../../lib/format';
import { ErrorState, SkeletonRows } from '../../components/ui';
import { platformInitial, type Platform, type ServiceRow } from './types';

export function PlatformTile({ platform, size = 36 }: { platform: Pick<Platform, 'name' | 'enName' | 'image' | 'color'>; size?: number }) {
    return (
        <span
            aria-hidden
            className="grid shrink-0 place-items-center overflow-hidden rounded-[10px] font-bold text-white"
            style={{ width: size, height: size, background: platform.image ? 'var(--surface-2)' : platform.color || '#475569' }}
        >
            {platform.image ? <img src={platform.image} alt="" className="size-full object-contain p-1" /> : platformInitial(platform)}
        </span>
    );
}

export default function ServicesPage() {
    const [params, setParams] = useSearchParams();
    const navigate = useNavigate();
    const cats = useQuery<{ categories: Platform[] }>('/admin/api/service-categories');
    const platforms = cats.data?.categories ?? [];
    const selectedId = Number(params.get('platform')) || platforms[0]?.id;
    const selected = platforms.find((p) => p.id === selectedId);
    const services = useQuery<{ services: ServiceRow[]; rate: number | null }>(selected ? `/admin/api/services?category=${selected.id}` : null);

    return (
        <div className="mx-auto max-w-[1280px]">
            <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 className="text-[22px] leading-tight font-bold">خدمات مجازی</h1>
                    <p className="mt-1 text-muted">پلتفرم‌ها و سرویس‌هایی که در صفحه خدمات شبکه‌های اجتماعی سایت فروخته می‌شوند.</p>
                </div>
                <Link to="/services/platforms/new" className="btn btn-secondary">
                    <Plus aria-hidden size={16} />
                    پلتفرم جدید
                </Link>
            </div>

            {cats.error ? (
                <ErrorState error={cats.error} onRetry={cats.reload} boxed />
            ) : (
                <div className="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                    <nav aria-label="پلتفرم‌ها" className="rounded-2xl border border-stroke bg-surface p-2">
                        {!cats.data ? (
                            <div className="space-y-2 p-2">
                                {[0, 1, 2].map((i) => (
                                    <div key={i} className="skeleton h-12" />
                                ))}
                            </div>
                        ) : platforms.length === 0 ? (
                            <p className="p-3 text-muted">هنوز پلتفرمی ساخته نشده است.</p>
                        ) : (
                            <ul className="space-y-0.5">
                                {platforms.map((p) => {
                                    const active = p.id === selectedId;
                                    return (
                                        <li key={p.id}>
                                            <button
                                                type="button"
                                                aria-current={active ? 'true' : undefined}
                                                onClick={() => setParams({ platform: String(p.id) })}
                                                className={`flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start transition-colors ${active ? 'bg-surface-2' : 'hover:bg-surface-2'}`}
                                            >
                                                <PlatformTile platform={p} />
                                                <span className="min-w-0 flex-1">
                                                    <span className={`flex items-center gap-1.5 ${active ? 'font-bold' : 'font-medium'}`}>
                                                        <span className="truncate">{p.name}</span>
                                                        {p.featured && <Star aria-label="ویژه" size={13} className="shrink-0 fill-current text-warn" />}
                                                        {!p.visible && <EyeOff aria-label="پنهان در سایت" size={13} className="shrink-0 text-muted" />}
                                                    </span>
                                                    <span className="num block text-[13px] text-muted">
                                                        {p.services} سرویس · {p.liveServices} فعال
                                                    </span>
                                                </span>
                                            </button>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </nav>

                    <section aria-labelledby="platform-services" className="min-w-0 overflow-hidden rounded-2xl border border-stroke bg-surface">
                        {selected && (
                            <div className="flex flex-wrap items-center justify-between gap-3 border-b border-stroke px-5 py-4">
                                <div className="flex min-w-0 items-center gap-3">
                                    <PlatformTile platform={selected} size={40} />
                                    <div className="min-w-0">
                                        <h2 id="platform-services" className="text-[16px] font-bold">
                                            سرویس‌های {selected.name}
                                        </h2>
                                        <p dir="ltr" className="latin text-end text-[13px] text-muted">
                                            /virtual-service/{selected.slug}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex gap-2">
                                    <Link to={`/services/platforms/${selected.id}`} className="btn btn-ghost">
                                        <Pencil aria-hidden size={15} />
                                        تنظیمات پلتفرم
                                    </Link>
                                    <Link to={`/services/new?platform=${selected.id}`} className="btn btn-primary">
                                        <Plus aria-hidden size={16} />
                                        سرویس جدید
                                    </Link>
                                </div>
                            </div>
                        )}
                        {services.error ? (
                            <ErrorState error={services.error} onRetry={services.reload} />
                        ) : !selected && cats.data ? (
                            <p className="px-5 py-12 text-center text-muted">یک پلتفرم بسازید تا سرویس‌هایش را اضافه کنید.</p>
                        ) : !services.data ? (
                            <SkeletonRows rows={4} />
                        ) : services.data.services.length === 0 ? (
                            <div className="px-5 py-12 text-center">
                                <p className="font-medium">این پلتفرم هنوز سرویسی ندارد.</p>
                                <p className="mt-1 text-muted">تا سرویس فعالی نداشته باشد، در سایت نمایش داده نمی‌شود.</p>
                            </div>
                        ) : (
                            <table className={`w-full ${services.loading ? 'opacity-60' : ''}`}>
                                <thead>
                                    <tr className="border-b border-stroke text-[13px] text-muted">
                                        <th scope="col" className="py-3 ps-5 pe-3 text-start font-medium">سرویس</th>
                                        <th scope="col" className="hidden px-3 text-start font-medium sm:table-cell">قیمت‌گذاری</th>
                                        <th scope="col" className="px-3 text-end font-medium">هر ۱۰۰۰ عدد</th>
                                        <th scope="col" className="py-3 ps-3 pe-5 text-start font-medium">وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {services.data.services.map((s) => (
                                        <tr
                                            key={s.id}
                                            onClick={(e) => {
                                                if (!(e.target as HTMLElement).closest('a')) navigate(`/services/${s.id}`);
                                            }}
                                            className="cursor-pointer border-b border-stroke last:border-0 hover:bg-surface-2"
                                        >
                                            <td className="py-3 ps-5 pe-3">
                                                <Link to={`/services/${s.id}`} className="font-bold underline-offset-4 hover:underline">
                                                    {s.name || s.slug}
                                                </Link>
                                                <div dir="ltr" className="latin text-end text-[12px] text-muted">
                                                    {s.slug}
                                                </div>
                                            </td>
                                            <td className="hidden px-3 sm:table-cell">{s.pricing === 'count' ? 'تعداد دلخواه' : `${s.plans} پلن`}</td>
                                            <td className="num px-3 text-end whitespace-nowrap">{s.tomanPer1000 != null ? (
                                                    toman(s.tomanPer1000)
                                                ) : (
                                                    <span className="text-warn">{services.data?.rate ? 'قیمت ثبت نشده' : 'نرخ دلار تنظیم نشده'}</span>
                                                )}</td>
                                            <td className="py-3 ps-3 pe-5">
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-[13px] font-medium ${
                                                        s.live ? 'bg-emerald-500/12 text-emerald-800 dark:text-emerald-300' : 'bg-surface-2 text-muted'
                                                    }`}
                                                >
                                                    {s.live ? 'فعال' : 'غیرفعال'}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </section>
                </div>
            )}
        </div>
    );
}
