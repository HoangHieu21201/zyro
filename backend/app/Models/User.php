<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'tier_id',
        'full_name',
        'email',
        'phone',
        'password',
        'avatar_url',
        'status',
        'birthday',
        'gender',
        'height_cm',
        'weight_kg',
        'google_id',
        'facebook_id',
        'accumulated_spent',
        'pending_spent',
        'accumulated_orders',
        'remember_token',
        'email_verified_at'
    ];

    protected function casts(): array
    {
        return [
            'tier_id' => 'integer',
            'birthday' => 'date',
            'height_cm' => 'integer',
            'weight_kg' => 'decimal:2',
            'accumulated_spent' => 'decimal:2',
            'pending_spent' => 'decimal:2',
            'accumulated_orders' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tier()
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
}
