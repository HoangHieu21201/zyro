<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function lookbook()
    {
        return $this->belongsTo(Lookbook::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
