<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;
use App\Events\ProductEvent;

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

    protected static function booted()
    {
        $trigger = function ($model) {
            if ($model->product) {
                event(new \App\Events\ProductEvent('updated', $model->product));
            }

            \Illuminate\Support\Facades\Cache::forget('client_home_data_dev');
            event(new \App\Events\ClientHomeUpdated());
        };

        static::saved($trigger);
        static::deleted($trigger);
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
