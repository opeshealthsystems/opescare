<?php
$tables = ['webhook_replays','webhook_deliveries'];
foreach ($tables as $t) {
    try {
        $cols = DB::connection()->getSchemaBuilder()->getColumns($t);
        $names = array_column($cols, 'name');
        echo strtoupper($t) . ': ' . implode(', ', $names) . "\n";
    } catch (\Throwable $e) {
        echo strtoupper($t) . ': TABLE MISSING — ' . $e->getMessage() . "\n";
    }
}
