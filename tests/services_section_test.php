<?php
require_once __DIR__ . '/../sections/services.php';

$rows = [
    ['name' => 'ssh', 'display_host' => 'localhost', 'status_color' => 'green', 'status_symbol' => '&#10003;'],
];

$html = build_services_html($rows);

if (strpos($html, 'ssh') === false || strpos($html, 'localhost') === false) {
    fwrite(STDERR, "Expected services section output to contain rendered values\n");
    exit(1);
}

echo "services section test passed\n";
