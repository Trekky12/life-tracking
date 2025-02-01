<?php

return [
    'displayErrorDetails' => false,
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
    'CSRF' => [
        'enabled' => true,
        // we need to exclude some routes from CSRF protection for remote triggers
        'exclude' => []
    ],
    'app' => [
        'cachemiss' => '1',
        // Base URL for cron
        'url' => 'https://example.com',
        // i18n settings
        'i18n' => [
            'template' => 'en',
            'php' => 'en_US',
            // https://momentjs.com/docs/#/displaying/
            'dateformatJS' => [
                'date' => 'DD.MM.YYYY',
                'time' => 'HH:mm:ss',
                'datetime' => 'DD.MM.YYYY HH:mm:ss',
                'datetimeShort' => 'DD.MM.YYYY HH:mm'
            ],
            // https://www.php.net/manual/de/function.date.php
            'dateformatTwig' => [
                'date' => 'd.m.Y',
                'time' => 'H:i:s',
                'datetime' => 'd.m.Y H:i:s',
                'datetimeShort' => 'd.m.Y H:i'
            ],
            // http://userguide.icu-project.org/formatparse/datetime
            'dateformatPHP' => [
                'date' => 'dd.MM.yyyy',
                'time' => 'HH:mm:ss',
                'datetime' => 'dd.MM.yyyy HH:mm:ss',
                'datetimeShort' => 'dd.MM.yyyy HH:mm',
                'trips_buttons' => 'EEE dd. MMM',
                'trips_list' => 'EEEE dd. MMMM yyyy',
                'month_name' => 'MMMM',
                'month_name_full' => 'dd. MMMM y',
                'mealplan_list' => 'EEEE dd. MMMM yyyy',
                'weekday' => 'EEE'
            ],
            'dateformatExcel' => [
                'date' => 'dd.mm.yyyy',
                'time' => 'HH:mm:ss',
                'datetime' => 'dd.MM.yyyy HH:mm:ss',
                'datetimeShort' => 'dd.MM.yyyy HH:mm',
                'trips_buttons' => 'EEE dd. MMM',
                'trips_list' => 'EEEE dd. MMMM yyyy',
                'month_name' => 'MMMM',
                'month_name_full' => 'dd. MMMM y',
            ],
            'currency' => '€',
            'routing' => 'de'
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
        // push notifications settings
        'push' => [
            'publicKey' => 'BHTdRzN90bnvOXiBtxHe1HaOL12EPW2m7sPa9q2EyDnsTjxe8ANhX07St_c1ZfOBlKDwsNrlWeZWBmAWr8hPJio',
            'privateKey' => '9bjcsaIxn1Zaf19N_oxKRBqYQfb_GnAubsd2gAnp4wI',
            'subject' => '',
            'TTL' => 3600,
            'urgency' => 'normal',
        ],
        'mapbox_token' => '',
        'modules' => [
            "location" => [
                "url" => "/location/",
                "rootRoute" => "location",
                "icon" => "fas fa-location-dot",
                "title" => "MENU_LOCATION"
            ],
            "finances" => [
                "url" => "/finances/",
                "rootRoute" => "finances",
                "icon" => "far fa-money-bill-1",
                "title" => "MENU_FINANCES"
            ],
            "cars" => [
                "url" => "/cars/",
                "rootRoute" => "car_service_refuel",
                "icon" => "fas fa-road",
                "title" => "MENU_CAR_REFUEL_SERVICE"
            ],
            "boards" => [
                "url" => "/boards/",
                "rootRoute" => "boards",
                "icon" => "fas fa-list-check",
                "title" => "BOARDS"
            ],
            "crawlers" => [
                "url" => "/crawlers/",
                "rootRoute" => "crawlers",
                "icon" => "fas fa-database",
                "title" => "CRAWLERS"
            ],
            "splitbills" => [
                "url" => "/splitbills/",
                "rootRoute" => "splitbills",
                "icon" => "far fa-handshake",
                "title" => "SPLITBILLS",
                "menu" => "splitbills"
            ],
            "trips" => [
                "url" => "/trips/",
                "rootRoute" => "trips",
                "icon" => "far fa-map",
                "title" => "TRIPS"
            ],
            "timesheets" => [
                "url" => "/timesheets/",
                "rootRoute" => "timesheets",
                "icon" => "far fa-clock",
                "title" => "TIMESHEETS"
            ],
            "workouts" => [
                "url" => "/workouts/",
                "rootRoute" => "workouts",
                "icon" => "fas fa-dumbbell",
                "title" => "WORKOUTS"
            ]
        ]
    ]
];
