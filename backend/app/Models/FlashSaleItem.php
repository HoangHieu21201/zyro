<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_items';

    protected $fillable = [
        'flash_sale_id',
        'variant_id',
        'flash_sale_price',
        'quantity_limit',
        'sold_quantity'
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'flash_sale_id' => 'integer',
            'variant_id' => 'integer',
            'flash_sale_price' => 'decimal:2',
            'quantity_limit' => 'integer',
            'sold_quantity' => 'integer',
        ];
    }

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
