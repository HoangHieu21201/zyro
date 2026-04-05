<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'variant_sku',
        'variant_attributes',
        'variant_image',
        'purchased_price',
        'cost_price',
        'quantity',
        'total_price',
        'lookbook_id',
        'lookbook_selections'
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'product_id' => 'integer',
            'variant_id' => 'integer',
            'variant_attributes' => 'array',
            'purchased_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
            'lookbook_id' => 'integer',
            'lookbook_selections' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
