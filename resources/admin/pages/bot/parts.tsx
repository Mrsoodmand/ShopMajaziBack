import { CircleCheck, CircleX, Clock, Hourglass, PauseCircle, Scale, Send, FilePen, type LucideIcon } from 'lucide-react';
import { Badge, type Tone } from '../../components/Status';

export type BotOrderStatus = 'waiting' | 'active' | 'done' | 'suspended' | 'ended';
export type BotPayStatus = 'paid' | 'waiting' | 'failed' | 'review' | 'mismatch' | 'bank-error';
export type BotMessageStatus = 'queued' | 'sent' | 'draft';

export type BotOrderSummary = { id: number; title: string; status: BotOrderStatus; createdAt: string | null };
export type BotPayment = {
    id: number;
    orderId: number | null;
    type: string;
    method: string | null;
    amount: number;
    status: BotPayStatus;
    trackId: string | null;
    at: string | null;
};
export type BotUserRef = { id: number; username: string; telegramId: string; name: string };

export const ORDER_STATUS: Record<BotOrderStatus, { label: string; tone: Tone; icon: LucideIcon }> = {
    waiting: { label: 'در انتظار پرداخت', tone: 'neutral', icon: Hourglass },
    active: { label: 'فعال', tone: 'success', icon: CircleCheck },
    done: { label: 'تکمیل‌شده', tone: 'info', icon: CircleCheck },
    suspended: { label: 'معلق (تمدیدنشده)', tone: 'warn', icon: PauseCircle },
    ended: { label: 'پایان‌یافته یا لغو', tone: 'danger', icon: CircleX },
};

const PAY_STATUS: Record<BotPayStatus, { label: string; tone: Tone; icon: LucideIcon }> = {
    paid: { label: 'پرداخت‌شده', tone: 'success', icon: CircleCheck },
    waiting: { label: 'در انتظار پرداخت', tone: 'neutral', icon: Hourglass },
    failed: { label: 'ناموفق', tone: 'danger', icon: CircleX },
    review: { label: 'در حال بررسی', tone: 'info', icon: Clock },
    mismatch: { label: 'مغایرت', tone: 'warn', icon: Scale },
    'bank-error': { label: 'خطای بانک', tone: 'danger', icon: CircleX },
};

export const MESSAGE_STATUS: Record<BotMessageStatus, { label: string; tone: Tone; icon: LucideIcon }> = {
    queued: { label: 'در صف ارسال', tone: 'progress', icon: Send },
    sent: { label: 'ارسال‌شده', tone: 'success', icon: CircleCheck },
    draft: { label: 'پیش‌نویس', tone: 'neutral', icon: FilePen },
};

export function BotOrderBadge({ status }: { status: BotOrderStatus }) {
    const m = ORDER_STATUS[status] ?? ORDER_STATUS.waiting;
    return <Badge tone={m.tone} icon={m.icon} label={m.label} />;
}

export function BotPayBadge({ status }: { status: BotPayStatus }) {
    const m = PAY_STATUS[status] ?? PAY_STATUS.waiting;
    return <Badge tone={m.tone} icon={m.icon} label={m.label} />;
}

export function BotMessageBadge({ status }: { status: BotMessageStatus }) {
    const m = MESSAGE_STATUS[status] ?? MESSAGE_STATUS.draft;
    return <Badge tone={m.tone} icon={m.icon} label={m.label} />;
}

/** "@username" when there is one, otherwise the display name or the Telegram id. */
export function botUserName(u: { username: string; name: string; telegramId: string }) {
    return u.username ? `@${u.username}` : u.name || u.telegramId;
}
