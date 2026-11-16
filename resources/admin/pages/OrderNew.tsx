import { useEffect, useId, useMemo, useState, type FormEvent } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router';
import { ArrowRight, Plus, Trash2, X } from 'lucide-react';
import { postJson, useQuery } from '../lib/api';
import { useBoot } from '../lib/boot';
import { toman } from '../lib/format';
import { FieldError, Panel } from '../components/ui';

/*
 * Admin › orders › ثبت سفارش جدید (Admin\OrderMakeApiController): an order for one customer made of catalog plans
 * and/or free-text services priced in Toman or dollars. "Customer pays" leaves it «در انتظار پرداخت» on the
 * customer's /account; "Already paid" records an outside payment and starts it at «درحال بررسی».
 */

type Catalog = {
    usdRate: number;
    products: { id: number; name: string; kind: 'service' | 'subscription'; plans: { id: number; name: string; price: number }[]; count: { step: number; price: number; min: number; max: number } | null }[];
};
type Customer = { id: number; name: string | null; mobile: string | null; wallet: number };
type Line =
    | { key: number; mode: 'product'; productId: string; planId: string; count: string; note: string }
    | { key: number; mode: 'custom'; title: string; note: string; currency: 'toman' | 'usd'; amount: string };

let seq = 1;
const newLine = (mode: Line['mode']): Line =>
    mode === 'product' ? { key: seq++, mode, productId: '', planId: '', count: '', note: '' } : { key: seq++, mode, title: '', note: '', currency: 'toman', amount: '' };
const num = (v: string) => Number(v.replace(/[,٬\s]/g, '').replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)))) || 0;

