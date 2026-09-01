<?php

return [

    /* ── Sidebar section labels (shared across staff views) ── */
    'sidebar_overview'       => 'Vue d’ensemble',
    'sidebar_clinical'       => 'Clinique',
    'sidebar_clinical_alerts'=> 'Alertes cliniques',
    'sidebar_hr_staff'       => 'RH & Personnel',
    'sidebar_inventory'      => 'Stock',
    'sidebar_supply_chain'   => 'Chaîne d’approvisionnement',
    'sidebar_operations'     => 'Opérations',

    /* ── Page titles (@section('title', ...)) ── */
    'title_analytics_dashboard'    => 'Tableau de bord analytique',
    'title_queue_analytics'        => 'Analyse de la file d’attente',
    'title_financial_analytics'    => 'Analyse financière',
    'title_ward_analytics'         => 'Analyse des services et des lits',
    'title_data_quality_analytics' => 'Analyse de la qualité des données',
    'title_clinical_alerts'        => 'Aide à la décision clinique — Alertes',
    'title_clinical_rules'         => 'Règles cliniques — ADC',
    'title_lab_alert_ranges'       => 'Seuils d’alerte de laboratoire — ADC',
    'title_drug_interactions'      => 'Interactions médicamenteuses — ADC',
    'title_staff_directory'        => 'Annuaire du personnel',
    'title_shift_management'       => 'Gestion des gardes',
    'title_duty_roster'            => 'Planning de garde',
    'title_leave_management'       => 'Gestion des congés',
    'title_pharmacy_inventory'     => 'Stock de la pharmacie',
    'title_blood_bank_inventory'   => 'Stock de la banque de sang',
    'title_data_import'            => 'Import de données',
    'title_import_upload'          => 'Nouvel import — Téléverser un fichier',
    'title_import_mapping'         => 'Import — Mapper les colonnes',
    'title_import_preview'         => 'Import — Aperçu et validation',
    'title_import_audit'           => 'Journal d’audit des imports',
    'title_record_immunization'    => 'Enregistrer une vaccination — Portail du personnel OpesCare',
    'title_ward_management'        => 'Gestion des services et des lits',
    'title_medical_attachments'    => 'Pièces jointes médicales',
    'title_upload_file'            => 'Téléverser un fichier',

    /* ── <option> visible labels ── */
    'opt_select'            => '— Sélectionner —',
    'opt_select_lower'      => '— sélectionner —',
    'opt_select_type'       => '— sélectionner le type —',
    'opt_select_category'   => '— Sélectionner une catégorie —',
    'opt_all'              => 'Tous',
    'opt_route_im'         => 'IM (Intramusculaire)',
    'opt_route_sc'         => 'SC (Sous-cutanée)',
    'opt_route_oral'       => 'Orale',
    'opt_route_intradermal'=> 'Intradermique',
    'opt_route_intranasal' => 'Intranasale',
    'opt_status_completed' => 'Effectuée',
    'opt_status_not_done'  => 'Non effectuée',

    /* ── JS / modal / confirm labels ── */
    'js_approve_leave'        => 'Approuver le congé',
    'js_reject_leave'         => 'Refuser le congé',
    'js_department'           => 'Service : ',
    'confirm_remove_attachment' => 'Supprimer cette pièce jointe ?',
    'js_required'            => 'Obligatoire :',
    'js_optional'            => 'Facultatif :',
    'files_accepted_types'   => 'PDF, images, Word, Excel, CSV',
    'js_floor'               => 'Étage ',

    /* ── Triage live vital-sign hints (JS) ── */
    'vital_label_spo2'       => 'SpO₂',
    'vital_label_pulse'      => 'Pouls',
    'vital_label_bp_systolic'=> 'TA systolique',
    'vital_label_temp'       => 'Temp.',
    'vital_label_resp_rate'  => 'Fréq. resp.',

    'vital_crit_hypoxia'        => 'Hypoxie sévère — Réanimation suggérée',
    'vital_crit_extreme_hr'     => 'FC extrême — Critique suggéré',
    'vital_crit_hypotension'    => 'Hypotension — Critique suggéré',
    'vital_crit_extreme_temp'   => 'Température extrême',
    'vital_crit_resp_failure'   => 'Risque d’insuffisance respiratoire',

    'vital_warn_low_o2'         => 'O₂ bas — Critique suggéré',
    'vital_warn_abnormal_hr'    => 'FC anormale',
    'vital_warn_low_bp'         => 'Tension artérielle basse',
    'vital_warn_abnormal_temp'  => 'Température anormale',
    'vital_warn_abnormal_resp'  => 'Fréquence respiratoire anormale',

    /* ── queue_display JS day / month name arrays ── */
    'days'   => ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
    'months' => ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'],

];
