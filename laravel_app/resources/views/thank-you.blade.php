@php
    use App\Enums\PaymentStatusEnum;
    use App\Enums\PaymentMethodEnum;

    // Convertemos apenas uma vez com tryFrom (aceita string)
    $statusEnum = $payment->status instanceof PaymentStatusEnum
        ? $payment->status
        : PaymentStatusEnum::tryFrom($payment->status);

    $methodEnum = $payment->payment_method instanceof PaymentMethodEnum
        ? $payment->payment_method
        : PaymentMethodEnum::tryFrom($payment->payment_method);
@endphp


@extends('layouts.app')

@section('title', 'Pagamento Processado')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Obrigado pelo seu pagamento!</h4>
            </div>
            <div class="card-body">

                {{-- Status --}}
                <h5>Status do pagamento:
                    @if($statusEnum)
                        <span class="badge bg-{{ $statusEnum->getColor() }}">
                            {{ $statusEnum->getLabel() }}
                        </span>
                    @else
                        <span class="badge bg-secondary">{{ $payment->status }}</span>
                    @endif
                </h5>

                {{-- Detalhes --}}
                <div class="mt-4">
                    <p><strong>Valor:</strong> R$ {{ number_format($payment->value, 2, ',', '.') }}</p>

                    <p><strong>Método de Pagamento:</strong>
                        {{ $methodEnum ? $methodEnum->getLabel() : $payment->payment_method }}
                    </p>

                    @if($payment->due_date)
                        <p><strong>Data de Vencimento:</strong> {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</p>
                    @endif
                </div>

                {{-- Boleto --}}
                @if($methodEnum === PaymentMethodEnum::BOLETO && isset($paymentData['invoice_url']))
                    <div class="mt-4">
                        <h5>Boleto Bancário</h5>
                        <p>Utilize o link abaixo para acessar seu boleto:</p>
                        <a href="{{ $paymentData['invoice_url'] }}" target="_blank" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Visualizar Boleto
                        </a>
                    </div>
                @endif

                {{-- PIX --}}
                @if($methodEnum === PaymentMethodEnum::PIX && isset($paymentData['pix_qrcode']) && isset($paymentData['pix_code']))
                    <div class="mt-4">
                        <h5>Pagamento via PIX</h5>
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <p>Escaneie o QR Code abaixo:</p>
                                <img src="data:image/png;base64,{{ $paymentData['pix_qrcode'] }}" alt="QR Code PIX" class="img-fluid mb-3" style="max-width: 200px;">
                            </div>
                            <div class="col-md-6">
                                <p>Ou copie o código PIX abaixo:</p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="pixCode" value="{{ $paymentData['pix_code'] }}" readonly>
                                    <button class="btn btn-outline-primary" type="button" onclick="copyPixCode()">Copiar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Cartão de Crédito --}}
                @if($methodEnum === PaymentMethodEnum::CREDIT_CARD)
                    <div class="mt-4">
                        <h5>Pagamento com Cartão de Crédito</h5>

                        @if($statusEnum === PaymentStatusEnum::CONFIRMED)
                            <div class="alert alert-success">Pagamento confirmado com sucesso!</div>
                        @elseif($statusEnum === PaymentStatusEnum::PENDING)
                            <div class="alert alert-warning">Pagamento está sendo processado. Em breve enviaremos uma confirmação para o seu e-mail.</div>
                        @elseif(in_array($statusEnum, [PaymentStatusEnum::FAILED, PaymentStatusEnum::DECLINED]))
                            <div class="alert alert-danger">
                                Não foi possível processar o pagamento com o cartão informado. Por favor, tente novamente com outro cartão ou método de pagamento.
                                @if($payment->error_message)
                                    <br>
                                    <small>Motivo: {{ $payment->error_message }}</small>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Ação final --}}
                <div class="text-center mt-4">
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Realizar Novo Pagamento</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyPixCode() {
        var pixCode = document.getElementById("pixCode");
        pixCode.select();
        pixCode.setSelectionRange(0, 99999);
        document.execCommand("copy");

        var button = pixCode.nextElementSibling;
        button.innerHTML = "Copiado!";
        setTimeout(function () {
            button.innerHTML = "Copiar";
        }, 2000);
    }
</script>
@endsection
