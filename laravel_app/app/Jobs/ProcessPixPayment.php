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

class ProcessPixPayment implements ShouldQueue
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
            // Obter os dados do PIX (QR Code e código)
            $pixData = $asaasService->getPixQrCode($this->payment->external_id);
            
            // Atualizar o pagamento com os dados do PIX
            $paymentRepository->update($this->payment->id, [
                'pix_code' => $pixData['encodedImage'] ?? null,
                'pix_qrcode' => $pixData['payload'] ?? null
            ]);
            
            Log::info("PIX processado com sucesso: ID {$this->payment->id}");
        } catch (\Exception $e) {
            Log::error("Erro ao processar PIX: " . $e->getMessage());
            
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