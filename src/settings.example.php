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
                // https://momentjs.com/docs/#/displaying/
                'dateformatJS' => [
                    'date' => 'DD.MM.YYYY',
                    'time' => 'HH:mm:ss',
                    'datetime' => 'DD.MM.YYYY HH:mm:ss'
                ],
                // https://www.php.net/manual/de/function.date.php
                'dateformatTwig' => [
                    'date' => 'd.m.Y',
                    'time' => 'H:i:s',
                    'datetime' => 'd.m.Y H:i:s'
                ],
                // http://userguide.icu-project.org/formatparse/datetime
                'dateformatPHP' => [
                    'date' => 'dd.MM.YYYY',
                    'time' => 'HH:mm:ss',
                    'datetime' => 'dd.MM.YYYY HH:mm:ss',
                    'trips_buttons' => 'EEE dd. MMM',
                    'trips_list' => 'EEEE dd. MMM',
                    'month_name' => 'MMMM',
                    'month_name_full' => 'dd. MMMM y',                    
                ],
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
                'fromAddress' => 'tracking@my-domain.de',
                'smtp' => false,
                'username' => '',
                'password' => '',
                'host' => '',
                'port' => '',
                'secure' => ''
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
                'crawler_record'
            ],
            // push notifications settings
            'push' => [
                'publicKey' => '',
                'privateKey' => '',
                'subject' => '',
                'TTL' => 3600,
                'urgency' => 'normal',
            ]
        ]
    ]
];
