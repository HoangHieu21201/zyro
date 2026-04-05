<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookbook extends Model
{
    use HasFactory;

    protected $table = 'lookbooks';

    protected $fillable = [
        'name',
        'slug',
        'description',
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

    public function items()
    {
        return $this->hasMany(LookbookItem::class);
    }
}
