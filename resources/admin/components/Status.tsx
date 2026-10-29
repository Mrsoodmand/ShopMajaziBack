import {
    CircleCheck,
    CircleX,
    Clock,
    CirclePlay,
    Hourglass,
    PauseCircle,
    Scale,
    Undo2,
    type LucideIcon,
} from 'lucide-react';
import { useBoot } from '../lib/boot';

export type Tone = 'neutral' | 'info' | 'progress' | 'warn' | 'success' | 'danger';

const toneClass: Record<Tone, string> = {
    neutral: 'bg-surface-2 text-muted',
    info: 'bg-indigo-soft text-indigo',
    progress: 'bg-sky-500/12 text-sky-700 dark:text-sky-300',
    warn: 'bg-warn-soft text-warn',
    success: 'bg-emerald-500/12 text-emerald-800 dark:text-emerald-300',
    danger: 'bg-error-soft text-error',
};

const orderStatus: Record<string, { tone: Tone; icon: LucideIcon }> = {
    'waiting to pay': { tone: 'neutral', icon: Hourglass },
    pending: { tone: 'info', icon: Clock },
    progressing: { tone: 'progress', icon: CirclePlay },
    suspended: { tone: 'warn', icon: PauseCircle },
    complete: { tone: 'success', icon: CircleCheck },
    failed: { tone: 'danger', icon: CircleX },
    'back money to wallet': { tone: 'danger', icon: Undo2 },
};

const payStatus: Record<string, { tone: Tone; icon: LucideIcon }> = {
    Complete: { tone: 'success', icon: CircleCheck },
    'waiting to pay': { tone: 'neutral', icon: Hourglass },
    failed: { tone: 'danger', icon: CircleX },
    '-3': { tone: 'warn', icon: Scale }, // card mismatch: paid, wallet not credited
    '-2': { tone: 'info', icon: Clock }, // under review
    '-4': { tone: 'danger', icon: CircleX }, // bank refused to verify
};

export function Badge({ tone, icon: Icon, label }: { tone: Tone; icon: LucideIcon; label: string }) {
    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[13px] font-medium whitespace-nowrap ${toneClass[tone]}`}>
            <Icon aria-hidden size={14} strokeWidth={2.25} />
            {label}
        </span>
    );
}

export function OrderStatus({ status }: { status: string }) {
    const { labels } = useBoot();
    const meta = orderStatus[status] ?? { tone: 'neutral' as Tone, icon: Clock };
    return <Badge {...meta} label={labels.orderStatus[status]?.trim() || status} />;
}

export function PayStatus({ status }: { status: string | null }) {
    const { labels } = useBoot();
    if (!status) return <span className="text-muted">—</span>;
    const meta = payStatus[status] ?? { tone: 'neutral' as Tone, icon: Clock };
    return <Badge {...meta} label={labels.payStatus[status]?.trim() || status} />;
}
