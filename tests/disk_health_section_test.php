<?php
require_once __DIR__ . '/../sections/disk_health.php';

$rows = [
    ['drive' => '/dev/sda', 'temp' => '42 &deg;C', 'health' => 'PASSED'],
];

$html = build_disk_health_html($rows);

if (strpos($html, '/dev/sda') === false || strpos($html, '42') === false) {
    fwrite(STDERR, "Expected disk health section output to contain rendered values\n");
    exit(1);
}

echo "disk health section test passed\n";
