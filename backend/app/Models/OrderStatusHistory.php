<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    // Tắt updated_at vì lịch sử chỉ cần created_at (Thời điểm ghi nhận)
    public const UPDATED_AT = null;

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
            'order_id'   => 'integer',
            'changed_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changer(): MorphTo
    {
        return $this->morphTo('changer', 'changed_by_type', 'changed_by')->withTrashed();
    }
}