<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HyperPayService
{
    private $baseUrl;
    private $accessToken;
    private $entities;
    private $currency;

    public function __construct()
    {
        $this->baseUrl = config('hyperpay.base_url');
        $this->accessToken = config('hyperpay.access_token');
        $this->currency = config('hyperpay.currency');

        // دعم طرق دفع متعددة
        $this->entities = [
            'visa'  => config('hyperpay.entity_id_visa_master'),
            'mada'  => config('hyperpay.entity_id_mada'),
            'applepay' => config('hyperpay.entity_id_applepay'),
            'stcpay'   => config('hyperpay.entity_id_stcpay'),
        ];
    }

    public function initiatePayment($amount, $paymentMethod, $customerData)
    {
        if (!isset($this->entities[$paymentMethod])) {
            return ['error' => 'Unsupported payment method'];
        }

        $entityId = $this->entities[$paymentMethod];
        $url = "{$this->baseUrl}v1/checkouts";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->asForm()->post($url, [
            'entityId'               => $entityId,
            'amount'                 => number_format($amount, 2, '.', ''),
            'currency'               => $this->currency,
            'paymentType'            => 'DB',
            'merchantTransactionId'  => uniqid(),
            'customer.email'         => $customerData['email'] ?? null,
            'billing.street1'        => $customerData['street'] ?? null,
            'billing.city'           => $customerData['city'] ?? null,
            'billing.state'          => $customerData['state'] ?? null,
            'billing.country'        => $customerData['country'] ?? null,
            'billing.postcode'       => $customerData['postcode'] ?? null,
            'customer.givenName'     => $customerData['first_name'] ?? null,
            'customer.surname'       => $customerData['last_name'] ?? null,
            'testMode'                  => 'EXTERNAL',
            'customParameters[3DS2_enrolled]' => 'true',
        ]);

        return $response->json();
    }

    public function getPaymentStatus($paymentTransactionId, $paymentMethod)
    {
        if (!isset($this->entities[$paymentMethod])) {
            return response()->json(['error' => 'Unsupported payment method'], 400);
        }

        $entityId = $this->entities[$paymentMethod];
        $url = "{$this->baseUrl}v1/checkouts/{$paymentTransactionId}/payment";

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->get($url, [
            'entityId' => $entityId,
        ]);
    }
}