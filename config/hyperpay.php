<?php

return [
    'base_url' => env('HYPERPAY_BASE_URL', 'https://eu-test.oppwa.com/v1/checkouts/'),
    'access_token' => env('HYPERPAY_ACCESS_TOKEN'),
    'entity_id_visa' => env('HYPERPAY_ENTITY_ID_VISA'),
    'entity_id_mada' => env('HYPERPAY_ENTITY_ID_MADA'),
    'currency' => env('HYPERPAY_CURRENCY', 'SAR'),
];
