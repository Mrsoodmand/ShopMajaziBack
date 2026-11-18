import { createContext, useCallback, useContext, useEffect, useLayoutEffect, useRef, useState, type RefObject } from 'react';
import { Link, Outlet, useLocation } from 'react-router';
import { AppWindow, ArrowLeftRight, CalendarDays, ChevronDown, ExternalLink, Monitor, UserRound, Moon, Sun, FileText, Globe, Handshake, House, Landmark, LogOut, Menu, MessageSquareText, Mic, Newspaper, PanelsTopLeft, Phone, ReceiptText, Images, Scale, SearchCheck, ShieldCheck, Smartphone, Sparkles, Ticket, UserCog, Users, X, type LucideIcon, BadgePercent, DatabaseZap, Bot } from 'lucide-react';
import { useBoot, type MenuLink } from '../lib/boot';
import { useTheme, type ThemeChoice } from '../lib/theme';

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
    pricing: BadgePercent,
    cache: DatabaseZap,
    captcha: Bot,
};

// One easing for the rail: quick to start, soft to land.
const EASE = 'ease-[cubic-bezier(0.16,1,0.3,1)]';

const rowClass =
    'group flex w-full items-center gap-3 rounded-lg px-3 py-1.5 text-[13.5px] text-rail-ink transition-colors duration-150 hover:bg-rail-hover hover:text-rail-strong focus-visible:outline-offset-0';
const iconClass = 'shrink-0 text-rail-muted transition-colors duration-150 group-hover:text-rail-strong';

const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

// Which sidebar group is open (by label). Only one at a time: opening a group closes the one that was open.
const OpenGroup = createContext<{ open: string | null; setOpen: (label: string | null) => void }>({ open: null, setOpen: () => {} });

