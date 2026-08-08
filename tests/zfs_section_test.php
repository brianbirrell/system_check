<?php
require_once __DIR__ . '/../sections/zfs.php';

$output = "pool: bigpool1\n state: ONLINE\n  scan: scrub repaired 0B in 00:00:00 with 0 errors on Mon Aug  3 00:15:38 2026\nconfig:\n\n        NAME                      STATE     READ WRITE CKSUM                    model    size\n        bigpool1                  ONLINE       0     0     0\n          9074418192826014774     ONLINE       0     0     0\n            17804970145267270151  ONLINE       0     0     0     HGST HDN726040ALE614    3.6T\n            9349034448680499658   ONLINE       0     0     0     HGST HDN726040ALE614    3.6T\n            13154272222409376020  ONLINE       0     0     0     HGST HDN726040ALE614    3.6T\n\nerrors: No known data errors\n\n  pool: fastpool\n state: ONLINE\n  scan: scrub repaired 0B in 00:03:34 with 0 errors on Mon Aug  3 00:10:32 2026\nconfig:\n\n        NAME                   STATE     READ WRITE CKSUM                    model    size\n        fastpool               ONLINE       0     0     0\n          7072818123188401187  ONLINE       0     0     0  Sabrent Rocket 4.0 Plus  931.5G\n\nerrors: No known data errors\n";

$pools = parse_zfs_status_output($output);
if (count($pools) !== 2) {
    fwrite(STDERR, "Expected 2 pools from parser\n");
    exit(1);
}

$html = build_zfs_html($pools);

if (strpos($html, 'bigpool1') === false || strpos($html, 'fastpool') === false || strpos($html, 'scrub repaired 0B') === false || strpos($html, 'HGST HDN726040ALE614') === false || strpos($html, '931.5G') === false) {
    fwrite(STDERR, "Expected ZFS section HTML to contain parsed pool details and model/size information\n");
    exit(1);
}

echo "zfs section test passed\n";
