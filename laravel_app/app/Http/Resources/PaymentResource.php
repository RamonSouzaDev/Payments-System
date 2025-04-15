<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PaymentResource",
 *     title="Payment Resource",
 *     description="Recurso de Pagamento",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="customer", ref="#/components/schemas/CustomerResource"),
 *     @OA\Property(property="payment_method", type="string", enum={"BOLETO", "CREDIT_CARD", "PIX"}, example="BOLETO"),
 *     @OA\Property(property="payment_method_formatted", type="string", example="Boleto Bancário"),
 *     @OA\Property(property="value", type="number", format="float", example=100.00),
 *     @OA\Property(property="value_formatted", type="string", example="R$ 100,00"),
 *     @OA\Property(property="status", type="string", enum={"PENDING", "CONFIRMED", "RECEIVED", "DECLINED", "FAILED", "REFUNDED", "CANCELED", "ERROR"}, example="PENDING"),
 *     @OA\Property(property="status_formatted", type="string", example="Pendente"),
 *     @OA\Property(property="external_id", type="string", example="pay_12345678"),
 *     @OA\Property(property="invoice_url", type="string", example="https://sandbox.asaas.com/i/12345678"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-12-31"),
 *     @OA\Property(property="due_date_formatted", type="string", example="31/12/2025"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-15T14:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-15T14:30:00Z")
 * )
 */
class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'payment_method' => $this->payment_method,
            'payment_method_formatted' => $this->getPaymentMethodFormatted(),
            'value' => $this->value,
            'value_formatted' => 'R$ ' . number_format($this->value, 2, ',', '.'),
            'status' => $this->status,
            'status_formatted' => $this->getStatusFormatted(),
            'external_id' => $this->external_id,
            'invoice_url' => $this->when($this->payment_method === 'BOLETO', $this->invoice_url),
            'pix_code' => $this->when($this->payment_method === 'PIX', $this->pix_code),
            'pix_qrcode' => $this->when($this->payment_method === 'PIX', $this->pix_qrcode),
            'due_date' => $this->due_date,
            'due_date_formatted' => $this->due_date ? $this->due_date->format('d/m/Y') : null,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get formatted payment method name.
     *
     * @return string
     */
    private function getPaymentMethodFormatted()
    {
        $methods = [
            'BOLETO' => 'Boleto Bancário',
            'CREDIT_CARD' => 'Cartão de Crédito',
            'PIX' => 'PIX',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get formatted status.
     *
     * @return string
     */
    private function getStatusFormatted()
    {
        $statuses = [
            'PENDING' => 'Pendente',
            'CONFIRMED' => 'Confirmado',
            'RECEIVED' => 'Recebido',
            'DECLINED' => 'Recusado',
            'FAILED' => 'Falhou',
            'REFUNDED' => 'Reembolsado',
            'CANCELED' => 'Cancelado',
            'ERROR' => 'Erro',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}