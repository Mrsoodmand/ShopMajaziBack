export type View = 'review' | 'paid' | 'waiting' | 'failed' | 'all';

export type Party = { id: number; name: string | null; mobile: string | null };

export type TransactionRow = {
    id: number;
    /** payments.type: 1 order payment, 2 refund, 3 wallet top-up, 4 withdrawal, 5 commission, 6 extra volume, 8 IP renewal. */
    type: string;
    /** Complete, waiting to pay, failed, -2 under review, -3 card mismatch, -4 bank error. */
    status: string;
    gate: 'Bank' | 'Wallet' | string | null;
    price: number;
    orderId: number | null;
    refId: string | null;
    card: string | null;
    note: string | null;
    createdAt: string | null;
    createdTs: number | null;
    paidAt: string | null;
    customer: Party | null;
};

export type TransactionsPage = {
    counts: Record<View, number>;
    page: number;
    lastPage: number;
    total: number;
    /** 'all', or the parts a limited admin may see. */
    scope: 'all' | ('bank' | 'mismatch')[];
    user: Party | null;
    transactions: TransactionRow[];
};

export type Transaction = TransactionRow & {
    gateway: string | null;
    authority: string | null;
    fee: number | null;
    error: string | null;
    registeredCard: string | null;
    cardMatches: boolean | null;
    updatedAt: string | null;
    customer: (Party & { email: string | null; wallet: number; walletBlocked: boolean }) | null;
    order: { id: number; type: string; service: string | null; price: number; status: string } | null;
    refundOf: number | null;
    refundedBy: { id: number; at: string | null } | null;
    history: { id: number; from: string | null; to: string | null; reason: string | null; by: string | null; at: string | null }[];
    can: { resolve: boolean; refund: boolean };
};

export const MISMATCH = '-3';

/** Where the money went or came from; refunds and commissions move inside the shop, with no gateway. */
export function gateName(gate: string | null, type?: string): string {
    if (gate === 'Bank') return 'درگاه بانک';
    if (gate === 'Wallet') return 'کیف پول';
    if (gate) return gate;
    return type === '2' || type === '5' ? 'واریز به کیف پول' : 'سیستمی';
}
