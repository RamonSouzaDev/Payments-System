<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodEnum;
use App\Services\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ThankYouController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Display the thank you page with payment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $paymentId = $request->query('payment');
            
            if (!$paymentId) {
                return redirect()->route('payments.index')
                    ->withErrors(['message' => 'Pagamento não encontrado']);
            }
            
            $payment = $this->paymentService->getPaymentDetails($paymentId);
            $paymentData = [];
            
            // Get additional payment data based on payment method
            if ($payment->payment_method === PaymentMethodEnum::BOLETO) {
                $paymentData['invoice_url'] = $this->paymentService->getBankSlipUrl($payment->id);
            } elseif ($payment->payment_method === PaymentMethodEnum::PIX) {
                try {
                    $pixData = $this->paymentService->getPixData($payment->id);
                    Log::debug('PIX Data: ' . json_encode($pixData)); // Para debug
                    $paymentData['pix_qrcode'] = $pixData['qrcode'];
                    $paymentData['pix_code'] = $pixData['code'];
                } catch (Exception $e) {
                    Log::error('Error retrieving PIX data: ' . $e->getMessage());
                    // Continue sem os dados do PIX
                }
            }
            
            return view('thank-you', [
                'payment' => $payment,
                'paymentData' => $paymentData
            ]);
        } catch (Exception $e) {
            Log::error('Error in thank you page: ' . $e->getMessage());
            return redirect()->route('payments.index')
                ->withErrors(['message' => 'Erro ao recuperar detalhes do pagamento: ' . $e->getMessage()]);
        }
    }
}