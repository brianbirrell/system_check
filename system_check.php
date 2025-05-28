<?php
/**
 * Loads the configuration settings from the 'config.php' file.
 *
 * @var array $config Associative array containing configuration options.
 * @throws Exception If the 'config.php' file is missing or returns invalid data.
 */
$config = require 'config.php';

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
?>

<?php
/**
 * System Check Page
 *
 * This file renders the main HTML structure for the System Check application.
 * 
 * Features:
 * - Loads and applies the user's preferred theme (light or dark) from localStorage.
 * - Provides a toggleTheme() function to switch between light and dark themes, updating both the stylesheet and localStorage.
 * - Automatically refreshes the page data when the browser tab becomes visible again.
 * - Includes a refreshData() function to reload the page.
 *
 * JavaScript:
 * - On DOMContentLoaded, applies the saved theme.
 * - Listens for visibility changes to refresh data when the tab is reactivated.
 * - Handles theme toggling and persistence.
 *
 * Stylesheets:
 * - Uses 'styles.css' for light theme and 'styles-dark.css' for dark theme.
 *
 * @file system_check.php
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html; charset=utf-8" http-equiv="content-type">
  <title>System Check</title>
  <link id="theme-stylesheet" rel="stylesheet" type="text/css" href="styles.css" data-theme="light">
  <script>
	// Load the saved theme on page load
	document.addEventListener('DOMContentLoaded', () => {
		const savedTheme = localStorage.getItem('theme') || 'light';
		const stylesheet = document.getElementById('theme-stylesheet');
		stylesheet.setAttribute('href', savedTheme === 'dark' ? 'styles-dark.css' : 'styles.css');
		stylesheet.setAttribute('data-theme', savedTheme);
	});

	// Refresh the page when switching back to this tab
	document.addEventListener('visibilitychange', () => {
		if (document.visibilityState === 'visible') {
			refreshData();
		}
	});

	// Toggle theme and save preference
	function toggleTheme() {
		const stylesheet = document.getElementById('theme-stylesheet');
		const currentTheme = stylesheet.getAttribute('data-theme');
		const newTheme = currentTheme === 'light' ? 'dark' : 'light';

		stylesheet.setAttribute('href', newTheme === 'dark' ? 'styles-dark.css' : 'styles.css');
		stylesheet.setAttribute('data-theme', newTheme);
		localStorage.setItem('theme', newTheme);
	}

	// Function to refresh data
	function refreshData() {
		location.reload();
	}
  </script>
</head>
<body>

<?php
/**
 * Outputs the disk free space information in an HTML table format, excluding devices that match a given pattern.
 *
 * This function executes the 'df -h' command to retrieve disk usage statistics,
 * parses the output, and displays it as an HTML table. Devices whose lines match
 * the provided exclusion regular expression pattern will be omitted from the output.
 * If any lines are excluded, a note is displayed below the table.
 *
 * @param string $dev_exclude_list A regular expression pattern to match device lines that should be excluded from the output.
 *
 * @return void
 */
