<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PaymentRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'cpf_cnpj' => 'required|string|min:11|max:18',
            'phone' => 'required|string|max:20',
            'payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
            'value' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'address_number' => 'nullable|string|max:20',
            'address_complement' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ];
        
        // Regras adicionais para cartão de crédito
        if ($this->input('payment_method') === PaymentMethodEnum::CREDIT_CARD->value) {
            $cardRules = [
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
            
            $rules = array_merge($rules, $cardRules);
        }
        
        // Para boleto, a data de vencimento é obrigatória
        if ($this->input('payment_method') === PaymentMethodEnum::BOLETO->value) {
            $rules['due_date'] = 'required|date|after_or_equal:today';
        }
        
        return $rules;
    }
    
    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'payment_method.enum' => 'O método de pagamento deve ser Boleto, Cartão de Crédito ou Pix.',
            'value.min' => 'O valor deve ser maior que zero.',
            'due_date.after_or_equal' => 'A data de vencimento deve ser hoje ou após hoje.',
            'cpf_cnpj.min' => 'O CPF/CNPJ deve ter no mínimo 11 caracteres.',
            'card_number.min' => 'O número do cartão deve ter no mínimo 13 caracteres.',
            'card_expiry_month.size' => 'O mês de validade deve ter 2 dígitos.',
            'card_expiry_year.size' => 'O ano de validade deve ter 2 dígitos.',
        ];
    }
}