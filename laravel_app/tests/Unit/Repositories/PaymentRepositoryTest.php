<?php

namespace Tests\Unit\Repositories;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Customer;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PaymentRepository(new Payment());
    }

    /** @test */
    public function it_can_create_a_payment()
    {
        $customer = Customer::factory()->create();

        $data = [
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
            'status' => PaymentStatusEnum::PENDING->value,
            'external_id' => 'pay_123',
        ];

        $payment = $this->repository->create($data);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals($customer->id, $payment->customer_id);
        $this->assertEquals(PaymentMethodEnum::BOLETO, $payment->payment_method);
        $this->assertEquals(100.00, $payment->value);
        $this->assertEquals(PaymentStatusEnum::PENDING, $payment->status);
        $this->assertEquals('pay_123', $payment->external_id);
    }

    /** @test */
    public function it_can_find_a_payment_by_id()
    {
        $customer = Customer::factory()->create();
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
        ]);

        $foundPayment = $this->repository->find($payment->id);

        $this->assertInstanceOf(Payment::class, $foundPayment);
        $this->assertEquals($payment->id, $foundPayment->id);
        $this->assertEquals(PaymentMethodEnum::BOLETO, $foundPayment->payment_method);
    }

    /** @test */
    public function it_can_update_a_payment()
    {
        $customer = Customer::factory()->create();
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
            'status' => PaymentStatusEnum::PENDING->value,
        ]);

        $updatedPayment = $this->repository->update($payment->id, [
            'status' => PaymentStatusEnum::CONFIRMED->value,
            'invoice_url' => 'https://example.com/invoice',
        ]);

        $this->assertEquals(PaymentStatusEnum::CONFIRMED, $updatedPayment->status);
        $this->assertEquals('https://example.com/invoice', $updatedPayment->invoice_url);
    }

    /** @test */
    public function it_can_delete_a_payment()
    {
        $customer = Customer::factory()->create();
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->repository->delete($payment->id);

        $this->assertDatabaseMissing('payments', [
            'id' => $payment->id,
        ]);
    }

    /** @test */
    public function it_can_find_payments_by_customer_id()
    {
        $customer = Customer::factory()->create();
        
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
        ]);
        
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::PIX->value,
        ]);

        $payments = $this->repository->findByCustomerId($customer->id);

        $this->assertCount(2, $payments);
        $this->assertEquals($customer->id, $payments[0]->customer_id);
    }

    /** @test */
    public function it_can_find_a_payment_by_external_id()
    {
        $customer = Customer::factory()->create();
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'external_id' => 'pay_123',
        ]);

        $payment = $this->repository->findByExternalId('pay_123');

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('pay_123', $payment->external_id);
    }

    /** @test */
    public function it_can_update_payment_status()
    {
        $customer = Customer::factory()->create();
        $payment = Payment::factory()->create([
            'customer_id' => $customer->id,
            'status' => PaymentStatusEnum::PENDING->value,
        ]);

        $updatedPayment = $this->repository->updateStatus($payment->id, PaymentStatusEnum::CONFIRMED->value);

        $this->assertEquals(PaymentStatusEnum::CONFIRMED, $updatedPayment->status);
    }

    /** @test */
    public function it_can_get_payments_by_payment_method()
    {
        $customer = Customer::factory()->create();
        
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
        ]);
        
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::BOLETO->value,
        ]);
        
        Payment::factory()->create([
            'customer_id' => $customer->id,
            'payment_method' => PaymentMethodEnum::PIX->value,
        ]);

        $boletoPayments = $this->repository->getByPaymentMethod(PaymentMethodEnum::BOLETO->value);
        $pixPayments = $this->repository->getByPaymentMethod(PaymentMethodEnum::PIX->value);

        $this->assertCount(2, $boletoPayments);
        $this->assertCount(1, $pixPayments);
    }
}