<?php
$checks = [
    'webhook_events'       => ['facility_id','client_id'],
    'webhook_subscriptions'=> ['facility_id'],
    'reconciliation_cases' => ['facility_id'],
];
$pass = true;
foreach ($checks as $table => $required) {
    $cols  = array_column(DB::connection()->getSchemaBuilder()->getColumns($table), 'name');
    foreach ($required as $col) {
        $ok = in_array($col, $cols);
        echo ($ok ? '✓' : '✗') . " $table.$col\n";
        if (!$ok) $pass = false;
    }
}
echo $pass ? "\nAll columns present — PASSED\n" : "\nMISSING columns — FAILED\n";
