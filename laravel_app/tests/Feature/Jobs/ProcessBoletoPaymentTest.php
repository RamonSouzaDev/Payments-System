<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessBoletoPayment;
use App\Models\Payment;
use App\Repositories\PaymentRepositoryInterface;
use App\Services\AsaasServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProcessBoletoPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_processes_boleto_payment()
    {
        // Configurar mocks
        $asaasService = Mockery::mock(AsaasServiceInterface::class);
        $paymentRepository = Mockery::mock(PaymentRepositoryInterface::class);
        
        $this->app->instance(AsaasServiceInterface::class, $asaasService);
        $this->app->instance(PaymentRepositoryInterface::class, $paymentRepository);
        
        // Criar um pagamento de teste
        $payment = new Payment();
        $payment->id = 1;
        $payment->external_id = 'pay_123456';
        $payment->payment_method = 'BOLETO';
        
        // Configurar expectativas
        $asaasService->shouldReceive('getPaymentLink')
            ->once()
            ->with('pay_123456')
            ->andReturn('https://example.com/boleto');
        
        $paymentRepository->shouldReceive('update')
            ->once()
            ->with(1, ['invoice_url' => 'https://example.com/boleto'])
            ->andReturn($payment);
        
        // Executar o job
        $job = new ProcessBoletoPayment($payment, []);
        $job->handle($asaasService, $paymentRepository);
        
        // Verificar que tudo ocorreu como esperado
        $this->assertTrue(true); // Se chegou aqui, o teste passou
    }
}