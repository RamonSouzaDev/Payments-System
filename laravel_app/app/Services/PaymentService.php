<?php

namespace App\Services;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessBoletoPayment;
use App\Jobs\ProcessCreditCardPayment;
use App\Jobs\ProcessPixPayment;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentService implements PaymentServiceInterface
{
    protected $asaasService;
    protected $customerRepository;
    protected $paymentRepository;

    public function __construct(
        AsaasServiceInterface $asaasService,
        CustomerRepositoryInterface $customerRepository,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->asaasService = $asaasService;
        $this->customerRepository = $customerRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function processPayment(array $data)
    {
        try {
            // First, check if customer already exists or create a new one
            $customer = $this->customerRepository->findByCpfCnpj($data['cpf_cnpj']);
            
            if (!$customer) {
                // Create customer in Asaas
                $asaasCustomer = $this->asaasService->createCustomer([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'cpf_cnpj' => $data['cpf_cnpj'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'address_number' => $data['address_number'] ?? null,
                    'address_complement' => $data['address_complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                ]);
                
                // Create customer in database
                $customer = $this->customerRepository->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'cpf_cnpj' => $data['cpf_cnpj'],
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'address_number' => $data['address_number'] ?? null,
                    'address_complement' => $data['address_complement'] ?? null,
                    'province' => $data['province'] ?? null,
                    'postal_code' => $data['postal_code'] ?? null,
                    'external_id' => $asaasCustomer['id'],
                ]);
            }
            
            // Prepare payment data for Asaas
            $paymentData = [
                'customer_id' => $customer->external_id,
                'payment_method' => $data['payment_method'],
                'value' => $data['value'],
                'due_date' => $data['due_date'] ?? date('Y-m-d'),
                'description' => $data['description'] ?? 'Payment',
            ];
            
            // Add credit card data if payment method is CREDIT_CARD
            if ($data['payment_method'] === PaymentMethodEnum::CREDIT_CARD->value) {
                $paymentData = array_merge($paymentData, [
                    'card_holder_name' => $data['card_holder_name'],
                    'card_number' => $data['card_number'],
                    'card_expiry_month' => $data['card_expiry_month'],
                    'card_expiry_year' => $data['card_expiry_year'],
                    'card_ccv' => $data['card_ccv'],
                    'holder_name' => $data['holder_name'],
                    'holder_email' => $data['holder_email'],
                    'holder_cpf_cnpj' => $data['holder_cpf_cnpj'],
                    'holder_postal_code' => $data['holder_postal_code'],
                    'holder_address_number' => $data['holder_address_number'],
                    'holder_address_complement' => $data['holder_address_complement'] ?? null,
                    'holder_phone' => $data['holder_phone'],
                ]);
            }
            
            // Create payment in Asaas
            $asaasPayment = $this->asaasService->createPayment($paymentData);
            
            // Save payment information in database
            $payment = $this->paymentRepository->create([
                'customer_id' => $customer->id,
                'payment_method' => $data['payment_method'],
                'value' => $data['value'],
                'status' => $asaasPayment['status'],
                'external_id' => $asaasPayment['id'],
                'due_date' => $data['due_date'] ?? date('Y-m-d'),
            ]);
            
            // Dispatch appropriate job based on payment method
            switch ($data['payment_method']) {
                case PaymentMethodEnum::BOLETO->value:
                    ProcessBoletoPayment::dispatch($payment, $data);
                    break;
                    
                case PaymentMethodEnum::CREDIT_CARD->value:
                    ProcessCreditCardPayment::dispatch($payment, $data);
                    break;
                    
                case PaymentMethodEnum::PIX->value:
                    ProcessPixPayment::dispatch($payment, $data);
                    break;
            }
            
            return $payment;
        } catch (Exception $e) {
            Log::error('Error processing payment: ' . $e->getMessage());
            
            // If a payment was created in our database, update with error
            if (isset($payment) && $payment) {
                $this->paymentRepository->update($payment->id, [
                    'status' => PaymentStatusEnum::ERROR->value,
                    'error_message' => $e->getMessage()
                ]);
            }
            
            throw $e;
        }
    }

    public function getPaymentDetails($paymentId)
    {
        $payment = $this->paymentRepository->find($paymentId);
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        // Get updated status from Asaas
        try {
            $status = $this->asaasService->getPaymentStatus($payment->external_id);
            if ($status !== $payment->status->value) {
                $payment = $this->paymentRepository->updateStatus($payment->id, $status);
            }
        } catch (Exception $e) {
            Log::error("Error updating payment status: " . $e->getMessage());
        }
        
        return $payment;
    }

    public function getBankSlipUrl($paymentId)
    {
        $payment = $this->paymentRepository->find($paymentId);
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        if ($payment->payment_method !== PaymentMethodEnum::BOLETO) {
            throw new Exception("Payment is not a bank slip");
        }
        
        if (!$payment->invoice_url) {
            try {
                $invoiceUrl = $this->asaasService->getPaymentLink($payment->external_id);
                $this->paymentRepository->update($payment->id, ['invoice_url' => $invoiceUrl]);
                return $invoiceUrl;
            } catch (Exception $e) {
                Log::error("Error getting bank slip URL: " . $e->getMessage());
                throw $e;
            }
        }
        
        return $payment->invoice_url;
    }
    
    public function getPixData($paymentId)
    {
        $payment = $this->paymentRepository->find($paymentId);
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        if ($payment->payment_method !== PaymentMethodEnum::PIX) {
            throw new Exception("Payment is not a PIX");
        }
        
        if (!$payment->pix_code || !$payment->pix_qrcode) {
            try {
                $pixData = $this->asaasService->getPixQrCode($payment->external_id);
                $this->paymentRepository->update($payment->id, [
                    'pix_code' => $pixData['encodedImage'] ?? null,
                    'pix_qrcode' => $pixData['payload'] ?? null,
                ]);
                
                return [
                    'qrcode' => $pixData['encodedImage'] ?? null,
                    'code' => $pixData['payload'] ?? null
                ];
            } catch (Exception $e) {
                Log::error("Error getting PIX data: " . $e->getMessage());
                throw $e;
            }
        }
        
        return [
            'qrcode' => $payment->pix_code,
            'code' => $payment->pix_qrcode
        ];
    }

    public function handlePaymentCallback(array $data)
    {
        $payment = $this->paymentRepository->findByExternalId($data['payment']['id']);
        
        if (!$payment) {
            throw new Exception("Payment not found");
        }
        
        $this->paymentRepository->updateStatus($payment->id, $data['payment']['status']);
        
        return $payment;
    }
}