<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case BOLETO = 'BOLETO';
    case CREDIT_CARD = 'CREDIT_CARD';
    case PIX = 'PIX';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::BOLETO => 'Boleto Bancário',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::PIX => 'PIX',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::BOLETO => 'fa-barcode',
            self::CREDIT_CARD => 'fa-credit-card',
            self::PIX => 'fa-qrcode',
        };
    }
}