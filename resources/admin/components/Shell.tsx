import { useCallback, useEffect, useLayoutEffect, useRef, useState, type RefObject } from 'react';
import { Link, Outlet, useLocation } from 'react-router';
import {
    AppWindow,
    ArrowLeftRight,
    Bot,
    ChevronDown,
    FileText,
    Globe,
    Handshake,
    House,
    Landmark,
    LogOut,
    Menu,
    MessageSquareText,
    Mic,
    Newspaper,
    PanelsTopLeft,
    Phone,
    ReceiptText,
    Images,
    Scale,
    SearchCheck,
    ShieldCheck,
    Smartphone,
    Sparkles,
    Ticket,
    UserCog,
    Users,
    X,
    type LucideIcon,
} from 'lucide-react';
import { useBoot, type MenuLink } from '../lib/boot';

const icons: Record<string, LucideIcon> = {
    home: House,
    orders: ReceiptText,
    transactions: ArrowLeftRight,
    bank: Landmark,
    mismatch: Scale,
    phone: Phone,
    services: Sparkles,
    appleid: Smartphone,
    ip: Globe,
    voice: Mic,
    accounts: AppWindow,
    users: Users,
    tickets: Ticket,
    comments: MessageSquareText,
    blog: FileText,
    news: Newspaper,
    pages: PanelsTopLeft,
    seo: SearchCheck,
    media: Images,
    roles: ShieldCheck,
    admins: UserCog,
    partners: Handshake,
    bot: Bot,
};

// One easing for the rail: quick to start, soft to land.
const EASE = 'ease-[cubic-bezier(0.16,1,0.3,1)]';

const rowClass =
    'group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[14px] text-rail-ink transition-colors duration-150 hover:bg-rail-hover hover:text-white focus-visible:outline-offset-0';
const iconClass = 'shrink-0 text-rail-muted transition-colors duration-150 group-hover:text-rail-ink';

const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

function MenuItem({ item }: { item: MenuLink }) {
    const location = useLocation();
    const Icon = item.icon ? icons[item.icon] : undefined;
    const childActive = !!item.children?.some((c) => c.to && isActive(c.to, location.pathname, location.search));
    const active = !!item.to && isActive(item.to, location.pathname, location.search);
    const icon = Icon ? <Icon aria-hidden size={18} strokeWidth={1.75} className={`${iconClass} ${active ? '!text-white' : ''}`} /> : null;
    const [open, setOpen] = useState(childActive);
    const opened = useRef(false); // opened by a click (not by navigating), so reveal it once it has slid open

    // Arriving on one of its pages from elsewhere (a link in a page, back/forward) opens the group.
    useEffect(() => {
        if (childActive) setOpen(true);
    }, [childActive]);

    if (item.children) {
        return (
            <li>
                <button
                    type="button"
                    className={rowClass}
                    aria-expanded={open}
                    onClick={() => {
                        opened.current = !open;
                        setOpen(!open);
                    }}
                >
                    {icon}
                    <span className="flex-1 text-start">{item.label}</span>
                    <ChevronDown aria-hidden size={16} className={`text-rail-muted transition-transform duration-300 ${EASE} ${open ? 'rotate-180' : ''}`} />
                </button>
                {/* Slides open: the row grows from 0fr to 1fr, so no height is measured or animated. */}
                <div
                    data-closed={open ? undefined : ''}
                    inert={!open}
                    className={`grid transition-[grid-template-rows] duration-300 ${EASE} ${open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'}`}
                    onTransitionEnd={(e) => {
                        if (e.target !== e.currentTarget || !open || !opened.current) return;
                        opened.current = false;
                        e.currentTarget.parentElement?.scrollIntoView({ block: 'nearest', behavior: reducedMotion() ? 'auto' : 'smooth' });
                    }}
                >
                    <ul className={`min-h-0 space-y-0.5 overflow-hidden ps-9 transition-opacity duration-200 ${open ? 'pt-0.5 pb-1 opacity-100' : 'opacity-0'}`}>
                        {item.children.map((child) => (
                            <MenuItem key={child.label + (child.to ?? child.href)} item={child} />
                        ))}
                    </ul>
                </div>
            </li>
        );
    }

    if (item.to) {
        // The highlight behind the active link is the rail's shared pill (see Sidebar), so it can glide.
        return (
            <li>
                <Link to={item.to} aria-current={active ? 'page' : undefined} className={`${rowClass} ${active ? 'font-medium !text-white' : ''}`}>
                    {icon}
                    {item.label}
                </Link>
            </li>
        );
    }

    return (
        <li>
            <a href={item.href} className={rowClass}>
                {icon}
                {item.label}
            </a>
        </li>
    );
}

/**
 * "Orders" is active anywhere under /orders except while one service's filter is on;
 * a per-service link ("/orders?type=2&view=all") is active while that type filter is on.
 */
function isActive(to: string, pathname: string, search: string) {
    const [path, query] = to.split('?');
    if (path === '/') return pathname === '/';
    if (!pathname.startsWith(path)) return false;
    const currentType = new URLSearchParams(search).get('type');
    const linkType = query ? new URLSearchParams(query).get('type') : null;
    return linkType ? currentType === linkType : !currentType || pathname !== path;
}

type Pill = { x: number; y: number; w: number; h: number; shown: boolean; glide: boolean };

/*
 * One highlight for the whole rail, placed under the active link. Navigating glides it to the new item; while a
 * group slides open or shut it follows the link exactly (no lag), and it fades out when its link is folded away.
 */
