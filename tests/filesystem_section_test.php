<?php
require_once __DIR__ . '/../sections/filesystem.php';

$rows = [
    ['type' => 'header', 'columns' => ['Filesystem', 'Size', 'Used', 'Avail', 'Use%', 'Mounted on']],
    ['type' => 'body', 'columns' => ['/dev/sda1', '100G', '50G', '50G', '50%', '/']],
];

$html = build_filesystem_html($rows, 0, 'tmpfs');

if (strpos($html, 'Filesystem') === false || strpos($html, '/dev/sda1') === false) {
    fwrite(STDERR, "Expected filesystem section output to contain rendered values\n");
    exit(1);
}

echo "filesystem section test passed\n";
