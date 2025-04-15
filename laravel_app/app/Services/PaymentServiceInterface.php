<?php

namespace App\Services;

interface PaymentServiceInterface
{
    public function processPayment(array $data);
    public function getPaymentDetails($paymentId);
    public function getBankSlipUrl($paymentId);
    public function getPixData($paymentId);
    public function handlePaymentCallback(array $data);
}