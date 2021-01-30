<?php

return [
    'country' => env('DEFAULT_COUNTRY','PH'),
    'signature' => env('SIGNATURE', 'kaching'),
    'seed' => [
        'user' => [
            'name' =>  env('SEED_NAME', 'eyJpdiI6Im5FR1dhaEJwUjZ4UHMwRzdLbTd0clE9PSIsInZhbHVlIjoiM283Y2VvdGp5N1Fyd3ZOb0p0NndUM0lXakdKQ1dlcS85aFFZN0o5a1YwZz0iLCJtYWMiOiIyOTBkOWMyZDY0YWMwZWJmMGVjMTNlYjA5MGFkNGU0MzQ3NzFkYjk4NjQ0MzAzMzkwMDBjZDcwMDYyMDI2N2NjIn0='),
            'email' => env('SEED_EMAIL', 'eyJpdiI6IlYrUnNDNW9GTzRhRHhaQmV5T051YUE9PSIsInZhbHVlIjoiZ1oxZ3BqM2tSZS9QM3pZNXVGV3k4S2htcUZLakUwSzl4Z3FPQUJRZDlSdz0iLCJtYWMiOiI3N2Y3ZDhlZmVjZDExNWIzNmYwYTI1MTVkZDhiMWU1ZTU4OTZiZTc4ODgzNWE3OWVkNzIzYjY5YWE3MmQwYzk5In0='),
            'password' => env('SEED_PASSWORD', 'eyJpdiI6IjhmUTNscEV4YjY2dExsYUQ2TFBMWVE9PSIsInZhbHVlIjoiSjNia0lWT2o5NlMzZ29SakdCUjhmMThRamlPVUx5MlBxRU9zK2docWh3WT0iLCJtYWMiOiI4ZTEwMGMxNTJlMjA1MjYyYzY0MjU5NjM0MWE2ZWJiOGVkNGIzNjRlMmQ3Y2UyYWY5NmNmZTI1ZWQ4OTAyOGJmIn0=')
        ],
        'team' => [
            'name' =>  env('SEED_TEAM_NAME','Team 537')
        ],
    ],
    'keywords' => [
        'transactions' => [
            'balance' => env('KEYWORD_TRANSACTION_BALANCE', 'balance'),
            'confirm' => env('KEYWORD_TRANSACTION_CONFIRM', 'confirm'),
            'transfer' => env('KEYWORD_TRANSACTION_TRANSFER', 'transfer'),
            'deposit' => env('KEYWORD_TRANSACTION_DEPOSIT', 'deposit'),
            'withdraw' => env('KEYWORD_TRANSACTION_WITHDRAW', 'withdraw'),
        ],
    ],
    'label' => [
        'otp' => env('APP_NAME', 'Test'),
    ],
    'permissions' => [
        'admin'      => ['send message', 'issue command', 'broadcast message'],
        'agent'      => ['issue command'],
        'cashier'    => ['issue command'],
        'subscriber' => ['send message' ],
    ],
];
