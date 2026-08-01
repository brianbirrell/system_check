<?php

function get_service_rows($services) {
    $rows = [];
    foreach ($services as $name => $location) {
        list($host, $port) = preg_split('/:/', $location);
        $hostname = explode('.', $host);
        $running = @fsockopen($host, $port, $errno, $errstr, 15);

        if (!$running) {
            $status_color = 'red';
            $status_symbol = '&#10007;';
        } else {
            fclose($running);
            $status_color = 'green';
            $status_symbol = '&#10003;';
        }

        $rows[] = [
            'name' => $name,
            'host' => $host,
            'display_host' => $hostname[0] ?? $host,
            'status_color' => $status_color,
            'status_symbol' => $status_symbol,
        ];
    }

    return $rows;
}

function build_services_html($rows) {
    $html = '<div class="table-container services-wide">';
    $html .= '<table class="section">';
    $html .= '<tr class="header"><td>&nbsp;Status&nbsp;</td><td>&nbsp;Service&nbsp;</td><td>&nbsp;Host&nbsp;</td></tr>';

    foreach ($rows as $row) {
        $html .= '<tr class="body">';
        $html .= '<td bgcolor="' . htmlspecialchars($row['status_color'], ENT_QUOTES, 'UTF-8') . '" align="center"><div align="center" class="status-symbol">' . $row['status_symbol'] . '</div></td>';
        $html .= '<td>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['display_host'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
    $html .= '</div>';

    return $html;
}

function render_services_section($services) {
    $rows = get_service_rows($services);
    echo build_services_html($rows);
}
