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
            'visa'       => config('hyperpay.entity_id_visa_master'),
            'mastercard' => config('hyperpay.entity_id_visa_master'),
            'mada'       => config('hyperpay.entity_id_mada'),
            'applepay'   => config('hyperpay.entity_id_apple_pay'),
        ];

        \Log::info('HyperPayService initialized', [
            'base_url' => $this->baseUrl,
            'access_token' => $this->accessToken,
            'currency' => $this->currency,
            'entities' => $this->entities,
        ]);
    }

    public function initiatePayment($amount, $paymentMethod, $customerData)
    {
        $paymentMethod = strtolower($paymentMethod);


        if (!isset($this->entities[$paymentMethod])) {
            \Log::error('Unsupported payment method in HyperPayService', [
                'paymentMethod' => $paymentMethod,
                'available_methods' => array_keys($this->entities),
            ]);
            return ['error' => 'Unsupported payment method'];
        }

        $entityId = $this->entities[$paymentMethod];

        \Log::info('Entity ID for payment method', [
            'paymentMethod' => $paymentMethod,
            'entityId' => $entityId,
        ]);

        if (empty($entityId)) {
            \Log::error('Entity ID is missing for payment method', [
                'paymentMethod' => $paymentMethod,
                'entityId' => $entityId,
                'config' => config('hyperpay'),
            ]);
            return ['error' => 'Entity ID is missing for the selected payment method'];
        }

        $url = "{$this->baseUrl}v1/checkouts";

        $email = $customerData['email'] ?? 'test@example.com';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            \Log::warning('Invalid or missing customer email', ['email' => $email]);
            $email = 'test@example.com';
        }

        $postData = [
            'entityId'               => $entityId,
            'amount'                 => number_format($amount, 2, '.', ''),
            'currency'               => $this->currency,
            'paymentType'            => 'DB',
            'merchantTransactionId'  => uniqid(),
            'customer.email'         => $email,
            'billing.street1'        => $customerData['street'] ?? 'NA',
            'billing.city'           => $customerData['city'] ?? 'NA',
            'billing.state'          => $customerData['state'] ?? 'NA',
            'billing.country'        => $customerData['country'] ?? 'NA',
            'billing.postcode'       => $customerData['postcode'] ?? 'NA',
            'customer.givenName'     => $customerData['first_name'] ?? 'Unknown',
            'customer.surname'       => $customerData['last_name'] ?? 'Unknown',
            'testMode'               => 'EXTERNAL',
            'customParameters[3DS2_enrolled]' => 'true',
        ];

        if ($paymentMethod === 'applepay') {
            $postData['paymentBrand'] = 'APPLEPAY';
            $postData['shopperResultUrl'] = route('payment.applepay.callback');
        }

        \Log::info('Initiating HyperPay payment', [
            'url' => $url,
            'postData' => $postData,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->timeout(30)->asForm()->post($url, $postData);

        $responseBody = $response->json();
        \Log::info('HyperPay payment response', [
            'status' => $response->status(),
            'body' => $responseBody,
        ]);


        if ($response->failed()) {
            $errorDetails = $responseBody['result'] ?? [];
            $parameterErrors = $errorDetails['parameterErrors'] ?? [];
            $errorMessage = $errorDetails['description'] ?? 'Unknown error';

            \Log::error('HyperPay payment failed', [
                'error' => $errorMessage,
                'parameterErrors' => $parameterErrors,
            ]);

            return [
                'error' => $errorMessage,
                'details' => $responseBody,
            ];
        }

        return $responseBody;
    }

    public function getPaymentStatus($checkoutId, $paymentMethod)
    {
        try {
            $paymentMethod = strtolower($paymentMethod);
            if (!isset($this->entities[$paymentMethod])) {
                return Http::response(['error' => 'Unsupported payment method'], 400);
            }

            $entityId = $this->entities[$paymentMethod];
            if (empty($entityId)) {
                \Log::error('Entity ID is missing for payment method in getPaymentStatus', [
                    'paymentMethod' => $paymentMethod,
                    'entityId' => $entityId,
                ]);
                return Http::response(['error' => 'Entity ID is missing for the selected payment method'], 500);
            }

            $url = "{$this->baseUrl}v1/checkouts/{$checkoutId}/payment";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($url, ['entityId' => $entityId]);

            return $response;

        } catch (\Exception $e) {
            \Log::error('HyperPay API Exception', ['error' => $e->getMessage()]);
            return Http::response(['error' => 'Unexpected error occurred', 'exception' => $e->getMessage()], 500);
        }
    }
}