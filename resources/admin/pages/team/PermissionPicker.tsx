import { useEffect, useRef } from 'react';
import { count } from '../../lib/format';

export type PermissionGroup = { id: number; name: string; permissions: { id: number; name: string; faName: string }[] };

/**
 * Permissions as checkboxes, one block per group with a "whole group" box.
 * `fixed` ones come from elsewhere (the admin's role): shown ticked and locked.
 */
export function PermissionPicker({
    groups,
    selected,
    onChange,
    fixed = [],
    fixedLabel = 'از نقش',
}: {
    groups: PermissionGroup[];
    selected: number[];
    onChange: (ids: number[]) => void;
    fixed?: number[];
    fixedLabel?: string;
}) {
    const chosen = new Set(selected);
    const locked = new Set(fixed);

    function toggle(ids: number[], on: boolean) {
        const next = new Set(chosen);
        for (const id of ids) {
            if (locked.has(id)) continue;
            if (on) next.add(id);
            else next.delete(id);
        }
        onChange([...next]);
    }

    return (
        <div className="grid gap-3 md:grid-cols-2">
            {groups.map((g) => {
                const free = g.permissions.filter((p) => !locked.has(p.id)).map((p) => p.id);
                const on = g.permissions.filter((p) => chosen.has(p.id) || locked.has(p.id)).length;
                return (
                    <fieldset key={g.id} className="m-0 min-w-0 rounded-xl border border-stroke p-0">
                        <legend className="sr-only">{g.name}</legend>
                        <div className="flex items-center justify-between gap-2 border-b border-stroke bg-surface-2 px-4 py-2.5">
                            <GroupBox
                                label={g.name}
                                state={on === 0 ? 'none' : on === g.permissions.length ? 'all' : 'some'}
                                disabled={free.length === 0}
                                onChange={(v) => toggle(free, v)}
                            />
                            <span className="num shrink-0 text-[12px] text-muted">
                                {count(on)} از {count(g.permissions.length)}
                            </span>
                        </div>
                        <ul className="m-0 space-y-0.5 p-2">
                            {g.permissions.map((p) => {
                                const isLocked = locked.has(p.id);
                                return (
                                    <li key={p.id}>
                                        <label className={`flex items-center gap-2.5 rounded-lg px-2 py-1.5 ${isLocked ? 'cursor-default' : 'cursor-pointer hover:bg-surface-2'}`}>
                                            <input
                                                type="checkbox"
                                                className="size-4 shrink-0 accent-[var(--primary)]"
                                                checked={isLocked || chosen.has(p.id)}
                                                disabled={isLocked}
                                                onChange={(e) => toggle([p.id], e.target.checked)}
                                            />
                                            <span className="min-w-0 flex-1">{p.faName}</span>
                                            {isLocked && <span className="shrink-0 rounded-md bg-surface-2 px-1.5 text-[12px] text-muted">{fixedLabel}</span>}
                                        </label>
                                    </li>
                                );
                            })}
                        </ul>
                    </fieldset>
                );
            })}
        </div>
    );
}

function GroupBox({ label, state, disabled, onChange }: { label: string; state: 'none' | 'some' | 'all'; disabled: boolean; onChange: (on: boolean) => void }) {
    const box = useRef<HTMLInputElement>(null);
    useEffect(() => {
        if (box.current) box.current.indeterminate = state === 'some';
    }, [state]);
    return (
        <label className={`flex min-w-0 items-center gap-2.5 font-bold ${disabled ? '' : 'cursor-pointer'}`}>
            <input ref={box} type="checkbox" className="size-4 shrink-0 accent-[var(--primary)]" checked={state === 'all'} disabled={disabled} onChange={(e) => onChange(e.target.checked)} />
            <span className="truncate">{label}</span>
        </label>
    );
}
