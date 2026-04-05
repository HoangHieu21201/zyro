<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'lookbook_id',
        'lookbook_selections'
    ];

    protected function casts(): array
    {
        return [
            'cart_id' => 'integer',
            'product_id' => 'integer',
            'variant_id' => 'integer',
            'quantity' => 'integer',
            'lookbook_id' => 'integer',
            'lookbook_selections' => 'array',
        ];
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
