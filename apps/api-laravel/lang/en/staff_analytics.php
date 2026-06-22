<?php

return [

    // ── Analytics page <title> (browser tab / @section('title')) ──
    'title_dashboard'      => 'Analytics Dashboard',
    'title_ward'           => 'Ward & Bed Analytics',
    'title_queue'          => 'Queue Analytics',
    'title_financial'      => 'Financial Analytics',
    'title_data_quality'   => 'Data Quality Analytics',

    // ── Analytics overview inline KPI-card hint fragments ──
    'visits_hint'          => ':done done · :active active',
    'revenue_of_invoiced'  => 'of :amount invoiced',
    'revenue_rate_suffix'  => ':rate%',
    'overdue_invoices'     => ':count overdue invoices',
    'total_registered'     => ':count total registered',
    'staff_active_of'      => 'active of :total',
    'staff_on_leave'       => ':count on leave',

    // ── CDSS page <title> (browser tab / @section('title')) ──
    'cdss_title_alerts'    => 'Clinical Decision Support — Alerts',
    'cdss_title_rules'     => 'Clinical Rules — CDSS',
    'cdss_title_lab'       => 'Lab Alert Ranges — CDSS',
    'cdss_title_ddi'       => 'Drug Interactions — CDSS',
    'cdss_title_patient'   => 'Clinical Alert History — :name',

    // ── CDSS lab ranges inline filter fragment ──
    'cdss_lab_age_prefix'  => 'Age:',

];
