<?php

function get_ups_data($upsDev) {
    $upsApp = trim(`which upsc`);

    if (empty($upsDev)) {
        throw new RuntimeException('UPS device identifier is not set.');
    }
    if (empty($upsApp) || !file_exists($upsApp)) {
        throw new RuntimeException("Unable to execute 'upsc'.");
    }

    $output = shell_exec(escapeshellcmd($upsApp . ' ' . escapeshellarg($upsDev)));
    if (empty($output)) {
        throw new RuntimeException('Unable to retrieve UPS data.');
    }

    $status = 'N/A';
    if (preg_match('/ups.status: (.*)/', $output, $matches)) {
        $status = $matches[1];
    }

    if ($status === 'OL') {
        $statusText = 'Online (power ok)';
    } elseif ($status === 'OB') {
        $statusText = 'On Battery (power failure)';
    } elseif ($status === 'LB') {
        $statusText = 'Low Battery (backup power low)';
    } else {
        $statusText = 'N/A';
    }

    $charge = preg_match('/battery.charge: (.*)/', $output, $matches) ? $matches[1] . '%' : 'N/A';
    $load = preg_match('/ups.load: (.*)/', $output, $matches) ? $matches[1] . '%' : 'N/A';
    $runtime = preg_match('/battery.runtime: (.*)/', $output, $matches) ? $matches[1] . 'sec' : 'N/A';

    return [
        'statusText' => $statusText,
        'charge' => $charge,
        'runtime' => $runtime,
        'load' => $load,
    ];
}

function build_ups_html($data) {
    $html = '<table class="section">';
    $html .= '<tr class="body"><td>';
    $html .= '&nbsp;Status=' . htmlspecialchars($data['statusText'], ENT_QUOTES, 'UTF-8') . ', Charge=' . htmlspecialchars($data['charge'], ENT_QUOTES, 'UTF-8') . ', Runtime=' . htmlspecialchars($data['runtime'], ENT_QUOTES, 'UTF-8') . ', Load=' . htmlspecialchars($data['load'], ENT_QUOTES, 'UTF-8');
    $html .= '</td></tr>';
    $html .= '</table>';

    return $html;
}

function render_ups_section($upsDev) {
    try {
        $data = get_ups_data($upsDev);
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_ups_html($data);
}
