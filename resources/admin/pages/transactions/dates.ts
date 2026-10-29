const jalaliParts = new Intl.DateTimeFormat('en-u-ca-persian-nu-latn', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    timeZone: 'Asia/Tehran',
});

/** A date in the shop's calendar and time zone: 1404/08/07. */
export function jalali(date: Date): string {
    const parts = jalaliParts.formatToParts(date);
    const get = (type: string) => parts.find((p) => p.type === type)?.value ?? '';
    return `${get('year')}/${get('month')}/${get('day')}`;
}

export type RangePreset = 'any' | 'today' | 'week' | 'month' | 'custom';

export const presets: { id: RangePreset; label: string }[] = [
    { id: 'any', label: 'همه زمان‌ها' },
    { id: 'today', label: 'امروز' },
    { id: 'week', label: '۷ روز اخیر' },
    { id: 'month', label: '۳۰ روز اخیر' },
    { id: 'custom', label: 'بازه دلخواه…' },
];

const DAY = 24 * 3600 * 1000;

/** The from/to a preset stands for (today counts as one of the days). */
export function presetRange(id: RangePreset): { from: string; to: string } {
    if (id === 'any' || id === 'custom') return { from: '', to: '' };
    const now = Date.now();
    const back = id === 'today' ? 0 : id === 'week' ? 6 : 29;
    return { from: jalali(new Date(now - back * DAY)), to: jalali(new Date(now)) };
}

/** Which preset a from/to pair matches, so the select shows it after a reload. */
export function matchPreset(from: string, to: string): RangePreset {
    if (!from && !to) return 'any';
    for (const p of ['today', 'week', 'month'] as const) {
        const r = presetRange(p);
        if (r.from === from && r.to === to) return p;
    }
    return 'custom';
}

/** Accepts ۱۴۰۴/۸/۷ or 1404-08-07; returns 1404/08/07, or null when it is not a plausible Jalali date. */
export function normalizeJalali(raw: string): string | null {
    const latin = raw
        .trim()
        .replace(/[۰-۹]/g, (d) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)))
        .replace(/[٠-٩]/g, (d) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)));
    const m = latin.match(/^(\d{4})[/\-.](\d{1,2})[/\-.](\d{1,2})$/);
    if (!m) return null;
    const [y, mo, d] = [Number(m[1]), Number(m[2]), Number(m[3])];
    if (y < 1300 || y > 1500 || mo < 1 || mo > 12 || d < 1 || d > (mo <= 6 ? 31 : 30)) return null;
    return `${y}/${String(mo).padStart(2, '0')}/${String(d).padStart(2, '0')}`;
}
