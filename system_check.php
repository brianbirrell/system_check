<?php
// Include the configuration file
$config = require 'config.php';

// Extract configuration values
$services = $config['services'];
$upsDev = $config['upsDev'];
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

		// Automatically refresh data on page load
		refreshData();
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
<body>

<?php
// df command function, formatted into a table
function output_df() {
	$df_cmd = trim(`which df`);
	$exclude_list = '^udev|tmpfs|\/sys|\/snap';

	if (file_exists("$df_cmd")) {
		$output = `$df_cmd -h`;
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
				if (!preg_match("/$exclude_list/i", $lines[$i])) {
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
			echo "<p>* Lines matching \"$exclude_list\" excluded</p>";
		}
	} else {
		echo "$df_cmd not found!\n";
	}
}

// free comand function, formatted into a table
function output_mem() {
	$free_cmd = trim(`which free`);

	if (file_exists("$free_cmd")) {
		$output = `$free_cmd -h`;
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
	} else {
		echo "$free not found!\n";
	}
}

function output_name() {
	$date = date('l, M d, Y - h:i:s A');
	$uname_cmd = trim(`which uname`);
	$hostname_cmd = trim(`which hostname`);
	$uptime_cmd = trim(`which uptime`);

	if (file_exists("$uname_cmd") && file_exists("$hostname_cmd") && file_exists("$uptime_cmd")) {
		$version = trim(`$uname_cmd -sr`);
		$name = trim(`$hostname_cmd -f`);
		$uptime = trim(`$uptime_cmd`);

		echo '<table class="section">';
		echo '<tr><td><p>';
		echo '<b>Name:</b>&nbsp;';
		echo "$name<br>";
		echo '<b>Time:</b>&nbsp;';
		echo "$date<br>";
		echo '<b>Version:</b>&nbsp;';
		echo "$version<br>";
		echo '<b>Uptime:</b>&nbsp;';
		echo "$uptime";
		echo '</p></td></tr>';
		echo '</table>';
	} else {
		echo "$uname_cmd, $hostname_cmd, or $uptime_cmd not found!\n";
	}
}

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
			echo "<tr class=\"body\"><td bgcolor=\"$status_color\" align=center><div align=\"center\" style=\"font-size: 13pt; color: white\">$status_symbol</div>";
			echo "</td><td>$name</td><td>$hostname[0]</td></tr>";
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
			echo "<tr class=\"body\"><td bgcolor=\"$status_color\" align=center><div align=\"center\" style=\"font-size: 13pt; color: white\">$status_symbol</div>";
			echo "</td><td>$name</td><td>$hostname[0]</td></tr>";
		}
		$count++;
	}
	output_service_footer();
}

function output_service_header() {
	echo '<div class="float-left"><table class="section">';
	echo '<tr class="header">';
	echo '<td>&nbsp;Status&nbsp;</td>';
	echo '<td>&nbsp;Service&nbsp;</td>';
	echo '<td>&nbsp;Host&nbsp;</td>';
	echo '</tr>';
}

function output_service_footer() {
	echo '</table></div>';
}