export default function OrderNew() {
    const { csrfToken } = useBoot();
    const navigate = useNavigate();
    const [params] = useSearchParams();
    const catalog = useQuery<Catalog>("/admin/api/orders/catalog").data ?? undefined;
    const [customer, setCustomer] = useState<Customer | null>(null);
    const [lines, setLines] = useState<Line[]>([newLine('custom')]);
    const [paid, setPaid] = useState(false);
    const [notify, setNotify] = useState(true);
    const [note, setNote] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const id = useId();

    // Opened from a customer's page: /orders/new?user=ID
    const preset = params.get('user');
    const { data: presetUser } = useQuery<{ users: Customer[] }>(preset ? `/admin/api/users?q=${encodeURIComponent(preset)}&per=20` : null);
    useEffect(() => {
        const u = presetUser?.users.find((x) => String(x.id) === preset);
        if (u && !customer) setCustomer(u);
    }, [presetUser, preset, customer]);

    const priced = useMemo(() => lines.map((l) => linePrice(l, catalog)), [lines, catalog]);
    const total = priced.reduce<number>((s, p) => s + (p ?? 0), 0);

    function update(key: number, patch: Partial<Line>) {
        setLines((ls) => ls.map((l) => (l.key === key ? ({ ...l, ...patch } as Line) : l)));
    }

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (!customer) return setError('مشتری را انتخاب کنید.');
        if (priced.some((p) => !p)) return setError('همه ردیف‌ها باید مبلغ داشته باشند.');
        setError(null);
        setBusy(true);
        try {
            const res = await postJson<{ success?: string; id: number }>('/admin/api/orders/make', csrfToken, {
                user_id: customer.id,
                paid,
                notify,
                note,
                lines: lines.map((l) =>
                    l.mode === 'product'
                        ? { product_id: Number(l.productId), plan_id: l.planId ? Number(l.planId) : undefined, count: l.count ? num(l.count) : undefined, note: l.note }
                        : { title: l.title, note: l.note, currency: l.currency, amount: l.amount },
                ),
            });
            navigate(`/orders/${res.id}`);
        } catch (err) {
            setError((err as Error).message);
            setBusy(false);
        }
    }

    return (
        <div className="mx-auto max-w-[960px]">
            <Link to="/orders" className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                سفارش‌ها
            </Link>
            <h1 className="mb-1 text-[22px] font-bold">ثبت سفارش جدید</h1>
            <p className="mb-6 text-muted">سفارشی برای یک مشتری بسازید؛ از محصولات سایت یا با شرح دلخواه و مبلغ تومانی یا دلاری.</p>

            <form onSubmit={submit} noValidate className="space-y-6">
                <Panel title="۱. مشتری">
                    {customer ? (
                        <div className="flex items-center justify-between gap-3 rounded-xl bg-surface-2 px-4 py-3">
                            <div>
                                <div className="font-bold">{customer.name || 'بدون نام'}</div>
                                <div dir="ltr" className="num text-end text-[13px] text-muted">{customer.mobile}</div>
                            </div>
                            <div className="flex items-center gap-3">
                                <span className="text-[13px] text-body">کیف پول: <span className="num">{toman(customer.wallet)}</span></span>
                                <button type="button" className="btn btn-ghost px-2" onClick={() => setCustomer(null)} aria-label="تغییر مشتری">
                                    <X aria-hidden size={16} />
                                </button>
                            </div>
                        </div>
                    ) : (
                        <CustomerSearch onPick={setCustomer} />
                    )}
                </Panel>

                <Panel title="۲. اقلام سفارش">
                    <div className="space-y-4">
                        {lines.map((l, i) => (
                            <LineEditor key={l.key} n={i + 1} line={l} catalog={catalog} price={priced[i]} onChange={(p) => update(l.key, p)} onRemove={lines.length > 1 ? () => setLines((ls) => ls.filter((x) => x.key !== l.key)) : undefined} />
                        ))}
                    </div>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <button type="button" className="btn btn-secondary" onClick={() => setLines((ls) => [...ls, newLine('custom')])}>
                            <Plus aria-hidden size={15} /> خدمت با شرح دلخواه
                        </button>
                        <button type="button" className="btn btn-secondary" onClick={() => setLines((ls) => [...ls, newLine('product')])}>
                            <Plus aria-hidden size={15} /> از محصولات سایت
                        </button>
                    </div>
                    {catalog && <p className="mt-3 text-[13px] text-muted">نرخ دلار امروز (خدمات): <span className="num">{toman(catalog.usdRate)}</span> — مبلغ دلاری با این نرخ تبدیل و ثابت می‌شود.</p>}
                </Panel>

                <Panel title="۳. پرداخت">
                    <fieldset className="grid gap-3 sm:grid-cols-2">
                        <legend className="sr-only">نحوه پرداخت</legend>
                        {[
                            { v: false, t: 'مشتری پرداخت می‌کند', d: 'سفارش «در انتظار پرداخت» می‌ماند؛ مشتری در حساب کاربری با کیف پول یا کارت پرداخت می‌کند.' },
                            { v: true, t: 'قبلاً پرداخت شده', d: 'پرداخت خارج از سایت ثبت می‌شود و سفارش «درحال بررسی» شروع می‌شود.' },
                        ].map((o) => (
                            <label key={String(o.v)} className={`cursor-pointer rounded-xl border p-4 ${paid === o.v ? 'border-primary ring-2 ring-ring' : 'border-stroke'}`}>
                                <input type="radio" name={`${id}-paid`} className="sr-only" checked={paid === o.v} onChange={() => setPaid(o.v)} />
                                <div className="font-bold">{o.t}</div>
                                <div className="mt-1 text-[13px] text-muted">{o.d}</div>
                            </label>
                        ))}
                    </fieldset>
                    <label htmlFor={`${id}-note`} className="mt-4 mb-1 block font-medium">یادداشت داخلی (در تاریخچه سفارش)</label>
                    <input id={`${id}-note`} value={note} onChange={(e) => setNote(e.target.value)} className="field" placeholder="مثلاً: درخواست تلفنی مشتری" />
                    <label className="mt-3 flex items-center gap-2 text-[14px]">
                        <input type="checkbox" checked={notify} onChange={(e) => setNotify(e.target.checked)} />
                        اطلاع به مشتری با پیامک
                    </label>
                </Panel>

                <div className="sticky bottom-0 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-stroke bg-surface px-5 py-4">
                    <div>
                        <div className="text-[13px] text-muted">جمع سفارش</div>
                        <div className="num text-[20px] font-bold">{toman(total)}</div>
                    </div>
                    <div className="flex flex-col items-end">
                        <button type="submit" className="btn btn-primary" disabled={busy}>
                            {busy ? 'در حال ثبت…' : paid ? 'ثبت سفارش پرداخت‌شده' : 'ثبت و ارسال برای پرداخت'}
                        </button>
                        {error && <FieldError id={`${id}-error`}>{error}</FieldError>}
                    </div>
                </div>
            </form>
        </div>
    );
}

/** Toman for a line as the server will price it (custom dollars are rounded up to a thousand), or null. */
function linePrice(l: Line, catalog: Catalog | undefined): number | null {
    if (l.mode === 'custom') {
        const a = num(l.amount);
        if (!a || !l.title.trim()) return null;
        if (l.currency === 'toman') return Math.round(a);
        return catalog ? Math.ceil((a * catalog.usdRate) / 1000) * 1000 : null;
    }
    const p = catalog?.products.find((x) => String(x.id) === l.productId);
    if (!p) return null;
    if (l.planId) return p.plans.find((x) => String(x.id) === l.planId)?.price ?? null;
    if (p.count && l.count) {
        const c = num(l.count);
        return c >= p.count.min && c <= p.count.max && c % p.count.step === 0 ? (c / p.count.step) * p.count.price : null;
    }
    return null;
}

