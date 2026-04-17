<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;
use App\Events\FlashSaleEvent;

class FlashSale extends Model
{
    use HasFactory;

    protected $table = 'flash_sales';

    protected $fillable = [
        'name',
        'slug',
        'banner_image',
        'start_time',
        'end_time',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            broadcast(new FlashSaleEvent('updated', $model));
            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });
        static::deleted(function ($model) {
            broadcast(new FlashSaleEvent('deleted', $model));

            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });
    }

    public function items()
    {
        return $this->hasMany(FlashSaleItem::class);
    }
}
