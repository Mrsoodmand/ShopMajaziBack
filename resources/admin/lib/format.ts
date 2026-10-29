const grouped = new Intl.NumberFormat('en-US');

export function toman(value: number | null | undefined): string {
    return value == null ? '—' : `${grouped.format(value)} تومان`;
}

export function count(value: number): string {
    return grouped.format(value);
}

/** Accepts Persian/Arabic digits and separators; returns a whole number of toman or null. */
export function parseAmount(raw: string): number | null {
    const latin = raw.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d))).replace(/[٠-٩]/g, (d) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
    const digits = latin.replace(/[,،٬\s]/g, '');
    if (!/^\d+$/.test(digits)) return null;
    const n = Number(digits);
    return Number.isSafeInteger(n) ? n : null;
}

/** A dollar price like "0.45", "۰٫۴۵" or "0/45" → 0.45; null when empty or not a number. */
export function parseDecimal(raw: string | number | null | undefined): number | null {
    if (raw == null || raw === '') return null;
    if (typeof raw === 'number') return raw;
    const latin = raw.replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d))).replace(/[٠-٩]/g, (d) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
    const clean = latin.replace(/[٫/]/g, '.').replace(/[,،٬\s]/g, '');
    return /^\d*\.?\d+$/.test(clean) ? Number(clean) : null;
}

/** Rounds toman the way the storefront does: the last two digits become 00 or 50. */
export function roundToman(raw: number): number {
    const n = Math.round(raw);
    return n - (n % 100) + (n % 100 >= 50 ? 50 : 0);
}

const HOUR = 3600;

/** How long something has been waiting (an order in the queue, a ticket); over a day counts as late. */
export function waited(ts: number | null): { text: string; late: boolean } | null {
    if (!ts) return null;
    const s = Math.max(0, Date.now() / 1000 - ts);
    const late = s > 24 * HOUR;
    if (s < HOUR) return { text: `${Math.max(1, Math.round(s / 60))} دقیقه`, late };
    if (s < 48 * HOUR) return { text: `${Math.round(s / HOUR)} ساعت`, late };
    const days = Math.round(s / (24 * HOUR));
    if (days < 60) return { text: `${days} روز`, late };
    if (days < 730) return { text: `${Math.round(days / 30)} ماه`, late };
    return { text: `${Math.round(days / 365)} سال`, late };
}

/** Short toman for chart labels: 787,600 → «۷۸۸ هزار», 3,200,000 → «۳٫۲ میلیون». */
export function shortToman(value: number): string {
    if (value >= 1_000_000_000) return `${trim(value / 1_000_000_000)} میلیارد`;
    if (value >= 1_000_000) return `${trim(value / 1_000_000)} میلیون`;
    if (value >= 1_000) return `${Math.round(value / 1_000)} هزار`;
    return grouped.format(value);
}

function trim(n: number) {
    return n >= 10 ? String(Math.round(n)) : n.toFixed(1).replace(/\.0$/, '').replace('.', '٫');
}
