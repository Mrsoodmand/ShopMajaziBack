import { createContext, useContext } from 'react';

/** `badge` names a number in `menuCounts` shown beside the link (pending orders, tickets waiting for a reply). */
export type MenuLink = { label: string; icon?: string; to?: string; href?: string; badge?: string; children?: MenuLink[] };
export type MenuSection = { title: string | null; items: MenuLink[] };

/** Data Laravel renders into the page (ReactAppController). */
export type AdminBoot = {
    admin: { id: number; name: string | null };
    basePath: string;
    csrfToken: string;
    links: { legacyHome: string; logout: string };
    actions: { changeStatus: string; refund: string; sendMessage: string; saveUser: string };
    labels: {
        orderStatus: Record<string, string>;
        orderType: Record<string, string>;
        payStatus: Record<string, string>;
        paymentType: Record<string, string>;
    };
    menu: MenuSection[];
    menuCounts: Record<string, number>;
};

export const BootContext = createContext<AdminBoot | null>(null);

export function useBoot(): AdminBoot {
    const boot = useContext(BootContext);
    if (!boot) throw new Error('Admin boot data is missing.');
    return boot;
}
