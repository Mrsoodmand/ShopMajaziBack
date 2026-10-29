/** Provider columns on services and countries. A blank code means that provider doesn't offer it. */
export type ProviderKey = 'pva' | 'man' | 'active' | 'land';
export type Codes = Record<ProviderKey, string>;

/** `server` is the number the storefront shows; Land has codes but sells nothing. */
export const PROVIDERS: { key: ProviderKey; label: string; server: number | null }[] = [
    { key: 'pva', label: 'SMS-PVA', server: 1 },
    { key: 'man', label: 'SMS-Man', server: 2 },
    { key: 'active', label: 'SMS-Activate', server: 3 },
    { key: 'land', label: 'Land', server: null },
];

export const emptyCodes: Codes = { pva: '', man: '', active: '', land: '' };

export type OfferCounts = { total: number; live: number };

export type Kind = 'services' | 'countries';

/** One list row: a service (app) or a country. */
export type CatalogRow = {
    id: number;
    name: string | null;
    enName: string | null;
    slug: string | null;
    image?: string | null;
    flag?: string | null;
    flagCode?: string | null;
    regionId?: number | null;
    live: boolean;
    featured?: boolean;
    sort: number;
    codes: Codes;
    offers: OfferCounts;
};

export type Region = { id: number; name: string };

export type BaseForm = {
    name: string;
    enName: string;
    slug: string;
    sort: number | string;
    live: boolean;
    image: string;
    codes: Codes;
    content: string;
    metaTitle: string;
    metaDesc: string;
    seo: string;
};

export type ServiceForm = BaseForm & { featured: boolean };
export type CountryForm = BaseForm & { flagCode: string; regionId: number | '' };

export type OfferState = 'live' | 'waiting' | 'gone';

export type Offer = {
    id: number;
    provider: ProviderKey;
    server: number | null;
    name: string;
    image: string | null;
    serviceCode: string;
    countryCode: string;
    price: number | null;
    providerPrice: number | null;
    state: OfferState;
    updatedAt: string | null;
};

export const API = '/admin/api/virtual-numbers';
export const UPLOAD_URL = `${API}/uploads/image`;

export const nouns: Record<Kind, { one: string; plural: string; other: string }> = {
    services: { one: 'سرویس', plural: 'سرویس‌ها', other: 'کشور' },
    countries: { one: 'کشور', plural: 'کشورها', other: 'سرویس' },
};

/** «شماره مجازی کشور روسیه» reads as «کشور روسیه» in lists, as on the storefront. */
export function shortName(name: string | null | undefined): string {
    return (name ?? '').replace('شماره مجازی', '').trim();
}

export function providerLabel(key: ProviderKey): string {
    const p = PROVIDERS.find((x) => x.key === key)!;
    return p.server ? `سرور ${p.server} · ${p.label}` : p.label;
}
