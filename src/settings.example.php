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
            // i18n settings
            'i18n' => [
                'template' => 'en',
                'php' => 'en_US',
                'dateformatJS' => 'YYYY-MM-DD',
                'dateformatJSFull' => 'DD.MM.YYYY HH:mm:ss',
                'twig' => 'Y-m-d',
                'currency' => '€'
            ],
            // default location
            'location' => [
                'lat' => 52.520007,
                'lng' => 13.404954,
                'zoom' => 11
            ],
            // mail params
            'mail' => [
                'fromName' => 'Life-Tracking',
                'fromAddress' => 'tracking@my-domain.de'
            ],
            // upload folder for images
            'upload_folder' => 'uploads',
            // access for all users
            'guest_access' => [
                'login',
                'logout',
                'cron'
            ],
            // the secret for the user token cookie
            'secret' => 'my_hash_secret',
            // we need to exclude some routes from CSRF protection for remote triggers
            'csrf_exlude' => [
                'finances_record',
                'location_record',
                'notifications_subscribe'
            ],
            // push notifications settings
            'push' => [
                'applicationServerPublicKey' => 'BEddEMqK4Cq6WUQNNQyqNjASr/YjVZYcjjKgJW728eHCNdPXVXY+nfTXZCkql0462Bi1zxIgOpaVDqtScfCkiEE='
            ]
        ]
    ]
];
