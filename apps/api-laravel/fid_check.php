<?php
$cols = array_column(DB::connection()->getSchemaBuilder()->getColumns('care_facilities'), 'name');
echo "care_facilities: " . implode(', ', $cols) . "\n";
$cols2 = array_column(DB::connection()->getSchemaBuilder()->getColumns('facility_registry'), 'name');
echo "facility_registry: " . implode(', ', $cols2) . "\n";
