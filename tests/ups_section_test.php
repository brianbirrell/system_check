<?php
require_once __DIR__ . '/../sections/ups.php';

$data = [
    'statusText' => 'Online (power ok)',
    'charge' => '100%',
    'runtime' => '3600sec',
    'load' => '20%',
];

$html = build_ups_html($data);

if (strpos($html, 'Online (power ok)') === false || strpos($html, '100%') === false) {
    fwrite(STDERR, "Expected UPS section output to contain rendered values\n");
    exit(1);
}

echo "ups section test passed\n";
