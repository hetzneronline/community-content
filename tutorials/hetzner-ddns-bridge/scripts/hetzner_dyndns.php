<?php
/*
 * Hetzner DNS Dynamic DNS Script (enhanced)
 *
 * Maintains per-realm configuration in a separate file, retries failed API calls,
 * and supports both the legacy DNS API and the newer Hetzner Console API so updates
 * are never silently dropped.
 *
 * `.htaccess` rewrites `/nic/update` and `/v3/update` into this file while blocking
 * direct requests, so the DynDNS-style paths remain viable long term.
 *
 * The configuration lives in hetzner_dyndns.config.php next to this script. Cron
 * retries can be triggered via `php hetzner_dyndns.php --cron` or the HTTP
 * `?action=cron` endpoint, which keeps history rows flagged when API calls fail.
 */

$config = load_config(__DIR__ . '/hetzner_dyndns.config.php');
$debug = $config['debug'] ?? false;

if (!class_exists('SQLite3')) {
    http_response_code(500);
    exit('SQLite3 support is not available in this PHP installation.');
}

$db = new DDnsDB($config);
$cronContext = get_cron_context();

if ($cronContext['is_cron']) {
    authenticate($config);
    $summary = process_pending_updates($db, $config, $cronContext['realm']);
    echo render_cron_summary($summary);
    exit;
}

authenticate($config);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit('Method Not Allowed');
}

$realmKey = trim($_GET['realm'] ?? get_default_realm_key($config));
if ($realmKey === '' || !isset($config['realms'][$realmKey])) {
    header('HTTP/1.0 400 Bad Request');
    exit('Unknown realm');
}

$realmConfig = get_realm_config($config, $realmKey);
$hostname = trim($_GET['hostname'] ?? '');
if ($hostname === '') {
    header('HTTP/1.0 400 Bad Request');
    exit('nohost');
}

if (!valid_hostname($hostname)) {
    exit('Invalid domain name');
}

$ipSource = $_GET['myip'] ?? resolve_client_ip();
$ips = parse_ip_list($ipSource);
if (!$ips['ipv4']) {
    exit('No valid IPv4 address provided');
}

$split = split_hostname($hostname);
$hostnameName = $split[0];
$domain = $split[1];
$zoneLookupName = get_zone_lookup_name($realmConfig, $domain);
$overriddenHostnameName = derive_hostname_name_from_zone($hostname, $zoneLookupName);
if ($overriddenHostnameName !== null) {
    $hostnameName = $overriddenHostnameName === '' ? '@' : $overriddenHostnameName;
}
$historyRow = fetch_history_row($db, $hostname);
if ($historyRow && $historyRow['realm'] !== $realmKey) {
    $historyRow = null;
}

if (should_skip_update($historyRow, $ips)) {
    $storedIpv4 = $historyRow && isset($historyRow['ip']) ? $historyRow['ip'] : 'n/a';
    $storedIpv6 = $historyRow && isset($historyRow['ip6']) ? $historyRow['ip6'] : 'n/a';
    log_debug(sprintf(
        'Skipping update for %s; stored IPv4=%s IPv6=%s match incoming values',
        $hostname,
        $storedIpv4,
        $storedIpv6
    ));
    echo 'good ' . $ips['ipv4'];
    exit;
}

$result = sync_host($db, $config, $realmKey, $realmConfig, $hostname, $domain, $zoneLookupName, $hostnameName, $ips, $historyRow);
send_notification($config['notifications'] ?? [], $realmKey, $hostname, $ips, $result);
if ($result['success']) {
    echo 'good ' . $ips['ipv4'];
    exit;
}

http_response_code(503);
echo $result['message'];
exit;

/* Helpers */
/**
 * Load and validate the shared configuration, ensuring realms are defined.
 */
function load_config(string $path): array
{
    if (!file_exists($path)) {
        throw new RuntimeException('Configuration file not found: ' . $path);
    }

    $config = require $path;
    if (!is_array($config)) {
        throw new RuntimeException('Configuration must return an array.');
    }

    if (empty($config['realms']) || !is_array($config['realms'])) {
        throw new RuntimeException('At least one realm must be configured.');
    }

    return $config;
}

function get_default_realm_key(array $config): ?string
{
    if (!empty($config['default_realm']) && isset($config['realms'][$config['default_realm']])) {
        return $config['default_realm'];
    }

    foreach ($config['realms'] as $key => $_) {
        return $key;
    }

    return null;
}

function get_realm_config(array $config, string $realmKey): array
{
    $realm = $config['realms'][$realmKey] ?? null;
    if (!$realm) {
        throw new RuntimeException('Realm not defined: ' . $realmKey);
    }

    $realm['dns_endpoint'] = rtrim($realm['dns_endpoint'] ?? 'https://dns.hetzner.com/api/v1', '/');
    $realm['console_endpoint'] = rtrim($realm['console_endpoint'] ?? 'https://api.hetzner.cloud/v1', '/');
    $realm['ttl'] = $realm['ttl'] ?? 60;
    $realm['api_order'] = array_values(array_filter($realm['api_order'] ?? ['dns', 'console']));
    $realm['zone_name'] = trim($realm['zone_name'] ?? '');

    return $realm;
}

