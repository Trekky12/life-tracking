<?php

return [
    'settings' => [
        'displayErrorDetails' => false,
        'determineRouteBeforeAppMiddleware' => true,
        // view settings
        'view' => [
            'template_path' => __DIR__ . '/../templates/',
            'cache_path' => __DIR__ . '/../cache/',
        ],
        // Monolog settings
        'logger' => [
            'name' => 'life-tracking',
            'path' => __DIR__ . '/../logs/app.log',
        ],
        // database settings
        'db' => [
            'host' => 'localhost',
            'user' => '',
            'pass' => '',
            'dbname' => 'tracking',
        ],
        'app' => [
            'i18n' => [
                'template' => 'en',
                'php' => 'en_US',
                'datatables' => 'English.lang',
                'dateformatJS' => 'YYYY-MM-DD',
                'currency' => '€'
            ],
            'location' => [
                'lat' => 52.520007,
                'lng' => 13.404954,
                'zoom' => 11
            ],
            'mail' => [
                'fromName' => 'Life-Tracking',
                'fromAddress' => 'tracking@my-domain.de'
            ],
            'upload_folder' => 'uploads'
        ]
    ]
];
