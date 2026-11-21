import type { ReactNode } from 'react';
import { count } from '../lib/format';

// The dashed "receipt" beside a plan's fields (accounts, social services, Telegram Stars): how its site price is
// worked out, line by line, and what the customer pays. `approx` marks prices read at the live dollar rate.
export type ReceiptLine = { label: string; value: ReactNode; strike?: boolean };

export function PriceReceipt({ lines, total, approx = false, empty = 'قیمت را وارد کنید' }: { lines: (ReceiptLine | false | null | undefined)[]; total: number | null; approx?: boolean; empty?: string }) {
    const shown = lines.filter(Boolean) as ReceiptLine[];
    return (
        <div className="grid gap-1.5 rounded-xl border border-dashed border-stroke bg-surface-2 p-3.5 text-[13px]">
            {shown.map((l) => (
                <div key={l.label} className="flex items-baseline justify-between gap-3">
                    <span className="text-muted">{l.label}</span>
                    <span className={`num ${l.strike ? 'text-muted line-through' : ''}`}>{l.value}</span>
                </div>
            ))}
            <div className={`flex flex-wrap items-baseline justify-between gap-x-3 ${shown.length ? 'mt-1 border-t border-stroke pt-2.5' : ''}`}>
                <span className="whitespace-nowrap font-bold">فروش در سایت</span>
                {total !== null ? (
                    <span className="num whitespace-nowrap text-[17px] font-bold text-ink">
                        {approx ? '≈ ' : ''}{count(total)} <span className="text-[12px] font-medium text-muted">تومان</span>
                    </span>
                ) : (
                    <span className="text-muted">{empty}</span>
                )}
            </div>
        </div>
    );
}

// "$20" kept left-to-right inside Persian text.
export const usd = (n: number) => <span dir="ltr">${n}</span>;
