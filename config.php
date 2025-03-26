<?php
// config.php

return [
// Set the services you want the script to check.
// You can add more by just adding a line "servers name"=>"host/IP:port number",
// You can use game servers or anything.
// e.g. "IRCdaemon"=>"localhost:6667",
    'services' => [
        'ssh' => 'quantum:22',
        'smtp' => 'grayspace.redirectme.net:25',
        'smtp-tls' => 'grayspace.redirectme.net:587',
        'http' => 'grayspace.redirectme.net:80',
        'https' => 'grayspace.redirectme.net:443',
        'imap' => 'grayspace.redirectme.net:143',
        'imaps' => 'grayspace.redirectme.net:993',
        'smb' => 'quantum:445',
        'spamd' => 'localhost:783',
        'mysql' => 'localhost:3306',
        'clamd' => 'localhost:3310',
        'upsd' => 'quantum:3493',
        'clamsmtp' => 'localhost:10025',
        'hddtemp' => 'localhost:7634',
        'webmin' => 'grayspace.redirectme.net:1551',
        'usermin' => 'grayspace.redirectme.net:5115',
        'redis' => 'localhost:6379',
        'portainer' => 'localhost:9443',
        'transmissiond' => 'quantum:9091',
        'memcached' => 'localhost:11211',
        'minecraft' => 'grayspace.redirectme.net:25565',
        'plex' => 'grayspace.redirectme.net:43500',
    ],
    'upsDev' => 'HomeOffice@quantum',
];