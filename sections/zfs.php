<?php

function get_zfs_status_output() {
    $zpoolApp = trim(`which zpool`);

    if (empty($zpoolApp) || !file_exists($zpoolApp)) {
        throw new RuntimeException("Unable to execute 'zpool'.");
    }

    $output = shell_exec(escapeshellcmd($zpoolApp . ' status -g -c model,size') . ' 2>&1');
    if (empty($output)) {
        throw new RuntimeException('Unable to retrieve ZFS pool status.');
    }

    return $output;
}

function parse_zfs_status_output($output) {
    $lines = preg_split('/\r\n|\n|\r/', trim($output));
    $pools = [];
    $current = null;
    $inConfig = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            if ($inConfig && !empty($current['config'])) {
                $inConfig = false;
            }
            continue;
        }

        if (preg_match('/^pool:\s*(.+)$/i', $trimmed, $matches)) {
            if ($current !== null) {
                $pools[] = $current;
            }
            $current = [
                'name' => trim($matches[1]),
                'state' => 'UNKNOWN',
                'status' => 'N/A',
                'scan' => 'N/A',
                'config' => [],
                'errors' => 'N/A',
            ];
            $inConfig = false;
            continue;
        }

        if ($current === null) {
            continue;
        }

        if (preg_match('/^state:\s*(.+)$/i', $trimmed, $matches)) {
            $current['state'] = trim($matches[1]);
            continue;
        }

        if (preg_match('/^status:\s*(.+)$/i', $trimmed, $matches)) {
            $current['status'] = trim($matches[1]);
            continue;
        }

        if (preg_match('/^scan:\s*(.+)$/i', $trimmed, $matches)) {
            $current['scan'] = trim($matches[1]);
            continue;
        }

        if (preg_match('/^config:\s*$/i', $trimmed)) {
            $inConfig = true;
            continue;
        }

        if (preg_match('/^errors:\s*(.+)$/i', $trimmed, $matches)) {
            $current['errors'] = trim($matches[1]);
            continue;
        }

        if ($inConfig) {
            $current['config'][] = preg_replace('/^\s+/', '', $line);
        }
    }

    if ($current !== null) {
        $pools[] = $current;
    }

    return $pools;
}

function build_zfs_html($pools) {
    if (empty($pools)) {
        return '<p>No ZFS pools found.</p>';
    }

    $html = '<table class="section">';
    foreach ($pools as $pool) {
        $html .= '<tr class="header"><td colspan="2">&nbsp;Pool: ' . htmlspecialchars($pool['name'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td></tr>';
        $html .= '<tr class="body"><td><b>State:</b></td><td>' . htmlspecialchars($pool['state'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        if ($pool['status'] !== 'N/A') {
            $html .= '<tr class="body"><td><b>Status:</b></td><td>' . htmlspecialchars($pool['status'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
        $html .= '<tr class="body"><td><b>Scan:</b></td><td>' . htmlspecialchars($pool['scan'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
        $html .= '<tr class="body"><td><b>Errors:</b></td><td>' . htmlspecialchars($pool['errors'], ENT_QUOTES, 'UTF-8') . '</td></tr>';

        if (!empty($pool['config'])) {
            $html .= '<tr class="body"><td colspan="2"><pre style="margin:0; white-space:pre-wrap;">' . htmlspecialchars(implode("\n", $pool['config']), ENT_QUOTES, 'UTF-8') . '</pre></td></tr>';
        }
    }
    $html .= '</table>';

    return $html;
}

function render_zfs_section() {
    try {
        $output = get_zfs_status_output();
        $pools = parse_zfs_status_output($output);
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_zfs_html($pools);
}
