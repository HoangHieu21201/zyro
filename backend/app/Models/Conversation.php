<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'user_id',
        'session_id',
        'admin_id',
        'status',
        'last_message_snippet',
        'last_message_at'
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'admin_id' => 'integer',
            'last_message_at' => 'datetime',
        ];
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}