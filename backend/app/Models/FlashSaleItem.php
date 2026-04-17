<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;
use App\Events\FlashSaleEvent;

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

    protected static function booted()
    {
        $trigger = function ($model) {
            if ($model->flashSale) {
                event(new \App\Events\FlashSaleEvent('updated', $model->flashSale));
            }

            \Illuminate\Support\Facades\Cache::forget('client_home_data_dev');
            event(new \App\Events\ClientHomeUpdated());
        };

        static::saved($trigger);
        static::deleted($trigger);
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
