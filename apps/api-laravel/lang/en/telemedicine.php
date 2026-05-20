<?php

return [
    'book' => [
        'title'       => 'Book Video Consultation',
        'provider'    => 'Select Provider',
        'date'        => 'Date & Time',
        'reason'      => 'Reason for Consultation',
        'submit'      => 'Book Consultation',
        'success'     => 'Video consultation booked.',
    ],
    'consent' => [
        'title'            => 'Telemedicine Consent',
        'body'             => 'I consent to receiving healthcare services via video/audio consultation through OpesCare.',
        'recording'        => 'I consent to this session being recorded for medical record purposes.',
        'no_recording'     => 'I do not consent to recording of this session.',
        'required'         => 'Consent is required before the consultation can begin.',
        'confirm'          => 'I Consent',
        'withdraw'         => 'Withdraw Consent',
    ],
    'session' => [
        'join'        => 'Join Consultation',
        'waiting'     => 'Waiting for :provider to join...',
        'in_progress' => 'Consultation in Progress',
        'end'         => 'End Consultation',
        'ended'       => 'Consultation ended.',
        'connecting'  => 'Connecting...',
        'poor_signal' => 'Poor connection quality detected.',
    ],
    'status' => [
        'scheduled'  => 'Scheduled',
        'waiting'    => 'In Waiting Room',
        'active'     => 'Active',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
        'no_show'    => 'No Show',
    ],
    'privacy' => [
        'no_record'   => 'By default, this consultation is not recorded.',
        'content_safe' => 'Consultation content is private and secured.',
    ],
];
