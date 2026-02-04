<?php

return [
    'trial' => [
        'plan' => 'professional',
        'days' => 14,
    ],

    'plans' => [
        'starter' => [
            'rank' => 1,
            'limits' => [
                'werven' => 1,
                'werknemers' => 10,
                'machines' => 25,
            ],
        ],
        'professional' => [
            'rank' => 2,
            'limits' => [
                'werven' => 5,
                'werknemers' => 50,
                'machines' => 150,
            ],
        ],
        'enterprise' => [
            'rank' => 3,
            'limits' => [
                'werven' => 'unlimited',
                'werknemers' => 'unlimited',
                'machines' => 'unlimited',
            ],
        ],
    ],

    'features' => [
        'document_management' => 'professional',
        'api_access' => 'enterprise',
    ],
];

