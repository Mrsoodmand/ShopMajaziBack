import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { createBrowserRouter, Link, Navigate, RouterProvider } from 'react-router';
import { BootContext, type AdminBoot } from './lib/boot';
import Shell from './components/Shell';
import Dashboard from './pages/dashboard/Dashboard';
import OrdersList from './pages/OrdersList';
import OrderDetail from './pages/OrderDetail';
import UsersList from './pages/UsersList';
import UserDetail from './pages/UserDetail';
import ServicesPage from './pages/services/ServicesPage';
import PlatformEditor from './pages/services/PlatformEditor';
import ServiceEditor from './pages/services/ServiceEditor';
import SeoPage from './pages/seo/SeoPage';
import MediaPage from './pages/media/MediaPage';
import TransactionsPage from './pages/transactions/TransactionsPage';
import CatalogList from './pages/virtual-numbers/CatalogList';
import CatalogEditor from './pages/virtual-numbers/CatalogEditor';
import VirtualNumberSettings from './pages/virtual-numbers/SettingsPage';
import StockList from './pages/stock/StockList';
import StockAdd from './pages/stock/StockAdd';
import StockItem from './pages/stock/StockItem';
import { APPLE, GOOGLE_VOICE } from './pages/stock/config';
import AccountsList from './pages/accounts/AccountsList';
import AccountEditor from './pages/accounts/AccountEditor';
import IpLocations from './pages/ip/IpLocations';
import IpLocationPage from './pages/ip/IpLocationPage';
import TicketsPage from './pages/tickets/TicketsPage';
import TicketDetail from './pages/tickets/TicketDetail';
import CommentsPage from './pages/comments/CommentsPage';
import PostsList from './pages/posts/PostsList';
import PostEditor from './pages/posts/PostEditor';
import PagesList from './pages/pages/PagesList';
import PageEditor from './pages/pages/PageEditor';
import RolesList from './pages/team/RolesList';
import RoleEditor from './pages/team/RoleEditor';
import AdminsList from './pages/team/AdminsList';
import AdminEditor from './pages/team/AdminEditor';
import PartnersList from './pages/partners/PartnersList';
import PartnerEditor from './pages/partners/PartnerEditor';
import BotUsers from './pages/bot/BotUsers';
import BotUserDetail from './pages/bot/BotUserDetail';
import BotOrders from './pages/bot/BotOrders';
import BotOrderDetail from './pages/bot/BotOrderDetail';
import BotMessages from './pages/bot/BotMessages';
import BotMessageEditor from './pages/bot/BotMessageEditor';
import { BOT_IP, IpScopeContext } from './pages/ip/scope';
import './styles.css';

function NotFound() {
    return (
        <div className="mx-auto max-w-lg py-20 text-center">
            <h1 className="text-[22px] font-bold">این صفحه در پنل جدید نیست</h1>
            <p className="mt-2 text-muted">بخش‌های دیگر از منوی کناری در پنل قدیمی باز می‌شوند.</p>
            <Link to="/orders" className="btn btn-secondary mt-6">
                رفتن به سفارش‌ها
            </Link>
        </div>
    );
}

const root = document.getElementById('admin-root');

