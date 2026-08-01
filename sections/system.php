<?php

function get_system_data() {
    $date = date('l, M d, Y - h:i:s A');
    $timezone = date_default_timezone_get();
    $uname_cmd = trim(`which uname`);
    $hostname_cmd = trim(`which hostname`);
    $uptime_cmd = trim(`which uptime`);

    if (empty($uname_cmd) || !file_exists($uname_cmd)) {
        throw new RuntimeException("'uname' command not found.");
    }
    if (empty($hostname_cmd) || !file_exists($hostname_cmd)) {
        throw new RuntimeException("'hostname' command not found.");
    }
    if (empty($uptime_cmd) || !file_exists($uptime_cmd)) {
        throw new RuntimeException("'uptime' command not found.");
    }

    $uptime = trim(shell_exec(escapeshellcmd($uptime_cmd)));
    if (empty($uptime)) {
        throw new RuntimeException('Failed to retrieve uptime information.');
    }

    $version = trim(shell_exec(escapeshellcmd($uname_cmd . ' -sr')));
    if (empty($version)) {
        throw new RuntimeException('Failed to retrieve system version.');
    }

    $name = trim(shell_exec(escapeshellcmd($hostname_cmd . ' -f')));
    if (empty($name)) {
        throw new RuntimeException('Failed to retrieve hostname.');
    }

    return [
        'date' => $date,
        'timezone' => $timezone,
        'version' => $version,
        'uptime' => $uptime,
        'name' => $name,
    ];
}

function build_system_html($data) {
    $html = '<table class="section">';
    $html .= '<tr class="body"><td><p>';
    $html .= '<b>Name:</b>&nbsp;' . htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8') . '<br>';
    $html .= '<b>Time:</b>&nbsp;' . htmlspecialchars($data['date'] . ' ' . $data['timezone'], ENT_QUOTES, 'UTF-8') . '<br>';
    $html .= '<b>Version:</b>&nbsp;' . htmlspecialchars($data['version'], ENT_QUOTES, 'UTF-8') . '<br>';
    $html .= '<b>Uptime:</b>&nbsp;' . htmlspecialchars($data['uptime'], ENT_QUOTES, 'UTF-8');
    $html .= '</p></td></tr>';
    $html .= '</table>';

    return $html;
}

function render_system_section() {
    try {
        $data = get_system_data();
    } catch (RuntimeException $exception) {
        echo '<table class="section"><tr class="body"><td><p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p></td></tr></table>';
        return;
    }

    echo build_system_html($data);
}
