<?php
// config.php

return [
    // Set the services you want the script to check.
    // You can add more by just adding a line "servers name"=>"host/IP:port number",
    // You can use game servers or anything.
    // e.g. "IRCdaemon"=>"localhost:6667",
    'services' => [
        'ssh' => 'localhost:22',
        'smtp' => 'localhost:25',
        'smtp-tls' => 'localhost:587',
        'http' => 'localhost:80',
        'https' => 'localhost:443',
        'imap' => 'localhost:143',
        'imaps' => 'localhost:993',
        'smb' => 'localhost:445',
        'spamd' => 'localhost:783',
        'mysql' => 'localhost:3306',
        'clamd' => 'localhost:3310',
        'upsd' => 'localhost:3493',
        'clamsmtp' => 'localhost:10025',
        'hddtemp' => 'localhost:7634',
        'webmin' => 'localhost:1551',
        'usermin' => 'localhost:5115',
        'redis' => 'localhost:6379',
        'portainer' => 'localhost:9443',
        'transmissiond' => 'localhost:9091',
        'memcached' => 'localhost:11211',
        'minecraft' => 'localhost:25565',
        'plex' => 'localhost:43500',
    ],

    // This is the name of the UPS device you want to monitor.
    'upsDev' => 'HomeOffice@localhost',

    // Example format: 'SYS_FAN[2-4]|PUMP_FAN[1]|Intrusion' excludes sensors named SYS_FAN2, SYS_FAN3, SYS_FAN4, PUMP_FAN1, and Intrusion.
    // Additional examples:
    // 'CPU_TEMP|GPU_TEMP' excludes sensors named CPU_TEMP and GPU_TEMP.
    // 'FAN[0-9]|TEMP_SENSOR' excludes sensors named FAN0, FAN1, FAN2, etc., and TEMP_SENSOR.
    // Edge case: '.*_FAN' matches any sensor name ending with '_FAN'.
    'sensor_exclude_list' => 'SYS_FAN[2-4]|PUMP_FAN[1]|Intrusion',

    // Regular expression pattern to exclude temporary or system files from the filesystem entries.
    // - 'none': Excludes entries with 'none', typically representing unmounted or placeholder devices.
    // - 'udev': Excludes udev-managed devices, which are dynamically created by the system.
    // - 'tmpfs': Excludes temporary file systems used for volatile storage.
    // - '/sys': Excludes system files related to kernel and hardware information.
    // - '/snap': Excludes filesystems related to Snap package management.
    'dev_exclude_list' => '^none|udev|tmpfs|\/sys|\/snap',
];
?>