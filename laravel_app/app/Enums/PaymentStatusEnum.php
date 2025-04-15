<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case RECEIVED = 'RECEIVED';
    case DECLINED = 'DECLINED';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';
    case CANCELED = 'CANCELED';
    case ERROR = 'ERROR';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::CONFIRMED => 'Confirmado',
            self::RECEIVED => 'Recebido',
            self::DECLINED => 'Recusado',
            self::FAILED => 'Falhou',
            self::REFUNDED => 'Reembolsado',
            self::CANCELED => 'Cancelado',
            self::ERROR => 'Erro',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::CONFIRMED, self::RECEIVED => 'success',
            self::DECLINED, self::FAILED, self::ERROR => 'danger',
            self::REFUNDED, self::CANCELED => 'secondary',
        };
    }
}