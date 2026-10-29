import { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router';
import { ArrowRight, Trash2 } from 'lucide-react';
import { ApiError, postJson, useQuery } from '../../lib/api';
import { useBoot } from '../../lib/boot';
import { useSave } from '../../lib/useSave';
import { EditorLayout } from '../../components/EditorLayout';
import { Field } from '../../components/form';
import { CopyButton, ErrorState, Panel, Row } from '../../components/ui';
import { ConfirmButton, EditorSkeleton, problemsFrom } from '../../components/list';
import type { StockItem as Item, StockKind } from './config';

/** One login: delivered ones are read-only (with copy buttons for support); ones in stock can be edited or removed. */
export default function StockItem({ kind }: { kind: StockKind }) {
    const { id } = useParams();
    const navigate = useNavigate();
    const location = useLocation();
    const { csrfToken } = useBoot();
    const back = kind.base + ((location.state as { list?: string } | null)?.list ?? '');
    const { data, error, reload } = useQuery<{ item: Item }>(`${kind.api}/${id}`);
    const save = useSave();
    const [form, setForm] = useState<Record<string, string>>({});
    const [deleting, setDeleting] = useState(false);
    const [deleteError, setDeleteError] = useState<string | null>(null);

    useEffect(() => {
        if (data) setForm(Object.fromEntries(kind.fields.map((f) => [f.key, data.item[f.key] ?? ''])));
    }, [data, kind.fields]);

    if (error) return <ErrorState error={error} onRetry={reload} boxed />;
    if (!data) return <EditorSkeleton />;
    const item = data.item;
    const title = item[kind.primary] || kind.title;

    async function remove() {
        setDeleting(true);
        setDeleteError(null);
        try {
            await postJson(`${kind.api}/${id}/delete`, csrfToken, {});
            navigate(back, { replace: true });
        } catch (e) {
            setDeleteError((e as ApiError).message);
            setDeleting(false);
        }
    }

    if (item.delivered) {
        return (
            <div className="mx-auto max-w-[760px]">
                <Link to={back} className="mb-3 inline-flex items-center gap-1.5 text-muted hover:text-ink">
                    <ArrowRight aria-hidden size={16} />
                    {kind.title}
                </Link>
                <h1 dir="ltr" className="latin mb-1 text-end text-[22px] leading-tight font-bold sm:text-start">
                    {title}
                </h1>
                <p className="mb-6 text-muted">
                    تحویل شده{item.orderId ? <> در سفارش <Link to={`/orders/${item.orderId}`} className="num font-medium text-ink underline underline-offset-4">#{item.orderId}</Link></> : null}
                    {item.kind === 'custom' ? ' · سفارشی' : ''} · ثبت <span className="num" dir="ltr">{item.createdAt}</span>
                </p>
                <Panel title="اطلاعات ورود">
                    <dl className="m-0">
                        {kind.fields.map((f) => (
                            <Row key={f.key} label={f.label} ltr={f.ltr}>
                                {item[f.key] ? (
                                    <>
                                        <span className={f.ltr ? 'latin' : ''}>{item[f.key]}</span>
                                        <CopyButton value={item[f.key]} label={f.label} />
                                    </>
                                ) : (
                                    <span className="text-muted">—</span>
                                )}
                            </Row>
                        ))}
                    </dl>
                </Panel>
            </div>
        );
    }

    const labels = Object.fromEntries(kind.fields.map((f) => [f.key, f.label]));
    return (
        <EditorLayout
            backTo={back}
            backLabel={kind.title}
            title={title}
            subtitle={<>در انبار · ثبت <span className="num" dir="ltr">{item.createdAt}</span></>}
            onSubmit={() => save.run(`${kind.api}/${id}`, form).then((r) => r && reload())}
            saving={save.saving}
            saveLabel="ذخیره تغییرات"
            saved={save.saved}
            problems={problemsFrom(save.errors, labels, save.failure)}
            side={
                <div className="space-y-2 border-b border-stroke pb-4">
                    <p className="text-[13px] text-muted">تا وقتی به سفارشی داده نشده، می‌توانید آن را از انبار بردارید.</p>
                    <ConfirmButton
                        label="حذف از انبار"
                        confirmLabel="بله، حذف شود"
                        question="حذف شود؟"
                        icon={<Trash2 aria-hidden size={16} />}
                        busy={deleting}
                        onConfirm={remove}
                        className="btn btn-secondary w-full text-error"
                    />
                    {deleteError && <p role="alert" className="text-[13px] font-bold text-error">{deleteError}</p>}
                </div>
            }
        >
            <Panel title="اطلاعات ورود">
                <div className="grid gap-4 sm:grid-cols-2">
                    {kind.fields.map((f) => (
                        <Field key={f.key} label={f.label} optional={f.optional} error={save.errors[f.key]} className={f.wide ? 'sm:col-span-2' : ''}>
                            {(p) => (
                                <input
                                    {...p}
                                    dir={f.ltr ? 'ltr' : undefined}
                                    autoComplete="off"
                                    className={`field ${f.ltr ? 'latin' : ''}`}
                                    value={form[f.key] ?? ''}
                                    onChange={(e) => {
                                        save.clearSaved();
                                        setForm({ ...form, [f.key]: e.target.value });
                                    }}
                                />
                            )}
                        </Field>
                    ))}
                </div>
            </Panel>
        </EditorLayout>
    );
}
