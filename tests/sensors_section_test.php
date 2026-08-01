<?php
require_once __DIR__ . '/../sections/sensor_parser.php';

$output = "r8169_0_2300:00-mdio-0\nAdapter: MDIO adapter\ntemp1: +43.0°C\n";
$html = render_sensors_output($output, 'Intrusion');

if (strpos($html, 'temp1') === false || strpos($html, 'Adapter') === false) {
    fwrite(STDERR, "Expected sensors section output to contain rendered values\n");
    exit(1);
}

echo "sensors section test passed\n";
