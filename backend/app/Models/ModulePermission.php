<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    public $timestamps = false;

    protected $table = 'module_permissions';

    protected $fillable = [
        'module_name',
        'module_code',
        'required_level'
    ];

    protected function casts(): array
    {
        return [
            'required_level' => 'integer',
        ];
    }
}