/**
 * Ziggy registers the same named routes once per central domain.
 * The last registration wins — often 127.0.0.1 / localhost from APP_URL —
 * so Links navigate off-site and appear "dead". Align to the current host.
 */
export function alignZiggyToCurrentHost() {
    if (typeof window === 'undefined' || !window.Ziggy) {
        return;
    }

    window.Ziggy.url = window.location.origin;
    window.Ziggy.port = window.location.port
        ? Number(window.location.port)
        : null;

    Object.values(window.Ziggy.routes || {}).forEach((routeDef) => {
        // Keep tenant pattern domains: {tenant_slug}.jalsateg.com
        if (routeDef.domain && !String(routeDef.domain).includes('{')) {
            delete routeDef.domain;
        }
    });
}
