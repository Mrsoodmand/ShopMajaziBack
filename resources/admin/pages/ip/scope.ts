import { createContext, useContext } from 'react';

/** Where the static-IP screens read from and link to. */
export type IpScope = {
    api: string;
    base: string;
    title: string;
    subtitle: string;
    /** Website location: flag image and SEO fields are required. */
    webPage: boolean;
    ordersLink: string;
    pageLink: string | null;
    clientFilters: { id: string; label: string }[];
    orderPath: (id: number) => string;
    userPath: (id: number) => string;
};

export const SHOP_IP: IpScope = {
    api: '/admin/api/ip',
    base: '/ip',
    title: 'آی‌پی ثابت',
    subtitle: 'هر کشور پلن‌های فروش، سرورها و کلاینت‌های آماده خودش را دارد. سفارش پرداخت‌شده یک کلاینت آزاد از همان کشور می‌گیرد.',
    webPage: true,
    ordersLink: '/orders?type=5&view=all',
    pageLink: '/pages/5',
    clientFilters: [
        { id: 'all', label: 'همه' },
        { id: 'free', label: 'آزاد' },
        { id: 'active', label: 'فعال' },
        { id: 'expired', label: 'منقضی' },
        { id: 'ended', label: 'پایان‌یافته' },
    ],
    orderPath: (id) => `/orders/${id}`,
    userPath: (id) => `/users/${id}`,
};

export const IpScopeContext = createContext<IpScope>(SHOP_IP);
export const useIpScope = () => useContext(IpScopeContext);
