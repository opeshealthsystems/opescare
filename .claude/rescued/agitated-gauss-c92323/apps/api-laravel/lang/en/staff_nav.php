<?php

return [

    /* ── Sidebar section labels (shared across staff views) ── */
    'sidebar_overview'       => 'Overview',
    'sidebar_clinical'       => 'Clinical',
    'sidebar_clinical_alerts'=> 'Clinical Alerts',
    'sidebar_hr_staff'       => 'HR & Staff',
    'sidebar_inventory'      => 'Inventory',
    'sidebar_supply_chain'   => 'Supply Chain',
    'sidebar_operations'     => 'Operations',

    /* ── Page titles (@section('title', ...)) ── */
    'title_analytics_dashboard'    => 'Analytics Dashboard',
    'title_queue_analytics'        => 'Queue Analytics',
    'title_financial_analytics'    => 'Financial Analytics',
    'title_ward_analytics'         => 'Ward & Bed Analytics',
    'title_data_quality_analytics' => 'Data Quality Analytics',
    'title_clinical_alerts'        => 'Clinical Decision Support — Alerts',
    'title_clinical_rules'         => 'Clinical Rules — CDSS',
    'title_lab_alert_ranges'       => 'Lab Alert Ranges — CDSS',
    'title_drug_interactions'      => 'Drug Interactions — CDSS',
    'title_staff_directory'        => 'Staff Directory',
    'title_shift_management'       => 'Shift Management',
    'title_duty_roster'            => 'Duty Roster',
    'title_leave_management'       => 'Leave Management',
    'title_pharmacy_inventory'     => 'Pharmacy Inventory',
    'title_blood_bank_inventory'   => 'Blood Bank Inventory',
    'title_data_import'            => 'Data Import',
    'title_import_upload'          => 'New Import — Upload File',
    'title_import_mapping'         => 'Import — Map Columns',
    'title_import_preview'         => 'Import — Preview & Validate',
    'title_import_audit'           => 'Import Audit Log',
    'title_record_immunization'    => 'Record Immunization — OpesCare Staff Portal',
    'title_ward_management'        => 'Ward & Bed Management',
    'title_medical_attachments'    => 'Medical Attachments',
    'title_upload_file'            => 'Upload File',

    /* ── <option> visible labels ── */
    'opt_select'            => '— Select —',
    'opt_select_lower'      => '— select —',
    'opt_select_type'       => '— select type —',
    'opt_select_category'   => '— Select category —',
    'opt_all'              => 'All',
    'opt_route_im'         => 'IM (Intramuscular)',
    'opt_route_sc'         => 'SC (Subcutaneous)',
    'opt_route_oral'       => 'Oral',
    'opt_route_intradermal'=> 'Intradermal',
    'opt_route_intranasal' => 'Intranasal',
    'opt_status_completed' => 'Completed',
    'opt_status_not_done'  => 'Not Done',

    /* ── JS / modal / confirm labels ── */
    'js_approve_leave'        => 'Approve Leave',
    'js_reject_leave'         => 'Reject Leave',
    'js_department'           => 'Department: ',
    'confirm_remove_attachment' => 'Remove this attachment?',
    'js_required'            => 'Required:',
    'js_optional'            => 'Optional:',
    'files_accepted_types'   => 'PDF, Images, Word, Excel, CSV',
    'js_floor'               => 'Floor ',

    /* ── Triage live vital-sign hints (JS) ── */
    'vital_label_spo2'       => 'SpO₂',
    'vital_label_pulse'      => 'Pulse',
    'vital_label_bp_systolic'=> 'BP Systolic',
    'vital_label_temp'       => 'Temp',
    'vital_label_resp_rate'  => 'Resp. Rate',

    'vital_crit_hypoxia'        => 'Severe hypoxia — suggest Resuscitation',
    'vital_crit_extreme_hr'     => 'Extreme HR — suggest Critical',
    'vital_crit_hypotension'    => 'Hypotension — suggest Critical',
    'vital_crit_extreme_temp'   => 'Extreme temperature',
    'vital_crit_resp_failure'   => 'Respiratory failure risk',

    'vital_warn_low_o2'         => 'Low O₂ — suggest Critical',
    'vital_warn_abnormal_hr'    => 'Abnormal HR',
    'vital_warn_low_bp'         => 'Low blood pressure',
    'vital_warn_abnormal_temp'  => 'Abnormal temperature',
    'vital_warn_abnormal_resp'  => 'Abnormal breathing rate',

    /* ── queue_display JS day / month name arrays ── */
    'days'   => ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
    'months' => ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],

];
