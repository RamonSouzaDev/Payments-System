<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentServiceInterface;
use Illuminate\Http\Request;
use App\Http\Resources\PaymentResource;
use App\Enums\PaymentMethodEnum;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API de Pagamentos Asaas",
 *     version="1.0.0",
 *     description="API para processamento de pagamentos via Asaas",
 *     @OA\Contact(
 *         email="seu-email@exemplo.com",
 *         name="Suporte API"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class PaymentApiController extends Controller
{
    protected $paymentService;
    
    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Criar um novo pagamento",
     *     description="Processa um novo pagamento através do Asaas",
     *     operationId="createPayment",
     *     tags={"Pagamentos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","cpf_cnpj","payment_method","value"},
     *             @OA\Property(property="name", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
     *             @OA\Property(property="cpf_cnpj", type="string", example="12345678901"),
     *             @OA\Property(property="phone", type="string", example="11987654321"),
     *             @OA\Property(property="payment_method", type="string", enum={"BOLETO", "CREDIT_CARD", "PIX"}, example="BOLETO"),
     *             @OA\Property(property="value", type="number", format="float", example=100.00),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="description", type="string", example="Pagamento de serviço"),
     *             @OA\Property(property="card_holder_name", type="string", example="JOAO SILVA"),
     *             @OA\Property(property="card_number", type="string", example="4111111111111111"),
     *             @OA\Property(property="card_expiry_month", type="string", example="12"),
     *             @OA\Property(property="card_expiry_year", type="string", example="25"),
     *             @OA\Property(property="card_ccv", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pagamento criado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno"
     *     )
     * )
     */
    public function store(PaymentRequest $request)
    {
        try {
            $paymentData = $request->validated();
            $payment = $this->paymentService->processPayment($paymentData);
            
            return new PaymentResource($payment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao processar pagamento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     summary="Obter detalhes de um pagamento",
     *     description="Retorna os detalhes de um pagamento específico",
     *     operationId="showPayment",
     *     tags={"Pagamentos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pagamento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do pagamento",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pagamento não encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $payment = $this->paymentService->getPaymentDetails($id);
            return new PaymentResource($payment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pagamento não encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}/status",
     *     summary="Verificar status de um pagamento",
     *     description="Retorna apenas o status atual de um pagamento",
     *     operationId="paymentStatus",
     *     tags={"Pagamentos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pagamento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status do pagamento",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="CONFIRMED")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pagamento não encontrado"
     *     )
     * )
     */
    public function status($id)
    {
        try {
            $payment = $this->paymentService->getPaymentDetails($id);
            return response()->json([
                'status' => $payment->status->value,
                'status_label' => $payment->status->getLabel()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pagamento não encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}