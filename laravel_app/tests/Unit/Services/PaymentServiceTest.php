<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Jobs\ProcessBoletoPayment;
use App\Jobs\ProcessCreditCardPayment;
use App\Jobs\ProcessPixPayment;
use App\Models\Customer;
use App\Models\Payment;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\PaymentRepositoryInterface;
use App\Services\AsaasServiceInterface;
use App\Services\PaymentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;
    private $asaasService;
    private $customerRepository;
    private $paymentRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fingir a fila (para evitar que os jobs sejam despachados realmente)
        Queue::fake();
        
        $this->asaasService = Mockery::mock(AsaasServiceInterface::class);
        $this->customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $this->paymentRepository = Mockery::mock(PaymentRepositoryInterface::class);
        
        $this->service = new PaymentService(
            $this->asaasService,
            $this->customerRepository,
            $this->paymentRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_should_create_a_new_customer_if_none_exists()
    {
        // Set up the test data
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
        ];
        
        // Set up the mocks
        $this->customerRepository->shouldReceive('findByCpfCnpj')
            ->with('12345678901')
            ->once()
            ->andReturn(null);
        
        $this->asaasService->shouldReceive('createCustomer')
            ->once()
            ->andReturn(['id' => 'cus_000001']);
        
        $customer = new Customer();
        $customer->id = 1;
        $customer->external_id = 'cus_000001';
        
        $this->customerRepository->shouldReceive('create')
            ->once()
            ->andReturn($customer);
        
        $this->asaasService->shouldReceive('createPayment')
            ->once()
            ->andReturn([
                'id' => 'pay_000001',
                'status' => PaymentStatusEnum::PENDING->value,
            ]);
        
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->status = PaymentStatusEnum::PENDING;
        $payment->payment_method = PaymentMethodEnum::BOLETO;
        
        $this->paymentRepository->shouldReceive('create')
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->processPayment($customerData);
        
        // Verify that the job was dispatched
        Queue::assertPushed(ProcessBoletoPayment::class, function ($job) {
            return $job instanceof ProcessBoletoPayment;
        });
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals('pay_000001', $result->external_id);
    }

    /** @test */
    public function it_should_use_existing_customer_if_found()
    {
        // Set up the test data
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
        ];
        
        // Create an existing customer
        $customer = new Customer();
        $customer->id = 1;
        $customer->external_id = 'cus_000001';
        
        // Set up the mocks
        $this->customerRepository->shouldReceive('findByCpfCnpj')
            ->with('12345678901')
            ->once()
            ->andReturn($customer);
        
        $this->asaasService->shouldReceive('createPayment')
            ->once()
            ->andReturn([
                'id' => 'pay_000001',
                'status' => PaymentStatusEnum::PENDING->value,
            ]);
        
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->status = PaymentStatusEnum::PENDING;
        $payment->payment_method = PaymentMethodEnum::BOLETO;
        
        $this->paymentRepository->shouldReceive('create')
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->processPayment($customerData);
        
        // Verify that the job was dispatched
        Queue::assertPushed(ProcessBoletoPayment::class, function ($job) {
            return $job instanceof ProcessBoletoPayment;
        });
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals('pay_000001', $result->external_id);
    }

    /** @test */
    public function it_should_process_a_credit_card_payment()
    {
        // Set up the test data
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'payment_method' => PaymentMethodEnum::CREDIT_CARD->value,
            'value' => 150.00,
            'card_holder_name' => 'Test User',
            'card_number' => '4111111111111111',
            'card_expiry_month' => '12',
            'card_expiry_year' => '25',
            'card_ccv' => '123',
            'holder_name' => 'Test User',
            'holder_email' => 'test@example.com',
            'holder_cpf_cnpj' => '12345678901',
            'holder_postal_code' => '12345-678',
            'holder_address_number' => '123',
            'holder_phone' => '1234567890',
        ];
        
        // Create an existing customer
        $customer = new Customer();
        $customer->id = 1;
        $customer->external_id = 'cus_000001';
        
        // Set up the mocks
        $this->customerRepository->shouldReceive('findByCpfCnpj')
            ->with('12345678901')
            ->once()
            ->andReturn($customer);
        
        $this->asaasService->shouldReceive('createPayment')
            ->once()
            ->andReturn([
                'id' => 'pay_000002',
                'status' => PaymentStatusEnum::CONFIRMED->value,
            ]);
        
        $payment = new Payment();
        $payment->id = 2;
        $payment->external_id = 'pay_000002';
        $payment->status = PaymentStatusEnum::CONFIRMED;
        $payment->payment_method = PaymentMethodEnum::CREDIT_CARD;
        
        $this->paymentRepository->shouldReceive('create')
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->processPayment($customerData);
        
        // Verify that the job was dispatched
        Queue::assertPushed(ProcessCreditCardPayment::class, function ($job) {
            return $job instanceof ProcessCreditCardPayment;
        });
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals('pay_000002', $result->external_id);
        $this->assertEquals(PaymentStatusEnum::CONFIRMED, $result->status);
    }

    /** @test */
    public function it_should_process_a_pix_payment()
    {
        // Set up the test data
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'payment_method' => PaymentMethodEnum::PIX->value,
            'value' => 200.00,
        ];
        
        // Create an existing customer
        $customer = new Customer();
        $customer->id = 1;
        $customer->external_id = 'cus_000001';
        
        // Set up the mocks
        $this->customerRepository->shouldReceive('findByCpfCnpj')
            ->with('12345678901')
            ->once()
            ->andReturn($customer);
        
        $this->asaasService->shouldReceive('createPayment')
            ->once()
            ->andReturn([
                'id' => 'pay_000003',
                'status' => PaymentStatusEnum::PENDING->value,
            ]);
        
        $payment = new Payment();
        $payment->id = 3;
        $payment->external_id = 'pay_000003';
        $payment->status = PaymentStatusEnum::PENDING;
        $payment->payment_method = PaymentMethodEnum::PIX;
        
        $this->paymentRepository->shouldReceive('create')
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->processPayment($customerData);
        
        // Verify that the job was dispatched
        Queue::assertPushed(ProcessPixPayment::class, function ($job) {
            return $job instanceof ProcessPixPayment;
        });
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
        $this->assertEquals('pay_000003', $result->external_id);
    }

    /** @test */
    public function it_should_handle_errors_during_payment_processing()
    {
        // Set up the test data
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
            'payment_method' => PaymentMethodEnum::BOLETO->value,
            'value' => 100.00,
        ];
        
        // Create an existing customer
        $customer = new Customer();
        $customer->id = 1;
        $customer->external_id = 'cus_000001';
        
        // Set up the mocks
        $this->customerRepository->shouldReceive('findByCpfCnpj')
            ->with('12345678901')
            ->once()
            ->andReturn($customer);
        
        $this->asaasService->shouldReceive('createPayment')
            ->once()
            ->andThrow(new Exception('API Error'));
        
        // Expect exception
        $this->expectException(Exception::class);
        
        // Call the method
        $this->service->processPayment($customerData);
    }

    /** @test */
    public function it_should_get_payment_details()
    {
        // Create a payment
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->status = PaymentStatusEnum::PENDING;
        
        // Set up the mocks
        $this->paymentRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($payment);
        
        $this->asaasService->shouldReceive('getPaymentStatus')
            ->with('pay_000001')
            ->once()
            ->andReturn(PaymentStatusEnum::CONFIRMED->value);
        
        $this->paymentRepository->shouldReceive('updateStatus')
            ->with(1, PaymentStatusEnum::CONFIRMED->value)
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->getPaymentDetails(1);
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
    }

    /** @test */
    public function it_should_get_bank_slip_url()
    {
        // Create a payment
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->payment_method = PaymentMethodEnum::BOLETO;
        $payment->invoice_url = null;
        
        // Set up the mocks
        $this->paymentRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($payment);
        
        $this->asaasService->shouldReceive('getPaymentLink')
            ->with('pay_000001')
            ->once()
            ->andReturn('https://example.com/invoice');
        
        $this->paymentRepository->shouldReceive('update')
            ->with(1, ['invoice_url' => 'https://example.com/invoice'])
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->getBankSlipUrl(1);
        
        // Check the result
        $this->assertEquals('https://example.com/invoice', $result);
    }

    /** @test */
    public function it_should_get_pix_data()
    {
        // Create a payment
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->payment_method = PaymentMethodEnum::PIX;
        $payment->pix_code = null;
        $payment->pix_qrcode = null;
        
        // Set up the mocks
        $this->paymentRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($payment);
        
        $this->asaasService->shouldReceive('getPixQrCode')
            ->with('pay_000001')
            ->once()
            ->andReturn([
                'encodedImage' => 'base64encodedimage',
                'payload' => 'pixcodetext',
            ]);
        
        $this->paymentRepository->shouldReceive('update')
            ->with(1, [
                'pix_code' => 'base64encodedimage',
                'pix_qrcode' => 'pixcodetext',
            ])
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->getPixData(1);
        
        // Check the result
        $this->assertEquals('base64encodedimage', $result['qrcode']);
        $this->assertEquals('pixcodetext', $result['code']);
    }

    /** @test */
    public function it_should_handle_payment_callback()
    {
        // Create a payment
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_000001';
        $payment->status = PaymentStatusEnum::PENDING;
        
        // Set up the mocks
        $this->paymentRepository->shouldReceive('findByExternalId')
            ->with('pay_000001')
            ->once()
            ->andReturn($payment);
        
        $this->paymentRepository->shouldReceive('updateStatus')
            ->with(1, PaymentStatusEnum::CONFIRMED->value)
            ->once()
            ->andReturn($payment);
        
        // Call the method
        $result = $this->service->handlePaymentCallback([
            'payment' => [
                'id' => 'pay_000001',
                'status' => PaymentStatusEnum::CONFIRMED->value,
            ],
        ]);
        
        // Check the result
        $this->assertInstanceOf(Payment::class, $result);
    }
}