import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { ArrowRight, Check, ExternalLink, FileText, Undo2, X } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { ErrorState, Notice, Panel, Row } from '../../components/ui';
import { Field } from '../../components/form';
import { StatusBadge, type ReqStatus } from './OrganizationsPage';
import { DiscountTable, VatInput, vatNumber, fromDraft, toDraft, type DiscountDraft, type Discounts, type Family } from './DiscountTable';

/* One organisation-account request (Admin\OrganizationApiController@request): the answers, then approve with a
 * discount or reject with a note. Approved: change the discount or turn the account back into a personal one. */

type Answer = { key: string; title: string; type: string; value: string; url?: string | null; mime?: string | null };
type Detail = {
    id: number;
    status: ReqStatus;
    company_name: string | null;
    official_invoice: boolean | null;
    note: string | null;
    answers: Answer[];
    createdAt: string | null;
    reviewedAt: string | null;
    reviewer: string | null;
    defaults: Discounts;
    defaultVat: number;
    families: Family[];
    maxDiscount: number;
    user: { id: number; name: string | null; mobile: string; email: string | null; organization: boolean; discounts: Discounts; vat: number; note: string | null } | null;
    others: { id: number; status: ReqStatus; createdAt: string | null }[];
};

export default function RequestDetail() {
    const { id } = useParams();
    const { csrfToken } = useBoot();
    const { data, error, reload } = useQuery<{ request: Detail }>(`/admin/api/organizations/requests/${id}`);
    const r = data?.request;
    const [discounts, setDiscounts] = useState<DiscountDraft>({});
    const [vat, setVat] = useState('');
    const [orgNote, setOrgNote] = useState('');
    const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
    const [note, setNote] = useState('');
    const [busy, setBusy] = useState(false);
    const [done, setDone] = useState<string | null>(null);
    const [failed, setFailed] = useState<string | null>(null);

    useEffect(() => {
        if (!r) return;
        const live = r.status === 'approved' && r.user?.organization;
        setOrgNote(r.user?.note ?? '');
        setDiscounts(toDraft(live ? r.user!.discounts : r.defaults));
        setVat(live ? (r.user!.vat > 0 ? String(r.user!.vat) : '') : String(r.defaultVat));
    }, [r]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!r) return <div className="skeleton mx-auto h-96 max-w-[960px] rounded-xl" />;

    async function act(url: string, body: unknown) {
        setBusy(true);
        setDone(null);
        setFailed(null);
        setFieldErrors({});
        try {
            const res = await postJson<{ success?: string }>(url, csrfToken, body);
            setDone(res.success ?? 'انجام شد.');
            reload();
        } catch (e) {
            const fields = e instanceof ApiError ? e.fields : {};
            setFieldErrors(Object.fromEntries(Object.entries(fields).map(([k, v]) => [k.replace(/^discounts\./, ''), v])));
            setFailed((e as Error).message);
        } finally {
            setBusy(false);
        }
    }

    const live = r.status === 'approved' && r.user?.organization;

    return (
        <div className="mx-auto grid max-w-[960px] gap-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <Link to="/organizations" className="mb-1 inline-flex items-center gap-1 text-[13px] text-muted hover:text-ink">
                        <ArrowRight aria-hidden size={14} />
                        حساب‌های سازمانی
                    </Link>
                    <h1 className="flex flex-wrap items-center gap-3 text-xl font-bold">
                        {r.company_name || 'بدون نام'}
                        <StatusBadge status={r.status} />
                    </h1>
                    <p className="mt-1 text-muted">
                        درخواست <span className="num">#{r.id}</span> · <span dir="ltr" className="num">{r.createdAt}</span>
                    </p>
                </div>
            </div>

            {done && <Notice>{done}</Notice>}
            {failed && <p role="alert" className="rounded-xl bg-error-soft px-3 py-2.5 font-medium text-error">{failed}</p>}

            <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                <Panel title="پاسخ‌ها">
                    {r.official_invoice !== null && (
                        <p className={`mb-3 rounded-lg px-3 py-2 text-[14px] font-medium ${r.official_invoice ? 'bg-primary-soft text-primary' : 'bg-surface-2 text-muted'}`}>
                            {r.official_invoice ? 'این شرکت فاکتور رسمی می‌خواهد؛ ارزش افزوده به هر خریدش اضافه می‌شود.' : 'فاکتور رسمی نمی‌خواهد؛ ارزش افزوده اضافه نمی‌شود.'}
                        </p>
                    )}
                    <dl>
                        {r.answers.map((a) => (
                            <Row key={a.key} label={a.title} ltr={['email', 'phone', 'number'].includes(a.type) || a.key === 'tax_code'}>
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
                        {r.answers.length === 0 && <p className="text-muted">پاسخی ثبت نشده است.</p>}
                    </dl>
                </Panel>

                <div className="grid content-start gap-5">
                    <Panel title="کاربر">
                        {r.user ? (
                            <dl>
                                <Row label="نام">{r.user.name || '—'}</Row>
                                <Row label="موبایل" ltr>{r.user.mobile}</Row>
                                {r.user.email && <Row label="ایمیل" ltr>{r.user.email}</Row>}
                                <Row label="حساب">{r.user.organization ? 'سازمانی' : 'شخصی'}</Row>
                            </dl>
                        ) : (
                            <p className="text-muted">کاربر پیدا نشد.</p>
                        )}
                        {r.user && (
                            <Link to={`/users/${r.user.id}`} className="btn btn-secondary mt-3 w-full">
                                صفحه کاربر
                            </Link>
                        )}
                    </Panel>

                    {r.user && (
                        <Panel title="یادداشت داخلی">
                            <Field label="یادداشت درباره این شرکت" hint="فقط مدیران می‌بینند؛ به مشتری نشان داده نمی‌شود.">
                                {(p) => <textarea {...p} rows={4} className="field" value={orgNote} onChange={(e) => setOrgNote(e.target.value)} maxLength={5000} />}
                            </Field>
                            <button type="button" className="btn btn-secondary mt-3 w-full" disabled={busy || orgNote === (r.user.note ?? '')} onClick={() => act(`/admin/api/organizations/${r.user!.id}/note`, { note: orgNote })}>
                                ذخیره یادداشت
                            </button>
                        </Panel>
                    )}

                    {r.status === 'pending' && (
                        <Panel title="بررسی">
                            <DiscountTable families={r.families} value={discounts} onChange={setDiscounts} errors={fieldErrors} max={r.maxDiscount} />
                            {r.official_invoice && (
                                <div className="mt-3">
                                    <VatInput label="ارزش افزوده (٪)" hint="به هر خرید این شرکت، بعد از تخفیف، اضافه می‌شود." value={vat} onChange={setVat} error={fieldErrors.vat} />
                                </div>
                            )}
                            <button type="button" className="btn btn-primary mt-3 w-full" disabled={busy} onClick={() => act(`/admin/api/organizations/requests/${r.id}/approve`, { discounts: fromDraft(discounts), ...(r.official_invoice ? { vat: vatNumber(vat) ?? 0 } : {}) })}>
                                <Check aria-hidden size={16} />
                                تایید و فعال‌سازی
                            </button>
                            <hr className="my-4 border-stroke" />
                            <Field label="دلیل رد" hint="مشتری این متن را در پنل خود می‌بیند و می‌تواند دوباره درخواست بدهد.">
                                {(p) => <textarea {...p} rows={3} className="field" value={note} onChange={(e) => setNote(e.target.value)} placeholder="کد اقتصادی با نام شرکت یکی نیست." />}
                            </Field>
                            <button type="button" className="btn btn-secondary mt-3 w-full text-error" disabled={busy || !note.trim()} onClick={() => act(`/admin/api/organizations/requests/${r.id}/reject`, { note })}>
                                <X aria-hidden size={16} />
                                رد درخواست
                            </button>
                        </Panel>
                    )}

                    {live && r.user && (
                        <Panel title="تخفیف‌های این شرکت">
                            <DiscountTable families={r.families} value={discounts} onChange={setDiscounts} errors={fieldErrors} max={r.maxDiscount} />
                            <div className="mt-3">
                                <VatInput label="ارزش افزوده فاکتور رسمی (٪)" hint="خالی یا صفر = بدون فاکتور رسمی و بدون ارزش افزوده." value={vat} onChange={setVat} error={fieldErrors.vat} />
                            </div>
                            <p className="mt-1 text-[13px] text-muted">از خرید بعدی اعمال می‌شود؛ سفارش‌های قبلی عوض نمی‌شوند.</p>
                            <button type="button" className="btn btn-primary mt-3 w-full" disabled={busy} onClick={() => act(`/admin/api/organizations/${r.user!.id}/discount`, { discounts: fromDraft(discounts), vat: vatNumber(vat) ?? 0 })}>
                                ذخیره تخفیف و ارزش افزوده
                            </button>
                            <hr className="my-4 border-stroke" />
                            <Field label="یادداشت برای مشتری" optional>
                                {(p) => <textarea {...p} rows={2} className="field" value={note} onChange={(e) => setNote(e.target.value)} />}
                            </Field>
                            <button
                                type="button"
                                className="btn btn-secondary mt-3 w-full text-error"
                                disabled={busy}
                                onClick={() => window.confirm('حساب این کاربر به شخصی برگردد؟ تخفیفش از خرید بعدی حذف می‌شود.') && act(`/admin/api/organizations/${r.user!.id}/cancel`, { note })}
                            >
                                <Undo2 aria-hidden size={16} />
                                برگرداندن به حساب شخصی
                            </button>
                        </Panel>
                    )}

                    {(r.reviewedAt || r.note) && (
                        <Panel title="نتیجه بررسی">
                            <dl>
                                {r.reviewer && <Row label="بررسی‌کننده">{r.reviewer}</Row>}
                                {r.reviewedAt && <Row label="تاریخ" ltr>{r.reviewedAt}</Row>}
                                {r.note && <Row label="یادداشت">{r.note}</Row>}
                            </dl>
                        </Panel>
                    )}

                    {r.others.length > 0 && (
                        <Panel title="درخواست‌های دیگر این کاربر">
                            <ul className="grid gap-2">
                                {r.others.map((o) => (
                                    <li key={o.id}>
                                        <Link to={`/organizations/requests/${o.id}`} className="flex items-center justify-between gap-2 rounded-lg px-2 py-1.5 hover:bg-surface-2">
                                            <span className="num text-muted">#{o.id} · <span dir="ltr">{o.createdAt}</span></span>
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
