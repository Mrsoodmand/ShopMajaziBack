<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * The 3x-ui (Sanaei) panel on each static-IP server: log in, then add VLESS clients to the server's inbound.
 * Wraps the old helpers (loginToSanaeiAccount, addClientToSanaie) so the admin API can be tested; with
 * DEV_FAKE_PROVIDERS on (local and tests) nothing leaves the machine and made-up clients come back.
 */
class IpServerPanel
{
    /** @param array $server the server's `ips.detail` (ip_api_domain, ip_port, ip_user, ip_password, …) */
    public function login(array $server): ?string
    {
        if (DevFakeProviders()) {
            Log::info('[dev] 3x-ui login to ' . ($server['ip_api_domain'] ?? '?'));
            return 'dev-session';
        }
        try {
            $result = loginToSanaeiAccount($server);
        } catch (\Throwable $e) {
            Log::warning('3x-ui login failed: ' . $e->getMessage());
            return null;
        }
        return !empty($result['login']) && !empty($result['sessionCookie']) ? (string) $result['sessionCookie'] : null;
    }

    /** @return array{code: string, uid: string}|null the client's config link, or null when the panel refused */
    public function addClient(array $server, string $session, string $name): ?array
    {
        if (DevFakeProviders()) {
            $uid = (string) \Illuminate\Support\Str::uuid();
            $host = $server['ip_address'] ?? 'dev.local';
            $port = $server['port_inbound'] ?? 443;
            return ['code' => "vless://$uid@$host:$port?type=tcp&security=tls#ShopMajazi-$name", 'uid' => $uid];
        }
        try {
            $result = addClientToSanaie([
                'Session' => $session,
                'api_domain' => $server['ip_api_domain'] ?? '',
                'ip_address' => $server['ip_address'] ?? '',
                'ip_port' => $server['ip_port'] ?? '',
                'ip_inbound' => $server['ip_inbound'] ?? '',
                'port_inbound' => $server['port_inbound'] ?? '',
                'name' => $name,
            ]);
        } catch (\Throwable $e) {
            Log::warning('3x-ui addClient failed: ' . $e->getMessage());
            return null;
        }
        return ($result['status'] ?? 0) == 1 ? ['code' => (string) $result['msg'], 'uid' => (string) $result['UID']] : null;
    }
}
