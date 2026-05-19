<?php

return [
    'government' => [
        'icon'  => 'landmark',
        'label' => 'Government',
        'types' => [
            'suc'  => ['icon' => 'landmark',    'label' => 'State University / College',    'desc' => 'SUC or publicly funded higher education institution'],
            'nga'  => ['icon' => 'building-2',  'label' => 'National Government Agency',    'desc' => 'Department, bureau, or attached national agency'],
            'lgu'  => ['icon' => 'building',    'label' => 'Local Government Unit',         'desc' => 'City, municipality, or provincial government office'],
            'gocc' => ['icon' => 'building-2',  'label' => 'GOCC / Government Corporation', 'desc' => 'Government-owned and -controlled corporation'],
        ],
    ],
    'education' => [
        'icon'  => 'graduation-cap',
        'label' => 'Education',
        'types' => [
            'private_school' => ['icon' => 'school',        'label' => 'Private School',            'desc' => 'Private K-12 or higher education institution'],
            'training'       => ['icon' => 'presentation',  'label' => 'Training Center / Institute','desc' => 'Technical, vocational, or professional training organization'],
        ],
    ],
    'private' => [
        'icon'  => 'briefcase',
        'label' => 'Private',
        'types' => [
            'sme'          => ['icon' => 'store',      'label' => 'Small / Medium Enterprise', 'desc' => 'Startup or SME with up to 200 employees'],
            'medium_large' => ['icon' => 'building-2', 'label' => 'Mid-size Company',           'desc' => 'Growing company with structured departments'],
            'enterprise'   => ['icon' => 'building',   'label' => 'Large Enterprise',           'desc' => 'Multi-entity or publicly listed corporation'],
        ],
    ],
    'industry' => [
        'icon'  => 'factory',
        'label' => 'Industry',
        'types' => [
            'bpo'           => ['icon' => 'headset',       'label' => 'BPO / Shared Services',   'desc' => 'Call center, IT-BPM, or shared services organization'],
            'manufacturing' => ['icon' => 'settings-2',    'label' => 'Manufacturing / Plant',    'desc' => 'Factory, production plant, or industrial facility'],
            'healthcare'    => ['icon' => 'hospital',      'label' => 'Healthcare / Hospital',    'desc' => 'Hospital, clinic, or healthcare services provider'],
            'retail'        => ['icon' => 'shopping-cart', 'label' => 'Retail / Distribution',    'desc' => 'Retail chain, distribution network, or logistics company'],
        ],
    ],
];
