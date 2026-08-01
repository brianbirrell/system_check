<?php

function parse_memory_output($output) {
    $rows = [];
    $lines = preg_split('/\r\n|\n|\r/', trim((string) $output));

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = preg_split('/\s+/', $line);
        $label = $parts[0] ?? '';

        if ($label === 'Mem:' || $label === 'Swap:') {
            $row = [
                'label' => $label,
                'total' => $parts[1] ?? '',
                'used' => $parts[2] ?? '',
                'free' => $parts[3] ?? '',
                'shared' => $parts[4] ?? '',
                'buff/cache' => $parts[5] ?? '',
                'available' => $parts[6] ?? '',
            ];
            $rows[] = $row;
        }
    }

    return $rows;
}

function get_memory_rows() {
    $free_cmd = trim(`which free`);

    if (empty($free_cmd) || !file_exists($free_cmd)) {
        throw new RuntimeException("'free' command not found.");
    }

    $output = shell_exec(escapeshellcmd($free_cmd . ' -h'));
    if (empty($output)) {
        throw new RuntimeException('Unable to retrieve memory data.');
    }

    return parse_memory_output($output);
}

function build_memory_html($rows) {
    $html = '<table class="section">';
    foreach ($rows as $row) {
        $html .= '<tr class="body">';
        $html .= '<td>&nbsp;' . htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['total'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['used'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['free'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['shared'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['buff/cache'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '<td align="right">&nbsp;' . htmlspecialchars($row['available'], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    return $html;
}

function render_memory_section() {
    try {
        $rows = get_memory_rows();
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_memory_html($rows);
}
