@extends('layouts.app')

@section('title', 'Realizar Pagamento')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Realizar Pagamento</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('payments.process') }}" method="POST" id="payment-form">
                    @csrf

                    <div class="mb-4">
                        <h5>Escolha o método de pagamento</h5>
                        @foreach($paymentMethods as $method)
                        <div class="form-check">
                            <input class="form-check-input payment-method" type="radio" name="payment_method" 
                                   id="{{ strtolower($method->value) }}" value="{{ $method->value }}" 
                                   {{ old('payment_method') == $method->value ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ strtolower($method->value) }}">
                                <i class="fas {{ $method->getIcon() }}"></i> {{ $method->getLabel() }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <h5>Informações do Cliente</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpf_cnpj" class="form-label">CPF/CNPJ</label>
                            <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" value="{{ old('cpf_cnpj') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="address" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="address_number" class="form-label">Número</label>
                            <input type="text" class="form-control" id="address_number" name="address_number" value="{{ old('address_number') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="address_complement" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="address_complement" name="address_complement" value="{{ old('address_complement') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="province" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="province" name="province" value="{{ old('province') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postal_code" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="value" class="form-label">Valor (R$)</label>
                            <input type="number" class="form-control" id="value" name="value" step="0.01" min="0.01" value="{{ old('value') ?? '100.00' }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="description" name="description" value="{{ old('description') ?? 'Pagamento de serviço' }}">
                        </div>
                    </div>

                    <!-- Campo específico para Boleto -->
                    <div id="boleto-fields" class="payment-specific-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Data de Vencimento</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" value="{{ old('due_date') ?? date('Y-m-d', strtotime('+3 days')) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Campos específicos para Cartão de Crédito -->
                    <div id="credit-card-fields" class="payment-specific-fields" style="display: none;">
                        <h5 class="mt-4">Informações do Cartão</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="card_holder_name" class="form-label">Nome no Cartão</label>
                                <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" value="{{ old('card_holder_name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="card_number" class="form-label">Número do Cartão</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" value="{{ old('card_number') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="card_expiry_month" class="form-label">Mês de Validade</label>
                                <input type="text" class="form-control" id="card_expiry_month" name="card_expiry_month" placeholder="MM" maxlength="2" value="{{ old('card_expiry_month') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="card_expiry_year" class="form-label">Ano de Validade</label>
                                <input type="text" class="form-control" id="card_expiry_year" name="card_expiry_year" placeholder="YY" maxlength="2" value="{{ old('card_expiry_year') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="card_ccv" class="form-label">CVC/CVV</label>
                                <input type="text" class="form-control" id="card_ccv" name="card_ccv" maxlength="4" value="{{ old('card_ccv') }}">
                            </div>
                        </div>

                        <h5 class="mt-4">Informações do Titular do Cartão</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="holder_name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="holder_name" name="holder_name" value="{{ old('holder_name') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="holder_email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="holder_email" name="holder_email" value="{{ old('holder_email') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="holder_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="holder_cpf_cnpj" name="holder_cpf_cnpj" value="{{ old('holder_cpf_cnpj') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="holder_phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="holder_phone" name="holder_phone" value="{{ old('holder_phone') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="holder_postal_code" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="holder_postal_code" name="holder_postal_code" value="{{ old('holder_postal_code') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="holder_address_number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="holder_address_number" name="holder_address_number" value="{{ old('holder_address_number') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="holder_address_complement" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="holder_address_complement" name="holder_address_complement" value="{{ old('holder_address_complement') }}">
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">Finalizar Pagamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Mostrar/ocultar campos específicos para cada método de pagamento
        function togglePaymentFields() {
            $('.payment-specific-fields').hide();
            
            if ($('#boleto').is(':checked')) {
                $('#boleto-fields').show();
            } else if ($('#credit-card').is(':checked')) {
                $('#credit-card-fields').show();
            }
        }

        // Verificar o estado inicial
        togglePaymentFields();

        // Adicionar evento de mudança para os radio buttons
        $('.payment-method').on('change', togglePaymentFields);

        // Auto-preenchimento dos dados do cartão com os dados do cliente
        $('#name').on('change', function() {
            if (!$('#card_holder_name').val()) {
                $('#card_holder_name').val($(this).val());
            }
            if (!$('#holder_name').val()) {
                $('#holder_name').val($(this).val());
            }
        });

        $('#email').on('change', function() {
            if (!$('#holder_email').val()) {
                $('#holder_email').val($(this).val());
            }
        });

        $('#cpf_cnpj').on('change', function() {
            if (!$('#holder_cpf_cnpj').val()) {
                $('#holder_cpf_cnpj').val($(this).val());
            }
        });

        $('#phone').on('change', function() {
            if (!$('#holder_phone').val()) {
                $('#holder_phone').val($(this).val());
            }
        });

        $('#postal_code').on('change', function() {
            if (!$('#holder_postal_code').val()) {
                $('#holder_postal_code').val($(this).val());
            }
        });

        $('#address_number').on('change', function() {
            if (!$('#holder_address_number').val()) {
                $('#holder_address_number').val($(this).val());
            }
        });

        $('#address_complement').on('change', function() {
            if (!$('#holder_address_complement').val()) {
                $('#holder_address_complement').val($(this).val());
            }
        });
    });
</script>
@endsection