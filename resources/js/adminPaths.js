/**
 * Admin panel paths — always relative to the current host.
 * Avoid Ziggy absolute URLs pointing at APP_URL (127.0.0.1).
 */
export const adminPaths = {
    dashboard: '/admin/dashboard',
    login: '/admin/login',
    logout: '/admin/logout',
    tenants: '/admin/tenants',
    tenantsSearchApi: '/admin/tenants/search-api',
    tenantsCreate: '/admin/tenants/create',
    tenant: (id) => `/admin/tenants/${id}`,
    tenantEdit: (id) => `/admin/tenants/${id}/edit`,
    tenantExtend: (id) => `/admin/tenants/${id}/extend`,
    tenantToggleStatus: (id) => `/admin/tenants/${id}/toggle-status`,
    tenantPlan: (id) => `/admin/tenants/${id}/plan`,
    tenantsBulkAutoRenew: '/admin/tenants/bulk-auto-renew',
    admins: '/admin/admins',
    adminsCreate: '/admin/admins/create',
    adminEdit: (id) => `/admin/admins/${id}/edit`,
    adminUpdate: (id) => `/admin/admins/${id}`,
    invoices: '/admin/invoices',
    invoice: (id) => `/admin/invoices/${id}`,
    invoiceStatus: (id) => `/admin/invoices/${id}/status`,
};

export function isAdminPathActive(prefixes) {
    const path = window.location.pathname.replace(/\/$/, '') || '/';
    return prefixes.some((prefix) => {
        const normalized = prefix.replace(/\/$/, '') || '/';
        return path === normalized || path.startsWith(`${normalized}/`);
    });
}
