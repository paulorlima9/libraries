<?php

use Modules\Exchanger\Drivers\Database;
use Modules\Exchanger\Drivers\Filesystem;

return [
    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    |
    | This will be used to determine the relative conversion rates for
    | other currencies. It is best to leave as it is
    |
    */

    'base_currency' => 'USD',

    /*
    |--------------------------------------------------------------------------
    | Default Converter
    |--------------------------------------------------------------------------
    |
    */

    'default_service' => env('CURRENCY_EXCHANGER_SERVICE', 'open_exchange_rates'),

    /*
    |--------------------------------------------------------------------------
    | Converters
    |--------------------------------------------------------------------------
    */

    'services' => [
        'open_exchange_rates' => [
            'app_id' => env('OPEN_EXCHANGE_RATES_APP_ID')
        ],
        'exchange_rates_api'  => [
            'key' => env('EXCHANGE_RATES_API_KEY')
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Storage Driver
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default storage driver that should be used
    | by the framework.
    |
    | Supported: "database", "filesystem"
    |
    */

    'default' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Storage Specific Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many storage drivers as you wish.
    |
    */

    'drivers' => [
        'database' => [
            'class'      => Database::class,
            'connection' => null,
            'table'      => 'exchange_rates',
        ],

        'filesystem' => [
            'class' => Filesystem::class,
            'disk'  => null,
            'path'  => 'exchange_rates.json',
        ],
    ],
];