<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodEnum;
use App\Http\Requests\BoletoPaymentRequest;
use App\Http\Requests\CreditCardPaymentRequest;
use App\Http\Requests\PixPaymentRequest;
use App\Services\PaymentServiceInterface;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Show the payment form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Passando os métodos de pagamento disponíveis para a view
        $paymentMethods = PaymentMethodEnum::cases();
        return view('payments.index', compact('paymentMethods'));
    }
    
    /**
     * Process a Boleto payment.
     *
     * @param  \App\Http\Requests\BoletoPaymentRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processBoleto(BoletoPaymentRequest $request)
    {
        try {
            $paymentData = $request->validated();
            $paymentData['payment_method'] = PaymentMethodEnum::BOLETO->value;
            
            $payment = $this->paymentService->processPayment($paymentData);
            
            return redirect()->route('thank-you', ['payment' => $payment->id]);
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Erro ao processar pagamento: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Process a Credit Card payment.
     *
     * @param  \App\Http\Requests\CreditCardPaymentRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processCreditCard(CreditCardPaymentRequest $request)
    {
        try {
            $paymentData = $request->validated();
            $paymentData['payment_method'] = PaymentMethodEnum::CREDIT_CARD->value;
            
            $payment = $this->paymentService->processPayment($paymentData);
            
            return redirect()->route('thank-you', ['payment' => $payment->id]);
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Erro ao processar pagamento: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Process a PIX payment.
     *
     * @param  \App\Http\Requests\PixPaymentRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPix(PixPaymentRequest $request)
    {
        try {
            $paymentData = $request->validated();

            $paymentData['payment_method'] = PaymentMethodEnum::PIX->value;
            
            $payment = $this->paymentService->processPayment($paymentData);
            
            return redirect()->route('thank-you', ['payment' => $payment->id]);
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Erro ao processar pagamento: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Generic payment processor that selects the appropriate method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request)
    {
        $paymentMethod = $request->input('payment_method');
    
        try {
            switch ($paymentMethod) {
                case PaymentMethodEnum::BOLETO->value:
                    $formRequest = app(BoletoPaymentRequest::class);
                    $validated = $formRequest->validated();
                    $validated['payment_method'] = PaymentMethodEnum::BOLETO->value;
                    break;
    
                case PaymentMethodEnum::CREDIT_CARD->value:
                    $formRequest = app(CreditCardPaymentRequest::class);
                    $validated = $formRequest->validated();
                    $validated['payment_method'] = PaymentMethodEnum::CREDIT_CARD->value;
                    break;
    
                case PaymentMethodEnum::PIX->value:
                    $formRequest = app(PixPaymentRequest::class);
                    $validated = $formRequest->validated();
                    $validated['payment_method'] = PaymentMethodEnum::PIX->value;
                    break;
    
                default:
                    return back()->withErrors(['message' => 'Método de pagamento inválido'])->withInput();
            }
    
            $payment = $this->paymentService->processPayment($validated);
    
            return redirect()->route('thank-you', ['payment' => $payment->id]);
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Erro ao processar pagamento: ' . $e->getMessage()])->withInput();
        }
    }
    
    
    /**
     * Handle webhook from Asaas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();
            
            // Validate the webhook event
            if (!isset($payload['event']) || !isset($payload['payment'])) {
                return response()->json(['error' => 'Invalid webhook payload'], 400);
            }
            
            $this->paymentService->handlePaymentCallback($payload);
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show payment details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $payment = $this->paymentService->getPaymentDetails($id);
            return view('payments.show', compact('payment'));
        } catch (Exception $e) {
            return back()->withErrors(['message' => 'Erro ao buscar pagamento: ' . $e->getMessage()]);
        }
    }
}