function LineEditor({ n, line, catalog, price, onChange, onRemove }: { n: number; line: Line; catalog?: Catalog; price: number | null; onChange: (p: Partial<Line>) => void; onRemove?: () => void }) {
    const id = useId();
    const product = line.mode === 'product' ? catalog?.products.find((x) => String(x.id) === line.productId) : undefined;
    return (
        <div className="rounded-xl border border-stroke p-4">
            <div className="mb-3 flex items-center justify-between gap-3">
                <h3 className="font-bold">
                    ردیف <span className="num">{n}</span> · <span className="font-normal text-muted">{line.mode === 'product' ? 'از محصولات سایت' : 'شرح دلخواه'}</span>
                </h3>
                <div className="flex items-center gap-3">
                    <span className="num font-bold">{price ? toman(price) : '—'}</span>
                    {onRemove && (
                        <button type="button" className="btn btn-ghost px-2" onClick={onRemove} aria-label={`حذف ردیف ${n}`}>
                            <Trash2 aria-hidden size={15} />
                        </button>
                    )}
                </div>
            </div>
            {line.mode === 'custom' ? (
                <div className="grid gap-3 sm:grid-cols-[1fr_160px_110px]">
                    <div>
                        <label htmlFor={`${id}-t`} className="mb-1 block font-medium">شرح خدمت</label>
                        <input id={`${id}-t`} value={line.title} onChange={(e) => onChange({ title: e.target.value })} className="field" placeholder="مثلاً: ساخت اکانت پی‌پال" />
                    </div>
                    <div>
                        <label htmlFor={`${id}-a`} className="mb-1 block font-medium">مبلغ</label>
                        <input id={`${id}-a`} dir="ltr" inputMode="decimal" value={line.amount} onChange={(e) => onChange({ amount: e.target.value })} className="field num" placeholder={line.currency === 'usd' ? '25' : '1500000'} />
                    </div>
                    <div>
                        <label htmlFor={`${id}-c`} className="mb-1 block font-medium">واحد</label>
                        <select id={`${id}-c`} value={line.currency} onChange={(e) => onChange({ currency: e.target.value as 'toman' | 'usd' })} className="field">
                            <option value="toman">تومان</option>
                            <option value="usd">دلار</option>
                        </select>
                    </div>
                </div>
            ) : (
                <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label htmlFor={`${id}-p`} className="mb-1 block font-medium">محصول</label>
                        <select id={`${id}-p`} value={line.productId} onChange={(e) => onChange({ productId: e.target.value, planId: '', count: '' })} className="field">
                            <option value="">{catalog ? 'انتخاب کنید' : 'در حال بارگذاری…'}</option>
                            {catalog?.products.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name} {p.kind === 'subscription' ? '(اشتراک)' : ''}
                                </option>
                            ))}
                        </select>
                    </div>
                    {product && product.plans.length > 0 && (
                        <div>
                            <label htmlFor={`${id}-pl`} className="mb-1 block font-medium">پلن</label>
                            <select id={`${id}-pl`} value={line.planId} onChange={(e) => onChange({ planId: e.target.value, count: '' })} className="field">
                                <option value="">انتخاب کنید</option>
                                {product.plans.map((pl) => (
                                    <option key={pl.id} value={pl.id}>
                                        {pl.name} — {toman(pl.price)}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                    {product?.count && !line.planId && (
                        <div>
                            <label htmlFor={`${id}-n`} className="mb-1 block font-medium">
                                تعداد <span className="num text-[12px] font-normal text-muted">({product.count.min.toLocaleString('fa')} تا {product.count.max.toLocaleString('fa')}، مضرب {product.count.step.toLocaleString('fa')})</span>
                            </label>
                            <input id={`${id}-n`} dir="ltr" inputMode="numeric" value={line.count} onChange={(e) => onChange({ count: e.target.value })} className="field num" />
                        </div>
                    )}
                </div>
            )}
            <label htmlFor={`${id}-note`} className="mt-3 mb-1 block font-medium">توضیحات برای مشتری <span className="font-normal text-muted">(اختیاری)</span></label>
            <input id={`${id}-note`} value={line.note} onChange={(e) => onChange({ note: e.target.value })} className="field" placeholder="مثلاً: لینک پیج یا جزئیات تحویل" />
        </div>
    );
}

function CustomerSearch({ onPick }: { onPick: (c: Customer) => void }) {
    const [q, setQ] = useState('');
    const [term, setTerm] = useState('');
    useEffect(() => {
        const t = setTimeout(() => setTerm(q.trim()), 300);
        return () => clearTimeout(t);
    }, [q]);
    const { data, loading } = useQuery<{ users: Customer[] }>(term.length >= 3 ? `/admin/api/users?q=${encodeURIComponent(term)}&per=20` : null);
    const id = useId();
    return (
        <div>
            <label htmlFor={id} className="mb-1 block font-medium">جستجوی مشتری</label>
            <input id={id} value={q} onChange={(e) => setQ(e.target.value)} className="field" placeholder="موبایل، نام یا ایمیل (حداقل ۳ حرف)" autoComplete="off" />
            {term.length >= 3 && (
                <ul className="m-0 mt-2 max-h-72 list-none divide-y divide-stroke overflow-y-auto rounded-xl border border-stroke p-0">
                    {loading && !data && <li className="px-4 py-3 text-muted">در حال جستجو…</li>}
                    {data?.users.length === 0 && <li className="px-4 py-3 text-muted">کاربری پیدا نشد.</li>}
                    {data?.users.map((u) => (
                        <li key={u.id}>
                            <button type="button" onClick={() => onPick(u)} className="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-start hover:bg-surface-2">
                                <span className="font-medium">{u.name || 'بدون نام'}</span>
                                <span dir="ltr" className="num text-[13px] text-muted">{u.mobile}</span>
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
