<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'meta_value'
    ];

    protected function casts(): array
    {
        return [
            'attribute_id' => 'integer',
        ];
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
