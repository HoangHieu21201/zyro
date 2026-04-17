<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes; 

    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'variant_name', 
        'user_id',
        'order_id',
        'order_item_id',
        'rating',
        'comment',
        'images',
        'fit_feedback',
        'reviewer_height',
        'reviewer_weight',
        'admin_reply',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'product_id'      => 'integer',
            'user_id'         => 'integer',
            'order_id'        => 'integer',
            'order_item_id'   => 'integer',
            'rating'          => 'integer',
            'images'          => 'array',
            'reviewer_height' => 'integer',
            'reviewer_weight' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class)->withTrashed();
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}