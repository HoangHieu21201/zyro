<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'value',
        'label',
        'badge_class',
        'level'
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }
}