/**
 * Determine if we were invoked via CLI/cron or an HTTP cron flag plus optional realm filter.
 */
function get_cron_context(): array
{
    $context = ['is_cron' => false, 'realm' => null];
    if (PHP_SAPI === 'cli') {
        $options = getopt('', ['cron', 'realm:']);
        if (isset($options['cron'])) {
            $context['is_cron'] = true;
            $context['realm'] = $options['realm'] ?? null;
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'cron') {
        $context['is_cron'] = true;
        $context['realm'] = $_GET['realm'] ?? null;
    }

    return $context;
}

/**
 * Guard both HTTP and CLI calls with the configured script password(s).
 */
function authenticate(array $config): void
{
    $realmLabel = $config['auth_realm'] ?? 'My Dynamic DNS service';
    $passwords = (array) $config['script_password'];
    $passwords = array_map('strval', $passwords);
    $authValue = $_SERVER['HTTP_X_AUTHENTICATION'] ?? null;

    if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
        $digest = $_SERVER['PHP_AUTH_DIGEST'];
        $expected = md5('update:' . $passwords[0]);
        if (strpos($digest, 'username="update"') === false || strpos($digest, 'response="' . $expected . '"') === false) {
            send_auth_headers($realmLabel);
        }
        return;
    }

    if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
        foreach ($passwords as $password) {
            if ($_SERVER['PHP_AUTH_USER'] === 'update' && $_SERVER['PHP_AUTH_PW'] === $password) {
                return;
            }
        }
        send_auth_headers($realmLabel);
    }

    if ($authValue !== null && in_array($authValue, $passwords, true)) {
        return;
    }

    if (isset($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Basic ') === 0) {
        [$user, $pass] = array_pad(explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)), 2), 2, '');
        foreach ($passwords as $password) {
            if ($user === 'update' && $pass === $password) {
                return;
            }
        }
    }

    if (isset($_GET['p']) && in_array($_GET['p'], $passwords, true)) {
        return;
    }

    send_auth_headers($realmLabel);
}

function send_auth_headers(string $realm): void
{
    header('HTTP/1.0 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="' . $realm . '"');
    exit('Unauthorized');
}

function resolve_client_ip(): ?string
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return null;
}

function parse_ip_list(?string $ip): array
{
    $result = ['ipv4' => null, 'ipv6' => null];
    if ($ip === null) {
        return $result;
    }
    $ip = trim($ip);
    if ($ip === '') {
        return $result;
    }

    $parts = array_map('trim', explode(',', $ip));
    foreach ($parts as $candidate) {
        if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $result['ipv4'] = $candidate;
            continue;
        }
        if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $result['ipv6'] = $candidate;
        }
    }

    return $result;
}

function valid_hostname(string $hostname): bool
{
    return (bool) preg_match('/^([a-z0-9](-*[a-z0-9])*)+(\.[a-z]{2,})+$/i', $hostname);
}

function split_hostname(string $hostname): array
{
    $parts = explode('.', $hostname);
    if (count($parts) < 2) {
        throw new RuntimeException('Invalid hostname format');
    }

    $domain = implode('.', array_slice($parts, -2));
    $hostnameName = implode('.', array_slice($parts, 0, -2));

    return [$hostnameName, $domain];
}

function get_zone_lookup_name(array $realmConfig, string $derivedDomain): string
{
    $zone = $realmConfig['zone_name'];
    if ($zone !== '') {
        log_debug(sprintf('Using configured zone_name "%s" instead of derived "%s".', $zone, $derivedDomain));
        return $zone;
    }
    log_debug(sprintf('No override provided, deriving zone as "%s".', $derivedDomain));
    return $derivedDomain;
}

function derive_hostname_name_from_zone(string $hostname, string $zoneName): ?string
{
    $hostname = rtrim($hostname, '.');
    $zoneName = rtrim($zoneName, '.');
    if ($zoneName === '' || strlen($zoneName) > strlen($hostname)) {
        return null;
    }
    if ($hostname === $zoneName) {
        return '@';
    }
    $suffix = '.' . $zoneName;
    if (strlen($suffix) >= strlen($hostname)) {
        return null;
    }
    if (substr($hostname, -strlen($suffix)) === $suffix) {
        return substr($hostname, 0, strlen($hostname) - strlen($suffix));
    }
    return null;
}

function log_debug(string $message): void
{
    global $debug;
    global $config;
    if (empty($debug)) {
        return;
    }

    $output = '[Hetzner DDNS] ' . $message . PHP_EOL;
    if (!empty($config['debug_log'])) {
        @file_put_contents($config['debug_log'], $output, FILE_APPEND | LOCK_EX);
        return;
    }

    error_log(trim($output));
}

