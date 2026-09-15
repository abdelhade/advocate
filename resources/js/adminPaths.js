/**
 * Admin panel paths — always relative to the current host.
 * Avoid Ziggy absolute URLs pointing at APP_URL (127.0.0.1).
 */
export const adminPaths = {
    dashboard: '/admin/dashboard',
    login: '/admin/login',
    logout: '/admin/logout',
    tenants: '/admin/tenants',
    tenantsCreate: '/admin/tenants/create',
    tenant: (id) => `/admin/tenants/${id}`,
    tenantEdit: (id) => `/admin/tenants/${id}/edit`,
    tenantExtend: (id) => `/admin/tenants/${id}/extend`,
    tenantToggleStatus: (id) => `/admin/tenants/${id}/toggle-status`,
    tenantPlan: (id) => `/admin/tenants/${id}/plan`,
    admins: '/admin/admins',
    adminsCreate: '/admin/admins/create',
    adminEdit: (id) => `/admin/admins/${id}/edit`,
    adminUpdate: (id) => `/admin/admins/${id}`,
};

export function isAdminPathActive(prefixes) {
    const path = window.location.pathname.replace(/\/$/, '') || '/';
    return prefixes.some((prefix) => {
        const normalized = prefix.replace(/\/$/, '') || '/';
        return path === normalized || path.startsWith(`${normalized}/`);
    });
}
