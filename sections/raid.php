<?php

function get_raid_data() {
    $myFile = '/proc/mdstat';

    if (!file_exists($myFile)) {
        throw new RuntimeException("Unable to find $myFile.");
    }

    $fh = fopen($myFile, 'r');
    if ($fh === false) {
        throw new RuntimeException("Unable to open $myFile.");
    }

    $theData = stream_get_contents($fh);
    fclose($fh);

    return explode("\n", $theData);
}

function build_raid_html($lines) {
    $html = '<table class="section">';
    foreach ($lines as $line) {
        if (strlen($line) > 1) {
            $html .= '<tr class="body"><td>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }
    }
    $html .= '</table>';

    return $html;
}

function render_raid_section() {
    try {
        $lines = get_raid_data();
    } catch (RuntimeException $exception) {
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        return;
    }

    echo build_raid_html($lines);
}