function fetch_history_row(SQLite3 $db, string $hostname): ?array
{
    $stmt = $db->prepare('SELECT * FROM history WHERE hostname = :hostname');
    $stmt->bindValue(':hostname', $hostname, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    if ($row === false) {
        return null;
    }

    // Convert empty strings back to null so logic that expects "missing" IDs works consistently.
    foreach (['zone_id', 'recordA_id', 'recordAAAA_id'] as $key) {
        if (isset($row[$key]) && $row[$key] === '') {
            $row[$key] = null;
        }
    }

    return $row;
}

function should_skip_update(?array $row, array $ips): bool
{
    if (!$row) {
        return false;
    }
    if (!empty($row['needs_sync'])) {
        return false;
    }
    if ($row['ip'] !== $ips['ipv4']) {
        return false;
    }
    if ($ips['ipv6'] !== null && $row['ip6'] !== $ips['ipv6']) {
        return false;
    }

    return true;
}

/**
 * Coordinate the update attempt, record the results, and mark rows for cron retries when needed.
 */
function sync_host(SQLite3 $db, array $config, string $realmKey, array $realmConfig, string $hostname, string $domain, string $zoneName, string $hostnameName, array $ips, ?array $historyRow): array
{
    // Attempt to update DNS records by iterating over all configured APIs until one succeeds.
    $attempt = try_update($realmConfig, $domain, $zoneName, $hostnameName, $ips, $historyRow);
    $needsSync = !$attempt['success'];
    $retryCount = $needsSync ? min(($historyRow['retry_count'] ?? 0) + 1, $config['max_retry_attempts'] ?? 5) : 0;
    $pendingSince = $needsSync ? ($historyRow['pending_since'] ?? time()) : null;
    upsert_history(
        $db,
        $hostname,
        $realmKey,
        $ips,
        $attempt['zone_id'],
        $attempt['recordA_id'],
        $attempt['recordAAAA_id'],
        $needsSync,
        $retryCount,
        $attempt['message'],
        $pendingSince
    );

    return $attempt;
}

/**
 * Attempt each configured API in order, carrying over the latest zone/record IDs.
 */
function try_update(array $realmConfig, string $domain, string $zoneName, string $hostnameName, array $ips, ?array $historyRow): array
{
    $zoneId = $historyRow['zone_id'] ?? null;
    $recordAId = $historyRow['recordA_id'] ?? null;
    $recordAAAAId = $historyRow['recordAAAA_id'] ?? null;
    $errors = [];

    foreach ($realmConfig['api_order'] as $api) {
        if ($api === 'dns') {
            if (empty($realmConfig['dns_token'])) {
                continue;
            }
            $response = update_via_dns_api($realmConfig, $domain, $zoneName, $hostnameName, $ips, $zoneId, $recordAId, $recordAAAAId);
        } elseif ($api === 'console') {
            if (empty($realmConfig['console_token'])) {
                continue;
            }
            $response = update_via_console_api($realmConfig, $zoneName, $hostnameName, $ips, null);
        } else {
            continue;
        }

        $zoneId = $response['zone_id'] ?? $zoneId;
        $recordAId = $response['recordA_id'] ?? $recordAId;
        $recordAAAAId = $response['recordAAAA_id'] ?? $recordAAAAId;

        if ($response['success']) {
            return array_merge($response, [
                'zone_id' => $zoneId,
                'recordA_id' => $recordAId,
                'recordAAAA_id' => $recordAAAAId,
                'api_used' => $api,
            ]);
        }

        $errors[] = sprintf('%s API error: %s', strtoupper($api), $response['message']);
    }

    return [
        'success' => false,
        'message' => implode(' | ', $errors) ?: 'No API configured',
        'zone_id' => $zoneId,
        'recordA_id' => $recordAId,
        'recordAAAA_id' => $recordAAAAId,
    ];
}

/**
 * Update the legacy Hetzner DNS Console zone/records, looking up record IDs when missing.
 */
function update_via_dns_api(array $realmConfig, string $domain, string $zoneName, string $hostnameName, array $ips, ?string $zoneId, ?string $recordAId, ?string $recordAAAAId): array
{
    $ttl = $realmConfig['ttl'];
    $endpoint = $realmConfig['dns_endpoint'];
    $token = $realmConfig['dns_token'];
    log_debug(sprintf('DNS API update prepared for %s (%s) in zone "%s" via %s', $hostnameName, $domain, $zoneName, $endpoint));

    if (!$zoneId) {
        $zoneId = fetch_zone_id($endpoint, $token, $zoneName);
        if (!$zoneId) {
            log_debug(sprintf('DNS API zone lookup failed for "%s" (derived "%s")', $zoneName, $domain));
            return ['success' => false, 'message' => 'Zone not found', 'zone_id' => null];
        }
        log_debug(sprintf('DNS API resolved zone "%s" to id %s', $zoneName, $zoneId));
    }

    if (!$recordAId || !$recordAAAAId) {
        $records = fetch_records($endpoint, $token, $zoneId, $hostnameName);
        $recordAId = $recordAId ?: $records['A'] ?? null;
        $recordAAAAId = $recordAAAAId ?: $records['AAAA'] ?? null;
    }

    if (!$recordAId) {
        return ['success' => false, 'message' => 'Missing A record ID', 'zone_id' => $zoneId];
    }

    $payload = [
        'value' => $ips['ipv4'],
        'ttl' => $ttl,
        'type' => 'A',
        'name' => $hostnameName,
        'zone_id' => $zoneId,
    ];

    $response = http_request('PUT', $endpoint . '/records/' . $recordAId, [
        'Content-Type: application/json',
        'Auth-API-Token: ' . $token,
    ], $payload);

        log_debug(sprintf('DNS API AAAA-record update returned %s', $response['success'] ? 'success' : 'failure'));
        if (!$response['success']) {
            return [
            'success' => false,
            'message' => $response['error'] ?? 'Failed to update A record',
            'zone_id' => $zoneId,
        ];
    }

    if ($ips['ipv6'] && $recordAAAAId) {
        $payload = [
            'value' => $ips['ipv6'],
            'ttl' => $ttl,
            'type' => 'AAAA',
            'name' => $hostnameName,
            'zone_id' => $zoneId,
        ];
        $response = http_request('PUT', $endpoint . '/records/' . $recordAAAAId, [
            'Content-Type: application/json',
            'Auth-API-Token: ' . $token,
        ], $payload);
        if (!$response['success']) {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'Failed to update AAAA record',
                'zone_id' => $zoneId,
                'recordA_id' => $recordAId,
            ];
        }
    }

    return [
        'success' => true,
        'message' => 'DNS record updated via classic API',
        'zone_id' => $zoneId,
        'recordA_id' => $recordAId,
        'recordAAAA_id' => $recordAAAAId,
    ];
}

