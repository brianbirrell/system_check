<?php
require_once __DIR__ . '/sensor_parser.php';

function render_sensors_section($sensor_exclude_list) {
    $sensors_cmd = trim(`which sensors`);

    if (empty($sensors_cmd) || !file_exists($sensors_cmd)) {
        echo '<p>Error: Unable to execute &#39;sensors&#39;. Please check permissions or configuration.</p>';
        return;
    }

    $output = shell_exec(escapeshellcmd($sensors_cmd));
    if (empty($output)) {
        echo '<p>Error: Unable to retrieve sensor data. Please contact the administrator.</p>';
        return;
    }

    echo render_sensors_output($output, $sensor_exclude_list);
}
