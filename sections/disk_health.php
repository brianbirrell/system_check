<?php

function get_disk_health_rows() {
    $lsblkApp = trim(`which lsblk`);
    $lsblkOpts = '-J -b -dpn -I 8,259 -o NAME,TYPE,SIZE,SERIAL';
    $smartApp = trim(`which smartctl`);

    if (empty($lsblkApp) || !file_exists($lsblkApp)) {
        throw new RuntimeException("'lsblk' command not found.");
    }
    if (empty($smartApp) || !file_exists($smartApp)) {
        throw new RuntimeException("'smartctl' command not found.");
    }

    $lsblkOutput = shell_exec(escapeshellcmd($lsblkApp . ' ' . $lsblkOpts));
    $lsblkData = json_decode($lsblkOutput, true);
    $drives = [];

    if (!is_array($lsblkData) || !isset($lsblkData['blockdevices']) || !is_array($lsblkData['blockdevices'])) {
        throw new RuntimeException("Unable to parse drive information from 'lsblk'.");
    }

    foreach ($lsblkData['blockdevices'] as $device) {
        if (!is_array($device)) {
            continue;
        }

        $deviceType = isset($device['type']) ? strtolower(trim((string) $device['type'])) : '';
        $deviceSize = isset($device['size']) ? (int) $device['size'] : 0;

        if ($deviceType !== 'disk' || $deviceSize <= 0) {
            continue;
        }

        if (!empty($device['name'])) {
            $drives[] = (string) $device['name'];
        }
    }

    $rows = [];
    foreach ($drives as $drive) {
        $sanitizedDrive = escapeshellarg($drive);
        $probeOutput = [];
        $probeRetval = null;
        exec('sudo ' . $smartApp . ' -i ' . $sanitizedDrive . ' 2>&1', $probeOutput, $probeRetval);

        if ($probeRetval !== 0) {
            continue;
        }

        exec('sudo ' . $smartApp . ' -H -A ' . $sanitizedDrive . ' 2>&1', $smartOutput, $retval);
        $health = 'N/A';
        $temp = 'N/A';

        if ($retval === 0) {
            foreach ($smartOutput as $line) {
                if (preg_match('/SMART overall-health.*?:\s*(\w+)/', $line, $m)) {
                    $health = (strtoupper($m[1]) === 'PASSED') ? 'PASSED' : 'FAILED';
                }
                if (preg_match('/Temperature_Celsius.*\s(\d+)\s\(.*\)$/', $line, $m)) {
                    $temp = $m[1] . ' &deg;C';
                }
                if (preg_match('/Temperature:\s*(\d+)\s*C/', $line, $m)) {
                    $temp = $m[1] . ' &deg;C';
                }
                if (preg_match('/Current Temperature:\s*(\d+)\s*C/', $line, $m)) {
                    $temp = $m[1] . ' &deg;C';
                }
            }
        } else {
            continue;
        }

        $rows[] = [
            'drive' => $drive,
            'temp' => $temp,
            'health' => $health,
        ];
    }

    return $rows;
}

function build_disk_health_html($rows) {
    $html = '<table class="section">';
    $html .= '<tr class="header"><td>&nbsp;Drive&nbsp;</td><td align="center">&nbsp;Temperature&nbsp;</td><td align="center">&nbsp;SMART Status&nbsp;</td></tr>';

    foreach ($rows as $row) {
        $html .= '<tr class="body">';
        $html .= '<td>' . htmlspecialchars($row['drive'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td align="center">' . $row['temp'] . '</td>';
        $html .= '<td align="center">' . htmlspecialchars($row['health'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    return $html;
}

function render_disk_health_section() {
    try {
        $rows = get_disk_health_rows();
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_disk_health_html($rows);
}