/**
 * Attempt to update the Hetzner Console API by sending rrset updates for the given host.
 */
function update_via_console_api(array $realmConfig, string $zoneName, string $hostnameName, array $ips, ?string $zoneId): array
{
    $endpoint = $realmConfig['console_endpoint'];
    $token = $realmConfig['console_token'];
    $ttl = $realmConfig['ttl'];
    log_debug(sprintf('Console API update prepared for %s in zone "%s"', $hostnameName, $zoneName));

    if (!$zoneId) {
        $zoneId = fetch_console_zone_id($endpoint, $token, $zoneName);
        if (!$zoneId) {
            log_debug(sprintf('Console API zone lookup failed for "%s"', $zoneName));
            return ['success' => false, 'message' => 'Zone not found on console API'];
        }
        log_debug(sprintf('Console API resolved zone "%s" to id %s', $zoneName, $zoneId));
    }

    $ttl = $realmConfig['ttl'];
    $nameCandidates = build_rrset_candidates($hostnameName);
    $updates = [
        'A' => $ips['ipv4'],
        'AAAA' => $ips['ipv6'],
    ];
    $hadMissing = false;

    foreach ($updates as $type => $value) {
        if (empty($value)) {
            continue;
        }

        $existing = fetch_console_rrsets($endpoint, $token, $zoneId, $type);
        $chosen = choose_rrset_name($existing, $nameCandidates);
        if ($chosen === null) {
            log_debug(sprintf('No console rrset found for type %s among %s; skipping', $type, json_encode($nameCandidates)));
            $hadMissing = true;
            continue;
        }

        $record = [
            'name' => $chosen,
            'type' => $type,
            'ttl' => $ttl,
            'records' => [
                ['value' => $value],
            ],
        ];
        $response = console_set_rrset($endpoint, $token, $zoneId, $record);
        if (!$response['success']) {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'Console API update failed',
                'zone_id' => $zoneId,
            ];
        }
    }

    if ($hadMissing) {
        return [
            'success' => false,
            'message' => 'Console rrset names missing',
            'zone_id' => $zoneId,
        ];
    }

    return [
        'success' => true,
        'message' => 'Records updated via Hetzner Console API',
        'zone_id' => $zoneId,
    ];
}

function fetch_zone_id(string $endpoint, string $token, string $zoneName): ?string
{
    $response = http_request('GET', $endpoint . '/zones?name=' . urlencode($zoneName), [
        'Content-Type: application/json',
        'Auth-API-Token: ' . $token,
    ]);

    $zones = $response['data']['zones'] ?? [];
    if (!empty($zones)) {
        return $zones[0]['id'] ?? null;
    }

    return null;
}

