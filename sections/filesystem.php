<?php

function parse_df_output($output, $dev_exclude_list) {
    $lines = preg_split('/\r\n|\n|\r/', $output);
    $rows = [];
    $matched = 0;

    for ($i = 0; $i < count($lines) && strlen($lines[$i]) > 1; $i++) {
        $line = $lines[$i];
        $cols = preg_split('/[\s]+/', trim($line));
        if ($i === 0) {
            $rows[] = ['type' => 'header', 'columns' => $cols];
            continue;
        }

        if (!preg_match('/' . $dev_exclude_list . '/i', $line)) {
            $rows[] = ['type' => 'body', 'columns' => $cols];
        } else {
            $matched++;
        }
    }

    return ['rows' => $rows, 'matched' => $matched];
}

function get_df_rows($dev_exclude_list) {
    $df_cmd = trim(`which df`);
    if (empty($df_cmd) || !file_exists($df_cmd)) {
        throw new RuntimeException("'df' command not found.");
    }

    $output = shell_exec(escapeshellcmd($df_cmd . ' -h'));
    if (empty($output)) {
        throw new RuntimeException('Unable to retrieve filesystem data.');
    }

    return parse_df_output($output, $dev_exclude_list);
}

function build_filesystem_html($rows, $matched, $dev_exclude_list) {
    $html = '<table class="section">';
    foreach ($rows as $row) {
        if ($row['type'] === 'header') {
            $html .= '<tr class="header">';
            for ($j = 0; $j < min(6, count($row['columns'])); $j++) {
                $html .= '<td align="left">&nbsp;' . htmlspecialchars($row['columns'][$j], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
            }
            $html .= '</tr>';
            continue;
        }

        $html .= '<tr class="body">';
        for ($j = 0; $j < min(6, count($row['columns'])); $j++) {
            $align = 'left';
            if ($j === 1 || $j === 2 || $j === 3) {
                $align = 'right';
            } elseif ($j === 4) {
                $align = 'center';
            }
            $html .= '<td align="' . $align . '">&nbsp;' . htmlspecialchars($row['columns'][$j], ENT_QUOTES, 'UTF-8') . '&nbsp;</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</table>';

    if ($matched > 0) {
        $html .= '<p>* Lines matching "' . htmlspecialchars($dev_exclude_list, ENT_QUOTES, 'UTF-8') . '" excluded</p>';
    }

    return $html;
}

function render_df_section($dev_exclude_list) {
    try {
        $result = get_df_rows($dev_exclude_list);
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_filesystem_html($result['rows'], $result['matched'], $dev_exclude_list);
}
