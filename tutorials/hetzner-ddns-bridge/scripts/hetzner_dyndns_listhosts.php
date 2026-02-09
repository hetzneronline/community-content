<?php
/**
 * Command-line helper that prints every hostname with the latest IP addresses.
 *
 * This script only runs from the CLI and shares the same configuration as the
 * main updater to locate the sqlite database.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This helper only runs from the command line.\n");
    exit(1);
}

$configPath = __DIR__ . '/hetzner_dyndns.config.php';
if (!file_exists($configPath)) {
    fwrite(STDERR, "Configuration file not found: $configPath\n");
    exit(1);
}

$config = require $configPath;
$dbPath = $config['history_db'] ?? (__DIR__ . '/hetzner_dyndns.sqlite3');

if (!file_exists($dbPath)) {
    fwrite(STDERR, "History database not found: $dbPath\n");
    exit(1);
}

$db = new SQLite3($dbPath);
$db->busyTimeout(5000);

$query = <<<'SQL'
SELECT hostname, realm, ip, ip6, timestamp, needs_sync
FROM history
ORDER BY hostname;
SQL;
$stmt = $db->prepare($query);
$result = $stmt->execute();

$rows = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = [
        'hostname' => $row['hostname'],
        'realm' => $row['realm'] ?: '-',
        'ipv4' => $row['ip'] ?? '-',
        'ipv6' => $row['ip6'] ?? '-',
        'updated' => $row['timestamp'] ? date('Y-m-d H:i:s', $row['timestamp']) : 'never',
        'pending' => $row['needs_sync'] ? 'yes' : 'no',
    ];
}

$headers = ['hostname', 'realm', 'ipv4', 'ipv6', 'updated', 'pending'];
$widths = array_combine($headers, array_map('strlen', $headers));
foreach ($rows as $row) {
    foreach ($headers as $column) {
        $widths[$column] = max($widths[$column], strlen($row[$column]));
    }
}

$separator = '';
foreach ($headers as $column) {
    $separator .= str_repeat('-', $widths[$column]) . ' ';
}
$separator = rtrim($separator);

$format = '';
foreach ($headers as $column) {
    $format .= '%-' . $widths[$column] . 's ';
}
$format = rtrim($format) . "\n";

printf($format, ...array_map('strtoupper', $headers));
echo $separator . "\n";
foreach ($rows as $row) {
    printf($format, ...array_values($row));
}
