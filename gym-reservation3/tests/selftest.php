<?php
// Pure-PHP checks; do not require MySQL or create any sample users.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require __DIR__.'/../app/Core/helpers.php';
date_default_timezone_set('Asia/Amman');
$checks = [
    ['HTML escaping', e('<script>') === '&lt;script&gt;'],
    ['Valid ID', positiveId('42') === 42],
    ['Reject invalid ID', positiveId('0') === null && positiveId('abc') === null],
    ['Validate datetime', localDateTime('2026-11-11T10:20') === '2026-11-11 10:20:00'],
    ['Reject invalid datetime', localDateTime('2026-02-30T10:20') === null],
    ['Reject array as ID', positiveId(['1']) === null],
    ['Password hash/verify', password_verify('unit-test-password',password_hash('unit-test-password',PASSWORD_DEFAULT))],
];
$failed=0;
foreach ($checks as [$name,$ok]) {
    echo ($ok?'PASS':'FAIL').' '.$name."\n";
    if (!$ok) $failed++;
}
exit($failed ? 1 : 0);
