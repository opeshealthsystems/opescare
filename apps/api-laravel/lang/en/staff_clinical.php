<?php

return [

    // Immunizations — record form
    'breadcrumb_record'  => 'Record',
    'ph_vaccine_code'    => 'e.g. BCG, OPV, DPT',
    'hint_vaccine_code'  => 'WHO-EPI code or local code',
    'ph_vaccine_name'    => 'e.g. Bacillus Calmette-Guérin',
    'opt_select'         => '— Select —',
    'route_im'           => 'IM (Intramuscular)',
    'route_sc'           => 'SC (Subcutaneous)',
    'route_oral'         => 'Oral',
    'route_intradermal'  => 'Intradermal',
    'route_intranasal'   => 'Intranasal',
    'ph_site'            => 'e.g. Left deltoid',
    'opt_completed'      => 'Completed',
    'opt_not_done'       => 'Not Done',

    // Immunizations — list
    'lbl_dose_n'         => 'Dose :n',

    // Wards — bed map + create form
    'lbl_floor_n'        => 'Floor :n',
    'ph_ward_name'       => 'e.g. General Ward A',
    'ph_floor'           => 'e.g. 2',
    'ph_building'        => 'e.g. Block A',
    'ward_unknown'       => 'Unknown',

    // Wards — admissions
    'lbl_optional'       => '(optional)',

    // Visits — triage
    'opt_na'             => 'N/A',

    // Visits — triage JS vital-range hints (rendered to staff via textContent)
    'vital_spo2'         => 'SpO₂',
    'vital_pulse'        => 'Pulse',
    'vital_bp_sys'       => 'BP Systolic',
    'vital_temp'         => 'Temp',
    'vital_rr'           => 'Resp. Rate',
    'note_spo2_crit'     => 'Severe hypoxia — suggest Resuscitation',
    'note_spo2_warn'     => 'Low O₂ — suggest Critical',
    'note_pulse_crit'    => 'Extreme HR — suggest Critical',
    'note_pulse_warn'    => 'Abnormal HR',
    'note_bp_crit'       => 'Hypotension — suggest Critical',
    'note_bp_warn'       => 'Low blood pressure',
    'note_temp_crit'     => 'Extreme temperature',
    'note_temp_warn'     => 'Abnormal temperature',
    'note_rr_crit'       => 'Respiratory failure risk',
    'note_rr_warn'       => 'Abnormal breathing rate',

];
