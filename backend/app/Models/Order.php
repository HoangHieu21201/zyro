<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'user_id',
        'shipping_info',
        'sub_total',
        'discount_amount',
        'shipping_fee',
        'total_amount',
        'refunded_amount',
        'voucher_id',
        'payment_method',
        'payment_status',
        'transaction_id',
        'payment_details',
        'shipping_provider',
        'tracking_number',
        'shipping_status',
        'status',
        'return_status', 
        'order_note'
    ];

    protected function casts(): array
    {
        return [
            'user_id'         => 'integer',
            'voucher_id'      => 'integer',
            'shipping_info'   => 'array',
            'payment_details' => 'array',
            'sub_total'       => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_fee'    => 'decimal:2',
            'total_amount'    => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('id', 'desc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class)->withTrashed();
    }
}