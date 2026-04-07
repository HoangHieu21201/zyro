<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vouchers';

    protected $fillable = [
        'name',
        'code',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'min_spend',
        'apply_type',
        'conditions',
        'usage_limit',
        'usage_count',
        'usage_limit_per_user',
        'start_time',
        'end_time',
        'is_public', // Bổ sung cờ Công khai/Bí mật
        'status'
    ];

    protected function casts(): array
    {
        return [
            'discount_value'       => 'decimal:2',
            'max_discount_amount'  => 'decimal:2',
            'min_spend'            => 'decimal:2',
            'conditions'           => 'array', // Lưu JSON các ID sản phẩm/danh mục được áp dụng
            'usage_limit'          => 'integer',
            'usage_count'          => 'integer',
            'usage_limit_per_user' => 'integer',
            'is_public'            => 'boolean',
            'start_time'           => 'datetime',
            'end_time'             => 'datetime',
        ];
    }
}