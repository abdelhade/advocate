/**
 * Ziggy / Laravel keep the FIRST registration for each route name.
 * Align absolute URL generation to the host the user is actually on so
 * Links (especially tenant `dashboard`) do not jump to 127.0.0.1 / another base domain.
 */
export function alignZiggyToCurrentHost() {
    if (typeof window === 'undefined' || !window.Ziggy) {
        return;
    }

    const host = window.location.hostname.toLowerCase();
    const origin = window.location.origin;

    window.Ziggy.url = origin;
    window.Ziggy.port = window.location.port
        ? Number(window.location.port)
        : null;

    // e.g. office.localhost → localhost, office.jalsateg.com → jalsateg.com
    let currentBase = host;
    const parts = host.split('.');
    if (parts.length >= 2) {
        currentBase = parts.slice(1).join('.');
    }

    Object.values(window.Ziggy.routes || {}).forEach((routeDef) => {
        if (!routeDef.domain) {
            return;
        }

        const domain = String(routeDef.domain);

        // Tenant pattern: rewrite base so {tenant_slug}.jalsateg.com works on *.localhost
        if (domain.includes('{tenant_slug}.')) {
            routeDef.domain = `{tenant_slug}.${currentBase}`;
            return;
        }

        // Fixed central domains — drop so relative links stay on current origin
        delete routeDef.domain;
    });
}
