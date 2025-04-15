<?php

namespace Tests\Unit\Services;

use App\Services\AsaasService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use ReflectionClass;
use Tests\TestCase;

class AsaasServiceTest extends TestCase
{
    private $service;
    private $mockHandler;
    private $container = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the HTTP client
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        // Add a history middleware to inspect requests
        $history = Middleware::history($this->container);
        $handlerStack->push($history);
        
        $client = new Client(['handler' => $handlerStack]);
        
        // Create the service instance with our mocked client
        $this->service = new AsaasService();
        
        // Use reflection to set the client property
        $reflection = new ReflectionClass($this->service);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->service, $client);
    }

    /** @test */
    public function it_can_create_a_customer()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_000001',
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpfCnpj' => '12345678901',
        ])));

        // Call the method
        $customer = $this->service->createCustomer([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf_cnpj' => '12345678901',
        ]);

        // Check the response
        $this->assertEquals('cus_000001', $customer['id']);
        $this->assertEquals('Test Customer', $customer['name']);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('customers', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_create_a_boleto_payment()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'pay_000001',
            'customer' => 'cus_000001',
            'billingType' => 'BOLETO',
            'value' => 100.00,
            'status' => 'PENDING',
        ])));

        // Call the method
        $payment = $this->service->createPayment([
            'customer_id' => 'cus_000001',
            'payment_method' => 'BOLETO',
            'value' => 100.00,
            'due_date' => '2023-12-31',
        ]);

        // Check the response
        $this->assertEquals('pay_000001', $payment['id']);
        $this->assertEquals('BOLETO', $payment['billingType']);
        $this->assertEquals(100.00, $payment['value']);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('payments', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_create_a_credit_card_payment()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'pay_000002',
            'customer' => 'cus_000001',
            'billingType' => 'CREDIT_CARD',
            'value' => 150.00,
            'status' => 'CONFIRMED',
        ])));

        // Call the method
        $payment = $this->service->createPayment([
            'customer_id' => 'cus_000001',
            'payment_method' => 'CREDIT_CARD',
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
        ]);

        // Check the response
        $this->assertEquals('pay_000002', $payment['id']);
        $this->assertEquals('CREDIT_CARD', $payment['billingType']);
        $this->assertEquals(150.00, $payment['value']);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('payments', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_create_a_pix_payment()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'pay_000003',
            'customer' => 'cus_000001',
            'billingType' => 'PIX',
            'value' => 200.00,
            'status' => 'PENDING',
        ])));

        // Call the method
        $payment = $this->service->createPayment([
            'customer_id' => 'cus_000001',
            'payment_method' => 'PIX',
            'value' => 200.00,
        ]);

        // Check the response
        $this->assertEquals('pay_000003', $payment['id']);
        $this->assertEquals('PIX', $payment['billingType']);
        $this->assertEquals(200.00, $payment['value']);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('payments', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_get_payment_status()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'pay_000001',
            'status' => 'CONFIRMED',
        ])));

        // Call the method
        $status = $this->service->getPaymentStatus('pay_000001');

        // Check the response
        $this->assertEquals('CONFIRMED', $status);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('payments/pay_000001', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_get_payment_link()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'invoiceUrl' => 'https://example.com/invoice',
        ])));

        // Call the method
        $link = $this->service->getPaymentLink('pay_000001');

        // Check the response
        $this->assertEquals('https://example.com/invoice', $link);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('payments/pay_000001/identificationField', $request->getUri()->getPath());
    }

    /** @test */
    public function it_can_get_pix_qr_code()
    {
        // Set up the mock response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'encodedImage' => 'base64encodedimage',
            'payload' => 'pixcodetext',
        ])));

        // Call the method
        $pixData = $this->service->getPixQrCode('pay_000001');

        // Check the response
        $this->assertEquals('base64encodedimage', $pixData['encodedImage']);
        $this->assertEquals('pixcodetext', $pixData['payload']);
        
        // Check the request
        $this->assertCount(1, $this->container);
        $request = $this->container[0]['request'];
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('payments/pay_000001/pixQrCode', $request->getUri()->getPath());
    }
}