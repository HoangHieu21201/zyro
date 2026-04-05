<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'cost_price',
        'price',
        'promotional_price',
        'stock_quantity',
        'reserved_stock',
        'image_url',
        'is_default'
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'cost_price' => 'decimal:2',
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'reserved_stock' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id');
    }
}
