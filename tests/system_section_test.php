<?php
require_once __DIR__ . '/../sections/system.php';

$data = [
    'name' => 'test-host',
    'date' => 'Friday, Jul 31, 2026 - 12:00:00 PM',
    'timezone' => 'UTC',
    'version' => 'Linux 6.8',
    'uptime' => '2 days, 1:02',
];

$html = build_system_html($data);

if (strpos($html, 'test-host') === false || strpos($html, 'Linux 6.8') === false || strpos($html, '2 days, 1:02') === false) {
    fwrite(STDERR, "Expected system section output to contain rendered values\n");
    exit(1);
}

echo "system section test passed\n";