function output_df($dev_exclude_list) {
	$matched = 0;
	$df_cmd = trim(`which df`);

	if (empty($df_cmd) && !file_exists($df_cmd)) {
		echo "<p>Error: 'df' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}

	$output = shell_exec(escapeshellcmd("$df_cmd -h"));
	$lines = preg_split("/\n/", $output);
	echo '<table class="section">';
	for ($i = 0; $i < sizeof($lines) && strlen($lines[$i]) > 1; $i++) {
		$cols = preg_split("/[\s]+/", $lines[$i]);
		if ($i == 0) {
			echo '<tr class="header">';
			for ($j = 0; $j < 6; $j++) {
				echo "<td align=left>&nbsp;$cols[$j]&nbsp;</td>";
			}
			echo '</tr>';
		} else {
			if (!preg_match("/$dev_exclude_list/i", $lines[$i])) {
				echo '<tr class="body">';
				for ($j = 0; $j < 6; $j++) {
					if ($j == 0 || $j == 5)
						$align = "left";
					else if ($j == 1 || $j == 2 || $j == 3)
						$align = "right";
					else if ($j == 4)
						$align = "center";
					echo "<td align=$align>&nbsp;$cols[$j]&nbsp;</td>";
				}
				echo '</tr>';
			} else {
				$matched++;
			}
		}
	}
	echo '</table>';
	if ($matched > 0) {
		echo "<p>* Lines matching \"$dev_exclude_list\" excluded</p>";
	}
}

/**
 * Outputs the system's memory usage in a formatted HTML table.
 *
 * This function attempts to locate and execute the 'free' command to retrieve
 * memory statistics. The output is parsed and displayed as an HTML table with
 * appropriate headers and data rows. If the 'free' command is not found or
 * cannot be executed, an error message is displayed instead.
 *
 * @return void
 */
function output_mem() {
	$free_cmd = trim(`which free`);

	if (empty($free_cmd) && !file_exists($free_cmd)) {
		echo "<p>Error: 'free' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}

	$output = shell_exec(escapeshellcmd("$free_cmd -h"));
	$lines = preg_split("/\n/", $output);
	echo '<table class="section">';
	for ($i = 0; $i < sizeof($lines) && strlen($lines[$i]) > 1; $i++) {
		$cols = preg_split("/[\s]+/", $lines[$i]);
		if ($i == 0)
			echo '<tr class="header">';
		else
			echo '<tr class="body">';
		foreach ($cols as $data)
			echo "<td align=right>&nbsp;$data&nbsp;</td>";
		echo '</tr>';
	}
	echo '</table>';
}

/**
 * Outputs a table displaying system information including hostname, current date and time,
 * system version, and uptime.
 *
 * This function retrieves the paths to the 'uname', 'hostname', and 'uptime' commands,
 * verifies their existence, and executes them to gather system details. The information
 * is then formatted and displayed in an HTML table. If any of the required commands are
 * not found, an error message is displayed instead.
 *
 * @return void
 */
function output_name() {
	$date = date('l, M d, Y - h:i:s A');
	$timezone = date_default_timezone_get();
	$uname_cmd = trim(`which uname`);
	$hostname_cmd = trim(`which hostname`);
	$uptime_cmd = trim(`which uptime`);

	// Check if the 'uname', 'hostname', and 'uptime' commands are available
	if (empty($uname_cmd) || !file_exists($uname_cmd)) {
		echo "<p>Error: 'uname' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}
	if (empty($hostname_cmd) || !file_exists($hostname_cmd)) {
		echo "<p>Error: 'hostname' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}
	if (empty($uptime_cmd) || !file_exists($uptime_cmd)) {
		echo "<p>Error: 'uptime' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}

	$uptime = trim(`$uptime_cmd`);
	if (empty($uptime)) {
		echo "<p>Error: Failed to retrieve uptime information. Please check the command or system configuration.</p>";
		return;
	}
	// Get the system version
	$version = trim(`$uname_cmd -sr`);
	if (empty($version)) {
		echo "<p>Error: Failed to retrieve system version. Please check the command or system configuration.</p>";
		return;
	}
	// Get the hostname
	$name = trim(`$hostname_cmd -f`);
	if (empty($name)) {
		echo "<p>Error: Failed to retrieve hostname. Please check the command or system configuration.</p>";
		return;
	}

	// Output the system information in a table
	echo '<table class="section">';
	echo '<tr><td><p>';
	echo '<b>Name:</b>&nbsp;';
	echo "$name<br>";
	echo '<b>Time:</b>&nbsp;';
	echo "$date $timezone<br>";
	echo '<b>Version:</b>&nbsp;';
	echo "$version<br>";
	echo '<b>Uptime:</b>&nbsp;';
	echo "$uptime";
	echo '</p></td></tr>';
	echo '</table>';
}

/**
 * Outputs the status of a list of services in an HTML table format.
 *
 * For each service in the provided associative array, this function attempts to open a socket connection
 * to the specified host and port to determine if the service is running. The status is displayed with a
 * colored symbol (green checkmark for running, red cross for not running) in the table.
 *
 * The function outputs two tables: one for services at even indices and one for services at odd indices.
 * Each table row contains the status symbol, service name, and the hostname (first segment of the host).
 *
 * @param array $services An associative array of services, where the key is the service name and the value is a string in the format "host:port".
 *
 * @return void
 */
function output_service($services) {
	$count = 0;

	output_service_header();
	foreach ($services as $name => $location) {
		if (!($count & 1)) {
			list ($host, $port) = preg_split('/:/', $location);
			$hostname = explode('.', $host);
			$running = @ fsockopen($host, $port, $errno, $errstr, 15);
			if (!$running) {
				$status_color = 'red';
				$status_symbol = '&#10007;';
			} else {
				fclose($running);
				$status_color = 'green';
				$status_symbol = '&#10003;';
			}
			echo "<tr class=\"body\"><td bgcolor=\"$status_color\" align=center><div align=\"center\" class=\"status-symbol\">$status_symbol</div>";
			$hostDisplay = isset($hostname[0]) ? $hostname[0] : $host;
			echo "</td><td>$name</td><td>$hostDisplay</td></tr>";
		}
		$count++;
	}
	output_service_footer();

	$count = 0;
	output_service_header();
	foreach ($services as $name => $location) {
		if ($count & 1) {
			list ($host, $port) = preg_split('/:/', $location);
			$hostname = explode('.', $host);
			$running = @ fsockopen($host, $port, $errno, $errstr, 15);
			if (!$running) {
				$status_color = 'red';
				$status_symbol = '&#10007;';
			} else {
				fclose($running);
				$status_color = 'green';
				$status_symbol = '&#10003;';
			}
			echo "<tr class=\"body\"><td bgcolor=\"$status_color\" align=center><div align=\"center\" class=\"status-symbol\">$status_symbol</div>";
			echo "</td><td>$name</td><td>$hostname[0]</td></tr>";
		}
		$count++;
	}
	output_service_footer();
}

/**
 * Outputs the HTML header for the services section.
 *
 * This function generates the opening HTML for a table with headers
 * for Status, Service, and Host, intended to be used in a system check interface.
 *
 * @return void
 */
function output_service_header() {
	echo '<div class="float-left"><table class="section">';
	echo '<tr class="header">';
	echo '<td>&nbsp;Status&nbsp;</td>';
	echo '<td>&nbsp;Service&nbsp;</td>';
	echo '<td>&nbsp;Host&nbsp;</td>';
	echo '</tr>';
}

/**
 * Outputs the closing tags for a table and a div, typically used as a footer for a service section.
 *
 * This function should be called after the corresponding opening tags have been output.
 *
 * @return void
 */
function output_service_footer() {
	echo '</table></div>';
}

/**
 * Outputs an HTML table displaying the health and temperature of all detected disk drives.
 *
 * This function uses `lsblk` to list all block devices (drives) and `smartctl` to query
 * their SMART status and temperature. For each detected drive, it displays:
 *   - Drive device name
 *   - Temperature (if available)
 *   - SMART overall health status (if available)
 *
 * Requirements:
 *   - The `lsblk` utility must be installed and available in the system path.
 *   - The `smartctl` utility must be installed and available in the system path.
 *   - The script must have permission to run `smartctl` (may require sudo).
 *
 * Output:
 *   - Prints an HTML table with the drive information.
 *
 * Notes:
 *   - If `lsblk` is not found, an error message is printed and the function returns.
 *   - If SMART data or temperature is not available for a drive, 'N/A' is displayed.
 *   - The `smartctl` command may require `sudo` permissions to access drive information.
 */
function output_disk_health() {
	$lsblkApp = trim(`which lsblk`);
	$lsblkOpts = "-dpn -I 8,259 -o NAME";
	$smartApp = trim(`which smartctl`);

	if (empty($lsblkApp) && !file_exists($lsblkApp)) {
		echo "<p>Error: 'lsblk' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}
	if (empty($smartApp) && !file_exists($smartApp)) {
		echo "<p>Error: 'smartctl' command not found. Please ensure it is installed and accessible.</p>";
		return;
	}

	$lsblkOutput = shell_exec(escapeshellcmd("$lsblkApp $lsblkOpts"));
	$drives = array_filter(array_map('trim', explode("\n", $lsblkOutput)));

	echo '<table class="section">';
	echo '<tr class="header">';
	echo '<td>&nbsp;Drive&nbsp;</td>';
	echo '<td>&nbsp;Temperature&nbsp;</td>';
	echo '<td>&nbsp;SMART Status&nbsp;</td>';
	echo '</tr>';

	foreach ($drives as $drive) {
		$sanitizedDrive = escapeshellarg($drive);
		exec("sudo $smartApp -H -A $sanitizedDrive 2>&1", $smartOutput, $retval);
		// Default values
		$health = 'N/A';
		$temp = 'N/A';

		// Parse SMART health
		foreach ($smartOutput as $line) {
			if (preg_match('/SMART overall-health self-assessment test result:\s*(\w+)/i', $line, $m)) {
				$health = strtoupper($m[1]);
			}
			// Try to find temperature (ATA or NVMe)
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

		echo '<tr class="body">';
		echo "<td>$drive</td>";
		echo "<td align=\"center\">$temp</td>";
		echo "<td align=\"center\">$health</td>";
		echo '</tr>';
	}

	echo '</table>';
}

/**
 * Outputs the contents of the /proc/mdstat file in an HTML table format.
 *
 * This function reads the first 512 bytes of the /proc/mdstat file, which contains
 * information about RAID devices on Linux systems. Each non-empty line from the file
 * is displayed as a row in an HTML table, with special characters escaped for safety.
 * If the file does not exist, an error message is displayed instead.
 *
 * @return void
 */
function output_raid() {
	$myFile = '/proc/mdstat';

	if (!file_exists("$myFile")) {
		echo "<p>Error: Unable to find $myFile. Please check permissions or configuration.</p>";
		return;
	}

	$fh = fopen($myFile, 'r');
	if ($fh === false) {
		echo "<p>Error: Unable to open $myFile. Please check permissions or configuration.</p>";
		return;
	}

	$theData = stream_get_contents($fh);
	fclose($fh);

	$lines = preg_split("/\n/", $theData);
	echo '<table class="section">';
	for ($i = 0; $i < sizeof($lines) && strlen($lines[$i]) > 1; $i++) {
		echo '<tr class="body"><td>';
		echo htmlspecialchars($lines[$i]);
		echo '</td></tr>';
	}
	echo '</table>';
}

/**
 * Outputs the status information of a UPS device using the 'upsc' command.
 *
 * This function checks for the presence of the 'upsc' utility, executes it with the specified UPS device,
 * parses the output for status, charge, load, and runtime, and displays the information in a formatted HTML block.
 *
 * Status codes are mapped to human-readable descriptions:
 *   - "OL": Online (power ok)
 *   - "OB": On Battery (power failure)
 *   - "LB": Low Battery (backup power low)
 *
 * If the 'upsc' utility is not found or cannot be executed, an error message is displayed.
 *
 * @param string $upsDev The UPS device identifier to query (e.g., 'myups@localhost').
 *
 * @return void
 */
function output_ups($upsDev) {
	$upsApp = trim(`which upsc`);

	if (empty($upsDev)) {
		echo "<p>Error: UPS device identifier is not set. Please check your configuration.</p>";
		return;
	}
	if (empty($upsApp) && !file_exists("$upsApp")) {
		echo "<p>Error: Unable to execute 'upsc'. Please check permissions or configuration.</p>";
		return;
	}

	$output = shell_exec(escapeshellcmd("$upsApp " . escapeshellarg($upsDev)));
	if (empty($output)) {
		echo "<p>Error: Unable to retrieve UPS data. Please check the UPS device or configuration.</p>";
		return;
	}

	if (preg_match('/ups.status: (.*)/', $output, $matches)) {
		$status = $matches[1];
	} else {
		$status = 'N/A';
	}

	# One of "OL," "OB," or "LB," which are online (power OK),
	# on battery (power failure), or low battery, respectively.
	if ($status == 'OL') {
		$statusText = "Online (power ok)";
	}
	elseif ($status == 'OB') {
		$statusText = "On Battery (power failure)";
	}
	elseif ($status == 'LB') {
		$statusText = "Low Battery (backup power low)";
	}
	else {
		$statusText = "N/A";
	}

	if (preg_match('/battery.charge: (.*)/', $output, $matches)) {
		$charge = $matches[1] . '%';
	} else {
		$charge = 'N/A';
	}
	if (preg_match('/ups.load: (.*)/', $output, $matches)) {
		$load = $matches[1] . '%';
	} else {
		$load = 'N/A';
	}
	if (preg_match('/battery.runtime: (.*)/', $output, $matches)) {
		$runtime = $matches[1] . 'sec';
	} else {
		$runtime = 'N/A';
	}
	echo '<p>';
	echo "$upsDev:<br>";
	echo "&nbsp;&nbsp;&nbsp;Status=$statusText, Charge=$charge, Runtime=$runtime, Load=$load";
	echo '</p>';
}

/**
 * Outputs a table of sensor information retrieved from the system's `sensors` command.
 *
 * This function executes the `sensors` command to gather hardware sensor data (such as temperature, voltage, etc.),
 * parses the output, and displays it in an HTML table format. It allows exclusion of lines matching a specified pattern.
 *
 * @param string $sensor_exclude_list A regular expression pattern. Lines matching this pattern will be excluded from the output.
 *
 * @return void Outputs HTML directly. Displays an error message if the `sensors` command is not available or fails to execute.
 */
function output_sensors($sensor_exclude_list) {
	$sensors_cmd = trim(`which sensors`);
	$matched = 0;

	if (empty($sensors_cmd) && !file_exists("$sensors_cmd")) {
		echo "<p>Error: Unable to execute 'sensors'. Please check permissions or configuration.</p>";
		return;
	}

	$retval = null;
	$output = [];
	$output = htmlspecialchars(shell_exec("$sensors_cmd 2>&1"), ENT_QUOTES, 'UTF-8');

	if (empty($output)) {
		echo "<p>Error: Unable to retrieve sensor data. Please contact the administrator.</p>";
		return;
	}
	else {
		$lines = explode("\n", $output);
		echo '<table class="section">';
		echo '<tr class="header">';
		echo '<td>&nbsp;Sensor&nbsp;</td>';
		echo '<td>&nbsp;Value&nbsp;</td>';
		echo '</tr>';

		$device = '';

		foreach ($lines as $line) {
			$line = trim($line);

			if (empty($line) || preg_match($sensor_exclude_list, $line)) continue; // Exclude matching lines

			if (strpos($line, ':') === false) {
				$device = $line;
				echo "<tr class=\"body\"><td colspan='2'><strong>{$device}</strong></td></tr>";
				continue;
			}

			list($key, $value) = explode(':', $line, 2);
			$value = preg_replace('/\s*\([^)]*\)/', '', trim($value)); // Remove inline parentheses content

			echo "<tr class=\"body\"><td>{$key}</td><td>{$value}</td></tr>";
		}

		echo "</table>";

		if ($matched > 0) {
			echo "<p>* Lines matching \"$sensor_exclude_list\" excluded</p>";
		}
	}
}
?>

<?php
/**
 * Renders a system check dashboard as an HTML table.
 *
 * The table includes the following sections:
 * - Header with "System Check" title and action buttons for toggling theme and refreshing data.
 * - System information (output_name)
 * - Memory usage (output_mem)
 * - Services status (output_service)
 * - Sensor readings (output_sensors)
 * - UPS (Uninterruptible Power Supply) status (output_ups)
 * - Filesystem information (output_df)
 * - Disk health status (output_disk_health)
 * - RAID information (output_raid)
 *
 * Each section is rendered by calling its respective PHP output function.
 * 
 * @param array $services            List of services to check and display.
 * @param array $sensor_exclude_list List of sensors to exclude from display.
 * @param string $upsDev             UPS device identifier.
 * @param array $dev_exclude_list    List of filesystems to exclude from display.
 */
?>
<table border=1 align=center cellpadding=2 cellspacing=0>
<thead>
<tr><td style="vertical-align: top;">
	<div style="display: flex; align-items: center; justify-content: space-between;">
		<h1>System Check</h1>
		<div class="btn-container">
			<button onclick="toggleTheme()" class="theme-toggle-button">Toggle Theme</button>
			&nbsp;
			<button onclick="refreshData()" class="refresh-button" title="Refresh Data">&#x21bb;</button>
		</div>
	</div>
</td></tr>
</thead>
<tbody>
<tr><td>
<h2>System:</h2>
<?php output_name(); ?>
</td></tr><tr><td>
<h2>Memory:</h2>
<?php output_mem(); ?>
</td></tr><tr><td>
<h2>Services:</h2>
<?php output_service(services: $services); ?>
</td></tr><tr><td>
<h2>Sensors:</h2>
<?php output_sensors($sensor_exclude_list); ?>
</td></tr><tr><td>
<h2>UPS Status:</h2>
<?php output_ups($upsDev); ?>
</td></tr><tr><td>
<h2>Filesystem Info:</h2>
<?php output_df($dev_exclude_list); ?>
</td></tr><tr><td>
<h2>Disk Health:</h2>
<?php output_disk_health(); ?>
</td></tr><tr><td>
<h2>RAID Info:</h2>
<?php output_raid(); ?>
</td></tr>
</tbody>
</table>

</body>
</html>