function useActivePill(content: RefObject<HTMLDivElement | null>) {
    const location = useLocation();
    const [pill, setPill] = useState<Pill | null>(null);

    const place = useCallback(
        (glide: boolean) => {
            const el = content.current?.querySelector<HTMLElement>('[aria-current="page"]');
            setPill((prev) => {
                if (el && !el.offsetWidth) return prev; // this rail is hidden (phone drawer closed, or desktop rail on a phone)
                if (!el || el.closest('[data-closed]')) return prev && { ...prev, shown: false, glide: true };
                return { x: el.offsetLeft, y: el.offsetTop, w: el.offsetWidth, h: el.offsetHeight, shown: true, glide: glide && !!prev?.shown };
            });
        },
        [content],
    );

    useLayoutEffect(() => place(true), [location.pathname, location.search, place]);

    useEffect(() => {
        const el = content.current;
        if (!el) return;
        const follow = new ResizeObserver(() => place(false));
        follow.observe(el);
        return () => follow.disconnect();
    }, [content, place]);

    return pill;
}

function Sidebar() {
    const { menu } = useBoot();
    const scroller = useRef<HTMLDivElement>(null);
    const content = useRef<HTMLDivElement>(null);
    const pill = useActivePill(content);

    // On load, bring the active item into the rail's view if the menu is long (only the rail scrolls).
    useLayoutEffect(() => {
        const box = scroller.current;
        const el = content.current?.querySelector<HTMLElement>('[aria-current="page"]');
        if (box && el && el.offsetTop + el.offsetHeight > box.clientHeight - 24) box.scrollTop = el.offsetTop - box.clientHeight / 3;
    }, []);

    return (
        <nav aria-label="منوی اصلی" className="flex h-full flex-col border-e border-white/10 bg-rail">
            <div className="flex h-14 shrink-0 items-center gap-2.5 px-5">
                <span aria-hidden className="grid size-7 place-items-center rounded-lg bg-primary text-[13px] font-bold text-primary-ink">
                    ش
                </span>
                <span className="font-bold text-white">مدیریت شاپ مجازی</span>
            </div>
            <div ref={scroller} className="rail-scroll flex-1 overflow-y-auto overscroll-contain px-3 pb-6">
                <div ref={content} className="relative">
                    {pill && (
                        <span
                            aria-hidden
                            className={`pointer-events-none absolute top-0 left-0 rounded-lg bg-rail-active ${pill.glide ? `transition-[translate,width,height,opacity] duration-300 ${EASE}` : 'transition-opacity duration-200'} ${pill.shown ? 'opacity-100' : 'opacity-0'}`}
                            style={{ translate: `${pill.x}px ${pill.y}px`, width: pill.w, height: pill.h }}
                        />
                    )}
                    <div className="relative">
                        {menu.map((section, i) => (
                            <div key={section.title ?? i} className="mt-4 first:mt-1">
                                {section.title && <h2 className="px-3 pb-1.5 text-[12px] font-medium text-rail-muted">{section.title}</h2>}
                                <ul className="space-y-0.5">
                                    {section.items.map((item) => (
                                        <MenuItem key={item.label} item={item} />
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </nav>
    );
}

export default function Shell() {
    const { admin, links } = useBoot();
    const drawer = useRef<HTMLDialogElement>(null);
    const location = useLocation();

    // Close the phone drawer after navigating.
    useEffect(() => {
        drawer.current?.close();
    }, [location.pathname, location.search]);

    return (
        <div className="min-h-dvh lg:grid lg:grid-cols-[260px_1fr]">
            <aside className="sticky top-0 hidden h-dvh lg:block">
                <Sidebar />
            </aside>

            <dialog
                ref={drawer}
                aria-label="منوی اصلی"
                className="rail-drawer m-0 h-dvh max-h-none w-[280px] max-w-[85vw] bg-transparent p-0 lg:hidden"
                style={{ insetInlineStart: 0, insetInlineEnd: 'auto' }}
                onClick={(e) => e.target === drawer.current && drawer.current.close()}
            >
                <div className="relative h-full">
                    <Sidebar />
                    <button
                        type="button"
                        className="absolute top-3 end-3 grid size-9 place-items-center rounded-lg text-rail-ink hover:bg-rail-hover"
                        onClick={() => drawer.current?.close()}
                        aria-label="بستن منو"
                    >
                        <X aria-hidden size={18} />
                    </button>
                </div>
            </dialog>

            <div className="flex min-w-0 flex-col">
                <header className="sticky top-0 z-10 flex h-14 items-center gap-2 border-b border-stroke bg-surface/90 px-4 backdrop-blur sm:px-6">
                    <button
                        type="button"
                        className="btn btn-ghost -ms-2 lg:hidden"
                        onClick={() => drawer.current?.showModal()}
                        aria-label="باز کردن منو"
                    >
                        <Menu aria-hidden size={20} />
                    </button>
                    <div className="flex-1" />
                    <a href={links.legacyHome} className="btn btn-ghost hidden text-[13px] font-medium sm:inline-flex">
                        پنل قدیمی
                    </a>
                    <span className="hidden h-5 w-px bg-stroke sm:block" aria-hidden />
                    <span className="px-2 text-[13px] text-body">{admin.name}</span>
                    <a href={links.logout} className="btn btn-ghost" aria-label="خروج" title="خروج">
                        <LogOut aria-hidden size={18} className="rtl:-scale-x-100" />
                    </a>
                </header>
                <main className="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
