<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'order_note'
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'shipping_info' => 'array',
            'sub_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'voucher_id' => 'integer',
            'payment_details' => 'array',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function histories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