function fetch_records(string $endpoint, string $token, string $zoneId, string $hostnameName): array
{
    $response = http_request('GET', $endpoint . '/records?zone_id=' . urlencode($zoneId), [
        'Content-Type: application/json',
        'Auth-API-Token: ' . $token,
    ]);

    $result = ['A' => null, 'AAAA' => null];
    foreach ($response['data']['records'] ?? [] as $record) {
        if ($record['name'] === $hostnameName && in_array($record['type'], ['A', 'AAAA'], true)) {
            $result[$record['type']] = $record['id'];
        }
    }

    return $result;
}

function fetch_console_zone_id(string $endpoint, string $token, string $zoneName): ?string
{
    $response = http_request('GET', $endpoint . '/zones?name=' . urlencode($zoneName), [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ]);

    $zones = $response['data']['zones'] ?? [];
    if (!empty($zones)) {
        return $zones[0]['id'] ?? null;
    }

    return null;
}

function fetch_console_rrsets(string $endpoint, string $token, string $zoneId, string $type): array
{
    $response = http_request('GET', $endpoint . '/zones/' . urlencode($zoneId) . '/rrsets?type=' . urlencode($type), [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ]);

    return $response['data']['rrsets'] ?? $response['data'] ?? [];
}

function choose_rrset_name(array $rrsets, array $candidates): ?string
{
    $available = [];
    foreach ($rrsets as $rrset) {
        if (empty($rrset['name'])) {
            continue;
        }
        $available[trim($rrset['name'], '.')] = $rrset['name'];
    }

    log_debug(sprintf('Console rrset names: %s', implode(',', array_keys($available))));
    foreach ($candidates as $candidate) {
        $key = trim($candidate, '.');
        if (isset($available[$key])) {
            return $available[$key];
        }
    }

    return null;
}

function build_rrset_candidates(string $hostnameName): array
{
    if ($hostnameName === '' || $hostnameName === '@') {
        return ['@'];
    }

    $parts = explode('.', $hostnameName);
    $candidates = [];
    for ($i = 0, $len = count($parts); $i < $len; $i++) {
        $candidates[] = implode('.', array_slice($parts, 0, $len - $i));
    }
    $candidates[] = '@';

    return array_filter(array_unique($candidates));
}

/**
 * Wrapper for curl calls that returns standardized success/error details.
 */
function http_request(string $method, string $url, array $headers = [], $body = null): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($body !== null) {
        $payload = is_string($body) ? $body : json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $responseBody = curl_exec($ch);
    $error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = null;
    if ($responseBody !== false) {
        $decoded = json_decode($responseBody, true);
        $data = $decoded === null ? null : $decoded;
    }

    $success = $responseBody !== false && $status >= 200 && $status < 300;
    $message = $error;
    if (!$message && !$success && is_array($data)) {
        $message = $data['error']['message'] ?? $data['error'] ?? null;
    }

    $bodySnippet = $responseBody !== false ? (strlen($responseBody) > 400 ? substr($responseBody, 0, 400) . '...' : $responseBody) : 'no body';
    log_debug(sprintf('HTTP %s %s -> %d (%s); body=%s; error=%s', $method, $url, $status, $success ? 'success' : 'failure', $bodySnippet, $message ?? 'none'));

    return [
        'success' => $success,
        'http_code' => $status,
        'body' => $responseBody,
        'data' => $data,
        'error' => $message,
    ];
}

function send_notification(array $config, string $realm, string $hostname, array $ips, array $result): void
{
    $defaults = [
        'enabled' => false,
        'method' => 'php',
        'from' => 'no-reply@localhost',
        'recipients' => [],
        'success_recipients' => [],
        'failure_recipients' => [],
        'send_on_success' => false,
        'send_on_failure' => true,
        'smtp' => [],
    ];
    $notifications = array_merge($defaults, $config);
    if (empty($notifications['enabled'])) {
        return;
    }

    $isSuccess = !empty($result['success']);
    if ($isSuccess && empty($notifications['send_on_success'])) {
        return;
    }
    if (!$isSuccess && empty($notifications['send_on_failure'])) {
        return;
    }

    $specific = $isSuccess ? $notifications['success_recipients'] : $notifications['failure_recipients'];
    $recipients = array_unique(array_filter($specific ?: $notifications['recipients']));
    if (empty($recipients)) {
        return;
    }

    $subject = sprintf('[Hetzner DDNS] %s %s', $isSuccess ? 'success' : 'failure', $hostname);
    $body = sprintf(
        "Realm: %s\nHostname: %s\nIPv4: %s\nIPv6: %s\nResult: %s\nMessage: %s\n",
        $realm,
        $hostname,
        $ips['ipv4'] ?? '-',
        $ips['ipv6'] ?? '-',
        $isSuccess ? 'success' : 'failure',
        $result['message'] ?? 'n/a'
    );

    $sent = false;
    if ($notifications['method'] === 'smtp') {
        $sent = send_email_smtp($notifications['smtp'], $notifications['from'], $recipients, $subject, $body);
    } else {
        $sent = send_email_php($notifications['from'], $recipients, $subject, $body);
    }

    log_debug(sprintf(
        'Notification (%s) for %s via %s was %s; recipients=%s',
        $isSuccess ? 'success' : 'failure',
        $hostname,
        $notifications['method'],
        $sent ? 'sent' : 'failed',
        implode(', ', $recipients)
    ));
}

