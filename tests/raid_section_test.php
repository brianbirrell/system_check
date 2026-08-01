<?php
require_once __DIR__ . '/../sections/raid.php';

$lines = ['Personalities : [raid1]', 'md0 : active raid1'];
$html = build_raid_html($lines);

if (strpos($html, 'raid1') === false) {
    fwrite(STDERR, "Expected RAID section output to contain rendered values\n");
    exit(1);
}

echo "raid section test passed\n";
