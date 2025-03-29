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

        $this->entities = [
            'visa'          => config('hyperpay.entity_id_visa_master'),
            'mastercard'    => config('hyperpay.entity_id_visa_master'),
            'mada'          => config('hyperpay.entity_id_mada'),
            'applepay'      => config('hyperpay.entity_id_apple_pay'),
        ];
    }

    public function initiatePayment($amount, $paymentMethod, $customerData)
    {
        if (!isset($this->entities[$paymentMethod])) {
            return ['error' => 'Unsupported payment method'];
        }

        $entityId = $this->entities[$paymentMethod];
        $url = "{$this->baseUrl}v1/checkouts";

        $postData = [
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
            'testMode'               => 'EXTERNAL',
            'customParameters[3DS2_enrolled]' => 'true',
        ];

        // إضافة Apple Pay كمعاملة منفصلة
        if ($paymentMethod === 'applepay') {
            $postData['paymentBrand'] = 'APPLEPAY';
            $postData['shopperResultUrl'] = route('payment.applepay.callback');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
        ])->timeout(30)->asForm()->post($url, $postData);


        return $response->json();
    }

    public function getPaymentStatus($checkoutId, $paymentMethod)
    {
        try {
            $paymentMethod = strtolower($paymentMethod);
            if (!isset($this->entities[$paymentMethod])) {
                return Http::response(['error' => 'Unsupported payment method'], 400);
            }

            $entityId = $this->entities[$paymentMethod];
            $url = "{$this->baseUrl}v1/checkouts/{$checkoutId}/payment";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($url, ['entityId' => $entityId]);

            return $response; // 🔹 إعادة `Http::Response` مباشرةً

        } catch (\Exception $e) {
            \Log::error('HyperPay API Exception', ['error' => $e->getMessage()]);
            return Http::response(['error' => 'Unexpected error occurred', 'exception' => $e->getMessage()], 500);
        }
    }
}
