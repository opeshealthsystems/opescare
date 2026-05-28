<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HL7 v2 ADT Outbound Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and connection settings for the HL7 v2 ADT MLLP outbound
    | sender. All values are read from environment variables.
    |
    */
    'host'        => env('HL7_HOST', '127.0.0.1'),
    'port'        => (int) env('HL7_PORT', 2575),
    'facility_id' => env('HL7_FACILITY_ID', 'OPESCARE'),
    'sending_app' => env('HL7_SENDING_APP', 'OPESCARE_EMR'),
    'timeout'     => (int) env('HL7_TIMEOUT', 5),
];
