<?php
$tests = [
    'memory_section_test.php',
    'system_section_test.php',
    'services_section_test.php',
    'sensors_section_test.php',
    'ups_section_test.php',
    'filesystem_section_test.php',
    'disk_health_section_test.php',
    'zfs_section_test.php',
    'raid_section_test.php',
];

foreach ($tests as $testFile) {
    $command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $testFile);
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    foreach ($output as $line) {
        echo $line . PHP_EOL;
    }

    if ($exitCode !== 0) {
        fwrite(STDERR, "Failed: {$testFile}" . PHP_EOL);
        exit($exitCode);
    }
}

echo 'section unit tests passed' . PHP_EOL;
