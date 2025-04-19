<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        Log::info('HyperPayService initialized', [
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
            Log::error(__('messages.unsupported_payment_method'), [
                'paymentMethod' => $paymentMethod,
                'available_methods' => array_keys($this->entities),
            ]);
            return ['error' => __('messages.unsupported_payment_method')];
        }

        $entityId = $this->entities[$paymentMethod];

        Log::info(__('messages.entity_id_for_payment_method'), [
            'paymentMethod' => $paymentMethod,
            'entityId' => $entityId,
        ]);

        if (empty($entityId)) {
            Log::error(__('messages.entity_id_missing_for_payment_method'), [
                'paymentMethod' => $paymentMethod,
                'entityId' => $entityId,
                'config' => config('hyperpay'),
            ]);
            return ['error' => __('messages.entity_id_missing_for_payment_method')];
        }

        $url = "{$this->baseUrl}v1/checkouts";

        $email = $customerData['email'] ?? 'test@example.com';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning(__('messages.invalid_customer_email'));
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

        Log::info(__('messages.initiating_hyperpay_payment'), [
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
        Log::info(__('messages.hyperpay_payment_response'), [
            'status' => $response->status(),
            'body' => $responseBody,
        ]);


        if ($response->failed()) {
            $errorDetails = $responseBody['result'] ?? [];
            $parameterErrors = $errorDetails['parameterErrors'] ?? [];
            $errorMessage = $errorDetails['description'] ?? 'Unknown error';

            Log::error(__('messages.hyperpay_payment_failed'), [
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
                return Http::response(['error' => __('messages.unsupported_payment_method')], 400);
            }

            $entityId = $this->entities[$paymentMethod];
            if (empty($entityId)) {
                Log::error(__('messages.entity_id_missing_for_payment_method'), [
                    'paymentMethod' => $paymentMethod,
                    'entityId' => $entityId,
                ]);
                return Http::response(['error' => __('messages.entity_id_missing_for_payment_method')], 500);
            }

            $url = "{$this->baseUrl}v1/checkouts/{$checkoutId}/payment";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->get($url, ['entityId' => $entityId]);

            return $response;

        } catch (\Exception $e) {
            Log::error(__('messages.hyperpay_api_exception'), ['error' => $e->getMessage()]);
            return Http::response(['error' => __('messages.unexpected_error_occurred'), 'exception' => $e->getMessage()], 500);
        }
    }
}