function MenuItem({ item }: { item: MenuLink }) {
    const location = useLocation();
    const Icon = item.icon ? icons[item.icon] : undefined;
    const childActive = !!item.children?.some((c) => c.to && isActive(c.to, location.pathname, location.search));
    const active = !!item.to && isActive(item.to, location.pathname, location.search);
    const icon = Icon ? <Icon aria-hidden size={18} strokeWidth={1.75} className={`${iconClass} ${active ? '!text-rail-active-ink' : ''}`} /> : null;
    const group = useContext(OpenGroup);
    const open = group.open === item.label;
    const setOpen = (on: boolean) => group.setOpen(on ? item.label : null);
    const opened = useRef(false); // opened by a click (not by navigating), so reveal it once it has slid open

    // Arriving on one of its pages (on load, from a link in a page, back/forward) opens the group, closing the others.
    useEffect(() => {
        if (childActive) group.setOpen(item.label);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [childActive]);

    if (item.children) {
        return (
            <li>
                <button
                    type="button"
                    className={`${rowClass} ${childActive ? 'font-semibold !text-rail-strong' : ''}`}
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
                {/*
                  * Slides open: the row grows from 0fr to 1fr, so no height is measured. The padding lives inside the
                  * clipped box, so nothing jumps at the start or end; the links fade in just behind the slide.
                  */}
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
                    <div className="min-h-0 overflow-hidden">
                        <ul className={`ms-[21px] my-1 space-y-0.5 border-s border-stroke ps-3 transition-opacity ${EASE} ${open ? 'opacity-100 delay-75 duration-300' : 'opacity-0 duration-150'}`}>
                            {item.children.map((child) => (
                                <MenuItem key={child.label + (child.to ?? child.href)} item={child} />
                            ))}
                        </ul>
                    </div>
                </div>
            </li>
        );
    }

    if (item.to) {
        // The highlight behind the active link is the rail's shared pill (see Sidebar), so it can glide.
        return (
            <li>
                <Link to={item.to} aria-current={active ? 'page' : undefined} className={`${rowClass} ${item.icon ? '' : '!px-2.5 !text-[13px]'} ${active ? 'font-semibold !text-rail-active-ink hover:!bg-transparent' : ''}`}>
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

/*
 * One highlight for the whole rail, placed under the active link. Navigating glides it to the new item; while a
 * group slides open or shut it follows the link exactly, and it fades out when its link is folded away.
 * It is moved by writing styles directly, so following a sliding group never re-renders the menu.
 */
function useActivePill(content: RefObject<HTMLDivElement | null>, pill: RefObject<HTMLSpanElement | null>) {
    const location = useLocation();
    const shown = useRef(false);

    const place = useCallback(
        (glide: boolean) => {
            const box = pill.current;
            const el = content.current?.querySelector<HTMLElement>('[aria-current="page"]');
            if (!box) return;
            if (el && !el.offsetWidth) return; // this rail is hidden (phone drawer closed, or desktop rail on a phone)
            if (!el || el.closest('[data-closed]')) {
                box.toggleAttribute('data-glide', true);
                box.style.opacity = '0';
                shown.current = false;
                return;
            }
            box.toggleAttribute('data-glide', glide && shown.current);
            box.style.translate = `${el.offsetLeft}px ${el.offsetTop}px`;
            box.style.width = `${el.offsetWidth}px`;
            box.style.height = `${el.offsetHeight}px`;
            box.style.opacity = '1';
            shown.current = true;
        },
        [content, pill],
    );

    useLayoutEffect(() => place(true), [location.pathname, location.search, place]);

    useEffect(() => {
        const el = content.current;
        if (!el) return;
        const follow = new ResizeObserver(() => place(false));
        follow.observe(el);
        return () => follow.disconnect();
    }, [content, place]);
}

/** With the scrollbar hidden, the rail's edges fade while there is more menu above or below. */
function useScrollFades(scroller: RefObject<HTMLDivElement | null>) {
    useEffect(() => {
        const box = scroller.current;
        if (!box) return;
        const update = () => {
            box.toggleAttribute('data-more-above', box.scrollTop > 2);
            box.toggleAttribute('data-more-below', box.scrollTop + box.clientHeight < box.scrollHeight - 2);
        };
        update();
        box.addEventListener('scroll', update, { passive: true });
        const watch = new ResizeObserver(update);
        watch.observe(box);
        if (box.firstElementChild) watch.observe(box.firstElementChild);
        return () => {
            box.removeEventListener('scroll', update);
            watch.disconnect();
        };
    }, [scroller]);
}

function Sidebar() {
    const { menu } = useBoot();
    const location = useLocation();
    // Start with the current page's group already open, so it doesn't slide open on load.
    const [openGroup, setOpenGroup] = useState<string | null>(
        () => menu.flatMap((s) => s.items).find((it) => it.children?.some((c) => c.to && isActive(c.to, location.pathname, location.search)))?.label ?? null,
    );
    const scroller = useRef<HTMLDivElement>(null);
    const content = useRef<HTMLDivElement>(null);
    const pill = useRef<HTMLSpanElement>(null);
    useActivePill(content, pill);
    useScrollFades(scroller);

    // On load, bring the active item into the rail's view if the menu is long (only the rail scrolls).
    useLayoutEffect(() => {
        const box = scroller.current;
        const el = content.current?.querySelector<HTMLElement>('[aria-current="page"]');
        if (box && el && el.offsetTop + el.offsetHeight > box.clientHeight - 24) box.scrollTop = el.offsetTop - box.clientHeight / 3;
    }, []);

    return (
        <nav aria-label="منوی اصلی" className="flex h-full flex-col border-e border-stroke bg-rail">
            <div className="flex h-16 shrink-0 items-center gap-2.5 border-b border-stroke px-5">
                <span aria-hidden className="grid size-8 place-items-center rounded-lg bg-primary text-sm font-black text-primary-ink">
                    ش
                </span>
                <span className="leading-tight">
                    <span className="block text-[15px] font-bold text-ink">شاپ مجازی</span>
                    <span className="block text-[11px] text-subtle">پنل مدیریت</span>
                </span>
            </div>
            <div ref={scroller} className="rail-scroll flex-1 overflow-y-auto overscroll-contain px-3 pt-3 pb-6">
                <div ref={content} className="relative">
                    <span ref={pill} aria-hidden className="rail-pill pointer-events-none absolute top-0 left-0 rounded-lg bg-rail-active opacity-0" />
                    <OpenGroup.Provider value={{ open: openGroup, setOpen: setOpenGroup }}>
                    <div className="relative">
                        {menu.map((section, i) => (
                            <div key={section.title ?? i} className="mt-3.5 first:mt-0">
                                {section.title && <h2 className="mb-1 px-3 text-[11px] font-semibold tracking-wide text-rail-muted">{section.title}</h2>}
                                <ul className="space-y-0.5">
                                    {section.items.map((item) => (
                                        <MenuItem key={item.label} item={item} />
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                    </OpenGroup.Provider>
                </div>
            </div>
        </nav>
    );
}

const TZ = 'Asia/Tehran';
const dayParts = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: TZ });
const shortDayFormat = new Intl.DateTimeFormat('fa-IR-u-ca-persian', { day: 'numeric', month: 'long', timeZone: TZ });
const timeFormat = new Intl.DateTimeFormat('fa-IR', { hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: TZ });
const gregorianFormat = new Intl.DateTimeFormat('en-GB', { dateStyle: 'full', timeZone: TZ });

/** «پنجشنبه، ۷ آبان ۱۴۰۵»: built from parts, since browsers join them with a Latin comma. */
function persianDay(date: Date) {
    const parts = dayParts.formatToParts(date);
    const get = (type: Intl.DateTimeFormatPartTypes) => parts.find((p) => p.type === type)?.value ?? '';
    return `${get('weekday')}، ${get('day')} ${get('month')} ${get('year')}`;
}

/** Today's Persian date and the time in Tehran; ticks over on each minute and when the tab comes back. */
function HeaderClock() {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        let timer = 0;
        const schedule = () => {
            timer = window.setTimeout(() => {
                setNow(new Date());
                schedule();
            }, 60_000 - (Date.now() % 60_000) + 50);
        };
        const onShow = () => {
            if (document.visibilityState !== 'visible') return;
            window.clearTimeout(timer);
            setNow(new Date());
            schedule();
        };
        schedule();
        document.addEventListener('visibilitychange', onShow);
        return () => {
            window.clearTimeout(timer);
            document.removeEventListener('visibilitychange', onShow);
        };
    }, []);

    return (
        <time
            dateTime={now.toISOString()}
            title={`${gregorianFormat.format(now)} · به وقت تهران`}
            className="flex items-center gap-2 text-[13px] whitespace-nowrap text-body"
        >
            <CalendarDays aria-hidden size={16} className="text-muted" />
            <span className="hidden md:inline">{persianDay(now)}</span>
            <span className="md:hidden">{shortDayFormat.format(now)}</span>
            <span aria-hidden className="h-4 w-px bg-stroke" />
            <span className="num font-bold text-ink">{timeFormat.format(now)}</span>
        </time>
    );
}

const themeOptions: { value: ThemeChoice; label: string; icon: LucideIcon }[] = [
    { value: 'light', label: 'روشن', icon: Sun },
    { value: 'dark', label: 'تیره', icon: Moon },
    { value: 'system', label: 'سیستم', icon: Monitor },
];

function ThemeSwitch() {
    const { choice, setChoice } = useTheme();
    return (
        <div role="radiogroup" aria-label="حالت نمایش" className="flex items-center rounded-lg border border-stroke bg-surface-2 p-0.5">
            {themeOptions.map(({ value, label, icon: Icon }) => (
                <button
                    key={value}
                    type="button"
                    role="radio"
                    aria-checked={choice === value}
                    aria-label={label}
                    title={label}
                    onClick={() => setChoice(value)}
                    className={`grid size-7 place-items-center rounded-md transition-colors ${choice === value ? 'bg-surface text-ink shadow-card' : 'text-subtle hover:text-ink'}`}
                >
                    <Icon aria-hidden size={16} />
                </button>
            ))}
        </div>
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
        <div className="min-h-dvh lg:grid lg:grid-cols-[256px_1fr]">
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
                        className="absolute top-3.5 end-2 grid size-9 place-items-center rounded-lg text-muted hover:bg-surface-2 hover:text-ink"
                        onClick={() => drawer.current?.close()}
                        aria-label="بستن منو"
                    >
                        <X aria-hidden size={18} />
                    </button>
                </div>
            </dialog>

            <div className="flex min-w-0 flex-col">
                <header className="sticky top-0 z-10 flex h-16 items-center gap-3 border-b border-stroke bg-surface/85 px-4 backdrop-blur sm:px-6">
                    <button
                        type="button"
                        className="btn btn-ghost -ms-2 lg:hidden"
                        onClick={() => drawer.current?.showModal()}
                        aria-label="باز کردن منو"
                    >
                        <Menu aria-hidden size={20} />
                    </button>
                    <HeaderClock />
                    <div className="flex-1" />
                    <a href={links.legacyHome} className="hidden items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[13px] text-muted hover:bg-surface-2 hover:text-ink sm:inline-flex">
                        <ExternalLink aria-hidden size={16} />
                        پنل قدیمی
                    </a>
                    <ThemeSwitch />
                    <div className="flex items-center gap-1 border-s border-stroke ps-3">
                        <span className="flex items-center gap-2 px-1">
                            <span aria-hidden className="grid size-8 shrink-0 place-items-center rounded-full bg-primary-soft text-[13px] font-bold text-indigo">
                                {admin.name?.trim().charAt(0) || <UserRound size={16} />}
                            </span>
                            <span className="hidden text-[13px] font-semibold text-ink sm:block">{admin.name}</span>
                        </span>
                        <a href={links.logout} className="grid size-9 place-items-center rounded-lg text-muted hover:bg-surface-2 hover:text-ink" aria-label="خروج" title="خروج">
                            <LogOut aria-hidden size={18} className="rtl:-scale-x-100" />
                        </a>
                    </div>
                </header>
                <main className="mx-auto w-full max-w-[1400px] flex-1 px-3 py-4 sm:px-6 sm:py-6 lg:py-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
