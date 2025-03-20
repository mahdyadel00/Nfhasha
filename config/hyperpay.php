<?php

return [
    'base_url' => env('HYPERPAY_BASE_URL', 'https://eu-test.oppwa.com/'),
    'access_token' => env('HYPERPAY_ACCESS_TOKEN'),
    'entity_id_visa_master' => env('HYPERPAY_ENTITY_ID_VISA_MASTER'),
    'entity_id_mada' => env('HYPERPAY_ENTITY_ID_MADA'),
    'entity_id_apple_pay' => env('HYPERPAY_ENTITY_ID_APPLEPAY'),
    'currency' => 'SAR',
];
