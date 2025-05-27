# System Check

A PHP-based system monitoring tool that provides information about disk usage, memory usage, system uptime, and service statuses. The application includes a light and dark theme toggle for better usability.

## Features

- **Disk Usage Monitoring**: Displays disk usage information in a table format.
- **Memory Usage Monitoring**: Shows memory usage statistics.
- **System Information**: Displays system name, version, uptime, and current date/time.
- **Service Status Check**: Monitors the status of configured services and displays their availability.
- **Theme Toggle**: Switch between light and dark themes with a persistent user preference.

## Requirements

- PHP 7.4 or higher
- A web server (e.g., Apache, Nginx)
- `df`, `free`, `uname`, `hostname`, and `uptime` commands available on the system
- `smartctl` for disk health monitoring
   - To allow smartctl to work the /etc/sudoers file needs to be updated with the following line
     ```bash
     ALL ALL=(ALL) NOPASSWD:/usr/sbin/smartctl

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/system_check.git
   cd system_check
   cp ./config-example.php config.php