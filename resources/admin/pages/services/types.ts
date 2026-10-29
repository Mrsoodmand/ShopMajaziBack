export type Platform = {
    id: number;
    name: string;
    enName: string | null;
    slug: string;
    short: string | null;
    tagline: string | null;
    taglineEn: string | null;
    content: string | null;
    image: string | null;
    color: string | null;
    sort: number;
    featured: boolean;
    visible: boolean;
    metaTitle: string | null;
    metaDesc: string | null;
    services?: number;
    liveServices?: number;
};

export type ServiceRow = {
    id: number;
    name: string | null;
    enName: string | null;
    slug: string;
    categoryId: number;
    live: boolean;
    pricing: 'count' | 'plans';
    dollarPer500: number | null;
    dollarPer1000: number | null;
    tomanPer1000: number | null;
    plans: number;
};

export type ServiceForm = {
    id?: number;
    categoryId: number | '';
    name: string;
    enName: string;
    slug: string;
    live: boolean;
    short: string;
    content: string;
    image: string;
    metaTitle: string;
    metaDesc: string;
    /** count: any amount in steps of 500 at dollarPer500; plans: fixed packages at dollarPer1000. */
    pricing: 'count' | 'plans';
    dollarPer1000: number | string | null;
    dollarPer500: number | string | null;
    minCount: number | string;
    maxCount: number | string;
    commission: number | string | null;
    plans: { id?: number; name: string; count: number | string; live: boolean }[];
    fields: { name: string; title: string; type: 'text' | 'select'; options: string[] }[];
    notes: { label: string; value: string }[];
    faq: { q: string; a: string }[];
};

export const UPLOAD_URL = '/admin/api/uploads/service-image';

/** Platform tile: uploaded logo, else the first letter on the brand colour. */
export function platformInitial(p: Pick<Platform, 'name' | 'enName'>) {
    return (p.enName || p.name || '?').trim().charAt(0).toUpperCase();
}