function send_email_php(string $from, array $recipients, string $subject, string $body): bool
{
    $to = implode(', ', $recipients);
    $headers = sprintf("From: %s\r\n", $from);
    return mail($to, $subject, normalize_email_body($body), $headers);
}

function send_email_smtp(array $smtp, string $from, array $recipients, string $subject, string $body): bool
{
    $host = $smtp['host'] ?? '';
    $port = $smtp['port'] ?? 25;
    $user = $smtp['username'] ?? '';
    $pass = $smtp['password'] ?? '';
    $security = strtolower($smtp['security'] ?? 'tls');
    $useSsl = $security === 'ssl';
    $protocol = $useSsl ? 'ssl://' : '';

    $fp = stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, 30);
    if (!$fp) {
        log_debug(sprintf('SMTP connection failed: %s (%s)', $errstr, $errno));
        return false;
    }

    stream_set_timeout($fp, 5);
    $greeting = smtp_read_response($fp);
    if ($greeting['code'] !== 220) {
        log_debug(sprintf('SMTP greeting failed: %s', implode(', ', $greeting['lines'])));
        fclose($fp);
        return false;
    }

    $ehlo = smtp_command($fp, "EHLO " . gethostname() . "\r\n");
    if ($ehlo['code'] !== 250) {
        log_debug(sprintf('SMTP EHLO failed: %s', implode(', ', $ehlo['lines'])));
        fclose($fp);
        return false;
    }

    $supportsStartTls = collect_smtp_keywords($ehlo['lines'], 'STARTTLS');
    if ($security === 'tls') {
        if (!$supportsStartTls) {
            log_debug('SMTP server does not advertise STARTTLS, cannot use tls security');
            fclose($fp);
            return false;
        }

        $resp = smtp_command($fp, "STARTTLS\r\n");
        if ($resp['code'] !== 220) {
            log_debug(sprintf('SMTP STARTTLS failed: %s', implode(', ', $resp['lines'])));
            fclose($fp);
            return false;
        }

        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            log_debug('SMTP STARTTLS: failed to enable crypto');
            fclose($fp);
            return false;
        }

        $ehlo = smtp_command($fp, "EHLO " . gethostname() . "\r\n");
        if ($ehlo['code'] !== 250) {
            log_debug(sprintf('SMTP EHLO after STARTTLS failed: %s', implode(', ', $ehlo['lines'])));
            fclose($fp);
            return false;
        }
    }

    if ($user !== '' && $pass !== '') {
        $resp = smtp_command($fp, "AUTH LOGIN\r\n");
        if ($resp['code'] !== 334) {
            log_debug(sprintf('SMTP AUTH LOGIN refused: %s', implode(', ', $resp['lines'])));
            fclose($fp);
            return false;
        }
        $resp = smtp_command($fp, base64_encode($user) . "\r\n");
        if ($resp['code'] !== 334) {
            log_debug('SMTP AUTH username rejected');
            fclose($fp);
            return false;
        }
        $resp = smtp_command($fp, base64_encode($pass) . "\r\n");
        if ($resp['code'] !== 235) {
            log_debug('SMTP AUTH password rejected');
            fclose($fp);
            return false;
        }
    }

    $resp = smtp_command($fp, "MAIL FROM:<$from>\r\n");
    if ($resp['code'] !== 250) {
        log_debug(sprintf('SMTP MAIL FROM failed: %s', implode(', ', $resp['lines'])));
        fclose($fp);
        return false;
    }

    foreach ($recipients as $recipient) {
        $rcptResp = smtp_command($fp, "RCPT TO:<$recipient>\r\n");
        if (!in_array($rcptResp['code'], [250, 251], true)) {
            log_debug(sprintf('SMTP RCPT TO %s failed: %s', $recipient, implode(', ', $rcptResp['lines'])));
            fclose($fp);
            return false;
        }
    }

    $dataResp = smtp_command($fp, "DATA\r\n");
    if ($dataResp['code'] !== 354) {
        log_debug(sprintf('SMTP DATA command rejected: %s', implode(', ', $dataResp['lines'])));
        fclose($fp);
        return false;
    }

    fwrite($fp, "Subject: $subject\r\n");
    fwrite($fp, "From: $from\r\n");
    fwrite($fp, "To: " . implode(', ', $recipients) . "\r\n");
    fwrite($fp, "\r\n" . normalize_email_body($body) . "\r\n.\r\n");
    $final = smtp_read_response($fp);
    if ($final['code'] !== 250) {
        log_debug(sprintf('SMTP DATA body rejected: %s', implode(', ', $final['lines'])));
        fclose($fp);
        return false;
    }

    smtp_command($fp, "QUIT\r\n");
    fclose($fp);
    return true;
}

