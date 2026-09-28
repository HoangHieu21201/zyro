<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'subject',
        'message',
        'status',
        'is_spam',
        'reply_message',
        'replied_at',
        'replied_by'
    ];

    protected function casts(): array
    {
        return [
            'is_spam' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }
}