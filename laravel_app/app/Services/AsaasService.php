<?php

namespace App\Services;

use App\Enums\PaymentMethodEnum;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;

class AsaasService implements AsaasServiceInterface
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.key');
        $this->baseUrl = config('services.asaas.url');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function createCustomer(array $data)
    {
        try {
            $response = $this->client->post('customers', [
                'json' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'cpfCnpj' => $data['cpf_cnpj'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'addressNumber' => $data['address_number'] ?? null,
                    'complement' => $data['address_complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postalCode' => $data['postal_code'] ?? null,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Error creating customer in Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error($e->getResponse()->getBody()->getContents());
            }
            throw $e;
        }
    }

    public function createPayment(array $data)
    {
        try {
            $paymentData = [
                'customer' => $data['customer_id'],
                'billingType' => $data['payment_method'],
                'value' => $data['value'],
                'dueDate' => $data['due_date'] ?? date('Y-m-d'),
                'description' => $data['description'] ?? 'Payment',
            ];

            // Add credit card data if payment method is CREDIT_CARD
            if ($data['payment_method'] === PaymentMethodEnum::CREDIT_CARD->value) {
                $paymentData['creditCard'] = [
                    'holderName' => $data['card_holder_name'],
                    'number' => $data['card_number'],
                    'expiryMonth' => $data['card_expiry_month'],
                    'expiryYear' => $data['card_expiry_year'],
                    'ccv' => $data['card_ccv'],
                ];
                $paymentData['creditCardHolderInfo'] = [
                    'name' => $data['holder_name'],
                    'email' => $data['holder_email'],
                    'cpfCnpj' => $data['holder_cpf_cnpj'],
                    'postalCode' => $data['holder_postal_code'],
                    'addressNumber' => $data['holder_address_number'],
                    'addressComplement' => $data['holder_address_complement'] ?? null,
                    'phone' => $data['holder_phone'],
                ];
            }

            // For PIX payment
            if ($data['payment_method'] === PaymentMethodEnum::PIX->value) {
                $paymentData['dueDate'] = date('Y-m-d');
            }

            $response = $this->client->post('payments', [
                'json' => $paymentData
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Error creating payment in Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error($e->getResponse()->getBody()->getContents());
            }
            throw $e;
        }
    }

    public function getPaymentStatus($paymentId)
    {
        try {
            $response = $this->client->get("payments/{$paymentId}");
            $payment = json_decode($response->getBody()->getContents(), true);
            return $payment['status'];
        } catch (RequestException $e) {
            Log::error('Error getting payment status from Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error($e->getResponse()->getBody()->getContents());
            }
            throw $e;
        }
    }

    public function getPaymentLink($paymentId)
    {
        try {
            $response = $this->client->get("payments/{$paymentId}/identificationField");
            $data = json_decode($response->getBody()->getContents(), true);
            return $data['invoiceUrl'] ?? null;
        } catch (RequestException $e) {
            Log::error('Error getting payment link from Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error($e->getResponse()->getBody()->getContents());
            }
            throw $e;
        }
    }

    public function getPixQrCode($paymentId)
    {
        try {
            $response = $this->client->get("payments/{$paymentId}/pixQrCode");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Error getting PIX QR code from Asaas: ' . $e->getMessage());
            if ($e->hasResponse()) {
                Log::error($e->getResponse()->getBody()->getContents());
            }
            throw $e;
        }
    }
}