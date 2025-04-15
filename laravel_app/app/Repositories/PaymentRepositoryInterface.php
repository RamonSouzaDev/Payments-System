<?php

namespace App\Repositories;

interface PaymentRepositoryInterface extends RepositoryInterface
{
    public function findByCustomerId($customerId);
    public function findByExternalId($externalId);
    public function updateStatus($id, $status);
    public function getByPaymentMethod($method);
}