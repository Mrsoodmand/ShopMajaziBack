import { useRef, useState } from 'react';
import { Link, useLocation, useParams } from 'react-router';
import { ArrowRight, FileText, Lock, LockOpen, Paperclip, Send, X } from 'lucide-react';
import { ApiError, postFormData, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { ErrorState, FieldError, Notice, Panel, Row } from '../../components/ui';
import { EditorSkeleton } from '../../components/list';
import { TicketBadge, type TicketSummary } from './TicketsPage';

type Message = { id: number; from: 'user' | 'admin'; operator: string | null; text: string; file: string | null; createdAt: string | null };
type Detail = { ticket: TicketSummary & { user: { id: number; name: string | null; mobile: string | null; email: string | null } | null }; messages: Message[] };

const isImage = (path: string) => /\.(png|jpe?g|webp|gif|bmp)$/i.test(path);

export default function TicketDetail() {
    const { id } = useParams();
    const location = useLocation();
    const { csrfToken } = useBoot();
    const back = '/tickets' + ((location.state as { list?: string } | null)?.list ?? '');
    const { data, error, reload } = useQuery<Detail>(`/admin/api/tickets/${id}`);
    const [text, setText] = useState('');
    const [file, setFile] = useState<File | null>(null);
    const [sending, setSending] = useState(false);
    const [fieldError, setFieldError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);
    const fileInput = useRef<HTMLInputElement>(null);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <EditorSkeleton />;
    const { ticket, messages } = data;

    async function send(close: boolean) {
        setSending(true);
        setFieldError(null);
        setNotice(null);
        const body = new FormData();
        body.set('message', text);
        body.set('close', close ? '1' : '0');
        if (file) body.set('file', file);
        try {
            const res = await postFormData<{ success: string }>(`/admin/api/tickets/${id}/reply`, csrfToken, body);
            setText('');
            setFile(null);
            if (fileInput.current) fileInput.current.value = '';
            setNotice(res.success);
            reload();
        } catch (e) {
            const err = e as ApiError;
            setFieldError(err.fields?.message ?? err.fields?.file ?? err.message);
        } finally {
            setSending(false);
        }
    }

    async function toggle() {
        try {
            const res = await postJson<{ success: string }>(`/admin/api/tickets/${id}/status`, csrfToken, { open: ticket.status === 'closed' });
            setNotice(res.success);
            reload();
        } catch (e) {
            setNotice((e as ApiError).message);
        }
    }

    return (
        <div className="mx-auto max-w-[1280px]">
            <Link to={back} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                <ArrowRight aria-hidden size={16} />
                تیکت‌ها
            </Link>
            <header className="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div className="min-w-0">
                    <h1 className="text-[22px] leading-tight font-bold">{ticket.subject || 'بدون موضوع'}</h1>
                    <p className="mt-1.5 flex flex-wrap items-center gap-2 text-muted">
                        <TicketBadge status={ticket.status} label={ticket.statusLabel} />
                        <span className="num">#{ticket.id}</span>
                        {ticket.department && <span>· {ticket.department}</span>}
                        <span>
                            · باز شده <span dir="ltr" className="num">{ticket.createdAt}</span>
                        </span>
                    </p>
                </div>
                <button type="button" className="btn btn-secondary" onClick={toggle}>
                    {ticket.status === 'closed' ? <LockOpen aria-hidden size={16} /> : <Lock aria-hidden size={16} />}
                    {ticket.status === 'closed' ? 'باز کردن دوباره' : 'بستن تیکت'}
                </button>
            </header>

            <div className="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div className="min-w-0 space-y-6">
                    <section aria-label="گفتگو" className="rounded-2xl border border-stroke bg-surface p-5">
                        {messages.length === 0 ? (
                            <p className="text-muted">پیامی ندارد.</p>
                        ) : (
                            <ol className="space-y-4">
                                {messages.map((m) => (
                                    <li key={m.id} className={`flex ${m.from === 'admin' ? 'justify-end' : 'justify-start'}`}>
                                        <div className={`max-w-[85%] rounded-2xl px-4 py-3 ${m.from === 'admin' ? 'rounded-ee-md bg-indigo-soft' : 'rounded-es-md bg-surface-2'}`}>
                                            <p className="mb-1 flex flex-wrap gap-x-2 text-[12px] text-muted">
                                                <span className="font-bold text-body">{m.from === 'admin' ? m.operator : ticket.user?.name || 'کاربر'}</span>
                                                <span dir="ltr" className="num">{m.createdAt}</span>
                                            </p>
                                            {/* Plain text on purpose: users' messages are never rendered as HTML. */}
                                            <p className="break-words whitespace-pre-wrap" dir="auto">{m.text}</p>
                                            {m.file && (
                                                <a href={m.file} target="_blank" rel="noreferrer" className="mt-2 block">
                                                    {isImage(m.file) ? (
                                                        <img src={m.file} alt="پیوست" className="max-h-56 rounded-lg border border-stroke" />
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1.5 text-[13px] font-medium underline">
                                                            <FileText aria-hidden size={15} />
                                                            پیوست
                                                        </span>
                                                    )}
                                                </a>
                                            )}
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </section>

                    <section aria-label="پاسخ" className="rounded-2xl border border-stroke bg-surface p-5">
                        <label htmlFor="ticket-reply" className="mb-1 block font-medium">
                            پاسخ شما
                        </label>
                        <textarea
                            id="ticket-reply"
                            rows={5}
                            className="field"
                            value={text}
                            onChange={(e) => setText(e.target.value)}
                            aria-invalid={fieldError ? true : undefined}
                            aria-describedby={fieldError ? 'ticket-reply-error' : undefined}
                        />
                        {fieldError && <FieldError id="ticket-reply-error">{fieldError}</FieldError>}
                        <div className="mt-3 flex flex-wrap items-center gap-2">
                            <input ref={fileInput} type="file" accept="image/png,image/jpeg,image/webp,application/pdf" className="sr-only" id="ticket-file" onChange={(e) => setFile(e.target.files?.[0] ?? null)} />
                            {file ? (
                                <span className="inline-flex items-center gap-1.5 rounded-lg bg-surface-2 px-2.5 py-1.5 text-[13px]">
                                    <Paperclip aria-hidden size={14} />
                                    <span dir="ltr" className="latin max-w-[180px] truncate">{file.name}</span>
                                    <button type="button" aria-label="برداشتن پیوست" className="text-muted hover:text-ink" onClick={() => { setFile(null); if (fileInput.current) fileInput.current.value = ''; }}>
                                        <X aria-hidden size={14} />
                                    </button>
                                </span>
                            ) : (
                                <label htmlFor="ticket-file" className="btn btn-ghost cursor-pointer">
                                    <Paperclip aria-hidden size={16} />
                                    پیوست (تصویر یا PDF)
                                </label>
                            )}
                            <div className="flex-1" />
                            <button type="button" className="btn btn-secondary" disabled={sending || !text.trim()} onClick={() => send(true)}>
                                ارسال و بستن
                            </button>
                            <button type="button" className="btn btn-primary" disabled={sending || !text.trim()} onClick={() => send(false)}>
                                <Send aria-hidden size={16} className="rtl:-scale-x-100" />
                                {sending ? 'در حال ارسال…' : 'ارسال پاسخ'}
                            </button>
                        </div>
                        {notice && <Notice>{notice}</Notice>}
                    </section>
                </div>

                <aside className="space-y-4 lg:sticky lg:top-20">
                    <Panel title="کاربر">
                        {ticket.user ? (
                            <dl className="m-0">
                                <Row label="نام">
                                    <Link to={`/users/${ticket.user.id}`} className="underline-offset-4 hover:underline">{ticket.user.name || 'بدون نام'}</Link>
                                </Row>
                                <Row label="موبایل" ltr>{ticket.user.mobile}</Row>
                                {ticket.user.email && <Row label="ایمیل" ltr><span className="latin">{ticket.user.email}</span></Row>}
                                {ticket.orderId && (
                                    <Row label="سفارش">
                                        <Link to={`/orders/${ticket.orderId}`} className="num underline-offset-4 hover:underline">#{ticket.orderId}</Link>
                                    </Row>
                                )}
                            </dl>
                        ) : (
                            <p className="text-muted">کاربر این تیکت پیدا نشد.</p>
                        )}
                    </Panel>
                </aside>
            </div>
        </div>
    );
}
