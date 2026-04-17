<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;
use App\Events\ClientHomeUpdated;
use App\Events\LookbookEvent;

class LookbookItem extends Model
{
    use HasFactory;

    protected $table = 'lookbook_items';

    protected $fillable = [
        'lookbook_id',
        'product_id',
        'pin_coordinates',
        'sort_order'
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'lookbook_id' => 'integer',
            'product_id' => 'integer',
            'pin_coordinates' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted()
    {
        $trigger = function ($model) {
            if ($model->lookbook) {
                event(new \App\Events\LookbookEvent('updated', $model->lookbook));
            }

            \Illuminate\Support\Facades\Cache::forget('client_home_data_dev');
            event(new \App\Events\ClientHomeUpdated());
        };

        static::saved($trigger);
        static::deleted($trigger);
    }


    public function lookbook()
    {
        return $this->belongsTo(Lookbook::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
