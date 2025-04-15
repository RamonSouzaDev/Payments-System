<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CustomerResource",
 *     title="Customer Resource",
 *     description="Recurso de Cliente",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", example="joao@exemplo.com"),
 *     @OA\Property(property="cpf_cnpj", type="string", example="12345678901"),
 *     @OA\Property(property="phone", type="string", example="11987654321"),
 *     @OA\Property(property="external_id", type="string", example="cus_12345678"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-15T14:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-15T14:30:00Z")
 * )
 */
class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'cpf_cnpj' => $this->cpf_cnpj,
            'phone' => $this->phone,
            'address' => $this->address,
            'address_number' => $this->address_number,
            'address_complement' => $this->address_complement,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            'external_id' => $this->external_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}