<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'order_status_histories';

    protected $fillable = [
        'order_id',
        'old_status',
        'new_status',
        'note',
        'changed_by_type',
        'changed_by'
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'changed_by' => 'integer',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
