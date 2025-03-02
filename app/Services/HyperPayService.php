<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HyperPayService
{
    private $baseUrl;
    private $accessToken;
    private $entityVisa;
    private $entityMada;
    private $currency;

    public function __construct()
    {
        $this->baseUrl = config('hyperpay.base_url');
        $this->accessToken = config('hyperpay.access_token');
        $this->entityVisa = config('hyperpay.entity_id_visa_master');
        $this->entityMada = config('hyperpay.entity_id_mada');
        $this->currency = config('hyperpay.currency');
    }
    public function initiatePayment($amount, $paymentMethod, $customerData)
    {
        $entityId = ($paymentMethod === 'mada') ? $this->entityMada : $this->entityVisa;
        $url = $this->baseUrl . 'v1/checkouts';

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->asForm()->post($url, [
            'entityId'                          => $entityId,
            'amount'                            => number_format($amount, 2, '.', ''),
            'currency'                          => $this->currency,
            'paymentType'                       => 'DB',
            // إزالة testMode لأنه غير مسموح في البيئة الحية
            'customParameters[3DS2_enrolled]'   => 'true',
            'merchantTransactionId'             => uniqid(),
            'customer.email'                    => $customerData['email'],
            'billing.street1'                   => $customerData['street'],
            'billing.city'                      => $customerData['city'],
            'billing.state'                     => $customerData['state'],
            'billing.country'                   => $customerData['country'],
            'billing.postcode'                  => $customerData['postcode'],
            'customer.givenName'                => $customerData['first_name'],
            'customer.surname'                  => $customerData['last_name'],
        ]);

        return $response->json();
    }


    public function getPaymentStatus($checkoutId)
    {
        $url = "{$this->baseUrl}v1/checkouts/{$checkoutId}/payment";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->get($url, [
            'entityId' => $this->entityVisa,
        ]);

        return $response->json();
    }
}
