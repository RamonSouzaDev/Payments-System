<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'payment_method',
        'value',
        'status',
        'external_id',
        'invoice_url',
        'pix_code',
        'pix_qrcode',
        'due_date',
        'error_message',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'payment_method' => PaymentMethodEnum::class,
        'status' => PaymentStatusEnum::class,
    ];

    /**
     * Get the customer that owns the payment.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    /**
     * Get the formatted payment method name.
     */
    public function getPaymentMethodLabelAttribute()
    {
        return $this->payment_method->getLabel();
    }
    
    /**
     * Get the formatted status name.
     */
    public function getStatusLabelAttribute()
    {
        return $this->status->getLabel();
    }
    
    /**
     * Get the status color class.
     */
    public function getStatusColorAttribute()
    {
        return $this->status->getColor();
    }
}