function smtp_command($fp, string $command): array
{
    fputs($fp, $command);
    return smtp_read_response($fp);
}

function smtp_read_response($fp): array
{
    $lines = [];
    while (($line = fgets($fp)) !== false) {
        $trim = trim($line, "\r\n");
        if ($trim === '') {
            continue;
        }
        $lines[] = $trim;
        if (strlen($trim) >= 4 && $trim[3] === ' ') {
            return ['code' => (int) substr($trim, 0, 3), 'lines' => $lines];
        }
    }
    return ['code' => 0, 'lines' => $lines];
}

function collect_smtp_keywords(array $lines, string $keyword): bool
{
    $keyword = strtoupper($keyword);
    foreach ($lines as $line) {
        if (stripos($line, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

function normalize_email_body(string $body): string
{
    $body = str_replace("\r\n", "\n", $body);
    $chunks = explode("\n", $body);
    return implode("\r\n", $chunks);
}

function console_set_rrset(string $endpoint, string $token, string $zoneId, array $record): array
{
    if (empty($record['records'])) {
        return [
            'success' => false,
            'http_code' => 400,
            'body' => null,
            'data' => null,
            'error' => 'Console rrset payload missing records',
        ];
    }
    $rrset = [
        'name' => $record['name'],
        'type' => $record['type'],
        'ttl' => $record['ttl'],
        'records' => $record['records'],
    ];
    $payload = [
        'ttl' => $rrset['ttl'],
        'records' => $rrset['records'],
    ];
    log_debug(sprintf('Setting console rrset %s/%s -> %s', $record['name'], $record['type'], json_encode($rrset['records'])));
    $name = $rrset['name'] === '@' ? '_' : $rrset['name'];
    return http_request('POST', $endpoint . '/zones/' . $zoneId . '/rrsets/' . urlencode($name) . '/' . urlencode($rrset['type']) . '/actions/set_records', [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ], $payload);
}

/**
 * Persist the latest IP/record state plus retry metadata so cron can resume work later.
 */
function upsert_history(SQLite3 $db, string $hostname, string $realm, array $ips, ?string $zoneId, ?string $recordAId, ?string $recordAAAAId, bool $needsSync, int $retryCount, ?string $lastError, ?int $pendingSince): void
{
    $stmt = $db->prepare('INSERT INTO history (hostname, realm, ip, ip6, zone_id, recordA_id, recordAAAA_id, timestamp, needs_sync, retry_count, last_error, pending_since)
        VALUES (:hostname, :realm, :ip, :ip6, :zone_id, :recordA_id, :recordAAAA_id, :timestamp, :needs_sync, :retry_count, :last_error, :pending_since)
        ON CONFLICT(hostname) DO UPDATE SET
        realm = excluded.realm,
        ip = excluded.ip,
        ip6 = excluded.ip6,
        zone_id = excluded.zone_id,
        recordA_id = excluded.recordA_id,
        recordAAAA_id = excluded.recordAAAA_id,
        timestamp = excluded.timestamp,
        needs_sync = excluded.needs_sync,
        retry_count = excluded.retry_count,
        last_error = excluded.last_error,
        pending_since = excluded.pending_since');

    $stmt->bindValue(':hostname', $hostname, SQLITE3_TEXT);
    $stmt->bindValue(':realm', $realm, SQLITE3_TEXT);
    $stmt->bindValue(':ip', $ips['ipv4'], SQLITE3_TEXT);
    $stmt->bindValue(':ip6', $ips['ipv6'], SQLITE3_TEXT);
    $stmt->bindValue(':zone_id', $zoneId ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':recordA_id', $recordAId ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':recordAAAA_id', $recordAAAAId ?? '', SQLITE3_TEXT);
    $stmt->bindValue(':timestamp', time(), SQLITE3_INTEGER);
    $stmt->bindValue(':needs_sync', $needsSync ? 1 : 0, SQLITE3_INTEGER);
    $stmt->bindValue(':retry_count', $retryCount, SQLITE3_INTEGER);
    $stmt->bindValue(':last_error', $lastError, SQLITE3_TEXT);
    $stmt->bindValue(':pending_since', $pendingSince, SQLITE3_INTEGER);
    $stmt->execute();
}

/**
 * Re-run pending updates stored in the history table; useful for cron jobs.
 */
function process_pending_updates(SQLite3 $db, array $config, ?string $realmFilter): array
{
    $query = 'SELECT * FROM history WHERE needs_sync = 1';
    if ($realmFilter !== null) {
        $query .= ' AND realm = :realm';
    }
    $stmt = $db->prepare($query);
    if ($realmFilter !== null) {
        $stmt->bindValue(':realm', $realmFilter, SQLITE3_TEXT);
    }
    $result = $stmt->execute();

    $summary = ['total' => 0, 'success' => 0, 'failed' => 0, 'details' => []];
    // Retry loop: keep trying pending hostnames until they either succeed or exhaust the retry limits.
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $summary['total']++;
        $realmKey = $row['realm'] ?: get_default_realm_key($config);

        if (!isset($config['realms'][$realmKey])) {
            upsert_history($db, $row['hostname'], $realmKey, ['ipv4' => $row['ip'], 'ipv6' => $row['ip6']], $row['zone_id'], $row['recordA_id'], $row['recordAAAA_id'], true, ($row['retry_count'] ?? 0), 'Realm removed', $row['pending_since']);
            $summary['details'][$row['hostname']] = ['status' => 'failed', 'message' => 'Realm not configured'];
            $summary['failed']++;
            continue;
        }

        $realmConfig = get_realm_config($config, $realmKey);
        [$hostnameName, $domain] = split_hostname($row['hostname']);
        $zoneLookupName = get_zone_lookup_name($realmConfig, $domain);
        $overriddenHostnameName = derive_hostname_name_from_zone($row['hostname'], $zoneLookupName);
        if ($overriddenHostnameName !== null) {
            $hostnameName = $overriddenHostnameName === '' ? '@' : $overriddenHostnameName;
        }
        $ips = ['ipv4' => $row['ip'], 'ipv6' => $row['ip6']];
        $result = sync_host($db, $config, $realmKey, $realmConfig, $row['hostname'], $domain, $zoneLookupName, $hostnameName, $ips, $row);
        if ($result['success']) {
            $summary['success']++;
            $summary['details'][$row['hostname']] = ['status' => 'success', 'api' => $result['api_used'] ?? $result['api'] ?? 'unknown'];
        } else {
            $summary['failed']++;
            $summary['details'][$row['hostname']] = ['status' => 'failed', 'message' => $result['message']];
        }
    }

    return $summary;
}

/**
 * Build a cron-friendly summary of how many hosts succeeded or still need retries.
 */
function render_cron_summary(array $summary): string
{
    $lines = [sprintf('processed: %d, success: %d, failed: %d', $summary['total'], $summary['success'], $summary['failed'])];
    // Append a line per hostname to clarify which API handled the retry or why it still fails.
    foreach ($summary['details'] as $hostname => $detail) {
        $line = " - $hostname: {$detail['status']}";
        $api = $detail['api'] ?? $detail['api_used'] ?? null;
        if ($api) {
            $line .= ' via ' . $api;
        }
        if (!empty($detail['message'])) {
            $line .= ' (' . $detail['message'] . ')';
        }
        $lines[] = $line;
    }

    return implode("\n", $lines);
}

class DDnsDB extends SQLite3
{
    public function __construct(array $config)
    {
        $this->open($config['history_db']);
        $this->busyTimeout(5000);
        $this->exec('PRAGMA foreign_keys = ON');
        $this->exec('PRAGMA journal_mode = WAL');
        $this->exec("PRAGMA synchronous = NORMAL");
        $this->exec("PRAGMA auto_vacuum = FULL");
        $this->exec("PRAGMA case_sensitive_like = OFF");
        $this->exec("PRAGMA encoding = 'UTF-8'");
        $this->exec("PRAGMA temp_store = MEMORY");
        $this->exec("PRAGMA cache_size = -2000");
        $this->exec("PRAGMA prepare_v2 = ON");
        $this->ensure_schema();
    }

    private function ensure_schema(): void
    {
        $this->exec('CREATE TABLE IF NOT EXISTS history (
            hostname TEXT PRIMARY KEY,
            realm TEXT NOT NULL DEFAULT \'\',
            ip TEXT,
            ip6 TEXT,
            zone_id TEXT,
            recordA_id TEXT,
            recordAAAA_id TEXT,
            timestamp INTEGER,
            needs_sync INTEGER DEFAULT 0,
            retry_count INTEGER DEFAULT 0,
            last_error TEXT,
            pending_since INTEGER
        )');

        $existing = [];
        $result = $this->query('PRAGMA table_info(history)');
        while ($column = $result->fetchArray(SQLITE3_ASSOC)) {
            $existing[$column['name']] = true;
        }

        $required = [
            'realm' => "TEXT NOT NULL DEFAULT ''",
            'needs_sync' => 'INTEGER DEFAULT 0',
            'retry_count' => 'INTEGER DEFAULT 0',
            'last_error' => 'TEXT',
            'pending_since' => 'INTEGER',
        ];

        foreach ($required as $name => $definition) {
            if (!isset($existing[$name])) {
                $this->exec(sprintf('ALTER TABLE history ADD COLUMN %s %s', $name, $definition));
            }
        }
    }
}
