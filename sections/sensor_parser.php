<?php

/**
 * Render sensor output into HTML rows.
 *
 * This parser handles the common `sensors` output format where:
 * - device headings are plain lines such as `r8169_0_2300:00-mdio-0`
 * - sensor rows are `key: value` pairs such as `temp1: +43.0°C`
 *
 * @param string $output Raw output from the `sensors` command.
 * @param string $sensor_exclude_list Regex pattern used to skip excluded sensors.
 * @return string HTML table markup.
 */
function render_sensors_output($output, $sensor_exclude_list) {
    $parentheses_pattern = '/\s*\([^)]*\)/';
    $matched = 0;
    $lines = preg_split("/\r\n|\n|\r/", trim((string) $output));
    $html = [];

    $html[] = '<table class="sensors">';
    $html[] = '<tr class="header">';
    $html[] = '<td>&nbsp;Sensor&nbsp;</td>';
    $html[] = '<td>&nbsp;Value&nbsp;</td>';
    $html[] = '</tr>';

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (preg_match("/$sensor_exclude_list/i", $line)) {
            $matched++;
            continue;
        }

        if (preg_match('/^[^:]+:\s+/', $line)) {
            list($key, $value) = explode(':', $line, 2);
            $key = trim($key);
            $value = preg_replace($parentheses_pattern, '', trim($value));
            $html[] = "<tr class=\"body\"><td>&ensp;&ensp;{$key}:&nbsp;</td><td>&nbsp;&nbsp;{$value}</td></tr>";
            continue;
        }

        $device = preg_replace($parentheses_pattern, '', $line);
        $html[] = "<tr class=\"body\"><td colspan='2'><strong>{$device}</strong></td></tr>";
    }

    $html[] = '</table>';

    if ($matched > 0) {
        $html[] = "<p>* Lines matching \"$sensor_exclude_list\" excluded</p>";
    }

    return implode("\n", $html);
}
