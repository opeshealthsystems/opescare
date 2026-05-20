<?php

return [
    'book' => [
        'title'       => 'Prendre rendez-vous en vidéo',
        'provider'    => 'Choisir un prestataire',
        'date'        => 'Date et heure',
        'reason'      => 'Motif de la consultation',
        'submit'      => 'Réserver la consultation',
        'success'     => 'Consultation vidéo réservée.',
    ],
    'consent' => [
        'title'            => 'Consentement à la télémédecine',
        'body'             => 'Je consens à recevoir des soins de santé par consultation vidéo/audio via OpesCare.',
        'recording'        => 'Je consens à l\'enregistrement de cette séance à des fins de dossier médical.',
        'no_recording'     => 'Je ne consens pas à l\'enregistrement de cette séance.',
        'required'         => 'Le consentement est requis avant le début de la consultation.',
        'confirm'          => 'Je consens',
        'withdraw'         => 'Retirer le consentement',
    ],
    'session' => [
        'join'        => 'Rejoindre la consultation',
        'waiting'     => 'En attente que :provider rejoigne...',
        'in_progress' => 'Consultation en cours',
        'end'         => 'Terminer la consultation',
        'ended'       => 'Consultation terminée.',
        'connecting'  => 'Connexion en cours...',
        'poor_signal' => 'Qualité de connexion insuffisante détectée.',
    ],
    'status' => [
        'scheduled'  => 'Planifié',
        'waiting'    => 'En salle d\'attente virtuelle',
        'active'     => 'Actif',
        'completed'  => 'Terminé',
        'cancelled'  => 'Annulé',
        'no_show'    => 'Absent',
    ],
    'privacy' => [
        'no_record'    => 'Par défaut, cette consultation n\'est pas enregistrée.',
        'content_safe' => 'Le contenu de la consultation est privé et sécurisé.',
    ],
];
