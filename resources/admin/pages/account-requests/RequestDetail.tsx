import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { ArrowRight, ExternalLink, FileText, Send, X } from 'lucide-react';
import { postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { toman } from '../../lib/format';
import { ErrorState, Notice, Panel, Row } from '../../components/ui';
import { Field } from '../../components/form';
import { StatusBadge, type ShownStatus } from './AccountRequestsPage';

/* One «درخواست اکانت» (Admin\AccountRequestApiController@show): the customer's answers, then a price (a custom order
 * of type 9 the customer pays in /account) or a rejection with a note. After the quote it links to that order. */

type Answer = { key: string; title: string; type: string; value: string; url?: string | null; mime?: string | null };
type Detail = {
    id: number;
    status: ShownStatus;
    site_name: string;
    site_url: string | null;
    note: string | null;
    answers: Answer[];
    order: { id: number; price: number; title: string } | null;
    createdAt: string | null;
    reviewedAt: string | null;
    reviewer: string | null;
    user: { id: number; name: string | null; mobile: string; email: string | null } | null;
    others: { id: number; status: ShownStatus; site: string; createdAt: string | null }[];
};

// "qq.com" → "https://qq.com", for the link; anything that doesn't look like an address stays text.
const siteHref = (url: string) => (/^[\w-]+(\.[\w-]+)+(\/\S*)?$/.test(url) ? `https://${url}` : /^https?:\/\/\S+$/.test(url) ? url : null);

export default function RequestDetail() {
    const { id } = useParams();
    const { csrfToken } = useBoot();
    const { data, error, reload } = useQuery<{ request: Detail }>(`/admin/api/account-requests/${id}`);
    const r = data?.request;
    const [title, setTitle] = useState('');
    const [currency, setCurrency] = useState<'toman' | 'usd'>('toman');
    const [amount, setAmount] = useState('');
    const [lineNote, setLineNote] = useState('');
    const [message, setMessage] = useState('');
    const [reason, setReason] = useState('');
    const [busy, setBusy] = useState(false);
    const [done, setDone] = useState<string | null>(null);
    const [failed, setFailed] = useState<string | null>(null);

    useEffect(() => {
        if (r && !title) {
            const plan = r.answers.find((a) => a.key === 'plan')?.value;
            setTitle(`اکانت ${r.site_name}${plan ? ` — ${plan}` : ''}`);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [r]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!r) return <div className="skeleton mx-auto h-96 max-w-[960px] rounded-xl" />;

    async function act(url: string, body: unknown) {
        setBusy(true);
        setDone(null);
        setFailed(null);
        try {
            const res = await postJson<{ success?: string }>(url, csrfToken, body);
            setDone(res.success ?? 'انجام شد.');
            reload();
        } catch (e) {
            setFailed((e as Error).message);
        } finally {
            setBusy(false);
        }
    }

    const href = r.site_url ? siteHref(r.site_url) : null;

    return (
        <div className="mx-auto grid max-w-[960px] gap-5">
            <div>
                <Link to="/account-requests" className="mb-1 inline-flex items-center gap-1 text-[13px] text-muted hover:text-ink">
                    <ArrowRight aria-hidden size={14} />
                    درخواست اکانت
                </Link>
                <h1 className="flex flex-wrap items-center gap-3 text-xl font-bold">
                    {r.site_name}
                    <StatusBadge status={r.status} />
                </h1>
                <p className="mt-1 text-muted">
                    درخواست <span className="num">#{r.id}</span> · <span dir="ltr" className="num">{r.createdAt}</span>
                    {r.site_url && (
                        <>
                            {' · '}
                            {href ? (
                                <a href={href} target="_blank" rel="noreferrer noopener" dir="ltr" className="inline-flex items-center gap-1 text-indigo hover:underline">
                                    {r.site_url}
                                    <ExternalLink aria-hidden size={13} />
                                </a>
                            ) : (
                                <span dir="ltr">{r.site_url}</span>
                            )}
                        </>
                    )}
                </p>
            </div>

            {done && <Notice>{done}</Notice>}
            {failed && <p role="alert" className="rounded-xl bg-error-soft px-3 py-2.5 font-medium text-error">{failed}</p>}

            <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
                <div className="grid content-start gap-5">
                    <Panel title="پاسخ‌های مشتری">
                        <dl>
                            {r.answers.map((a) => (
                                <Row key={a.key} label={a.title} ltr={['email', 'phone', 'number'].includes(a.type) || a.key === 'site_url'}>
                                    {a.type === 'file' ? (
                                        a.url ? (
                                            <a href={a.url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-1.5 text-indigo hover:underline">
                                                <FileText aria-hidden size={15} />
                                                {a.value}
                                                <ExternalLink aria-hidden size={13} />
                                            </a>
                                        ) : (
                                            <span className="text-muted">{a.value} (فایل در دسترس نیست)</span>
                                        )
                                    ) : a.type === 'yesno' ? (
                                        a.value === 'yes' ? 'بله' : 'خیر'
                                    ) : (
                                        <span className="whitespace-pre-line">{a.value}</span>
                                    )}
                                </Row>
                            ))}
                        </dl>
                    </Panel>

                    {r.order && (
                        <Panel title="سفارش این درخواست">
                            <dl>
                                <Row label="سفارش">
                                    <Link to={`/orders/${r.order.id}`} className="num text-indigo hover:underline">#{r.order.id}</Link>
                                </Row>
                                <Row label="شرح">{r.order.title}</Row>
                                <Row label="مبلغ">{toman(r.order.price)}</Row>
                            </dl>
                            <p className="mt-2 text-[13px] text-muted">پرداخت، تحویل و پیام به مشتری از صفحه همین سفارش انجام می‌شود.</p>
                        </Panel>
                    )}

                    {(r.reviewedAt || r.note) && (
                        <Panel title="جواب پشتیبانی">
                            <dl>
                                {r.reviewer && <Row label="پاسخ‌دهنده">{r.reviewer}</Row>}
                                {r.reviewedAt && <Row label="تاریخ" ltr>{r.reviewedAt}</Row>}
                                {r.note && <Row label="یادداشت برای مشتری"><span className="whitespace-pre-line">{r.note}</span></Row>}
                            </dl>
                        </Panel>
                    )}
                </div>

                <div className="grid content-start gap-5">
                    <Panel title="کاربر">
                        {r.user ? (
                            <>
                                <dl>
                                    <Row label="نام">{r.user.name || '—'}</Row>
                                    <Row label="موبایل" ltr>{r.user.mobile}</Row>
                                    {r.user.email && <Row label="ایمیل" ltr>{r.user.email}</Row>}
                                </dl>
                                <Link to={`/users/${r.user.id}`} className="btn btn-secondary mt-3 w-full">صفحه کاربر</Link>
                            </>
                        ) : (
                            <p className="text-muted">کاربر پیدا نشد.</p>
                        )}
                    </Panel>

                    {r.status === 'pending' && (
                        <Panel title="جواب به درخواست">
                            <div className="grid gap-3">
                                <Field label="عنوان سفارش" hint="مشتری همین را در سفارش و فاکتور می‌بیند.">
                                    {(p) => <input {...p} className="field" value={title} onChange={(e) => setTitle(e.target.value)} />}
                                </Field>
                                <div className="grid grid-cols-[minmax(0,1fr)_110px] gap-2">
                                    <Field label="مبلغ">
                                        {(p) => <input {...p} inputMode="decimal" dir="ltr" className="field num text-end" value={amount} onChange={(e) => setAmount(e.target.value)} placeholder={currency === 'usd' ? '12' : '1,500,000'} />}
                                    </Field>
                                    <Field label="واحد">
                                        {(p) => (
                                            <select {...p} className="field" value={currency} onChange={(e) => setCurrency(e.target.value as 'toman' | 'usd')}>
                                                <option value="toman">تومان</option>
                                                <option value="usd">دلار</option>
                                            </select>
                                        )}
                                    </Field>
                                </div>
                                {currency === 'usd' && <p className="-mt-1 text-[13px] text-muted">با نرخ امروز خدمات به تومان تبدیل و گرد می‌شود و همان ثابت می‌ماند.</p>}
                                <Field label="جزئیات سفارش" optional hint="مثلا مدت اشتراک؛ کنار عنوان در سفارش می‌آید.">
                                    {(p) => <input {...p} className="field" value={lineNote} onChange={(e) => setLineNote(e.target.value)} />}
                                </Field>
                                <Field label="پیام برای مشتری" optional>
                                    {(p) => <textarea {...p} rows={2} className="field" value={message} onChange={(e) => setMessage(e.target.value)} placeholder="تحویل حدود ۲ ساعت بعد از پرداخت." />}
                                </Field>
                                <button
                                    type="button"
                                    className="btn btn-primary w-full"
                                    disabled={busy || !title.trim() || !amount.trim()}
                                    onClick={() => act(`/admin/api/account-requests/${r.id}/quote`, { title, currency, amount, note: lineNote, message })}
                                >
                                    <Send aria-hidden size={16} className="-scale-x-100" />
                                    ارسال قیمت و ساخت سفارش
                                </button>
                                <p className="text-[13px] text-muted">سفارش «در انتظار پرداخت» ساخته می‌شود و مشتری از پنل کاربری‌اش پرداخت می‌کند.</p>
                            </div>
                            <hr className="my-4 border-stroke" />
                            <Field label="دلیل رد" hint="مشتری این متن را در پنل خود می‌بیند.">
                                {(p) => <textarea {...p} rows={3} className="field" value={reason} onChange={(e) => setReason(e.target.value)} placeholder="فعلا راهی برای خرید این اشتراک از ایران نداریم." />}
                            </Field>
                            <button type="button" className="btn btn-secondary mt-3 w-full text-error" disabled={busy || !reason.trim()} onClick={() => act(`/admin/api/account-requests/${r.id}/reject`, { note: reason })}>
                                <X aria-hidden size={16} />
                                رد درخواست
                            </button>
                        </Panel>
                    )}

                    {r.others.length > 0 && (
                        <Panel title="درخواست‌های دیگر این کاربر">
                            <ul className="grid gap-2">
                                {r.others.map((o) => (
                                    <li key={o.id}>
                                        <Link to={`/account-requests/${o.id}`} className="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 hover:bg-surface-2">
                                            <span className="min-w-0 truncate"><span className="num text-muted">#{o.id}</span> {o.site}</span>
                                            <StatusBadge status={o.status} />
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </Panel>
                    )}
                </div>
            </div>
        </div>
    );
}
