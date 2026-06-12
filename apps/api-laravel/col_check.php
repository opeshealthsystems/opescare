<?php
$tables = ['webhook_events','webhook_subscriptions','reconciliation_cases','medical_id_access_events','identity_merge_cases'];
foreach ($tables as $t) {
    $cols = DB::connection()->getSchemaBuilder()->getColumns($t);
    $names = array_column($cols, 'name');
    echo strtoupper($t) . ': ' . implode(', ', $names) . "\n";
}
