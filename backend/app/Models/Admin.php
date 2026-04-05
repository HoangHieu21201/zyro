<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens; 

class Admin extends Authenticatable 
{
    use HasApiTokens, HasFactory, SoftDeletes; 

    protected $table = 'admins';

    protected $fillable = [
        'role_id',
        'fullname',
        'email',
        'password',
        'phone',
        'avatar_url',
        'status',
        'address',
        'email_verified_at'
    ];

    protected function casts(): array
    {
        return [
            'role_id' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
