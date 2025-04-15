<?php

namespace App\Jobs;

use App\Repositories\PaymentRepositoryInterface;
use App\Services\AsaasServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCreditCardPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payment;
    protected $paymentData;

    /**
     * Create a new job instance.
     */
    public function __construct($payment, array $paymentData)
    {
        $this->payment = $payment;
        $this->paymentData = $paymentData;
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasServiceInterface $asaasService, PaymentRepositoryInterface $paymentRepository): void
    {
        try {
            // Para pagamentos com cartão de crédito, geralmente não há etapas adicionais
            // pois a confirmação é instantânea, mas podemos atualizar o status
            $status = $asaasService->getPaymentStatus($this->payment->external_id);
            
            if ($status !== $this->payment->status) {
                $paymentRepository->updateStatus($this->payment->id, $status);
            }
            
            Log::info("Cartão de crédito processado com sucesso: ID {$this->payment->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar cartão de crédito: " . $e->getMessage());
            
            // Se o Job falhar, podemos marcar o pagamento como tendo um erro
            $paymentRepository->update($this->payment->id, [
                'status' => 'ERROR',
                'error_message' => $e->getMessage()
            ]);
            
            // Propagar a exceção para que o job possa ser retentado
            throw $e;
        }
    }
}