<?php

return [
    'title' => 'Anomalie Orari - :name',
    'back_to_user' => 'Torna ai Dati Utente',
    'period' => [
        'title' => 'Anomalie rilevate nel periodo',
        'working_days' => '{0} Nessun giorno lavorativo|{1} :count giorno lavorativo|[2,*] :count giorni lavorativi',
    ],
    'summary' => [
        'expected_hours' => 'Ore Previste',
        'actual_hours' => 'Ore Effettive',
        'difference' => 'Differenza',
        'difference_positive' => 'ore in eccesso',
        'difference_negative' => 'ore mancanti',
        'anomaly_type' => 'Tipo Anomalie',
        'weekly_monthly' => 'Settimanali e Mensili',
        'weekly' => 'Settimanali',
        'monthly' => 'Mensili',
    ],
    'weekly' => [
        'title' => 'Analisi Settimanale',
        'actual_hours_prefix' => 'Ore effettive:',
        'expected_hours' => 'Ore previste',
        'actual_hours' => 'Ore effettive',
        'difference' => 'Differenza',
        'attendance_title' => 'Presenze della settimana',
        'attendance_empty' => 'Nessuna presenza registrata in questa settimana.',
        'time_off_title' => 'Permessi e ferie della settimana',
        'time_off_empty' => 'Nessun permesso o ferie in questa settimana.',
        'table' => [
            'date' => 'Data',
            'type' => 'Tipo',
            'time_in' => 'Entrata',
            'time_out' => 'Uscita',
            'hours' => 'Ore',
            'actions' => 'Azioni',
        ],
        'status' => [
            'limit_exceeded' => 'Limite superato (>40h)',
            'excess' => 'Ore in eccesso',
            'shortage' => 'Ore mancanti',
            'regular' => 'Regolare',
        ],
    ],
    'time_off' => [
        'title' => 'Permessi e Ferie',
        'table' => [
            'type' => 'Tipo',
            'start' => 'Inizio',
            'end' => 'Fine',
            'hours' => 'Ore',
            'actions' => 'Azioni',
        ],
    ],
    'actions' => [
        'edit_attendance_title' => 'Modifica presenza',
        'edit_attendance_aria' => 'Modifica presenza del :date',
        'edit_time_off_title' => 'Modifica permesso o ferie',
        'edit_time_off_aria' => 'Modifica permesso o ferie dal :date',
        'download_report' => 'Scarica Report PDF',
    ],
];
