<?php
require_once __DIR__ . '/sections/sensor_parser.php';
require_once __DIR__ . '/sections/system.php';
require_once __DIR__ . '/sections/memory.php';
require_once __DIR__ . '/sections/services.php';
require_once __DIR__ . '/sections/sensors.php';
require_once __DIR__ . '/sections/ups.php';
require_once __DIR__ . '/sections/filesystem.php';
require_once __DIR__ . '/sections/disk_health.php';
require_once __DIR__ . '/sections/raid.php';

/**
 * Loads the configuration settings from the 'config.php' file.
 *
 * @var array $config Associative array containing configuration options.
 * @throws Exception If the 'config.php' file is missing or returns invalid data.
 */
$config = require __DIR__ . '/config.php';

/**
 * Retrieves configuration values for system check.
 *
 * @var array $services            List of services to be checked.
 * @var string $upsDev             Device identifier for the UPS.
 * @var array $dev_exclude_list    List of device names to exclude from checks.
 * @var array $sensor_exclude_list List of sensor names to exclude from checks.
 */
$services = $config['services'];
$upsDev = $config['upsDev'];
$dev_exclude_list = $config['dev_exclude_list'];
$sensor_exclude_list = $config['sensor_exclude_list'];

$validThemes = ['light', 'dark'];
$theme = '';

if (isset($_POST['theme']) && in_array($_POST['theme'], $validThemes, true)) {
    $theme = $_POST['theme'];
    setcookie('theme', $theme, [
        'expires' => time() + (365 * 24 * 60 * 60),
        'path' => '/',
        'samesite' => 'Lax',
    ]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
} elseif (isset($_COOKIE['theme']) && in_array($_COOKIE['theme'], $validThemes, true)) {
    $theme = $_COOKIE['theme'];
}

$bodyThemeClass = $theme === 'dark' ? 'theme-dark' : ($theme === 'light' ? 'theme-light' : '');
$nextTheme = $theme === 'dark' ? 'light' : 'dark';
$toggleTitle = $nextTheme === 'dark' ? 'Enable dark mode' : 'Enable light mode';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="content-type">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Check</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="<?= htmlspecialchars($bodyThemeClass, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="page-wrap">
        <table class="main" align=center cellpadding=2 cellspacing=0>
            <thead>
                <tr><td style="vertical-align: top;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h1>System Check</h1>
                        <div class="btn-container">
                            <form method="post" class="theme-toggle-form">
                                <input type="hidden" name="theme" value="<?= htmlspecialchars($nextTheme, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="theme-toggle-button" aria-label="<?= htmlspecialchars($toggleTitle, ENT_QUOTES, 'UTF-8'); ?>" title="<?= htmlspecialchars($toggleTitle, ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="theme-toggle-sun" aria-hidden="true">☀</span>
                                    <span class="theme-toggle-moon" aria-hidden="true">🌙</span>
                                    <span class="theme-toggle-thumb" aria-hidden="true"></span>
                                </button>
                            </form>
                            &nbsp;
                            <a href="" class="refresh-button" title="Refresh Data" aria-label="Refresh Data">&#x21bb;</a>
                        </div>
                    </div>
                </td></tr>
            </thead>
            <tbody>
                <tr><td>
                    <h2>System:</h2>
                    <?php render_system_section(); ?>
                </td></tr>
                <tr><td>
                    <h2>Memory:</h2>
                    <?php render_memory_section(); ?>
                </td></tr>
                <tr><td>
                    <h2>Services:</h2>
                    <?php render_services_section($services); ?>
                </td></tr>
                <tr><td>
                    <h2>Sensors:</h2>
                    <?php render_sensors_section($sensor_exclude_list); ?>
                </td></tr>
                <tr><td>
                    <h2>UPS Status:</h2>
                    <?php render_ups_section($upsDev); ?>
                </td></tr>
                <tr><td>
                    <h2>Filesystem Info:</h2>
                    <?php render_df_section($dev_exclude_list); ?>
                </td></tr>
                <tr><td>
                    <h2>Disk Health:</h2>
                    <?php render_disk_health_section(); ?>
                </td></tr>
                <tr><td>
                    <h2>RAID Info:</h2>
                    <?php render_raid_section(); ?>
                </td></tr>
            </tbody>
        </table>
    </div>
</body>
</html>
