<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;
use App\Events\LookbookEvent;

class Lookbook extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lookbooks';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'gender', 
        'main_image',
        'total_price_estimate',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'total_price_estimate' => 'decimal:2',
        ];
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            broadcast(new LookbookEvent('updated', $model));
            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });
        static::deleted(function ($model) {
            broadcast(new LookbookEvent('deleted', $model));

            Cache::forget('client_home_data_dev');
            broadcast(new ClientHomeUpdated());
        });
    }


    public function items()
    {
        return $this->hasMany(LookbookItem::class);
    }
}