if (root) {
    // Laravel renders the signed-in admin, menu and labels as JSON on the mount element.
    const boot = JSON.parse(root.dataset.boot ?? '{}') as AdminBoot;
    const router = createBrowserRouter(
        [
            {
                element: <Shell />,
                children: [
                    { index: true, element: <Dashboard /> },
                    { path: 'orders', element: <OrdersList /> },
                    { path: 'orders/:id', element: <OrderDetail /> },
                    { path: 'users', element: <UsersList /> },
                    { path: 'users/:id', element: <UserDetail /> },
                    { path: 'services', element: <ServicesPage /> },
                    { path: 'services/platforms/:id', element: <PlatformEditor /> },
                    { path: 'services/:id', element: <ServiceEditor /> },
                    { path: 'seo', element: <SeoPage /> },
                    { path: 'media', element: <MediaPage /> },
                    // One route for the list and an opened transaction, so the list stays mounted while the panel changes.
                    { path: 'transactions/:id?', element: <TransactionsPage /> },
                    // Virtual numbers: services (apps) and countries share one list and one editor.
                    { path: 'virtual-numbers', element: <Navigate to="/virtual-numbers/services" replace /> },
                    { path: 'virtual-numbers/services', element: <CatalogList key="services" kind="services" /> },
                    { path: 'virtual-numbers/services/:id', element: <CatalogEditor key="services" kind="services" /> },
                    { path: 'virtual-numbers/countries', element: <CatalogList key="countries" kind="countries" /> },
                    { path: 'virtual-numbers/countries/:id', element: <CatalogEditor key="countries" kind="countries" /> },
                    { path: 'virtual-numbers/settings', element: <VirtualNumberSettings /> },
                    // Apple ID and Google Voice: one inventory module, two kinds ("new" is the add screen).
                    { path: 'apple-ids', element: <StockList key="apple" kind={APPLE} /> },
                    { path: 'apple-ids/new', element: <StockAdd key="apple" kind={APPLE} /> },
                    { path: 'apple-ids/:id', element: <StockItem key="apple" kind={APPLE} /> },
                    { path: 'google-voice', element: <StockList key="gv" kind={GOOGLE_VOICE} /> },
                    { path: 'google-voice/new', element: <StockAdd key="gv" kind={GOOGLE_VOICE} /> },
                    { path: 'google-voice/:id', element: <StockItem key="gv" kind={GOOGLE_VOICE} /> },
                    { path: 'accounts', element: <AccountsList /> },
                    { path: 'accounts/:id', element: <AccountEditor /> },
                    { path: 'ip', element: <IpLocations /> },
                    { path: 'ip/:id', element: <IpLocationPage /> },
                    { path: 'tickets', element: <TicketsPage /> },
                    { path: 'tickets/:id', element: <TicketDetail /> },
                    { path: 'comments', element: <CommentsPage /> },
                    { path: 'blog', element: <PostsList key="blog" kind="blog" /> },
                    { path: 'blog/:id', element: <PostEditor key="blog" kind="blog" /> },
                    { path: 'news', element: <PostsList key="news" kind="news" /> },
                    { path: 'news/:id', element: <PostEditor key="news" kind="news" /> },
                    { path: 'pages', element: <PagesList /> },
                    { path: 'pages/:id', element: <PageEditor /> },
                    { path: 'roles', element: <RolesList /> },
                    { path: 'roles/:id', element: <RoleEditor key="role" /> },
                    { path: 'admins', element: <AdminsList /> },
                    { path: 'admins/:id', element: <AdminEditor /> },
                    { path: 'partners', element: <PartnersList /> },
                    { path: 'partners/:id', element: <PartnerEditor /> },
                    { path: 'bot/users', element: <BotUsers /> },
                    { path: 'bot/users/:id', element: <BotUserDetail /> },
                    { path: 'bot/orders', element: <BotOrders /> },
                    { path: 'bot/orders/:id', element: <BotOrderDetail /> },
                    { path: 'bot/messages', element: <BotMessages /> },
                    { path: 'bot/messages/:id', element: <BotMessageEditor /> },
                    { path: 'bot/ip', element: <IpScopeContext.Provider value={BOT_IP}><IpLocations key="bot" /></IpScopeContext.Provider> },
                    { path: 'bot/ip/:id', element: <IpScopeContext.Provider value={BOT_IP}><IpLocationPage key="bot" /></IpScopeContext.Provider> },
                    { path: '*', element: <NotFound /> },
                ],
            },
        ],
        { basename: boot.basePath },
    );

    createRoot(root).render(
        <StrictMode>
            <BootContext.Provider value={boot}>
                <RouterProvider router={router} />
            </BootContext.Provider>
        </StrictMode>,
    );
}
