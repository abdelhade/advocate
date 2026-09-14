<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantUrl
{
    /**
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        return ['www', 'admin', 'api', 'mail', 'app', 'static', 'cdn', 'assets'];
    }

    public static function baseDomain(): string
    {
        $configured = config('tenancy.tenant_base_domain');

        if (is_string($configured) && $configured !== '') {
            return strtolower($configured);
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        return strtolower(preg_replace('/^www\./i', '', $host) ?: 'localhost');
    }

    /**
     * @return list<string>
     */
    public static function availableDomains(): array
    {
        $domains = config('tenancy.available_domains', []);

        if (! is_array($domains) || $domains === []) {
            return [self::baseDomain()];
        }

        return array_values(array_unique(array_map('strtolower', $domains)));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function availableDomainOptions(): array
    {
        $labels = config('tenancy.domain_labels', []);

        return array_map(static function (string $domain) use ($labels) {
            return [
                'value' => $domain,
                'label' => $labels[$domain] ?? $domain,
            ];
        }, self::availableDomains());
    }

    public static function isAllowedDomain(string $domain): bool
    {
        return in_array(strtolower($domain), self::availableDomains(), true);
    }

    /**
     * @return list<string>
     */
    public static function centralDomains(): array
    {
        return array_values(array_filter(config('tenancy.central_domains', [])));
    }

    public static function isCentralHost(?string $host = null): bool
    {
        $host = $host ?? request()->getHost();

        return in_array($host, self::centralDomains(), true);
    }

    public static function slugFromHost(?string $host = null): ?string
    {
        $host = strtolower($host ?? request()->getHost());

        if (self::isCentralHost($host)) {
            return null;
        }

        foreach (self::availableDomains() as $base) {
            $suffix = '.'.$base;
            if (! str_ends_with($host, $suffix)) {
                continue;
            }

            $slug = substr($host, 0, -strlen($suffix));

            if ($slug === '' || str_contains($slug, '.')) {
                return null;
            }

            return $slug;
        }

        return null;
    }

    public static function domainFromHost(?string $host = null): ?string
    {
        $host = strtolower($host ?? request()->getHost());

        foreach (self::availableDomains() as $base) {
            if ($host === $base || str_ends_with($host, '.'.$base)) {
                return $base;
            }
        }

        return null;
    }

    public static function isReservedSlug(string $slug): bool
    {
        return in_array(strtolower($slug), self::reservedSlugs(), true);
    }

    public static function domainFor(Tenant|string $tenant): string
    {
        // Local/testing: always use the configured base domain so artisan serve works.
        if (app()->environment(['local', 'testing'])) {
            return self::baseDomain();
        }

        if ($tenant instanceof Tenant && filled($tenant->domain)) {
            return strtolower($tenant->domain);
        }

        return self::baseDomain();
    }

    public static function for(Tenant|string $tenant, string $path = '/dashboard', ?Request $request = null): string
    {
        $slug = $tenant instanceof Tenant ? $tenant->slug : $tenant;
        $domain = self::domainFor($tenant);
        $request = $request ?? request();
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portStr = ($port && ! in_array((int) $port, [80, 443], true)) ? ':'.$port : '';
        $path = '/'.ltrim($path, '/');

        return "{$scheme}://{$slug}.{$domain}{$portStr}{$path}";
    }

    public static function loginUrl(?Request $request = null): string
    {
        $request = $request ?? request();
        $slug = $request->route('tenant_slug') ?: self::slugFromHost($request->getHost());

        if ($slug) {
            return route('login', ['tenant_slug' => $slug]);
        }

        return route('central.login');
    }

    public static function central(string $path = '/', ?Request $request = null): string
    {
        $request = $request ?? request();
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portStr = ($port && ! in_array((int) $port, [80, 443], true)) ? ':'.$port : '';
        $path = '/'.ltrim($path, '/');
        if ($path === '/') {
            $path = '';
        }

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: self::baseDomain();

        return "{$scheme}://{$host}{$portStr}{$path}";
    }
}
