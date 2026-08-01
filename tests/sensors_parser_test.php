<?php
require_once __DIR__ . '/../sensor_parser.php';

$output = <<<EOS
r8169_0_2300:00-mdio-0
Adapter: MDIO adapter
temp1:        +43.0°C  (high = +120.0°C)
EOS;

$html = render_sensors_output($output, 'SYS_FAN[2-4]|PUMP_FAN[1]|Intrusion');

if (strpos($html, 'r8169_0_2300:00-mdio-0') === false) {
    fwrite(STDERR, "Expected device heading to be rendered\n");
    exit(1);
}

if (strpos($html, 'Adapter') === false || strpos($html, 'temp1') === false) {
    fwrite(STDERR, "Expected sensor rows to be rendered\n");
    exit(1);
}

echo "sensor parser regression test passed\n";
