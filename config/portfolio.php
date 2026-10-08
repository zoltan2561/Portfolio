<?php

return [
    'base_url' => env('PORTFOLIO_BASE_URL', 'https://pzoli.com'),
    'statistics_password' => env('STATISTICS_PASSWORD'),
    'site_name' => 'Papp Zoltán | Személyes szakmai portfólió',
    'szoftlab_url' => env('PORTFOLIO_SZOFTLAB_URL', 'https://szoftlab.hu'),
    'person' => [
        'name' => 'Papp Zoltán',
        'job_title' => 'PHP/Laravel developer with a broad IT background.',
        'image' => 'icons/profile-2026-720.jpg',
        'knows_about' => [
            'PHP', 'Laravel', 'JavaScript', 'SQL', 'AI integrations', 'Automation',
            'Linux', 'Docker', 'Networks', 'Hardware', 'IT support',
        ],
        'contact' => [
            'email' => 'melo@pzoli.com',
            'linkedin' => 'https://linkedin.com/in/papp-zoltán-41a7a4172/',
            'github' => 'https://github.com/zoltan2561',
            'instagram' => 'https://www.instagram.com/zoltan.ppp/',
            'facebook' => 'https://facebook.com/ztech20',
            'facebook_business' => 'https://www.facebook.com/szoftlab/',
        ],
        'same_as' => [
            'https://github.com/zoltan2561',
        ],
    ],
    'locales' => json_decode(file_get_contents(resource_path('content/portfolio.json')), true, 512, JSON_THROW_ON_ERROR),
    'references' => json_decode(file_get_contents(resource_path('content/portfolio-references.json')), true, 512, JSON_THROW_ON_ERROR),
];
