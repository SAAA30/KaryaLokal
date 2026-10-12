<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'customer_id', 'recipient_name', 'recipient_phone', 'shipping_address',
        'city', 'province', 'postal_code', 'shipping_method', 'tracking_number', 'status',
        'payment_method', 'payment_status', 'subtotal', 'shipping_cost', 'discount_total', 'total',
        'payment_proof_path', 'payment_submitted_at', 'payment_verified_at', 'payment_rejection_reason',
        'payment_expires_at',
        'recipient_gift_name', 'gift_sender_name', 'gift_message', 'gift_theme', 'paid_at', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'received_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'payment_expires_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentDeadline(): ?Carbon
    {
        if ($this->payment_expires_at) {
            return $this->payment_expires_at;
        }

        if (!$this->created_at) {
            return null;
        }

        $createdAt = $this->created_at->copy();

        return match ($this->payment_method) {
            'qris' => $createdAt->addMinutes((int) config('services.karyalokal_payment.qris_expiry_minutes', 30)),
            'virtual_account' => $createdAt->addHours((int) config('services.karyalokal_payment.va_expiry_hours', 2)),
            default => $createdAt->addHours((int) config('services.karyalokal_payment.bank_expiry_hours', 24)),
        };
    }
}
