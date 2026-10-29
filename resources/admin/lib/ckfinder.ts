/*
 * CKFinder, the media library (files under public/upload, connector Admin › routes/admin.php `ckfinder/*`, behind the
 * admin login). Its script is loaded on first use, not with the admin bundle.
 */

type CKFinderFile = { getUrl(): string; get(key: 'name'): string };
type CKFinderEvent = { data: { files: { first(): CKFinderFile }; resizedUrl?: string } };
type CKFinderInstance = { on(event: string, cb: (evt: CKFinderEvent) => void): void };
type CKFinderConfig = Record<string, unknown> & { onInit?: (finder: CKFinderInstance) => void };
type CKFinderApi = {
    modal(config: CKFinderConfig): void;
    widget(id: string, config: CKFinderConfig): void;
};

declare global {
    interface Window {
        CKFinder?: CKFinderApi;
    }
}

export const CKFINDER_CONNECTOR = '/admin/ckfinder/connector';
const SCRIPT = '/js/ckfinder/ckfinder.js';

// Persian (right to left), talking to the admin-only connector; dark like the admin when the device is dark
// (the jquery-mobile skin's "b" swatch; the default skin has no dark version).
export const ckfinderBase = {
    connectorPath: CKFINDER_CONNECTOR,
    language: 'fa',
    ...(window.matchMedia?.('(prefers-color-scheme: dark)').matches ? { skin: 'jquery-mobile', swatch: 'b' } : {}),
};

let loading: Promise<CKFinderApi> | null = null;

export function loadCKFinder(): Promise<CKFinderApi> {
    if (window.CKFinder) return Promise.resolve(window.CKFinder);
    loading ??= new Promise<CKFinderApi>((resolve, reject) => {
        const s = document.createElement('script');
        s.src = SCRIPT;
        s.async = true;
        s.onload = () => (window.CKFinder ? resolve(window.CKFinder) : reject(new Error('کتابخانه رسانه بارگذاری نشد.')));
        s.onerror = () => {
            loading = null; // let the next click try again
            reject(new Error('کتابخانه رسانه بارگذاری نشد. اتصال را بررسی کنید و دوباره امتحان کنید.'));
        };
        document.head.appendChild(s);
    });
    return loading;
}

/** Opens the library to pick one image; resolves with its address ("/upload/…"), or never if closed without a choice. */
export async function chooseImage(): Promise<string> {
    const CKFinder = await loadCKFinder();
    return new Promise((resolve) => {
        CKFinder.modal({
            ...ckfinderBase,
            resourceType: 'Images',
            chooseFiles: true,
            width: Math.min(1100, window.innerWidth - 32),
            height: Math.min(700, window.innerHeight - 32),
            onInit(finder) {
                finder.on('files:choose', (evt) => resolve(evt.data.files.first().getUrl()));
                finder.on('file:choose:resizedImage', (evt) => evt.data.resizedUrl && resolve(evt.data.resizedUrl));
            },
        });
    });
}
