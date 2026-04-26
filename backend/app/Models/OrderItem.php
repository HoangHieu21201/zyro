<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'order_id'            => 'integer',
            'product_id'          => 'integer',
            'variant_id'          => 'integer',
            'lookbook_id'         => 'integer', 
            'variant_attributes'  => 'array',
            'lookbook_selections' => 'array',
            'purchased_price'     => 'decimal:2',
            'cost_price'          => 'decimal:2',
            'quantity'            => 'integer',
            'total_price'         => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }

    public function lookbook(): BelongsTo
    {
        return $this->belongsTo(Lookbook::class)->withTrashed();
    }
}