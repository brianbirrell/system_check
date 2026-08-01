<?php
require_once __DIR__ . '/../sections/memory.php';

$sample = <<<EOS
              total        used        free      shared  buff/cache   available
Mem:           15914        5797        1212         630         8905        8959
Swap:             0           0           0
EOS;

$data = parse_memory_output($sample);

if (!is_array($data) || count($data) < 2) {
    fwrite(STDERR, "Expected parsed memory rows\n");
    exit(1);
}

$mem = $data[0];
if ($mem['label'] !== 'Mem:' || $mem['total'] !== '15914' || $mem['used'] !== '5797') {
    fwrite(STDERR, "Expected memory values to be parsed correctly\n");
    exit(1);
}

echo "memory section test passed\n";
