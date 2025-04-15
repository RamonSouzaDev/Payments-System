<?php

namespace App\Services;

interface AsaasServiceInterface
{
    public function createCustomer(array $data);
    public function createPayment(array $data);
    public function getPaymentStatus($paymentId);
    public function getPaymentLink($paymentId);
    public function getPixQrCode($paymentId);
}