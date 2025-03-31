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
    'upsDev' => 'HomeOffice@quantum',
];
?>