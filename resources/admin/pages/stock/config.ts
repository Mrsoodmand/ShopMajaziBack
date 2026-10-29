/*
 * Apple IDs and Google Voice numbers share one inventory screen: logins added by hand, kept encrypted on the server,
 * handed to orders. Each kind says where its API lives and which login fields it has.
 */

export type StockField = { key: string; label: string; ltr?: boolean; optional?: boolean; placeholder?: string; wide?: boolean };

export type StockKind = {
    id: 'apple-ids' | 'google-voice';
    api: string;
    base: string;
    title: string;
    noun: string;
    addTitle: string;
    /** The field shown as the row's name in the list. */
    primary: 'appleId' | 'number';
    fields: StockField[];
    filters: { id: string; label: string }[];
    defaultFilter: string;
    /** The `pages` row that holds this service's price and page texts. */
    settingsPage: number;
    ordersHref: string;
    subtitle: string;
};

export const APPLE: StockKind = {
    id: 'apple-ids',
    api: '/admin/api/apple-ids',
    base: '/apple-ids',
    title: 'اپل آیدی',
    noun: 'اپل آیدی',
    addTitle: 'افزودن اپل آیدی آماده',
    primary: 'appleId',
    fields: [
        { key: 'appleId', label: 'اپل آیدی (ایمیل)', ltr: true, placeholder: 'name@icloud.com', wide: true },
        { key: 'password', label: 'رمز عبور', ltr: true },
        { key: 'birthday', label: 'تاریخ تولد', ltr: true, placeholder: '1990/01/31' },
        { key: 'q1', label: 'پاسخ سؤال امنیتی ۱' },
        { key: 'q2', label: 'پاسخ سؤال امنیتی ۲' },
        { key: 'q3', label: 'پاسخ سؤال امنیتی ۳' },
    ],
    filters: [
        { id: 'stock', label: 'در انبار' },
        { id: 'sold', label: 'فروخته‌شده' },
        { id: 'custom', label: 'سفارشی' },
        { id: 'all', label: 'همه' },
    ],
    defaultFilter: 'stock',
    settingsPage: 3,
    ordersHref: '/orders?type=3&view=all',
    subtitle: 'آماده‌ها از انبار به سفارش‌ها داده می‌شوند؛ سفارشی‌ها را پشتیبانی برای هر سفارش می‌سازد.',
};

export const GOOGLE_VOICE: StockKind = {
    id: 'google-voice',
    api: '/admin/api/google-voices',
    base: '/google-voice',
    title: 'گوگل ویس',
    noun: 'شماره',
    addTitle: 'افزودن شماره گوگل ویس',
    primary: 'number',
    fields: [
        { key: 'number', label: 'شماره', ltr: true, placeholder: '+1 (502) 818-0090' },
        { key: 'email', label: 'ایمیل حساب گوگل', ltr: true, placeholder: 'name@gmail.com' },
        { key: 'password', label: 'رمز عبور', ltr: true },
        { key: 'backupEmail', label: 'ایمیل پشتیبان', ltr: true, optional: true },
        { key: 'twoFactor', label: 'کد ورود دومرحله‌ای', ltr: true, optional: true, wide: true },
    ],
    filters: [
        { id: 'all', label: 'همه' },
        { id: 'stock', label: 'در انبار' },
        { id: 'sold', label: 'تحویل‌شده' },
    ],
    defaultFilter: 'all',
    settingsPage: 4,
    ordersHref: '/orders?type=6&view=all',
    subtitle: 'شماره‌های فروخته‌شده از نامبرلند و شماره‌هایی که دستی اضافه می‌شوند.',
};

export type StockRow = {
    id: number;
    appleId?: string;
    number?: string;
    kind?: 'ready' | 'custom';
    delivered: boolean;
    orderId: number | null;
    user: { id: number; name: string | null; mobile: string | null } | null;
    createdAt: string | null;
};

export type StockPage = {
    page: number;
    lastPage: number;
    total: number;
    counts: Record<string, number>;
    waitingOrders?: number;
    items: StockRow[];
};

export type StockItem = Record<string, string> & { id: number; delivered: boolean; orderId: number | null; kind?: 'ready' | 'custom'; createdAt: string | null };

export function blankRow(kind: StockKind): Record<string, string> {
    return Object.fromEntries(kind.fields.map((f) => [f.key, '']));
}