function output_disk_health() {
	$smartApp     = 'sudo ' . trim( `which smartctl` );
	$smartAppOpts = '-d sat';
	$host = 'localhost';
	$port = '7634';
	$hdTempLines;
	$hdTempRec;
	$hdTempHash;
	$hdList = '';
	$i = 0;

	$blkApp = trim(`which lsblk`);
	$blkOpts = "-l -d -o NAME,TYPE";

	if (file_exists("$blkApp")) {
		$theData = `$blkApp $blkOpts`;

		preg_match_all('/(hd[a-z]|sd[a-z]|nvme\dn\d)/', $theData, $matches);

		$handle = @ fsockopen($host, $port, $errno, $errstr, 15);
		if (!$handle) {
			echo "ERROR: unable to connect to $host:$port";
		}
		else {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
			}
			fclose($handle);

			$buffer = preg_replace('/^\|/', '', $buffer);
			$buffer = preg_replace('/\|$/', '', $buffer);

			$hdTempLines = preg_split('/\|\|/', $buffer);

			foreach ($hdTempLines as $line) {
				$hdTempRec = preg_split('/\|/', $line);
				$hdTempHash["$hdTempRec[0]"] = $hdTempRec[2] . ' &deg;' . $hdTempRec[3];
			}
		}

		echo '<table class="section">';
		echo '<tr class="header">';
		echo '<td>&nbsp;Drive&nbsp;</td>';
		echo '<td>&nbsp;Temperature&nbsp;</td>';
		echo '<td>&nbsp;SMART Status&nbsp;</td>';
		echo '</tr>';

		// Get the Temp and SMART info for each drive.
		foreach ($matches[1] as $hdName) {
			$value = '';
			echo '<tr class="body">';
			echo "<td>/dev/$hdName&nbsp;&nbsp;&nbsp;&nbsp;";
			echo '</td><td>';

			if (array_key_exists("/dev/$hdName", $hdTempHash)) {
				echo $hdTempHash["/dev/$hdName"];
			} else {
				echo 'N/A';
			}
			echo '</td>';

			// /etc/sudoers need a line like this to get S.M.A.R.T info
			// ALL ALL=(ALL) NOPASSWD:/usr/sbin/smartctl
			exec("$smartApp $smartAppOpts /dev/$hdName", $value, $retval);
			echo "<td>";
			if (!$retval) {
				echo "PASSED";
			} else {
				echo "FAILED! ($retval)";
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	else {
		echo "$myFile not found!\n";
	}
}

function output_raid() {
	$myFile = '/proc/mdstat';

	if (file_exists("$myFile")) {
		$fh = fopen($myFile, 'r');
		$theData = fread($fh, 512);
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
	else {
		echo "$myFile not found!\n";
	}
}

function output_ups($upsDev) {
	$upsApp = trim(`which upsc`);

	if (file_exists("$upsApp")) {
		$output = `$upsApp $upsDev`;

		preg_match('/status: (.*)/', $output, $matches);
		$status = $matches[1];

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
			$statusText = $status;
		}

		preg_match('/charge: (.*)/', $output, $matches);
		$charge = $matches[1] . '%';
		preg_match('/load: (.*)/', $output, $matches);
		$load = $matches[1] . '%';
		preg_match('/runtime: (.*)/', $output, $matches);
		$runtime = $matches[1] . 'sec';

		echo '<p>';
		echo "$upsDev:<br>";
		echo "&nbsp;&nbsp;&nbsp;Status=$statusText, Charge=$charge, Runtime=$runtime, Load=$load";
		echo '</p>';
	} else {
		echo "$upsApp not found!\n";
	}
}

function output_sensors() {
	$sensors_cmd = trim(`which sensors`);
	$exclude_list = 'SYS_FAN[2-4]|PUMP_FAN[1]|Intrusion';
	$matched = 0;

	if (file_exists("$sensors_cmd")) {
		$output = `sudo $sensors_cmd`;
		$lines = preg_split("/\n/", $output);

		echo '<table class="section">';
		echo '<tr class="header">';
		echo '<td>&nbsp;Sensor&nbsp;</td>';
		echo '<td>&nbsp;Information&nbsp;</td>';
		echo '</tr>';

		foreach ($lines as $line) {
			if (!preg_match("/$exclude_list/i", $line)) {
				list ($sensor, $data) = preg_split('/:/', $line);
				echo '<tr class="body">';
				echo "<td>$sensor</td>";
				echo "<td>$data</td>";
				echo '</tr>';
			} else {
				$matched++;
			}
		}
		echo '</table>';
		if ($matched > 0) {
			echo "<p>* Lines matching \"$exclude_list\" excluded</p>";
		}
	} else {
		echo "$sensors_cmd not found!\n";
	}
}
?>

<table border=1 align=center cellpadding=2 cellspacing=0>
<tr><td>
<h1>System:</h1>
<?php output_name(); ?>
</td></tr><tr><td>
<h1>Memory:</h1>
<?php output_mem(); ?>
</td></tr><tr><td>
<h1>Services:</h1>
<?php output_service($services); ?>
</td></tr><tr><td>
<h1>Sensors:</h1>
<?php output_sensors(); ?>
</td></tr><tr><td>
<h1>UPS Status:</h1>
<?php output_ups($upsDev); ?>
</td></tr><tr><td>
<h1>Filesystem Info:</h1>
<?php output_df(); ?>
</td></tr><tr><td>
<h1>Disk Health:</h1>
<?php output_disk_health(); ?>
</td></tr><tr><td>
<h1>RAID Info:</h1>
<?php output_raid(); ?>
</td></tr><tr><td>
	<button onclick="toggleTheme()">Toggle Theme</button>
	<button onclick="refreshData()">Refresh Data</button>
</td></tr>
</table>

</body>
</html>