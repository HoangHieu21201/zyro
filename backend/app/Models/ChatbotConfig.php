<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConfig extends Model
{
    use HasFactory;

    protected $table = 'chatbot_configs';

    protected $fillable = [
        'key',
        'value',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Hàm tiện ích giúp lấy cấu hình bot cực nhanh
     */
    public static function getValue($key, $default = null)
    {
        $config = self::where('key', $key)->where('is_active', true)->first();
        return $config ? $config->value : $default;
    }
}