import { useCallback, useEffect, useState } from 'react';

/** Light, dark, or follow the device. Stored per browser; the Blade shells apply it before paint. */
export type ThemeChoice = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'admin-theme';
const media = () => window.matchMedia('(prefers-color-scheme: dark)');

function readChoice(): ThemeChoice {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        return saved === 'light' || saved === 'dark' ? saved : 'system';
    } catch {
        return 'system';
    }
}

/** Whether the admin is showing dark right now (for widgets that pick their own skin, like CKFinder). */
export const isDark = () => document.documentElement.classList.contains('dark');

export function useTheme() {
    const [choice, setChoiceState] = useState<ThemeChoice>(readChoice);
    const [systemDark, setSystemDark] = useState(() => media().matches);

    useEffect(() => {
        const query = media();
        const listener = (event: MediaQueryListEvent) => setSystemDark(event.matches);
        query.addEventListener('change', listener);
        return () => query.removeEventListener('change', listener);
    }, []);

    const resolved = choice === 'system' ? (systemDark ? 'dark' : 'light') : choice;

    useEffect(() => {
        const root = document.documentElement;
        root.classList.add('theme-switching');
        root.classList.toggle('dark', resolved === 'dark');
        const frame = requestAnimationFrame(() => requestAnimationFrame(() => root.classList.remove('theme-switching')));
        return () => cancelAnimationFrame(frame);
    }, [resolved]);

    const setChoice = useCallback((next: ThemeChoice) => {
        setChoiceState(next);
        try {
            if (next === 'system') localStorage.removeItem(STORAGE_KEY);
            else localStorage.setItem(STORAGE_KEY, next);
        } catch {
            /* storage blocked: the choice lasts for this visit */
        }
    }, []);

    return { choice, resolved, setChoice };
}
