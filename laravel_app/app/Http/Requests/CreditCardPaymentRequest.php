<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreditCardPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cpf_cnpj' => 'required|string|min:11|max:18',
            'phone' => 'required|string|max:20',
            'value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'card_holder_name' => 'required|string|max:255',
            'card_number' => 'required|string|min:13|max:16',
            'card_expiry_month' => 'required|string|size:2',
            'card_expiry_year' => 'required|string|size:2',
            'card_ccv' => 'required|string|min:3|max:4',
            'holder_name' => 'required|string|max:255',
            'holder_email' => 'required|email|max:255',
            'holder_cpf_cnpj' => 'required|string|min:11|max:18',
            'holder_postal_code' => 'required|string|max:20',
            'holder_address_number' => 'required|string|max:20',
            'holder_address_complement' => 'nullable|string|max:255',
            'holder_phone' => 'required|string|max:20',
        ];
    }
}