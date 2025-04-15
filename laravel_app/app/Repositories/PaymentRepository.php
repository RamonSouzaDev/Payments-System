<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Enums\PaymentStatusEnum;

class PaymentRepository implements PaymentRepositoryInterface
{
    protected $model;

    public function __construct(Payment $payment)
    {
        $this->model = $payment;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }

    public function findByCustomerId($customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }

    public function findByExternalId($externalId)
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    public function updateStatus($id, $status)
    {
        $payment = $this->find($id);
    
        $enumStatus = PaymentStatusEnum::tryFrom($status);
    
        if (!$enumStatus) {
            throw new \InvalidArgumentException("Invalid payment status: $status");
        }
    
        $payment->status = $enumStatus;
        $payment->save();
    
        return $payment;
    }
    
    public function getByPaymentMethod($method)
    {
        return $this->model->where('payment_method', $method)->get();
    }
}