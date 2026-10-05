<?php

return [
    'admin_password_hash' => env('RANDI_ADMIN_PASSWORD_HASH', ''),
    'base_url' => env('RANDI_BASE_URL', env('APP_URL', 'https://pzoli.com')),
    'activities' => [
        'coffee_walk' => ['☕', 'Kávé és séta', 'Koffein, friss levegő, és egy beszélgetés, amit nem kell időre befejezni.'],
        'dinner' => ['🍝', 'Egy finom vacsora', 'Jó kaja, jó társaság. A „csak egy falatot kérek” belefér.'],
        'cinema' => ['🎬', 'Mozi', 'Popcornból nem ígérem, hogy pontosan a felét eszem meg.'],
        'mini_trip' => ['🌿', 'Egy kis kiruccanás', 'Szép hely, közös élmény, nulla teljesítménytúra.'],
        'bowling' => ['🎳', 'Bowling vagy biliárd', 'Egy kis verseny. A szabályokat azért előtte tisztázzuk.'],
        'surprise' => ['🎁', 'Lepj meg!', 'Én szervezek, neked csak meg kell jelenned.'],
        'custom' => ['💡', 'Van jobb ötletem', 'Mesélj! A legjobb tervek néha így kezdődnek.'],
    ],
    'time_windows' => [
        'afternoon' => ['Délután', '14:00–17:00', 17],
        'early_evening' => ['Kora este', '17:00–19:00', 19],
        'evening' => ['Este', '19:00–22:00', 22],
        'flexible' => ['Az időpontban rugalmas vagyok', '', null],
    ],
];
