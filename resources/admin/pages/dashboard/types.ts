/** Shape of /admin/api/dashboard (Admin\DashboardApiController). Blocks the admin may not open are left out. */

export type Customer = { id: number; name: string | null; mobile: string };

export type QueueOrder = {
    id: number;
    type: string;
    service: string | null;
    price: number;
    status: string;
    createdAt: string | null;
    createdTs: number | null;
    customer: Customer | null;
};

export type Day = { date: string; weekday: string; orders: number; sales: number };

export type Ticket = {
    id: number;
    subject: string | null;
    status: string;
    unread: boolean;
    customer: string | null;
    updatedAt: string | null;
    updatedTs: number | null;
};

export type Mismatch = { id: number; price: number; customer: string | null; userId: number | null; createdAt: string | null; createdTs: number | null };

export type Comment = { id: number; name: string | null; text: string; createdAt: string | null; createdTs: number | null };

export type Dashboard = {
    today: string;
    users: { total: number; today: number };
    days?: Day[];
    byType?: { type: string; orders: number; sales: number }[];
    queue?: {
        total: number;
        counts: Record<string, number>;
        waitingToPay: number;
        oldest: QueueOrder[];
    };
    tickets?: { total: number; items: Ticket[]; link: string; itemLink: string };
    mismatches?: { total: number; items: Mismatch[]; link: string };
    comments?: { total: number; items: Comment[]; link: string };
};
