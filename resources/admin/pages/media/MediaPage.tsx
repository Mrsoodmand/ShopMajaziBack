import { useEffect, useState } from 'react';
import { AlertTriangle } from 'lucide-react';
import { ckfinderBase, loadCKFinder } from '../../lib/ckfinder';

/*
 * Media library: CKFinder over public/upload, the folder every image on the site comes from (services, platforms,
 * share images, blog…). Upload, rename, move, resize and delete here; image fields pick from it with "از کتابخانه".
 */
export default function MediaPage() {
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let alive = true;
        const box = () => document.getElementById('media-library');
        loadCKFinder()
            .then((CKFinder) => {
                const el = box();
                if (!alive || !el) return;
                el.innerHTML = '';
                CKFinder.widget('media-library', { ...ckfinderBase, width: '100%', height: '100%' });
            })
            .catch((e: Error) => alive && setError(e.message));
        return () => {
            alive = false;
            box()?.replaceChildren();
        };
    }, []);

    return (
        <div className="mx-auto flex h-[calc(100dvh-7.5rem)] min-h-[520px] max-w-[1280px] flex-col">
            <div className="mb-4">
                <h1 className="text-[22px] leading-tight font-bold">کتابخانه رسانه</h1>
                <p className="mt-1 max-w-3xl text-muted">
                    همه تصویرها و فایل‌های سایت. اینجا بارگذاری، جابه‌جا، تغییر اندازه و حذف کنید؛ در فرم‌ها هم با دکمه «از کتابخانه» از همین‌جا انتخاب می‌شود.
                    فایلی که جایی از سایت استفاده می‌شود را حذف یا جابه‌جا نکنید، وگرنه آن تصویر در سایت دیده نمی‌شود.
                </p>
            </div>
            {error ? (
                <p role="alert" className="flex items-start gap-2 rounded-2xl border border-stroke bg-surface p-5 font-bold text-error">
                    <AlertTriangle aria-hidden size={18} className="mt-0.5 shrink-0" />
                    {error}
                </p>
            ) : (
                <div id="media-library" aria-label="کتابخانه رسانه" className="min-h-0 flex-1 overflow-hidden rounded-2xl border border-stroke bg-surface" />
            )}
        </div>
    );
}
